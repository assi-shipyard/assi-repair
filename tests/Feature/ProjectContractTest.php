<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Project;
use App\Models\project_contract;
use App\Models\project_contract_document;
use App\Models\Ship;
use App\Models\User;
use App\Services\ProjectActivityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectContractTest extends TestCase
{
    use RefreshDatabase;

    private User $legal;

    private User $viewer;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::set(project_contract_document::DISK, Storage::fake('project-contract-test', ['throw' => true]));
        $this->legal = User::create(['employee_id' => '900000001', 'password' => 'test-password']);
        $this->legal->assignRole(Role::findOrCreate('legal'));
        $this->viewer = User::create(['employee_id' => '900000002', 'password' => 'test-password']);
        $company = Company::create(['unique_id' => (string) Str::uuid(), 'name' => 'Pemilik Uji', 'address' => 'Alamat Uji']);
        $ship = Ship::create([
            'name' => 'Kapal Kontrak', 'company_id' => $company->id,
            'length_overall' => 80, 'breadth' => 14, 'height' => 10,
            'empty_draft' => 3, 'gross_tonnage' => 2000,
        ]);
        $this->project = Project::create([
            'project_code' => 'CONTRACT-TEST', 'ship_id' => $ship->id,
            'project_type' => 'Floating Repair', 'status' => 'Not Started', 'progress' => 0,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'contract_number' => 'KONTRAK/2026/001',
            'status' => 'drafting',
            'signing_due_date' => '2026-11-01',
        ], $overrides);
    }

    private function pdf(string $name = 'kontrak.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF");
    }

    public function test_legal_can_record_a_draft_without_upload_and_view_the_form(): void
    {
        $this->actingAs($this->legal)
            ->get(route('project.show', $this->project->unique_id))
            ->assertOk()->assertSee('Kontrak belum dicatat.')->assertSee('id="project-contract-form"', false);
        $this->put(route('project.contract.update', $this->project->unique_id), $this->payload())
            ->assertRedirect(route('project.show', $this->project->unique_id))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('project_contracts', [
            'project_id' => $this->project->id, 'contract_number' => 'KONTRAK/2026/001', 'status' => 'drafting',
        ]);
        $this->assertDatabaseCount('project_contract_documents', 0);
        $this->get(route('project.show', $this->project->unique_id))
            ->assertOk()->assertSee('KONTRAK/2026/001')->assertSee('Kontrak sedang disusun');
    }

    public function test_uploads_are_private_versioned_and_audited_with_the_actor(): void
    {
        $this->actingAs($this->legal);
        foreach (['drafting', 'signed_by_shipyard'] as $status) {
            $this->put(route('project.contract.update', $this->project->unique_id), $this->payload([
                'status' => $status, 'document' => $this->pdf(),
            ]))->assertSessionHasNoErrors()->assertRedirect();
        }

        $documents = project_contract_document::orderBy('id')->get();
        $this->assertCount(2, $documents);
        $this->assertNotSame($documents[0]->document_path, $documents[1]->document_path);
        foreach ($documents as $document) {
            Storage::disk('ship')->assertExists($document->document_path);
            $this->assertStringStartsWith('projects/'.$this->project->unique_id.'/contracts/', $document->document_path);
            $this->assertSame($this->legal->id, $document->uploaded_by);
        }
        $this->assertSame('drafting', $documents[0]->status);
        $this->assertSame('signed_by_shipyard', $documents[1]->status);
        $audits = AuditLog::where('event_name', 'project.contract_updated')->orderBy('id')->get();
        $this->assertCount(2, $audits);
        $this->assertSame($this->legal->id, $audits[1]->user_id);
        $this->assertSame('drafting', $audits[1]->event_payload['before']['status']);
        $this->assertSame('signed_by_shipyard', $audits[1]->event_payload['after']['status']);
        $this->assertSame($documents[1]->unique_id, $audits[1]->event_payload['document_uuid']);
        $activity = $this->project->activities()->where('event', 'contract_updated')->latest('id')->firstOrFail();
        $this->assertSame($this->legal->id, $activity->actor_id);
        $this->assertContains([
            'field' => 'Status kontrak', 'old' => 'Kontrak sedang disusun', 'new' => 'Ditandatangani pihak pertama (galangan)',
        ], $activity->changes);
    }

    public function test_every_signing_stage_is_supported_and_leaves_a_timeline_entry(): void
    {
        $this->actingAs($this->legal);
        foreach (project_contract::STATUS_LABELS as $status => $label) {
            $this->put(route('project.contract.update', $this->project->unique_id), $this->payload(['status' => $status]))
                ->assertSessionHasNoErrors()->assertRedirect();
            $this->get(route('project.show', $this->project->unique_id))->assertOk()->assertSee($label);
        }
        $this->assertSame(5, $this->project->activities()->where('event', 'contract_updated')->count());
        $this->assertDatabaseCount('project_contracts', 1);
    }

    public function test_other_internal_users_cannot_write_but_admins_can_manage_contracts(): void
    {
        $this->actingAs($this->viewer);
        $this->get(route('project.show', $this->project->unique_id))
            ->assertOk()->assertDontSee('id="project-contract-form"', false);
        $this->put(route('project.contract.update', $this->project->unique_id), $this->payload(['document' => $this->pdf()]))
            ->assertForbidden();
        $this->assertDatabaseCount('project_contracts', 0);
        $this->assertSame([], Storage::disk('ship')->allFiles());
        $this->viewer->assignRole(Role::findOrCreate('admin'));
        $this->get(route('project.show', $this->project->unique_id))
            ->assertOk()->assertSee('id="project-contract-form"', false);
        $this->put(route('project.contract.update', $this->project->unique_id), $this->payload(['document' => $this->pdf()]))
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('project_contracts', ['project_id' => $this->project->id, 'status' => 'drafting']);
        $document = project_contract_document::firstOrFail();
        $this->assertSame($this->viewer->id, $document->uploaded_by);
        Storage::disk('ship')->assertExists($document->document_path);
    }

    public function test_contract_management_is_in_a_tab_after_documents(): void
    {
        $this->actingAs($this->legal)->get(route('project.show', $this->project->unique_id))
            ->assertOk()
            ->assertSeeInOrder(['href="#tab_dokumen"', 'href="#tab_kontrak"'], false)
            ->assertSeeInOrder(['id="tab_kontrak"', 'id="project-contract"', 'id="project-contract-form"'], false);
    }

    public function test_contract_tab_remains_open_after_saving_or_validation_errors(): void
    {
        $this->actingAs($this->legal);
        $this->followingRedirects()
            ->put(route('project.contract.update', $this->project->unique_id), $this->payload())
            ->assertOk()->assertSee('class="tab-pane active show" id="tab_kontrak"', false);
        $this->followingRedirects()->from(route('project.show', $this->project->unique_id))
            ->put(route('project.contract.update', $this->project->unique_id), $this->payload(['contract_number' => '']))
            ->assertOk()->assertSee('class="tab-pane active show" id="tab_kontrak"', false)
            ->assertSee('Nomor kontrak wajib diisi.');
    }

    public function test_guests_and_customer_accounts_cannot_access_contract_endpoints(): void
    {
        $this->put(route('project.contract.update', $this->project->unique_id), $this->payload())
            ->assertRedirect(route('login'));
        $this->get(route('project.contract.download', [$this->project->unique_id, Str::uuid()]))
            ->assertRedirect(route('login'));
        $customer = User::create([
            'account_type' => User::TYPE_CUSTOMER, 'name' => 'Pelanggan Uji',
            'email' => 'contract-customer@example.test', 'password' => 'test-password',
        ]);
        $customer->assignRole(Role::findOrCreate('legal'));
        $this->actingAs($customer)->put(route('project.contract.update', $this->project->unique_id), $this->payload())
            ->assertForbidden();
        $this->get(route('project.contract.download', [$this->project->unique_id, Str::uuid()]))->assertForbidden();
    }

    public function test_input_is_validated_before_any_contract_or_file_is_saved(): void
    {
        $this->actingAs($this->legal);
        $spoofed_pdf = UploadedFile::fake()->createWithContent('fake.pdf', 'This is not a PDF.');
        $invalid_inputs = [
            ['contract_number', ''],
            ['contract_number', str_repeat('a', 101)],
            ['contract_number', ['invalid']],
            ['status', 'invalid'],
            ['status', ['drafting']],
            ['signing_due_date', '2026-02-30'],
            ['signing_due_date', 'tomorrow'],
            ['document', UploadedFile::fake()->create('malware.exe', 1, 'application/octet-stream')],
            ['document', UploadedFile::fake()->create('oversized.pdf', 20481, 'application/pdf')],
            ['document', new UploadedFile($spoofed_pdf->getPathname(), 'fake.pdf', 'application/pdf', null, true)],
            ['document', UploadedFile::fake()->create('contract.exe', 1, 'application/pdf')],
            ['document', 'not-a-file'],
        ];
        foreach ($invalid_inputs as $index => [$field, $value]) {
            $response = $this->put(route('project.contract.update', $this->project->unique_id), $this->payload([$field => $value]));
            $this->assertTrue(session()->has('errors'), 'Expected validation failure for '.$field.' at case '.$index);
            $response->assertSessionHasErrors($field);
        }
        $this->assertDatabaseCount('project_contracts', 0);
        $this->assertSame([], Storage::disk('ship')->allFiles());
    }

    public function test_supported_file_formats_and_exact_size_limit_are_accepted(): void
    {
        $this->actingAs($this->legal);
        foreach ([
            ['contract.pdf', 'application/pdf'],
            ['contract.doc', 'application/msword'],
            ['contract.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        ] as [$name, $mime_type]) {
            $this->put(route('project.contract.update', $this->project->unique_id), $this->payload([
                'document' => UploadedFile::fake()->create($name, 20480, $mime_type),
            ]))->assertSessionHasNoErrors()->assertRedirect();
        }
        $this->assertDatabaseCount('project_contract_documents', 3);
    }

    public function test_saving_without_a_new_upload_keeps_existing_versions_and_ignores_no_op_updates(): void
    {
        $this->actingAs($this->legal);
        $this->put(route('project.contract.update', $this->project->unique_id), $this->payload(['document' => $this->pdf()]))
            ->assertSessionHasNoErrors();
        $document = project_contract_document::firstOrFail();
        $this->put(route('project.contract.update', $this->project->unique_id), $this->payload())
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('project_contract_documents', 1);
        Storage::disk('ship')->assertExists($document->document_path);
        $this->assertSame(1, $this->project->activities()->where('event', 'contract_updated')->count());
        $this->assertSame(1, AuditLog::where('event_name', 'project.contract_updated')->count());
    }

    public function test_internal_downloads_are_scoped_to_project_and_use_uuids(): void
    {
        $this->actingAs($this->legal)
            ->put(route('project.contract.update', $this->project->unique_id), $this->payload(['document' => $this->pdf()]))
            ->assertSessionHasNoErrors();
        $document = project_contract_document::firstOrFail();
        $this->actingAs($this->viewer)
            ->get(route('project.contract.download', [$this->project->unique_id, $document->unique_id]))
            ->assertDownload('kontrak.pdf')->assertHeader('X-Content-Type-Options', 'nosniff');
        $other_project = $this->project->replicate(['unique_id', 'project_code']);
        $other_project->project_code = 'OTHER-PROJECT';
        $other_project->save();
        $this->get(route('project.contract.download', [$other_project->unique_id, $document->unique_id]))->assertNotFound();
        $this->get(route('project.contract.download', [$this->project->id, $document->unique_id]))->assertNotFound();
        $this->get(route('project.contract.download', [$this->project->unique_id, $document->id]))->assertNotFound();
        Storage::disk('ship')->delete($document->document_path);
        $this->get(route('project.contract.download', [$this->project->unique_id, $document->unique_id]))->assertNotFound();
        $this->actingAs($this->legal)->put(route('project.contract.update', Str::uuid()), $this->payload())->assertNotFound();
    }

    public function test_optional_reminder_uses_wib_days_and_stops_when_customer_signs(): void
    {
        $this->travelTo(Carbon::parse('2026-10-26 18:00:00', 'UTC'));
        $this->actingAs($this->legal);
        $expectations = [
            ['2026-10-30', 3, 'Sisa waktu penandatanganan 3 hari.'],
            ['2026-10-27', 0, 'Batas penandatanganan hari ini.'],
            ['2026-10-25', -2, 'Terlambat 2 hari.'],
        ];
        foreach ($expectations as [$date, $days, $message]) {
            $this->put(route('project.contract.update', $this->project->unique_id), $this->payload(['signing_due_date' => $date]))
                ->assertSessionHasNoErrors();
            $this->assertSame($days, $this->project->contract()->firstOrFail()->remaining_days());
            $this->get(route('project.show', $this->project->unique_id))
                ->assertOk()->assertSee($message)->assertSee('Oktober 2026')->assertSee('WIB');
        }
        $this->put(route('project.contract.update', $this->project->unique_id), $this->payload([
            'signing_due_date' => '2026-10-25', 'status' => 'signed_by_customer',
        ]))->assertSessionHasNoErrors();
        $this->assertNull($this->project->contract()->firstOrFail()->remaining_days());
        $this->get(route('project.show', $this->project->unique_id))
            ->assertOk()->assertDontSee('Pengingat Penandatanganan Kontrak')->assertSee('Pengingat dihentikan.');
        $this->put(route('project.contract.update', $this->project->unique_id), $this->payload(['signing_due_date' => '']))
            ->assertSessionHasNoErrors();
        $this->assertNull($this->project->contract()->firstOrFail()->signing_due_date);
        $this->get(route('project.show', $this->project->unique_id))
            ->assertOk()->assertDontSee('Pengingat Penandatanganan Kontrak');
    }

    public function test_audit_failure_rolls_back_contract_and_removes_the_new_upload(): void
    {
        $this->actingAs($this->legal);
        $this->mock(ProjectActivityService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('record')->once()->andThrow(new RuntimeException('Audit unavailable'));
        });
        $this->withoutExceptionHandling();
        try {
            $this->put(route('project.contract.update', $this->project->unique_id), $this->payload(['document' => $this->pdf()]));
            $this->fail('Expected the audit failure to propagate.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit unavailable', $exception->getMessage());
        }
        $this->assertDatabaseCount('project_contracts', 0);
        $this->assertDatabaseCount('project_contract_documents', 0);
        $this->assertSame([], Storage::disk('ship')->allFiles());
        $this->assertSame(0, AuditLog::where('event_name', 'project.contract_updated')->count());
    }
}
