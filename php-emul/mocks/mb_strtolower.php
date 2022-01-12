<?php

use PHPEmul\SymbolicVariable;
use PhpParser\Node\Scalar\String_;

function mb_strtolower_mock($emul, $string, $encoding=null)
{
    if ($string instanceof SymbolicVariable) {
        $result = clone $string;
        $regex_value = $result->variable_value;
        $result->variable_value = mb_strtolower($regex_value);
        $result->type = String_::class;
        return $result;
    }
    if ($encoding instanceof SymbolicVariable) {
        $encoding = null;
    }
	return mb_strtolower($string, $encoding);
}