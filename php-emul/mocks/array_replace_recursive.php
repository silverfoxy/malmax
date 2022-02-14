<?php

function array_replace_recursive_mock(PHPEmul\Emulator $emul, $array, ...$replacements)
{
    if (!$array instanceof \PHPEmul\SymbolicVariable) {
        // Skip replacing a concrete array with a Symbol
        foreach ($replacements as $replacement) {
            if ($replacement instanceof \PHPEmul\SymbolicVariable) {
                return $array;
            }
        }
    }
    return array_replace_recursive($array, ...$replacements);
}
