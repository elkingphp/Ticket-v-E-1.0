<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        // Create a Materialized View for heavy dashboard statistics
        // This pre-calculates the counts per program and month for the last 12 months

        DB::statement('DROP MATERIALIZED VIEW IF EXISTS education.mv_lecture_stats;');

        DB::statement('
            CREATE MATERIALIZED VIEW education.mv_lecture_stats AS
            SELECT 
                program_id,
                date_trunc(\'month\', starts_at) as month,
                status,
                COUNT(*) as lecture_count
            FROM 
                education.lectures
            WHERE 
                starts_at >= CURRENT_DATE - INTERVAL \'1 year\'
                AND deleted_at IS NULL
            GROUP BY 
                program_id, 
                date_trunc(\'month\', starts_at), 
                status
            WITH DATA;
        ');

        // Unique index required for CONCURRENT REFRESH
        DB::statement('CREATE UNIQUE INDEX idx_mv_lecture_stats_unique ON education.mv_lecture_stats (program_id, month, status);');

        // General lookup indexes
        DB::statement('CREATE INDEX idx_mv_lecture_stats_month ON education.mv_lecture_stats (month);');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS education.idx_mv_lecture_stats_unique;');
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS education.mv_lecture_stats;');
    }
};
