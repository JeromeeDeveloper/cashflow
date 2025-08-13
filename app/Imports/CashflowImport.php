<?php

namespace App\Imports;

use App\Models\Cashflow;
use App\Models\CashflowFile;
use App\Models\GLAccount;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashflowImport implements ToCollection, WithStartRow
{
    protected $cashflowFile;
    protected $branchId;
    protected $year;
    protected $month;

    public function __construct(CashflowFile $cashflowFile, $branchId, $year, $month)
    {
        $this->cashflowFile = $cashflowFile;
        $this->branchId = $branchId;
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * Start reading from row 3 (after the header rows)
     */
    public function startRow(): int
    {
        return 3;
    }

    /**
     * Process the Excel data
     */
    public function collection(Collection $rows)
    {
        $cashflowData = [];
        $currentSection = null;
        $cooperativeName = '';
        $reportTitle = '';

        // Extract cooperative name and report title from first two rows
        if (isset($rows[0]) && isset($rows[0][0])) {
            $cooperativeName = $rows[0][0] ?? '';
        }
        if (isset($rows[1]) && isset($rows[1][0])) {
            $reportTitle = $rows[1][0] ?? '';
        }

        foreach ($rows as $index => $row) {
            $columnA = trim($row[0] ?? '');
            $columnB = trim($row[1] ?? '');
            $columnC = trim($row[2] ?? '');

            // Skip empty rows
            if (empty($columnA) && empty($columnB) && empty($columnC)) {
                continue;
            }

            // Check for section headers
            if (str_contains(strtoupper($columnA), 'CASH BEGINNING BALANCE')) {
                $currentSection = 'beginning_balance';
                $cashflowData['cash_beginning_balance'] = $this->extractNumericValue($columnB);
                continue;
            }

            if (str_contains(strtoupper($columnA), 'TOTAL CASH AVAILABLE')) {
                $currentSection = 'cash_available';
                $cashflowData['total_cash_available'] = $this->extractNumericValue($columnB);
                continue;
            }

            if (str_contains(strtoupper($columnA), 'LESS: DISBURSEMENTS') || str_contains(strtoupper($columnA), 'LESS DISBURSEMENTS')) {
                $currentSection = 'disbursements';
                continue;
            }

            if (str_contains(strtoupper($columnA), 'TOTAL DISBURSEMENTS')) {
                $currentSection = 'total_disbursements';
                $cashflowData['total_disbursements'] = $this->extractNumericValue($columnB);
                continue;
            }

            if (str_contains(strtoupper($columnA), 'CASH ENDING BALANCE')) {
                $currentSection = 'ending_balance';
                $cashflowData['cash_ending_balance'] = $this->extractNumericValue($columnB);
                continue;
            }

            // Process product/category entries
            if (!empty($columnB) && !empty($columnC) && $this->isNumeric($columnC)) {
                $productName = $columnB;
                $amount = $this->extractNumericValue($columnC);

                // Find or create GL account
                $glAccount = $this->findOrCreateGLAccount($productName);

                $cashflowData['products'][] = [
                    'gl_account_id' => $glAccount->id,
                    'account_name' => $productName,
                    'actual_amount' => $amount,
                    'cashflow_category' => $this->determineCategory($currentSection, $productName),
                    'section' => $currentSection
                ];
            }
        }

        // Store the processed data for the specific branch
        $this->storeCashflowData($cashflowData, $cooperativeName, $reportTitle);
    }

    /**
     * Find existing GL account by name or create new one
     */
    private function findOrCreateGLAccount($productName)
    {
        // Try to find existing GL account by name
        $glAccount = GLAccount::where('account_name', 'LIKE', '%' . $productName . '%')
            ->orWhere('account_name', 'LIKE', '%' . strtoupper($productName) . '%')
            ->first();

        if ($glAccount) {
            return $glAccount;
        }

        // Try partial match
        $words = explode(' ', strtoupper($productName));
        foreach ($words as $word) {
            if (strlen($word) > 3) { // Only search for words longer than 3 characters
                $glAccount = GLAccount::where('account_name', 'LIKE', '%' . $word . '%')
                    ->first();
                if ($glAccount) {
                    return $glAccount;
                }
            }
        }

        // Create new GL account if not found
        $accountCode = $this->generateAccountCode();
        $glAccount = GLAccount::create([
            'account_code' => $accountCode,
            'account_name' => $productName,
        ]);

        return $glAccount;
    }

    /**
     * Generate unique account code
     */
    private function generateAccountCode()
    {
        $lastAccount = GLAccount::orderBy('account_code', 'desc')->first();

        if ($lastAccount) {
            // Try to extract number from last account code
            $lastNumber = (int) preg_replace('/[^0-9]/', '', $lastAccount->account_code);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1000; // Start with 1000
        }

        return (string) $nextNumber;
    }

    /**
     * Extract numeric value from string
     */
    private function extractNumericValue($value)
    {
        if (empty($value)) {
            return 0;
        }

        // Remove currency symbols, commas, and spaces
        $cleanValue = preg_replace('/[^\d.-]/', '', $value);

        return is_numeric($cleanValue) ? (float) $cleanValue : 0;
    }

    /**
     * Check if value is numeric
     */
    private function isNumeric($value)
    {
        if (empty($value)) {
            return false;
        }

        $cleanValue = preg_replace('/[^\d.-]/', '', $value);
        return is_numeric($cleanValue);
    }

    /**
     * Determine cashflow category based on section and product name
     */
    private function determineCategory($section, $productName)
    {
        $productName = strtoupper($productName);

        // Operating activities
        if (str_contains($productName, 'SALES') ||
            str_contains($productName, 'REVENUE') ||
            str_contains($productName, 'INCOME') ||
            str_contains($productName, 'SERVICE') ||
            str_contains($productName, 'FEE')) {
            return 'Operating';
        }

        // Investing activities
        if (str_contains($productName, 'EQUIPMENT') ||
            str_contains($productName, 'BUILDING') ||
            str_contains($productName, 'INVESTMENT') ||
            str_contains($productName, 'ASSET') ||
            str_contains($productName, 'PROPERTY')) {
            return 'Investing';
        }

        // Financing activities
        if (str_contains($productName, 'LOAN') ||
            str_contains($productName, 'CAPITAL') ||
            str_contains($productName, 'SHARE') ||
            str_contains($productName, 'DIVIDEND') ||
            str_contains($productName, 'BORROWING')) {
            return 'Financing';
        }

        // Default based on section
        switch ($section) {
            case 'beginning_balance':
            case 'cash_available':
            case 'ending_balance':
                return 'Operating';
            case 'disbursements':
            case 'total_disbursements':
                return 'Operating';
            default:
                return 'Operating';
        }
    }

    /**
     * Store the processed cashflow data for the specific branch
     */
    private function storeCashflowData($cashflowData, $cooperativeName, $reportTitle)
    {
        try {
            DB::beginTransaction();

            // Create or find a summary GL account
            $summaryGLAccount = GLAccount::firstOrCreate(
                ['account_code' => 'SUMMARY'],
                ['account_name' => 'Cash Flow Summary']
            );

            // Store summary data for the specific branch - only if we have summary data
            if (isset($cashflowData['cash_beginning_balance']) ||
                isset($cashflowData['total_cash_available']) ||
                isset($cashflowData['total_disbursements']) ||
                isset($cashflowData['cash_ending_balance'])) {

                $summaryData = [
                    'cashflow_file_id' => $this->cashflowFile->id,
                    'branch_id' => $this->branchId,
                    'gl_account_id' => $summaryGLAccount->id,
                    'year' => $this->year,
                    'month' => $this->month,
                    'period' => "{$this->month} {$this->year}",
                    'account_type' => 'Summary',
                    'cashflow_category' => 'Operating',
                    'actual_amount' => null,
                    'projection_percentage' => null,
                    'projected_amount' => null,
                    'period_values' => json_encode([
                        'cooperative_name' => $cooperativeName ?? '',
                        'report_title' => $reportTitle ?? '',
                        'cash_beginning_balance' => $cashflowData['cash_beginning_balance'] ?? null,
                        'total_cash_available' => $cashflowData['total_cash_available'] ?? null,
                        'total_disbursements' => $cashflowData['total_disbursements'] ?? null,
                        'cash_ending_balance' => $cashflowData['cash_ending_balance'] ?? null,
                    ]),
                    'total' => null,
                    'cash_beginning_balance' => $cashflowData['cash_beginning_balance'] ?? null,
                    'total_cash_available' => $cashflowData['total_cash_available'] ?? null,
                    'less_disbursements' => $cashflowData['total_disbursements'] ?? null,
                    'total_disbursements' => $cashflowData['total_disbursements'] ?? null,
                    'cash_ending_balance' => $cashflowData['cash_ending_balance'] ?? null,
                    'grand_total' => null,
                ];

                Cashflow::create($summaryData);
            }

            // Store individual product/category entries for the specific branch
            if (isset($cashflowData['products']) && is_array($cashflowData['products'])) {
                foreach ($cashflowData['products'] as $product) {
                    $productData = [
                        'cashflow_file_id' => $this->cashflowFile->id,
                        'branch_id' => $this->branchId,
                        'gl_account_id' => $product['gl_account_id'],
                        'year' => $this->year,
                        'month' => $this->month,
                        'period' => "{$this->month} {$this->year}",
                        'account_type' => 'GL Account',
                        'cashflow_category' => $product['cashflow_category'],
                        'actual_amount' => $product['actual_amount'],
                        'projection_percentage' => null,
                        'projected_amount' => null,
                        'period_values' => json_encode([
                            'section' => $product['section'],
                            'amount' => $product['actual_amount'],
                            'gl_account_id' => $product['gl_account_id']
                        ]),
                        'total' => $product['actual_amount'],
                        'cash_beginning_balance' => null,
                        'total_cash_available' => null,
                        'less_disbursements' => null,
                        'total_disbursements' => null,
                        'cash_ending_balance' => null,
                        'grand_total' => null,
                    ];

                    Cashflow::create($productData);
                }
            }

            // Update cashflow file status
            $this->cashflowFile->update([
                'status' => 'processed',
                'description' => "Processed cashflow data for {$this->month} {$this->year}"
            ]);

            DB::commit();

            Log::info("Cashflow data imported successfully for branch {$this->branchId} from file: {$this->cashflowFile->file_name}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error importing cashflow data: " . $e->getMessage());
            throw $e;
        }
    }
}
