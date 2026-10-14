<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\OrganizationalUnit;
use App\Models\Position;
use App\Models\PriceBook;
use App\Models\Project;
use App\Models\ProjectDocumentJob;
use App\Models\ProjectDocumentJobPhoto;
use App\Models\ProjectJobDocument;
use App\Models\ProjectJobGroup;
use App\Models\Ship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectJobDocumentPhotoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Project $project;

    private ProjectJobDocument $document;

    private ProjectDocumentJob $job;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('ship');
        Storage::fake('public');

        $this->admin = User::create([
            'employee_id' => '800000021',
            'email' => 'photo-admin@example.test',
            'password' => 'secret-pass',
        ]);
        $this->admin->assignRole(Role::findOrCreate('admin'));
        $this->actingAs($this->admin);

        $company = Company::create([
            'unique_id' => (string) Str::uuid(),
            'name' => 'Pemilik Kapal Uji',
            'address' => 'Alamat Uji',
        ]);
        $ship = Ship::create([
            'name' => 'KM Uji Foto',
            'company_id' => $company->id,
            'length_overall' => 80,
            'breadth' => 14,
            'height' => 10,
            'empty_draft' => 3,
            'gross_tonnage' => 2000,
        ]);
        $division = OrganizationalUnit::create([
            'name' => 'Divisi Reparasi dan Rekayasa Umum',
            'type' => 'division',
        ]);
        $position = Position::create([
            'name' => 'Pimpinan Foto',
            'organizational_unit_id' => $division->id,
        ]);
        $employee = Employee::create([
            'employee_id' => '800000022',
            'name' => 'Pegawai Foto',
            'position_id' => $position->id,
        ]);
        $book = PriceBook::create(['code' => 'BOOK-PHOTO', 'name' => 'Buku Foto']);

        $this->post(route('project.store'), [
            'ship_id' => $ship->id,
            'project_type' => 'Floating Repair',
            'project_leader_employee_id' => $employee->id,
            'division_ids' => [$division->id],
            'status' => 'Not Started',
            'progress' => 0,
            'price_book_uuid' => $book->unique_id,
        ])->assertSessionHasNoErrors();
        $this->project = Project::firstOrFail();

        $this->document = ProjectJobDocument::create([
            'project_id' => $this->project->id,
            'document_type' => 'initial_boq',
            'status' => 'draft',
            'revision_no' => 1,
        ]);

        $group = ProjectJobGroup::query()->firstOrFail();

        $this->job = ProjectDocumentJob::create([
            'unique_id' => (string) Str::uuid(),
            'project_job_document_id' => $this->document->id,
            'project_job_group_id' => $group->id,
            'job_name' => 'Pekerjaan Uji Foto',
            'job_volume_estimated' => '10',
            'unit' => 'm',
            'unit_price' => '25000',
            'est_price' => '250000',
            'sort_order' => 1,
            'depth_level' => 1,
        ]);
    }

    public function test_photos_endpoint_returns_json_for_ajax_request(): void
    {
        $photo = ProjectDocumentJobPhoto::create([
            'unique_id' => (string) Str::uuid(),
            'project_document_job_id' => $this->job->id,
            'photo_path' => 'photos/test.jpg',
            'photo_category' => 'progress',
            'caption' => 'Foto Progres Awal',
            'uploaded_by' => $this->admin->id,
        ]);

        $response = $this->getJson(route('project.job-document.workflow.job.photos', [
            $this->project->unique_id,
            $this->document->unique_id,
            $this->job->unique_id,
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('job.unique_id', $this->job->unique_id)
            ->assertJsonPath('photos.0.unique_id', $photo->unique_id)
            ->assertJsonPath('photos.0.caption', 'Foto Progres Awal')
            ->assertJsonPath('photos.0.category_label', 'Progres');
    }

    public function test_photo_can_be_uploaded_via_ajax(): void
    {
        $file = UploadedFile::fake()->image('lambung.jpg', 800, 600)->size(500);

        $response = $this->postJson(route('project.job-document.workflow.job.photo.store', [
            $this->project->unique_id,
            $this->document->unique_id,
            $this->job->unique_id,
        ]), [
            'job_photo' => $file,
            'photo_category' => 'result_before',
            'caption' => 'Kondisi sebelum perbaikan plat',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('photo.caption', 'Kondisi sebelum perbaikan plat')
            ->assertJsonPath('photo.category_label', 'Sebelum Perbaikan');

        $this->assertDatabaseHas('project_document_job_photos', [
            'project_document_job_id' => $this->job->id,
            'photo_category' => 'result_before',
            'caption' => 'Kondisi sebelum perbaikan plat',
        ]);

        $createdPhoto = ProjectDocumentJobPhoto::firstOrFail();
        Storage::disk('ship')->assertExists($createdPhoto->photo_path);
    }

    public function test_photo_upload_validates_file_and_category(): void
    {
        $response = $this->postJson(route('project.job-document.workflow.job.photo.store', [
            $this->project->unique_id,
            $this->document->unique_id,
            $this->job->unique_id,
        ]), [
            'job_photo' => 'not-a-file',
            'photo_category' => 'invalid-category',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['job_photo', 'photo_category']);
    }

    public function test_photo_can_be_deleted_via_ajax(): void
    {
        $photo = ProjectDocumentJobPhoto::create([
            'unique_id' => (string) Str::uuid(),
            'project_document_job_id' => $this->job->id,
            'photo_path' => 'photos/delete_me.jpg',
            'photo_category' => 'quotation',
            'uploaded_by' => $this->admin->id,
        ]);
        Storage::disk('ship')->put($photo->photo_path, 'fake image content');

        $response = $this->deleteJson(route('project.job-document.workflow.job.photo.destroy', [
            $this->project->unique_id,
            $this->document->unique_id,
            $this->job->unique_id,
            $photo->unique_id,
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 0);

        $this->assertDatabaseMissing('project_document_job_photos', [
            'id' => $photo->id,
        ]);
        Storage::disk('ship')->assertMissing($photo->photo_path);
    }

    public function test_boq_sheet_renders_photo_modal_and_trigger_button(): void
    {
        ProjectDocumentJobPhoto::create([
            'unique_id' => (string) Str::uuid(),
            'project_document_job_id' => $this->job->id,
            'photo_path' => 'photos/view_test.jpg',
            'photo_category' => 'progress',
            'uploaded_by' => $this->admin->id,
        ]);

        $response = $this->get(route('project.job-document.workflow.show', [
            $this->project->unique_id,
            $this->document->unique_id,
        ]));

        $response->assertOk()
            ->assertSee('boq-photo-modal')
            ->assertSee('boq-job-photos-btn')
            ->assertSee('boq-job-photo-badge-wrapper')
            ->assertSee('Foto');
    }

    public function test_satisfaction_notes_and_final_boq_render_photo_modal(): void
    {
        foreach ([ProjectJobDocument::TYPE_SATISFACTION_NOTES, ProjectJobDocument::TYPE_FINAL_BOQ] as $docType) {
            $doc = ProjectJobDocument::create([
                'project_id' => $this->project->id,
                'document_type' => $docType,
                'status' => 'draft',
                'revision_no' => 1,
            ]);

            $group = ProjectJobGroup::query()->firstOrFail();
            ProjectDocumentJob::create([
                'unique_id' => (string) Str::uuid(),
                'project_job_document_id' => $doc->id,
                'project_job_group_id' => $group->id,
                'job_name' => 'Pekerjaan '.$docType,
                'job_volume_estimated' => '5',
                'unit' => 'ls',
                'unit_price' => '10000',
                'est_price' => '50000',
                'sort_order' => 1,
                'depth_level' => 1,
            ]);

            $response = $this->get(route('project.job-document.workflow.show', [
                $this->project->unique_id,
                $doc->unique_id,
            ]));

            $response->assertOk()
                ->assertSee('boq-photo-modal')
                ->assertSee('boq-job-photos-btn');
        }
    }

    public function test_cannot_upload_photo_to_locked_document(): void
    {
        $this->document->update(['status' => 'locked']);

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson(route('project.job-document.workflow.job.photo.store', [
            $this->project->unique_id,
            $this->document->unique_id,
            $this->job->unique_id,
        ]), [
            'job_photo' => $file,
        ]);

        $response->assertStatus(403);
    }
}
