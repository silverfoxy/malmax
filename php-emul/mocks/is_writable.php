<?php
function is_writable_mock($emul, $filename)
{
    if ($filename instanceof \PHPEmul\SymbolicVariable) {
        return $filename->isset;
    }
    else {
        return is_writable($filename);
    }
}