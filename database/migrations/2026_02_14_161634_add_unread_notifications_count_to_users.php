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
            $table->unsignedInteger('unread_notifications_count')->default(0)->after('status');
            $table->index('unread_notifications_count');
        });

        // Backfill existing data
        echo "⏳ Backfilling unread_notifications_count for existing users...\n";

        // Using raw SQL for performance and compatibility across environments
        \Illuminate\Support\Facades\DB::statement('
            UPDATE users 
            SET unread_notifications_count = (
                SELECT COUNT(*) 
                FROM notifications 
                WHERE notifications.notifiable_id = users.id 
                AND notifications.notifiable_type LIKE \'%User%\'
                AND notifications.read_at IS NULL
            )
        ');

        echo "✅ Backfill complete.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['unread_notifications_count']);
            $table->dropColumn('unread_notifications_count');
        });
    }
};