<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GLAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GLAccountsController extends Controller
{
        /**
     * Display the GL accounts management page.
     */
    public function index()
    {
        $glAccounts = GLAccount::with(['parent', 'children', 'cashflows'])
            ->orderBy('account_code')
            ->get();

        // Separate selected and unselected accounts
        $selectedAccounts = $glAccounts->where('is_selected', true);
        $unselectedAccounts = $glAccounts->where('is_selected', false);

        // Group by parent accounts
        $parentAccounts = $glAccounts->where('account_type', 'parent');
        $detailAccounts = $glAccounts->where('account_type', 'detail');
        $summaryAccounts = $glAccounts->where('account_type', 'summary');

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
     * Update a GL account.
     */
    public function update(Request $request, GLAccount $glAccount)
    {
        $request->validate([
            'account_code' => 'required|string|max:50|unique:gl_accounts,account_code,' . $glAccount->id,
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:parent,detail,summary',
            'cashflow_type' => 'required|in:receipts,disbursements',
            'parent_id' => 'nullable|exists:gl_accounts,id',
            'is_active' => 'boolean',
            'is_selected' => 'boolean'
        ]);

        // Check if trying to set as parent of itself
        if ($request->parent_id == $glAccount->id) {
            return response()->json(['error' => 'An account cannot be its own parent.'], 422);
        }

        // Check if trying to set a parent account as child of its own child
        if ($request->parent_id) {
            $potentialParent = GLAccount::find($request->parent_id);
            if ($potentialParent && $glAccount->getAllDescendants()->contains($potentialParent->id)) {
                return response()->json(['error' => 'Cannot set a parent account as child of its own descendant.'], 422);
            }
        }

        $glAccount->update([
            'account_code' => $request->account_code,
            'account_name' => $request->account_name,
            'account_type' => $request->account_type,
            'parent_id' => $request->parent_id,
            'is_active' => $request->boolean('is_active'),
            'is_selected' => $request->boolean('is_selected')
        ]);

        // Update level based on parent
        if ($request->parent_id) {
            $parent = GLAccount::find($request->parent_id);
            $glAccount->update(['level' => $parent->level + 1]);
        } else {
            $glAccount->update(['level' => 1]);
        }

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
                    'level' => $glAccount->level + 1
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
                    'level' => 1
                ]);
                break;

            case 'remove_children':
                $glAccount->children()->update([
                    'parent_id' => null,
                    'level' => 1
                ]);
                break;

            case 'remove_all':
                $glAccount->update([
                    'parent_id' => null,
                    'level' => 1
                ]);
                $glAccount->children()->update([
                    'parent_id' => null,
                    'level' => 1
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
}
