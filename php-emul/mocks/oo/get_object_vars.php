<?php
function get_object_vars_mock($emul,$object)
{
	if (!$object instanceof PHPEmul\EmulatorObject) {
        return get_object_vars($object);
    }
    else {
        return $object->properties;
    }
}