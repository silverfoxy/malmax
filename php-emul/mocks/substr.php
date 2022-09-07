<?php

use PHPEmul\SymbolicVariable;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeAbstract;

function substr_mock($emul, $string, $offset, $length=null)
{
    if ($offset instanceof SymbolicVariable || $length instanceof  SymbolicVariable) {
        return new SymbolicVariable('substr symbolic needle or offset', '*');
    }
    elseif ($string instanceof SymbolicVariable) {
        $result = clone $string;
        $regex_value = $result->variable_value;
        $substr = $length === null ? substr($regex_value, $offset) : substr($regex_value, $offset, $length);
        // Is the substring still symbolic?
        if ($substr === false) {
            return $result;
        }
        elseif (strpos($substr, '*') !== false) {
            $result->variable_value = $substr;
            return $result;
        }
        else { // Substring is concrete
            return $substr;
        }
    }
    else {
        return $length === null ? substr($string, $offset) : substr($string, $offset, $length);
    }
}