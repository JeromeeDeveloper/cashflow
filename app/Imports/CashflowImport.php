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
    protected $periodType;
    protected $week;

    public function __construct(CashflowFile $cashflowFile, $branchId, $year, $month, $periodType = 'monthly', $week = null)
    {
        $this->cashflowFile = $cashflowFile;
        $this->branchId = $branchId;
        $this->year = $year;
        $this->month = $month;
        $this->periodType = $periodType;
        $this->week = $week;
    }

    /**
     * Start reading from row 1 (data starts immediately)
     */
    public function startRow(): int
    {
        return 1;
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

            // Skip "Prepared by:" rows
            if (str_contains(strtolower($accountCode), 'prepared by') || str_contains(strtolower($accountName), 'prepared by')) {
                Log::info("Skipping 'Prepared by:' row: Code='{$accountCode}', Name='{$accountName}'");
                continue;
            }

            // Skip "TOTAL CASH BALANCE (END)" row
            if (str_contains(strtoupper($accountCode), 'TOTAL CASH BALANCE (END)') || str_contains(strtoupper($accountName), 'TOTAL CASH BALANCE (END)')) {
                Log::info("Skipping 'TOTAL CASH BALANCE (END)' row: Code='{$accountCode}', Name='{$accountName}'");
                continue;
            }

            // Check for section headers (these might be in column A or B)
            if (str_contains(strtoupper($accountCode), 'CASH BEGINNING BALANCE') || str_contains(strtoupper($accountName), 'CASH BEGINNING BALANCE')) {
                $currentSection = 'beginning_balance';
                $cashflowData['cash_beginning_balance'] = $this->extractNumericValue($actualAmount);
                continue;
            }

            // Check for ADD: RECEIPTS section
            if (str_contains(strtoupper($accountCode), 'ADD: RECEIPTS') || str_contains(strtoupper($accountName), 'ADD: RECEIPTS') ||
                str_contains(strtoupper($accountCode), 'ADD RECEIPTS') || str_contains(strtoupper($accountName), 'ADD RECEIPTS')) {
                $currentSection = 'receipts';
                Log::info("Entering RECEIPTS section");
                continue;
            }

            if (str_contains(strtoupper($accountCode), 'TOTAL CASH AVAILABLE') || str_contains(strtoupper($accountName), 'TOTAL CASH AVAILABLE')) {
                $currentSection = 'cash_available';
                $cashflowData['total_cash_available'] = $this->extractNumericValue($actualAmount);
                continue;
            }

            // Check for LESS: DISBURSEMENTS section
            if (str_contains(strtoupper($accountCode), 'LESS: DISBURSEMENTS') || str_contains(strtoupper($accountCode), 'LESS DISBURSEMENTS') ||
                str_contains(strtoupper($accountName), 'LESS: DISBURSEMENTS') || str_contains(strtoupper($accountName), 'LESS DISBURSEMENTS')) {
                $currentSection = 'disbursements';
                Log::info("Entering DISBURSEMENTS section");
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

            // Process GL account entries
            Log::info("Checking row: Code='{$accountCode}', Name='{$accountName}', Amount='{$actualAmount}', Section='{$currentSection}', IsNumeric=" . ($this->isNumeric($actualAmount) ? 'true' : 'false'));

            // Skip empty rows
            if (empty($accountName)) {
                continue;
            }

            // Determine account type based on structure
            $accountType = $this->determineAccountType($rows, $index);
            Log::info("Account type determined: '{$accountType}' for '{$accountName}'");
            Log::info("Row index: {$index}, Account name: '{$accountName}', Ends with colon: " . (substr(trim($accountName), -1) === ':' ? 'true' : 'false'));

            // Handle child accounts (those starting with >)
            if (str_starts_with(trim($accountName), '>')) {
                Log::info("Processing child account: Code='{$accountCode}', Name='{$accountName}'");

                // Find or create GL account (this will handle hierarchical structure)
                $glAccount = $this->findOrCreateGLAccount($accountCode, $accountName, 'child', $currentSection === 'receipts' ? 'receipts' : 'disbursements');

                Log::info("Child GL Account result: ID={$glAccount->id}, Code='{$glAccount->account_code}', Name='{$glAccount->account_name}'");

                // If child row has an amount, store it as a product
                if (!empty($actualAmount) && $this->isNumeric($actualAmount)) {
                    $amount = $this->extractNumericValue($actualAmount);
                    $cashflowType = $currentSection === 'receipts' ? 'receipts' : 'disbursements';

                    $cashflowData['products'][] = [
                        'gl_account_id' => $glAccount->id,
                        'account_name' => trim(str_replace('>', '', $accountName)),
                        'actual_amount' => $amount,
                        'section' => $currentSection,
                        'cashflow_type' => $cashflowType
                    ];
                    Log::info("Added CHILD product to array: " . json_encode(end($cashflowData['products'])));
                }

                // Do not treat child header lines without amount as products
                continue;
            }

            // Handle parent accounts (those that have children below them)
            if ($accountType === 'parent') {
                Log::info("Processing parent account: Code='{$accountCode}', Name='{$accountName}'");

                // Find or create GL account (this will handle hierarchical structure)
                $glAccount = $this->findOrCreateGLAccount($accountCode, $accountName, 'parent', $currentSection === 'receipts' ? 'receipts' : 'disbursements');

                Log::info("Parent GL Account result: ID={$glAccount->id}, Code='{$glAccount->account_code}', Name='{$glAccount->account_name}'");

                // Don't add parent accounts to products array - they're just headers
                continue;
            }

            // Process single accounts (must have account name and actual amount)
            if (!empty($actualAmount) && $this->isNumeric($actualAmount)) {
                $amount = $this->extractNumericValue($actualAmount);

                Log::info("Processing single account: Code='{$accountCode}', Name='{$accountName}', Amount='{$amount}', Section='{$currentSection}'");

                // Find or create GL account using account code and name
                $glAccount = $this->findOrCreateGLAccount($accountCode, $accountName, $accountType, $currentSection === 'receipts' ? 'receipts' : 'disbursements');

                Log::info("Single GL Account result: ID={$glAccount->id}, Code='{$glAccount->account_code}', Name='{$glAccount->account_name}'");

                // Determine cashflow type based on current section
                $cashflowType = 'disbursements'; // default
                if ($currentSection === 'receipts') {
                    $cashflowType = 'receipts';
                } elseif ($currentSection === 'disbursements') {
                    $cashflowType = 'disbursements';
                }

                $cashflowData['products'][] = [
                    'gl_account_id' => $glAccount->id,
                    'account_name' => $accountName,
                    'actual_amount' => $amount,
                    'section' => $currentSection,
                    'cashflow_type' => $cashflowType
                ];

                Log::info("Added product to array: " . json_encode(end($cashflowData['products'])));
            }
        }

        // Log the collected data before storing
        Log::info("Collected cashflow data: " . json_encode($cashflowData));

        // Update parent-child relationships
        $this->updateParentChildRelationships($rows);

        // Store the processed data for the specific branch
        $this->storeCashflowData($cashflowData, $cooperativeName, $reportTitle);
    }

    /**
     * Find existing GL account by code/name or create new one with hierarchical support
     */
    private function findOrCreateGLAccount($accountCode, $accountName, $accountType = null, $cashflowType = null)
    {
        // Handle child accounts (those starting with >)
        if (str_starts_with(trim($accountName), '>')) {
            return $this->handleChildAccount($accountCode, $accountName, $cashflowType);
        }

        // First try to find by account code and cashflow type (composite unique constraint)
        if (!empty($accountCode)) {
            $glAccount = GLAccount::where('account_code', $accountCode)
                ->where('cashflow_type', $cashflowType ?? 'disbursements')
                ->first();
            if ($glAccount) {
                return $glAccount;
            }
        }

        // Only if no account code is provided, try to find by name and cashflow type
        // But be more strict - only exact name matches to avoid duplicates
        if (empty($accountCode)) {
            $glAccount = GLAccount::where('account_name', $accountName)
                ->where('cashflow_type', $cashflowType ?? 'disbursements')
                ->first();
            if ($glAccount) {
                return $glAccount;
            }
        }

        // Create new GL account if not found
        $finalAccountCode = !empty($accountCode) ? $accountCode : $this->generateAccountCode();

        // Use the passed account type, or determine based on name if not provided
        if ($accountType === null) {
            $accountType = 'single';
            $level = 0;

            if (str_starts_with(trim($accountName), '>')) {
                $accountType = 'child';
                $level = 1;
            }
        } else {
            // Set level based on account type
            $level = ($accountType === 'child') ? 1 : 0;
        }

        Log::info("Creating GL account: '{$accountName}' with type: '{$accountType}', level: {$level}");

        $glAccount = GLAccount::create([
            'account_code' => $finalAccountCode,
            'account_name' => $accountName,
            'account_type' => $accountType,
            'level' => $level,
            'cashflow_type' => $cashflowType ?? 'disbursements', // Set cashflow type based on section
        ]);

        return $glAccount;
    }

    /**
     * Handle child account creation (e.g., "> Principal")
     */
    private function handleChildAccount($accountCode, $accountName, $cashflowType = null)
    {
        Log::info("Processing child account: '{$accountName}'");

        // Clean child name (remove > and spaces)
        $childName = trim(str_replace('>', '', $accountName));

        if (empty($childName)) {
            Log::info("Child name is empty after cleaning, skipping");
            return null;
        }

        Log::info("Cleaned child name: '{$childName}'");

        // Try to find existing child account with same cashflow type
        $childAccount = GLAccount::where('account_name', $childName)
            ->where('account_type', 'child')
            ->where('cashflow_type', $cashflowType ?? 'disbursements')
            ->first();

        if ($childAccount) {
            Log::info("Found existing child account: ID={$childAccount->id}, Name='{$childAccount->account_name}'");
            return $childAccount;
        }

        // Create new child account (parent_id will be set later when we find the parent)
        $childCode = $this->generateAccountCode();

        Log::info("Creating new child account: '{$childName}' with code: '{$childCode}'");

        $childAccount = GLAccount::create([
            'account_code' => $childCode,
            'account_name' => $childName,
            'account_type' => 'child',
            'level' => 1,
            'parent_id' => null, // Will be updated when parent is found
            'cashflow_type' => $cashflowType ?? 'disbursements', // Inherit cashflow type from parent section
        ]);

        Log::info("Created child account: ID={$childAccount->id}, Name='{$childAccount->account_name}'");

        return $childAccount;
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
            return $this->findOrCreateGLAccount($accountCode, $accountName, 'single');
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
     * Update parent-child relationships after processing all accounts
     */
    private function updateParentChildRelationships($rows)
    {
        Log::info("Updating parent-child relationships");

        $currentParent = null;

        foreach ($rows as $index => $row) {
            $accountName = trim($row[1] ?? ''); // Column B

            if (empty($accountName)) {
                continue;
            }

            Log::info("Processing row {$index}: '{$accountName}'");

            // If this is a parent account (has children below or ends with colon)
            if ($this->determineAccountType($rows, $index) === 'parent') {
                // For now, we'll find parent accounts without cashflow_type filter
                // since the relationship update happens after all accounts are created
                $currentParent = GLAccount::where('account_name', $accountName)
                    ->where('account_type', 'parent')
                    ->first();

                if ($currentParent) {
                    Log::info("Found parent account: '{$accountName}' with ID: {$currentParent->id}");
                } else {
                    Log::warning("Parent account '{$accountName}' not found in database");
                }
            }

            // If this is a child account (starts with >)
            if (str_starts_with($accountName, '>')) {
                $childName = trim(str_replace('>', '', $accountName));
                Log::info("Processing child account: '{$childName}'");

                if ($currentParent) {
                    // Update child account with parent_id - find child with matching cashflow_type
                    $childAccount = GLAccount::where('account_name', $childName)
                        ->where('account_type', 'child')
                        ->where('cashflow_type', $currentParent->cashflow_type)
                        ->first();

                    if ($childAccount) {
                        $childAccount->update(['parent_id' => $currentParent->id]);
                        Log::info("Updated child account '{$childName}' with parent_id: {$currentParent->id}");
                    } else {
                        Log::warning("Child account '{$childName}' not found in database");
                    }
                } else {
                    Log::warning("Child account '{$childName}' has no parent assigned");
                }
            }
        }

        Log::info("Parent-child relationships updated");
    }

    /**
     * Find or create parent account
     */
    private function findOrCreateParentAccount($parentName, $cashflowType = null)
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

        // Try to find existing parent account by name and cashflow type
        $parentAccount = GLAccount::where('account_name', $parentName)
            ->where('cashflow_type', $cashflowType ?? 'disbursements')
            ->first();

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
                'cashflow_type' => $cashflowType ?? 'disbursements',
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

        Log::info("Creating child account with data: account_code='{$childCode}', account_name='{$childName}', parent_id='{$parentId}', account_type='child', level=1");

        try {
            $childAccount = GLAccount::create([
                'account_code' => $childCode,
                'account_name' => $childName,
                'parent_id' => $parentId,
                'account_type' => 'child',
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
     * Determine account type based on structure
     */
    private function determineAccountType($rows, $currentIndex)
    {
        $currentRow = $rows[$currentIndex];
        $currentAccountName = trim($currentRow[1] ?? ''); // Column B

        // If current row starts with >, it's a child
        if (str_starts_with($currentAccountName, '>')) {
            return 'child';
        }

        // Direct check: If account name ends with colon, it's likely a parent
        if (substr(trim($currentAccountName), -1) === ':') {
            Log::info("Account '{$currentAccountName}' ends with colon, checking for children below...");

            // Look ahead to confirm it has children
            for ($i = $currentIndex + 1; $i < count($rows); $i++) {
                $nextRow = $rows[$i];
                $nextAccountName = trim($nextRow[1] ?? ''); // Column B

                Log::info("Checking row {$i}: '{$nextAccountName}'");

                // Skip empty rows
                if (empty($nextAccountName)) {
                    Log::info("Row {$i} is empty, continuing...");
                    continue;
                }

                // If we find a child account (starts with >), then current is a parent
                if (str_starts_with($nextAccountName, '>')) {
                    Log::info("Found child account '{$nextAccountName}', so '{$currentAccountName}' is a parent");
                    return 'parent';
                }

                // If we find another main account (no >), then current is not a parent
                if (!str_starts_with($nextAccountName, '>')) {
                    Log::info("Found main account '{$nextAccountName}', so '{$currentAccountName}' is not a parent");
                    break;
                }
            }

            // Even if no children found, colon indicates it's a parent (for future use)
            Log::info("No children found for '{$currentAccountName}', but colon indicates it's a parent");
            return 'parent';
        }

        // Look ahead to see if this account has children below (with >)
        Log::info("Checking if '{$currentAccountName}' has children below...");
        Log::info("Current row index: {$currentIndex}, Total rows: " . count($rows));

        for ($i = $currentIndex + 1; $i < count($rows); $i++) {
            $nextRow = $rows[$i];
            $nextAccountName = trim($nextRow[1] ?? ''); // Column B

            Log::info("Checking row {$i}: '{$nextAccountName}'");

            // Skip empty rows
            if (empty($nextAccountName)) {
                Log::info("Row {$i} is empty, continuing...");
                continue;
            }

            // If we find a child account (starts with >), then current is a parent
            if (str_starts_with($nextAccountName, '>')) {
                Log::info("Found child account '{$nextAccountName}', so '{$currentAccountName}' is a parent");
                return 'parent';
            }

            // If we find another main account (no >), then current is not a parent
            if (!str_starts_with($nextAccountName, '>')) {
                Log::info("Found main account '{$nextAccountName}', so '{$currentAccountName}' is not a parent");
                break;
            }
        }

        // If no children found, it's a single account
        Log::info("No children found for '{$currentAccountName}', so it's a single account");
        return 'single';
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

                    // Store amounts as-is (no conversion during upload)
                    $actualAmount = $product['actual_amount'];

                    $productData = [
                        'cashflow_file_id' => $this->cashflowFile->id,
                        'branch_id' => $this->branchId,
                        'gl_account_id' => $product['gl_account_id'],
                        'cashflow_type' => $product['cashflow_type'],
                        'section' => $product['section'], // Add section field
                        'year' => $this->year,
                        'month' => $this->month,
                        'period' => $this->periodType === 'weekly' ? "Week {$this->week} {$this->month} {$this->year}" : "{$this->month} {$this->year}",
                        'actual_amount' => $actualAmount,
                        'projection_percentage' => null,
                        'projected_amount' => null,
                        'period_values' => json_encode([
                            'section' => $product['section'],
                            'amount' => $product['actual_amount'],
                            'gl_account_id' => $product['gl_account_id'],
                            'cashflow_type' => $product['cashflow_type'],
                            'period_type' => $this->periodType,
                            'week' => $this->week
                        ]),
                        'total' => $actualAmount,
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
