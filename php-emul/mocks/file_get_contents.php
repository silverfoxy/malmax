<?php

function file_get_contents_mock(PHPEmul\Emulator $emul, $filename)
{
    if ($filename === 'php://input') {
        return new \PHPEmul\SymbolicVariable();
    }
	return file_get_contents($filename);
}
