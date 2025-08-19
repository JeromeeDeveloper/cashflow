<?php

namespace App\Exports;

use App\Models\Cashflow;
use App\Models\Branch;
use App\Models\GLAccount;
use Illuminate\Contracts\Support\Arrayable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Log;

class CashflowExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    private ?int $year;
    private ?string $month;
    private ?int $branchId;
    private int $period;

    public function __construct(?int $year = null, ?string $month = null, ?int $branchId = null, int $period = 3)
    {
        $this->year = $year;
        $this->month = $month;
        $this->branchId = $branchId;
        $this->period = $period;
    }

    public function headings(): array
    {
        $row1 = ['NAME OF COOPERATIVE', '', '', '', '', '', '', ''];
        $row2 = ['CASH FLOW REPORT', '', '', '', '', '', '', ''];
        $row3 = [date('F j, Y'), '', '', '', '', '', '', ''];
        $row4 = ['', '', '', '', '', '', '', ''];
        $row5 = ['', '', '', '', '', '', '', ''];
        $row6 = ['PARTICULARS', 'ACTUAL', 'PROJECTION %', 'CASH PROJECTION/PLAN', '', '', 'TOTAL', ''];

        // Determine date labels based on selected month/year
        $isMonthly = !empty($this->month);
        $dateLabels = [];
        if ($isMonthly) {
            $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            $startMonthIndex = array_search($this->month, $months);
            if ($startMonthIndex === false) {
                $startMonthIndex = (int)date('n') - 1;
            }
            for ($i = 0; $i < $this->period; $i++) {
                $dateLabels[] = $months[($startMonthIndex + $i) % 12];
            }
            $selectedLabel = $this->month ?? $months[(int)date('n') - 1];
        } else {
            $startYear = (int)($this->year ?? date('Y'));
            for ($i = 0; $i < $this->period; $i++) {
                $dateLabels[] = (string)($startYear + $i);
            }
            $selectedLabel = (string)$startYear;
        }

        // Row 7: B7 should display the selected month/year label
        $row7 = ['', $selectedLabel, '', ...$dateLabels, 'TOTAL'];

        return [$row1, $row2, $row3, $row4, $row5, $row6, $row7];
    }

    public function array(): array
    {
        try {
            // Get cashflow data with relationships
            $query = Cashflow::with(['glAccount', 'branch'])
                ->where('year', $this->year);
            if ($this->month) {
                $query->where('month', $this->month);
            }

            if ($this->branchId) {
                $query->where('branch_id', $this->branchId);
            }

            $all = $query->orderBy('id')->get();

            // Debug: Log what we're getting
            Log::info('Cashflow data found:', [
                'count' => $all->count(),
                'sample' => $all->take(5)->map(function($item) {
                    return [
                        'id' => $item->id,
                        'gl_account_id' => $item->gl_account_id,
                        'section' => $item->section,
                        'actual_amount' => $item->actual_amount,
                        'gl_account_name' => $item->glAccount ? $item->glAccount->account_name : 'N/A'
                    ];
                })->toArray()
            ]);

            // Get unique GL accounts from cashflow data
            $glAccountIds = $all->pluck('gl_account_id')->unique();
            $glAccounts = GLAccount::with('children')
                ->whereIn('id', $glAccountIds)
                ->orderBy('account_code')
                ->get();

            // Debug: Log GL accounts found in cashflow data
            Log::info('GL Accounts found in cashflow data:', [
                'count' => $glAccounts->count(),
                'sample' => $glAccounts->take(5)->map(function($item) {
                    return [
                        'id' => $item->id,
                        'account_code' => $item->account_code,
                        'account_name' => $item->account_name,
                        'account_type' => $item->account_type,
                        'children_count' => $item->children->count()
                    ];
                })->toArray()
            ]);

            // Calculate beginning balance (sum of all receipts)
            $beginningBalance = $all->where('section', 'receipts')->sum('actual_amount');

            // Row 8: CASH BEGINNING BALANCE
            $beginningRow = ['CASH BEGINNING BALANCE', $beginningBalance, '', ''];

            // Add period columns with formulas
            if ($this->period <= 12) {
                for ($i = 1; $i <= $this->period; $i++) {
                    $beginningRow[] = $beginningBalance;
                }
            } else {
                for ($i = 1; $i <= $this->period; $i++) {
                    $beginningRow[] = $beginningBalance;
                }
            }

            $beginningRow[] = $beginningBalance * $this->period; // Total
            $rows[] = $beginningRow;

            // Row 9: ADD: RECEIPTS (Section header)
            $receiptsHeader = ['ADD: RECEIPTS', '', '', ''];
            for ($i = 1; $i <= $this->period; $i++) {
                $receiptsHeader[] = '';
            }
            $receiptsHeader[] = '';
            $rows[] = $receiptsHeader;

            // Process receipts (section = 'receipts')
            $receiptsTotal = [];
            for ($i = 1; $i <= $this->period; $i++) {
                $receiptsTotal[$i] = 0;
            }
            $receiptsTotal['total'] = 0;

            // Get receipts data
            $receiptsData = $all->where('section', 'receipts');

            // Process receipts data directly
            foreach ($receiptsData as $cashflow) {
                $glAccount = $cashflow->glAccount;
                if (!$glAccount) {
                    continue; // Skip if no GL account found
                }

                $actual = (float) ($cashflow->actual_amount ?? 0);
                $projection = (float) ($cashflow->projection_percentage ?? 0);

                // Calculate projections
                $projections = [];
                $currentValue = $actual;

                for ($i = 1; $i <= $this->period; $i++) {
                    $currentValue = $currentValue * (1 + ($projection / 100));
                    $projections[] = $currentValue;
                    $receiptsTotal[$i] += $currentValue;
                }

                // Determine if this is a child account (check if parent exists)
                $accountName = $glAccount->account_name;
                if ($glAccount->parent_id) {
                    $accountName = '> ' . $accountName;
                }

                $receiptRow = [
                    $accountName, // Column A: PARTICULARS
                    $actual, // Column B: ACTUAL
                    $projection, // Column C: PROJECTION %
                ];

                // Add projection values (Column D, E, F, etc.)
                foreach ($projections as $value) {
                    $receiptRow[] = $value;
                }

                $receiptRow[] = array_sum($projections); // Total column
                $receiptsTotal['total'] += array_sum($projections);

                $rows[] = $receiptRow;
            }

            // Row after receipts: TOTAL CASH AVAILABLE
            $tcaRow = ['TOTAL CASH AVAILABLE', '', '', ''];

            for ($i = 1; $i <= $this->period; $i++) {
                $tcaRow[] = $beginningBalance + $receiptsTotal[$i];
            }

            $tcaRow[] = ($beginningBalance * $this->period) + $receiptsTotal['total'];
            $rows[] = $tcaRow;

            // Row: LESS: DISBURSEMENTS (Section header)
            $disbursementsHeader = ['LESS: DISBURSEMENTS', '', '', ''];
            for ($i = 1; $i <= $this->period; $i++) {
                $disbursementsHeader[] = '';
            }
            $disbursementsHeader[] = '';
            $rows[] = $disbursementsHeader;

            // Process disbursements (section = 'disbursements')
            $disbursementsTotal = [];
            for ($i = 1; $i <= $this->period; $i++) {
                $disbursementsTotal[$i] = 0;
            }
            $disbursementsTotal['total'] = 0;

            // Get disbursements data
            $disbursementsData = $all->where('section', 'disbursements');

            // Process disbursements data directly
            foreach ($disbursementsData as $cashflow) {
                $glAccount = $cashflow->glAccount;
                if (!$glAccount) {
                    continue; // Skip if no GL account found
                }

                $actual = (float) ($cashflow->actual_amount ?? 0);
                $projection = (float) ($cashflow->projection_percentage ?? 0);

                // Calculate projections
                $projections = [];
                $currentValue = $actual;

                for ($i = 1; $i <= $this->period; $i++) {
                    $currentValue = $currentValue * (1 + ($projection / 100));
                    $projections[] = $currentValue;
                    $disbursementsTotal[$i] += $currentValue;
                }

                // Determine if this is a child account (check if parent exists)
                $accountName = $glAccount->account_name;
                if ($glAccount->parent_id) {
                    $accountName = '> ' . $accountName;
                }

                $disbursementRow = [
                    $accountName, // Column A: PARTICULARS
                    $actual, // Column B: ACTUAL
                    $projection, // Column C: PROJECTION %
                ];

                // Add projection values (Column D, E, F, etc.)
                foreach ($projections as $value) {
                    $disbursementRow[] = $value;
                }

                $disbursementRow[] = array_sum($projections); // Total column
                $disbursementsTotal['total'] += array_sum($projections);

                $rows[] = $disbursementRow;
            }

            // Row: TOTAL DISBURSEMENTS
            $tdRow = ['TOTAL DISBURSEMENTS', '', '', ''];

            for ($i = 1; $i <= $this->period; $i++) {
                $tdRow[] = $disbursementsTotal[$i];
            }

            $tdRow[] = $disbursementsTotal['total'];
            $rows[] = $tdRow;

            // Row: CASH ENDING BALANCE
            $cebRow = ['CASH ENDING BALANCE', '', '', ''];

            for ($i = 1; $i <= $this->period; $i++) {
                $cebRow[] = ($beginningBalance + $receiptsTotal[$i]) - $disbursementsTotal[$i];
            }

            $cebTotal = ($beginningBalance * $this->period) + $receiptsTotal['total'] - $disbursementsTotal['total'];
            $cebRow[] = $cebTotal;

            $rows[] = $cebRow;

            return $rows;

        } catch (\Exception $e) {
            // Return error information for debugging
            Log::error('Export error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return [
                ['ERROR', 'Error occurred during export', $e->getMessage()],
                ['Stack trace', $e->getTraceAsString(), '']
            ];
        }
    }

    public function styles(Worksheet $sheet)
    {
        // Set default font
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // Style the main title rows
        $sheet->getStyle('A1:A3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        // Style the headers row (Row 6)
        $sheet->getStyle('A6:' . $this->getColumnLetter($this->period + 3) . '6')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Style the month/year labels row (Row 7) - make bold for visibility
        $sheet->getStyle('D7:' . $this->getColumnLetter($this->period + 3) . '7')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Style the CASH PROJECTION/PLAN header (Row 6, Columns D-F)
        $projectionRange = 'D6:' . $this->getColumnLetter($this->period + 3) . '6';
        $sheet->getStyle($projectionRange)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Bold summary rows (CASH BEGINNING BALANCE, TOTAL CASH AVAILABLE, etc.)
        // Note: specific row indices depend on how many product rows exist; keep conservative defaults
        // Caller can adjust later if needed

        // Indent child account rows visually handled by prefix '> '

        // Borders for entire used range (approximation)
        $lastRow = $this->period + 200; // generous
        $tableRange = 'A1:' . $this->getColumnLetter($this->period + 3) . $lastRow;
        $sheet->getStyle($tableRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Merge cells for CASH PROJECTION/PLAN (D6-F6 or more based on period)
        $projectionEndCol = $this->getColumnLetter($this->period + 3);
        $sheet->mergeCells('D6:' . $projectionEndCol . '6');

        return $sheet;
    }

    private function getColumnLetter($columnNumber): string
    {
        $letter = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $letter = chr(65 + ($columnNumber % 26)) . $letter;
            $columnNumber = intval($columnNumber / 26);
        }
        return $letter;
    }
}


