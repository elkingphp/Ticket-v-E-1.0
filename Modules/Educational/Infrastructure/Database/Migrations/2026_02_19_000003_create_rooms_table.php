<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.rooms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('floor_id')->constrained('education.floors')->cascadeOnDelete();

            $table->string('name');
            $table->string('code');
            $table->integer('capacity');

            $table->enum('room_type', ['lecture', 'lab', 'hall', 'meeting']);
            $table->enum('room_status', ['active', 'maintenance', 'disabled'])->default('active');

            $table->integer('version')->default(1); // For optimistic locking

            $table->timestamps();

            // Unique composite
            $table->unique(['floor_id', 'code']);

            // Important indexes for scheduling searches
            $table->index('room_type');
            $table->index('room_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.rooms');
    }
};
