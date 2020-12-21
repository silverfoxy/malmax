<?php 
function set_error_handler_mock($emul,$handler,$error_reporting=E_ERROR)
{
	return $emul->set_error_handler($handler,$error_reporting);
}