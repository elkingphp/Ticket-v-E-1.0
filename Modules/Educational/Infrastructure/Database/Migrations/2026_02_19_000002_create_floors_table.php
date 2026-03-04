<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.floors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('building_id')->constrained('education.buildings')->cascadeOnDelete();

            $table->string('floor_number');
            $table->string('name')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->unique(['building_id', 'floor_number']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.floors');
    }
};
