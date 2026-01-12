<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'username' => 'superadmin',
            'phone' => '1234567890',
            'email' => 'superadmin@iskillbiz.com',
            'password' => Hash::make('superadmin@iskillbiz.com'), // Pass: same as email
            'gender' => 'male',
            'dob' => '1990-01-01',
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super admin');

        // Admin
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin',
            'phone' => '0987654321',
            'email' => 'admin@iskillbiz.com',
            'password' => Hash::make('admin@iskillbiz.com'),
            'gender' => 'female',
            'dob' => '1992-05-05',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Client
        $client = User::create([
            'first_name' => 'Client',
            'last_name' => 'User',
            'username' => 'client',
            'phone' => '1122334455',
            'email' => 'client@iskillbiz.com',
            'password' => Hash::make('client@iskillbiz.com'),
            'gender' => 'other',
            'dob' => '2000-10-10',
            'email_verified_at' => now(),
        ]);
        $client->assignRole('client');
    }
}
