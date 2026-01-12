<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(['name' => 'super admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $client = Role::firstOrCreate(['name' => 'client']);

        // Give all permissions to Super Admin
        $superAdmin->syncPermissions(Permission::all());

        // Give specific permissions to Admin
        $admin->syncPermissions([
            'read_users',
            'create_users',
            'update_users',
            'read_roles',
        ]);

        // Client gets no extra permissions by default
    }
}
