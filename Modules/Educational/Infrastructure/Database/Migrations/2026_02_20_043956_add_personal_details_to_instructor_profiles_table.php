<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('education.instructor_profiles', function (Blueprint $table) {
            $table->string('arabic_name')->nullable();
            $table->string('english_name')->nullable();
            $table->text('address')->nullable();
            $table->text('national_id')->nullable();
            $table->text('passport_number')->nullable();
            $table->string('governorate')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education.instructor_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'arabic_name',
                'english_name',
                'address',
                'national_id',
                'passport_number',
                'governorate',
                'date_of_birth',
                'gender'
            ]);
        });
    }
};
