<?php

namespace App\Traits;

trait BooleanTrait
{
    /**
     * @param mixed $input
     * @return bool
     */
    protected function toBool(mixed $input): bool
    {
        if (is_bool($input)) {
            return $input;
        }
        if (is_numeric($input)) {
            return $input > 0;
        }
        if (is_string($input)) {
            $first = strtoupper(substr($input, 0, 1));
            return $first === 'T';
        }

        return false;
    }

}