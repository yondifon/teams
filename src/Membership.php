<?php

namespace Malico\Teams;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;

abstract class Membership extends Pivot
{
    /**
     * The table associated with the pivot model.
     *
     * @var string
     */
    protected $table = 'team_user';

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
