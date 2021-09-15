<?php

function session_set_cookie_params_mock(emul\Emulator $emul, int $lifetime, string $path = '', string $domain = '', bool $secure = false, bool $httponly = false)
{
	// Currently, all cookies are symbolic so no need to update the cookies
    // return true on success
    return true;
}
