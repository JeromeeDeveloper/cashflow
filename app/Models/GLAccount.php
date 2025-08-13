<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GLAccount extends Model
{
    use HasFactory;

    protected $table = 'gl_accounts';

    protected $fillable = [
        'account_code',
        'account_name',
    ];

    /**
     * Get the cashflows for this GL account.
     */
    public function cashflows(): HasMany
    {
        return $this->hasMany(Cashflow::class, 'gl_account_id');
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
