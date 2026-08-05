<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\DockingOccupancy;
use App\Models\DockingSpace;
use App\Models\Project;
use App\Models\ProjectDockingRequest;
use App\Models\Ship;
use App\Models\ShipClass;
use App\Models\ShipType;
use App\Models\User;
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

            $ships->push(Ship::create([
                'name' => $ship['name'],
                'company_id' => $company_id,
                'ship_type_id' => $ship_type_by_name[$ship['ship_type_name']] ?? null,
                'ship_class_id' => $ship_class_by_abbreviation[$ship['ship_class_abbreviation']] ?? null,
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
            ['name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'address' => 'Jl. Gajah Mada No. 14, Jakarta Pusat, DKI Jakarta, Indonesia', 'phone_1' => '+62-21-6334342', 'phone_2' => null, 'email' => 'info@pelni.co.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Divisi Operasi Armada', 'pic_phone' => null, 'pic_email' => 'humas@pelni.co.id', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik operator kapal penumpang nasional.'],
            ['name' => 'PT ASDP Indonesia Ferry (Persero)', 'address' => 'Jl. Jenderal Ahmad Yani Kav. 52A, Cempaka Putih, Jakarta Pusat, Indonesia', 'phone_1' => '+62-21-4208911', 'phone_2' => null, 'email' => 'corporate.secretary@asdp.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Divisi Operasi Ferry', 'pic_phone' => null, 'pic_email' => 'contactcenter@asdp.id', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik operator penyeberangan nasional.'],
            ['name' => 'PT Samudera Indonesia Tbk', 'address' => 'Samudera Indonesia Building, Jl. Letjen S. Parman Kav. 35, Jakarta Barat, Indonesia', 'phone_1' => '+62-21-2939-8800', 'phone_2' => null, 'email' => 'corsec@samudera.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Commercial Department', 'pic_phone' => null, 'pic_email' => 'marketing@samudera.id', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik emiten maritim Indonesia.'],
            ['name' => 'PT Meratus Line', 'address' => 'Jl. Aloon-Aloon Priok No. 27, Surabaya, Jawa Timur, Indonesia', 'phone_1' => '+62-31-3292266', 'phone_2' => null, 'email' => 'customer.service@meratusline.com', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Customer Service', 'pic_phone' => null, 'pic_email' => 'customer.service@meratusline.com', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik operator pelayaran peti kemas domestik.'],
            ['name' => 'PT Pelayaran Tempuran Emas Tbk', 'address' => 'Jl. Yos Sudarso Kav. 33, Jakarta Utara, DKI Jakarta, Indonesia', 'phone_1' => '+62-21-430-2057', 'phone_2' => null, 'email' => 'corsec@temasline.com', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Corporate Secretary', 'pic_phone' => null, 'pic_email' => 'corsec@temasline.com', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik emiten pelayaran peti kemas.'],
            ['name' => 'PT Pelita Samudera Shipping Tbk', 'address' => 'Menara Kadin Indonesia Lt. 26, Jl. H.R. Rasuna Said X-5, Jakarta Selatan, Indonesia', 'phone_1' => '+62-21-5296-0118', 'phone_2' => null, 'email' => 'corsec@pelitasamudera.co.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Commercial Team', 'pic_phone' => null, 'pic_email' => 'info@pelitasamudera.co.id', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik operator logistik batubara dan bulk.'],
            ['name' => 'PT Wintermar Offshore Marine Tbk', 'address' => 'Jl. Kebayoran Lama No. 155, Jakarta Selatan, DKI Jakarta, Indonesia', 'phone_1' => '+62-21-530-5201', 'phone_2' => null, 'email' => 'corpsec@wintermar.com', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Offshore Operations', 'pic_phone' => null, 'pic_email' => 'info@wintermar.com', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik operator offshore support vessel.'],
            ['name' => 'PT Buana Lintas Lautan Tbk', 'address' => 'Graha BIP Lt. 10, Jl. Gatot Subroto Kav. 23, Jakarta Selatan, Indonesia', 'phone_1' => '+62-21-250-3310', 'phone_2' => null, 'email' => 'corsec@bull.co.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Tanker Commercial', 'pic_phone' => null, 'pic_email' => 'info@bull.co.id', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik operator tanker domestik dan regional.'],
            ['name' => 'PT Soechi Lines Tbk', 'address' => 'Sudirman Plaza, Indofood Tower Lt. 9, Jakarta Selatan, Indonesia', 'phone_1' => '+62-21-8063-7800', 'phone_2' => null, 'email' => 'corsec@soechiline.com', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Commercial Tanker', 'pic_phone' => null, 'pic_email' => 'info@soechiline.com', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik operator tanker dan galangan.'],
            ['name' => 'PT Berlian Laju Tanker Tbk', 'address' => 'Wisma BSG Lt. 10, Jl. Abdul Muis No. 40, Jakarta Pusat, Indonesia', 'phone_1' => '+62-21-2358-6000', 'phone_2' => null, 'email' => 'corpsecretary@blt.co.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Corporate Secretary', 'pic_phone' => null, 'pic_email' => 'corpsecretary@blt.co.id', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik operator tanker Indonesia.'],
            ['name' => 'PT Salam Pacific Indonesia Lines', 'address' => 'SPIL Tower, Jl. Rajawali No. 10, Surabaya, Jawa Timur, Indonesia', 'phone_1' => '+62-31-329-2299', 'phone_2' => null, 'email' => 'customer.care@spil.co.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Customer Care', 'pic_phone' => null, 'pic_email' => 'customer.care@spil.co.id', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik operator kontainer domestik.'],
            ['name' => 'PT Tanto Intim Line', 'address' => 'Jl. Kalianget No. 12, Surabaya, Jawa Timur, Indonesia', 'phone_1' => '+62-31-329-1888', 'phone_2' => null, 'email' => 'customer.service@tantointimline.com', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Customer Service', 'pic_phone' => null, 'pic_email' => 'customer.service@tantointimline.com', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik operator pelayaran peti kemas.'],
            ['name' => 'PT Pelayaran Bahtera Adhiguna', 'address' => 'Jl. R.P. Soeroso No. 2-4, Jakarta Pusat, Indonesia', 'phone_1' => '+62-21-3190-3535', 'phone_2' => null, 'email' => 'info@bahtera-adhiguna.co.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Divisi Operasi', 'pic_phone' => null, 'pic_email' => 'info@bahtera-adhiguna.co.id', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik operator logistik energi.'],
            ['name' => 'PT Djakarta Lloyd (Persero)', 'address' => 'Gedung Djakarta Lloyd, Jl. R.P. Soeroso No. 2, Jakarta Pusat, Indonesia', 'phone_1' => '+62-21-390-2454', 'phone_2' => null, 'email' => 'humas@djakartalloyd.co.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Humas', 'pic_phone' => null, 'pic_email' => 'humas@djakartalloyd.co.id', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik operator pelayaran BUMN.'],
            ['name' => 'PT Dharma Lautan Utama', 'address' => 'Jl. Kanginan No. 3-5, Surabaya, Jawa Timur, Indonesia', 'phone_1' => '+62-31-329-1133', 'phone_2' => null, 'email' => 'cs@dlu.co.id', 'ceo_name' => null, 'ceo_phone' => null, 'ceo_email' => null, 'pic_name' => 'Layanan Pelanggan', 'pic_phone' => null, 'pic_email' => 'cs@dlu.co.id', 'registration_number' => null, 'tax_id' => null, 'comment' => 'Profil publik operator penyeberangan antarpulau.'],
        ];
    }

    private function ship_seed_data(): array
    {
        return [
            ['name' => 'KM Kelud', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14502', 'net_tonnage' => '6765', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133905', 'mmsi_number' => '525001001', 'call_sign' => 'YDBK2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Umsini', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '145.00', 'breadth' => '23.40', 'height' => '13.80', 'empty_draft' => '4.70', 'loaded_draft' => '5.80', 'gross_tonnage' => '14234', 'net_tonnage' => '6600', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15000', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '640000', 'engine_fuel_consumption' => '1180', 'imo_number' => '9133890', 'mmsi_number' => '525001002', 'call_sign' => 'YDBN2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Lawit', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '123.40', 'breadth' => '22.00', 'height' => '12.20', 'empty_draft' => '4.20', 'loaded_draft' => '5.10', 'gross_tonnage' => '10698', 'net_tonnage' => '4800', 'engine_brand' => 'MAN B&W', 'engine_model' => '6L58/64', 'engine_power' => '12000', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '520000', 'engine_fuel_consumption' => '980', 'imo_number' => '9133876', 'mmsi_number' => '525001003', 'call_sign' => 'YDBP2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Bukit Raya', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '123.40', 'breadth' => '22.00', 'height' => '12.20', 'empty_draft' => '4.20', 'loaded_draft' => '5.10', 'gross_tonnage' => '10698', 'net_tonnage' => '4800', 'engine_brand' => 'MAN B&W', 'engine_model' => '6L58/64', 'engine_power' => '12000', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '520000', 'engine_fuel_consumption' => '980', 'imo_number' => '9133864', 'mmsi_number' => '525001004', 'call_sign' => 'YDBQ2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Leuser', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '123.40', 'breadth' => '22.00', 'height' => '12.20', 'empty_draft' => '4.20', 'loaded_draft' => '5.10', 'gross_tonnage' => '10698', 'net_tonnage' => '4800', 'engine_brand' => 'MAN B&W', 'engine_model' => '6L58/64', 'engine_power' => '12000', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '520000', 'engine_fuel_consumption' => '980', 'imo_number' => '9133852', 'mmsi_number' => '525001005', 'call_sign' => 'YDBR2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Ciremai', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14501', 'net_tonnage' => '6764', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133840', 'mmsi_number' => '525001006', 'call_sign' => 'YDBS2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Dobonsolo', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14502', 'net_tonnage' => '6765', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133838', 'mmsi_number' => '525001007', 'call_sign' => 'YDBT2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Lambelu', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '145.00', 'breadth' => '23.40', 'height' => '13.80', 'empty_draft' => '4.70', 'loaded_draft' => '5.80', 'gross_tonnage' => '14234', 'net_tonnage' => '6600', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15000', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '640000', 'engine_fuel_consumption' => '1180', 'imo_number' => '9133826', 'mmsi_number' => '525001008', 'call_sign' => 'YDBU2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Sinabung', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14501', 'net_tonnage' => '6764', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133814', 'mmsi_number' => '525001009', 'call_sign' => 'YDBV2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Dorolonda', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14501', 'net_tonnage' => '6764', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133802', 'mmsi_number' => '525001010', 'call_sign' => 'YDBW2', 'build_year' => '1998-01-01'],
            ['name' => 'KM Gunung Dempo', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14502', 'net_tonnage' => '6765', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133797', 'mmsi_number' => '525001011', 'call_sign' => 'YDBX2', 'build_year' => '2008-01-01'],
            ['name' => 'KM Nggapulu', 'company_name' => 'PT Pelayaran Nasional Indonesia (Persero)', 'ship_type_name' => 'KM (Kapal Motor)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.50', 'breadth' => '23.40', 'height' => '14.00', 'empty_draft' => '4.80', 'loaded_draft' => '5.90', 'gross_tonnage' => '14502', 'net_tonnage' => '6765', 'engine_brand' => 'MAN B&W', 'engine_model' => '8L58/64', 'engine_power' => '15280', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '428', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9133785', 'mmsi_number' => '525001012', 'call_sign' => 'YDBY2', 'build_year' => '2008-01-01'],
            ['name' => 'KMP Portlink III', 'company_name' => 'PT ASDP Indonesia Ferry (Persero)', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '109.10', 'breadth' => '18.80', 'height' => '10.20', 'empty_draft' => '3.20', 'loaded_draft' => '4.20', 'gross_tonnage' => '8798', 'net_tonnage' => '2650', 'engine_brand' => 'Wartsila', 'engine_model' => '8L26', 'engine_power' => '8160', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '750', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '180000', 'engine_fuel_consumption' => '760', 'imo_number' => '9462647', 'mmsi_number' => '525001013', 'call_sign' => 'YBPL3', 'build_year' => '2010-01-01'],
            ['name' => 'KMP Portlink V', 'company_name' => 'PT ASDP Indonesia Ferry (Persero)', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '109.00', 'breadth' => '18.80', 'height' => '10.20', 'empty_draft' => '3.20', 'loaded_draft' => '4.20', 'gross_tonnage' => '8800', 'net_tonnage' => '2650', 'engine_brand' => 'Wartsila', 'engine_model' => '8L26', 'engine_power' => '8160', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '750', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '180000', 'engine_fuel_consumption' => '760', 'imo_number' => '9462659', 'mmsi_number' => '525001014', 'call_sign' => 'YBPL5', 'build_year' => '2011-01-01'],
            ['name' => 'KMP Jatra II', 'company_name' => 'PT ASDP Indonesia Ferry (Persero)', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '126.00', 'breadth' => '22.00', 'height' => '12.00', 'empty_draft' => '3.80', 'loaded_draft' => '4.80', 'gross_tonnage' => '13288', 'net_tonnage' => '4200', 'engine_brand' => 'MAN', 'engine_model' => '12V48/60', 'engine_power' => '18900', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '500', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '260000', 'engine_fuel_consumption' => '1180', 'imo_number' => '9708570', 'mmsi_number' => '525001015', 'call_sign' => 'YBJT2', 'build_year' => '2015-01-01'],
            ['name' => 'KMP Jatra III', 'company_name' => 'PT ASDP Indonesia Ferry (Persero)', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '126.00', 'breadth' => '22.00', 'height' => '12.00', 'empty_draft' => '3.80', 'loaded_draft' => '4.80', 'gross_tonnage' => '13288', 'net_tonnage' => '4200', 'engine_brand' => 'MAN', 'engine_model' => '12V48/60', 'engine_power' => '18900', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '500', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '260000', 'engine_fuel_consumption' => '1180', 'imo_number' => '9708582', 'mmsi_number' => '525001016', 'call_sign' => 'YBJT3', 'build_year' => '2016-01-01'],
            ['name' => 'MV Meratus Jayakarta', 'company_name' => 'PT Meratus Line', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '148.00', 'breadth' => '22.80', 'height' => '14.40', 'empty_draft' => '6.20', 'loaded_draft' => '7.40', 'gross_tonnage' => '16925', 'net_tonnage' => '7940', 'engine_brand' => 'MAN B&W', 'engine_model' => '7S50MC', 'engine_power' => '10850', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '127', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '720000', 'engine_fuel_consumption' => '1450', 'imo_number' => '9510018', 'mmsi_number' => '525001017', 'call_sign' => 'YBMJ1', 'build_year' => '2009-01-01'],
            ['name' => 'MV Meratus Medan 1', 'company_name' => 'PT Meratus Line', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '172.00', 'breadth' => '27.50', 'height' => '16.30', 'empty_draft' => '7.20', 'loaded_draft' => '8.60', 'gross_tonnage' => '22000', 'net_tonnage' => '9800', 'engine_brand' => 'MAN B&W', 'engine_model' => '8S60MC', 'engine_power' => '15500', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '105', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '880000', 'engine_fuel_consumption' => '1680', 'imo_number' => '9645005', 'mmsi_number' => '525001018', 'call_sign' => 'YBMM1', 'build_year' => '2013-01-01'],
            ['name' => 'MV Meratus Kupang', 'company_name' => 'PT Meratus Line', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '142.00', 'breadth' => '22.00', 'height' => '13.80', 'empty_draft' => '5.80', 'loaded_draft' => '7.10', 'gross_tonnage' => '14500', 'net_tonnage' => '6700', 'engine_brand' => 'Hyundai', 'engine_model' => '7S50MC-C', 'engine_power' => '10300', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '127', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '650000', 'engine_fuel_consumption' => '1320', 'imo_number' => '9536012', 'mmsi_number' => '525001019', 'call_sign' => 'YBMK1', 'build_year' => '2010-01-01'],
            ['name' => 'MV SPIL Nirmala', 'company_name' => 'PT Salam Pacific Indonesia Lines', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '153.00', 'breadth' => '25.00', 'height' => '15.00', 'empty_draft' => '6.40', 'loaded_draft' => '7.80', 'gross_tonnage' => '18200', 'net_tonnage' => '8100', 'engine_brand' => 'MAN B&W', 'engine_model' => '8S50MC', 'engine_power' => '12300', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '118', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '780000', 'engine_fuel_consumption' => '1520', 'imo_number' => '9550016', 'mmsi_number' => '525001020', 'call_sign' => 'YBSN1', 'build_year' => '2011-01-01'],
            ['name' => 'MV SPIL Citra', 'company_name' => 'PT Salam Pacific Indonesia Lines', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.00', 'breadth' => '23.00', 'height' => '14.20', 'empty_draft' => '6.00', 'loaded_draft' => '7.40', 'gross_tonnage' => '16000', 'net_tonnage' => '7350', 'engine_brand' => 'Sulzer', 'engine_model' => '7RTA52U', 'engine_power' => '11100', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '124', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '720000', 'engine_fuel_consumption' => '1400', 'imo_number' => '9562015', 'mmsi_number' => '525001021', 'call_sign' => 'YBSC1', 'build_year' => '2012-01-01'],
            ['name' => 'MV Tanto Ceria', 'company_name' => 'PT Tanto Intim Line', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '138.00', 'breadth' => '22.60', 'height' => '13.90', 'empty_draft' => '5.60', 'loaded_draft' => '6.90', 'gross_tonnage' => '13200', 'net_tonnage' => '6100', 'engine_brand' => 'MAN B&W', 'engine_model' => '6S50MC', 'engine_power' => '8900', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '127', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '590000', 'engine_fuel_consumption' => '1180', 'imo_number' => '9574014', 'mmsi_number' => '525001022', 'call_sign' => 'YBTC1', 'build_year' => '2013-01-01'],
            ['name' => 'MV Tanto Bersinar', 'company_name' => 'PT Tanto Intim Line', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '134.00', 'breadth' => '21.80', 'height' => '13.50', 'empty_draft' => '5.40', 'loaded_draft' => '6.70', 'gross_tonnage' => '12400', 'net_tonnage' => '5700', 'engine_brand' => 'Hyundai', 'engine_model' => '6S46MC-C', 'engine_power' => '8350', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '129', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '560000', 'engine_fuel_consumption' => '1120', 'imo_number' => '9586013', 'mmsi_number' => '525001023', 'call_sign' => 'YBTB1', 'build_year' => '2014-01-01'],
            ['name' => 'MV Tanto Sejahtera', 'company_name' => 'PT Tanto Intim Line', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '146.00', 'breadth' => '23.00', 'height' => '14.20', 'empty_draft' => '6.00', 'loaded_draft' => '7.30', 'gross_tonnage' => '15600', 'net_tonnage' => '7100', 'engine_brand' => 'MAN B&W', 'engine_model' => '7S50MC', 'engine_power' => '10400', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '124', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '690000', 'engine_fuel_consumption' => '1360', 'imo_number' => '9598012', 'mmsi_number' => '525001024', 'call_sign' => 'YBTS1', 'build_year' => '2015-01-01'],
            ['name' => 'MV Temas Samudra', 'company_name' => 'PT Pelayaran Tempuran Emas Tbk', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '164.00', 'breadth' => '25.20', 'height' => '15.40', 'empty_draft' => '6.90', 'loaded_draft' => '8.10', 'gross_tonnage' => '19800', 'net_tonnage' => '9200', 'engine_brand' => 'MAN B&W', 'engine_model' => '8S50MC', 'engine_power' => '12800', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '118', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '810000', 'engine_fuel_consumption' => '1590', 'imo_number' => '9600011', 'mmsi_number' => '525001025', 'call_sign' => 'YBTS2', 'build_year' => '2016-01-01'],
            ['name' => 'MV Temas Ocean', 'company_name' => 'PT Pelayaran Tempuran Emas Tbk', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '152.00', 'breadth' => '23.50', 'height' => '14.70', 'empty_draft' => '6.30', 'loaded_draft' => '7.60', 'gross_tonnage' => '17400', 'net_tonnage' => '8100', 'engine_brand' => 'Hyundai', 'engine_model' => '7S50MC-C', 'engine_power' => '11150', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '124', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '750000', 'engine_fuel_consumption' => '1460', 'imo_number' => '9602010', 'mmsi_number' => '525001026', 'call_sign' => 'YBTO1', 'build_year' => '2017-01-01'],
            ['name' => 'MV Samudera Sentosa', 'company_name' => 'PT Samudera Indonesia Tbk', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '182.00', 'breadth' => '28.20', 'height' => '17.20', 'empty_draft' => '8.00', 'loaded_draft' => '9.30', 'gross_tonnage' => '28500', 'net_tonnage' => '12500', 'engine_brand' => 'MAN B&W', 'engine_model' => '8S60MC-C', 'engine_power' => '17800', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '99', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '980000', 'engine_fuel_consumption' => '1900', 'imo_number' => '9604009', 'mmsi_number' => '525001027', 'call_sign' => 'YBSS1', 'build_year' => '2018-01-01'],
            ['name' => 'MV Samudera Nusantara', 'company_name' => 'PT Samudera Indonesia Tbk', 'ship_type_name' => 'Container', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '172.00', 'breadth' => '27.10', 'height' => '16.50', 'empty_draft' => '7.40', 'loaded_draft' => '8.80', 'gross_tonnage' => '23800', 'net_tonnage' => '10800', 'engine_brand' => 'Wartsila', 'engine_model' => '7RT-flex58T', 'engine_power' => '16200', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '103', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '910000', 'engine_fuel_consumption' => '1740', 'imo_number' => '9606007', 'mmsi_number' => '525001028', 'call_sign' => 'YBSN2', 'build_year' => '2019-01-01'],
            ['name' => 'MT BULL Papua', 'company_name' => 'PT Buana Lintas Lautan Tbk', 'ship_type_name' => 'Tanker', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '183.00', 'breadth' => '32.20', 'height' => '19.10', 'empty_draft' => '9.10', 'loaded_draft' => '11.80', 'gross_tonnage' => '30100', 'net_tonnage' => '17000', 'engine_brand' => 'MAN B&W', 'engine_model' => '6S60MC-C', 'engine_power' => '14200', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '105', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '1050000', 'engine_fuel_consumption' => '1960', 'imo_number' => '9608005', 'mmsi_number' => '525001029', 'call_sign' => 'YBBP1', 'build_year' => '2012-01-01'],
            ['name' => 'MT BULL Sulawesi', 'company_name' => 'PT Buana Lintas Lautan Tbk', 'ship_type_name' => 'Tanker', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '176.00', 'breadth' => '30.10', 'height' => '18.20', 'empty_draft' => '8.70', 'loaded_draft' => '11.20', 'gross_tonnage' => '26800', 'net_tonnage' => '14800', 'engine_brand' => 'Hyundai', 'engine_model' => '6S50MC-C', 'engine_power' => '12300', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '118', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '920000', 'engine_fuel_consumption' => '1780', 'imo_number' => '9610004', 'mmsi_number' => '525001030', 'call_sign' => 'YBBS1', 'build_year' => '2013-01-01'],
            ['name' => 'MT Soechi Chemical 1', 'company_name' => 'PT Soechi Lines Tbk', 'ship_type_name' => 'Tanker', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '169.00', 'breadth' => '27.40', 'height' => '16.50', 'empty_draft' => '8.00', 'loaded_draft' => '10.30', 'gross_tonnage' => '22400', 'net_tonnage' => '12100', 'engine_brand' => 'MAN B&W', 'engine_model' => '6S50MC', 'engine_power' => '11200', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '124', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '840000', 'engine_fuel_consumption' => '1650', 'imo_number' => '9612002', 'mmsi_number' => '525001031', 'call_sign' => 'YBSC2', 'build_year' => '2011-01-01'],
            ['name' => 'MT Soechi Gas 1', 'company_name' => 'PT Soechi Lines Tbk', 'ship_type_name' => 'Tanker', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '156.00', 'breadth' => '25.00', 'height' => '15.40', 'empty_draft' => '7.30', 'loaded_draft' => '9.20', 'gross_tonnage' => '18500', 'net_tonnage' => '9800', 'engine_brand' => 'Wartsila', 'engine_model' => '6RT-flex50', 'engine_power' => '9800', 'engine_type' => 'Diesel 2-Stroke', 'engine_rpm' => '127', 'engine_fuel_type' => 'IFO 180', 'engine_fuel_capacity' => '760000', 'engine_fuel_consumption' => '1490', 'imo_number' => '9614000', 'mmsi_number' => '525001032', 'call_sign' => 'YBSG1', 'build_year' => '2014-01-01'],
            ['name' => 'AHTS Wintermar Noble', 'company_name' => 'PT Wintermar Offshore Marine Tbk', 'ship_type_name' => 'AHTS (Anchor Handling Tug Service)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '63.20', 'breadth' => '15.80', 'height' => '7.80', 'empty_draft' => '4.10', 'loaded_draft' => '5.10', 'gross_tonnage' => '1850', 'net_tonnage' => '620', 'engine_brand' => 'Caterpillar', 'engine_model' => '3516C', 'engine_power' => '5200', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '1600', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '210000', 'engine_fuel_consumption' => '580', 'imo_number' => '9616008', 'mmsi_number' => '525001033', 'call_sign' => 'YBWN1', 'build_year' => '2015-01-01'],
            ['name' => 'AHTS Wintermar Supporter', 'company_name' => 'PT Wintermar Offshore Marine Tbk', 'ship_type_name' => 'AHTS (Anchor Handling Tug Service)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '58.40', 'breadth' => '14.60', 'height' => '7.30', 'empty_draft' => '3.90', 'loaded_draft' => '4.80', 'gross_tonnage' => '1650', 'net_tonnage' => '560', 'engine_brand' => 'Caterpillar', 'engine_model' => '3512C', 'engine_power' => '4300', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '1600', 'engine_fuel_type' => 'MDO', 'engine_fuel_capacity' => '185000', 'engine_fuel_consumption' => '520', 'imo_number' => '9618006', 'mmsi_number' => '525001034', 'call_sign' => 'YBWS1', 'build_year' => '2016-01-01'],
            ['name' => 'KM Dharma Kencana VII', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '127.00', 'breadth' => '22.00', 'height' => '12.20', 'empty_draft' => '3.90', 'loaded_draft' => '4.90', 'gross_tonnage' => '13700', 'net_tonnage' => '4300', 'engine_brand' => 'MAN', 'engine_model' => '12V48/60', 'engine_power' => '19000', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '500', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '280000', 'engine_fuel_consumption' => '1200', 'imo_number' => '9620005', 'mmsi_number' => '525001035', 'call_sign' => 'YBDK7', 'build_year' => '2017-01-01'],
            ['name' => 'KM Dharma Kencana VIII', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '128.20', 'breadth' => '22.10', 'height' => '12.30', 'empty_draft' => '3.95', 'loaded_draft' => '4.95', 'gross_tonnage' => '13820', 'net_tonnage' => '4350', 'engine_brand' => 'MAN', 'engine_model' => '12V48/60', 'engine_power' => '19200', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '500', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '282000', 'engine_fuel_consumption' => '1210', 'imo_number' => '9620006', 'mmsi_number' => '525001036', 'call_sign' => 'YBDK8', 'build_year' => '2018-01-01'],
            ['name' => 'KM Dharma Kencana IX', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '129.00', 'breadth' => '22.30', 'height' => '12.40', 'empty_draft' => '4.00', 'loaded_draft' => '5.00', 'gross_tonnage' => '13940', 'net_tonnage' => '4400', 'engine_brand' => 'MAN', 'engine_model' => '12V48/60B', 'engine_power' => '19400', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '500', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '284000', 'engine_fuel_consumption' => '1220', 'imo_number' => '9620007', 'mmsi_number' => '525001037', 'call_sign' => 'YBDK9', 'build_year' => '2019-01-01'],
            ['name' => 'KM Dharma Kencana X', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '130.10', 'breadth' => '22.40', 'height' => '12.50', 'empty_draft' => '4.05', 'loaded_draft' => '5.05', 'gross_tonnage' => '14100', 'net_tonnage' => '4460', 'engine_brand' => 'Wartsila', 'engine_model' => '12V32', 'engine_power' => '19800', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '720', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '286000', 'engine_fuel_consumption' => '1230', 'imo_number' => '9620008', 'mmsi_number' => '525001038', 'call_sign' => 'YBDKX', 'build_year' => '2020-01-01'],
            ['name' => 'KM Dharma Ferry I', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'Ferry/Ro-Ro', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '112.40', 'breadth' => '20.00', 'height' => '11.00', 'empty_draft' => '3.40', 'loaded_draft' => '4.35', 'gross_tonnage' => '9800', 'net_tonnage' => '3120', 'engine_brand' => 'Caterpillar', 'engine_model' => '8M43C', 'engine_power' => '12600', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '514', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '220000', 'engine_fuel_consumption' => '860', 'imo_number' => '9620009', 'mmsi_number' => '525001039', 'call_sign' => 'YBDF1', 'build_year' => '2012-01-01'],
            ['name' => 'KM Dharma Ferry II', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'Ferry/Ro-Ro', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '113.00', 'breadth' => '20.10', 'height' => '11.10', 'empty_draft' => '3.45', 'loaded_draft' => '4.40', 'gross_tonnage' => '9950', 'net_tonnage' => '3180', 'engine_brand' => 'Caterpillar', 'engine_model' => '8M43C', 'engine_power' => '12800', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '514', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '222000', 'engine_fuel_consumption' => '870', 'imo_number' => '9620010', 'mmsi_number' => '525001040', 'call_sign' => 'YBDF2', 'build_year' => '2013-01-01'],
            ['name' => 'KM Dharma Ferry III', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'Ferry/Ro-Ro', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '114.20', 'breadth' => '20.20', 'height' => '11.20', 'empty_draft' => '3.50', 'loaded_draft' => '4.45', 'gross_tonnage' => '10100', 'net_tonnage' => '3230', 'engine_brand' => 'MAN', 'engine_model' => '9L32/40', 'engine_power' => '13000', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '750', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '224000', 'engine_fuel_consumption' => '880', 'imo_number' => '9620011', 'mmsi_number' => '525001041', 'call_sign' => 'YBDF3', 'build_year' => '2014-01-01'],
            ['name' => 'KM Dharma Ferry IV', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'Ferry/Ro-Ro', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '115.00', 'breadth' => '20.30', 'height' => '11.20', 'empty_draft' => '3.55', 'loaded_draft' => '4.50', 'gross_tonnage' => '10280', 'net_tonnage' => '3300', 'engine_brand' => 'MAN', 'engine_model' => '9L32/40', 'engine_power' => '13200', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '750', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '226000', 'engine_fuel_consumption' => '890', 'imo_number' => '9620012', 'mmsi_number' => '525001042', 'call_sign' => 'YBDF4', 'build_year' => '2015-01-01'],
            ['name' => 'KM Dharma Ferry V', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'Ferry/Ro-Ro', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '116.20', 'breadth' => '20.40', 'height' => '11.30', 'empty_draft' => '3.60', 'loaded_draft' => '4.55', 'gross_tonnage' => '10420', 'net_tonnage' => '3360', 'engine_brand' => 'Wartsila', 'engine_model' => '8L31', 'engine_power' => '13400', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '720', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '228000', 'engine_fuel_consumption' => '900', 'imo_number' => '9620013', 'mmsi_number' => '525001043', 'call_sign' => 'YBDF5', 'build_year' => '2016-01-01'],
            ['name' => 'KM Dharma Rucitra 1', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '118.80', 'breadth' => '20.80', 'height' => '11.60', 'empty_draft' => '3.70', 'loaded_draft' => '4.70', 'gross_tonnage' => '11200', 'net_tonnage' => '3550', 'engine_brand' => 'MAN', 'engine_model' => '10V48/60', 'engine_power' => '14800', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '500', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '236000', 'engine_fuel_consumption' => '980', 'imo_number' => '9620014', 'mmsi_number' => '525001044', 'call_sign' => 'YBDR1', 'build_year' => '2014-01-01'],
            ['name' => 'KM Dharma Rucitra 2', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '119.40', 'breadth' => '20.90', 'height' => '11.70', 'empty_draft' => '3.75', 'loaded_draft' => '4.75', 'gross_tonnage' => '11320', 'net_tonnage' => '3600', 'engine_brand' => 'MAN', 'engine_model' => '10V48/60', 'engine_power' => '15000', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '500', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '238000', 'engine_fuel_consumption' => '990', 'imo_number' => '9620015', 'mmsi_number' => '525001045', 'call_sign' => 'YBDR2', 'build_year' => '2015-01-01'],
            ['name' => 'KM Dharma Rucitra 3', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '120.00', 'breadth' => '21.00', 'height' => '11.80', 'empty_draft' => '3.80', 'loaded_draft' => '4.80', 'gross_tonnage' => '11480', 'net_tonnage' => '3660', 'engine_brand' => 'Wartsila', 'engine_model' => '10V31', 'engine_power' => '15200', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '720', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '240000', 'engine_fuel_consumption' => '1000', 'imo_number' => '9620016', 'mmsi_number' => '525001046', 'call_sign' => 'YBDR3', 'build_year' => '2016-01-01'],
            ['name' => 'KM Dharma Rucitra 4', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '121.20', 'breadth' => '21.20', 'height' => '11.90', 'empty_draft' => '3.85', 'loaded_draft' => '4.85', 'gross_tonnage' => '11640', 'net_tonnage' => '3720', 'engine_brand' => 'Wartsila', 'engine_model' => '10V31', 'engine_power' => '15400', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '720', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '242000', 'engine_fuel_consumption' => '1010', 'imo_number' => '9620017', 'mmsi_number' => '525001047', 'call_sign' => 'YBDR4', 'build_year' => '2017-01-01'],
            ['name' => 'KM Dharma Rucitra 5', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '122.00', 'breadth' => '21.30', 'height' => '12.00', 'empty_draft' => '3.90', 'loaded_draft' => '4.90', 'gross_tonnage' => '11810', 'net_tonnage' => '3780', 'engine_brand' => 'MAN', 'engine_model' => '12V48/60', 'engine_power' => '15600', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '500', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '244000', 'engine_fuel_consumption' => '1020', 'imo_number' => '9620018', 'mmsi_number' => '525001048', 'call_sign' => 'YBDR5', 'build_year' => '2018-01-01'],
            ['name' => 'KM Dharma Kartika 1', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '110.00', 'breadth' => '19.60', 'height' => '10.80', 'empty_draft' => '3.30', 'loaded_draft' => '4.20', 'gross_tonnage' => '9200', 'net_tonnage' => '2940', 'engine_brand' => 'Caterpillar', 'engine_model' => '9M32C', 'engine_power' => '11800', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '600', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '210000', 'engine_fuel_consumption' => '820', 'imo_number' => '9620019', 'mmsi_number' => '525001049', 'call_sign' => 'YBDT1', 'build_year' => '2011-01-01'],
            ['name' => 'KM Dharma Kartika 2', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '111.20', 'breadth' => '19.70', 'height' => '10.90', 'empty_draft' => '3.35', 'loaded_draft' => '4.25', 'gross_tonnage' => '9350', 'net_tonnage' => '3000', 'engine_brand' => 'Caterpillar', 'engine_model' => '9M32C', 'engine_power' => '12000', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '600', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '212000', 'engine_fuel_consumption' => '830', 'imo_number' => '9620020', 'mmsi_number' => '525001050', 'call_sign' => 'YBDT2', 'build_year' => '2012-01-01'],
            ['name' => 'KM Dharma Kartika 3', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '112.40', 'breadth' => '19.90', 'height' => '11.00', 'empty_draft' => '3.40', 'loaded_draft' => '4.30', 'gross_tonnage' => '9500', 'net_tonnage' => '3060', 'engine_brand' => 'MAN', 'engine_model' => '8L32/40', 'engine_power' => '12200', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '750', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '214000', 'engine_fuel_consumption' => '840', 'imo_number' => '9620021', 'mmsi_number' => '525001051', 'call_sign' => 'YBDT3', 'build_year' => '2013-01-01'],
            ['name' => 'KM Dharma Kartika 4', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '113.60', 'breadth' => '20.00', 'height' => '11.10', 'empty_draft' => '3.45', 'loaded_draft' => '4.35', 'gross_tonnage' => '9680', 'net_tonnage' => '3120', 'engine_brand' => 'MAN', 'engine_model' => '8L32/40', 'engine_power' => '12400', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '750', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '216000', 'engine_fuel_consumption' => '850', 'imo_number' => '9620022', 'mmsi_number' => '525001052', 'call_sign' => 'YBDT4', 'build_year' => '2014-01-01'],
            ['name' => 'KM Dharma Kartika 5', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'KMP (Kapal Motor Penyeberangan)', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '114.80', 'breadth' => '20.10', 'height' => '11.20', 'empty_draft' => '3.50', 'loaded_draft' => '4.40', 'gross_tonnage' => '9840', 'net_tonnage' => '3180', 'engine_brand' => 'Wartsila', 'engine_model' => '8L26', 'engine_power' => '12600', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '750', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '218000', 'engine_fuel_consumption' => '860', 'imo_number' => '9620023', 'mmsi_number' => '525001053', 'call_sign' => 'YBDT5', 'build_year' => '2015-01-01'],
            ['name' => 'KM Dharma Bahari 1', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'Ferry/Ro-Ro', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '116.00', 'breadth' => '20.40', 'height' => '11.40', 'empty_draft' => '3.60', 'loaded_draft' => '4.50', 'gross_tonnage' => '10320', 'net_tonnage' => '3290', 'engine_brand' => 'MAN', 'engine_model' => '10V32/40', 'engine_power' => '13000', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '750', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '230000', 'engine_fuel_consumption' => '900', 'imo_number' => '9620024', 'mmsi_number' => '525001054', 'call_sign' => 'YBDB1', 'build_year' => '2016-01-01'],
            ['name' => 'KM Dharma Bahari 2', 'company_name' => 'PT Dharma Lautan Utama', 'ship_type_name' => 'Ferry/Ro-Ro', 'ship_class_abbreviation' => 'BKI', 'length_overall' => '117.20', 'breadth' => '20.50', 'height' => '11.50', 'empty_draft' => '3.65', 'loaded_draft' => '4.55', 'gross_tonnage' => '10490', 'net_tonnage' => '3350', 'engine_brand' => 'MAN', 'engine_model' => '10V32/40', 'engine_power' => '13200', 'engine_type' => 'Diesel 4-Stroke', 'engine_rpm' => '750', 'engine_fuel_type' => 'HSD', 'engine_fuel_capacity' => '232000', 'engine_fuel_consumption' => '910', 'imo_number' => '9620025', 'mmsi_number' => '525001055', 'call_sign' => 'YBDB2', 'build_year' => '2017-01-01'],
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

                $project = Project::create([
                    'unique_id' => (string) Str::uuid(),
                    'project_code' => sprintf('PRJ-DMY-%s-%03d', now()->format('Ym'), $index + 1),
                    'ship_id' => $ship->id,
                    'project_type' => 'Docking & Repair',
                    'start_date_estimation' => $start_at->toDateString(),
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
}
