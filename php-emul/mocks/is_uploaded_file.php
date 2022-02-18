<?php
function is_uploaded_file_mock($emul, $filename)
{
    if ($filename instanceof \PHPEmul\SymbolicVariable) {
        return $filename->isset;
    }
    else {
        // For /tmp/* file names, return true
        if (strpos($filename, '/tmp') !== false ) {
            return true;
        }
        else {
            return is_uploaded_file($filename);
        }
    }
}