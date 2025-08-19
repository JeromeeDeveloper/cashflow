<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GLAccount extends Model
{
    use HasFactory;

    protected $table = 'gl_accounts';

    protected $fillable = [
        'account_code',
        'account_name',
        'parent_id',
        'account_type',
        'level',
        'is_active',
        'is_selected',
        'cashflow_type',
        'merged_into',
        'merged_from',
    ];

    /**
     * Get the cashflows for this GL account.
     */
    public function cashflows(): HasMany
    {
        return $this->hasMany(Cashflow::class, 'gl_account_id');
    }

    /**
     * Get the parent account.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(GLAccount::class, 'parent_id');
    }

    /**
     * Get the child accounts.
     */
    public function children(): HasMany
    {
        return $this->hasMany(GLAccount::class, 'parent_id');
    }

    /**
     * Get all descendants (children, grandchildren, etc.).
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors (parent, grandparent, etc.).
     */
    public function ancestors(): BelongsTo
    {
        return $this->parent()->with('ancestors');
    }

    /**
     * Check if this is a parent account.
     */
    public function isParent(): bool
    {
        return $this->account_type === 'parent';
    }

    /**
     * Check if this is a detail account.
     */
    public function isDetail(): bool
    {
        return $this->account_type === 'detail';
    }

    /**
     * Check if this is a summary account.
     */
    public function isSummary(): bool
    {
        return $this->account_type === 'summary';
    }

    /**
     * Get the full hierarchical name (e.g., "Loan Collection > Principal").
     */
    public function getHierarchicalNameAttribute(): string
    {
        $name = $this->account_name;
        $parent = $this->parent;

        while ($parent) {
            $name = $parent->account_name . ' > ' . $name;
            $parent = $parent->parent;
        }

        return $name;
    }

    /**
     * Get the account type for cashflow categorization (receipts/disbursements).
     */
    public function getCashflowTypeAttribute($value): string
    {
        // If explicitly set in DB, always honor it
        if (!is_null($value) && $value !== '') {
            return $value;
        }

        // Otherwise, infer based on name/code patterns as a fallback
        $accountName = strtolower($this->account_name ?? '');
        $accountCode = strtolower($this->account_code ?? '');

        $receiptPatterns = [
            'income', 'revenue', 'collection', 'receipt', 'payment', 'loan', 'interest',
            'fee', 'commission', 'sale', 'rent', 'dividend', 'refund'
        ];

        $disbursementPatterns = [
            'expense', 'cost', 'payment', 'disbursement', 'outlay', 'expenditure',
            'purchase', 'salary', 'rent', 'utility', 'maintenance', 'repair'
        ];

        foreach ($receiptPatterns as $pattern) {
            if (str_contains($accountName, $pattern) || str_contains($accountCode, $pattern)) {
                return 'receipts';
            }
        }

        foreach ($disbursementPatterns as $pattern) {
            if (str_contains($accountName, $pattern) || str_contains($accountCode, $pattern)) {
                return 'disbursements';
            }
        }

        // Final fallback
        return 'disbursements';
    }

    /**
     * Get all descendants including children, grandchildren, etc.
     */
    public function getAllDescendants()
    {
        return $this->children()->with('children')->get();
    }

    /**
     * Check if account has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get the most common cashflow type from this account's cashflows.
     */
    public function getMostCommonCashflowType(): ?string
    {
        $cashflows = $this->cashflows()->select('section')->get();

        if ($cashflows->isEmpty()) {
            return null;
        }

        $sections = $cashflows->pluck('section')->filter();

        if ($sections->isEmpty()) {
            return null;
        }

        // Count occurrences of each section
        $sectionCounts = $sections->countBy();

        // Get the section with the highest count
        $mostCommon = $sectionCounts->sortDesc()->keys()->first();

        return $mostCommon;
    }

    /**
     * Get the full account name with code.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->account_code} - {$this->account_name}";
    }

    /**
     * Scope to filter by account code.
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('account_code', $code);
    }

    /**
     * Scope to filter by account name.
     */
    public function scopeByName($query, $name)
    {
        return $query->where('account_name', 'LIKE', '%' . $name . '%');
    }

    /**
     * Get the account this account is merged into.
     */
    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(GLAccount::class, 'merged_into');
    }

    /**
     * Get accounts that were merged into this account.
     */
    public function mergedFrom(): HasMany
    {
        return $this->hasMany(GLAccount::class, 'merged_into');
    }

    /**
     * Check if this account has been merged into another account.
     */
    public function isMerged(): bool
    {
        return !is_null($this->merged_into);
    }

    /**
     * Check if this account has other accounts merged into it.
     */
    public function hasMergedAccounts(): bool
    {
        return $this->mergedFrom()->exists();
    }

    /**
     * Get the merged accounts as an array.
     */
    public function getMergedFromArrayAttribute(): array
    {
        return $this->merged_from ? json_decode($this->merged_from, true) : [];
    }

    /**
     * Set the merged accounts array.
     */
    public function setMergedFromArrayAttribute($value): void
    {
        $this->merged_from = json_encode($value);
    }

    /**
     * Scope to get only non-merged accounts.
     */
    public function scopeNotMerged($query)
    {
        return $query->whereNull('merged_into');
    }

    /**
     * Scope to get only merged accounts.
     */
    public function scopeMerged($query)
    {
        return $query->whereNotNull('merged_into');
    }
}
