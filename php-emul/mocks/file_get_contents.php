<?php

function file_get_contents_mock(emul\Emulator $emul, $filename)
{
    if ($filename === 'php://input') {
        return new emul\SymbolicVariable();
    }
	return file_get_contents($filename);
}
