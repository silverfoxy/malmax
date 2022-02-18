<?php
function fopen_mock($emul, $filename, $mode, $use_include_path = false, $context = null)
{
    if ($filename instanceof \PHPEmul\SymbolicVariable) {
        return $filename;
    }
    else {
        return fopen($filename, $mode, $use_include_path, $context);
    }
}