<?php

namespace App\Models;

use Malico\Teams\Membership as TeamsMembership;

class Membership extends TeamsMembership
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
