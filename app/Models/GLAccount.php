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
