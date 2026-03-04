<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('education.session_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Update schedule_templates to use session_type_id
        Schema::table('education.schedule_templates', function (Blueprint $table) {
            $table->foreignId('session_type_id')->nullable()->after('group_id')->constrained('education.session_types');
        });

        // Update lectures to use session_type_id
        Schema::table('education.lectures', function (Blueprint $table) {
            $table->foreignId('session_type_id')->nullable()->after('group_id')->constrained('education.session_types');
        });
    }

    public function down(): void
    {
        Schema::table('education.lectures', function (Blueprint $table) {
            $table->dropForeign(['session_type_id']);
            $table->dropColumn('session_type_id');
        });

        Schema::table('education.schedule_templates', function (Blueprint $table) {
            $table->dropForeign(['session_type_id']);
            $table->dropColumn('session_type_id');
        });

        Schema::dropIfExists('education.session_types');
    }
};
