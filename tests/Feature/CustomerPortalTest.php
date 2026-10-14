<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CustomerDocument;
use App\Models\CustomerFeedback;
use App\Models\CustomerProjectAccess;
use App\Models\Employee;
use App\Models\OrganizationalUnit;
use App\Models\Position;
use App\Models\Project;
use App\Models\ProjectDocumentJob;
use App\Models\ProjectDocumentJobMaterial;
use App\Models\ProjectJobDocument;
use App\Models\Ship;
use App\Models\User;
use App\Services\customer_management_service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// PHPUnit discovers test classes using its conventional *Test.php file name.
class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::create(['account_type' => 'admin', 'username' => 'administrator', 'password' => 'SecretPassword123']);
        $user->assignRole(Role::findOrCreate('admin', 'web'));

        return $user;
    }

    private function customer(): User
    {
        return User::create([
            'account_type' => 'customer', 'name' => 'Surveyor Uji',
            'email' => Str::uuid().'@example.test', 'password' => 'SecretPassword123',
        ]);
    }

    private function project(string $status = 'In Progress'): Project
    {
        $company = Company::create(['unique_id' => (string) Str::uuid(), 'name' => 'Pemilik Kapal', 'address' => 'Alamat Uji']);
        $ship = Ship::create([
            'name' => 'Kapal Uji', 'company_id' => $company->id, 'length_overall' => 80,
            'breadth' => 14, 'height' => 10, 'empty_draft' => 3, 'gross_tonnage' => 2000,
        ]);

        return Project::create([
            'project_code' => 'UJI-'.Str::random(8), 'ship_id' => $ship->id,
            'project_type' => 'Floating Repair', 'status' => $status, 'progress' => 25,
        ]);
    }

    private function access(User $user, Project $project, bool $financial = false): CustomerProjectAccess
    {
        return CustomerProjectAccess::create([
            'user_id' => $user->id, 'project_id' => $project->id, 'access_version' => (string) Str::uuid(),
            'view_prices' => $financial, 'approve_initial_boq' => $financial,
            'approve_final_boq' => $financial, 'approve_satisfaction_notes' => true,
        ]);
    }

    private function source(Project $project, string $type = 'initial_boq', int $revision = 1): ProjectJobDocument
    {
        $source = ProjectJobDocument::create([
            'project_id' => $project->id, 'document_type' => $type, 'revision_no' => $revision,
            'status' => 'approved', 'notes' => 'CATATAN INTERNAL RAHASIA',
        ]);
        $job = ProjectDocumentJob::create([
            'project_job_document_id' => $source->id, 'job_name' => 'Pekerjaan Pelat',
            'est_price' => '987654321.25', 'est_currency' => 'IDR',
            'actual_cost' => '123456789.99', 'actual_currency' => 'IDR',
            'job_weight_percent' => '75', 'job_volume_estimated' => '12',
        ]);
        ProjectDocumentJobMaterial::create([
            'project_document_job_id' => $job->id, 'material_name' => 'Material Uji',
            'price' => '876543210.50', 'currency' => 'IDR', 'notes' => 'MATERIAL RAHASIA',
        ]);

        return $source;
    }

    private function publish(User $customer, Project $project, string $type = 'initial_boq', int $revision = 1): CustomerDocument
    {
        $source = $this->source($project, $type, $revision);
        $admin = $this->admin();
        app(customer_management_service::class)->publish($admin, ['document_id' => $source->unique_id, 'approver_id' => $customer->unique_id]);

        return CustomerDocument::where('project_job_document_id', $source->id)->firstOrFail();
    }

    public function test_customer_login_is_email_based_and_never_uses_employee_context(): void
    {
        $customer = $this->customer();
        $this->withSession(['employee_name' => 'Internal', 'customer_price_unlocks' => ['old' => true]])
            ->post(route('customer.login.process'), ['email' => strtoupper($customer->email), 'password' => 'SecretPassword123'])
            ->assertRedirect(route('customer.index'))->assertSessionMissing('employee_name')->assertSessionMissing('customer_price_unlocks');
        $this->assertAuthenticatedAs($customer);
        $this->get(route('customer.index'))->assertOk()->assertSee('Surveyor Uji')->assertDontSee('Kamus Harga')->assertDontSee('Database');
        $this->get(route('login'))->assertRedirect(route('customer.index'));
        $this->get(route('logout'))->assertRedirect(route('customer.login'));
    }

    public function test_inactive_customers_and_internal_accounts_cannot_use_customer_login(): void
    {
        $customer = $this->customer();
        $customer->update(['is_active' => false]);
        $this->post(route('customer.login.process'), ['email' => $customer->email, 'password' => 'SecretPassword123'])->assertSessionHas('error');
        $internal = User::create(['employee_id' => '111111111', 'email' => 'internal@example.test', 'password' => 'SecretPassword123']);
        $this->post(route('customer.login.process'), ['email' => $internal->email, 'password' => 'SecretPassword123'])->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_account_type_isolation_blocks_internal_routes_and_portal_impersonation(): void
    {
        $customer = $this->customer();
        $project = $this->project();
        $this->actingAs($customer);
        foreach (['dashboard', 'company.index', 'ship.index', 'employee.index', 'price-book.index', 'settings.index', 'accounts.index', 'accounts.projects'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        $this->get(route('project.job-document.workflow.index', $project->unique_id))->assertForbidden();
        $this->actingAs($this->admin())->get(route('customer.index'))->assertForbidden();
    }

    public function test_assigned_projects_are_scoped_across_all_lifecycle_filters(): void
    {
        $customer = $this->customer();
        $projects = [$this->project('Not Started'), $this->project('In Progress'), $this->project('Completed')];
        foreach ($projects as $project) {
            $this->access($customer, $project);
        }
        $unassigned = $this->project();
        $this->actingAs($customer)->get(route('customer.index'))->assertOk()->assertDontSee($unassigned->project_code);
        foreach ($projects as $project) {
            $this->get(route('customer.index', ['status' => $project->status]))->assertOk()->assertSee($project->project_code);
            $this->get(route('customer.project', $project->unique_id))->assertOk();
        }
        $this->get(route('customer.project', $unassigned->unique_id))->assertNotFound();
        $this->get('/customer/project/'.$unassigned->id)->assertNotFound();
    }

    public function test_company_membership_does_not_grant_project_access(): void
    {
        $project = $this->project();
        $customer = $this->customer();
        $customer->update(['company_id' => $project->ship->company_id]);
        $this->actingAs($customer)->get(route('customer.project', $project->unique_id))->assertNotFound();
    }

    public function test_locked_html_and_json_exclude_all_prices_and_internal_information(): void
    {
        $customer = $this->customer();
        $project = $this->project();
        $this->access($customer, $project, true);
        $publication = $this->publish($customer, $project);
        $this->actingAs($customer)->get(route('customer.document', $publication))->assertOk()
            ->assertSee('Harga RAB Terkunci')->assertDontSee('987654321')->assertDontSee('876543210')
            ->assertDontSee('123456789')->assertDontSee('CATATAN INTERNAL RAHASIA')->assertDontSee('MATERIAL RAHASIA');
        $response = $this->getJson(route('customer.document.data', $publication))->assertOk()
            ->assertJsonPath('data.prices_unlocked', false)->assertJsonMissingPath('data.prices');
        foreach (['est_price', 'actual_cost', 'price', 'currency', 'job_weight_percent', 'notes', 'financial_snapshot'] as $key) {
            $this->assertStringNotContainsString('"'.$key.'":', $response->getContent());
        }
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringNotContainsString('987654321', DB::table('customer_documents')->value('financial_snapshot'));
    }

    public function test_prices_require_permission_and_password_and_expire_after_exactly_five_minutes(): void
    {
        $customer = $this->customer();
        $project = $this->project();
        $this->access($customer, $project, true);
        $publication = $this->publish($customer, $project);
        $this->actingAs($customer)->post(route('customer.unlock', $publication), ['current_password' => 'wrong-password'])->assertSessionHasErrors('current_password');
        $this->getJson(route('customer.document.data', $publication))->assertJsonMissingPath('data.prices');
        $this->post(route('customer.unlock', $publication), ['current_password' => 'SecretPassword123'])->assertRedirect(route('customer.document', $publication));
        $this->getJson(route('customer.document.data', $publication))->assertJsonPath('data.prices_unlocked', true)
            ->assertJsonPath('data.prices.jobs.'.array_key_first($publication->financial_snapshot['jobs']).'.price', '987654321.25')
            ->assertJsonPath('data.prices.totals.IDR', 1864197531.75)
            ->assertDontSee('123456789')->assertDontSee('actual_cost');
        $this->travel(299)->seconds();
        $this->getJson(route('customer.document.data', $publication))->assertJsonPath('data.prices_unlocked', true);
        $this->travel(1)->seconds();
        $this->getJson(route('customer.document.data', $publication))->assertJsonMissingPath('data.prices')->assertJsonPath('data.prices_unlocked', false);
        $this->assertDatabaseHas('audit_logs', ['event_name' => 'customer.prices_unlocked', 'user_id' => $customer->id]);
    }

    public function test_external_surveyor_cannot_unlock_prices_even_with_their_correct_password(): void
    {
        $owner = $this->customer();
        $surveyor = $this->customer();
        $project = $this->project();
        $this->access($owner, $project, true);
        $this->access($surveyor, $project);
        $publication = $this->publish($owner, $project);
        $this->actingAs($surveyor)->post(route('customer.unlock', $publication), ['current_password' => 'SecretPassword123'])->assertForbidden();
        $this->getJson(route('customer.document.data', $publication))->assertJsonMissingPath('data.prices');
        $this->post(route('customer.decision', $publication), ['decision' => 'approved'])->assertForbidden();
    }

    public function test_other_project_documents_are_not_accessible(): void
    {
        $owner = $this->customer();
        $outsider = $this->customer();
        $project = $this->project();
        $this->access($owner, $project, true);
        $publication = $this->publish($owner, $project);
        $this->actingAs($outsider)->get(route('customer.document', $publication))->assertNotFound();
        $this->getJson(route('customer.document.data', $publication))->assertNotFound();
        $this->post(route('customer.unlock', $publication), ['current_password' => 'SecretPassword123'])->assertNotFound();
    }

    public function test_access_version_and_password_changes_invalidate_unlocked_prices(): void
    {
        $customer = $this->customer();
        $project = $this->project();
        $access = $this->access($customer, $project, true);
        $publication = $this->publish($customer, $project);
        $this->actingAs($customer)->post(route('customer.unlock', $publication), ['current_password' => 'SecretPassword123'])->assertSessionHasNoErrors();
        $access->update(['access_version' => (string) Str::uuid()]);
        $this->getJson(route('customer.document.data', $publication))->assertJsonMissingPath('data.prices');
        $this->post(route('customer.unlock', $publication), ['current_password' => 'SecretPassword123'])->assertSessionHasNoErrors();
        $this->get(route('customer.document', $publication))->assertOk()->assertSee('987.654.321,25')->assertSee('876.543.210,50')->assertSee('Total RAB')->assertDontSee('123.456.789,99');
        $customer->update(['password' => 'ChangedPassword123']);
        $this->getJson(route('customer.document.data', $publication))->assertJsonMissingPath('data.prices');
        $access->update(['is_active' => false]);
        $this->get(route('customer.document', $publication))->assertNotFound();
    }

    public function test_rab_decision_requires_unlock_and_cannot_be_repeated_or_change_internal_approval(): void
    {
        $customer = $this->customer();
        $project = $this->project();
        $this->access($customer, $project, true);
        $publication = $this->publish($customer, $project);
        $this->actingAs($customer)->post(route('customer.decision', $publication), ['decision' => 'approved'])->assertSessionHasErrors('decision');
        $this->post(route('customer.unlock', $publication), ['current_password' => 'SecretPassword123'])->assertSessionHasNoErrors();
        $this->post(route('customer.decision', $publication), ['decision' => 'approved'])->assertSessionHasNoErrors();
        $this->assertSame('approved', $publication->fresh()->status);
        $this->assertSame($customer->id, $publication->fresh()->decided_by);
        $this->assertDatabaseHas('project_job_documents', ['id' => $publication->project_job_document_id, 'status' => 'approved', 'approved_by_employee_id' => null]);
        $this->post(route('customer.decision', $publication), ['decision' => 'rejected', 'comment' => 'Tidak setuju'])->assertSessionHasErrors('decision');
        $this->assertSame('approved', $publication->fresh()->status);
        $this->assertSame(1, AuditLog::where('event_name', 'customer.document_decided')->count());
    }

    public function test_surveyor_can_approve_satisfaction_notes_without_any_prices(): void
    {
        $customer = $this->customer();
        $project = $this->project();
        $this->access($customer, $project);
        $publication = $this->publish($customer, $project, 'satisfaction_notes');
        $this->actingAs($customer)->getJson(route('customer.document.data', $publication))->assertJsonPath('data.can_decide', true)->assertJsonMissingPath('data.prices');
        $this->post(route('customer.decision', $publication), ['decision' => 'changes_requested'])->assertSessionHasErrors('comment');
        $this->post(route('customer.decision', $publication), ['decision' => 'changes_requested', 'comment' => 'Perbaiki volume.'])->assertSessionHasNoErrors();
        $this->assertSame('changes_requested', $publication->fresh()->status);
        $this->assertSame(['jobs' => []], $publication->financial_snapshot);
    }

    public function test_final_boq_has_the_same_financial_gate_as_initial_boq(): void
    {
        $customer = $this->customer();
        $project = $this->project();
        $this->access($customer, $project, true);
        $publication = $this->publish($customer, $project, 'final_boq');
        $this->actingAs($customer)->get(route('customer.document', $publication))->assertOk()->assertSee('RAB Akhir')->assertDontSee('987654321');
        $this->post(route('customer.decision', $publication), ['decision' => 'approved'])->assertSessionHasErrors('decision');
        $this->post(route('customer.unlock', $publication), ['current_password' => 'SecretPassword123'])->assertSessionHasNoErrors();
        $this->post(route('customer.decision', $publication), ['decision' => 'approved'])->assertSessionHasNoErrors();
    }

    public function test_publication_is_immutable_and_new_revision_supersedes_pending_decisions(): void
    {
        $customer = $this->customer();
        $project = $this->project();
        $this->access($customer, $project, true);
        $publication = $this->publish($customer, $project);
        ProjectDocumentJob::where('project_job_document_id', $publication->project_job_document_id)->firstOrFail()
            ->update(['job_name' => 'MODIFIED INTERNAL', 'est_price' => '1']);
        $this->actingAs($customer)->get(route('customer.document', $publication))->assertSee('Pekerjaan Pelat')->assertDontSee('MODIFIED INTERNAL');
        $source = $this->source($project, 'initial_boq', 2);
        $admin = User::where('username', 'administrator')->firstOrFail();
        $this->actingAs($admin)->post(route('accounts.publish'), ['document_id' => $source->unique_id, 'approver_id' => $customer->unique_id])->assertSessionHasNoErrors();
        $this->assertSame('superseded', $publication->fresh()->status);
        $this->actingAs($customer)->post(route('customer.decision', $publication), ['decision' => 'approved'])->assertSessionHasErrors('decision');
        $this->assertDatabaseCount('customer_documents', 2);
    }

    public function test_only_latest_internally_approved_revision_and_authorized_approver_can_be_published(): void
    {
        $customer = $this->customer();
        $project = $this->project();
        $this->access($customer, $project);
        $source = $this->source($project);
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('accounts.publish'), ['document_id' => $source->unique_id, 'approver_id' => $customer->unique_id])->assertSessionHasErrors('approver_id');
        $source->update(['status' => 'draft']);
        $this->post(route('accounts.publish'), ['document_id' => $source->unique_id, 'approver_id' => $customer->unique_id])->assertSessionHasErrors('document_id');
        $source->update(['status' => 'approved']);
        $this->source($project, 'initial_boq', 2);
        $this->post(route('accounts.publish'), ['document_id' => $source->unique_id, 'approver_id' => $customer->unique_id])->assertSessionHasErrors('approver_id');
        $this->assertDatabaseCount('customer_documents', 0);
    }

    public function test_customer_feedback_is_author_scoped_and_internal_handlers_can_respond(): void
    {
        $customer = $this->customer();
        $other = $this->customer();
        $project = $this->project();
        $this->access($customer, $project);
        $this->access($other, $project);
        $this->actingAs($customer)->post(route('customer.feedback', $project->unique_id), ['kind' => 'complaint', 'message' => 'KOMPLAIN PRIBADI'])->assertSessionHasNoErrors();
        $entry = CustomerFeedback::firstOrFail();
        $this->actingAs($other)->get(route('customer.project', $project->unique_id))->assertDontSee('KOMPLAIN PRIBADI');
        $this->actingAs($customer)->put(route('accounts.respond', $entry), ['status' => 'resolved', 'response' => 'Tidak sah'])->assertForbidden();
        $handler = User::create(['employee_id' => '444444444', 'password' => 'SecretPassword123']);
        $handler->givePermissionTo(Permission::findOrCreate('manage-customer-projects', 'web'));
        $this->actingAs($handler)->get(route('accounts.projects'))->assertOk()->assertSee('KOMPLAIN PRIBADI');
        $this->put(route('accounts.respond', $entry), ['status' => 'resolved', 'response' => 'Sudah ditindaklanjuti.'])->assertSessionHasNoErrors();
        $this->actingAs($customer)->get(route('customer.project', $project->unique_id))->assertSee('Sudah ditindaklanjuti.');
        $this->assertDatabaseHas('audit_logs', ['event_name' => 'customer.feedback_responded', 'user_id' => $handler->id]);
    }

    public function test_satisfaction_rating_requires_completed_project_and_is_submitted_once(): void
    {
        $customer = $this->customer();
        $project = $this->project();
        $this->access($customer, $project);
        $payload = ['kind' => 'satisfaction', 'rating' => 5, 'message' => 'Sangat baik.'];
        $this->actingAs($customer)->post(route('customer.feedback', $project->unique_id), $payload)->assertSessionHasErrors('kind');
        $project->update(['status' => 'Completed']);
        $this->post(route('customer.feedback', $project->unique_id), [...$payload, 'rating' => 6])->assertSessionHasErrors('rating');
        $this->post(route('customer.feedback', $project->unique_id), $payload)->assertSessionHasNoErrors();
        $this->post(route('customer.feedback', $project->unique_id), $payload)->assertSessionHasErrors('kind');
        $this->assertDatabaseCount('customer_feedback', 1);
    }

    public function test_admin_provisions_customers_without_fake_nik_and_initial_password_change_is_required(): void
    {
        $admin = $this->admin();
        $company = Company::create(['unique_id' => (string) Str::uuid(), 'name' => 'Surveyor Eksternal', 'address' => 'Alamat Uji']);
        $this->actingAs($admin)->get(route('accounts.index'))->assertOk()->assertSee('Akun Administrator');
        $this->post(route('accounts.store'), [
            'account_type' => 'customer', 'name' => 'Pelanggan Baru', 'email' => 'NEW@EXAMPLE.TEST',
            'company_id' => $company->unique_id, 'password' => 'SecretPassword123', 'password_confirmation' => 'SecretPassword123',
            'employee_id' => '999999999', 'is_active' => false,
        ])->assertSessionHasNoErrors();
        $customer = User::where('email', 'new@example.test')->firstOrFail();
        $this->assertNull($customer->employee_id);
        $this->assertTrue($customer->is_active);
        $this->assertTrue($customer->must_change_password);
        $this->actingAs($customer)->get(route('customer.index'))->assertRedirect(route('customer.settings'));
        $this->get(route('customer.settings'))->assertOk();
        $this->put(route('customer.password'), [
            'current_password' => 'SecretPassword123', 'password' => 'ChangedPassword123', 'password_confirmation' => 'ChangedPassword123',
        ])->assertSessionHasNoErrors();
        $this->assertFalse($customer->fresh()->must_change_password);
        $this->assertTrue(Hash::check('ChangedPassword123', $customer->fresh()->password));
        $this->get(route('customer.index'))->assertOk();
        $this->assertStringNotContainsString('SecretPassword123', json_encode(AuditLog::all()->toArray()));
    }

    public function test_account_and_access_management_require_admin_and_financial_approval_permission(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $project = $this->project();
        $payload = [
            'user_id' => $customer->unique_id, 'project_id' => $project->unique_id, 'is_active' => true,
            'view_prices' => false, 'approve_initial_boq' => true, 'approve_final_boq' => false, 'approve_satisfaction_notes' => true,
        ];
        $this->actingAs($admin)->post(route('accounts.access'), $payload)->assertSessionHasErrors('view_prices');
        $this->post(route('accounts.access'), [...$payload, 'approve_initial_boq' => false])->assertSessionHasNoErrors();
        $this->get(route('accounts.projects'))->assertOk()->assertSee('Surveyor Uji');
        $this->actingAs($customer)->post(route('accounts.access'), $payload)->assertForbidden();
        $this->actingAs($admin)->put(route('accounts.status', $admin), ['is_active' => false])->assertSessionHasErrors('is_active');
        $this->put(route('accounts.status', $customer), ['is_active' => false])->assertSessionHasNoErrors();
        $this->actingAs($customer->fresh())->get(route('customer.index'))->assertForbidden();
        $this->assertGuest();
    }

    public function test_employee_creation_does_not_reuse_customer_with_null_employee_id(): void
    {
        $customer = $this->customer();
        $admin = $this->admin();
        $unit = OrganizationalUnit::create(['name' => 'Divisi Uji', 'type' => 'division']);
        $position = Position::create(['name' => 'Staf Uji', 'organizational_unit_id' => $unit->id]);
        $this->actingAs($admin)->post(route('employee.store'), [
            'name' => 'Karyawan Baru', 'employee_id' => '199607021', 'position_id' => $position->id, 'email' => null,
        ])->assertSessionHasNoErrors();
        $this->assertSame('customer', $customer->fresh()->account_type);
        $this->assertNull($customer->fresh()->employee_id);
        $employee = Employee::where('employee_id', '199607021')->firstOrFail();
        $this->assertNotSame($customer->id, $employee->user_id);
        $this->assertSame('internal', $employee->user->account_type);
        $this->post(route('employee.store'), [
            'name' => 'Email Bentrok', 'employee_id' => '001234568', 'position_id' => $position->id, 'email' => $customer->email,
        ])->assertSessionHasErrors('email');
    }

    public function test_new_and_legacy_admin_logins_and_employee_nik_login_are_preserved(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee(route('accounts.edit', $admin), false);
        $this->get(route('logout'));
        $this->post(route('admin.login.process'), ['username' => $admin->username, 'password' => 'SecretPassword123'])->assertRedirect(route('dashboard'));
        $this->get(route('logout'));
        $legacy = User::create(['employee_id' => 'legacyadmin', 'password' => 'SecretPassword123']);
        $legacy->assignRole('admin');
        $this->post(route('admin.login.process'), ['username' => 'legacyadmin', 'password' => 'SecretPassword123'])->assertRedirect(route('dashboard'));
        $this->get(route('logout'));
        User::create(['employee_id' => '001234567', 'password' => 'SecretPassword123']);
        $this->post(route('login.process'), ['employee_id' => '001234567', 'password' => 'SecretPassword123'])->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_edit_customer_and_reset_password_without_changing_account_type(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $company = Company::create(['unique_id' => (string) Str::uuid(), 'name' => 'Surveyor Eksternal', 'address' => 'Alamat Uji']);
        $payload = ['name' => 'Nama Baru', 'email' => $customer->email, 'company_id' => $company->unique_id, 'password' => null];
        $this->actingAs($admin)->get(route('accounts.edit', $customer))->assertOk()->assertSee('Ubah Akun');
        $this->put(route('accounts.update', $customer), [...$payload, 'account_type' => 'admin'])->assertSessionHasErrors('account_type');
        $this->put(route('accounts.update', $customer), $payload)->assertSessionHasNoErrors();
        $this->assertSame('Nama Baru', $customer->fresh()->name);
        $this->assertSame('customer', $customer->fresh()->account_type);
        $this->assertTrue(Hash::check('SecretPassword123', $customer->fresh()->password));
        $this->put(route('accounts.update', $customer), [
            ...$payload, 'password' => 'ResetPassword123', 'password_confirmation' => 'ResetPassword123',
        ])->assertSessionHasNoErrors();
        $this->assertTrue($customer->fresh()->must_change_password);
        $this->assertTrue(Hash::check('ResetPassword123', $customer->fresh()->password));
        $this->assertStringNotContainsString('ResetPassword123', json_encode(AuditLog::all()->toArray()));
        $this->get('/accounts/'.$customer->id.'/edit')->assertNotFound();
    }

    public function test_initial_password_cannot_be_reused_as_the_new_password(): void
    {
        $customer = $this->customer();
        $customer->update(['must_change_password' => true]);
        $this->actingAs($customer)->put(route('customer.password'), [
            'current_password' => 'SecretPassword123', 'password' => 'SecretPassword123', 'password_confirmation' => 'SecretPassword123',
        ])->assertSessionHasErrors('password');
        $this->assertTrue($customer->fresh()->must_change_password);
    }

    public function test_pending_approval_can_be_reassigned_but_completed_decision_cannot(): void
    {
        $first = $this->customer();
        $second = $this->customer();
        $project = $this->project();
        $this->access($first, $project);
        $this->access($second, $project);
        $publication = $this->publish($first, $project, 'satisfaction_notes');
        $admin = User::where('username', 'administrator')->firstOrFail();
        $this->actingAs($admin)->put(route('accounts.reassign', $publication), ['approver_id' => $second->unique_id])->assertSessionHasNoErrors();
        $this->actingAs($first)->post(route('customer.decision', $publication), ['decision' => 'approved'])->assertForbidden();
        $this->actingAs($second)->post(route('customer.decision', $publication), ['decision' => 'approved'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->put(route('accounts.reassign', $publication), ['approver_id' => $first->unique_id])->assertSessionHasErrors('approver_id');
        $this->assertSame($second->id, $publication->fresh()->decided_by);
    }

    public function test_financial_unlock_is_document_and_user_specific_and_manual_lock_removes_it(): void
    {
        $first = $this->customer();
        $second = $this->customer();
        $project = $this->project();
        $this->access($first, $project, true);
        $this->access($second, $project, true);
        $initial = $this->publish($first, $project);
        $source = $this->source($project, 'final_boq');
        $admin = User::where('username', 'administrator')->firstOrFail();
        app(customer_management_service::class)->publish($admin, ['document_id' => $source->unique_id, 'approver_id' => $first->unique_id]);
        $final = CustomerDocument::where('project_job_document_id', $source->id)->firstOrFail();
        $this->actingAs($first)->post(route('customer.unlock', $initial), ['current_password' => 'SecretPassword123'])->assertSessionHasNoErrors();
        $this->getJson(route('customer.document.data', $final))->assertJsonMissingPath('data.prices');
        $this->actingAs($second)->getJson(route('customer.document.data', $initial))->assertJsonMissingPath('data.prices');
        $this->actingAs($first)->post(route('customer.lock', $initial))->assertSessionHasNoErrors();
        $this->getJson(route('customer.document.data', $initial))->assertJsonMissingPath('data.prices');
    }

    public function test_financial_unlock_attempts_are_rate_limited(): void
    {
        $customer = $this->customer();
        $project = $this->project();
        $this->access($customer, $project, true);
        $publication = $this->publish($customer, $project);
        $this->actingAs($customer);
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post(route('customer.unlock', $publication), ['current_password' => 'incorrect'])->assertSessionHasErrors('current_password');
        }
        $this->post(route('customer.unlock', $publication), ['current_password' => 'SecretPassword123'])->assertStatus(429);
    }

    public function test_document_decision_rolls_back_if_audit_cannot_be_persisted(): void
    {
        $customer = $this->customer();
        $project = $this->project();
        $this->access($customer, $project);
        $publication = $this->publish($customer, $project, 'satisfaction_notes');
        AuditLog::creating(function (AuditLog $audit_log): void {
            if ($audit_log->event_name === 'customer.document_decided') {
                throw new RuntimeException('Audit persistence unavailable.');
            }
        });
        $this->actingAs($customer)->withoutExceptionHandling();
        try {
            $this->post(route('customer.decision', $publication), ['decision' => 'approved']);
            $this->fail('A decision must not be saved without its audit record.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit persistence unavailable.', $exception->getMessage());
        }
        $this->assertSame('pending', $publication->fresh()->status);
        $this->assertNull($publication->fresh()->decided_at);
    }

    public function test_migration_backfills_legacy_admins_without_resetting_credentials(): void
    {
        $migration = require database_path('migrations/2026_10_11_000007_add_customer_accounts_and_portal.php');
        $migration->down();
        $password_hash = Hash::make('LegacyPassword123');
        $admin_id = DB::table('users')->insertGetId(['employee_id' => 'legacy-admin', 'password' => $password_hash]);
        $internal_id = DB::table('users')->insertGetId(['employee_id' => '001234567', 'password' => $password_hash]);
        $role = Role::findOrCreate('admin', 'web');
        DB::table('model_has_roles')->insert(['role_id' => $role->id, 'model_type' => User::class, 'model_id' => $admin_id]);
        $migration->up();
        $admin = User::findOrFail($admin_id);
        $this->assertSame('admin', $admin->account_type);
        $this->assertSame('legacy-admin', $admin->username);
        $this->assertSame($password_hash, $admin->password);
        $this->assertTrue(Str::isUuid($admin->unique_id));
        $this->assertSame('internal', User::findOrFail($internal_id)->account_type);
    }

    public function test_customer_history_prevents_project_ship_and_company_deletion_with_explicit_messages(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $project = $this->project();
        $project->update(['progress' => 0]);
        $this->access($customer, $project);
        $this->actingAs($admin)->delete(route('project.destroy', $project->unique_id))->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        $this->delete(route('ship.destroy', $project->ship->unique_id))->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('ships', ['id' => $project->ship_id]);
        $surveyor_company = Company::create(['unique_id' => (string) Str::uuid(), 'name' => 'Surveyor Uji', 'address' => 'Alamat Uji']);
        $customer->update(['company_id' => $surveyor_company->id]);
        $this->delete(route('company.destroy', $surveyor_company->unique_id))->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('companies', ['id' => $surveyor_company->id]);
    }
}
