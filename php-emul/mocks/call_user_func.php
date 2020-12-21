<?php

function call_user_func_mock(PHPEmul\Emulator $emul,  $callback)
{
	$args=func_get_args();
	array_shift($args); //$emul
	array_shift($args); //$callback
	return $emul->call_function($callback, $args);
}
