<?php

use PHPEmul\Emulator;
use PHPEmul\SymbolicVariable;

function in_array_mock(Emulator $emul, $arg_references, $needle, $haystack , $strict=false)
{
    if ($haystack instanceof SymbolicVariable) {
        if ($needle instanceof SymbolicVariable) {
            return new $needle;
        }
        else {
            return new SymbolicVariable('in_array');
        }
    }
    else {
        // Concrete haystack
        if ($needle instanceof SymbolicVariable) {
            // Fork
            $forked_process_info = $emul->fork_execution([]);
            list($pid, $child_pid) = $forked_process_info;
            if ($child_pid !== 0) {
                while (sizeof($haystack) > 0) {
                    // fork
                    $element = array_shift($haystack);
                    if (sizeof($haystack) > 0) {
                        // If there are items in $haystack, fork to run them
                        $forked_process_info = $emul->fork_execution([]);
                        list($pid, $child_pid) = $forked_process_info;
                    }
                    else {
                        // If no items remaining in $haystack, just run with the existing one
                        $child_pid = 0;
                    }
                    if ($child_pid !== 0) {
                        $emul->variable_set($arg_references[0]->value, $element);
                        return true;
                    }
                }
            } else {
                return false;
            }
        }
        else {
            return in_array($needle, $haystack, $strict);
        }
    }
}