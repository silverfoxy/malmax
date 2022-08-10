<?php
function fread_mock($emul, $stream, $length)
{
    if ($stream instanceof \PHPEmul\SymbolicVariable) {
        $stream->type = \PhpParser\Node\Scalar\String_::class;
        $stream->isset = true;
        return $stream;
    }
    else {
        // For /tmp/* file names, return true
        if (is_string($stream) && strpos($stream, '/tmp') !== false ) {
            return new \PHPEmul\SymbolicVariable($stream, $stream, \PhpParser\Node\Scalar\String_::class, true);
        }
        else {
            return fread($stream, $length);
        }
    }
}