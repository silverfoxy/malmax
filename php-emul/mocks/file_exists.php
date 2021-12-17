<?php

use PHPEmul\SymbolicVariable;
use PhpParser\Node\Expr\Assign;

function file_exists_mock($emul, $filename)
{
    if ($filename instanceof SymbolicVariable) {
        if ($filename->variable_value === '*') {
            // Not useful to return all files in current dir for *
            return $filename;
        }
        $files = $emul->get_candidate_files($filename->variable_value);
        if (sizeof($files) === 0) {
            return false;
        }
        elseif (sizeof($files) === 1) {
            $file = $files[0];
            $emul->variable_set($emul->mocked_core_function_args[0]->value, $file);
            return file_exists($file);
        }
        else {
            while(sizeof($files) > 1) {
                $file = array_pop($files);
                $forked_process_info = $emul->fork_execution([$file => range(1, 100)]);
                list($pid, $child_pid) = $forked_process_info;
                if ($child_pid === 0) {
                    $emul->variable_set($emul->mocked_core_function_args[0]->value, $file);
                    return file_exists($file);
                }
            }
            $file = array_pop($files);
            $variable = $emul->mocked_core_function_args[0]->value;
            if ($variable instanceof Assign) {
                $variable = $variable->var;
            }
            $emul->variable_set($variable, $file);
            return file_exists($file);
        }
    }
    else {
        return file_exists($filename);
    }
}