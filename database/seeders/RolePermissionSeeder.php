<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions list
        $permissions = [
            // Core & Users
            'view-dashboard',
            'view-executive-dashboard',
            'manage-users',
            'manage-roles',
            'manage-organizations',
            'view-audit-logs',
            'manage-settings',

            // CMDB
            'view-cmdb',
            'create-cmdb',
            'edit-cmdb',
            'delete-cmdb',
            'view-cmdb-graph',
            'manage-ci-relationships',

            // ITAM
            'view-assets',
            'create-assets',
            'edit-assets',
            'delete-assets',
            'manage-asset-maintenance',
            'print-asset-qr',

            // Service Desk
            'view-tickets',
            'create-tickets',
            'assign-tickets',
            'resolve-tickets',
            'close-tickets',

            // Incident Management
            'view-incidents',
            'create-incidents',
            'manage-incidents',

            // Monitoring
            'view-monitoring',
            'manage-monitoring',

            // Security / CSIRT
            'view-security-incidents',
            'report-security-incident',
            'manage-security-incidents',

            // Risk Management
            'view-risks',
            'manage-risks',

            // IKASANDI
            'view-ikasandi-dashboard',
            'fill-assessment',
            'verify-assessment',
            'manage-assessment-indicators',

            // Knowledge & Docs
            'view-knowledge',
            'manage-knowledge',
            'view-documents',
            'upload-documents',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm);
        }

        // Create Roles
        $superAdmin = Role::findOrCreate('Super Admin');
        $superAdmin->givePermissionTo(Permission::all());

        $adminPersandian = Role::findOrCreate('Admin Persandian');
        $adminPersandian->givePermissionTo([
            'view-dashboard',
            'view-executive-dashboard',
            'manage-organizations',
            'view-audit-logs',
            'view-cmdb', 'create-cmdb', 'edit-cmdb', 'delete-cmdb', 'view-cmdb-graph', 'manage-ci-relationships',
            'view-assets', 'create-assets', 'edit-assets', 'manage-asset-maintenance', 'print-asset-qr',
            'view-tickets', 'create-tickets', 'assign-tickets', 'resolve-tickets', 'close-tickets',
            'view-incidents', 'create-incidents', 'manage-incidents',
            'view-monitoring', 'manage-monitoring',
            'view-security-incidents', 'report-security-incident', 'manage-security-incidents',
            'view-risks', 'manage-risks',
            'view-ikasandi-dashboard', 'verify-assessment', 'manage-assessment-indicators',
            'view-knowledge', 'manage-knowledge', 'view-documents', 'upload-documents',
        ]);

        $technician = Role::findOrCreate('IT Technician');
        $technician->givePermissionTo([
            'view-dashboard',
            'view-cmdb', 'edit-cmdb', 'view-cmdb-graph', 'manage-ci-relationships',
            'view-assets', 'edit-assets', 'manage-asset-maintenance', 'print-asset-qr',
            'view-tickets', 'resolve-tickets',
            'view-incidents', 'manage-incidents',
            'view-monitoring',
            'view-security-incidents',
            'view-knowledge', 'manage-knowledge', 'view-documents', 'upload-documents',
        ]);

        $opdUser = Role::findOrCreate('OPD User');
        $opdUser->givePermissionTo([
            'view-dashboard',
            'view-cmdb',
            'view-assets',
            'view-tickets', 'create-tickets',
            'report-security-incident',
            'fill-assessment',
            'view-knowledge',
            'view-documents',
        ]);

        $management = Role::findOrCreate('Management');
        $management->givePermissionTo([
            'view-dashboard',
            'view-executive-dashboard',
            'view-ikasandi-dashboard',
            'view-cmdb',
            'view-assets',
            'view-monitoring',
            'view-risks',
            'view-knowledge',
            'view-documents',
        ]);
    }
}
