<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.trainee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_profile_id')->constrained('education.trainee_profiles')->cascadeOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.trainee_documents');
    }
};
