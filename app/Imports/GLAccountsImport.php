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
        foreach ($rows as $index => $row) {
            // Normalize keys from heading row
            $accountCode = trim((string)($row['lan'] ?? $row['account_code'] ?? ''));
            $accountName = trim((string)($row['lan_desc'] ?? $row['account_name'] ?? ''));
            $category = trim((string)($row['category'] ?? ''));
            $level = $row['level'] ?? null;

            if ($accountCode === '' && $accountName === '') {
                continue; // skip empty rows
            }

            try {
                GLAccount::updateOrCreate(
                    ['account_code' => $accountCode],
                    [
                        'account_name' => $accountName,
                        'category' => $category !== '' ? $category : null,
                        'level' => $level !== null && $level !== '' ? (string)$level : null,
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('GLAccountsImport row failed', [
                    'row_index' => $index,
                    'account_code' => $accountCode,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}


