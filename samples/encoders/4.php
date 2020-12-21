<?php
# include used with pack/unpack encode decode (not base64)
if (@$argc!=2) exit(1);

$content=file_get_contents($argv[1]);

$code="<"."?php\n";
$code.="\$tmp=tempnam('/tmp','FOO');\n";
$code.="\$c=pack('H*','";

$encoded=unpack("H*","?>".$content)[1];
$code.=substr($encoded,0,1)."'.'".substr($encoded,1);

$code.="');\n";

$code.="file_put_contents(\$tmp, \$c);\n";
$code.="include \$tmp;\n";
echo $code;