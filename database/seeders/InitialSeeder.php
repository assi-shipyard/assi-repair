<?php

namespace Database\Seeders;

use App\Models\ShipClass;
use App\Models\ShipType;
use Illuminate\Database\Seeder;

class InitialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shipTypes = [
            'Tugboat',
            'Cargo',
            'Ferry/Ro-Ro',
            'Perintis',
            'Barge',
            'KLM (Kapal Layar Motor)',
            'KM (Kapal Motor)',
            'KMP (Kapal Motor Penyeberangan)',
            'Yacht',
            'Submarine',
            'Kapal Perang',
            'Container',
            'Tanker',
            'SPOB (Self Propelled Oil Barge)',
            'SPHB (Self Propelled Hopper Barge)',
            'AHTS (Anchor Handling Tug Service)',
            'Crew Boat',
            'CSD (Cutter Suction Dredger)',
        ];

        $shipClasses = [
            ['name' => 'Biro Klasifikasi Indonesia', 'abbreviation' => 'BKI'],
            ['name' => 'Lloyd’s Register', 'abbreviation' => 'LR'],
            ['name' => 'Nippon Kaiji Kyokai', 'abbreviation' => 'ClassNK'],
            ['name' => 'American Bureau of Shipping', 'abbreviation' => 'ABS'],
            ['name' => 'Det Norske Veritas', 'abbreviation' => 'DNV'],
            ['name' => 'Bureau Veritas', 'abbreviation' => 'BV'],
            ['name' => 'Russian Maritime Register of Shipping', 'abbreviation' => 'RS'],
            ['name' => 'Korean Register of Shipping', 'abbreviation' => 'KR'],
            ['name' => 'China Classification Society', 'abbreviation' => 'CCS'],
            ['name' => 'Indian Register of Shipping', 'abbreviation' => 'IRS'],
            ['name' => 'Registro Italiano Navale', 'abbreviation' => 'RINA'],
            ['name' => 'Polski Rejestr Statków', 'abbreviation' => 'PRS'],
            ['name' => 'Türk Loydu', 'abbreviation' => 'TL'],
            ['name' => 'Vietnam Register', 'abbreviation' => 'VR'],
        ];

        foreach ($shipTypes as $type) {
            ShipType::updateOrCreate(['name' => $type], ['name' => $type]);
        }

        foreach ($shipClasses as $classification) {
            ShipClass::updateOrCreate(
                ['name' => $classification['name']],
                ['abbreviation' => $classification['abbreviation']]
            );
        }
    }
}
