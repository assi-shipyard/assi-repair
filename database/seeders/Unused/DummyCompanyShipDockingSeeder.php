<?php

declare(strict_types=1);

namespace Database\Seeders\Unused;

use App\Models\Company;
use App\Models\DockingOccupancy;
use App\Models\DockingSpace;
use App\Models\Project;
use App\Models\ProjectDockingRequest;
use App\Models\Ship;
use App\Models\ShipClass;
use App\Models\ShipType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DummyCompanyShipDockingSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->clear_existing_data();

            $companies = $this->seed_companies();
            $ships = $this->seed_ships($companies);

            $this->seed_docking_schedules($ships);
        });
    }

    private function clear_existing_data(): void
    {
        DB::table('floating_repair_histories')->delete();
        DB::table('docking_capacity_evaluations')->delete();
        DB::table('docking_occupancies')->delete();
        DB::table('project_docking_requests')->delete();

        DB::table('project_job_document_histories')->delete();
        DB::table('project_document_job_photos')->delete();
        DB::table('project_document_job_materials')->delete();
        DB::table('project_document_jobs')->delete();
        DB::table('project_job_documents')->delete();

        DB::table('project_access')->delete();
        DB::table('project_documents')->delete();
        DB::table('project_owner_surveyors')->delete();
        DB::table('project_divisions')->delete();
        DB::table('projects')->delete();

        DB::table('ship_documents')->delete();
        DB::table('ship_images')->delete();
        DB::table('ships')->delete();

        DB::table('company_documents')->delete();
        DB::table('companies')->delete();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Company>
     */
    private function seed_companies()
    {
        $companies = collect();

        foreach ($this->company_seed_data() as $company) {
            $companies->push(Company::create([
                'unique_id' => (string) Str::uuid(),
                'name' => $company['name'],
                'address' => $company['address'],
                'phone_1' => $company['phone_1'],
                'phone_2' => $company['phone_2'],
                'email' => $company['email'],
                'ceo_name' => $company['ceo_name'],
                'ceo_phone' => $company['ceo_phone'],
                'ceo_email' => $company['ceo_email'],
                'pic_name' => $company['pic_name'],
                'pic_phone' => $company['pic_phone'],
                'pic_email' => $company['pic_email'],
                'registration_number' => $company['registration_number'],
                'tax_id' => $company['tax_id'],
                'comment' => $company['comment'],
            ]));
        }

        return $companies;
    }

    /**
     * @param \Illuminate\Support\Collection<int, Company> $companies
     * @return \Illuminate\Support\Collection<int, Ship>
     */
    private function seed_ships($companies)
    {
        $company_by_name = $companies->keyBy('name');
        $ship_type_by_name = ShipType::query()->pluck('id', 'name')->all();
        $ship_class_by_abbreviation = ShipClass::query()->pluck('id', 'abbreviation')->all();

        $ships = collect();

        foreach ($this->ship_seed_data() as $ship) {
            $company_id = $company_by_name[$ship['company_name']]?->id;

            if ($company_id === null) {
                continue;
            }

            $ship_type_id = $ship_type_by_name[$ship['ship_type_name']] ?? null;
            $ship_class_id = $ship_class_by_abbreviation[$ship['ship_class_abbreviation']] ?? null;

            if ($ship_type_id === null || $ship_class_id === null) {
                throw new \RuntimeException(
                    sprintf(
                        'Master data kapal tidak lengkap untuk %s. Jalankan InitialSeeder sebelum DummyCompanyShipDockingSeeder.',
                        $ship['name']
                    )
                );
            }

            $ships->push(Ship::create([
                'name' => $ship['name'],
                'company_id' => $company_id,
                'ship_type_id' => $ship_type_id,
                'ship_class_id' => $ship_class_id,
                'length_overall' => $ship['length_overall'],
                'breadth' => $ship['breadth'],
                'height' => $ship['height'],
                'empty_draft' => $ship['empty_draft'],
                'loaded_draft' => $ship['loaded_draft'],
                'gross_tonnage' => $ship['gross_tonnage'],
                'net_tonnage' => $ship['net_tonnage'],
                'engine_brand' => $ship['engine_brand'],
                'engine_model' => $ship['engine_model'],
                'engine_power' => $ship['engine_power'],
                'engine_type' => $ship['engine_type'],
                'engine_rpm' => $ship['engine_rpm'],
                'engine_fuel_type' => $ship['engine_fuel_type'],
                'engine_fuel_capacity' => $ship['engine_fuel_capacity'],
                'engine_fuel_consumption' => $ship['engine_fuel_consumption'],
                'imo_number' => $ship['imo_number'],
                'mmsi_number' => $ship['mmsi_number'],
                'call_sign' => $ship['call_sign'],
                'flag' => 'Indonesia',
                'build_year' => $ship['build_year'],
            ]));
        }

        return $ships;
    }

    private function company_seed_data(): array
    {
        return [
            ['name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'address' => 'Jl. Gajah Mada No. 14, Jakarta Pusat, DKI Jakarta, Indonesia', 'phone_1' => '+62-21-6334342', 'phone_2' => '+62-21-63854130', 'email' => 'humas@pelni.co.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Divisi Operasi Armada', 'pic_phone' => '+62-21-6334342', 'pic_email' => 'humas@pelni.co.id', 'registration_number' => 'BUMN-PERSERO-PELNI', 'tax_id' => null, 'comment' => 'Data profil perusahaan pelayaran penumpang nasional (sumber publik perusahaan).'],
            ['name' => 'PT ASDP Indonesia Ferry (Persero)', 'address' => 'Jl. Jenderal Ahmad Yani Kav. 52A, Cempaka Putih, Jakarta Pusat, DKI Jakarta, Indonesia', 'phone_1' => '+62-21-4208911', 'phone_2' => '+62-21-42801231', 'email' => 'contactcenter@asdp.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Divisi Operasi Penyeberangan', 'pic_phone' => '+62-21-4208911', 'pic_email' => 'corporate.secretary@asdp.id', 'registration_number' => 'BUMN-PERSERO-ASDP', 'tax_id' => null, 'comment' => 'Data profil operator penyeberangan nasional (sumber publik perusahaan).'],
            ['name' => 'PT Dharma Lautan Utama', 'address' => 'Jl. Kanginan No. 3-5, Surabaya, Jawa Timur, Indonesia', 'phone_1' => '+62-31-3291133', 'phone_2' => '+62-31-3291144', 'email' => 'cs@dlu.co.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Layanan Pelanggan', 'pic_phone' => '+62-31-3291133', 'pic_email' => 'cs@dlu.co.id', 'registration_number' => 'DOK-OPR-DLU', 'tax_id' => null, 'comment' => 'Data profil operator kapal penumpang dan kendaraan antarpulau.'],
            ['name' => 'PT Meratus Line', 'address' => 'Jl. Aloon-Aloon Priok No. 27, Surabaya, Jawa Timur, Indonesia', 'phone_1' => '+62-31-3292266', 'phone_2' => '+62-31-3292277', 'email' => 'customer.service@meratusline.com', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Customer Service', 'pic_phone' => '+62-31-3292266', 'pic_email' => 'customer.service@meratusline.com', 'registration_number' => 'DOK-OPR-MERATUS', 'tax_id' => null, 'comment' => 'Data profil operator kontainer domestik.'],
            ['name' => 'PT Salam Pacific Indonesia Lines', 'address' => 'SPIL Tower, Jl. Rajawali No. 10, Surabaya, Jawa Timur, Indonesia', 'phone_1' => '+62-31-3292299', 'phone_2' => '+62-31-3292200', 'email' => 'customer.care@spil.co.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Customer Care', 'pic_phone' => '+62-31-3292299', 'pic_email' => 'customer.care@spil.co.id', 'registration_number' => 'DOK-OPR-SPIL', 'tax_id' => null, 'comment' => 'Data profil operator kontainer nasional.'],
            ['name' => 'PT Samudera Indonesia Tbk', 'address' => 'Samudera Indonesia Building, Jl. Letjen S. Parman Kav. 35, Jakarta Barat, DKI Jakarta, Indonesia', 'phone_1' => '+62-21-29398800', 'phone_2' => '+62-21-29398801', 'email' => 'corsec@samudera.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Corporate Secretary', 'pic_phone' => '+62-21-29398800', 'pic_email' => 'corsec@samudera.id', 'registration_number' => 'IDX-SMDR', 'tax_id' => null, 'comment' => 'Data profil emiten pelayaran dan logistik maritim.'],
            ['name' => 'PT Pelayaran Tempuran Emas Tbk', 'address' => 'Jl. Yos Sudarso Kav. 33, Jakarta Utara, DKI Jakarta, Indonesia', 'phone_1' => '+62-21-4302057', 'phone_2' => '+62-21-4302046', 'email' => 'corsec@temasline.com', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Corporate Secretary', 'pic_phone' => '+62-21-4302057', 'pic_email' => 'corsec@temasline.com', 'registration_number' => 'IDX-TMAS', 'tax_id' => null, 'comment' => 'Data profil emiten operator kapal kontainer.'],
            ['name' => 'PT Buana Lintas Lautan Tbk', 'address' => 'Graha BIP Lt. 10, Jl. Gatot Subroto Kav. 23, Jakarta Selatan, DKI Jakarta, Indonesia', 'phone_1' => '+62-21-2503310', 'phone_2' => '+62-21-2503311', 'email' => 'corsec@bull.co.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Tanker Commercial', 'pic_phone' => '+62-21-2503310', 'pic_email' => 'info@bull.co.id', 'registration_number' => 'IDX-BULL', 'tax_id' => null, 'comment' => 'Data profil operator kapal tanker domestik dan regional.'],
            ['name' => 'PT Soechi Lines Tbk', 'address' => 'Sudirman Plaza, Indofood Tower Lt. 9, Jakarta Selatan, DKI Jakarta, Indonesia', 'phone_1' => '+62-21-80637800', 'phone_2' => '+62-21-80637801', 'email' => 'corsec@soechiline.com', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Commercial Tanker', 'pic_phone' => '+62-21-80637800', 'pic_email' => 'info@soechiline.com', 'registration_number' => 'IDX-SOCI', 'tax_id' => null, 'comment' => 'Data profil operator tanker dan galangan kapal.'],
            ['name' => 'PT Wintermar Offshore Marine Tbk', 'address' => 'Jl. Kebayoran Lama No. 155, Jakarta Selatan, DKI Jakarta, Indonesia', 'phone_1' => '+62-21-5305201', 'phone_2' => '+62-21-5305202', 'email' => 'corpsec@wintermar.com', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Offshore Operations', 'pic_phone' => '+62-21-5305201', 'pic_email' => 'info@wintermar.com', 'registration_number' => 'IDX-WINS', 'tax_id' => null, 'comment' => 'Data profil operator kapal penunjang offshore.'],
        ];
    }

    private function ship_seed_data(): array
    {
        return [
            ['name' => 'KM Kelud', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14502', 'net_tonnage' => '6765', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133905', 'mmsi_number' => null, 'call_sign' => 'YDBK2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Umsini', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '145.00', 'breadth' => '23.40', 'height' => '13.80', 'empty_draft' => '4.70', 'loaded_draft' => '5.80', 'gross_tonnage' => '14234', 'net_tonnage' => '6600', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15000', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '640000', 'engine_fuel_consumption' => '1180', 'imo_number' => '9133890', 'mmsi_number' => null, 'call_sign' => 'YDBN2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Ciremai', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14501', 'net_tonnage' => '6764', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133840', 'mmsi_number' => null, 'call_sign' => 'YDBS2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Dobonsolo', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14502', 'net_tonnage' => '6765', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133838', 'mmsi_number' => null, 'call_sign' => 'YDBT2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Lambelu', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '145.00', 'breadth' => '23.40', 'height' => '13.80', 'empty_draft' => '4.70', 'loaded_draft' => '5.80', 'gross_tonnage' => '14234', 'net_tonnage' => '6600', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15000', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '640000', 'engine_fuel_consumption' => '1180', 'imo_number' => '9133826', 'mmsi_number' => null, 'call_sign' => 'YDBU2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Sinabung', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14501', 'net_tonnage' => '6764', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133814', 'mmsi_number' => null, 'call_sign' => 'YDBV2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Dorolonda', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14501', 'net_tonnage' => '6764', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133802', 'mmsi_number' => null, 'call_sign' => 'YDBW2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Gunung Dempo', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14502', 'net_tonnage' => '6765', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133797', 'mmsi_number' => null, 'call_sign' => 'YDBX2', 'build_year' => '2008-01-01'],
            ['name' => 'KM Nggapulu', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14502', 'net_tonnage' => '6765', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133785', 'mmsi_number' => null, 'call_sign' => 'YDBY2', 'build_year' => '2008-01-01'],

            ['name' => 'KMP Portlink III', 'company_name' => 'PT ASDP Indonesia Ferry (Persero)', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '109.10', 'breadth' => '18.80', 'height' => '10.20', 'empty_draft' => '3.20', 'loaded_draft' => '4.20', 'gross_tonnage' => '8798', 'net_tonnage' => '2650', 'engine_brand' => 'Wartsila', 'engine_model' => '8L26', 'engine_power' => '8160', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '750', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '180000', 'engine_fuel_consumption' => '760', 'imo_number' => '9462647', 'mmsi_number' => null, 'call_sign' => 'YBPL3', 'build_year' => '2010-01-01'],
            ['name' => 'KMP Portlink V', 'company_name' => 'PT ASDP Indonesia Ferry (Persero)', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '109.00', 'breadth' => '18.80', 'height' => '10.20', 'empty_draft' => '3.20', 'loaded_draft' => '4.20', 'gross_tonnage' => '8800', 'net_tonnage' => '2650', 'engine_brand' => 'Wartsila', 'engine_model' => '8L26', 'engine_power' => '8160', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '750', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '180000', 'engine_fuel_consumption' => '760', 'imo_number' => '9462659', 'mmsi_number' => null, 'call_sign' => 'YBPL5', 'build_year' => '2011-01-01'],
            ['name' => 'KMP Jatra II', 'company_name' => 'PT ASDP Indonesia Ferry (Persero)', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '126.00', 'breadth' => '22.00', 'height' => '12.00', 'empty_draft' => '3.80', 'loaded_draft' => '4.80', 'gross_tonnage' => '13288', 'net_tonnage' => '4200', 'engine_brand' => 'MAN', 'engine_model' => '12V48/60', 'engine_power' => '18900', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '500', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '260000', 'engine_fuel_consumption' => '1180', 'imo_number' => '9708570', 'mmsi_number' => null, 'call_sign' => 'YBJT2', 'build_year' => '2015-01-01'],
            ['name' => 'KMP Jatra III', 'company_name' => 'PT ASDP Indonesia Ferry (Persero)', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '126.00', 'breadth' => '22.00', 'height' => '12.00', 'empty_draft' => '3.80', 'loaded_draft' => '4.80', 'gross_tonnage' => '13288', 'net_tonnage' => '4200', 'engine_brand' => 'MAN', 'engine_model' => '12V48/60', 'engine_power' => '18900', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '500', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '260000', 'engine_fuel_consumption' => '1180', 'imo_number' => '9708582', 'mmsi_number' => null, 'call_sign' => 'YBJT3', 'build_year' => '2016-01-01'],

            ['name' => 'KM Dharma Kencana VII', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '127.00', 'breadth' => '22.00', 'height' => '12.20', 'empty_draft' => '3.90', 'loaded_draft' => '4.90', 'gross_tonnage' => '13700', 'net_tonnage' => '4300', 'engine_brand' => 'MAN', 'engine_model' => '12V48/60', 'engine_power' => '19000', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '500', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '280000', 'engine_fuel_consumption' => '1200', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2017-01-01'],
            ['name' => 'KM Dharma Kencana VIII', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '128.20', 'breadth' => '22.10', 'height' => '12.30', 'empty_draft' => '3.95', 'loaded_draft' => '4.95', 'gross_tonnage' => '13820', 'net_tonnage' => '4350', 'engine_brand' => 'MAN', 'engine_model' => '12V48/60', 'engine_power' => '19200', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '500', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '282000', 'engine_fuel_consumption' => '1210', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2018-01-01'],
            ['name' => 'KM Dharma Kencana IX', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '129.00', 'breadth' => '22.30', 'height' => '12.40', 'empty_draft' => '4.00', 'loaded_draft' => '5.00', 'gross_tonnage' => '13940', 'net_tonnage' => '4400', 'engine_brand' => 'MAN', 'engine_model' => '12V48/60B', 'engine_power' => '19400', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '500', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '284000', 'engine_fuel_consumption' => '1220', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2019-01-01'],
            ['name' => 'KM Dharma Kencana X', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '130.10', 'breadth' => '22.40', 'height' => '12.50', 'empty_draft' => '4.05', 'loaded_draft' => '5.05', 'gross_tonnage' => '14100', 'net_tonnage' => '4460', 'engine_brand' => 'Wartsila', 'engine_model' => '12V32', 'engine_power' => '19800', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '720', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '286000', 'engine_fuel_consumption' => '1230', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2020-01-01'],

            ['name' => 'MV Meratus Jayakarta', 'company_name' => 'PT Meratus Line', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '148.00', 'breadth' => '22.80', 'height' => '14.40', 'empty_draft' => '6.20', 'loaded_draft' => '7.40', 'gross_tonnage' => '16925', 'net_tonnage' => '7940', 'engine_brand' => 'MAN B&W', 'engine_model' => '7S50MC', 'engine_power' => '10850', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '127', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '720000', 'engine_fuel_consumption' => '1450', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2009-01-01'],
            ['name' => 'MV Meratus Medan 1', 'company_name' => 'PT Meratus Line', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '172.00', 'breadth' => '27.50', 'height' => '16.30', 'empty_draft' => '7.20', 'loaded_draft' => '8.60', 'gross_tonnage' => '22000', 'net_tonnage' => '9800', 'engine_brand' => 'MAN B&W', 'engine_model' => '8S60MC', 'engine_power' => '15500', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '105', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '880000', 'engine_fuel_consumption' => '1680', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2013-01-01'],
            ['name' => 'MV Meratus Kupang', 'company_name' => 'PT Meratus Line', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '142.00', 'breadth' => '22.00', 'height' => '13.80', 'empty_draft' => '5.80', 'loaded_draft' => '7.10', 'gross_tonnage' => '14500', 'net_tonnage' => '6700', 'engine_brand' => 'Hyundai', 'engine_model' => '7S50MC-C', 'engine_power' => '10300', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '127', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1320', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2010-01-01'],

            ['name' => 'MV SPIL Nirmala', 'company_name' => 'PT Salam Pacific Indonesia Lines', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '153.00', 'breadth' => '25.00', 'height' => '15.00', 'empty_draft' => '6.40', 'loaded_draft' => '7.80', 'gross_tonnage' => '18200', 'net_tonnage' => '8100', 'engine_brand' => 'MAN B&W', 'engine_model' => '8S50MC', 'engine_power' => '12300', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '118', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '780000', 'engine_fuel_consumption' => '1520', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2011-01-01'],
            ['name' => 'MV SPIL Citra', 'company_name' => 'PT Salam Pacific Indonesia Lines', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.00', 'breadth' => '23.00', 'height' => '14.20', 'empty_draft' => '6.00', 'loaded_draft' => '7.40', 'gross_tonnage' => '16000', 'net_tonnage' => '7350', 'engine_brand' => 'Sulzer', 'engine_model' => '7RTA52U', 'engine_power' => '11100', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '124', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '720000', 'engine_fuel_consumption' => '1400', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2012-01-01'],

            ['name' => 'MV Samudera Sentosa', 'company_name' => 'PT Samudera Indonesia Tbk', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '182.00', 'breadth' => '28.20', 'height' => '17.20', 'empty_draft' => '8.00', 'loaded_draft' => '9.30', 'gross_tonnage' => '28500', 'net_tonnage' => '12500', 'engine_brand' => 'MAN B&W', 'engine_model' => '8S60MC-C', 'engine_power' => '17800', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '99', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '980000', 'engine_fuel_consumption' => '1900', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2018-01-01'],
            ['name' => 'MV Samudera Nusantara', 'company_name' => 'PT Samudera Indonesia Tbk', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '172.00', 'breadth' => '27.10', 'height' => '16.50', 'empty_draft' => '7.40', 'loaded_draft' => '8.80', 'gross_tonnage' => '23800', 'net_tonnage' => '10800', 'engine_brand' => 'Wartsila', 'engine_model' => '7RT-flex58T', 'engine_power' => '16200', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '103', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '910000', 'engine_fuel_consumption' => '1740', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2019-01-01'],

            ['name' => 'MV Temas Samudra', 'company_name' => 'PT Pelayaran Tempuran Emas Tbk', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '164.00', 'breadth' => '25.20', 'height' => '15.40', 'empty_draft' => '6.90', 'loaded_draft' => '8.10', 'gross_tonnage' => '19800', 'net_tonnage' => '9200', 'engine_brand' => 'MAN B&W', 'engine_model' => '8S50MC', 'engine_power' => '12800', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '118', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '810000', 'engine_fuel_consumption' => '1590', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2016-01-01'],
            ['name' => 'MV Temas Ocean', 'company_name' => 'PT Pelayaran Tempuran Emas Tbk', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '152.00', 'breadth' => '23.50', 'height' => '14.70', 'empty_draft' => '6.30', 'loaded_draft' => '7.60', 'gross_tonnage' => '17400', 'net_tonnage' => '8100', 'engine_brand' => 'Hyundai', 'engine_model' => '7S50MC-C', 'engine_power' => '11150', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '124', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '750000', 'engine_fuel_consumption' => '1460', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2017-01-01'],

            ['name' => 'MT BULL Papua', 'company_name' => 'PT Buana Lintas Lautan Tbk', 'ship_type_name' => 'Tanker', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '183.00', 'breadth' => '32.20', 'height' => '19.10', 'empty_draft' => '9.10', 'loaded_draft' => '11.80', 'gross_tonnage' => '30100', 'net_tonnage' => '17000', 'engine_brand' => 'MAN B&W', 'engine_model' => '6S60MC-C', 'engine_power' => '14200', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '105', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '1050000', 'engine_fuel_consumption' => '1960', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2012-01-01'],
            ['name' => 'MT BULL Sulawesi', 'company_name' => 'PT Buana Lintas Lautan Tbk', 'ship_type_name' => 'Tanker', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '176.00', 'breadth' => '30.10', 'height' => '18.20', 'empty_draft' => '8.70', 'loaded_draft' => '11.20', 'gross_tonnage' => '26800', 'net_tonnage' => '14800', 'engine_brand' => 'Hyundai', 'engine_model' => '6S50MC-C', 'engine_power' => '12300', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '118', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '920000', 'engine_fuel_consumption' => '1780', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2013-01-01'],

            ['name' => 'MT Soechi Chemical 1', 'company_name' => 'PT Soechi Lines Tbk', 'ship_type_name' => 'Tanker', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '169.00', 'breadth' => '27.40', 'height' => '16.50', 'empty_draft' => '8.00', 'loaded_draft' => '10.30', 'gross_tonnage' => '22400', 'net_tonnage' => '12100', 'engine_brand' => 'MAN B&W', 'engine_model' => '6S50MC', 'engine_power' => '11200', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '124', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '840000', 'engine_fuel_consumption' => '1650', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2011-01-01'],
            ['name' => 'MT Soechi Gas 1', 'company_name' => 'PT Soechi Lines Tbk', 'ship_type_name' => 'Tanker', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '156.00', 'breadth' => '25.00', 'height' => '15.40', 'empty_draft' => '7.30', 'loaded_draft' => '9.20', 'gross_tonnage' => '18500', 'net_tonnage' => '9800', 'engine_brand' => 'Wartsila', 'engine_model' => '6RT-flex50', 'engine_power' => '9800', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '127', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '760000', 'engine_fuel_consumption' => '1490', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2014-01-01'],

            ['name' => 'AHTS Wintermar Noble', 'company_name' => 'PT Wintermar Offshore Marine Tbk', 'ship_type_name' => 'AHTS (Anchor Handling Tug Service)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '63.20', 'breadth' => '15.80', 'height' => '7.80', 'empty_draft' => '4.10', 'loaded_draft' => '5.10', 'gross_tonnage' => '1850', 'net_tonnage' => '620', 'engine_brand' => 'Caterpillar', 'engine_model' => '3516C', 'engine_power' => '5200', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '1600', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '210000', 'engine_fuel_consumption' => '580', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2015-01-01'],
            ['name' => 'AHTS Wintermar Supporter', 'company_name' => 'PT Wintermar Offshore Marine Tbk', 'ship_type_name' => 'AHTS (Anchor Handling Tug Service)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '58.40', 'breadth' => '14.60', 'height' => '7.30', 'empty_draft' => '3.90', 'loaded_draft' => '4.80', 'gross_tonnage' => '1650', 'net_tonnage' => '560', 'engine_brand' => 'Caterpillar', 'engine_model' => '3512C', 'engine_power' => '4300', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '1600', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '185000', 'engine_fuel_consumption' => '520', 'imo_number' => null, 'mmsi_number' => null, 'call_sign' => null, 'build_year' => '2016-01-01'],
        ];
    }

    /**
     * @param \Illuminate\Support\Collection<int, Ship> $ships
     */
    private function seed_docking_schedules($ships): void
    {
        $admin_user_id = User::query()->where('employee_id', 'assiadmin')->value('id');
        $schedule_anchor = now()->startOfDay()->addDays(3);

        $selected_ships = $ships->shuffle()->values();

        DockingSpace::query()
            ->orderBy('id')
            ->get()
            ->values()
            ->each(function (DockingSpace $docking_space, int $index) use ($selected_ships, $admin_user_id, $schedule_anchor): void {
                $ship = $selected_ships[$index % $selected_ships->count()];

                $start_at = $schedule_anchor->copy()->addDays($index * 4)->setHour(8);
                $end_at = $start_at->copy()->addDays(random_int(7, 18))->setHour(17);

                $project_date = $start_at->toDateString();
                $project_code = $this->generate_project_code($ship, 'Docking & Repair', $project_date);

                $project = Project::create([
                    'unique_id' => (string) Str::uuid(),
                    'project_code' => $project_code,
                    'ship_id' => $ship->id,
                    'project_type' => 'Docking & Repair',
                    'start_date_estimation' => $project_date,
                    'end_date_estimation' => $end_at->toDateString(),
                    'progress' => 0,
                    'status' => 'Not Started',
                    'comment' => 'Proyek dummy otomatis untuk simulasi jadwal docking.',
                    'created_by' => $admin_user_id,
                ]);

                $docking_request = ProjectDockingRequest::create([
                    'project_id' => $project->id,
                    'ship_id' => $ship->id,
                    'requested_by' => $admin_user_id,
                    'requested_docking_space_id' => $docking_space->id,
                    'requested_start_at' => $start_at,
                    'requested_end_at' => $end_at,
                    'request_notes' => 'Permintaan docking dummy otomatis untuk pengujian alur.',
                    'request_status' => 'approved',
                    'reviewed_by' => $admin_user_id,
                    'reviewed_at' => $start_at->copy()->subDay(),
                    'approved_by' => $admin_user_id,
                    'approved_at' => $start_at->copy()->subDay(),
                ]);

                DockingOccupancy::create([
                    'project_id' => $project->id,
                    'ship_id' => $ship->id,
                    'docking_space_id' => $docking_space->id,
                    'project_docking_request_id' => $docking_request->id,
                    'docked_at' => $start_at,
                    'estimated_undock_at' => $end_at,
                    'occupancy_status' => 'scheduled',
                    'remarks' => 'Jadwal docking dummy otomatis per docking space.',
                    'created_by' => $admin_user_id,
                ]);
            });
    }

    private function generate_project_code(Ship $ship, string $project_type, string $project_date): string
    {
        $project_date_value = Carbon::parse($project_date);
        $project_type_code = $this->project_type_code($project_type);
        $ship_type_code = $this->ship_type_code($ship->type?->name);
        $year_code = $project_date_value->format('y');
        $month_code = chr(96 + (int) $project_date_value->format('n'));
        $last_order_number = Project::query()
            ->where('project_code', 'like', $project_type_code.'.%.'.$year_code.'.%.%')
            ->pluck('project_code')
            ->map(function (string $project_code): int {
                $parts = explode('.', $project_code);

                return isset($parts[1]) && ctype_digit($parts[1]) ? (int) $parts[1] : 0;
            })
            ->max() ?? 0;

        return implode('.', [
            $project_type_code,
            str_pad((string) ($last_order_number + 1), 3, '0', STR_PAD_LEFT),
            $year_code,
            $ship_type_code,
            $month_code,
        ]);
    }

    private function project_type_code(string $project_type): string
    {
        $normalized = strtolower(trim($project_type));

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

    private function ship_type_code(?string $ship_type_name): string
    {
        $normalized = strtolower(trim((string) $ship_type_name));

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
