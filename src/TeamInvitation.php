<?php

namespace Malico\Teams;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class TeamInvitation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'role',
        'invited_by_id',
    ];

    /**
     * Get the team that the invitation belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(Teams::teamModel());
    }

    /**
     * Get the user who sent the invitation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invitedBy()
    {
        return $this->belongsTo(Teams::userModel(), 'invited_by_id');
    }

    public function role(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value) {
                    return Teams::findRole($value);
                }

                return null;
            },
            set: function (string|Role|null $value) {
                if (! $value) {
                    return null;
                }
                if ($value instanceof Role) {
                    return ['role' => $value->key];
                }

                return ['role' => $value];
            }
        );
    }
}
