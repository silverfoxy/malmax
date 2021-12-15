<?php

use PHPEmul\SymbolicVariable;

function mb_strtolower_mock($emul, $string, $encoding=null)
{
    if ($string instanceof SymbolicVariable) {
        $regex_value = $string->variable_value;
        $new_value = mb_strtolower($regex_value);
        $string->variable_value = $new_value;
        return $string;
    }
    if ($encoding instanceof SymbolicVariable) {
        $encoding = null;
    }
	return mb_strtolower($string, $encoding);
}