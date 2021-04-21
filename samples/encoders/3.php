<?php
# base64 wrapper, but uses concat for the base64 encoded string
# to prevent simple linear decoders.
# Also uses include and tempfile to run, instead of eval
if (@$argc!=2) exit(1);

$content=file_get_contents($argv[1]);
$code="<"."?php\n";
$code.="\$tmp=tempnam('/tmp','FOO');\n";
$code.="\$c=base64_decode('";

$encoded=base64_encode("?>".$content);
$code.=substr($encoded,0,1)."'.'".substr($encoded,1);

$code.="');\n";

$code.="file_put_contents(\$tmp, \$c);\n";
$code.="include \$tmp;\n";
echo $code;