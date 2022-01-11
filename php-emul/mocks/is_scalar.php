<?php

use PHPEmul\SymbolicVariable;
use PhpParser\Node\Scalar;

function is_scalar_mock($emul, $value)
{
    if ($value instanceof SymbolicVariable) {
        if ($value->type === Scalar::class) {
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