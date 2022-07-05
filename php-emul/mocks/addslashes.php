<?php

function addslashes_mock(PHPEmul\Emulator $emul, $string)
{
    // This preserve the isset property of symbolic variables
	if ($string instanceof \PHPEmul\SymbolicVariable) {
        return $string;
    }
    else {
        return addslashes($string);
    }
}
