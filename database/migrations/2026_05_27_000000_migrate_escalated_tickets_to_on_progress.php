<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tickets')
            ->where('status', 'escalated')
            ->update(['status' => 'on_progress']);
    }

    public function down(): void
    {
        // Status "escalated" sudah dihapus dari domain; tidak ada rollback yang akurat.
    }
};
