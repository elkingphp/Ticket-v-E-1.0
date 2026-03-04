<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::table('education.trainee_profiles', function (Blueprint $table) {
            $table->string('arabic_name')->nullable();
            $table->string('english_name')->nullable();
            $table->string('secondary_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('nationality')->default('egyptian');
            $table->date('date_of_birth')->nullable();
            $table->text('passport_number')->nullable(); // Encrypted like national_id
            $table->foreignId('governorate_id')->nullable()->constrained('education.governorates')->nullOnDelete();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('educational_status', ['student', 'graduate'])->nullable();
            $table->string('military_number')->nullable();
            $table->string('device_code')->nullable();
            $table->string('sect')->nullable();

            $table->foreignId('job_profile_id')->nullable()->constrained('education.job_profiles')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('education.programs')->nullOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('education.groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('education.trainee_profiles', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['job_profile_id']);
            $table->dropForeign(['program_id']);
            $table->dropForeign(['group_id']);
            $table->dropColumn([
                'arabic_name',
                'english_name',
                'secondary_phone',
                'address',
                'nationality',
                'date_of_birth',
                'passport_number',
                'governorate_id',
                'gender',
                'educational_status',
                'military_number',
                'device_code',
                'sect',
                'job_profile_id',
                'program_id',
                'group_id'
            ]);
        });
    }
};
