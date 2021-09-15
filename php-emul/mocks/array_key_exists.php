<?php

function array_key_exists_mock(emul\Emulator $emul, $key, $array)
{
	if ($key instanceof emul\SymbolicVariable || $array instanceof emul\SymbolicVariable) {
	    return new emul\SymbolicVariable();
    }
	else {
	    if (!is_int($key) && ! is_string($key)) {
	        $emul->verbose(sprintf('[Warning] Invalid array_key_exists (%s, %d): %s'.PHP_EOL, $emul->current_file, $emul->current_line, print_r($key, true)));
	        return false;
        }
	    else {
            return array_key_exists($key, $array);
        }
    }
}
