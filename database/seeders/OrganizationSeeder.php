<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $opds = [
            [
                'code' => 'DISKOMINFO',
                'name' => 'Dinas Komunikasi dan Informatika',
                'category' => 'dinas',
                'address' => 'Jl. Jend. Sudirman No. 190, Ciamis',
                'phone' => '(0265) 771123',
                'email' => 'diskominfo@ciamis.go.id',
                'website_url' => 'https://diskominfo.ciamiskab.go.id',
                'head_name' => 'H. Tino Armyanto, ST., M.Si.',
                'head_nip' => '197508122002121004',
                'departments' => [
                    'Bidang Persandian dan Keamanan Informasi',
                    'Bidang Pengelolaan Informasi Komunikasi Publik (IKP)',
                    'Bidang Penyelenggaraan E-Government',
                    'Sekretariat Diskominfo',
                ],
                'locations' => [
                    ['name' => 'Data Center Diskominfo Lt. 2', 'room' => 'Server Room Utama'],
                    ['name' => 'Ruang NOC & CSIRT', 'room' => 'NOC Room'],
                    ['name' => 'Bidang Sandi & Siber', 'room' => 'Ruang Kerja Sandi'],
                ],
            ],
            [
                'code' => 'DINKES',
                'name' => 'Dinas Kesehatan',
                'category' => 'dinas',
                'address' => 'Jl. Mr. Iwa Kusumasumantri No. 12, Ciamis',
                'phone' => '(0265) 771034',
                'email' => 'dinkes@ciamis.go.id',
                'website_url' => 'https://dinkes.ciamiskab.go.id',
                'head_name' => 'dr. H. Yoyo, M.M.Kes.',
                'head_nip' => '196903151999031005',
                'departments' => [
                    'Bidang Pelayanan Kesehatan',
                    'Bidang Pencegahan Penyakit',
                    'Sekretariat Dinkes',
                ],
                'locations' => [
                    ['name' => 'Server SIMPUS Dinkes', 'room' => 'Ruang IT Dinkes'],
                ],
            ],
            [
                'code' => 'DISDIK',
                'name' => 'Dinas Pendidikan',
                'category' => 'dinas',
                'address' => 'Jl. Perintis Kemerdekaan No. 27, Ciamis',
                'phone' => '(0265) 771045',
                'email' => 'disdik@ciamis.go.id',
                'website_url' => 'https://disdik.ciamiskab.go.id',
                'head_name' => 'Dr. Erwan Darmawan, S.STP., M.Si.',
                'head_nip' => '197605141995111001',
                'departments' => ['Bidang Pembinaan SD', 'Bidang Pembinaan SMP', 'Sekretariat'],
                'locations' => [
                    ['name' => 'Ruang Server Dapodik Disdik', 'room' => 'Ruang Data'],
                ],
            ],
            [
                'code' => 'DPMPTSP',
                'name' => 'Dinas Penanaman Modal dan PTSP',
                'category' => 'dinas',
                'address' => 'Jl. R.A.A. Sastrawinata No. 1, Ciamis',
                'phone' => '(0265) 771567',
                'email' => 'dpmptsp@ciamis.go.id',
                'website_url' => 'https://dpmptsp.ciamiskab.go.id',
                'head_name' => 'Drs. Rudi, S.E.',
                'head_nip' => '197109201992011002',
                'departments' => ['Bidang Pelayanan Perizinan', 'Bidang Pengendalian'],
                'locations' => [
                    ['name' => 'Ruang Server Pelayanan Perizinan', 'room' => 'Ruang IT'],
                ],
            ],
            [
                'code' => 'BAPPEDA',
                'name' => 'Badan Perencanaan Pembangunan Daerah',
                'category' => 'badan',
                'address' => 'Jl. Jend. Ahmad Yani No. 165, Ciamis',
                'phone' => '(0265) 771345',
                'email' => 'bappeda@ciamis.go.id',
                'website_url' => 'https://bappeda.ciamiskab.go.id',
                'head_name' => 'David Firdauzi, S.T., M.Si.',
                'head_nip' => '197401121998031003',
                'departments' => ['Bidang Perencanaan Makro', 'Bidang Litbang'],
                'locations' => [
                    ['name' => 'Server SIPD & E-Planning', 'room' => 'Ruang Server Bappeda'],
                ],
            ],
            [
                'code' => 'RSUD-CMS',
                'name' => 'RSUD Kabupaten Ciamis',
                'category' => 'rsud',
                'address' => 'Jl. Rumah Sakit No. 30, Ciamis',
                'phone' => '(0265) 771118',
                'email' => 'rsud@ciamis.go.id',
                'website_url' => 'https://rsud.ciamiskab.go.id',
                'head_name' => 'dr. H. Rizali Sofiyan, M.M.',
                'head_nip' => '197304192003121003',
                'departments' => ['Instalasi SIMRS & TI', 'Bagian Tata Usaha'],
                'locations' => [
                    ['name' => 'Server Room SIMRS', 'room' => 'Server SIMRS Lt. 1'],
                ],
            ],
            [
                'code' => 'SETDA',
                'name' => 'Sekretariat Daerah',
                'category' => 'bagian_setda',
                'address' => 'Jl. Jend. Sudirman No. 16, Ciamis',
                'phone' => '(0265) 771001',
                'email' => 'setda@ciamis.go.id',
                'website_url' => 'https://setda.ciamiskab.go.id',
                'head_name' => 'Dr. H. Andang Firman T., S.T., M.T.',
                'head_nip' => '197008151996031004',
                'departments' => ['Bagian Organisasi', 'Bagian Hukum', 'Bagian Umum'],
                'locations' => [
                    ['name' => 'Gedung Bupati & Setda', 'room' => 'Ruang Telekomunikasi'],
                ],
            ],
            [
                'code' => 'KEC-CIAMIS',
                'name' => 'Kecamatan Ciamis',
                'category' => 'kecamatan',
                'address' => 'Jl. Letnan Harun No. 2, Ciamis',
                'phone' => '(0265) 771234',
                'email' => 'kec.ciamis@ciamis.go.id',
                'website_url' => 'https://kec-ciamis.ciamiskab.go.id',
                'head_name' => 'Camat Ciamis',
                'head_nip' => '197702022005011008',
                'departments' => ['Seksi Pelayanan Umum', 'Seksi Tata Pemerintahan'],
                'locations' => [
                    ['name' => 'Kantor Kecamatan Ciamis', 'room' => 'Ruang PATEN'],
                ],
            ],
            [
                'code' => 'KEC-KAWALI',
                'name' => 'Kecamatan Kawali',
                'category' => 'kecamatan',
                'address' => 'Jl. Siliwangi No. 14, Kawali',
                'phone' => '(0265) 791002',
                'email' => 'kec.kawali@ciamis.go.id',
                'website_url' => 'https://kec-kawali.ciamiskab.go.id',
                'head_name' => 'Camat Kawali',
                'head_nip' => '197906102006041012',
                'departments' => ['Seksi Pelayanan Umum', 'Seksi Pemerintahan'],
                'locations' => [
                    ['name' => 'Kantor Kecamatan Kawali', 'room' => 'Ruang Administrasi'],
                ],
            ],
        ];

        foreach ($opds as $data) {
            $depts = $data['departments'] ?? [];
            $locs = $data['locations'] ?? [];
            unset($data['departments'], $data['locations']);

            $org = Organization::create($data);

            foreach ($depts as $deptName) {
                Department::create([
                    'organization_id' => $org->id,
                    'name' => $deptName,
                ]);
            }

            foreach ($locs as $loc) {
                Location::create([
                    'organization_id' => $org->id,
                    'name' => $loc['name'],
                    'room' => $loc['room'] ?? null,
                    'address' => $org->address,
                ]);
            }
        }
    }
}
