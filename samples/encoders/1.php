<?php
#simple base64 wrapper
if (@$argc!=2) exit(1);

$content=file_get_contents($argv[1]);
$code="<"."?php ";
$code.="eval(base64_decode('";


$code.=base64_encode("?>".$content);

$code.="'));\n";

echo $code;