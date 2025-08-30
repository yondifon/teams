<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Malico\Teams\Events\TeamCreated;
use Malico\Teams\Events\TeamDeleted;
use Malico\Teams\Events\TeamUpdated;
use Malico\Teams\Team as BaseTeam;

class Team extends BaseTeam
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get defualt attributes for the model.
     *
     * @return array<string, mixed>
     */
    public $attributes = [
        'personal_team' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
        ];
    }
}
