<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateRoleDisplayNamesSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'admin' => 'مدير',
            'super-admin' => 'مدير النظام',
            'editor' => 'محرر',
            'regular-user' => 'مستخدم عادي',
        ];

        foreach ($roles as $name => $displayName) {
            DB::table('roles')->where('name', $name)->update(['display_name' => $displayName]);
        }
    }
}
