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
                'status' => 'active',
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
                'status' => 'active',
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
                'status' => 'active',
                'branch_id' => $branches->where('name', 'Main Branch - Makati')->first()?->id,
            ],
            [
                'name' => 'Carlos Reyes',
                'email' => 'carlos.reyes@makati.coop.com',
                'password' => bcrypt('branch123'),
                'phone' => '+63 956 789 0123',
                'role' => 'branch',
                'status' => 'active',
                'branch_id' => $branches->where('name', 'Main Branch - Makati')->first()?->id,
            ],

            // Quezon City Branch
            [
                'name' => 'Luzviminda Cruz',
                'email' => 'luz.cruz@qc.coop.com',
                'password' => bcrypt('branch123'),
                'phone' => '+63 967 890 1234',
                'role' => 'branch',
                'status' => 'active',
                'branch_id' => $branches->where('name', 'Quezon City Branch')->first()?->id,
            ],
            [
                'name' => 'Fernando Torres',
                'email' => 'fernando.torres@qc.coop.com',
                'password' => bcrypt('branch123'),
                'phone' => '+63 978 901 2345',
                'role' => 'branch',
                'status' => 'active',
                'branch_id' => $branches->where('name', 'Quezon City Branch')->first()?->id,
            ],

            // Cebu City Branch
            [
                'name' => 'Carmen Lim',
                'email' => 'carmen.lim@cebu.coop.com',
                'password' => bcrypt('branch123'),
                'phone' => '+63 989 012 3456',
                'role' => 'branch',
                'status' => 'active',
                'branch_id' => $branches->where('name', 'Cebu City Branch')->first()?->id,
            ],
            [
                'name' => 'Manuel Ong',
                'email' => 'manuel.ong@cebu.coop.com',
                'password' => bcrypt('branch123'),
                'phone' => '+63 990 123 4567',
                'role' => 'branch',
                'status' => 'active',
                'branch_id' => $branches->where('name', 'Cebu City Branch')->first()?->id,
            ],

            // Davao City Branch
            [
                'name' => 'Elena Rodriguez',
                'email' => 'elena.rodriguez@davao.coop.com',
                'password' => bcrypt('branch123'),
                'phone' => '+63 901 234 5678',
                'role' => 'branch',
                'status' => 'active',
                'branch_id' => $branches->where('name', 'Davao City Branch')->first()?->id,
            ],
            [
                'name' => 'Antonio Silva',
                'email' => 'antonio.silva@davao.coop.com',
                'password' => bcrypt('branch123'),
                'phone' => '+63 902 345 6789',
                'role' => 'branch',
                'status' => 'active',
                'branch_id' => $branches->where('name', 'Davao City Branch')->first()?->id,
            ],

            // Baguio City Branch
            [
                'name' => 'Patricia Gomez',
                'email' => 'patricia.gomez@baguio.coop.com',
                'password' => bcrypt('branch123'),
                'phone' => '+63 903 456 7890',
                'role' => 'branch',
                'status' => 'active',
                'branch_id' => $branches->where('name', 'Baguio City Branch')->first()?->id,
            ],
            [
                'name' => 'Ricardo Mendoza',
                'email' => 'ricardo.mendoza@baguio.coop.com',
                'password' => bcrypt('branch123'),
                'phone' => '+63 904 567 8901',
                'role' => 'branch',
                'status' => 'active',
                'branch_id' => $branches->where('name', 'Baguio City Branch')->first()?->id,
            ],

            // Iloilo City Branch
            [
                'name' => 'Sofia Hernandez',
                'email' => 'sofia.hernandez@iloilo.coop.com',
                'password' => bcrypt('branch123'),
                'phone' => '+63 905 678 9012',
                'role' => 'branch',
                'status' => 'active',
                'branch_id' => $branches->where('name', 'Iloilo City Branch')->first()?->id,
            ],
            [
                'name' => 'Miguel Lopez',
                'email' => 'miguel.lopez@iloilo.coop.com',
                'password' => bcrypt('branch123'),
                'phone' => '+63 906 789 0123',
                'role' => 'branch',
                'status' => 'active',
                'branch_id' => $branches->where('name', 'Iloilo City Branch')->first()?->id,
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
