<?php

use PHPEmul\SymbolicVariable;

function strtr_mock($emul, $string, $from, $to)
{
    if ($from instanceof SymbolicVariable || $to instanceof  SymbolicVariable) {
        return new SymbolicVariable('strtr symbolic from or to', '*');
    }
    elseif ($string instanceof SymbolicVariable) {
        $result = clone $string;
        $regex_value = $result->variable_value;
        $result->variable_value = strtr($regex_value, $from, $to);
        return $result;
    }
    else {
        return strtr($string, $from, $to);
    }
}