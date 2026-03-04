<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.buildings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campus_id')->constrained('education.campuses')->cascadeOnDelete();

            $table->string('name');
            $table->string('code');
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            // Constraints
            $table->unique(['campus_id', 'code']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.buildings');
    }
};
