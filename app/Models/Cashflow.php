<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cashflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashflow_file_id',
        'branch_id',
        'gl_account_id',
        'section',
        'year',
        'month',
        'period',
        'actual_amount',
        'projection_percentage',
        'projected_amount',
        'period_values',
        'total',
        'cash_beginning_balance',
        'total_cash_available',
        'less_disbursements',
        'total_disbursements',
        'cash_ending_balance',
    ];

    protected $casts = [
        'year' => 'integer',
        'actual_amount' => 'decimal:2',
        'projection_percentage' => 'decimal:2',
        'projected_amount' => 'decimal:2',
        'period_values' => 'array',
        'total' => 'decimal:2',
        'cash_beginning_balance' => 'decimal:2',
        'total_cash_available' => 'decimal:2',
        'less_disbursements' => 'decimal:2',
        'total_disbursements' => 'decimal:2',
        'cash_ending_balance' => 'decimal:2',
    ];

    /**
     * Get the cashflow file that owns this cashflow.
     */
    public function cashflowFile(): BelongsTo
    {
        return $this->belongsTo(CashflowFile::class);
    }

    /**
     * Get the branch that owns this cashflow.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the GL account that owns this cashflow.
     */
    public function glAccount(): BelongsTo
    {
        return $this->belongsTo(GLAccount::class);
    }
}
