<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS education AUTHORIZATION CURRENT_USER;');

        DB::statement('
            CREATE TABLE education.evaluation_types (
                id BIGSERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                target_type VARCHAR(50) DEFAULT \'lecture\',
                allowed_roles JSONB,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE
            )
        ');

        Schema::table('education.evaluation_forms', function (Blueprint $table) {
            $table->foreignId('form_type_id')
                ->nullable()
                ->after('type')
                ->constrained('education.evaluation_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('education.evaluation_forms', function (Blueprint $table) {
            $table->dropForeign(['form_type_id']);
            $table->dropColumn('form_type_id');
        });

        DB::statement('DROP TABLE IF EXISTS education.evaluation_types');
    }
};
