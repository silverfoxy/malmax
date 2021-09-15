<?php

function debug_print_backtrace_mock(emul\Emulator $emul, $options=0, $limit=0)
{
	$emul->output($emul->print_backtrace($options,$limit));
}
