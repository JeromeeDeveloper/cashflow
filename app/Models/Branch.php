<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'head_id',
    ];

    /**
     * Get the head office user that manages this branch.
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    /**
     * Get the users that belong to this branch.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the cashflow files for this branch.
     */
    public function cashflowFiles(): HasMany
    {
        return $this->hasMany(CashflowFile::class);
    }

    /**
     * Get the cashflows for this branch.
     */
    public function cashflows(): HasManyThrough
    {
        return $this->hasManyThrough(Cashflow::class, CashflowFile::class);
    }
}
