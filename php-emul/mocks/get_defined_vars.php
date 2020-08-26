<?php

function get_defined_vars_mock(PHPEmul\Emulator $emul)
{
	echo "mocked get_defined_vars called!",PHP_EOL;
	return $emul->variables;
}
