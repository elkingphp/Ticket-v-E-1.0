<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('legacy_data_conflicts', function (Blueprint $table) {
            $table->id();
            $table->string('source_table');
            $table->unsignedBigInteger('legacy_id');
            $table->string('conflict_type');
            $table->string('duplicate_key');
            $table->json('payload_json')->nullable();
            $table->string('resolution_status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legacy_data_conflicts');
    }
};
