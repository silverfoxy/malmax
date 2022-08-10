<?php
function fopen_mock($emul, $filename, $mode, $use_include_path = false, $context = null)
{
    if ($filename instanceof \PHPEmul\SymbolicVariable) {
        return $filename;
    }
    else {
        // For /tmp/* file names, return true
        if (strpos($filename, '/tmp') !== false ) {
            return new \PHPEmul\SymbolicVariable($filename, $filename, \PhpParser\NodeAbstract::class, true);
        }
        else {
            return fopen($filename, $mode, $use_include_path, $context);
        }
    }
}