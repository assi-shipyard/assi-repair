<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CustomerDocument;
use App\Models\CustomerFeedback;
use App\Models\CustomerProjectAccess;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class customer_portal_service
{
    public const PRICE_UNLOCK_SECONDS = 300;

    public function login(Request $request, array $credentials): bool
    {
        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'account_type' => User::TYPE_CUSTOMER,
            'is_active' => true,
        ])) {
            return false;
        }

        $request->session()->regenerate();
        $request->session()->forget(['employee_id', 'employee_name', 'employee_position', 'customer_price_unlocks']);

        return true;
    }

    public function index_data(Request $request): array
    {
        $status_labels = [
            'Not Started' => 'Proyek Mendatang',
            'In Progress' => 'Proyek Berjalan',
            'Completed' => 'Riwayat Proyek',
        ];
        $status = $request->query('status');
        if ($status !== null && (! is_string($status) || ! array_key_exists($status, $status_labels))) {
            throw ValidationException::withMessages(['status' => 'Status proyek tidak valid.']);
        }

        $projects = Project::query()->with('ship')
            ->whereIn('id', CustomerProjectAccess::query()->select('project_id')
                ->where('user_id', $request->user()->id)->where('is_active', true))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')->paginate(15)->withQueryString();

        $pending_documents = CustomerDocument::query()->with('project')
            ->where('approver_id', $request->user()->id)->where('status', 'pending')
            ->whereIn('project_id', CustomerProjectAccess::query()->select('project_id')
                ->where('user_id', $request->user()->id)->where('is_active', true))
            ->latest('id')->paginate(10, ['*'], 'approval_page');

        return compact('projects', 'status_labels', 'status', 'pending_documents');
    }

    public function project_access(User $user, Project $project, bool $lock = false): CustomerProjectAccess
    {
        abort_unless($user->is_customer() && $user->is_active, 403, 'Akses tidak diizinkan.');
        $query = CustomerProjectAccess::query()
            ->where('user_id', $user->id)->where('project_id', $project->id)->where('is_active', true);

        return ($lock ? $query->lockForUpdate() : $query)->firstOrFail();
    }

    public function project_data(Request $request, string $project_id): array
    {
        $project = Project::query()->with('ship')->where('unique_id', $project_id)->firstOrFail();
        $access = $this->project_access($request->user(), $project);
        $documents = CustomerDocument::query()->where('project_id', $project->id)->latest('id')->paginate(15, ['*'], 'documents_page');
        $feedback = CustomerFeedback::query()->where('project_id', $project->id)
            ->where('user_id', $request->user()->id)->latest('id')->paginate(10, ['*'], 'feedback_page');

        return compact('project', 'access', 'documents', 'feedback');
    }

    public function document_data(Request $request, CustomerDocument $document): array
    {
        $document->load('project.ship');
        $access = $this->project_access($request->user(), $document->project);
        $prices_unlocked = $this->prices_unlocked($request, $document, $access);
        $data = [
            'unique_id' => $document->unique_id,
            'title' => CustomerDocument::TYPE_LABELS[$document->document_type],
            'document_type' => $document->document_type,
            'revision_no' => $document->revision_no,
            'status' => $document->status,
            'snapshot' => $document->public_snapshot,
            'decision_comment' => $document->decision_comment,
            'decided_at' => $document->decided_at === null ? null : $document->decided_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H:i').' WIB',
            'prices_unlocked' => $prices_unlocked,
            'can_unlock' => $document->is_financial() && $access->view_prices,
            'can_decide' => $document->status === 'pending'
                && $document->approver_id === $request->user()->id
                && $access->can_approve($document->document_type)
                && (! $document->is_financial() || $prices_unlocked),
        ];

        // Only this allowlisted projection reaches customer HTML/JSON; internal costs and notes never do.
        if ($prices_unlocked) {
            $data['prices'] = $document->financial_snapshot;
            $data['unlock_remaining_seconds'] = max(0, $request->session()->get('customer_price_unlocks.'.$document->unique_id.'.expires_at') - now()->timestamp);
        }

        return $data;
    }

    public function prices_unlocked(Request $request, CustomerDocument $document, CustomerProjectAccess $access, ?User $user = null): bool
    {
        if (! $document->is_financial() || ! $access->is_active || ! $access->view_prices) {
            return false;
        }

        $user ??= $request->user();
        $unlock = $request->session()->get('customer_price_unlocks.'.$document->unique_id);

        return is_array($unlock)
            && ($unlock['user_id'] ?? null) === $user->id
            && ($unlock['expires_at'] ?? 0) > now()->timestamp
            && ($unlock['access_version'] ?? null) === $access->access_version
            && hash_equals(hash('sha256', $user->password), $unlock['password_fingerprint'] ?? '');
    }

    public function unlock_prices(Request $request, CustomerDocument $document): void
    {
        $document->load('project');
        $access = $this->project_access($request->user(), $document->project);
        abort_unless($document->is_financial() && $access->view_prices, 403, 'Anda tidak memiliki akses harga.');
        $this->audit($request->user(), 'customer.prices_unlocked', $document->unique_id);
        $request->session()->put('customer_price_unlocks.'.$document->unique_id, [
            'user_id' => $request->user()->id,
            'expires_at' => now()->timestamp + self::PRICE_UNLOCK_SECONDS,
            'password_fingerprint' => hash('sha256', $request->user()->password),
            'access_version' => $access->access_version,
        ]);
    }

    public function lock_prices(Request $request, CustomerDocument $document): void
    {
        $document->load('project');
        $this->project_access($request->user(), $document->project);
        $this->audit($request->user(), 'customer.prices_locked', $document->unique_id);
        $request->session()->forget('customer_price_unlocks.'.$document->unique_id);
    }

    public function decide(Request $request, CustomerDocument $document, array $data): void
    {
        DB::transaction(function () use ($request, $document, $data): void {
            $project = Project::query()->lockForUpdate()->findOrFail($document->project_id);
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $access = $this->project_access($user, $project, true);
            $document = CustomerDocument::query()->lockForUpdate()->findOrFail($document->id);
            abort_unless($document->approver_id === $user->id && $access->can_approve($document->document_type), 403, 'Anda bukan pemberi persetujuan untuk dokumen ini.');

            if ($document->status !== 'pending') {
                throw ValidationException::withMessages(['decision' => 'Dokumen sudah diputuskan atau digantikan revisi baru.']);
            }

            if ($document->is_financial() && ! $this->prices_unlocked($request, $document, $access, $user)) {
                throw ValidationException::withMessages(['decision' => 'Buka harga dengan kata sandi sebelum memberikan keputusan RAB.']);
            }

            $document->update([
                'status' => $data['decision'],
                'decision_comment' => $data['comment'] ?? null,
                'decided_at' => now(),
                'decided_by' => $user->id,
            ]);
            $this->audit($user, 'customer.document_decided', $document->unique_id, ['decision' => $data['decision']]);
        });
    }

    public function submit_feedback(Request $request, string $project_id, array $data): void
    {
        DB::transaction(function () use ($request, $project_id, $data): void {
            $project = Project::query()->where('unique_id', $project_id)->lockForUpdate()->firstOrFail();
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $this->project_access($user, $project, true);
            if ($data['kind'] === 'satisfaction' && $project->status !== 'Completed') {
                throw ValidationException::withMessages(['kind' => 'Penilaian kepuasan tersedia setelah proyek selesai.']);
            }
            if ($data['kind'] === 'satisfaction'
                && CustomerFeedback::query()->where('project_id', $project->id)->where('user_id', $user->id)->where('kind', 'satisfaction')->exists()) {
                throw ValidationException::withMessages(['kind' => 'Anda sudah mengirim penilaian kepuasan untuk proyek ini.']);
            }
            $feedback = CustomerFeedback::query()->create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'kind' => $data['kind'],
                'rating' => $data['rating'] ?? null,
                'message' => $data['message'],
            ]);
            $this->audit($user, 'customer.feedback_submitted', $feedback->unique_id, ['kind' => $data['kind']]);
        });
    }

    public function update_password(Request $request, string $password): void
    {
        DB::transaction(function () use ($request, $password): void {
            $request->user()->update(['password' => $password, 'must_change_password' => false]);
            $request->user()->forceFill(['remember_token' => null])->save();
            DB::table('sessions')->where('user_id', $request->user()->id)->delete();
            $this->audit($request->user(), 'customer.password_changed', $request->user()->unique_id);
        });
        $request->session()->forget('customer_price_unlocks');
        $request->session()->regenerate();
    }

    public function audit(User $actor, string $event, string $resource_id, array $details = []): void
    {
        AuditLog::query()->create([
            'user_id' => $actor->id,
            'employee_id' => $actor->employee_id,
            'event_name' => $event,
            'event_payload' => ['resource_id' => $resource_id, ...$details],
        ]);
    }
}
