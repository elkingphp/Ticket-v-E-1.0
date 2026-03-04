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
        Schema::dropIfExists('ticket_delete_requests');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed for legacy cleanup
    }
};
