<?php

function header_remove_mock(PHPEmul\Emulator $emul,$string)
{
	$emul->verbose("Header removed: {$string}".PHP_EOL,4);
}
