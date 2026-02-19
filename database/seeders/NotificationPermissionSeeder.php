<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NotificationPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * هذا الـ Seeder يضيف صلاحية "view notifications" لجميع الأدوار
     * لأن كل مستخدم يجب أن يرى تنبيهاته الخاصة
     */
    public function run(): void
    {
        // 1. إنشاء أو الحصول على Permission
        $permission = Permission::firstOrCreate(
        ['name' => 'view notifications'],
        ['guard_name' => 'web']
        );

        echo "✅ Permission 'view notifications' created/found\n";

        // 2. الحصول على جميع الأدوار
        $roles = Role::all();

        if ($roles->isEmpty()) {
            echo "⚠️ No roles found. Creating default roles...\n";

            // إنشاء الأدوار الأساسية إذا لم تكن موجودة
            $defaultRoles = ['super-admin', 'admin', 'editor', 'user'];
            foreach ($defaultRoles as $roleName) {
                $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
                );
                $roles->push($role);
                echo "  ✅ Role '{$roleName}' created\n";
            }
        }

        // 3. إضافة الصلاحية لجميع الأدوار
        $assignedCount = 0;
        foreach ($roles as $role) {
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
                echo "  ✅ Permission assigned to role: {$role->name}\n";
                $assignedCount++;
            }
            else {
                echo "  ℹ️  Role '{$role->name}' already has this permission\n";
            }
        }

        // 4. ملخص
        echo "\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "  📊 Summary:\n";
        echo "  • Permission: view notifications\n";
        echo "  • Total Roles: {$roles->count()}\n";
        echo "  • Newly Assigned: {$assignedCount}\n";
        echo "  • Status: ✅ All users can now view notifications\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }
}