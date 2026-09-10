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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('id')->constrained('organizations')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('organization_id')->constrained('departments')->nullOnDelete();
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('nip')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('password');
            $table->string('avatar_url')->nullable()->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'organization_id',
                'department_id',
                'username',
                'phone',
                'nip',
                'is_active',
                'avatar_url',
                'last_login_at',
                'deleted_at',
            ]);
        });
    }
};
