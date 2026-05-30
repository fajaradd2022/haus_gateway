<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK pada driver yang mendukung; SQLite tidak menyimpan FK sebagai constraint terpisah.
        if (DB::getDriverName() !== 'sqlite' && Schema::hasColumn('messages', 'deleted_by')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropForeign(['deleted_by']);
            });
        }

        Schema::table('messages', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['deleted_at', 'deleted_by', 'is_deleted_for_everyone'],
                fn (string $col) => Schema::hasColumn('messages', $col),
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('is_internal_note');
            $table->foreignId('deleted_by')->nullable()->after('deleted_at')
                  ->constrained('users')->nullOnDelete();
            $table->boolean('is_deleted_for_everyone')->default(false)->after('deleted_by');
        });
    }
};
