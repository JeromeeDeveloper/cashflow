<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin Users
        $adminUsers = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('password'),
                'phone' => '+63 900 000 0001',
                'role' => 'admin',
                'status' => 'inactive', // Seeded users start as inactive until they log in
            ],
        ];

        foreach ($adminUsers as $adminUser) {
            User::create($adminUser);
        }

        // Create Head Office Users (if not already created by BranchesTableSeeder)
        $headUsers = [
            [
                'name' => 'John Smith',
                'email' => 'head@gmail.com',
                'password' => bcrypt('password'),
                'phone' => '+63 912 345 6789',
                'role' => 'head',
                'status' => 'inactive', // Seeded users start as inactive until they log in
            ],
        ];

        foreach ($headUsers as $headUser) {
            User::firstOrCreate(
                ['email' => $headUser['email']],
                $headUser
            );
        }

        // Get branches for branch user assignment
        $branches = Branch::all();

        // Create Branch Users
        $branchUsers = [
            // Main Branch - Makati
            [
                'name' => 'Ana Santos',
                'email' => 'branch@gmail.com',
                'password' => bcrypt('password'),
                'phone' => '+63 945 678 9012',
                'role' => 'branch',
                'status' => 'inactive',
                'branch_id' => $branches->where('name', 'Main Branch - Makati')->first()?->id,
            ],

        ];

        foreach ($branchUsers as $branchUser) {
            User::firstOrCreate(
                ['email' => $branchUser['email']],
                $branchUser
            );
        }
    }
}
