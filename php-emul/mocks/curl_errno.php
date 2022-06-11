<?php

function curl_errno_mock(PHPEmul\Emulator $emul, $ch)
{
    $errno = call_user_func('curl_errno', $ch);
    if ($errno !== 0) {
        return new \PHPEmul\SymbolicVariable('curl_errno', '*', PhpParser\Node\Scalar\LNumber::class, true);
    }
    else {
        return $errno;
    }
}
