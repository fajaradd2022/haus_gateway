<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── messages: soft-delete support ──────────────────────────────
        Schema::table('messages', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('is_internal_note');
            $table->foreignId('deleted_by')->nullable()->after('deleted_at')
                  ->constrained('users')->nullOnDelete();
            $table->boolean('is_deleted_for_everyone')->default(false)->after('deleted_by');
        });

        // ── tickets: archive + category ────────────────────────────────
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('last_message_at');
            $table->string('category', 100)->nullable()->after('channel');
        });

        // ── customers: enrich with extra profile fields ─────────────────
        Schema::table('customers', function (Blueprint $table) {
            $table->string('company', 191)->nullable()->after('phone_number');
            $table->string('email', 191)->nullable()->after('company');
            $table->text('notes')->nullable()->after('email');
            $table->string('avatar_url', 500)->nullable()->after('notes');
            $table->boolean('is_vip')->default(false)->after('avatar_url');
            $table->boolean('is_blocked')->default(false)->after('is_vip');
            $table->timestamp('last_contact_at')->nullable()->after('is_blocked');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_at', 'deleted_by', 'is_deleted_for_everyone']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['archived_at', 'category']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['company', 'email', 'notes', 'avatar_url', 'is_vip', 'is_blocked', 'last_contact_at']);
        });
    }
};
