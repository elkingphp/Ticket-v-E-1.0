<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('profile_completion_score')->default(0)->after('status');
            $table->string('security_risk_level')->default('medium')->after('profile_completion_score');
            $table->timestamp('scheduled_for_deletion_at')->nullable()->after('blocked_at');
        });

        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                $indexes = Schema::getIndexes('sessions');
                $hasIndex = collect($indexes)->contains(function ($index) {
                        return $index['name'] === 'sessions_user_id_index';
                    }
                    );

                    if (!$hasIndex) {
                        $table->index('user_id');
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_completion_score', 'security_risk_level', 'scheduled_for_deletion_at']);
        });

        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
            });
        }
    }
};