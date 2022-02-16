<?php
function is_readable_mock($emul, $filename)
{
    if ($filename instanceof \PHPEmul\SymbolicVariable) {
        return $filename->isset;
    }
    else {
        return is_readable($filename);
    }
}