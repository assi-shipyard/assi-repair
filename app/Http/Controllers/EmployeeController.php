<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AccountDirectoryRequest;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    private const PROFILE_PHOTO_MAX_BYTES = 524288;

    private const PROFILE_PHOTO_MAX_DIMENSION = 1600;

    public function index(AccountDirectoryRequest $request): View
    {
        $filters = $request->validated();
        $base_query = Employee::query()->where(function ($query): void {
            $query->whereDoesntHave('user')->orWhereHas('user', fn ($user_query) => $user_query->where('account_type', User::TYPE_INTERNAL));
        });
        $total = (clone $base_query)->count();
        $active_total = (clone $base_query)->where('status', 'active')->count();
        $inactive_total = (clone $base_query)->where('status', 'inactive')->count();
        $employees = $base_query->with(['position.organizational_unit', 'manager', 'user'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')->orWhere('employee_id', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhereHas('position', fn ($position_query) => $position_query->where('name', 'like', '%'.$search.'%')
                            ->orWhereHas('organizational_unit', fn ($unit_query) => $unit_query->where('name', 'like', '%'.$search.'%')))
                        ->orWhereHas('manager', fn ($manager_query) => $manager_query->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('name')->paginate(12)->withQueryString();

        return view('employee.index', compact('employees', 'filters', 'total', 'active_total', 'inactive_total'));
    }

    public function create(): View
    {
        $positions = Position::orderBy('name')->get();
        $managers = Employee::orderBy('name')->get();

        return view('employee.create', compact('positions', 'managers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validation = Validator::make($request->all(), $this->rules(), $this->messages(false));

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        $payload = $validation->validated();

        try {
            $this->persist_employee(new Employee, $payload, $request);
        } catch (RuntimeException $exception) {
            return redirect()->back()
                ->withErrors(['profile_photo' => $exception->getMessage()])
                ->withInput();
        }

        return redirect()->route('employee.index')->with('success', 'Karyawan berhasil dibuat.');
    }

    public function show(string $employee): View
    {
        $employee = $this->find_employee($employee, [
            'position',
            'manager',
            'position.organizational_unit',
        ]);

        return view('employee.show', compact('employee'));
    }

    public function edit(string $employee): View
    {
        $employee = $this->find_employee($employee, ['position.organizational_unit', 'user']);

        $positions = Position::orderBy('name')->get();
        $managers = Employee::where('id', '!=', $employee->id)->orderBy('name')->get();

        return view('employee.edit', compact('employee', 'positions', 'managers'));
    }

    public function update(Request $request, string $employee): RedirectResponse
    {
        $employee = $this->find_employee($employee);

        $validation = Validator::make($request->all(), $this->rules($employee->id, true), $this->messages(true));

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        $payload = $validation->validated();

        if ((int) ($payload['manager_id'] ?? 0) === $employee->id) {
            return redirect()->back()
                ->withErrors(['manager_id' => 'Seorang karyawan tidak dapat menjadi manajer dirinya sendiri.'])
                ->withInput();
        }

        $has_linked_user = $employee->user()->exists() || User::query()->where('employee_id', $employee->employee_id)->exists();

        if (! $has_linked_user && empty($payload['password'])) {
            $payload['password'] = $payload['employee_id'];
        }

        try {
            $this->persist_employee($employee, $payload, $request);
        } catch (RuntimeException $exception) {
            return redirect()->back()
                ->withErrors(['profile_photo' => $exception->getMessage()])
                ->withInput();
        }

        return redirect()->route('employee.index')->with('success', 'Karyawan berhasil diperbarui.');
    }

    public function destroy(string $employee): RedirectResponse
    {
        $employee = $this->find_employee($employee);

        if ($employee->profile_photo_path !== null) {
            Storage::disk('ship')->delete($employee->profile_photo_path);
            Storage::disk('local')->delete($employee->profile_photo_path);
        }

        if ($employee->subordinates()->exists()) {
            $employee->subordinates()->update([
                'direct_manager_employee_id' => $employee->direct_manager_employee_id,
            ]);
        }

        $employee->delete();

        return redirect()->route('employee.index')->with('success', 'Karyawan berhasil dihapus.');
    }

    public function photo(string $employee): StreamedResponse
    {
        $employee = $this->find_employee($employee);

        if ($employee->profile_photo_path === null) {
            abort(404);
        }

        $disk = Storage::disk('ship')->exists($employee->profile_photo_path) ? 'ship' : 'local';

        if (! Storage::disk($disk)->exists($employee->profile_photo_path)) {
            abort(404);
        }

        return Storage::disk($disk)->response($employee->profile_photo_path);
    }

    private function rules(?int $employee_id = null, bool $is_update = false): array
    {
        $email_rule = 'nullable|email|unique:employees,email';
        $employee_code_rule = 'required|string|digits:9|regex:/^[1-9][0-9]{8}$/|unique:employees,employee_id';

        if ($employee_id !== null) {
            $email_rule .= ','.$employee_id;
            $employee_code_rule .= ','.$employee_id;
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => $email_rule,
            'employee_id' => $employee_code_rule,
            'position_id' => 'required|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'remove_photo' => 'nullable|boolean',
        ];

        $existing_employee = $employee_id === null ? null : Employee::query()->find($employee_id);
        $linked_user_id = $existing_employee?->user_id;
        if ($linked_user_id === null && $existing_employee !== null) {
            $linked_user_id = User::query()->where('employee_id', $existing_employee->employee_id)->value('id');
        }
        $rules['email'] = [...explode('|', $email_rule), 'max:64', Rule::unique('users', 'email')->ignore($linked_user_id)];
        $rules['employee_id'] = [
            ...explode('|', $employee_code_rule),
            Rule::unique('users', 'employee_id')->where(fn ($query) => $query->where('account_type', '!=', User::TYPE_INTERNAL)),
            Rule::unique('users', 'username'),
        ];

        if ($is_update) {
            $rules['status'] = 'required|in:active,inactive';
            $rules['password'] = 'nullable|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }

    private function find_employee(string $employee, array $relations = []): Employee
    {
        $query = Employee::query()->with($relations);

        if (preg_match('/^[0-9a-fA-F-]{36}$/', $employee) === 1) {
            return $query->where('unique_id', $employee)->firstOrFail();
        }

        return $query->findOrFail((int) $employee);
    }

    private function messages(bool $is_update = false): array
    {
        $messages = [
            'name.required' => 'Nama karyawan wajib diisi.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            'email.max' => 'Email maksimal 64 karakter.',
            'email.unique' => 'Email sudah ada.',
            'employee_id.required' => 'Nomor induk karyawan wajib diisi.',
            'employee_id.string' => 'Nomor induk karyawan harus berupa 9 digit angka.',
            'employee_id.regex' => 'NIK harus tepat 9 digit dan tidak boleh diawali angka 0. Empat digit pertama merupakan tahun.',
            'employee_id.digits' => 'Nomor induk karyawan harus berupa 9 digit angka.',
            'employee_id.unique' => 'Nomor induk karyawan sudah ada.',
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status harus berupa aktif atau tidak aktif.',
            'position_id.required' => 'Jabatan wajib diisi.',
            'position_id.exists' => 'Jabatan yang dipilih tidak ada.',
            'manager_id.exists' => 'Manajer yang dipilih tidak ada.',
            'profile_photo.image' => 'Foto profil harus berupa gambar.',
            'profile_photo.mimes' => 'Foto profil harus berformat JPG, JPEG, PNG, atau WEBP.',
            'profile_photo.max' => 'Ukuran foto profil maksimal 10 MB.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ];

        // if (! $is_update) {
        //     $messages['password.required'] = 'Kata sandi wajib diisi.';
        // }

        return $messages;
    }

    private function persist_employee(Employee $employee, array $payload, Request $request): void
    {
        DB::transaction(function () use ($employee, $payload, $request): void {
            $linked_user = $employee->user;

            if ($linked_user === null && $employee->employee_id !== null) {
                $linked_user = User::query()->where('employee_id', $employee->employee_id)->first();
            }

            if ($linked_user === null) {
                $linked_user = User::query()->where('employee_id', $payload['employee_id'])->first();
            }

            if ($linked_user === null) {
                $linked_user = new User;
            }

            if ($linked_user->exists && ($linked_user->account_type !== User::TYPE_INTERNAL || $linked_user->hasRole('admin'))) {
                throw ValidationException::withMessages(['employee_id' => 'Identitas ini digunakan oleh akun non-karyawan.']);
            }

            $linked_user->account_type = User::TYPE_INTERNAL;
            $linked_user->employee_id = $payload['employee_id'];
            $linked_user->email = $payload['email'] ?? null;

            if (! empty($payload['password'])) {
                $linked_user->password = $payload['password'];
            } elseif (! $linked_user->exists) {
                $linked_user->password = $payload['employee_id'];
            }

            $linked_user->save();

            $position = Position::query()->findOrFail($payload['position_id']);

            if ($request->boolean('remove_photo') && $employee->profile_photo_path !== null) {
                Storage::disk('ship')->delete($employee->profile_photo_path);
                Storage::disk('local')->delete($employee->profile_photo_path);
                $employee->profile_photo_path = null;
            }

            if ($request->hasFile('profile_photo')) {
                if ($employee->profile_photo_path !== null) {
                    Storage::disk('ship')->delete($employee->profile_photo_path);
                    Storage::disk('local')->delete($employee->profile_photo_path);
                }

                $employee->profile_photo_path = $this->store_profile_photo($request->file('profile_photo'));
            }

            $employee->fill([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'employee_id' => $payload['employee_id'],
                'status' => $payload['status'] ?? 'active',
                'user_id' => $linked_user->id,
                'position_id' => $position->id,
                'direct_manager_employee_id' => $payload['manager_id'] ?? null,
            ]);

            $employee->save();
        });
    }

    private function store_profile_photo(UploadedFile $profile_photo): string
    {
        $image = Image::read($profile_photo->getRealPath());
        $max_dimension = self::PROFILE_PHOTO_MAX_DIMENSION;
        $minimum_quality = 35;

        while ($max_dimension >= 500) {
            $working_image = clone $image;
            $working_image->scaleDown(width: $max_dimension, height: $max_dimension);

            for ($quality = 85; $quality >= $minimum_quality; $quality -= 5) {
                $encoded_image = $working_image->toJpeg(quality: $quality, progressive: true, strip: true);

                if ($encoded_image->size() <= self::PROFILE_PHOTO_MAX_BYTES) {
                    $filename = sprintf(
                        'employees/profile-photos/%s_%s.jpg',
                        now()->format('YmdHis'),
                        bin2hex(random_bytes(6))
                    );

                    Storage::disk('ship')->put($filename, (string) $encoded_image);

                    return $filename;
                }
            }

            $max_dimension -= 200;
        }

        throw new RuntimeException('Foto profil tidak dapat dikompres di bawah 512 KB. Gunakan gambar dengan resolusi lebih kecil.');
    }
}
