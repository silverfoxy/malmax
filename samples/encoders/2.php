<?php
# simple base64 wrapper, but uses concat for the base64 encoded string
# to prevent simple linear decoders.
if (@$argc!=2) exit(1);

$content=file_get_contents($argv[1]);
$code="<"."?php ";
$code.="eval(base64_decode('";

$encoded=base64_encode("?>".$content);
$code.=substr($encoded,0,1)."'.'".substr($encoded,1);

$code.="'));\n";

echo $code;