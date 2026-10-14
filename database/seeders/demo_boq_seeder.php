<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PriceBook;
use App\Models\PriceBookItem;
use App\Models\Project;
use App\Models\ProjectJobGroup;
use App\Models\ProjectDocumentJob;
use App\Models\ProjectJobDocument;
use App\Models\User;
use App\Services\project_boq_service;
use App\Services\ProjectJobDocumentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

// Demo data only: builds an approved Repair List and a priced RAB Awal draft for one project.
// Run with: php artisan db:seed --class="Database\Seeders\demo_boq_seeder"
class demo_boq_seeder extends Seeder
{
    private const PROJECT_CODE = 'D.001.26.O.h';

    private const PRICE_ITEMS = [
        ['service', 'JS-UM-01', 'Penyediaan listrik darat', 'hari', 1500000, null, null],
        ['service', 'JS-UM-02', 'Penyediaan air tawar', 'ton', 75000, null, null],
        ['service', 'JS-UM-03', 'Gas free certificate & fire watch', 'ls', 6500000, null, null],
        ['service', 'JS-DOC-01', 'Naik dan turun dok', 'ls', 45000000, null, null],
        ['service', 'JS-DOC-02', 'Sewa dok', 'hari', 12000000, 2, 'docking'],
        ['service', 'JS-DOC-03', 'Kapal tunda (tug assist)', 'jam', 3500000, null, 'tugboat'],
        ['service', 'JS-DOC-04', 'Sewa crane 25 ton', 'jam', 750000, 8, 'crane'],
        ['service', 'JS-LB-01', 'Penggantian pelat baja (cropping & renewal)', 'kg', 42000, 15, 'plate'],
        ['service', 'JS-LB-02', 'Pengukuran ketebalan pelat (UT)', 'titik', 25000, 100, null],
        ['service', 'JS-CT-01', 'Sandblasting SA 2.5', 'm²', 85000, null, 'area'],
        ['service', 'JS-CT-02', 'Pengecatan per lapis', 'm²', 18000, null, 'area'],
        ['service', 'JS-CT-04', 'Cleaning lambung (high pressure water jet)', 'm²', 12000, 500, 'area'],
        ['service', 'JS-CT-03', 'Penulisan draft mark & nama kapal', 'ls', 7500000, null, null],
        ['service', 'JS-PM-01', 'Overhaul & polishing propeller', 'ls', 28000000, null, null],
        ['service', 'JS-PM-02', 'Cabut, periksa, pasang poros propeller', 'ls', 35000000, null, null],
        ['service', 'JS-PM-03', 'Overhaul katup laut', 'bh', 2750000, null, null],
        ['service', 'JS-PP-01', 'Penggantian pipa baja', 'm', 450000, null, 'pipe'],
        ['service', 'JS-KL-01', 'Megger test motor listrik', 'unit', 450000, null, null],
        ['material', 'MT-PL-10', 'Pelat baja SS400 10 mm', 'kg', 18500, null, null],
        ['material', 'MT-PL-12', 'Pelat baja SS400 12 mm', 'kg', 18500, null, null],
        ['material', 'MT-EL-01', 'Elektroda las E7016', 'kg', 45000, null, null],
        ['material', 'MT-CT-01', 'Cat antifouling', 'liter', 285000, null, null],
        ['material', 'MT-CT-02', 'Cat anticorrosive', 'liter', 165000, null, null],
        ['material', 'MT-ZN-01', 'Zinc anode', 'bh', 650000, null, null],
        ['material', 'MT-PP-01', 'Pipa baja SCH40 2 inci', 'm', 210000, null, null],
        ['material', 'MT-PK-01', 'Gasket & packing', 'set', 350000, null, null],
    ];

    public function __construct(
        private readonly project_boq_service $boq,
        private readonly ProjectJobDocumentService $documents,
    ) {}

    public function run(): void
    {
        $project = Project::query()->where('project_code', self::PROJECT_CODE)->first()
            ?? throw new RuntimeException('Proyek '.self::PROJECT_CODE.' tidak ditemukan.');

        if (ProjectJobDocument::query()->where('project_id', $project->id)->exists()) {
            $this->command?->warn('Proyek '.self::PROJECT_CODE.' sudah memiliki dokumen pekerjaan; seeder dilewati.');

            return;
        }

        $actor_id = User::query()->role('admin')->value('id');
        $employee_id = $project->project_leader_employee_id;

        DB::transaction(function () use ($project, $actor_id, $employee_id): void {
            $this->ensure_price_book();

            $repair_list = $this->documents->createDocument($project, [
                'document_type' => ProjectJobDocument::TYPE_REPAIR_LIST,
                'document_number' => 'RL/001/X/2026',
                'status' => 'draft',
                'prepared_by_employee_id' => $employee_id,
                'notes' => 'Contoh Repair List hasil survei awal kapal.',
            ], $actor_id);

            $this->build_jobs($repair_list, $actor_id);
            $this->documents->submit($repair_list, $actor_id);
            $this->documents->approve($repair_list, $employee_id, $actor_id);

            $initial_boq = $this->boq->create_document_from_previous_phase($project, ProjectJobDocument::TYPE_INITIAL_BOQ, [
                'document_number' => 'RAB/001/X/2026',
                'prepared_by_employee_id' => $employee_id,
                'notes' => 'Contoh RAB Awal disalin dari Repair List lalu diberi harga dari Kamus Harga.',
            ], $actor_id);

            $this->price_copied_jobs($initial_boq, $actor_id);
        });

        $this->command?->info('Contoh Repair List (disetujui) dan RAB Awal (draft) dibuat untuk proyek '.self::PROJECT_CODE.'.');
    }

    private function ensure_price_book(): void
    {
        if (PriceBook::query()->where('reference_slot', 1)->exists()) {
            $book = PriceBook::query()->where('reference_slot', 1)->firstOrFail();
        } else {
            $book = PriceBook::create(['code' => 'KH-2026', 'name' => 'Kamus Harga Utama 2026', 'reference_slot' => 1, 'description' => 'Contoh kamus harga acuan.']);
        }

        foreach (self::PRICE_ITEMS as [$group, $code, $name, $unit, $price, $minimum, $rule]) {
            $book->items()->updateOrCreate(['code' => $code], [
                'group' => $group,
                'name' => $name,
                'unit' => $unit,
                'calculation_rule' => $rule,
                'unit_price' => $price,
                'has_minimum_charge' => $minimum !== null,
                'minimum_quantity' => $minimum,
            ]);
        }
    }

    // Each entry: [group name, job spec, sub-jobs, materials]. Specs reference price items by code.
    private function job_plan(): array
    {
        $plate = fn (int $length, int $width, int $thickness, int $quantity): array => ['type' => 'plate', 'material' => 'steel', 'length' => $length, 'width' => $width, 'thickness' => $thickness, 'quantity' => $quantity, 'safety_factor' => 1.1];

        return [
            ['Pekerjaan Umum', ['job_name' => 'Penyediaan listrik darat selama docking', 'item' => 'JS-UM-01', 'job_volume_estimated' => 14], [], []],
            ['Pekerjaan Umum', ['job_name' => 'Penyediaan air tawar', 'item' => 'JS-UM-02', 'job_volume_estimated' => 40], [], []],
            ['Pekerjaan Umum', ['job_name' => 'Gas free certificate & fire watch tangki', 'item' => 'JS-UM-03', 'job_volume_estimated' => 1], [], []],

            ['Pekerjaan Docking', ['job_name' => 'Naik dan turun dok', 'item' => 'JS-DOC-01', 'job_volume_estimated' => 1], [], []],
            ['Pekerjaan Docking', ['job_name' => 'Sewa dok', 'item' => 'JS-DOC-02', 'job_volume_estimated' => 9.5], [], []],
            ['Pekerjaan Docking', ['job_name' => 'Kapal tunda saat naik dan turun dok', 'item' => 'JS-DOC-03', 'calculation' => ['type' => 'tugboat', 'entries' => [
                ['start_at' => '2026-10-19T08:00', 'end_at' => '2026-10-19T10:30', 'is_holiday' => false],
                ['start_at' => '2026-10-31T16:00', 'end_at' => '2026-10-31T18:00', 'is_holiday' => false],
            ]], 'notes' => 'Naik dok hari kerja pagi, turun dok Sabtu sore.'], [], []],

            ['Pekerjaan Lambung & Konstruksi', ['job_name' => 'Pengukuran ketebalan pelat lambung', 'item' => 'JS-LB-02', 'job_volume_estimated' => 400], [], []],
            ['Pekerjaan Lambung & Konstruksi', ['job_name' => 'Penggantian pelat lambung bawah garis air', 'item' => 'JS-LB-01', 'calculation' => $plate(2400, 1200, 12, 4), 'notes' => 'Frame 45–52 sisi kiri dan kanan.'], [
                [['job_name' => 'Penggantian pelat bilge keel kiri', 'item' => 'JS-LB-01', 'calculation' => $plate(1800, 300, 10, 1)], [
                    ['material_name' => 'Pelat baja SS400 10 mm', 'item' => 'MT-PL-10', 'volume' => 50],
                ]],
                [['job_name' => 'Pemasangan pelat doubler kecil', 'item' => 'JS-LB-01', 'calculation' => $plate(300, 200, 8, 3)], []],
            ], [
                ['material_name' => 'Pelat baja SS400 12 mm', 'item' => 'MT-PL-12', 'volume' => 325.6, 'dimension' => '2400 × 1200 × 12 mm, 4 lembar'],
                ['material_name' => 'Elektroda las E7016', 'item' => 'MT-EL-01', 'volume' => 60],
            ]],

            ['Pekerjaan Pengecatan', ['job_name' => 'Cleaning lambung dengan water jet', 'item' => 'JS-CT-04', 'job_volume_estimated' => 420], [], []],
            ['Pekerjaan Pengecatan', ['job_name' => 'Sandblasting lambung bawah garis air', 'item' => 'JS-CT-01', 'job_volume_estimated' => 1250], [], []],
            ['Pekerjaan Pengecatan', ['job_name' => 'Pengecatan lambung bawah garis air', 'notes' => 'Sistem cat sesuai rekomendasi pabrikan.'], [
                [['job_name' => 'Anticorrosive 2 lapis', 'item' => 'JS-CT-02', 'job_volume_estimated' => 2500], [
                    ['material_name' => 'Cat anticorrosive', 'item' => 'MT-CT-02', 'volume' => 420],
                ]],
                [['job_name' => 'Antifouling 2 lapis', 'item' => 'JS-CT-02', 'job_volume_estimated' => 2500], [
                    ['material_name' => 'Cat antifouling', 'item' => 'MT-CT-01', 'volume' => 450],
                ]],
            ], []],
            ['Pekerjaan Pengecatan', ['job_name' => 'Penulisan draft mark & nama kapal', 'item' => 'JS-CT-03', 'job_volume_estimated' => 1], [], []],

            ['Pekerjaan Permesinan', ['job_name' => 'Cabut, periksa, dan pasang poros propeller', 'item' => 'JS-PM-02', 'job_volume_estimated' => 1], [
                [['job_name' => 'Sewa crane untuk angkat propeller', 'item' => 'JS-DOC-04', 'job_volume_estimated' => 3], []],
            ], []],
            ['Pekerjaan Permesinan', ['job_name' => 'Overhaul & polishing propeller', 'item' => 'JS-PM-01', 'job_volume_estimated' => 1], [], []],
            ['Pekerjaan Permesinan', ['job_name' => 'Overhaul katup laut', 'item' => 'JS-PM-03', 'job_volume_estimated' => 6], [
                [['job_name' => 'Sewa crane untuk angkat katup laut', 'item' => 'JS-DOC-04', 'job_volume_estimated' => 1.5], []],
            ], [
                ['material_name' => 'Gasket & packing', 'item' => 'MT-PK-01', 'volume' => 6],
            ]],
            ['Pekerjaan Permesinan', ['job_name' => 'Penggantian zinc anode lambung', 'job_volume_estimated' => 40, 'unit' => 'bh', 'unit_price' => 150000, 'notes' => 'Harga jasa pasang tanpa item kamus harga.'], [], [
                ['material_name' => 'Zinc anode', 'item' => 'MT-ZN-01', 'volume' => 40],
            ]],

            ['Pekerjaan Perpipaan', ['job_name' => 'Penggantian pipa air laut pendingin mesin induk', 'item' => 'JS-PP-01', 'calculation' => ['type' => 'pipe', 'size' => '2 inci SCH 40', 'length_m' => 0.75, 'quantity' => 4]], [], [
                ['material_name' => 'Pipa baja SCH40 2 inci', 'item' => 'MT-PP-01', 'volume' => 4],
            ]],

            ['Pekerjaan Kelistrikan', ['job_name' => 'Megger test motor listrik', 'item' => 'JS-KL-01', 'job_volume_estimated' => 12], [], []],
        ];
    }

    private function build_jobs(ProjectJobDocument $document, ?int $actor_id): void
    {
        $groups = ProjectJobGroup::query()->pluck('unique_id', 'name');

        foreach ($this->job_plan() as [$group_name, $spec, $children, $materials]) {
            $root = $this->boq->save_job($document, null, [...$this->job_data($spec), 'project_job_group_id' => $groups[$group_name]], $actor_id);
            $this->add_materials($document, $root, $materials, $actor_id);

            foreach ($children as [$child_spec, $child_materials]) {
                $child = $this->boq->save_job($document, null, [...$this->job_data($child_spec), 'parent_job_id' => $root->unique_id], $actor_id);
                $this->add_materials($document, $child, $child_materials, $actor_id);
            }
        }
    }

    // Re-saves the copied rows in the price phase so unit prices come from the price book.
    private function price_copied_jobs(ProjectJobDocument $document, ?int $actor_id): void
    {
        $specs = [];
        foreach ($this->job_plan() as [$group_name, $spec, $children, $materials]) {
            $specs[$spec['job_name']] = [$spec, $materials, $group_name];
            foreach ($children as [$child_spec, $child_materials]) {
                $specs[$child_spec['job_name']] = [$child_spec, $child_materials, null];
            }
        }

        $groups = ProjectJobGroup::query()->pluck('unique_id', 'name');
        $jobs = ProjectDocumentJob::query()->where('project_job_document_id', $document->id)->with('materials')->orderBy('id')->get();

        foreach ($jobs as $job) {
            [$spec, $materials, $group_name] = $specs[$job->job_name];
            $data = $this->job_data($spec);
            if ($group_name !== null) {
                $data['project_job_group_id'] = $groups[$group_name];
            }
            $this->boq->save_job($document, $job, $data, $actor_id);

            foreach ($job->materials as $index => $material) {
                $this->boq->save_material($document, $job, $material, $this->material_data($materials[$index]), $actor_id);
            }
        }
    }

    private function add_materials(ProjectJobDocument $document, ProjectDocumentJob $job, array $materials, ?int $actor_id): void
    {
        foreach ($materials as $material) {
            $this->boq->save_material($document, $job, null, $this->material_data($material), $actor_id);
        }
    }

    private function job_data(array $spec): array
    {
        $data = $spec;
        unset($data['item']);
        $data['price_book_item_id'] = isset($spec['item']) ? $this->item_id($spec['item']) : null;

        return $data;
    }

    private function material_data(array $spec): array
    {
        $data = $spec;
        unset($data['item']);
        $data['price_book_item_id'] = isset($spec['item']) ? $this->item_id($spec['item']) : null;

        return $data;
    }

    private function item_id(string $code): string
    {
        return PriceBookItem::query()
            ->whereHas('price_book', fn ($query) => $query->where('reference_slot', 1))
            ->where('code', $code)
            ->value('unique_id');
    }
}
