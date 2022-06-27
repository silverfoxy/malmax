<?php

use PHPEmul\SymbolicVariable;
use PhpParser\Node\Scalar;

function is_string_mock($emul, $value)
{
    if ($value instanceof SymbolicVariable) {
        if (strpos($value->type, Scalar\String_::class) !== false) {
            // strpos allows for inheritance (Scalar\String_ vs Scalar).
            return true;
        }
        else {
            return new SymbolicVariable('is_string', '*', Scalar::class, true);
        }
    }
    else {
        return is_string($value);
    }
}