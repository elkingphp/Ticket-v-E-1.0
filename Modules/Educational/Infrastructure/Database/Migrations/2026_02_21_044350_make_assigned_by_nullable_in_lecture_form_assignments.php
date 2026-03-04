<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Set the database connection to Postgres to allow schema operations.
     */
    protected $connection = 'pgsql';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('education.lecture_form_assignments', function (Blueprint $table) {
            $table->foreignId('assigned_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education.lecture_form_assignments', function (Blueprint $table) {
            $table->foreignId('assigned_by')->nullable(false)->change();
        });
    }
};
