<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.trainee_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_profile_id')->constrained('education.trainee_profiles')->cascadeOnDelete();
            $table->string('relation');
            $table->string('name');
            $table->string('national_id')->nullable();
            $table->string('phone');
            $table->string('phone2')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('photo_disk')->nullable();
            $table->foreignId('governorate_id')->nullable()->constrained('education.governorates')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.trainee_emergency_contacts');
    }
};
