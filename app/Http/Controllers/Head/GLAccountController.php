<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\GLAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class GLAccountController extends Controller
{
    /**
     * Display the GL accounts management page.
     */
    public function index(): View
    {
        $glAccounts = GLAccount::withCount('cashflows')->orderBy('account_code')->get();
        return view('head.gl-accounts', compact('glAccounts'));
    }

    /**
     * Store a newly created GL account.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_code' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
            'cashflow_type' => 'required|in:receipts,disbursements',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for composite unique constraint: account_code + cashflow_type
        $existingAccount = GLAccount::where('account_code', $request->account_code)
            ->where('cashflow_type', $request->cashflow_type)
            ->first();

        if ($existingAccount) {
            return response()->json([
                'success' => false,
                'message' => 'An account with this code and cash flow type already exists.',
                'errors' => [
                    'account_code' => ['This account code with the selected cash flow type already exists.']
                ]
            ], 422);
        }

        try {
            $glAccount = GLAccount::create([
                'account_code' => $request->account_code,
                'account_name' => $request->account_name,
                'cashflow_type' => $request->cashflow_type,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'GL Account created successfully',
                'data' => $glAccount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating GL account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified GL account.
     */
    public function show(GLAccount $glAccount): JsonResponse
    {
        $glAccount->loadCount('cashflows');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $glAccount->id,
                'account_code' => $glAccount->account_code,
                'account_name' => $glAccount->account_name,
                'cashflows_count' => $glAccount->cashflows_count,
                'created_at' => $glAccount->created_at->format('M d, Y H:i'),
                'updated_at' => $glAccount->updated_at->format('M d, Y H:i'),
            ]
        ]);
    }

    /**
     * Update the specified GL account.
     */
    public function update(Request $request, GLAccount $glAccount): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_code' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
            'cashflow_type' => 'required|in:receipts,disbursements',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for composite unique constraint: account_code + cashflow_type
        $existingAccount = GLAccount::where('account_code', $request->account_code)
            ->where('cashflow_type', $request->cashflow_type)
            ->where('id', '!=', $glAccount->id)
            ->first();

        if ($existingAccount) {
            return response()->json([
                'success' => false,
                'message' => 'An account with this code and cash flow type already exists.',
                'errors' => [
                    'account_code' => ['This account code with the selected cash flow type already exists.']
                ]
            ], 422);
        }

        try {
            $glAccount->update([
                'account_code' => $request->account_code,
                'account_name' => $request->account_name,
                'cashflow_type' => $request->cashflow_type,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'GL Account updated successfully',
                'data' => $glAccount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating GL account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified GL account.
     */
    public function destroy(GLAccount $glAccount): JsonResponse
    {
        try {
            // Check if account is used in cashflows
            if ($glAccount->cashflows()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete account. It is being used in cash flow entries.'
                ], 422);
            }

            $glAccount->delete();

            return response()->json([
                'success' => true,
                'message' => 'GL Account deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting GL account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get GL accounts for dropdowns.
     */
    public function getAccounts(Request $request): JsonResponse
    {
        $search = $request->get('search', '');

        $accounts = GLAccount::where('account_name', 'LIKE', "%{$search}%")
            ->orWhere('account_code', 'LIKE', "%{$search}%")
            ->orderBy('account_code')
            ->limit(10)
            ->get(['id', 'account_code', 'account_name']);

        return response()->json([
            'success' => true,
            'data' => $accounts
        ]);
    }
}
