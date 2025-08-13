<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\User;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a head office user
        $headUser = User::where('role', 'head')->first();

        if (!$headUser) {
            $headUser = User::create([
                'name' => 'Head Office Manager',
                'email' => 'head@company.com',
                'password' => bcrypt('password'),
                'role' => 'head',
                'status' => 'active',
            ]);
        }

        $branches = [
            [
                'name' => 'Main Branch',
                'head_id' => $headUser->id,
            ],
            [
                'name' => 'North Branch',
                'head_id' => $headUser->id,
            ],
            [
                'name' => 'South Branch',
                'head_id' => $headUser->id,
            ],
            [
                'name' => 'East Branch',
                'head_id' => $headUser->id,
            ],
            [
                'name' => 'West Branch',
                'head_id' => $headUser->id,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }

        $this->command->info('Branches seeded successfully!');
    }
}
