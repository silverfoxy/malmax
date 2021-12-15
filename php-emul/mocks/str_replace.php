<?php

use PHPEmul\SymbolicVariable;

function str_replace_mock($emul, $search, $replace, $subject, &$count=null)
{
    if ($search instanceof SymbolicVariable || $replace instanceof SymbolicVariable) {
        $count = new SymbolicVariable('Symbolic number of str replacements');
        return new SymbolicVariable('str_replace w symbolic s/r', '*');
    }
    elseif ($subject instanceof SymbolicVariable) {
        $regex_value = $subject->variable_value;
        $new_value = str_replace($search, $replace, $regex_value, $count);
        $subject->variable_value = $new_value;
        return $subject;
    }
    else {
        return str_replace($search, $replace, $subject, $count);
    }
}