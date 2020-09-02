<?php

function array_key_exists_mock(PHPEmul\Emulator $emul, $key, $array)
{
	if ($key instanceof \PHPEmul\SymbolicVariable || $array instanceof \PHPEmul\SymbolicVariable) {
	    return new \PHPEmul\SymbolicVariable();
    }
	else {
        return array_key_exists($key, $array);
    }
}
