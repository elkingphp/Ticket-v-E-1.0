<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('education.room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // الاسم المعروض
            $table->string('slug')->unique(); // المفتاح (lecture, lab, hall...)
            $table->string('color')->default('primary'); // لون البادج
            $table->string('icon')->default('ri-door-open-line'); // أيقونة
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Add foreign key to rooms table => nullable for backward compat
        Schema::table('education.rooms', function (Blueprint $table) {
            $table->foreignId('room_type_id')
                ->nullable()
                ->after('room_type')
                ->constrained('education.room_types')
                ->nullOnDelete();
        });

        // Seed default types
        $now = now();
        DB::table('education.room_types')->insert([
            ['name' => 'قاعة محاضرات', 'slug' => 'lecture', 'color' => 'primary', 'icon' => 'ri-presentation-line', 'description' => 'قاعة مخصصة للمحاضرات النظرية', 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'معمل', 'slug' => 'lab', 'color' => 'success', 'icon' => 'ri-flask-line', 'description' => 'معمل للتجارب والتطبيقات العملية', 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'قاعة كبرى', 'slug' => 'hall', 'color' => 'info', 'icon' => 'ri-government-line', 'description' => 'قاعة كبيرة للمؤتمرات والتجمعات', 'is_active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'غرفة اجتماعات', 'slug' => 'meeting', 'color' => 'warning', 'icon' => 'ri-team-line', 'description' => 'غرفة صغيرة للاجتماعات', 'is_active' => true, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Migrate existing room_type string values to room_type_id
        DB::statement("
            UPDATE education.rooms r
            SET room_type_id = (
                SELECT rt.id FROM education.room_types rt WHERE rt.slug = r.room_type LIMIT 1
            )
            WHERE r.room_type IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('education.rooms', function (Blueprint $table) {
            $table->dropForeign(['room_type_id']);
            $table->dropColumn('room_type_id');
        });

        Schema::dropIfExists('education.room_types');
    }
};
