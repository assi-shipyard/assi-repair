<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\customer_document;
use App\Models\customer_feedback;
use App\Models\customer_project_access;
use App\Models\Project;
use App\Models\ProjectJobDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class customer_management_service
{
    public function __construct(
        private readonly customer_portal_service $portal,
        private readonly ProjectDocumentComparisonService $comparison,
    ) {}

    public function account_data(): array
    {
        return [
            'users' => User::query()->with(['employee', 'company'])->orderBy('account_type')->latest('id')->paginate(20),
            'companies' => Company::query()->orderBy('id')->lazy(100),
        ];
    }

    public function create_account(User $actor, array $data): void
    {
        DB::transaction(function () use ($actor, $data): void {
            $user = User::query()->create([
                'account_type' => $data['account_type'],
                'name' => $data['name'],
                'username' => $data['username'] ?? null,
                'email' => strtolower($data['email']),
                'password' => $data['password'],
                'must_change_password' => $data['account_type'] === User::TYPE_CUSTOMER,
                'company_id' => isset($data['company_id'])
                    ? Company::query()->where('unique_id', $data['company_id'])->firstOrFail()->id : null,
            ]);
            if ($user->account_type === User::TYPE_ADMIN) {
                $user->assignRole(Role::findOrCreate('admin', 'web'));
            }
            $this->portal->audit($actor, 'account.created', $user->unique_id, ['account_type' => $user->account_type]);
        });
    }

    public function set_account_status(User $actor, User $user, bool $is_active): void
    {
        if ($actor->id === $user->id && ! $is_active) {
            throw ValidationException::withMessages(['is_active' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }
        DB::transaction(function () use ($actor, $user, $is_active): void {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            $user->update(['is_active' => $is_active]);
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->forceFill(['remember_token' => null])->save();
            $this->portal->audit($actor, 'account.status_changed', $user->unique_id, ['is_active' => $is_active]);
        });
    }

    public function management_data(): array
    {
        return [
            'customers' => User::query()->where('account_type', User::TYPE_CUSTOMER)->where('is_active', true)->orderBy('id')->lazy(100),
            'projects' => Project::query()->with('ship')->orderByDesc('id')->lazy(100),
            'access_entries' => customer_project_access::query()->with(['project', 'user'])->latest('id')->paginate(15, ['*'], 'access_page'),
            'documents' => ProjectJobDocument::query()->with('project')
                ->whereIn('document_type', array_keys(customer_document::TYPE_LABELS))
                ->whereIn('status', ['approved', 'locked'])
                ->whereNotIn('id', customer_document::query()->select('project_job_document_id'))
                ->orderByDesc('id')->lazy(100),
            'publications' => customer_document::query()->with(['project', 'approver'])->latest('id')->paginate(15, ['*'], 'publication_page'),
            'feedback' => customer_feedback::query()->with(['project', 'user'])->latest('id')->paginate(15, ['*'], 'feedback_page'),
        ];
    }

    public function edit_data(User $user): array
    {
        abort_unless(in_array($user->account_type, [User::TYPE_ADMIN, User::TYPE_CUSTOMER], true), 403, 'Gunakan formulir karyawan untuk akun internal.');
        $user->load('company');

        return ['user' => $user, 'companies' => Company::query()->orderBy('id')->lazy(100)];
    }

    public function update_account(User $actor, User $user, array $data): void
    {
        DB::transaction(function () use ($actor, $user, $data): void {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            abort_unless(in_array($user->account_type, [User::TYPE_ADMIN, User::TYPE_CUSTOMER], true), 403, 'Gunakan formulir karyawan untuk akun internal.');
            $before = $user->only(['name', 'username', 'email', 'company_id']);
            $user->fill([
                'name' => $data['name'], 'email' => $data['email'],
                'username' => $data['username'] ?? null,
                'company_id' => isset($data['company_id'])
                    ? Company::query()->where('unique_id', $data['company_id'])->firstOrFail()->id : null,
            ]);
            $password_reset = ! empty($data['password']);
            if ($password_reset) {
                $user->password = $data['password'];
                $user->must_change_password = $user->is_customer();
            }
            $user->remember_token = null;
            $user->save();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $this->portal->audit($actor, 'account.updated', $user->unique_id, [
                'before' => $before, 'after' => $user->only(['name', 'username', 'email', 'company_id']),
                'password_reset' => $password_reset,
            ]);
        });
    }

    public function save_access(User $actor, array $data): void
    {
        if (($data['approve_initial_boq'] || $data['approve_final_boq']) && ! $data['view_prices']) {
            throw ValidationException::withMessages(['view_prices' => 'Persetujuan RAB memerlukan izin melihat harga. Surveyor tanpa izin harga dapat menyetujui Catatan Kepuasan.']);
        }
        DB::transaction(function () use ($actor, $data): void {
            $project = Project::query()->where('unique_id', $data['project_id'])->lockForUpdate()->firstOrFail();
            $user = User::query()->where('unique_id', $data['user_id'])->where('account_type', User::TYPE_CUSTOMER)->lockForUpdate()->firstOrFail();
            $access = customer_project_access::query()->updateOrCreate(
                ['user_id' => $user->id, 'project_id' => $project->id],
                [...array_diff_key($data, array_flip(['user_id', 'project_id'])), 'access_version' => (string) Str::uuid()],
            );
            // Revoking and restoring access must not restore an earlier session's financial unlock.
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->forceFill(['remember_token' => null])->save();
            $this->portal->audit($actor, 'customer.access_changed', $access->unique_id, [
                'customer_id' => $user->unique_id, 'project_id' => $project->unique_id,
                'permissions' => array_diff_key($data, array_flip(['user_id', 'project_id'])),
            ]);
        });
    }

    public function publish(User $actor, array $data): void
    {
        DB::transaction(function () use ($actor, $data): void {
            $source = ProjectJobDocument::query()->where('unique_id', $data['document_id'])->firstOrFail();
            Project::query()->lockForUpdate()->findOrFail($source->project_id);
            $approver = User::query()->where('unique_id', $data['approver_id'])->lockForUpdate()->firstOrFail();
            $access = $this->portal->project_access($approver, $source->project()->firstOrFail(), true);
            $source = ProjectJobDocument::query()->whereKey($source->id)->lockForUpdate()->firstOrFail();
            $source->load('jobs.materials');
            if (! array_key_exists($source->document_type, customer_document::TYPE_LABELS)
                || ! in_array($source->status, ['approved', 'locked'], true)) {
                throw ValidationException::withMessages(['document_id' => 'Hanya RAB atau Catatan Kepuasan yang telah disetujui internal dapat diterbitkan.']);
            }
            if (! $access->can_approve($source->document_type)) {
                throw ValidationException::withMessages(['approver_id' => 'Pelanggan tidak memiliki wewenang persetujuan untuk jenis dokumen ini.']);
            }
            if (customer_document::query()->where('project_job_document_id', $source->id)->exists()
                || ProjectJobDocument::query()->where('project_id', $source->project_id)->where('document_type', $source->document_type)
                    ->where('revision_no', '>', $source->revision_no)->exists()) {
                throw ValidationException::withMessages(['document_id' => 'Dokumen sudah diterbitkan atau bukan revisi terbaru.']);
            }

            $public_snapshot = ['document_number' => $source->document_number, 'jobs' => []];
            $financial_snapshot = ['jobs' => []];
            foreach ($source->jobs as $job) {
                $public_snapshot['jobs'][] = [
                    'unique_id' => $job->unique_id,
                    'name' => $job->job_name,
                    'volume' => $job->job_volume_estimated,
                    'actual_volume' => $job->actual_volume,
                    'progress' => $job->progress_percent,
                    'start_date' => $job->est_start_date?->locale('id')->translatedFormat('j F Y'),
                    'end_date' => $job->est_finish_date?->locale('id')->translatedFormat('j F Y'),
                    'materials' => $job->materials->map(fn ($material): array => [
                        'unique_id' => $material->unique_id, 'name' => $material->material_name,
                        'volume' => $material->volume, 'dimension' => $material->dimension,
                    ])->all(),
                ];
                if ($source->document_type !== 'satisfaction_notes') {
                    $financial_snapshot['jobs'][$job->unique_id] = [
                        'price' => $job->est_price,
                        'currency' => $job->est_currency ?: 'IDR',
                        'materials' => $job->materials->mapWithKeys(fn ($material): array => [
                            $material->unique_id => ['price' => $material->price, 'currency' => $material->currency ?: 'IDR'],
                        ])->all(),
                    ];
                }
            }
            if ($source->document_type !== 'satisfaction_notes') {
                $financial_snapshot['totals'] = $this->comparison->document_totals($source);
            }

            customer_document::query()->where('project_id', $source->project_id)->where('document_type', $source->document_type)
                ->where('status', 'pending')->lazyById(100)->each(function (customer_document $old) use ($actor): void {
                    $old->update(['status' => 'superseded']);
                    $this->portal->audit($actor, 'customer.publication_superseded', $old->unique_id);
                });
            $publication = customer_document::query()->create([
                'project_id' => $source->project_id,
                'project_job_document_id' => $source->id,
                'document_type' => $source->document_type,
                'revision_no' => $source->revision_no,
                'public_snapshot' => $public_snapshot,
                'financial_snapshot' => $financial_snapshot,
                'approver_id' => $approver->id,
                'published_by' => $actor->id,
            ]);
            $this->portal->audit($actor, 'customer.document_published', $publication->unique_id, ['revision_no' => $source->revision_no]);
        });
    }

    public function respond(User $actor, customer_feedback $feedback, array $data): void
    {
        DB::transaction(function () use ($actor, $feedback, $data): void {
            $feedback = customer_feedback::query()->lockForUpdate()->findOrFail($feedback->id);
            $old_status = $feedback->status;
            $old_response = $feedback->response;
            $feedback->update([
                'status' => $data['status'], 'response' => $data['response'],
                'responded_by' => $actor->id, 'responded_at' => now(),
            ]);
            $this->portal->audit($actor, 'customer.feedback_responded', $feedback->unique_id, [
                'old_status' => $old_status, 'new_status' => $data['status'],
                'old_response' => $old_response, 'new_response' => $data['response'],
            ]);
        });
    }

    public function reassign(User $actor, customer_document $document, string $approver_id): void
    {
        DB::transaction(function () use ($actor, $document, $approver_id): void {
            $project = Project::query()->lockForUpdate()->findOrFail($document->project_id);
            $approver = User::query()->where('unique_id', $approver_id)->lockForUpdate()->firstOrFail();
            $access = $this->portal->project_access($approver, $project, true);
            $document = customer_document::query()->lockForUpdate()->findOrFail($document->id);
            if ($document->status !== 'pending' || ! $access->can_approve($document->document_type)) {
                throw ValidationException::withMessages(['approver_id' => 'Hanya permintaan yang belum diputuskan dapat dialihkan kepada pelanggan berwenang.']);
            }
            $previous_approver = User::query()->findOrFail($document->approver_id);
            $document->update(['approver_id' => $approver->id]);
            $this->portal->audit($actor, 'customer.approver_reassigned', $document->unique_id, [
                'previous_approver_id' => $previous_approver->unique_id, 'approver_id' => $approver->unique_id,
            ]);
        });
    }
}
