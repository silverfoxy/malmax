<?php

use PHPEmul\SymbolicVariable;

function is_object_mock($emul, $value)
{
    if($value instanceof SymbolicVariable){
        if($value->type === Node\Stmt\ClassLike::class){
            return true;
        }
        else{
            return new SymbolicVariable('is_object', '*', \PhpParser\Node\Scalar::class, true);
        }
    }
    else{
        return is_object($value);
    }
}