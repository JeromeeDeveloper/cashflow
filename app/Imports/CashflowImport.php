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
        $cashflowData = [
            'products' => [] // Initialize products array
        ];
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
            $accountCode = trim($row[0] ?? '');  // Column A: account_code
            $accountName = trim($row[1] ?? '');  // Column B: account_name
            $actualAmount = trim($row[2] ?? ''); // Column C: actual_amount

            // Skip empty rows
            if (empty($accountCode) && empty($accountName) && empty($actualAmount)) {
                continue;
            }

            // Check for section headers (these might be in column A or B)
            if (str_contains(strtoupper($accountCode), 'CASH BEGINNING BALANCE') || str_contains(strtoupper($accountName), 'CASH BEGINNING BALANCE')) {
                $currentSection = 'beginning_balance';
                $cashflowData['cash_beginning_balance'] = $this->extractNumericValue($actualAmount);
                continue;
            }

            if (str_contains(strtoupper($accountCode), 'TOTAL CASH AVAILABLE') || str_contains(strtoupper($accountName), 'TOTAL CASH AVAILABLE')) {
                $currentSection = 'cash_available';
                $cashflowData['total_cash_available'] = $this->extractNumericValue($actualAmount);
                continue;
            }

            if (str_contains(strtoupper($accountCode), 'LESS: DISBURSEMENTS') || str_contains(strtoupper($accountCode), 'LESS DISBURSEMENTS') ||
                str_contains(strtoupper($accountName), 'LESS: DISBURSEMENTS') || str_contains(strtoupper($accountName), 'LESS DISBURSEMENTS')) {
                $currentSection = 'disbursements';
                continue;
            }

            if (str_contains(strtoupper($accountCode), 'TOTAL DISBURSEMENTS') || str_contains(strtoupper($accountName), 'TOTAL DISBURSEMENTS')) {
                $currentSection = 'total_disbursements';
                $cashflowData['total_disbursements'] = $this->extractNumericValue($actualAmount);
                continue;
            }

            if (str_contains(strtoupper($accountCode), 'CASH ENDING BALANCE') || str_contains(strtoupper($accountName), 'CASH ENDING BALANCE')) {
                $currentSection = 'ending_balance';
                $cashflowData['cash_ending_balance'] = $this->extractNumericValue($actualAmount);
                continue;
            }

            // Process GL account entries (must have account name and actual amount)
            Log::info("Checking row: Code='{$accountCode}', Name='{$accountName}', Amount='{$actualAmount}', IsNumeric=" . ($this->isNumeric($actualAmount) ? 'true' : 'false'));

            if (!empty($accountName) && !empty($actualAmount) && $this->isNumeric($actualAmount)) {
                $amount = $this->extractNumericValue($actualAmount);

                Log::info("Processing account: Code='{$accountCode}', Name='{$accountName}', Amount='{$amount}'");

                // Find or create GL account using account code and name
                $glAccount = $this->findOrCreateGLAccount($accountCode, $accountName);

                Log::info("GL Account result: ID={$glAccount->id}, Code='{$glAccount->account_code}', Name='{$glAccount->account_name}'");

                $cashflowData['products'][] = [
                    'gl_account_id' => $glAccount->id,
                    'account_name' => $accountName,
                    'actual_amount' => $amount,
                    'section' => $currentSection
                ];

                Log::info("Added product to array: " . json_encode(end($cashflowData['products'])));
            }
        }

        // Log the collected data before storing
        Log::info("Collected cashflow data: " . json_encode($cashflowData));

        // Store the processed data for the specific branch
        $this->storeCashflowData($cashflowData, $cooperativeName, $reportTitle);
    }

    /**
     * Find existing GL account by code/name or create new one with hierarchical support
     */
    private function findOrCreateGLAccount($accountCode, $accountName)
    {
        // Handle hierarchical accounts (e.g., "Loan Collection > Principal")
        if (str_contains($accountName, '>')) {
            return $this->handleHierarchicalAccount($accountCode, $accountName);
        }

        // First try to find by account code (exact match)
        if (!empty($accountCode)) {
            $glAccount = GLAccount::where('account_code', $accountCode)->first();
            if ($glAccount) {
                return $glAccount;
            }
        }

        // Try to find existing GL account by name (exact match first)
        $glAccount = GLAccount::where('account_name', $accountName)->first();
        if ($glAccount) {
            return $glAccount;
        }

        // Try partial name match
        $glAccount = GLAccount::where('account_name', 'LIKE', '%' . $accountName . '%')
            ->orWhere('account_name', 'LIKE', '%' . strtoupper($accountName) . '%')
            ->first();

        if ($glAccount) {
            return $glAccount;
        }

        // Try partial match with words
        $words = explode(' ', strtoupper($accountName));
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
        $finalAccountCode = !empty($accountCode) ? $accountCode : $this->generateAccountCode();
        $glAccount = GLAccount::create([
            'account_code' => $finalAccountCode,
            'account_name' => $accountName,
            'account_type' => 'detail',
            'level' => 0,
        ]);

        return $glAccount;
    }

    /**
     * Handle hierarchical account creation (e.g., "Loan Collection : > Principal")
     */
    private function handleHierarchicalAccount($accountCode, $accountName)
    {
        Log::info("Raw hierarchical account name: '{$accountName}'");

        $parts = array_map('trim', explode('>', $accountName));
        Log::info("Split parts: " . json_encode($parts));

        if (count($parts) < 2) {
            // Not a valid hierarchical structure, treat as regular account
            Log::info("Not enough parts for hierarchical structure, treating as regular account");
            return $this->findOrCreateGLAccount($accountCode, $accountName);
        }

                // Clean parent name: remove colon and extra spaces
        $rawParentName = $parts[0];
        $parentName = trim(str_replace([':', '  '], ['', ' '], $rawParentName));
        $childName = trim($parts[1]);

        Log::info("Raw parent name: '{$rawParentName}'");
        Log::info("Cleaned parent name: '{$parentName}'");
        Log::info("Child name: '{$childName}'");

        // Additional debugging for parent name
        Log::info("Parent name length: " . strlen($parentName));
        Log::info("Parent name is empty: " . (empty($parentName) ? 'true' : 'false'));
        Log::info("Parent name is null: " . (is_null($parentName) ? 'true' : 'false'));
        Log::info("Parent name trimmed result: '" . trim($parentName) . "'");

        Log::info("Processing hierarchical account: Parent='{$parentName}', Child='{$childName}'");

        // Find or create parent account
        $parentAccount = $this->findOrCreateParentAccount($parentName);
        Log::info("Parent account result: ID={$parentAccount->id}, Name='{$parentAccount->account_name}', Type='{$parentAccount->account_type}'");

        // Find or create child account
        $childAccount = $this->findOrCreateChildAccount($childName, $parentAccount->id);
        Log::info("Child account result: ID={$childAccount->id}, Name='{$childAccount->account_name}', Parent_ID={$childAccount->parent_id}");

        return $childAccount;
    }

    /**
     * Find or create parent account
     */
    private function findOrCreateParentAccount($parentName)
    {
        Log::info("Looking for parent account: '{$parentName}'");
        Log::info("Parent name length: " . strlen($parentName));
        Log::info("Parent name bytes: " . bin2hex($parentName));
        Log::info("Parent name trimmed: '" . trim($parentName) . "'");

        // Test database connection and table
        try {
            $testResult = DB::table('gl_accounts')->select('id')->limit(1)->get();
            Log::info("Database connection test successful, table accessible");
        } catch (\Exception $e) {
            Log::error("Database connection test failed: " . $e->getMessage());
        }

        // Try to find existing parent account by name (regardless of type)
        $parentAccount = GLAccount::where('account_name', $parentName)->first();

        if ($parentAccount) {
            Log::info("Found existing account: ID={$parentAccount->id}, Name='{$parentAccount->account_name}', Type='{$parentAccount->account_type}'");

            // If found, update it to be a parent account if it isn't already
            if ($parentAccount->account_type !== 'parent') {
                $parentAccount->update([
                    'account_type' => 'parent',
                    'level' => 0
                ]);
                Log::info("Updated account to parent type");
            }
            return $parentAccount;
        }

        Log::info("Parent account not found, creating new one");

        // Create new parent account
        $parentCode = $this->generateAccountCode();

        Log::info("Creating parent account with data: account_code='{$parentCode}', account_name='{$parentName}', account_type='parent', level=0");

        try {
            $dataToInsert = [
                'account_code' => $parentCode,
                'account_name' => $parentName,
                'account_type' => 'parent',
                'level' => 0,
            ];

            Log::info("About to insert parent account data: " . json_encode($dataToInsert));
            Log::info("Parent name value type: " . gettype($parentName));
            Log::info("Parent name is empty: " . (empty($parentName) ? 'true' : 'false'));
            Log::info("Parent name is null: " . (is_null($parentName) ? 'true' : 'false'));

            // Try to insert directly with DB to see if there's a model issue
            try {
                $insertedId = DB::table('gl_accounts')->insertGetId($dataToInsert);
                Log::info("Direct DB insert successful, ID: {$insertedId}");

                // Check what was actually stored in DB
                $rawDbCheck = DB::table('gl_accounts')->where('id', $insertedId)->first();
                Log::info("Raw DB check immediately after insert: " . json_encode($rawDbCheck));

                // Now fetch the created record with Eloquent
                $parentAccount = GLAccount::find($insertedId);
                Log::info("Fetched with Eloquent after direct DB insert - ID: {$parentAccount->id}, Name: '{$parentAccount->account_name}'");

                // Check if the model has the data
                Log::info("Model attributes after Eloquent fetch: " . json_encode($parentAccount->getAttributes()));

                // Try alternative fetch methods
                $parentAccount2 = GLAccount::where('id', $insertedId)->first();
                Log::info("Alternative fetch with where()->first() - ID: {$parentAccount2->id}, Name: '{$parentAccount2->account_name}'");

                $parentAccount3 = new GLAccount();
                $parentAccount3->setRawAttributes((array) $rawDbCheck);
                Log::info("Manual model creation from raw data - ID: {$parentAccount3->id}, Name: '{$parentAccount3->account_name}'");

            } catch (\Exception $e) {
                Log::error("Direct DB insert failed: " . $e->getMessage());

                // Fallback to model creation
                $parentAccount = GLAccount::create($dataToInsert);
                Log::info("GLAccount::create() completed successfully");
            }

            // Check what the model actually has after creation
            Log::info("Model after creation - ID: {$parentAccount->id}, Name: '{$parentAccount->account_name}', Code: '{$parentAccount->account_code}'");

            // Refresh from database to see what was actually stored
            $parentAccount->refresh();
            Log::info("Model after refresh - ID: {$parentAccount->id}, Name: '{$parentAccount->account_name}', Code: '{$parentAccount->account_code}'");

            // Also check if we can access the attributes directly
            Log::info("Model attributes: " . json_encode($parentAccount->getAttributes()));
        } catch (\Exception $e) {
            Log::error("Error creating parent account: " . $e->getMessage());
            Log::error("Data attempted: " . json_encode([
                'account_code' => $parentCode,
                'account_name' => $parentName,
                'account_type' => 'parent',
                'level' => 0,
            ]));
            throw $e;
        }

        Log::info("Created parent GL account: {$parentName} with ID: {$parentAccount->id}");

                    // Verify what was actually stored
            $storedAccount = GLAccount::find($parentAccount->id);
            Log::info("Verification - Stored account: ID={$storedAccount->id}, Code='{$storedAccount->account_code}', Name='{$storedAccount->account_name}', Type='{$storedAccount->account_type}', Level={$storedAccount->level}");

            // Direct database query to see what's actually stored
            $rawDbResult = DB::table('gl_accounts')->where('id', $parentAccount->id)->first();
            Log::info("Raw DB query result: " . json_encode($rawDbResult));

        return $parentAccount;
    }

    /**
     * Find or create child account
     */
    private function findOrCreateChildAccount($childName, $parentId)
    {
        // Try to find existing child account
        $childAccount = GLAccount::where('account_name', $childName)
            ->where('parent_id', $parentId)
            ->first();

        if ($childAccount) {
            return $childAccount;
        }

        // Create new child account
        $childCode = $this->generateAccountCode();

        Log::info("Creating child account with data: account_code='{$childCode}', account_name='{$childName}', parent_id='{$parentId}', account_type='detail', level=1");

        try {
            $childAccount = GLAccount::create([
                'account_code' => $childCode,
                'account_name' => $childName,
                'parent_id' => $parentId,
                'account_type' => 'detail',
                'level' => 1,
            ]);
            Log::info("GLAccount::create() for child completed successfully");
        } catch (\Exception $e) {
            Log::error("Error creating child account: " . $e->getMessage());
            Log::error("Data attempted: " . json_encode([
                'account_code' => $childCode,
                'account_name' => $childName,
                'parent_id' => $parentId,
                'account_type' => 'detail',
                'level' => 1,
            ]));
            throw $e;
        }

        Log::info("Created child GL account: {$childName} with parent: {$parentId}");

        // Verify what was actually stored
        $storedChildAccount = GLAccount::find($childAccount->id);
        Log::info("Verification - Stored child account: ID={$storedChildAccount->id}, Code='{$storedChildAccount->account_code}', Name='{$storedChildAccount->account_name}', Parent_ID='{$storedChildAccount->parent_id}', Type='{$storedChildAccount->account_type}', Level={$storedChildAccount->level}");

        return $childAccount;
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
        $isNumeric = is_numeric($cleanValue);

        Log::info("isNumeric check: '{$value}' -> cleaned: '{$cleanValue}' -> result: " . ($isNumeric ? 'true' : 'false'));

        return $isNumeric;
    }

    /**
     * Determine cashflow category based on section
     */

    /**
     * Store the processed cashflow data for the specific branch
     */
    private function storeCashflowData($cashflowData, $cooperativeName, $reportTitle)
    {
        try {
            DB::beginTransaction();

            // Summary records are not needed when importing actual data
            // Only store the actual product/category entries from Excel

            // Store individual product/category entries for the specific branch
            if (isset($cashflowData['products']) && is_array($cashflowData['products'])) {
                Log::info("Found " . count($cashflowData['products']) . " products to store");

                foreach ($cashflowData['products'] as $index => $product) {
                    Log::info("Storing product {$index}: " . json_encode($product));

                    $productData = [
                        'cashflow_file_id' => $this->cashflowFile->id,
                        'branch_id' => $this->branchId,
                        'gl_account_id' => $product['gl_account_id'],
                        'year' => $this->year,
                        'month' => $this->month,
                        'period' => "{$this->month} {$this->year}",
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
                    ];

                    Log::info("Creating cashflow with data: " . json_encode($productData));

                    $cashflow = Cashflow::create($productData);
                    Log::info("Created cashflow with ID: {$cashflow->id}");
                }
            } else {
                Log::warning("No products found in cashflowData: " . json_encode($cashflowData));
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
