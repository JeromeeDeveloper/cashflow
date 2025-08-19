<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GLAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class GLAccountsController extends Controller
{
        /**
     * Display the GL accounts management page.
     */
    public function index()
    {
        $allAccounts = GLAccount::with(['parent', 'children', 'cashflows'])
            ->orderBy('account_code')
            ->get();

        // Get only parent accounts and standalone accounts (accounts without parents)
        $glAccounts = $allAccounts->filter(function($account) {
            return $account->parent_id === null; // Only show accounts without parents in main table
        });

        // Separate selected and unselected accounts
        $selectedAccounts = $allAccounts->where('is_selected', true);
        $unselectedAccounts = $allAccounts->where('is_selected', false);

        // Group by parent accounts
        $parentAccounts = $allAccounts->where('account_type', 'parent');
        $detailAccounts = $allAccounts->where('account_type', 'detail');
        $summaryAccounts = $allAccounts->where('account_type', 'summary');

        return view('admin.gl-accounts.index', compact(
            'glAccounts',
            'selectedAccounts',
            'unselectedAccounts',
            'parentAccounts',
            'detailAccounts',
            'summaryAccounts'
        ));
    }

    /**
     * Update the selection status of a GL account.
     */
    public function updateSelection(Request $request, GLAccount $glAccount): JsonResponse
    {
        $request->validate([
            'is_selected' => 'required|boolean'
        ]);

        $glAccount->update([
            'is_selected' => $request->is_selected
        ]);

        return response()->json([
            'success' => true,
            'message' => 'GL Account selection updated successfully',
            'data' => $glAccount
        ]);
    }

    /**
     * Bulk update selection status.
     */
    public function bulkUpdateSelection(Request $request): JsonResponse
    {
        $request->validate([
            'account_ids' => 'required|array',
            'account_ids.*' => 'exists:gl_accounts,id',
            'is_selected' => 'required|boolean'
        ]);

        GLAccount::whereIn('id', $request->account_ids)
            ->update(['is_selected' => $request->is_selected]);

        return response()->json([
            'success' => true,
            'message' => count($request->account_ids) . ' GL accounts updated successfully'
        ]);
    }

    /**
     * Select all GL accounts.
     */
    public function selectAll(): JsonResponse
    {
        GLAccount::query()->update(['is_selected' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All GL accounts selected successfully'
        ]);
    }

    /**
     * Deselect all GL accounts.
     */
    public function deselectAll(): JsonResponse
    {
        GLAccount::query()->update(['is_selected' => false]);

        return response()->json([
            'success' => true,
            'message' => 'All GL accounts deselected successfully'
        ]);
    }

    /**
     * Update the order of selected accounts.
     */
    public function updateOrder(Request $request): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:gl_accounts,id',
            'order.*.order' => 'required|integer|min:0'
        ]);

        foreach ($request->order as $item) {
            GLAccount::where('id', $item['id'])->update(['display_order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Account order updated successfully'
        ]);
    }

    /**
     * Get GL accounts for AJAX requests.
     */
    public function getAccounts(Request $request): JsonResponse
    {
        $query = GLAccount::query();

        // Filter by search term
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('account_code', 'like', "%{$search}%")
                  ->orWhere('account_name', 'like', "%{$search}%");
            });
        }

        // Filter by account type
        if ($request->has('account_type') && $request->account_type) {
            $query->where('account_type', $request->account_type);
        }

        // When loading for make-parent modal, only show eligible child candidates (single accounts and not the parent itself)
        if ($request->has('context') && $request->context === 'make_parent') {
            $query->where('account_type', 'single');
        }

        // Filter by selection status
        if ($request->has('is_selected') && $request->is_selected !== '') {
            $query->where('is_selected', $request->is_selected);
        }

        $glAccounts = $query->orderBy('account_code')->get();

        return response()->json([
            'success' => true,
            'data' => $glAccounts
        ]);
    }

    /**
     * Get statistics for GL accounts.
     */
    public function getStats()
    {
        $total = GLAccount::count();
        $selected = GLAccount::where('is_selected', true)->count();
        $active = GLAccount::where('is_active', true)->count();
        $parentAccounts = GLAccount::where('account_type', 'parent')->count();
        $selectionPercentage = $total > 0 ? round(($selected / $total) * 100, 1) : 0;

        return response()->json([
            'total' => $total,
            'selected' => $selected,
            'active' => $active,
            'parentAccounts' => $parentAccounts,
            'selectionPercentage' => $selectionPercentage
        ]);
    }

    /**
     * Show the form for editing a GL account.
     */
    public function edit(GLAccount $glAccount)
    {
        $parentAccounts = GLAccount::where('account_type', 'parent')
            ->where('id', '!=', $glAccount->id)
            ->get();

        return response()->json([
            'account' => $glAccount->load('parent', 'children'),
            'parentAccounts' => $parentAccounts
        ]);
    }

    /**
     * Store a newly created GL account.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:50|unique:gl_accounts,account_code',
            'account_name' => 'required|string|max:255',
            'cashflow_type' => 'required|in:receipts,disbursements',
            'is_active' => 'sometimes|boolean',
            'is_selected' => 'sometimes|boolean'
        ]);

        $glAccount = GLAccount::create([
            'account_code' => $validated['account_code'],
            'account_name' => $validated['account_name'],
            'cashflow_type' => $validated['cashflow_type'],
            'is_active' => $request->boolean('is_active', true),
            'is_selected' => $request->boolean('is_selected', false),
            'account_type' => 'single',
            'level' => 1,
            'parent_id' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'GL Account created successfully.',
            'account' => $glAccount
        ], 201);
    }

    /**
     * Update a GL account.
     */
    public function update(Request $request, GLAccount $glAccount)
    {
        $validated = $request->validate([
            'account_code' => 'sometimes|required|string|max:50|unique:gl_accounts,account_code,' . $glAccount->id,
            'account_name' => 'sometimes|required|string|max:255',
            // account_type is managed by relationship actions; not editable in this form
            'cashflow_type' => 'sometimes|required|in:receipts,disbursements',
            // parent_id is managed via relationship actions; not editable in this form
            'is_active' => 'sometimes|boolean',
            'is_selected' => 'sometimes|boolean'
        ]);

        // Parent-child changes are not allowed via this endpoint

        $updateData = [];
        if ($request->has('account_code')) {
            $updateData['account_code'] = $validated['account_code'];
        }
        if ($request->has('account_name')) {
            $updateData['account_name'] = $validated['account_name'];
        }
        if ($request->has('cashflow_type')) {
            $updateData['cashflow_type'] = $validated['cashflow_type'];
        }
        if ($request->has('is_active')) {
            $updateData['is_active'] = $request->boolean('is_active');
        }
        if ($request->has('is_selected')) {
            $updateData['is_selected'] = $request->boolean('is_selected');
        }
        // Do not update account_type here
        $glAccount->update($updateData);

        // Do not change hierarchy level via this endpoint

        return response()->json([
            'success' => true,
            'message' => 'GL Account updated successfully.',
            'account' => $glAccount->load('parent', 'children')
        ]);
    }

    /**
     * Make an account a parent and assign children.
     */
    public function makeParent(Request $request, GLAccount $glAccount)
    {
        $request->validate([
            'child_ids' => 'required|array',
            'child_ids.*' => 'exists:gl_accounts,id'
        ]);

        // Check if any of the selected children are already parents of this account
        foreach ($request->child_ids as $childId) {
            $child = GLAccount::find($childId);
            if ($child && $child->getAllDescendants()->contains($glAccount->id)) {
                return response()->json(['error' => 'Cannot create circular parent-child relationships.'], 422);
            }
        }

        // Update the account to be a parent
        $glAccount->update([
            'account_type' => 'parent',
            'parent_id' => null,
            'level' => 1
        ]);

        // Assign children
        foreach ($request->child_ids as $childId) {
            $child = GLAccount::find($childId);
            if ($child) {
                $child->update([
                    'parent_id' => $glAccount->id,
                    'level' => $glAccount->level + 1,
                    'account_type' => 'child'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Account made parent and children assigned successfully.',
            'account' => $glAccount->load('parent', 'children')
        ]);
    }

    /**
     * Remove parent-child relationships.
     */
    public function removeParentChild(Request $request, GLAccount $glAccount)
    {
        $request->validate([
            'action' => 'required|in:remove_parent,remove_children,remove_all'
        ]);

        switch ($request->action) {
            case 'remove_parent':
                $glAccount->update([
                    'parent_id' => null,
                    'level' => 1,
                ]);
                // If it still has children, remain a parent; otherwise become single
                $glAccount->refresh();
                $glAccount->update([
                    'account_type' => ($glAccount->children()->count() > 0) ? 'parent' : 'single'
                ]);
                break;

            case 'remove_children':
                $glAccount->children()->update([
                    'parent_id' => null,
                    'level' => 1,
                    'account_type' => 'single'
                ]);
                // After removing children, update this account type based on whether it has a parent
                $glAccount->refresh();
                $glAccount->update([
                    'account_type' => ($glAccount->parent) ? 'child' : 'single'
                ]);
                break;

            case 'remove_all':
                $glAccount->update([
                    'parent_id' => null,
                    'level' => 1,
                    'account_type' => 'single'
                ]);
                $glAccount->children()->update([
                    'parent_id' => null,
                    'level' => 1,
                    'account_type' => 'single'
                ]);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Parent-child relationships removed successfully.',
            'account' => $glAccount->load('parent', 'children')
        ]);
    }

    /**
     * Update cashflow type for multiple accounts.
     */
    public function updateCashflowTypes(Request $request)
    {
        $request->validate([
            'account_ids' => 'required|array',
            'account_ids.*' => 'exists:gl_accounts,id',
            'cashflow_type' => 'required|in:receipts,disbursements'
        ]);

        GLAccount::whereIn('id', $request->account_ids)
            ->update(['cashflow_type' => $request->cashflow_type]);

        return response()->json([
            'success' => true,
            'message' => 'Cashflow types updated successfully.'
        ]);
    }

    /**
     * Merge multiple accounts into one main account.
     */
    public function mergeAccounts(Request $request, GLAccount $glAccount)
    {
        $request->validate([
            'account_ids' => 'required|array',
            'account_ids.*' => 'exists:gl_accounts,id',
            'new_account_name' => 'required|string|max:255',
            'new_account_code' => 'required|string|max:50'
        ]);

        // Check if any of the accounts to merge are already merged
        $accountsToMerge = GLAccount::whereIn('id', $request->account_ids)->get();
        foreach ($accountsToMerge as $account) {
            if ($account->isMerged()) {
                return response()->json([
                    'success' => false,
                    'message' => "Account {$account->account_name} is already merged into another account."
                ], 422);
            }
        }

        // Check if the main account is already merged
        if ($glAccount->isMerged()) {
            return response()->json([
                'success' => false,
                'message' => "Main account {$glAccount->account_name} is already merged into another account."
            ], 422);
        }

        // Start transaction
        DB::beginTransaction();

        try {
            // Update the main account with new name and code
            $glAccount->update([
                'account_name' => $request->new_account_name,
                'account_code' => $request->new_account_code,
                'account_type' => 'parent', // Merged accounts become parent type
                'level' => 1
            ]);

            // Store information about merged accounts
            $mergedAccountsInfo = [];
            foreach ($accountsToMerge as $account) {
                $mergedAccountsInfo[] = [
                    'id' => $account->id,
                    'code' => $account->account_code,
                    'name' => $account->account_name,
                    'merged_at' => now()->toISOString()
                ];
            }

            // Update the main account's merged_from field
            $glAccount->update([
                'merged_from' => json_encode($mergedAccountsInfo)
            ]);

            // Mark all accounts to merge as merged into the main account
            GLAccount::whereIn('id', $request->account_ids)->update([
                'merged_into' => $glAccount->id,
                'is_active' => false // Deactivate merged accounts
            ]);

            // Move cashflows from merged accounts to the main account
            foreach ($accountsToMerge as $account) {
                $account->cashflows()->update(['gl_account_id' => $glAccount->id]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Accounts merged successfully.',
                'account' => $glAccount->load('mergedFrom')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to merge accounts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unmerge accounts (restore merged accounts).
     */
    public function unmergeAccounts(Request $request, GLAccount $glAccount)
    {
        if (!$glAccount->hasMergedAccounts()) {
            return response()->json([
                'success' => false,
                'message' => 'This account has no merged accounts to restore.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Get merged accounts info
            $mergedAccountsInfo = $glAccount->getMergedFromArrayAttribute();

            // Restore merged accounts
            foreach ($mergedAccountsInfo as $accountInfo) {
                $account = GLAccount::find($accountInfo['id']);
                if ($account) {
                    $account->update([
                        'merged_into' => null,
                        'is_active' => true
                    ]);
                }
            }

            // Clear merged_from field from main account
            $glAccount->update([
                'merged_from' => null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Accounts unmerged successfully.',
                'account' => $glAccount->load('mergedFrom')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to unmerge accounts: ' . $e->getMessage()
            ], 500);
        }
    }
}
