<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashflowFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'original_name',
        'file_type',
        'year',
        'month',
        'branch_id',
        'uploaded_by',
        'status',
        'description',
    ];

    protected $casts = [
        'year' => 'integer',
        'status' => 'string',
    ];

    /**
     * Get the branch that owns this cashflow file.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the cashflows for this file.
     */
    public function cashflows(): HasMany
    {
        return $this->hasMany(Cashflow::class);
    }

    /**
     * Get the user who uploaded this file.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope to filter files by year.
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope to filter files by month.
     */
    public function scopeForMonth($query, $month)
    {
        return $query->where('month', $month);
    }

    /**
     * Scope to filter files by branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
