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
    ];
}
