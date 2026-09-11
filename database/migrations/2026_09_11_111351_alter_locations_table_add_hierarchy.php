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
        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->change();
            $table->foreignId('parent_id')->nullable()->after('organization_id')->constrained('locations')->nullOnDelete();
            $table->string('code')->nullable()->unique()->after('parent_id');
            $table->string('type')->default('location')->after('code'); // 'location' or 'room'
            $table->boolean('is_active')->default(true)->after('coordinates');
            $table->text('description')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'code', 'type', 'is_active', 'description']);
            // Reverting organization_id to not nullable could fail if there are nulls, so we leave it or carefully alter it.
        });
    }
};
