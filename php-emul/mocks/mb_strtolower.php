<?php

use PHPEmul\SymbolicVariable;

function mb_strtolower_mock($emul, $string, $encoding=null)
{
    if ($string instanceof SymbolicVariable) {
        $result = clone $string;
        $regex_value = $result->variable_value;
        $result->variable_value = mb_strtolower($regex_value);
        return $result;
    }
    if ($encoding instanceof SymbolicVariable) {
        $encoding = null;
    }
	return mb_strtolower($string, $encoding);
}