<?php

function header_mock(emul\Emulator $emul, $string, $replace=true, $http_respnse_code=null)
{
	$emul->verbose("Header: {$string}".PHP_EOL,1);
}
