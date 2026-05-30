<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: contacts + contact_tags + enrich tickets/customers
 *
 * Lifecycle:
 *   customers  →  contacts  (contacts is the enriched replacement)
 *   tickets.customer_id stays; tickets.contact_id added as FK to contacts
 *   contact_tags is a many-to-many pivot (contacts ↔ tags)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Tags master table ────────────────────────────────────────
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();              // e.g. "VIP", "IT Issue", "Billing"
            $table->string('color', 7)->default('#667781');    // hex colour for badge
            $table->timestamps();
        });

        // ── 2. Contacts table (enriched customer profile) ───────────────
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            // Core identity
            $table->string('name');
            $table->string('phone_number', 30)->unique();       // primary WA identifier
            $table->string('email', 191)->nullable()->unique();
            $table->string('avatar_url', 500)->nullable();      // profile picture (from WAHA / upload)

            // Organisation
            $table->string('company', 191)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('job_title', 100)->nullable();

            // WhatsApp / gateway metadata
            $table->string('wa_id', 50)->nullable()->unique(); // e.g. "628123456789@c.us"
            $table->string('wa_push_name', 191)->nullable();   // display name from WA
            $table->string('source')->default('whatsapp');     // whatsapp | email | manual | import
            $table->timestamp('last_seen_at')->nullable();      // last WA activity timestamp
            $table->boolean('is_wa_verified')->default(false); // is number registered on WA

            // Helpdesk config
            $table->boolean('is_vip')->default(false);
            $table->boolean('is_blocked')->default(false);     // block incoming messages
            $table->integer('sla_override_minutes')->nullable(); // custom SLA per contact
            $table->text('notes')->nullable();                  // permanent internal notes about contact

            // Metrics (denormalised for speed)
            $table->unsignedInteger('total_tickets')->default(0);
            $table->unsignedInteger('open_tickets')->default(0);
            $table->timestamp('first_contact_at')->nullable();
            $table->timestamp('last_contact_at')->nullable();

            // Soft-delete + timestamps
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('company');
            $table->index('source');
            $table->index(['is_vip', 'is_blocked']);
        });

        // ── 3. contact_tag pivot (many-to-many) ─────────────────────────
        Schema::create('contact_tag', function (Blueprint $table) {
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['contact_id', 'tag_id']);
        });

        // ── 4. Enrich tickets with contact_id ────────────────────────────
        //    We keep the legacy customer_id column intact so existing data
        //    is not broken; contact_id is the new canonical FK.
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('contact_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('contacts')
                ->nullOnDelete();

            $table->string('channel_ref', 191)->nullable()->after('channel'); // e.g. WA message_id
            $table->timestamp('resolved_at')->nullable()->after('last_message_at');
            $table->timestamp('first_response_at')->nullable()->after('resolved_at');
            $table->unsignedInteger('response_time_seconds')->nullable()->after('first_response_at'); // SLA metric
            $table->unsignedInteger('resolution_time_seconds')->nullable()->after('response_time_seconds');

            $table->index('contact_id');
            $table->index('resolved_at');
        });

        // ── 5. Enrich contacts with additional_phones (JSON list) ────────
        //    (done inside contacts table above via JSON, but we can also
        //    have a proper child table for multiple phone numbers)
        Schema::create('contact_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number', 30);
            $table->string('label', 50)->default('other'); // mobile | work | home | other
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['contact_id', 'phone_number']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropIndex(['contact_id']);
            $table->dropIndex(['resolved_at']);
            $table->dropColumn([
                'contact_id',
                'channel_ref',
                'resolved_at',
                'first_response_at',
                'response_time_seconds',
                'resolution_time_seconds',
            ]);
        });

        Schema::dropIfExists('contact_phones');
        Schema::dropIfExists('contact_tag');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('tags');
    }
};
