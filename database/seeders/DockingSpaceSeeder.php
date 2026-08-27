<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DockingSpace;
use Illuminate\Database\Seeder;

class DockingSpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->docking_spaces() as $space) {
            DockingSpace::updateOrCreate(
                ['name' => $space['name']],
                [
                    'max_draft' => $space['max_draft'],
                    'max_tonnage' => $space['max_tonnage'],
                    'max_breadth' => $space['max_breadth'],
                    'max_capacity' => $space['max_capacity'],
                ]
            );
        }
    }

    private function docking_spaces(): array
    {
        return [
            ['name' => 'Floating Dock', 'max_draft' => '5.5', 'max_tonnage' => '3000', 'max_breadth' => '25', 'max_capacity' => '2'],
            ['name' => 'Slipway', 'max_draft' => '3.5', 'max_tonnage' => '1000', 'max_breadth' => '13', 'max_capacity' => '1'],
            ['name' => 'Launching Way 1', 'max_draft' => '4.0', 'max_tonnage' => '1400', 'max_breadth' => '15', 'max_capacity' => '1'],
            ['name' => 'Launching Way 2', 'max_draft' => '4.5', 'max_tonnage' => '2800', 'max_breadth' => '25', 'max_capacity' => '2'],
            ['name' => 'Launching Way 3 - Line A', 'max_draft' => '4.0', 'max_tonnage' => '1600', 'max_breadth' => '25', 'max_capacity' => '1'],
            ['name' => 'Launching Way 3 - Line B', 'max_draft' => '4.0', 'max_tonnage' => '1600', 'max_breadth' => '25', 'max_capacity' => '1'],
            ['name' => 'Launching Way 3 - Line C', 'max_draft' => '4.0', 'max_tonnage' => '1600', 'max_breadth' => '25', 'max_capacity' => '1'],
            ['name' => 'Launching Way 3 - Line D', 'max_draft' => '4.0', 'max_tonnage' => '1600', 'max_breadth' => '25', 'max_capacity' => '1'],
        ];
    }
}
