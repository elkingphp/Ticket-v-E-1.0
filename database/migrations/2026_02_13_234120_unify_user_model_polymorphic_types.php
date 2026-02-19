<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $tables = [
        'notifications' => 'notifiable_type',
        'model_has_roles' => 'model_type',
        'personal_access_tokens' => 'tokenable_type',
    ];

    private array $legacyModels = [
        'Modules\Core\Domain\Models\User',
        'Modules\Users\Domain\Models\User',
        'App\Models\User'
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $column) {
            if (!Schema::hasTable($table))
                continue;

            try {
                \Illuminate\Support\Facades\DB::table($table)
                    ->whereIn($column, $this->legacyModels)
                    ->update([$column => 'user']);
            }
            catch (\Exception $e) {
                // Handling duplicates if they exist
                \Illuminate\Support\Facades\Log::warning("Migration error in table $table: " . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $column) {
            if (!Schema::hasTable($table))
                continue;

            \Illuminate\Support\Facades\DB::table($table)
                ->where($column, 'user')
                ->update([$column => 'Modules\Users\Domain\Models\User']);
        }
    }
};