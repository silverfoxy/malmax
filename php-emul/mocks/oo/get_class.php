<?php
function get_class_mock($emul, $object=null)
{
	if ($object===null)
		$object= $emul->get_current_this();
	return $emul->get_class($object);
}