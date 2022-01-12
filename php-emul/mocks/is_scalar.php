<?php

use PHPEmul\SymbolicVariable;
use PhpParser\Node\Scalar;

function is_scalar_mock($emul, $value)
{
    if ($value instanceof SymbolicVariable) {
        if (strpos($value->type, Scalar::class) !== false) {
            // strpos allows for inheritance (Scalar\String_ vs Scalar).
            return true;
        }
        else {
            return new SymbolicVariable('is_scalar', '*', Scalar::class, true);
        }
    }
    else {
        return is_scalar($value);
    }
}