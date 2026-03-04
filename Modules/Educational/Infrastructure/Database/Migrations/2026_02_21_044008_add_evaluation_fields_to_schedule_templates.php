<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Set the database connection to Postgres to allow schema operations.
     */
    protected $connection = 'pgsql';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('education.schedule_templates', function (Blueprint $table) {
            $table->foreignId('evaluation_form_id')
                ->nullable()
                ->after('session_type_id')
                ->constrained('education.evaluation_forms')
                ->nullOnDelete();

            $table->jsonb('allow_evaluator_types')
                ->nullable()
                ->after('evaluation_form_id')
                ->comment('Default evaluator types for lectures generated from this template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education.schedule_templates', function (Blueprint $table) {
            $table->dropForeign(['evaluation_form_id']);
            $table->dropColumn(['evaluation_form_id', 'allow_evaluator_types']);
        });
    }
};
