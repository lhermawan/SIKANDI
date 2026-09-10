<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\CiRelationship;
use App\Models\CiType;
use App\Models\ConfigurationItem;
use App\Models\Incident;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Risk;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Website;
use Illuminate\Database\Seeder;

class SampleCmdbSeeder extends Seeder
{
    public function run(): void
    {
        $diskominfo = Organization::where('code', 'DISKOMINFO')->first();
        $dinkes = Organization::where('code', 'DINKES')->first();
        $dcLocation = Location::where('name', 'like', '%Data Center%')->first();
        $dinkesLocation = Location::where('name', 'like', '%SIMPUS%')->first();
        $vendor1 = Vendor::first();
        $tech = User::where('username', 'teknisi')->first();
        $opdUser = User::where('username', 'opd.dinkes')->first();

        // CI Types
        $typeServer = CiType::where('code', 'server')->first();
        $typeFirewall = CiType::where('code', 'firewall')->first();
        $typeSwitch = CiType::where('code', 'switch')->first();
        $typeDb = CiType::where('code', 'database')->first();
        $typeApp = CiType::where('code', 'application')->first();
        $typeWeb = CiType::where('code', 'website')->first();

        // Asset Categories
        $catSrv = AssetCategory::where('code', 'CAT-SRV')->first();
        $catSec = AssetCategory::where('code', 'CAT-SEC')->first();
        $catNet = AssetCategory::where('code', 'CAT-NET')->first();

        // 1. Assets Physical
        $assetServer01 = Asset::create([
            'asset_number' => 'AST-2026-0001',
            'name' => 'Dell PowerEdge R740 Server Rack',
            'asset_category_id' => $catSrv->id,
            'vendor_id' => $vendor1->id,
            'organization_id' => $diskominfo->id,
            'location_id' => $dcLocation->id,
            'brand' => 'Dell',
            'model' => 'PowerEdge R740',
            'serial_number' => 'DELL-SN-748921',
            'purchase_date' => '2024-03-15',
            'purchase_price' => 145000000.00,
            'warranty_expiry_date' => '2027-03-15',
            'lifecycle_status' => 'in_use',
            'condition' => 'good',
            'notes' => 'Server Virtualisasi Proxmox Host Utama Diskominfo',
        ]);

        $assetFirewall = Asset::create([
            'asset_number' => 'AST-2026-0002',
            'name' => 'FortiGate 200F Security Appliance',
            'asset_category_id' => $catSec->id,
            'vendor_id' => $vendor1->id,
            'organization_id' => $diskominfo->id,
            'location_id' => $dcLocation->id,
            'brand' => 'Fortinet',
            'model' => 'FG-200F',
            'serial_number' => 'FGT-SN-998811',
            'purchase_date' => '2023-08-10',
            'purchase_price' => 180000000.00,
            'warranty_expiry_date' => '2026-08-10',
            'lifecycle_status' => 'in_use',
            'condition' => 'good',
            'notes' => 'Next-Gen Firewall Datacenter Pemkab Ciamis',
        ]);

        $assetSwitch = Asset::create([
            'asset_number' => 'AST-2026-0003',
            'name' => 'Cisco Catalyst 3850 48-Port PoE',
            'asset_category_id' => $catNet->id,
            'vendor_id' => $vendor1->id,
            'organization_id' => $diskominfo->id,
            'location_id' => $dcLocation->id,
            'brand' => 'Cisco',
            'model' => 'WS-C3850-48P',
            'serial_number' => 'CSCO-SN-332145',
            'purchase_date' => '2023-01-20',
            'purchase_price' => 85000000.00,
            'warranty_expiry_date' => '2026-01-20',
            'lifecycle_status' => 'in_use',
            'condition' => 'good',
            'notes' => 'Core Switch Utama Ruang Server',
        ]);

        // 2. CMDB Configuration Items
        // CI 1: Firewall
        $ciFirewall = ConfigurationItem::create([
            'ci_code' => 'CI-SEC-00001',
            'name' => 'Firewall Utama DC Ciamis (FortiGate 200F)',
            'ci_type_id' => $typeFirewall->id,
            'asset_id' => $assetFirewall->id,
            'organization_id' => $diskominfo->id,
            'location_id' => $dcLocation->id,
            'hostname' => 'fw-core.ciamiskab.go.id',
            'ip_address' => '103.147.220.1',
            'operating_system' => 'FortiOS',
            'os_version' => '7.4.3',
            'environment' => 'production',
            'status' => 'active',
            'criticality' => 'critical',
            'owner_person' => 'Bidang Sandi & Siber',
            'responsible_unit' => 'Diskominfo Kabupaten Ciamis',
            'specifications' => ['Throughput' => '27 Gbps', 'Interfaces' => '18x GE RJ45, 4x 10GE SFP+'],
            'description' => 'Perlindungan perimeter jaringan, IPS, Antivirus, dan SSL Inspection',
        ]);

        // CI 2: Switch Core
        $ciSwitch = ConfigurationItem::create([
            'ci_code' => 'CI-NET-00001',
            'name' => 'Core Switch Datacenter (Cisco 3850)',
            'ci_type_id' => $typeSwitch->id,
            'asset_id' => $assetSwitch->id,
            'organization_id' => $diskominfo->id,
            'location_id' => $dcLocation->id,
            'hostname' => 'sw-core-dc.ciamiskab.local',
            'ip_address' => '192.168.10.1',
            'operating_system' => 'Cisco IOS-XE',
            'os_version' => '16.12.5',
            'environment' => 'production',
            'status' => 'active',
            'criticality' => 'critical',
            'owner_person' => 'NOC Diskominfo',
            'responsible_unit' => 'Diskominfo',
            'specifications' => ['Ports' => '48x 1G PoE+, 4x 10G SFP+ Uplink'],
            'description' => 'Switch distribusi utama interkoneksi server dan router gateway',
        ]);

        // CI 3: Physical Server SRV-01
        $ciServer01 = ConfigurationItem::create([
            'ci_code' => 'CI-SRV-00001',
            'name' => 'Server Web Produksi DC 01 (Dell R740)',
            'ci_type_id' => $typeServer->id,
            'asset_id' => $assetServer01->id,
            'organization_id' => $diskominfo->id,
            'location_id' => $dcLocation->id,
            'hostname' => 'srv-web01.ciamiskab.go.id',
            'ip_address' => '192.168.10.20',
            'operating_system' => 'Ubuntu Server LTS',
            'os_version' => '24.04',
            'environment' => 'production',
            'status' => 'active',
            'criticality' => 'critical',
            'owner_person' => 'Tim Infrastruktur E-Gov',
            'responsible_unit' => 'Diskominfo Kabupaten Ciamis',
            'specifications' => ['CPU' => '2x Intel Xeon Gold 5218R (40 Cores)', 'RAM' => '128 GB DDR4 ECC', 'Storage' => '4x 1.92TB SSD RAID 10'],
            'description' => 'Host server aplikasi dan web portal resmi Pemkab Ciamis',
        ]);

        // CI 4: Database Server Galera Cluster
        $ciDatabase = ConfigurationItem::create([
            'ci_code' => 'CI-DAT-00001',
            'name' => 'Cluster Database Utama (MariaDB Galera)',
            'ci_type_id' => $typeDb->id,
            'organization_id' => $diskominfo->id,
            'location_id' => $dcLocation->id,
            'hostname' => 'db-cluster.ciamiskab.local',
            'ip_address' => '192.168.10.30',
            'operating_system' => 'Debian Linux',
            'os_version' => '12',
            'environment' => 'production',
            'status' => 'active',
            'criticality' => 'critical',
            'owner_person' => 'Database Administrator',
            'responsible_unit' => 'Diskominfo',
            'specifications' => ['Engine' => 'MariaDB 11.4 Galera Cluster 3-Node', 'Max Connections' => 2000],
            'description' => 'Penyimpan basis data terdistribusi dengan replikasi sinkron',
        ]);

        // CI 5: Website Resmi Pemkab Ciamis
        $ciWebPortal = ConfigurationItem::create([
            'ci_code' => 'CI-WEB-00001',
            'name' => 'Portal Resmi Pemerintah Kabupaten Ciamis',
            'ci_type_id' => $typeWeb->id,
            'organization_id' => $diskominfo->id,
            'url' => 'https://ciamiskab.go.id',
            'domain' => 'ciamiskab.go.id',
            'environment' => 'production',
            'status' => 'active',
            'criticality' => 'critical',
            'owner_person' => 'Diskominfo Ciamis',
            'responsible_unit' => 'Bidang IKP & Persandian',
            'description' => 'Website portal gerbang utama informasi publik Pemkab Ciamis',
        ]);

        // CI 6: Aplikasi SIMPUS Dinas Kesehatan
        $ciAppSimpus = ConfigurationItem::create([
            'ci_code' => 'CI-APP-00001',
            'name' => 'Sistem Informasi Manajemen Puskesmas (SIMPUS)',
            'ci_type_id' => $typeApp->id,
            'organization_id' => $dinkes->id,
            'location_id' => $dinkesLocation?->id ?? $dcLocation->id,
            'url' => 'https://simpus.ciamiskab.go.id',
            'domain' => 'simpus.ciamiskab.go.id',
            'environment' => 'production',
            'status' => 'active',
            'criticality' => 'high',
            'owner_person' => 'Pengelola SIM Dinkes',
            'responsible_unit' => 'Dinas Kesehatan Kabupaten Ciamis',
            'description' => 'Aplikasi pelayanan rekam medis pasien di 37 Puskesmas se-Kabupaten Ciamis',
        ]);

        // 3. CMDB Relationships (Bidirectional Graph Data)
        // Website ciamiskab.go.id -> hosted_on -> Server SRV-01
        CiRelationship::create([
            'source_ci_id' => $ciWebPortal->id,
            'target_ci_id' => $ciServer01->id,
            'relationship_type' => 'hosted_on',
            'description' => 'Portal Ciamis dihosting pada NGINX web server SRV-01',
        ]);

        // App SIMPUS -> hosted_on -> Server SRV-01
        CiRelationship::create([
            'source_ci_id' => $ciAppSimpus->id,
            'target_ci_id' => $ciServer01->id,
            'relationship_type' => 'hosted_on',
            'description' => 'Aplikasi SIMPUS berjalan di atas container SRV-01',
        ]);

        // Server SRV-01 -> uses -> Database Cluster
        CiRelationship::create([
            'source_ci_id' => $ciServer01->id,
            'target_ci_id' => $ciDatabase->id,
            'relationship_type' => 'uses',
            'description' => 'Koneksi query database via dedicated VLAN',
        ]);

        // Server SRV-01 -> connects_to -> Switch Core
        CiRelationship::create([
            'source_ci_id' => $ciServer01->id,
            'target_ci_id' => $ciSwitch->id,
            'relationship_type' => 'connects_to',
            'description' => 'Uplink 10Gbps SFP+ ke Port 1 Switch Core',
        ]);

        // Switch Core -> protected_by -> Firewall FortiGate
        CiRelationship::create([
            'source_ci_id' => $ciSwitch->id,
            'target_ci_id' => $ciFirewall->id,
            'relationship_type' => 'protected_by',
            'description' => 'Seluruh traffic inbound/outbound melewati inspeksi FortiGate',
        ]);

        // 4. Website Monitoring Entry
        Website::create([
            'ci_id' => $ciWebPortal->id,
            'organization_id' => $diskominfo->id,
            'name' => 'Portal Resmi Kab. Ciamis',
            'url' => 'https://ciamiskab.go.id',
            'check_interval_minutes' => 5,
            'current_status' => 'up',
            'http_status_code' => 200,
            'response_time_ms' => 128,
            'ip_address' => '103.147.220.1',
            'ssl_status' => 'valid',
            'ssl_issuer' => "Let's Encrypt Authority X3",
            'ssl_expires_at' => now()->addMonths(2),
            'last_checked_at' => now(),
            'last_status_change_at' => now()->subDays(10),
            'is_active' => true,
        ]);

        // 5. Service Catalog & Tickets
        $service = Service::create([
            'code' => 'SRV-CSIRT',
            'name' => 'Penanganan Insiden Siber & Aduan Keamanan',
            'category' => 'Persandian & Keamanan',
            'sla_response_hours' => 1,
            'sla_resolution_hours' => 12,
            'description' => 'Layanan cepat tanggap darurat serangan siber, malware, atau defacement',
        ]);

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-2026-0001',
            'requester_id' => $opdUser->id,
            'organization_id' => $dinkes->id,
            'service_id' => $service->id,
            'ci_id' => $ciAppSimpus->id,
            'assigned_technician_id' => $tech->id,
            'category' => 'incident',
            'priority' => 'high',
            'status' => 'in_progress',
            'title' => 'Percobaan Brute Force Login pada SIMPUS Dinkes',
            'description' => 'Terdeteksi lebih dari 500 percobaan login gagal dari IP asing pada portal SIMPUS',
            'sla_due_at' => now()->addHours(12),
            'first_response_at' => now()->subHour(),
        ]);

        // 6. Incident Linked to CI and Ticket
        Incident::create([
            'incident_number' => 'INC-2026-0001',
            'title' => 'Serangan Otomatis Brute Force Endpoint Auth SIMPUS',
            'source' => 'service_desk',
            'ticket_id' => $ticket->id,
            'ci_id' => $ciAppSimpus->id,
            'organization_id' => $dinkes->id,
            'assigned_technician_id' => $tech->id,
            'priority' => 'high',
            'status' => 'investigation',
            'impact_description' => 'Aplikasi mengalami kenaikan beban response time 3 detik',
            'root_cause' => 'Belum diterapkannya Rate Limiter dan CAPTCHA pada form login Puskesmas',
            'detected_at' => now()->subHours(2),
        ]);

        // 7. Risk Linked to CI
        Risk::create([
            'risk_code' => 'RSK-2026-001',
            'title' => 'Risiko Gangguan Layanan Akibat Lonjakan Traffic (DDoS)',
            'description' => 'Potensi downtime pada server web publik jika diserang trafik bot masif',
            'organization_id' => $diskominfo->id,
            'ci_id' => $ciServer01->id,
            'asset_id' => $assetServer01->id,
            'threat' => 'DDoS Attack Volume Tinggi',
            'vulnerability' => 'Bandwidth uplink terbatas pada 1 Gbps direct pipe',
            'likelihood' => 3,
            'impact' => 4,
            'owner_id' => $tech->id,
            'status' => 'analyzed',
            'due_date' => now()->addMonths(3),
        ]);
    }
}
