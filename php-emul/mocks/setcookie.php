<?php

function setcookie_mock(PHPEmul\Emulator $emul, $name, $value)
{
	$_COOKIE[$name] = $value;
	return true;
}
