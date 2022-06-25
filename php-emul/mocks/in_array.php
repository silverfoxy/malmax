<?php

use PHPEmul\SymbolicVariable;

function in_array_mock($emul, $needle, $haystack, $strict=false)
{
    if ($needle instanceof SymbolicVariable) {
        if ($haystack instanceof SymbolicVariable) {
            return $haystack;
        }
        else {
            if ($emul->fork_on_symbolic_in_array === true) {
                foreach ($haystack as $value) {
                    // fork
                    $forked_process_info = $emul->fork_execution(['AD_in_array_'.$value => range(1, 100)]);
                    list($pid, $child_pid) = $forked_process_info;
                    if ($child_pid === 0) {
                        if (isset(end($emul->mocked_core_function_args)[0]->value)) {
                            $emul->variable_set(end($emul->mocked_core_function_args)[0]->value, $value);
                        }
                        return true;
                    }
                }
                return false;
            }
            else {
                $needle->concrete_values = $haystack;
                $emul->variable_set(end($emul->mocked_core_function_args)[0]->value, $needle);
                return $needle;
            }
        }
    }
    elseif ($haystack instanceof SymbolicVariable) {
        return $haystack;
    }
    else {
        return in_array($needle, $haystack, $strict);
    }
}