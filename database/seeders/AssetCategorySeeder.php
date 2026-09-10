<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'CAT-SRV', 'name' => 'Server & Datacenter Rack', 'description' => 'Fasilitas server rak & blade', 'useful_life_months' => 60],
            ['code' => 'CAT-NET', 'name' => 'Perangkat Jaringan (Switch/Router)', 'description' => 'Infrastruktur backbone fiber & switch', 'useful_life_months' => 60],
            ['code' => 'CAT-PC', 'name' => 'PC Workstation & Laptop', 'description' => 'Komputer personal dan laptop dinas', 'useful_life_months' => 48],
            ['code' => 'CAT-SAN', 'name' => 'Perangkat Sandi & Kriptografi', 'description' => 'Aparatur sandi negara & modul enkripsi', 'useful_life_months' => 72],
            ['code' => 'CAT-SEC', 'name' => 'Perangkat Keamanan Jaringan (Firewall)', 'description' => 'Next-gen Firewall, IPS, WAF', 'useful_life_months' => 60],
            ['code' => 'CAT-UPS', 'name' => 'Power & UPS Datacenter', 'description' => 'Uninterruptible Power Supply & Genset', 'useful_life_months' => 60],
            ['code' => 'CAT-PRN', 'name' => 'Printer & Scanner Dokumen', 'description' => 'Periferal cetak dan pemindai', 'useful_life_months' => 36],
        ];

        foreach ($categories as $cat) {
            AssetCategory::create($cat);
        }

        $vendors = [
            ['name' => 'PT. Lintas Jaringan Nusantara', 'contact_person' => 'Budi Santoso', 'phone' => '081234567890', 'email' => 'sales@lintas.co.id', 'address' => 'Bandung'],
            ['name' => 'PT. Mega Komputindo Mandiri', 'contact_person' => 'Eka Prasetya', 'phone' => '081987654321', 'email' => 'support@megakomputindo.com', 'address' => 'Jakarta'],
            ['name' => 'CV. Galuh Solusi Informatika', 'contact_person' => 'Asep Kurnia', 'phone' => '085223344556', 'email' => 'info@galuhsolusi.co.id', 'address' => 'Ciamis'],
        ];

        foreach ($vendors as $vendor) {
            Vendor::create($vendor);
        }
    }
}
