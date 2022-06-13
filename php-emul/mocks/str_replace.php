<?php

use PHPEmul\SymbolicVariable;
use PhpParser\Node\Scalar\String_;

function str_replace_mock($emul, $search, $replace, $subject, &$count=null)
{
    if ($search instanceof SymbolicVariable || $replace instanceof SymbolicVariable) {
        $count = new SymbolicVariable('Symbolic number of str replacements');
        return new SymbolicVariable('str_replace w symbolic s/r', '*');
    }
    elseif ($subject instanceof SymbolicVariable) {
        $result = clone $subject;
        $regex_value = $result->variable_value;
        $result->variable_value = str_replace($search, $replace, $regex_value, $count);
        $result->type = String_::class;
        if (sizeof($result->concrete_values) > 0) {
            array_walk($result->concrete_values, function (&$value, $key, $params) {
                $search = $params[0];
                $replace = $params[1];
                $value = str_replace($search, $replace, $value);
            }, [$search, $replace]);
        }
        return $result;
    }
    else {
        return str_replace($search, $replace, $subject, $count);
    }
}