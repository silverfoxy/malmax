<?php

function setcookie_mock(PHPEmul\Emulator $emul, $name, $value)
{
    if (is_string($value)) {
        $value_var = new \PhpParser\Node\Scalar\String_($value);
    }
    elseif ($value === null) {
        $value_var = null;
    }

	$emul->variable_set(new \PhpParser\Node\Expr\ArrayDimFetch(
	        new \PhpParser\Node\Expr\Variable('_COOKIE'),
            new \PhpParser\Node\Scalar\String_($name)),
        $value_var);
    // $_COOKIE[$name] = $value;
	return true;
}
