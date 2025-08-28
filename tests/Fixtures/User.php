<?php

namespace Malico\Teams\Tests\Fixtures;

use App\Models\User as BaseUser;
use Malico\Teams\HasTeams;

class User extends BaseUser
{
    use HasTeams;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
