<?php

function header_remove_mock(emul\Emulator $emul, $string)
{
	$emul->verbose("Header removed: {$string}".PHP_EOL,4);
}
