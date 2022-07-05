<?php

function file_get_contents_mock(PHPEmul\Emulator $emul, $filename)
{
    if ($filename === 'php://input') {
        return new \PHPEmul\SymbolicVariable();
    }
    else if ($filename instanceof \PHPEmul\SymbolicVariable) {
        $files = $emul->get_candidate_files($filename->variable_value);
        if (sizeof($files) === 0) {
            // Failure
            return false;
        }
        elseif (sizeof($files) > 10) { // Too many files, potential wildcard
            $symbolic_file = clone $filename;
            $symbolic_file->isset = new \PHPEmul\SymbolicVariable();
            return $symbolic_file;
        }
        elseif (sizeof($files) === 1) {
            $file = $files[0];
            if (isset(end($emul->mocked_core_function_args)[0]->value)) {
                $emul->variable_set(end($emul->mocked_core_function_args)[0]->value, $file);
            }
            return file_get_contents($file);
        }
        else {
            while(sizeof($files) > 1) {
                $file = array_pop($files);
                $forked_process_info = $emul->fork_execution([$file => range(1, rand(2, 20))]);
                list($pid, $child_pid) = $forked_process_info;
                if ($child_pid === 0) {
                    if (isset(end($emul->mocked_core_function_args)[0]->value)) {
                        $emul->variable_set(end($emul->mocked_core_function_args)[0]->value, $file);
                    }
                    return file_get_contents($file);
                }
            }
            $file = array_pop($files);
            $variable = end($emul->mocked_core_function_args)[0]->value;
            if ($variable instanceof Assign) {
                $variable = $variable->var;
            }
            $emul->variable_set($variable, $file);
            return file_get_contents($file);
        }
    }
    else {
        return file_get_contents($filename);
    }
}
