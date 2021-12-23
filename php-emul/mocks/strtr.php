<?php

use PHPEmul\SymbolicVariable;
use PhpParser\Node\Scalar\String_;


/**
 * strtr has two signatures, one signature requires string, from, to
 * The other requries string, and array replace_pairs
 * If 2 arguments are given (to=null), we switch to the second signature
 * @param $emul
 * @param $string
 * @param $from
 * @param $to
 * @return SymbolicVariable|string
 */
function strtr_mock($emul, $string, $from, $to=null)
{
    if ($from instanceof SymbolicVariable || $to instanceof  SymbolicVariable) {
        return new SymbolicVariable('strtr symbolic from or to', '*');
    }
    elseif ($string instanceof SymbolicVariable) {
        if ($to === null) {
            $array_from_to = $from;
            $result = clone $string;
            foreach ($array_from_to as $from => $to) {
                $result = strtr_mock($emul, $result, $from, $to);
            }
            return $result;
        }
        $result = clone $string;
        $regex_value = $result->variable_value;
        $result->variable_value = strtr($regex_value, $from, $to);
        $result->type = String_::class;
        return $result;
    }
    else {
        return strtr($string, $from, $to);
    }
}