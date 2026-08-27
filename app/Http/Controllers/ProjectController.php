<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\OrganizationalUnit;
use App\Models\Project;
use App\Models\Ship;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        // Fetch projects with related data and apply search filter if provided
        $projects = Project::with(['ship', 'leader', 'ppc', 'divisions', 'owner_surveyors'])
            ->when($request->filled('search_project'), function ($query) use ($request) {
                $search = trim((string) $request->input('search_project'));

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('project_code', 'like', '%'.$search.'%')
                        ->orWhere('project_type', 'like', '%'.$search.'%')
                        ->orWhereHas('ship', function ($shipQuery) use ($search) {
                            $shipQuery->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->get();

        // If the request is an AJAX request, return a JSON response with the rendered HTML and project count
        if ($request->ajax()) {
            return new JsonResponse([
                'html' => view('project.partials.projectcards', compact('projects'))->render(),
                'count' => $projects->count(),
            ]);
        }

        return view('project.index', compact('projects'));
    }

    public function create()
    {
        // Fetch ships, employees, and divisions for the form
        $ships = Ship::with(['company', 'type'])->orderBy('name')->get();
        $employees = Employee::with('position.organizational_unit')->orderBy('name')->get();

        // Division is strictly filtered to only "Divisi Reparasi dan Rekayasa Umum" and "Divisi Bangunan Baru" for project creation.
        $divisions = OrganizationalUnit::query()
            ->where('type', 'division')
            ->whereIn('name', ['Divisi Reparasi dan Rekayasa Umum', 'Divisi Bangunan Baru'])
            ->orderBy('name')
            ->get();

        // Get PPC employees for the form, filtering by position name containing "PPC"
        $ppc_employees = $employees->filter(function (Employee $employee) {
            return str_contains(strtolower((string) $employee->position?->name), 'ppc');
        });

        // Keep code empty until ship and project type are selected by user.
        $nextProjectCode = '';
        $projectTypeOptions = [
            'Docking Repair',
            'Floating Repair',
            'Emergency Docking',
            'Other',
        ];

        return view('project.create', compact('ships', 'employees', 'divisions', 'ppc_employees', 'nextProjectCode', 'projectTypeOptions'));
    }

    public function generate_code_preview(Request $request): JsonResponse
    {
        // Validate the incoming request data for generating a project code preview
        $validation = Validator::make($request->all(), [
            'project_type' => 'required|string',
            'ship_id' => 'required|exists:ships,id',
            'start_date_estimation' => 'nullable|date',
        ], [
            'project_type.required' => 'Tipe proyek wajib dipilih untuk generate kode proyek.',
            'ship_id.required' => 'Kapal wajib dipilih untuk generate kode proyek.',
            'ship_id.exists' => 'Kapal yang dipilih tidak valid.',
            'start_date_estimation.date' => 'Tanggal estimasi mulai tidak valid.',
        ]);

        // If validation fails, return a JSON response with the first error message and a 422 status code
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 422);
        }

        // Retrieve the validated data and generate a unique project code based on the provided project type, ship ID, and start date estimation
        $data = $validation->validated();

        // Generate a unique project code based on the provided project type, ship ID, and start date estimation
        $projectCode = $this->generate_unique_project_code(
            (string) ($data['project_type'] ?? ''),
            isset($data['ship_id']) ? (int) $data['ship_id'] : null,
            $data['start_date_estimation'] ?? null
        );

        return response()->json([
            'project_code' => $projectCode,
        ]);
    }

    public function store(Request $request)
    {
        // Validate the incoming request data against the defined rules and messages
        $validation = Validator::make($request->all(), $this->validation_rules(), $this->validation_messages());

        // If validation fails, redirect back with errors and input data
        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        // Retrieve the validated data, division IDs, and normalized surveyor rows from the request
        $data = $validation->validated();
        $divisionValidation = $this->validate_project_team_division($data);

        if ($divisionValidation !== null) {
            return back()->withErrors($divisionValidation)->withInput();
        }

        $divisionIds = $data['division_ids'] ?? [];
        $surveyorRows = $this->normalize_surveyors($data['owner_surveyors'] ?? []);

        // Re-generate project code on server to avoid stale or manipulated client-side values.
        $data['project_code'] = $this->generate_unique_project_code(
            (string) ($data['project_type'] ?? ''),
            isset($data['ship_id']) ? (int) $data['ship_id'] : null,
            $data['start_date_estimation'] ?? null
        );

        // Generate a unique UUID for the project and set the created_by field to the authenticated user's ID
        $data['unique_id'] = (string) Str::uuid();
        $data['created_by'] = auth()->id();

        // Remove the division_ids and owner_surveyors from the data array before creating the project
        unset($data['division_ids'], $data['owner_surveyors'], $data['division_manager_employee_id']);

        // Create the project, sync the divisions, and create the owner surveyors
        $project = Project::create($data);
        $project->divisions()->sync($divisionIds);
        $project->owner_surveyors()->createMany($surveyorRows);

        $createdFlag = \App\Models\NotificationFlag::where('code', 'project.created')->first();
        if ($createdFlag) {
            $createdUserIds = \App\Models\NotificationSetting::where('notification_flag_id', $createdFlag->id)->pluck('user_id');
            if ($createdUserIds->isNotEmpty()) {
                $usersToNotify = \App\Models\User::whereIn('id', $createdUserIds)->get();
                \Illuminate\Support\Facades\Notification::send($usersToNotify, new \App\Notifications\ProjectNotification($project, 'project.created'));
            }
        }

        return redirect()
            ->route('project.index')
            ->with('success', 'Proyek '.$project->project_code.' untuk kapal '.$project->ship->name.' berhasil dibuat.');
    }

    public function show(string $id)
    {
        // Fetch the project with related data based on the unique_id. If not found, redirect back with an error message.
        $project = Project::with(['ship.company', 'leader', 'ppc', 'divisions', 'owner_surveyors', 'created_by', 'job_documents'])
            ->firstWhere('unique_id', $id);

        // If the project is not found, redirect back to the project index with an error message
        if (! $project) {
            return redirect()->route('project.index')->with('error', 'Proyek tidak ditemukan.');
        }

        return view('project.show', compact('project'));
    }

    public function edit(string $id)
    {
        // Fetch the project with related divisions and owner surveyors based on the unique_id. If not found, redirect back with an error message.
        $project = Project::with(['divisions', 'owner_surveyors'])->firstWhere('unique_id', $id);

        // If the project is not found, redirect back to the project index with an error message
        if (! $project) {
            return redirect()->route('project.index')->with('error', 'Proyek tidak ditemukan.');
        }

        // Fetch ships, employees, and divisions for the form
        $ships = Ship::with('company')->orderBy('name')->get();
        $employees = Employee::with('position.organizational_unit')->orderBy('name')->get();
        $divisions = OrganizationalUnit::query()
            ->where('type', 'division')
            ->orderBy('name')
            ->get();
        $projectTypeOptions = [
            'Docking Repair',
            'Floating Repair',
            'Emergency Docking',
            'Other',
        ];

        return view('project.edit', compact('project', 'ships', 'employees', 'divisions', 'projectTypeOptions'));
    }

    public function update(Request $request, string $id)
    {
        // Fetch the project based on the unique_id. If not found, redirect back with an error message.
        $project = Project::firstWhere('unique_id', $id);

        // If the project is not found, redirect back to the project index with an error message
        if (! $project) {
            return redirect()->route('project.index')->with('error', 'Proyek tidak ditemukan.');
        }

        // Validate the incoming request data against the defined rules and messages, ignoring the current project's ID for unique validation
        $validation = Validator::make($request->all(), $this->validation_rules($project->id), $this->validation_messages());

        // If validation fails, redirect back with errors and input data
        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        // Retrieve the validated data, division IDs, and normalized surveyor rows from the request
        $data = $validation->validated();
        $divisionValidation = $this->validate_project_team_division($data);

        if ($divisionValidation !== null) {
            return back()->withErrors($divisionValidation)->withInput();
        }

        $divisionIds = $data['division_ids'] ?? [];
        $surveyorRows = $this->normalize_surveyors($data['owner_surveyors'] ?? []);

        if (
            (int) ($data['ship_id'] ?? 0) !== (int) $project->ship_id
            || (string) ($data['project_type'] ?? '') !== (string) ($project->project_type ?? '')
            || (string) ($data['start_date_estimation'] ?? '') !== (string) ($project->start_date_estimation?->format('Y-m-d') ?? '')
        ) {
            $data['project_code'] = $this->generate_unique_project_code(
                (string) ($data['project_type'] ?? ''),
                isset($data['ship_id']) ? (int) $data['ship_id'] : null,
                $data['start_date_estimation'] ?? null
            );
        }

        // Remove the division_ids and owner_surveyors from the data array before updating the project
        unset($data['division_ids'], $data['owner_surveyors'], $data['division_manager_employee_id']);

        // Update the project, sync the divisions, and delete and recreate the owner surveyors
        $oldStatus = $project->status;

        $project->update($data);
        $project->divisions()->sync($divisionIds);
        $project->owner_surveyors()->delete();
        $project->owner_surveyors()->createMany($surveyorRows);

        if ($oldStatus !== 'Completed' && $project->status === 'Completed') {
            $finishedFlag = \App\Models\NotificationFlag::where('code', 'project.finished')->first();
            if ($finishedFlag) {
                $finishedUserIds = \App\Models\NotificationSetting::where('notification_flag_id', $finishedFlag->id)->pluck('user_id');
                if ($finishedUserIds->isNotEmpty()) {
                    $usersToNotify = \App\Models\User::whereIn('id', $finishedUserIds)->get();
                    \Illuminate\Support\Facades\Notification::send($usersToNotify, new \App\Notifications\ProjectNotification($project, 'project.finished'));
                }
            }
        }

        return redirect()->route('project.index')->with('success', 'Proyek '.$project->project_code.' untuk kapal '.$project->ship->name.' berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        // Fetch the project based on the unique_id. If not found, redirect back with an error message.
        $project = Project::firstWhere('unique_id', $id);

        // If the project is not found, redirect back to the project index with an error message
        if (! $project) {
            return redirect()->route('project.index')->with('error', 'Proyek tidak ditemukan.');
        }

        // Check if the project's progress is greater than 0. If so, prevent deletion and redirect back with an error message
        if ((float) $project->progress > 0) {
            return redirect()->route('project.index')->with('error', 'Proyek tidak dapat dihapus karena memiliki progress lebih dari 0%.');
        }

        // Store the project code for the success message, then delete the project and redirect back to the project index with a success message
        $projectCode = $project->project_code;
        $project->delete();

        return redirect()->route('project.index')->with('success', 'Proyek '.$projectCode.''.' berhasil dihapus.');
    }

    // This function returns the validation rules for creating or updating a project. If an $ignoreProjectId is provided, it will ignore that project ID when checking for unique project codes.
    private function validation_rules(?int $ignoreProjectId = null): array
    {
        $projectCodeRule = 'nullable|string';

        if ($ignoreProjectId) {
            $projectCodeRule = 'required|unique:projects,project_code,'.$ignoreProjectId;
        }

        return [
            'project_code' => $projectCodeRule,
            'ship_id' => 'required|exists:ships,id',
            'project_leader_employee_id' => 'nullable|exists:employees,id',
            'project_ppc_employee_id' => 'nullable|exists:employees,id',
            'division_manager_employee_id' => 'nullable|exists:employees,id',
            'division_ids' => 'required|array|min:1',
            'division_ids.*' => 'exists:organizational_units,id',
            'project_type' => 'required|string',
            'start_date_estimation' => 'nullable|date',
            'end_date_estimation' => 'nullable|date|after_or_equal:start_date_estimation',
            'start_date_actual' => 'nullable|date',
            'end_date_actual' => 'nullable|date|after_or_equal:start_date_actual',
            'progress' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:Not Started,In Progress,Completed',
            'comment' => 'nullable|string',
            'owner_surveyors' => 'nullable|array',
            'owner_surveyors.*.name' => 'nullable|string',
            'owner_surveyors.*.company' => 'nullable|string',
            'owner_surveyors.*.position' => 'nullable|string',
            'owner_surveyors.*.email' => 'nullable|email',
            'owner_surveyors.*.phone' => 'nullable|string',
        ];
    }

    // This function returns custom validation messages for the project form fields, providing user-friendly error messages for various validation failures.
    private function validation_messages(): array
    {
        return [
            'project_code.required' => 'Kode proyek wajib diisi.',
            'project_code.unique' => 'Kode proyek sudah digunakan.',
            'ship_id.required' => 'Kapal belum dipilih.',
            'ship_id.exists' => 'Kapal yang dipilih tidak valid.',
            'project_leader_employee_id.exists' => 'PIMPRO yang dipilih tidak valid.',
            'project_ppc_employee_id.exists' => 'PPC yang dipilih tidak valid.',
            'division_manager_employee_id.exists' => 'Manajer divisi yang dipilih tidak valid.',
            'division_ids.required' => 'Divisi pelaksana akan terisi otomatis dari PIMPRO/PPC/Manajer.',
            'division_ids.array' => 'Divisi pelaksana tidak valid.',
            'division_ids.*.exists' => 'Ada divisi pelaksana yang tidak valid.',
            'project_type.required' => 'Tipe proyek wajib diisi.',
            'end_date_estimation.after_or_equal' => 'Tanggal estimasi selesai harus sama atau setelah tanggal estimasi mulai.',
            'end_date_actual.after_or_equal' => 'Tanggal selesai aktual harus sama atau setelah tanggal mulai aktual.',
            'progress.numeric' => 'Progress harus berupa angka.',
            'progress.min' => 'Progress tidak boleh kurang dari 0%.',
            'progress.max' => 'Progress tidak boleh lebih dari 100%.',
            'status.in' => 'Status proyek tidak valid.',
            'owner_surveyors.*.email.email' => 'Email owner surveyor harus berupa alamat email yang valid.',
        ];
    }

    // This function normalizes the surveyors' data by trimming strings and converting empty strings to null.
    private function normalize_surveyors(array $surveyors): array
    {
        // Use a collection to map and filter the surveyors array, trimming strings and converting empty strings to null. Only include surveyors with a non-empty name.
        return collect($surveyors)
            ->map(function (array $surveyor): array {
                return [
                    'name' => trim((string) ($surveyor['name'] ?? '')),
                    'company' => $this->nullable_trim($surveyor['company'] ?? null),
                    'position' => $this->nullable_trim($surveyor['position'] ?? null),
                    'email' => $this->nullable_trim($surveyor['email'] ?? null),
                    'phone' => $this->nullable_trim($surveyor['phone'] ?? null),
                ];
            })
            ->filter(fn (array $surveyor): bool => $surveyor['name'] !== '')
            ->values()
            ->all();
    }

    // This function trims a string and converts empty strings to null.
    private function nullable_trim(mixed $value): ?string
    {
        // If the value is null, return null. Otherwise, trim the string and return null if it's empty.
        if ($value === null) {
            return null;
        }

        // Trim the value and convert empty strings to null
        $value = trim((string) $value);

        // Return null if the trimmed value is an empty string, otherwise return the trimmed value
        return $value === '' ? null : $value;
    }

    private function validate_project_team_division(array $data): ?array
    {
        $leaderId = isset($data['project_leader_employee_id']) ? (int) $data['project_leader_employee_id'] : null;
        $ppcId = isset($data['project_ppc_employee_id']) ? (int) $data['project_ppc_employee_id'] : null;
        $managerId = isset($data['division_manager_employee_id']) ? (int) $data['division_manager_employee_id'] : null;
        $requestedDivisionId = isset($data['division_ids'][0]) ? (int) $data['division_ids'][0] : null;

        $selectedEmployeeIds = array_values(array_filter([$leaderId, $ppcId, $managerId]));

        if (count($selectedEmployeeIds) === 0) {
            return [
                'division_ids' => 'Pilih minimal salah satu dari PIMPRO, PPC, atau Manajer Divisi.',
            ];
        }

        if ($requestedDivisionId === null || $requestedDivisionId === 0) {
            return [
                'division_ids' => 'Divisi pelaksana wajib terisi otomatis dari PIMPRO/PPC/Manajer.',
            ];
        }

        if (count(($data['division_ids'] ?? [])) > 1) {
            return [
                'division_ids' => 'Divisi pelaksana hanya boleh satu divisi.',
            ];
        }

        $employees = Employee::query()
            ->with('position.organizational_unit')
            ->whereIn('id', $selectedEmployeeIds)
            ->get();

        if ($employees->count() !== count($selectedEmployeeIds)) {
            return null;
        }

        $divisionIds = $employees
            ->map(fn (Employee $employee): int => (int) ($employee->position?->organizational_unit?->id ?? 0))
            ->filter(fn (int $divisionId): bool => $divisionId > 0)
            ->unique()
            ->values();

        if ($divisionIds->count() !== 1) {
            return [
                'project_ppc_employee_id' => 'PIMPRO, PPC, dan Manajer Divisi harus berasal dari divisi yang sama.',
                'division_manager_employee_id' => 'PIMPRO, PPC, dan Manajer Divisi harus berasal dari divisi yang sama.',
            ];
        }

        $teamDivisionId = (int) $divisionIds->first();

        if ($requestedDivisionId !== $teamDivisionId) {
            return [
                'division_ids' => 'Divisi pelaksana harus sama dengan divisi PIMPRO/PPC/Manajer.',
            ];
        }

        return null;
    }

    // This function generates a unique project code based on project type, ship type, and selected project date.
    private function generate_unique_project_code(?string $projectType, ?int $shipId, ?string $projectDate = null): string
    {
        /*
        * This script is for automatic generation of project code based on specific pattern. The pattern is defined as follows:
        * - First section is indicator of the project: D (for Docking Repair), F (for Floating Repair), E (for Emergency Docking), and L (for Other Type of Order)
        * - Followed by a dot (.)
        * - Second section is three digit numbers (0-9) for the order number. Starting from 001, 002, 003, and so on. This must check the previous project record.
        * - Followed by a dot (.)
        * - Third section is two digits (0-9) for the two digit year of the order. For example, 23 for 2023, 24 for 2024, and so on.
        * - Followed by a dot (.)
        * - Fourth section is an uppercase letter (A-Z) for type of ship: F (for Ferry), C (for Cargo), T (for Tugboat), and O (for Other Type of Ship)
        * - Followed by a dot (.)
        * - Final section is a lowercase letter (a-z) for the month of project start. For example, a for January, b for February, c for March, and so on.
        * Example of valid project code: D.001.23.F.a
        */

        // First step: Generate the first section of the project code based on the project type
        $firstSection = $this->project_type_code($projectType);

        $baseDate = $projectDate ? Carbon::parse($projectDate) : now();
        $thirdSection = $baseDate->format('y');

        $ship = $shipId ? Ship::with('type')->find($shipId) : null;
        $fourthSection = $this->ship_type_code($ship?->type?->name);

        $currentMonth = (int) $baseDate->format('n'); // 1-12
        $monthLetter = chr(96 + $currentMonth); // a for January, b for February, ..., l for December

        // Second step: Generate order number by prefix and year so numbering restarts each year.
        $lastOrderNumber = Project::query()
            ->where('project_code', 'like', $firstSection.'.%.'.$thirdSection.'.%.%')
            ->pluck('project_code')
            ->map(function (string $code): int {
                $parts = explode('.', $code);

                return isset($parts[1]) && ctype_digit($parts[1]) ? (int) $parts[1] : 0;
            })
            ->max() ?? 0;

        $nextOrderNumber = str_pad((string) ($lastOrderNumber + 1), 3, '0', STR_PAD_LEFT);

        // Combine all sections to form the final project code
        $projectCode = implode('.', [$firstSection, $nextOrderNumber, $thirdSection, $fourthSection, $monthLetter]);

        return $projectCode;
    }

    private function project_type_code(?string $projectType): string
    {
        $normalized = strtolower(trim((string) $projectType));

        if (str_contains($normalized, 'emergency')) {
            return 'E';
        }

        if (str_contains($normalized, 'floating')) {
            return 'F';
        }

        if (str_contains($normalized, 'docking')) {
            return 'D';
        }

        return 'L';
    }

    private function ship_type_code(?string $shipTypeName): string
    {
        $normalized = strtolower(trim((string) $shipTypeName));

        if (str_contains($normalized, 'ferry')) {
            return 'F';
        }

        if (str_contains($normalized, 'barge') || str_contains($normalized, 'tongkang')) {
            return 'B';
        }

        if (str_contains($normalized, 'cargo') || str_contains($normalized, 'container')) {
            return 'C';
        }

        if (str_contains($normalized, 'tug') || str_contains($normalized, 'ahts')) {
            return 'T';
        }

        return 'O';
    }
}
