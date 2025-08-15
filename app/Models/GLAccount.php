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
    public function getCashflowTypeAttribute(): string
    {
        // Determine if this account is typically for receipts or disbursements
        // based on account type and name patterns
        $accountName = strtolower($this->account_name ?? '');
        $accountCode = strtolower($this->account_code ?? '');

        // Receipts patterns
        $receiptPatterns = [
            'income', 'revenue', 'collection', 'receipt', 'payment', 'loan', 'interest',
            'fee', 'commission', 'sale', 'rent', 'dividend', 'refund'
        ];

        // Disbursements patterns
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

        // Default based on account type
        return in_array($this->account_type, ['Income', 'Asset']) ? 'receipts' : 'disbursements';
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
}
