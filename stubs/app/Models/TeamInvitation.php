<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Malico\Teams\TeamInvitation as BaseTeamInvitation;

class TeamInvitation extends BaseTeamInvitation
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'role',
        'invited_by_id',
        'expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
