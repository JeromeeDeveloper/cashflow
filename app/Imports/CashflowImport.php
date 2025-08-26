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
        // First, validate all account codes exist in GL Accounts table
        $validationResult = $this->validateAccountCodes($rows);
        if (!$validationResult['valid']) {
            throw new \Exception($validationResult['message']);
        }

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

                // Find GL account (this will handle hierarchical structure)
                $glAccount = $this->findOrCreateGLAccount($accountCode, $accountName, 'child', $currentSection === 'receipts' ? 'receipts' : 'disbursements');

                // Skip if GL account not found
                if (!$glAccount) {
                    Log::warning("Skipping child account '{$accountName}' - GL account not found");
                    continue;
                }

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

                // Find GL account (this will handle hierarchical structure)
                $glAccount = $this->findOrCreateGLAccount($accountCode, $accountName, 'parent', $currentSection === 'receipts' ? 'receipts' : 'disbursements');

                // Skip if GL account not found
                if (!$glAccount) {
                    Log::warning("Skipping parent account '{$accountName}' - GL account not found");
                    continue;
                }

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
     * Find existing GL account by code - do not create new ones
     */
    private function findOrCreateGLAccount($accountCode, $accountName, $accountType = null, $cashflowType = null)
    {
        // Handle child accounts (those starting with >)
        if (str_starts_with(trim($accountName), '>')) {
            return $this->handleChildAccount($accountCode, $accountName, $cashflowType);
        }

        // Find by account code (this should always exist since we validated it)
        if (!empty($accountCode)) {
            $glAccount = GLAccount::where('account_code', $accountCode)->first();
            if ($glAccount) {
                Log::info("Found existing GL account: ID={$glAccount->id}, Code='{$glAccount->account_code}', Name='{$glAccount->account_name}'");
                return $glAccount;
            }
        }

        // If we reach here, something went wrong with validation
        Log::error("GL Account not found for code: '{$accountCode}', name: '{$accountName}' - this should not happen after validation");
        throw new \Exception("GL Account with code '{$accountCode}' not found. Please ensure all account codes exist in GL Account Management.");
    }

    /**
     * Handle child account lookup (e.g., "> Principal") - do not create new ones
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

        // If child account not found, log warning and return null
        Log::warning("Child account not found: '{$childName}' with cashflow type '{$cashflowType}'. Skipping this entry.");
        return null;
    }

    /**
     * Handle hierarchical account lookup (e.g., "Loan Collection : > Principal") - do not create new ones
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

        // Find parent account
        $parentAccount = $this->findOrCreateParentAccount($parentName);
        if (!$parentAccount) {
            Log::warning("Parent account not found for hierarchical structure: '{$parentName}'. Skipping this entry.");
            return null;
        }

        Log::info("Parent account result: ID={$parentAccount->id}, Name='{$parentAccount->account_name}', Type='{$parentAccount->account_type}'");

        // Find child account
        $childAccount = $this->findOrCreateChildAccount($childName, $parentAccount->id);
        if (!$childAccount) {
            Log::warning("Child account not found for hierarchical structure: '{$childName}'. Skipping this entry.");
            return null;
        }

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
     * Find existing parent account - do not create new ones
     */
    private function findOrCreateParentAccount($parentName, $cashflowType = null)
    {
        Log::info("Looking for parent account: '{$parentName}'");

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

        // If parent account not found, log warning and return null
        Log::warning("Parent account not found: '{$parentName}' with cashflow type '{$cashflowType}'. Skipping this entry.");
        return null;
    }

    /**
     * Find existing child account - do not create new ones
     */
    private function findOrCreateChildAccount($childName, $parentId)
    {
        // Try to find existing child account
        $childAccount = GLAccount::where('account_name', $childName)
            ->where('parent_id', $parentId)
            ->first();

        if ($childAccount) {
            Log::info("Found existing child account: ID={$childAccount->id}, Name='{$childAccount->account_name}'");
            return $childAccount;
        }

        // If child account not found, log warning and return null
        Log::warning("Child account not found: '{$childName}' with parent ID '{$parentId}'. Skipping this entry.");
        return null;
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
     * Validate that all account codes exist in GL Accounts table
     */
    private function validateAccountCodes(Collection $rows)
    {
        $unmatchedCodes = [];
        $validRows = 0;

        foreach ($rows as $index => $row) {
            $accountCode = trim($row[0] ?? '');  // Column A: account_code
            $accountName = trim($row[1] ?? '');  // Column B: account_name
            $actualAmount = trim($row[2] ?? ''); // Column C: actual_amount

            // Skip empty rows
            if (empty($accountCode) && empty($accountName) && empty($actualAmount)) {
                continue;
            }

            // Skip section headers and special rows
            if ($this->isSectionHeader($accountCode, $accountName)) {
                continue;
            }

            // Skip "Prepared by:" and "TOTAL CASH BALANCE (END)" rows
            if (str_contains(strtolower($accountCode), 'prepared by') ||
                str_contains(strtolower($accountName), 'prepared by') ||
                str_contains(strtoupper($accountCode), 'TOTAL CASH BALANCE (END)') ||
                str_contains(strtoupper($accountName), 'TOTAL CASH BALANCE (END)')) {
                continue;
            }

            // Only validate rows that have account codes and names (actual GL account entries)
            if (!empty($accountCode) && !empty($accountName) && $this->isNumeric($actualAmount)) {
                $validRows++;

                // Check if account code exists in GL Accounts table
                $glAccount = GLAccount::where('account_code', $accountCode)->first();
                if (!$glAccount) {
                    $unmatchedCodes[] = [
                        'code' => $accountCode,
                        'name' => $accountName,
                        'row' => $index + 1
                    ];
                }
            }
        }

                if (!empty($unmatchedCodes)) {
            $message = "Validation failed! The following account codes were not found in the GL Accounts table:\n\n";
            foreach ($unmatchedCodes as $unmatched) {
                $message .= "Row {$unmatched['row']}: Code '{$unmatched['code']}' - '{$unmatched['name']}'\n";
            }
            $message .= "\nPlease add these accounts to the GL Account Management first, or contact Admin/Head Office for assistance.";
            $message .= "\n\nNote: Only existing GL accounts can be used for cashflow imports. No new accounts will be created automatically.";

            return [
                'valid' => false,
                'message' => $message,
                'unmatchedCodes' => $unmatchedCodes
            ];
        }

        return [
            'valid' => true,
            'message' => "All account codes validated successfully. Found {$validRows} valid GL account entries. All accounts exist in GL Account Management.",
            'unmatchedCodes' => []
        ];
    }

    /**
     * Check if row is a section header
     */
    private function isSectionHeader($accountCode, $accountName)
    {
        $sectionHeaders = [
            'CASH BEGINNING BALANCE',
            'ADD: RECEIPTS',
            'ADD RECEIPTS',
            'TOTAL CASH AVAILABLE',
            'LESS: DISBURSEMENTS',
            'LESS DISBURSEMENTS',
            'TOTAL DISBURSEMENTS',
            'CASH ENDING BALANCE'
        ];

        foreach ($sectionHeaders as $header) {
            if (str_contains(strtoupper($accountCode), $header) || str_contains(strtoupper($accountName), $header)) {
                return true;
            }
        }

        return false;
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
