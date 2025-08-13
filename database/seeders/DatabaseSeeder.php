<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BranchesTableSeeder::class,    // Create branches and head users first
            UsersTableSeeder::class,       // Create admin and branch users
            CashflowDataSeeder::class,     // Create sample cashflow data
        ]);
    }
}
