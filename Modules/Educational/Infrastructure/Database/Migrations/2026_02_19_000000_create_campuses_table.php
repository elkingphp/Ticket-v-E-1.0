<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Set the database connection to Postgres to allow schema operations.
     */
    protected $connection = 'pgsql';

    public function up(): void
    {
        // Explicitly create the schema if it doesn't exist
        DB::statement('CREATE SCHEMA IF NOT EXISTS education AUTHORIZATION CURRENT_USER;');

        Schema::create('education.campuses', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            // Indexes
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.campuses');
        // We typically don't drop schemas automatically, but you could if needed
    }
};
