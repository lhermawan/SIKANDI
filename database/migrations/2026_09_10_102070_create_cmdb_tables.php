<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ci_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->index();
            $table->string('name');
            $table->enum('category', ['hardware', 'software', 'network', 'service', 'security', 'iot', 'other'])->default('hardware');
            $table->string('icon')->default('server');
            $table->string('color')->default('#3b82f6');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('configuration_items', function (Blueprint $table) {
            $table->id();
            $table->string('ci_code')->unique()->index(); // Format: CI-SRV-00001
            $table->string('name')->index();
            $table->foreignId('ci_type_id')->constrained('ci_types');
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->string('hostname')->nullable()->index();
            $table->string('ip_address')->nullable()->index();
            $table->string('mac_address')->nullable();
            $table->string('domain')->nullable()->index();
            $table->string('url')->nullable();

            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable()->index();
            $table->string('operating_system')->nullable();
            $table->string('os_version')->nullable();

            $table->enum('environment', ['production', 'staging', 'development', 'dr'])->default('production');
            $table->enum('status', ['planned', 'active', 'maintenance', 'warning', 'down', 'retired', 'archived'])->default('active')->index();
            $table->enum('criticality', ['critical', 'high', 'medium', 'low'])->default('medium')->index();

            $table->string('owner_person')->nullable();
            $table->string('responsible_unit')->nullable();
            $table->json('specifications')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ci_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_ci_id')->constrained('configuration_items')->cascadeOnDelete();
            $table->foreignId('target_ci_id')->constrained('configuration_items')->cascadeOnDelete();
            $table->enum('relationship_type', [
                'depends_on',
                'used_by',
                'hosted_on',
                'runs_on',
                'connects_to',
                'contains',
                'uses',
                'protected_by',
                'managed_by',
                'located_at',
                'owned_by',
                'supports',
                'part_of',
            ])->index();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['source_ci_id', 'target_ci_id', 'relationship_type'], 'ci_rel_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ci_relationships');
        Schema::dropIfExists('configuration_items');
        Schema::dropIfExists('ci_types');
    }
};
