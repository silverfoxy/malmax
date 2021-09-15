<?php

function session_cache_limiter_mock(emul\Emulator $emul, string $cache_limiter)
{
	$emul->cache_limiter = $cache_limiter;
	return $cache_limiter;
}
