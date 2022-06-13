<?php

use PHPEmul\SymbolicVariable;

function in_array_mock($emul, $needle, $haystack, $strict=false)
{
    if ($needle instanceof SymbolicVariable) {
        if ($haystack instanceof SymbolicVariable) {
            return $haystack;
        }
        else {
            $needle->concrete_values = $haystack;
            $emul->variable_set(end($emul->mocked_core_function_args)[0]->value, $needle);
            return $needle;
        }
    }
    elseif ($haystack instanceof SymbolicVariable) {
        return $haystack;
    }
    else {
        return in_array($needle, $haystack, $strict);
    }
}