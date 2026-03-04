<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.programs', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();

            $table->enum('status', ['draft', 'published', 'running', 'completed', 'archived'])->default('draft');

            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();

            $table->integer('version')->default(1);

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.programs');
    }
};
