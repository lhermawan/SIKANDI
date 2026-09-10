<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $diskominfo = Organization::where('code', 'DISKOMINFO')->first();
        $dinkes = Organization::where('code', 'DINKES')->first();
        $sandiDept = Department::where('name', 'like', '%Persandian%')->first();

        // 1. Super Admin
        $superAdmin = User::create([
            'organization_id' => $diskominfo?->id,
            'name' => 'Super Administrator SIKANDI',
            'username' => 'superadmin',
            'email' => 'superadmin@ciamis.go.id',
            'phone' => '081122334455',
            'nip' => '198501012010011001',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $superAdmin->assignRole('Super Admin');

        // 2. Admin Persandian
        $adminSandi = User::create([
            'organization_id' => $diskominfo?->id,
            'department_id' => $sandiDept?->id,
            'name' => 'Admin Sandi & Siber Ciamis',
            'username' => 'admin.sandi',
            'email' => 'admin.sandi@ciamis.go.id',
            'phone' => '081234567891',
            'nip' => '198802142011011002',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $adminSandi->assignRole('Admin Persandian');

        // 3. IT Technician
        $technician = User::create([
            'organization_id' => $diskominfo?->id,
            'name' => 'Teknisi NOC & Infrastruktur',
            'username' => 'teknisi',
            'email' => 'teknisi@ciamis.go.id',
            'phone' => '081345678912',
            'nip' => '199205202015031004',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $technician->assignRole('IT Technician');

        // 4. OPD User (Dinas Kesehatan)
        $opdUser = User::create([
            'organization_id' => $dinkes?->id,
            'name' => 'Operator TIK Dinkes Ciamis',
            'username' => 'opd.dinkes',
            'email' => 'opd.dinkes@ciamis.go.id',
            'phone' => '081456789123',
            'nip' => '199011122014022003',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $opdUser->assignRole('OPD User');

        // 5. Management (Pimpinan)
        $management = User::create([
            'organization_id' => $diskominfo?->id,
            'name' => 'Pimpinan Diskominfo Ciamis',
            'username' => 'pimpinan',
            'email' => 'pimpinan@ciamis.go.id',
            'phone' => '081567891234',
            'nip' => '197508122002121004',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $management->assignRole('Management');
    }
}
