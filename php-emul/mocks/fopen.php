<?php
function fopen_mock($emul, $filename, $mode, $use_include_path = false, $context = null)
{
    if ($filename instanceof \PHPEmul\SymbolicVariable) {
        return $filename;
    }
    else {
        // For /tmp/php* file names, return Symbol
        if (strpos($filename, '/tmp/php') !== false ) {
            return new \PHPEmul\SymbolicVariable($filename, '*', \PhpParser\NodeAbstract::class, true);
        }
        else {
            return fopen($filename, $mode, $use_include_path, $context);
        }
    }
}