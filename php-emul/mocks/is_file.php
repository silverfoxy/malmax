<?php

use PHPEmul\SymbolicVariable;

function is_file_mock($emul, $filename)
{
    if ($filename instanceof SymbolicVariable) {
        $files = $emul->get_candidate_files($filename->variable_value);
        if (sizeof($files) === 0) {
            return false;
        }
        elseif (sizeof($files) === 1) {
            return is_file(files[0]);
        }
        else {
            while(sizeof($files) > 1) {
                $file = array_pop($files);
                $forked_process_info = $emul->fork_execution([$file => []]);
                list($pid, $child_pid) = $forked_process_info;
                if ($child_pid === 0) {
                    return is_file($file);
                }
            }
            $file = array_pop($files);
            return is_file($file);
        }
    }
    else {
        return is_file($filename);
    }
}