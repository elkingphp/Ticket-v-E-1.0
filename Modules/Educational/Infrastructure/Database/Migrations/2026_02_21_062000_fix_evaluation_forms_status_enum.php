<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        // 1. Drop the constraint first so we can update the values
        DB::statement('ALTER TABLE "education"."evaluation_forms" DROP CONSTRAINT IF EXISTS evaluation_forms_status_check');

        // 2. Rename existing 'active' to 'published' to maintain data integrity
        DB::table('education.evaluation_forms')
            ->where('status', 'active')
            ->update(['status' => 'published']);

        // 3. Add the new constraint
        DB::statement("ALTER TABLE \"education\".\"evaluation_forms\" ADD CONSTRAINT evaluation_forms_status_check CHECK (status IN ('draft', 'published', 'archived'))");
    }

    public function down(): void
    {
        // 1. Drop the constraint first
        DB::statement('ALTER TABLE "education"."evaluation_forms" DROP CONSTRAINT IF EXISTS evaluation_forms_status_check');

        // 2. Revert 'published' back to 'active'
        DB::table('education.evaluation_forms')
            ->where('status', 'published')
            ->update(['status' => 'active']);

        // 3. Revert the check constraint
        DB::statement("ALTER TABLE \"education\".\"evaluation_forms\" ADD CONSTRAINT evaluation_forms_status_check CHECK (status IN ('draft', 'active', 'archived'))");
    }
};
