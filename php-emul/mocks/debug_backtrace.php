<?php

function debug_backtrace_mock(emul\Emulator $emul, $options=0, $limit=0)
{
	// return $emul->backtrace($options,$limit);
    return [];
}
