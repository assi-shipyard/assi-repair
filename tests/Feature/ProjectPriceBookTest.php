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
use App\Models\ProjectDocumentJobMaterial;
use App\Models\ProjectJobDocument;
use App\Models\Ship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectPriceBookTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create(['employee_id' => '800000001', 'email' => 'project-book@example.test', 'password' => 'secret-pass']);
    }

    private function company(string $name): Company
    {
        return Company::create(['unique_id' => (string) Str::uuid(), 'name' => $name, 'address' => 'Alamat Uji']);
    }

    private function payload(): array
    {
        $company = $this->company('Pemilik Kapal');
        $ship = Ship::create([
            'name' => 'Kapal Uji', 'company_id' => $company->id, 'length_overall' => 80,
            'breadth' => 14, 'height' => 10, 'empty_draft' => 3, 'gross_tonnage' => 2000,
        ]);
        $division = OrganizationalUnit::create(['name' => 'Divisi Reparasi dan Rekayasa Umum', 'type' => 'division']);
        $position = Position::create(['name' => 'Pimpinan Uji', 'organizational_unit_id' => $division->id]);
        $employee = Employee::create(['employee_id' => '800000002', 'name' => 'Pegawai Uji', 'position_id' => $position->id]);

        return [
            'ship_id' => $ship->id, 'project_type' => 'Floating Repair',
            'project_leader_employee_id' => $employee->id, 'division_ids' => [$division->id],
            'status' => 'Not Started', 'progress' => 0,
        ];
    }

    public function test_project_explicitly_selects_any_book_regardless_of_customer(): void
    {
        $this->actingAs($this->user());
        $payload = $this->payload();
        $other_company = $this->company('Pelanggan Buku Berbeda');
        $book = PriceBook::create(['code' => 'OTHER', 'name' => 'Buku Kesepakatan', 'company_id' => $other_company->id]);
        $this->get(route('project.create'))->assertOk()->assertSee($book->unique_id)->assertSee('Buku Kamus Harga');
        $this->post(route('project.store'), [...$payload, 'price_book_uuid' => $book->unique_id])
            ->assertSessionHasNoErrors()->assertRedirect(route('project.index'));
        $project = Project::firstOrFail();
        $this->assertSame($book->id, $project->price_book_id);
        $this->assertSame($book->id, $project->price_book->id);
        $this->get(route('project.edit', $project->unique_id))->assertOk()
            ->assertSee('value="'.$book->unique_id.'" selected', false);
        $this->get(route('project.show', $project->unique_id))->assertOk()->assertSee('Buku Kesepakatan');
    }

    public function test_book_is_not_automatically_selected_and_can_be_changed_or_cleared(): void
    {
        $this->actingAs($this->user());
        $payload = $this->payload();
        $reference = PriceBook::create(['code' => 'MAIN', 'name' => 'Buku Utama', 'reference_slot' => 1]);
        $this->post(route('project.store'), $payload)->assertSessionHasNoErrors();
        $project = Project::firstOrFail();
        $this->assertNull($project->price_book_id);
        $payload['project_code'] = $project->project_code;
        $this->put(route('project.update', $project->unique_id), [
            ...$payload, 'price_book_uuid' => $reference->unique_id,
        ])->assertSessionHasNoErrors();
        $this->assertSame($reference->id, $project->fresh()->price_book_id);
        $activity = $project->activities()->where('event', 'updated')->latest('id')->firstOrFail();
        $this->assertContains(['field' => 'Buku Kamus Harga', 'old' => null, 'new' => 'Buku Utama'], $activity->changes);
        $this->put(route('project.update', $project->unique_id), $payload)->assertSessionHasNoErrors();
        $this->assertSame($reference->id, $project->fresh()->price_book_id);
        $this->put(route('project.update', $project->unique_id), [...$payload, 'price_book_uuid' => ''])
            ->assertSessionHasNoErrors();
        $this->assertNull($project->fresh()->price_book_id);
    }

    public function test_invalid_book_identifiers_are_rejected(): void
    {
        $this->actingAs($this->user());
        $payload = $this->payload();
        $book = PriceBook::create(['code' => 'MAIN', 'name' => 'Buku Utama', 'reference_slot' => 1]);
        foreach ([(string) $book->id, (string) Str::uuid(), ['invalid']] as $identifier) {
            $this->post(route('project.store'), [...$payload, 'price_book_uuid' => $identifier])
                ->assertSessionHasErrors('price_book_uuid');
        }
        $this->assertDatabaseCount('projects', 0);
        $this->post(route('project.store'), [...$payload, 'price_book_uuid' => $book->unique_id])->assertSessionHasNoErrors();
        $project = Project::firstOrFail();
        $this->put(route('project.update', $project->unique_id), [
            ...$payload, 'project_code' => $project->project_code, 'price_book_uuid' => (string) Str::uuid(),
        ])->assertSessionHasErrors('price_book_uuid');
        $this->assertSame($book->id, $project->fresh()->price_book_id);
    }

    public function test_book_changes_leave_saved_document_prices_unchanged(): void
    {
        $this->actingAs($this->user());
        $payload = $this->payload();
        $first = PriceBook::create(['code' => 'FIRST', 'name' => 'Buku Pertama']);
        $second = PriceBook::create(['code' => 'SECOND', 'name' => 'Buku Kedua']);
        $this->post(route('project.store'), [...$payload, 'price_book_uuid' => $first->unique_id])->assertSessionHasNoErrors();
        $project = Project::firstOrFail();
        $document = ProjectJobDocument::create(['project_id' => $project->id, 'document_type' => 'initial_boq', 'status' => 'draft', 'revision_no' => 0]);
        $job = ProjectDocumentJob::create(['project_job_document_id' => $document->id, 'job_name' => 'Pekerjaan Uji', 'est_price' => '7000']);
        $material = ProjectDocumentJobMaterial::create(['project_document_job_id' => $job->id, 'material_name' => 'Pelat Uji', 'price' => '14000']);
        $this->put(route('project.update', $project->unique_id), [
            ...$payload, 'project_code' => $project->project_code, 'price_book_uuid' => $second->unique_id,
        ])->assertSessionHasNoErrors();
        $first->update(['name' => 'Buku Pertama Diperbarui']);
        $this->assertSame('7000.00', $job->fresh()->est_price);
        $this->assertSame('14000.00', $material->fresh()->price);
    }

    public function test_empty_book_used_by_a_project_cannot_be_deleted(): void
    {
        $user = $this->user();
        $user->assignRole(Role::findOrCreate('admin'));
        $this->actingAs($user);
        $payload = $this->payload();
        $book = PriceBook::create(['code' => 'MAIN', 'name' => 'Buku Utama', 'reference_slot' => 1]);
        $this->post(route('project.store'), [...$payload, 'price_book_uuid' => $book->unique_id])->assertSessionHasNoErrors();
        $this->delete(route('price-book.destroy', $book))->assertSessionHasErrors('book');
        $this->assertDatabaseHas('price_books', ['id' => $book->id]);
    }

    public function test_guests_cannot_set_a_project_book(): void
    {
        $this->post(route('project.store'), [])->assertRedirect(route('login'));
    }
}
