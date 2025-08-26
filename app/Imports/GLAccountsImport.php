<?php

namespace App\Imports;

use App\Models\GLAccount;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class GLAccountsImport implements ToCollection, WithHeadingRow
{
    /**
     * The row number to use as headings.
     */
    public function headingRow(): int
    {
        return 4; // Headers are on row 4 (A4:D4)
    }

    /**
     * Handle the imported collection.
     * Expected headers:
     * - A4: LAN -> account_code
     * - C4: LAN Desc -> account_name
     * - D4: Category -> category
     * - E4: Level -> level
     */
    public function collection(Collection $rows)
    {
        Log::info('GLAccountsImport started', [
            'total_rows' => $rows->count(),
            'first_row_keys' => $rows->first() ? array_keys($rows->first()->toArray()) : []
        ]);

        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($rows as $index => $row) {
            Log::info("Processing row {$index}", [
                'row_data' => $row->toArray(),
                'available_keys' => array_keys($row->toArray())
            ]);

            // Try multiple possible key variations
            $accountCode = trim((string)($row['LAN'] ?? $row['lan'] ?? $row['account_code'] ?? ''));
            $accountName = trim((string)($row['LAN Desc'] ?? $row['lan_desc'] ?? $row['LAN_Desc'] ?? $row['account_name'] ?? ''));
            $category = trim((string)($row['Category'] ?? $row['category'] ?? ''));
            $level = $row['Level'] ?? $row['level'] ?? null;

            Log::info("Extracted values", [
                'account_code' => $accountCode,
                'account_name' => $accountName,
                'category' => $category,
                'level' => $level
            ]);

            if ($accountCode === '' && $accountName === '') {
                Log::info("Skipping empty row {$index}");
                $skippedCount++;
                continue; // skip empty rows
            }

            try {
                $result = GLAccount::updateOrCreate(
                    ['account_code' => $accountCode],
                    [
                        'account_name' => $accountName,
                        'category' => $category !== '' ? $category : null,
                        'level' => $level !== null && $level !== '' ? (string)$level : null,
                        'cashflow_type' => 'disbursements', // Default value
                        'account_type' => 'single', // Default value
                        'is_active' => true, // Default value
                        'is_selected' => false, // Default value
                    ]
                );

                Log::info("Row {$index} processed successfully", [
                    'account_code' => $accountCode,
                    'account_name' => $accountName,
                    'result_id' => $result->id
                ]);

                $processedCount++;

            } catch (\Throwable $e) {
                Log::error('GLAccountsImport row failed', [
                    'row_index' => $index,
                    'account_code' => $accountCode,
                    'account_name' => $accountName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $errorCount++;
            }
        }

        Log::info('GLAccountsImport completed', [
            'total_rows' => $rows->count(),
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'errors' => $errorCount
        ]);
    }
}


