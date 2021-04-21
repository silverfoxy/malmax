<?php

function session_cache_limiter_mock(PHPEmul\Emulator $emul, string $cache_limiter)
{
	$emul->cache_limiter = $cache_limiter;
	return $cache_limiter;
}
