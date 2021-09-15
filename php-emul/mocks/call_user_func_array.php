<?php

function call_user_func_array_mock(emul\Emulator $emul, $callback , array $param_arr )
{
	return $emul->call_function($callback,$param_arr);
}
