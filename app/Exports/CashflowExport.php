<?php

namespace App\Exports;

use App\Models\Cashflow;
use App\Models\Branch;
use Illuminate\Contracts\Support\Arrayable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CashflowExport implements FromArray, WithHeadings, ShouldAutoSize
{
    private ?int $year;
    private ?string $month;
    private ?int $branchId;

    public function __construct(?int $year = null, ?string $month = null, ?int $branchId = null)
    {
        $this->year = $year;
        $this->month = $month;
        $this->branchId = $branchId;
    }

    public function headings(): array
    {
        return [
            'Particulars',
            'Actual',
            '% Projection',
            'Month 1',
            'Month 2',
            'Month 3',
            'Total',
        ];
    }

    public function array(): array
    {
        $query = Cashflow::with(['glAccount', 'branch'])
            ->when($this->year, fn($q) => $q->where('year', $this->year))
            ->when($this->month, fn($q) => $q->where('month', $this->month))
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId));

        $all = $query->orderBy('id')->get();

        // Summary record (if any)
        $summaryRecord = $all->firstWhere('account_type', 'Summary');
        $beginning = (float) ($summaryRecord->cash_beginning_balance ?? 0);

        // Partition receipts vs disbursements using period_values.section
        $receipts = $all->filter(function ($row) {
            if ($row->account_type === 'Summary') return false;
            $pv = is_array($row->period_values) ? $row->period_values : [];
            $section = $pv['section'] ?? null;
            return strtolower((string) $section) !== 'disbursements';
        });

        $disbursements = $all->filter(function ($row) {
            if ($row->account_type === 'Summary') return false;
            $pv = is_array($row->period_values) ? $row->period_values : [];
            $section = $pv['section'] ?? null;
            return strtolower((string) $section) === 'disbursements';
        });

        $rows = [];

        // 1) CASH BEGINNING BALANCE
        $b1 = $beginning;
        $b2 = $b1;
        $b3 = $b2;
        $bTotal = $b1 + $b2 + $b3;
        $rows[] = [
            'CASH BEGINNING BALANCE',
            null, // Actual not used for summary lines in planning grid
            null,
            round($b1, 2),
            round($b2, 2),
            round($b3, 2),
            round($bTotal, 2),
        ];

        // Helper to compute projection months
        $mapProjected = function ($row): array {
            $actual = (float) ($row->actual_amount ?? 0);
            $pct = (float) ($row->projection_percentage ?? 0);
            $m1 = $actual * (1 + ($pct / 100));
            $m2 = $m1;
            $m3 = $m2;
            $total = $m1 + $m2 + $m3;

            // Use hierarchical name if available, otherwise fall back to account name
            $glAccount = $row->glAccount;
            $name = $glAccount ? ($glAccount->hierarchical_name ?? $glAccount->account_name) : 'Unknown Account';

            return [
                $name,
                round($actual, 2),
                round($pct, 2),
                round($m1, 2),
                round($m2, 2),
                round($m3, 2),
                round($total, 2),
            ];
        };

        // 2) Receipts list
        $receiptRows = $receipts->map($mapProjected)->values()->all();
        $rows = array_merge($rows, $receiptRows);

        // Sum of receipts per month
        $receiptsSums = $receipts->map(function ($row) use ($mapProjected) {
            $r = $mapProjected($row);
            return [
                'm1' => $r[3],
                'm2' => $r[4],
                'm3' => $r[5],
                'total' => $r[6],
            ];
        })->reduce(function ($carry, $i) {
            $carry['m1'] += $i['m1'];
            $carry['m2'] += $i['m2'];
            $carry['m3'] += $i['m3'];
            $carry['total'] += $i['total'];
            return $carry;
        }, ['m1' => 0.0, 'm2' => 0.0, 'm3' => 0.0, 'total' => 0.0]);

        // 3) TOTAL CASH AVAILABLE
        $tca1 = $b1 + $receiptsSums['m1'];
        $tca2 = $b2 + $receiptsSums['m2'];
        $tca3 = $b3 + $receiptsSums['m3'];
        $tcaTotal = $tca1 + $tca2 + $tca3;
        $rows[] = [
            'TOTAL CASH AVAILABLE',
            null,
            null,
            round($tca1, 2),
            round($tca2, 2),
            round($tca3, 2),
            round($tcaTotal, 2),
        ];

        // 4) Disbursements list
        $disbRows = $disbursements->map($mapProjected)->values()->all();
        $rows = array_merge($rows, $disbRows);

        $disbSums = $disbursements->map(function ($row) use ($mapProjected) {
            $r = $mapProjected($row);
            return [
                'm1' => $r[3],
                'm2' => $r[4],
                'm3' => $r[5],
                'total' => $r[6],
            ];
        })->reduce(function ($carry, $i) {
            $carry['m1'] += $i['m1'];
            $carry['m2'] += $i['m2'];
            $carry['m3'] += $i['m3'];
            $carry['total'] += $i['total'];
            return $carry;
        }, ['m1' => 0.0, 'm2' => 0.0, 'm3' => 0.0, 'total' => 0.0]);

        // 5) TOTAL DISBURSEMENTS
        $rows[] = [
            'TOTAL DISBURSEMENTS',
            null,
            null,
            round($disbSums['m1'], 2),
            round($disbSums['m2'], 2),
            round($disbSums['m3'], 2),
            round($disbSums['total'], 2),
        ];

        // 6) CASH ENDING BALANCE
        $end1 = $tca1 - $disbSums['m1'];
        $end2 = $tca2 - $disbSums['m2'];
        $end3 = $tca3 - $disbSums['m3'];
        $endTotal = $end1 + $end2 + $end3;
        $rows[] = [
            'CASH ENDING BALANCE',
            null,
            null,
            round($end1, 2),
            round($end2, 2),
            round($end3, 2),
            round($endTotal, 2),
        ];

        return $rows;
    }
}


