<?php

function debug_backtrace_mock(PHPEmul\Emulator $emul,$options=0,$limit=0)
{
	// return $emul->backtrace($options,$limit);
    return [];
}
