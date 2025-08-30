<?php

namespace App\Models;

use Malico\Teams\Membership as BaseMembership;

class Membership extends BaseMembership
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
