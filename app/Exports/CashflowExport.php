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
        // Row 1: NAME OF COOPERATIVE
        $row1 = ['NAME OF COOPERATIVE', '', '', '', '', '', '', ''];

        // Row 2: CASH FLOW MONITORING REPORT
        $row2 = ['CASH FLOW MONITORING REPORT', '', '', '', '', '', '', ''];

        // Row 3: Empty row for spacing
        $row3 = ['', '', '', '', '', '', '', ''];

        // Row 4: Empty row for spacing
        $row4 = ['', '', '', '', '', '', '', ''];

        // Row 5: Headers with merged PARTICULARS (A5-B5) and CASH PROJECTION PLAN (D5-F5)
        $row5 = ['PARTICULARS', '', 'ACTUAL', 'CASH PROJECTION PLAN', '', '', 'TOTAL', ''];

        // Row 6: Date and actual month names
        $monthNames = [];
        if ($this->period <= 12) {
            // Monthly periods - use actual month names
            $startMonth = $this->month ? array_search($this->month, [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ]) : 0;

            for ($i = 0; $i < $this->period; $i++) {
                $monthIndex = ($startMonth + $i) % 12;
                $monthNames[] = [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ][$monthIndex];
            }
        } else {
            // Yearly periods
            for ($i = 1; $i <= $this->period; $i++) {
                $monthNames[] = "Year {$i}";
            }
        }

        $row6 = ['', '', '[DATE]', ...$monthNames, ''];

        // Row 7: Empty row for spacing
        $row7 = ['', '', '', '', '', '', '', ''];

        return [$row1, $row2, $row3, $row4, $row5, $row6, $row7];
    }

    public function array(): array
    {
        try {
            // Get cashflow data with relationships
            $query = Cashflow::with(['glAccount', 'branch'])
                ->where('year', $this->year)
                ->where('month', $this->month);

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

            // Get GL accounts with children
            $glAccounts = GLAccount::with('children')
                ->orderBy('account_code')
                ->get();

            // Debug: Log GL accounts
            Log::info('GL Accounts found:', [
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
            $beginningRow = ['', 'CASH BEGINNING BALANCE', $beginningBalance];

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
            $receiptsHeader = ['', 'ADD: RECEIPTS', '', ''];
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

            foreach ($glAccounts as $account) {
                // Find cashflow data for this account in receipts section
                $cashflow = $receiptsData->firstWhere('gl_account_id', $account->id);

                if ($cashflow) {
                    $actual = (float) ($cashflow->actual_amount ?? 0);
                    // Use projection percentage from database, or default to 0 if missing
                    $projection = (float) ($cashflow->projection_percentage ?? 0);

                    // Calculate projections using the formula from the photo
                    $projections = [];
                    $currentValue = $actual;

                    for ($i = 1; $i <= $this->period; $i++) {
                        $currentValue = $currentValue * (1 + ($projection / 100));
                        $projections[] = $currentValue;
                        $receiptsTotal[$i] += $currentValue;
                    }

                    $receiptRow = [
                        '', // Column A empty
                        $account->account_name, // Column B: PARTICULARS
                        $actual, // Column C: ACTUAL
                    ];

                    // Add projection values (Column D, E, F, etc.)
                    foreach ($projections as $value) {
                        $receiptRow[] = $value;
                    }

                    $receiptRow[] = array_sum($projections); // Total column
                    $receiptsTotal['total'] += array_sum($projections);

                    $rows[] = $receiptRow;

                    // Add child accounts if this is a parent
                    if ($account->children->count() > 0) {
                        foreach ($account->children as $child) {
                            $childCashflow = $receiptsData->firstWhere('gl_account_id', $child->id);

                            if ($childCashflow) {
                                $childActual = (float) ($childCashflow->actual_amount ?? 0);
                                $childProjection = (float) ($childCashflow->projection_percentage ?? 0);

                                $childProjections = [];
                                $childCurrentValue = $childActual;

                                for ($i = 1; $i <= $this->period; $i++) {
                                    $childCurrentValue = $childCurrentValue * (1 + ($childProjection / 100));
                                    $childProjections[] = $childCurrentValue;
                                    $receiptsTotal[$i] += $childCurrentValue;
                                }

                                $childRow = [
                                    '', // Column A empty
                                    '> ' . $child->account_name, // Column B: PARTICULARS (with > prefix)
                                    $childActual, // Column C: ACTUAL
                                ];

                                // Add child projection values (Column D, E, F, etc.)
                                foreach ($childProjections as $value) {
                                    $childRow[] = $value;
                                }

                                $childRow[] = array_sum($childProjections); // Total column
                                $receiptsTotal['total'] += array_sum($childProjections);

                                $rows[] = $childRow;
                            }
                        }
                    }
                }
            }

            // Row after receipts: TOTAL CASH AVAILABLE
            $tcaRow = ['', 'TOTAL CASH AVAILABLE', ''];

            for ($i = 1; $i <= $this->period; $i++) {
                $tcaRow[] = $beginningBalance + $receiptsTotal[$i];
            }

            $tcaRow[] = ($beginningBalance * $this->period) + $receiptsTotal['total'];
            $rows[] = $tcaRow;

            // Row: LESS: DISBURSEMENTS (Section header)
            $disbursementsHeader = ['', 'LESS: DISBURSEMENTS', '', ''];
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

            foreach ($glAccounts as $account) {
                // Find cashflow data for this account in disbursements section
                $cashflow = $disbursementsData->firstWhere('gl_account_id', $account->id);

                if ($cashflow) {
                    $actual = (float) ($cashflow->actual_amount ?? 0);
                    $projection = (float) ($cashflow->projection_percentage ?? 0);

                    // Calculate projections using the formula from the photo
                    $projections = [];
                    $currentValue = $actual;

                    for ($i = 1; $i <= $this->period; $i++) {
                        $currentValue = $currentValue * (1 + ($projection / 100));
                        $projections[] = $currentValue;
                        $disbursementsTotal[$i] += $currentValue;
                    }

                    $disbursementRow = [
                        '', // Column A empty
                        $account->account_name, // Column B: PARTICULARS
                        $actual, // Column C: ACTUAL
                    ];

                    // Add projection values (Column D, E, F, etc.)
                    foreach ($projections as $value) {
                        $disbursementRow[] = $value;
                    }

                    $disbursementRow[] = array_sum($projections); // Total column
                    $disbursementsTotal['total'] += array_sum($projections);

                    $rows[] = $disbursementRow;

                    // Add child accounts if this is a parent
                    if ($account->children->count() > 0) {
                        foreach ($account->children as $child) {
                            $childCashflow = $disbursementsData->firstWhere('gl_account_id', $child->id);

                            if ($childCashflow) {
                                $childActual = (float) ($childCashflow->actual_amount ?? 0);
                                $childProjection = (float) ($childCashflow->projection_percentage ?? 0);

                                $childProjections = [];
                                $childCurrentValue = $childActual;

                                for ($i = 1; $i <= $this->period; $i++) {
                                    $childCurrentValue = $childCurrentValue * (1 + ($childProjection / 100));
                                    $childProjections[] = $childCurrentValue;
                                    $disbursementsTotal[$i] += $childCurrentValue;
                                }

                                $childRow = [
                                    '', // Column A empty
                                    '> ' . $child->account_name, // Column B: PARTICULARS (with > prefix)
                                    $childActual, // Column C: ACTUAL
                                ];

                                // Add child projection values (Column D, E, F, etc.)
                                foreach ($childProjections as $value) {
                                    $childRow[] = $value;
                                }

                                $childRow[] = array_sum($childProjections); // Total column
                                $disbursementsTotal['total'] += array_sum($childProjections);

                                $rows[] = $childRow;
                            }
                        }
                    }
                }
            }

            // Row: TOTAL DISBURSEMENTS
            $tdRow = ['', 'TOTAL DISBURSEMENTS', ''];

            for ($i = 1; $i <= $this->period; $i++) {
                $tdRow[] = $disbursementsTotal[$i];
            }

            $tdRow[] = $disbursementsTotal['total'];
            $rows[] = $tdRow;

            // Row: CASH ENDING BALANCE
            $cebRow = ['', 'CASH ENDING BALANCE', ''];

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
        // Style the main title rows
        $sheet->getStyle('A1:A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        // Style the headers row (Row 5)
        $sheet->getStyle('A5:G5')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Style the month/year labels row (Row 6)
        $sheet->getStyle('D6:' . $this->getColumnLetter($this->period + 3) . '6')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Style the CASH PROJECTION PLAN header (Row 5, Columns D-F)
        $projectionRange = 'D5:' . $this->getColumnLetter($this->period + 3) . '5';
        $sheet->getStyle($projectionRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Style summary rows (CASH BEGINNING BALANCE, TOTAL CASH AVAILABLE, etc.)
        $summaryRows = [8, 12, 15, 18]; // Adjust row numbers based on your data
        foreach ($summaryRows as $row) {
            $summaryRange = "A{$row}:" . $this->getColumnLetter($this->period + 3) . "{$row}";
            $sheet->getStyle($summaryRange)->applyFromArray([
                'font' => [
                    'bold' => true,
                ],
            ]);
        }

        // Style section headers (ADD: RECEIPTS, LESS: DISBURSEMENTS)
        $sectionRows = [9, 16]; // Adjust row numbers based on your data
        foreach ($sectionRows as $row) {
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'italic' => true,
                ],
            ]);
        }

        // Style child account rows (those starting with ">")
        $sheet->getStyle('A:A')->getAlignment()->setIndent(1);

        // Add borders to the entire table
        $lastRow = $this->period + 11; // Adjust based on your data
        $tableRange = "A1:" . $this->getColumnLetter($this->period + 3) . "{$lastRow}";
        $sheet->getStyle($tableRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Merge cells for PARTICULARS (A5-B5)
        $sheet->mergeCells('A5:B5');

        // Merge cells for CASH PROJECTION PLAN (D5-F5 or more based on period)
        $projectionEndCol = $this->getColumnLetter($this->period + 3);
        $sheet->mergeCells("D5:{$projectionEndCol}5");

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


