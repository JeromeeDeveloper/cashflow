<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Cashflow;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class CashflowController extends Controller
{
    /**
     * Display the cashflow index page for the logged-in user's branch.
     */
    public function index(): View
    {
        $user = Auth::user();

        // Check if user has a branch
        if (!$user->branch_id) {
            abort(403, 'User is not associated with any branch.');
        }

        $branch = $user->branch;
        $cashflows = Cashflow::with(['branch', 'cashflowFile'])
            ->where('branch_id', $user->branch_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('branch.cashflow', compact('cashflows', 'branch'));
    }

    /**
     * Get cashflows for the logged-in user's branch with optional filtering.
     */
    public function getCashflows(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with any branch.'
            ], 403);
        }

        $query = Cashflow::with(['branch', 'cashflowFile'])
            ->where('branch_id', $user->branch_id);

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->where('month', $request->month);
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
     * Display the specified cashflow (read-only for branch users).
     */
    public function show(Cashflow $cashflow): JsonResponse
    {
        $user = Auth::user();

        // Ensure user can only view cashflows from their branch
        if ($cashflow->branch_id !== $user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this cashflow.'
            ], 403);
        }

        $cashflow->load(['branch', 'cashflowFile']);

        return response()->json([
            'success' => true,
            'data' => $cashflow
        ]);
    }

    /**
     * Get summary statistics for the logged-in user's branch.
     */
    public function getSummary(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with any branch.'
            ], 403);
        }

        $query = Cashflow::where('branch_id', $user->branch_id);

        // Apply filters
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
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
     * Export cashflow data for the logged-in user's branch.
     */
    public function export(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with any branch.'
            ], 403);
        }

        $query = Cashflow::with(['branch', 'cashflowFile'])
            ->where('branch_id', $user->branch_id);

        // Apply filters
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        $cashflows = $query->orderBy('created_at', 'desc')->get();

        // In a real application, you would generate and return an actual file
        // For now, we'll return the data for frontend processing
        return response()->json([
            'success' => true,
            'message' => 'Export data prepared successfully',
            'data' => $cashflows
        ]);
    }
}


