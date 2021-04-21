<?php
function class_exists_mock(PHPEmul\OOEmulator $emul,$name,$autoload=true)
{
	if ($autoload) $emul->autoload($name);
	return $emul->class_exists($name);
}
