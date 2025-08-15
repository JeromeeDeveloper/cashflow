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
        $branch = Auth::user()->branch; // eager loaded in relationships

        if (!$branch) {
            abort(403, 'User is not associated with any branch.');
        }

        $cashflows = Cashflow::with(['branch', 'cashflowFile', 'glAccount'])
            ->where('branch_id', $branch->id)
            ->whereHas('glAccount', function($query) {
                $query->where('is_selected', true);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('branch.cashflow', compact('cashflows', 'branch'));
    }

    /**
     * Get cashflows for the logged-in user's branch with optional filtering.
     */
    public function getCashflows(Request $request): JsonResponse
    {
        $branchId = optional(Auth::user()->branch)->id;

        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with any branch.'
            ], 403);
        }

        $query = Cashflow::with(['branch', 'cashflowFile', 'glAccount'])
            ->where('branch_id', $branchId)
            ->whereHas('glAccount', function($query) {
                $query->where('is_selected', true);
            });

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
            $query->where('category', $request->category);
        }

        $cashflows = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'cashflows' => $cashflows
        ]);
    }

    /**
     * Display the specified cashflow (read-only for branch users).
     */
    public function show(Cashflow $cashflow): JsonResponse
    {
        $this->authorizeCashflow($cashflow);

        $cashflow->load(['branch', 'cashflowFile', 'glAccount']);

        return response()->json([
            'success' => true,
            'cashflow' => $cashflow
        ]);
    }

    /**
     * Get summary statistics for the logged-in user's branch.
     */
    public function getSummary(Request $request): JsonResponse
    {
        $branchId = optional(Auth::user()->branch)->id;

        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with any branch.'
            ], 403);
        }

        $query = Cashflow::where('branch_id', $branchId);

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
        $branchId = optional(Auth::user()->branch)->id;

        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with any branch.'
            ], 403);
        }

        $query = Cashflow::with(['branch', 'cashflowFile', 'glAccount'])
            ->where('branch_id', $branchId);

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

    /**
     * Update the specified cashflow entry.
     */
    public function update(Request $request, Cashflow $cashflow): JsonResponse
    {
        $this->authorizeCashflow($cashflow);

        $validated = $request->validate([
            'actual_amount' => 'required|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
        ]);

        $cashflow->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cash flow entry updated successfully',
            'cashflow' => $cashflow->fresh()
        ]);
    }

    /**
     * Remove the specified cashflow entry.
     */
    public function destroy(Cashflow $cashflow): JsonResponse
    {
        $this->authorizeCashflow($cashflow);

        $cashflow->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cash flow entry deleted successfully'
        ]);
    }

    /**
     * Authorize that the user can access this cashflow (must be from their branch)
     */
    private function authorizeCashflow(Cashflow $cashflow): void
    {
        $branchId = optional(Auth::user()->branch)->id;
        abort_if($cashflow->branch_id !== $branchId, 403, 'Unauthorized access to this cashflow.');
    }
}


