<?php

use PHPEmul\SymbolicVariable;

function mb_strtoupper_mock($emul, $string, $encoding=null)
{
    if ($string instanceof SymbolicVariable) {
        $regex_value = $string->variable_value;
        return mb_strtoupper($regex_value);;
    }
    if ($encoding instanceof SymbolicVariable) {
        $encoding = null;
    }
	return mb_strtoupper($string, $encoding);
}