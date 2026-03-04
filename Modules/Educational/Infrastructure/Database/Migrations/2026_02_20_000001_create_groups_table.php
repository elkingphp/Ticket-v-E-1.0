<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('program_id')->constrained('education.programs')->cascadeOnDelete();

            $table->string('name');
            $table->integer('capacity')->default(20);

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->unique(['program_id', 'name']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.groups');
    }
};
