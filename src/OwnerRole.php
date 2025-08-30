<?php

namespace Malico\Teams;

class OwnerRole extends Role
{
    public function __construct()
    {
        parent::__construct('owner', 'Owner', ['*']);
    }
}
