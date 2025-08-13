<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\CashflowFile;
use App\Models\Cashflow;
use App\Models\User;

class CashflowDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();
        $currentYear = date('Y');
        $months = ['January', 'February', 'March', 'April', 'May', 'June'];

        foreach ($branches as $branch) {
            // Create sample cashflow files for each branch
            foreach ($months as $month) {
                $cashflowFile = CashflowFile::create([
                    'file_name' => "cashflow_{$branch->name}_" . strtolower($month) . "_{$currentYear}.xlsx",
                    'file_path' => "cashflow_files/{$branch->id}/" . strtolower($month) . "_{$currentYear}.xlsx",
                    'original_name' => "Cashflow_Report_" . strtolower($month) . "_{$currentYear}.xlsx",
                    'file_type' => 'cashflow',
                    'year' => $currentYear,
                    'month' => $month,
                    'branch_id' => $branch->id,
                    'uploaded_by' => $branch->head->name ?? 'System',
                    'status' => 'processed',
                    'description' => "Monthly cashflow report for {$branch->name} - {$month} {$currentYear}",
                ]);

                // Create sample cashflow entries for each file
                $this->createSampleCashflows($cashflowFile, $branch, $month, $currentYear);
            }
        }

        $this->command->info('Sample cashflow data seeded successfully!');
    }

    private function createSampleCashflows($cashflowFile, $branch, $month, $year)
    {
        // Sample account data
        $accounts = [
            // Operating Activities
            ['code' => '1001', 'name' => 'Cash on Hand', 'type' => 'Asset', 'category' => 'Operating'],
            ['code' => '1002', 'name' => 'Cash in Bank', 'type' => 'Asset', 'category' => 'Operating'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'Asset', 'category' => 'Operating'],
            ['code' => '1200', 'name' => 'Inventory', 'type' => 'Asset', 'category' => 'Operating'],
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'Liability', 'category' => 'Operating'],
            ['code' => '2100', 'name' => 'Accrued Expenses', 'type' => 'Liability', 'category' => 'Operating'],
            ['code' => '3000', 'name' => 'Service Revenue', 'type' => 'Income', 'category' => 'Operating'],
            ['code' => '3100', 'name' => 'Interest Income', 'type' => 'Income', 'category' => 'Operating'],
            ['code' => '4000', 'name' => 'Operating Expenses', 'type' => 'Expense', 'category' => 'Operating'],
            ['code' => '4100', 'name' => 'Salaries and Wages', 'type' => 'Expense', 'category' => 'Operating'],

            // Investing Activities
            ['code' => '5000', 'name' => 'Equipment Purchase', 'type' => 'Asset', 'category' => 'Investing'],
            ['code' => '5100', 'name' => 'Building Investment', 'type' => 'Asset', 'category' => 'Investing'],
            ['code' => '5200', 'name' => 'Investment Securities', 'type' => 'Asset', 'category' => 'Investing'],

            // Financing Activities
            ['code' => '6000', 'name' => 'Long-term Loans', 'type' => 'Liability', 'category' => 'Financing'],
            ['code' => '6100', 'name' => 'Share Capital', 'type' => 'Equity', 'category' => 'Financing'],
            ['code' => '6200', 'name' => 'Dividends Paid', 'type' => 'Equity', 'category' => 'Financing'],
        ];

        foreach ($accounts as $account) {
            // Generate realistic amounts based on account type and category
            $baseAmount = $this->generateBaseAmount($account['type'], $account['category']);
            $projectionPercentage = rand(80, 120); // 80% to 120% projection
            $projectedAmount = $baseAmount * ($projectionPercentage / 100);
            $total = $baseAmount + $projectedAmount;

            // Add some variation based on branch and month
            $branchMultiplier = $this->getBranchMultiplier($branch->name);
            $monthMultiplier = $this->getMonthMultiplier($month);

            $actualAmount = $baseAmount * $branchMultiplier * $monthMultiplier;
            $projectedAmount = $projectedAmount * $branchMultiplier * $monthMultiplier;
            $total = $actualAmount + $projectedAmount;

            Cashflow::create([
                'cashflow_file_id' => $cashflowFile->id,
                'branch_id' => $branch->id,
                'year' => $year,
                'month' => $month,
                'period' => "{$month} {$year}",
                'account_code' => $account['code'],
                'account_name' => $account['name'],
                'account_type' => $account['type'],
                'cashflow_category' => $account['category'],
                'actual_amount' => round($actualAmount, 2),
                'projection_percentage' => $projectionPercentage,
                'projected_amount' => round($projectedAmount, 2),
                'period_values' => json_encode([
                    'week1' => round($actualAmount * 0.25, 2),
                    'week2' => round($actualAmount * 0.25, 2),
                    'week3' => round($actualAmount * 0.25, 2),
                    'week4' => round($actualAmount * 0.25, 2),
                ]),
                'total' => round($total, 2),
                'cash_beginning_balance' => round($total * 0.1, 2),
                'total_cash_available' => round($total * 1.1, 2),
                'less_disbursements' => round($total * 0.3, 2),
                'total_disbursements' => round($total * 0.3, 2),
                'cash_ending_balance' => round($total * 0.8, 2),
                'grand_total' => round($total * 1.2, 2),
            ]);
        }
    }

    private function generateBaseAmount($type, $category)
    {
        $baseAmounts = [
            'Asset' => [
                'Operating' => rand(50000, 500000),
                'Investing' => rand(100000, 2000000),
                'Financing' => rand(100000, 1000000),
            ],
            'Liability' => [
                'Operating' => rand(30000, 300000),
                'Investing' => rand(50000, 500000),
                'Financing' => rand(200000, 3000000),
            ],
            'Equity' => [
                'Operating' => rand(10000, 100000),
                'Investing' => rand(50000, 500000),
                'Financing' => rand(500000, 5000000),
            ],
            'Income' => [
                'Operating' => rand(100000, 1000000),
                'Investing' => rand(50000, 500000),
                'Financing' => rand(10000, 100000),
            ],
            'Expense' => [
                'Operating' => rand(50000, 500000),
                'Investing' => rand(20000, 200000),
                'Financing' => rand(10000, 100000),
            ],
        ];

        return $baseAmounts[$type][$category] ?? rand(10000, 100000);
    }

    private function getBranchMultiplier($branchName)
    {
        $multipliers = [
            'Main Branch - Makati' => 1.5,    // Largest branch
            'Quezon City Branch' => 1.3,      // Second largest
            'Cebu City Branch' => 1.2,        // Regional center
            'Davao City Branch' => 1.1,       // Growing branch
            'Baguio City Branch' => 0.9,      // Smaller branch
            'Iloilo City Branch' => 0.8,      // Smallest branch
        ];

        return $multipliers[$branchName] ?? 1.0;
    }

    private function getMonthMultiplier($month)
    {
        $multipliers = [
            'January' => 0.8,   // Post-holiday slowdown
            'February' => 0.9,  // Still recovering
            'March' => 1.0,     // Normal
            'April' => 1.1,     // Tax season
            'May' => 1.2,       // Summer activity
            'June' => 1.1,      // Mid-year
        ];

        return $multipliers[$month] ?? 1.0;
    }
}
