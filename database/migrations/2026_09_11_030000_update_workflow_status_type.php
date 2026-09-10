<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah ENUM menjadi VARCHAR(50) untuk mendukung fleksibilitas status workflow SOC baru
        DB::statement('ALTER TABLE security_incidents MODIFY workflow_status VARCHAR(50) NOT NULL DEFAULT "reported"');
        
        // Memperbaiki inkonsistensi nama status lama yang mungkin terbawa sebelum kita ganti ke workflow baru
        DB::statement('UPDATE security_incidents SET workflow_status = "investigating" WHERE workflow_status = "investigation"');
        DB::statement('UPDATE security_incidents SET workflow_status = "resolved" WHERE workflow_status = "closed"');
    }

    public function down(): void
    {
        // Kita biarkan sebagai VARCHAR karena mengembalikan ke ENUM bisa memicu error yang sama jika ada data baru
    }
};