<?php
function session_write_close_mock($emul,$options=array())
{
	$emul->verbose("Calling session_write_close, ignored by emulator, returning true...\n",3);
	return true;
}