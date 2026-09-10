<?php

namespace Database\Seeders;

use App\Models\CiType;
use Illuminate\Database\Seeder;

class CiTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'server', 'name' => 'Server Physical', 'category' => 'hardware', 'icon' => 'server', 'color' => '#2563eb'],
            ['code' => 'vm', 'name' => 'Virtual Machine', 'category' => 'hardware', 'icon' => 'cpu', 'color' => '#3b82f6'],
            ['code' => 'pc', 'name' => 'PC / Desktop', 'category' => 'hardware', 'icon' => 'monitor', 'color' => '#0284c7'],
            ['code' => 'laptop', 'name' => 'Laptop', 'category' => 'hardware', 'icon' => 'laptop', 'color' => '#0ea5e9'],
            ['code' => 'printer', 'name' => 'Printer / Scanner', 'category' => 'hardware', 'icon' => 'printer', 'color' => '#64748b'],
            ['code' => 'router', 'name' => 'Router', 'category' => 'network', 'icon' => 'git-commit', 'color' => '#f97316'],
            ['code' => 'switch', 'name' => 'Switch Network', 'category' => 'network', 'icon' => 'shuffle', 'color' => '#ea580c'],
            ['code' => 'firewall', 'name' => 'Firewall Hardware/Appliance', 'category' => 'security', 'icon' => 'shield', 'color' => '#dc2626'],
            ['code' => 'access_point', 'name' => 'Access Point (Wi-Fi)', 'category' => 'network', 'icon' => 'wifi', 'color' => '#16a34a'],
            ['code' => 'network_device', 'name' => 'Network Device', 'category' => 'network', 'icon' => 'radio', 'color' => '#059669'],
            ['code' => 'storage', 'name' => 'SAN / NAS Storage', 'category' => 'hardware', 'icon' => 'hard-drive', 'color' => '#475569'],
            ['code' => 'database', 'name' => 'Database Engine/Instance', 'category' => 'software', 'icon' => 'database', 'color' => '#7c3aed'],
            ['code' => 'application', 'name' => 'Aplikasi / SIM', 'category' => 'software', 'icon' => 'terminal', 'color' => '#9333ea'],
            ['code' => 'website', 'name' => 'Website / Web Portal', 'category' => 'service', 'icon' => 'globe', 'color' => '#0891b2'],
            ['code' => 'domain', 'name' => 'Domain Name (DNS)', 'category' => 'service', 'icon' => 'at-sign', 'color' => '#0d9488'],
            ['code' => 'ssl_cert', 'name' => 'SSL Certificate', 'category' => 'security', 'icon' => 'lock', 'color' => '#10b981'],
            ['code' => 'api', 'name' => 'REST API / Webhook', 'category' => 'service', 'icon' => 'code', 'color' => '#6366f1'],
            ['code' => 'service', 'name' => 'IT Service', 'category' => 'service', 'icon' => 'layers', 'color' => '#8b5cf6'],
            ['code' => 'cloud_resource', 'name' => 'Cloud Resource (VPS/Bucket)', 'category' => 'service', 'icon' => 'cloud', 'color' => '#06b6d4'],
            ['code' => 'cctv', 'name' => 'CCTV Surveillance', 'category' => 'iot', 'icon' => 'video', 'color' => '#d97706'],
            ['code' => 'iot_device', 'name' => 'IoT Sensor / Device', 'category' => 'iot', 'icon' => 'zap', 'color' => '#eab308'],
            ['code' => 'security_device', 'name' => 'Perangkat Sandi / HSM / VPN', 'category' => 'security', 'icon' => 'key', 'color' => '#b91c1c'],
            ['code' => 'other', 'name' => 'Other CI', 'category' => 'other', 'icon' => 'box', 'color' => '#6b7280'],
        ];

        foreach ($types as $type) {
            CiType::create($type);
        }
    }
}
