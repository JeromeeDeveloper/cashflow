<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\Cashflow;
use App\Models\Branch;
use App\Exports\CashflowExport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class CashflowController extends Controller
{
    /**
     * Display the cashflow index page.
     */
    public function index(): View
    {
        $branches = Branch::all();

        // Get the most recent month with data
        $latestData = Cashflow::with(['branch', 'cashflowFile', 'glAccount'])
            ->whereHas('glAccount', function ($q) {
                $q->where('is_selected', 1);
            })
            ->select('year', 'month')
            ->orderBy('year', 'desc')
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') DESC")
            ->first();

        // If no data exists, use current month
        if (!$latestData) {
            $currentYear = date('Y');
            $currentMonth = date('F'); // Full month name
        } else {
            $currentYear = $latestData->year;
            $currentMonth = $latestData->month;
        }

        // Get cashflows for the determined period
        $cashflows = Cashflow::with(['branch', 'cashflowFile', 'glAccount'])
            ->whereHas('glAccount', function ($q) {
                $q->where('is_selected', 1);
            })
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->orderBy('created_at', 'desc')
            ->get();

        // Debug: Log what we're getting
        Log::info('Head cashflow controller - cashflows found:', [
            'count' => $cashflows->count(),
            'year' => $currentYear,
            'month' => $currentMonth,
            'sample' => $cashflows->take(3)->map(function($item) {
                return [
                    'id' => $item->id,
                    'gl_account_id' => $item->gl_account_id,
                    'actual_amount' => $item->actual_amount,
                    'gl_account_name' => $item->glAccount ? $item->glAccount->account_name : 'N/A'
                ];
            })->toArray()
        ]);

        return view('head.cashflow', compact('cashflows', 'branches', 'currentYear', 'currentMonth'));
    }

    /**
     * Get cashflows with optional filtering.
     */
    public function getCashflows(Request $request): JsonResponse
    {
        $query = Cashflow::with(['branch', 'cashflowFile', 'glAccount'])
            ->whereHas('glAccount', function ($q) {
                $q->where('is_selected', 1);
            });

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        // Filter by branch
        if ($request->branch_id && $request->branch_id !== '') {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('cashflow_category', $request->category);
        }

        $cashflows = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $cashflows
        ]);
    }

    /**
     * Get all cashflows for finding the most recent data.
     */
    public function getAllCashflows(): JsonResponse
    {
        $cashflows = Cashflow::with(['branch', 'cashflowFile', 'glAccount'])
            ->whereHas('glAccount', function ($q) {
                $q->where('is_selected', 1);
            })
            ->select('id', 'year', 'month', 'branch_id', 'gl_account_id')
            ->orderBy('year', 'desc')
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') DESC")
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cashflows
        ]);
    }

    /**
     * Store a newly created cashflow.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'gl_account_id' => 'required|exists:gl_accounts,id',
            'account_type' => 'required|in:Asset,Liability,Equity,Income,Expense',
            'cashflow_category' => 'required|in:Operating,Investing,Financing',
            'branch_id' => 'required|exists:branches,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|string|max:20',
            'actual_amount' => 'required|numeric|min:0',
            'projection_percentage' => 'required|numeric|min:0|max:100',
            'projected_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        try {
            $cashflow = Cashflow::create([
                'cashflow_file_id' => null, // Will be set when processing uploaded files
                'branch_id' => $request->branch_id,
                'gl_account_id' => $request->gl_account_id,
                'year' => $request->year,
                'month' => $request->month,
                'account_type' => $request->account_type,
                'cashflow_category' => $request->cashflow_category,
                'actual_amount' => $request->actual_amount,
                'projection_percentage' => $request->projection_percentage,
                'projected_amount' => $request->projected_amount,
                'total' => $request->total,
            ]);

            $cashflow->load(['branch', 'glAccount']);

            return response()->json([
                'success' => true,
                'message' => 'Cashflow entry created successfully',
                'data' => $cashflow
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating cashflow entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified cashflow.
     */
    public function show(Cashflow $cashflow): JsonResponse
    {
        $cashflow->load(['branch', 'cashflowFile']);

        return response()->json([
            'success' => true,
            'data' => $cashflow
        ]);
    }

    /**
     * Update the specified cashflow.
     */
    public function update(Request $request, Cashflow $cashflow): JsonResponse
    {
        $request->validate([
            'account_code' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:Asset,Liability,Equity,Income,Expense',
            'cashflow_category' => 'required|in:Operating,Investing,Financing',
            'branch_id' => 'required|exists:branches,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|string|max:20',
            'actual_amount' => 'required|numeric|min:0',
            'projection_percentage' => 'required|numeric|min:0|max:100',
            'projected_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        try {
            $cashflow->update([
                'branch_id' => $request->branch_id,
                'year' => $request->year,
                'month' => $request->month,
                'account_code' => $request->account_code,
                'account_name' => $request->account_name,
                'account_type' => $request->account_type,
                'cashflow_category' => $request->cashflow_category,
                'actual_amount' => $request->actual_amount,
                'projection_percentage' => $request->projection_percentage,
                'projected_amount' => $request->projected_amount,
                'total' => $request->total,
            ]);

            $cashflow->load('branch');

            return response()->json([
                'success' => true,
                'message' => 'Cash flow entry updated successfully',
                'data' => $cashflow
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cash flow entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified cashflow.
     */
    public function destroy(Cashflow $cashflow): JsonResponse
    {
        try {
            $cashflow->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cash flow entry deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cash flow entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get summary statistics for cashflow.
     */
    public function getSummary(Request $request): JsonResponse
    {
        $query = Cashflow::query();

        // Apply filters
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        if ($request->filled('branch_id') && $request->branch_id !== '') {
            $query->where('branch_id', $request->branch_id);
        }

        $summary = [
            'total_operating' => $query->where('cashflow_category', 'Operating')->sum('total'),
            'total_investing' => $query->where('cashflow_category', 'Investing')->sum('total'),
            'total_financing' => $query->where('cashflow_category', 'Financing')->sum('total'),
        ];

        $summary['net_cashflow'] = $summary['total_operating'] + $summary['total_investing'] + $summary['total_financing'];

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Update projection percentage for a cashflow entry.
     */
    public function updateProjectionPercentage(Request $request, Cashflow $cashflow): JsonResponse
    {
        $request->validate([
            'projection_percentage' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $cashflow->update([
                'projection_percentage' => $request->projection_percentage,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Projection percentage updated successfully',
                'data' => $cashflow
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update projection percentage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export cashflow data.
     */
    public function export(Request $request)
    {
        $year = $request->integer('year');
        $month = $request->get('month');
        $branchId = $request->filled('branch_id') ? (int) $request->branch_id : null;
        $period = $request->integer('period', 3); // Default to 3 months

        $fileNameParts = ['cashflow'];
        if ($branchId) { $fileNameParts[] = 'branch_'.$branchId; }
        if ($month) { $fileNameParts[] = strtolower($month); }
        if ($year) { $fileNameParts[] = (string) $year; }
        $fileNameParts[] = $period . ($period <= 12 ? 'months' : 'years');
        $fileName = implode('_', $fileNameParts) . '.xlsx';

        return Excel::download(new CashflowExport($year, $month, $branchId, $period), $fileName);
    }
}
