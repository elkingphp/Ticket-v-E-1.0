<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase A · Hardening Step 1
 * ─────────────────────────────────────────────────────────
 * Adds lifecycle columns to evaluation_forms:
 *   • published_at  – official timestamp of when form was published
 *   • deleted_at    – soft-delete (never physically destroy published forms)
 */
return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::table('education.evaluation_forms', function (Blueprint $table) {
            // Official publish timestamp (NULL = not yet published)
            $table->timestamp('published_at')->nullable()->after('description');

            // Soft deletes — draft-only forms can be "deleted" but never truly erased
            $table->softDeletes()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('education.evaluation_forms', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('published_at');
        });
    }
};
