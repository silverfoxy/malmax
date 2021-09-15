<?php
function session_name_mock(emul\Emulator $emul, string $name='')
{
	if ($name !== '') {
	    $previous_name = $emul->session_name;
	    $emul->session_name = $name;
	    return $previous_name;
    }
	else {
	    return $emul->session_name;
    }
}