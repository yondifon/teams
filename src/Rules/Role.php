<?php

namespace Malico\Teams\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Malico\Teams\Teams;

class Role implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! in_array($value, array_keys(Teams::$roles))) {
            $fail('The :attribute must be a valid role.')->translate();
        }
    }
}
