<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\User;

class BranchesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create head office users first
        $headUsers = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@coop.com',
                'password' => bcrypt('password123'),
                'phone' => '+63 912 345 6789',
                'role' => 'head',
                'status' => 'active',
            ],
            [
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia@coop.com',
                'password' => bcrypt('password123'),
                'phone' => '+63 923 456 7890',
                'role' => 'head',
                'status' => 'active',
            ],
            [
                'name' => 'Robert Johnson',
                'email' => 'robert.johnson@coop.com',
                'password' => bcrypt('password123'),
                'phone' => '+63 934 567 8901',
                'role' => 'head',
                'status' => 'active',
            ],
        ];

        foreach ($headUsers as $headUser) {
            User::create($headUser);
        }

        // Get head users for branch assignment
        $headUserIds = User::where('role', 'head')->pluck('id')->toArray();

        // Sample branches data
        $branches = [
            [
                'name' => 'Main Branch - Makati',
                'head_id' => $headUserIds[0] ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Quezon City Branch',
                'head_id' => $headUserIds[1] ?? 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cebu City Branch',
                'head_id' => $headUserIds[2] ?? 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Davao City Branch',
                'head_id' => $headUserIds[0] ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Baguio City Branch',
                'head_id' => $headUserIds[1] ?? 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Iloilo City Branch',
                'head_id' => $headUserIds[2] ?? 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Create branches
        foreach ($branches as $branch) {
            Branch::create($branch);
        }

        $this->command->info('Branches seeded successfully!');
    }
}
