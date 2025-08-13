<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'branch_id',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the branch that the user belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Check if the user is a branch user.
     */
    public function isBranchUser(): bool
    {
        return $this->role === 'branch' && $this->branch_id !== null;
    }

    /**
     * Check if the user is a head office user.
     */
    public function isHeadUser(): bool
    {
        return $this->role === 'head';
    }

    /**
     * Check if the user is an admin user.
     */
    public function isAdminUser(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Update the last login timestamp.
     */
    public function updateLastLogin(): void
    {
        try {
            $this->update(['last_login_at' => now()]);
        } catch (\Exception $e) {
            // If the column doesn't exist yet, just log the error and continue
            Log::warning('Could not update last_login_at: ' . $e->getMessage());
        }
    }
}
