<?php

// var_dump(base64_encode(gzcompress('$s=0; for ($i=0;$i<100;++$i)  $s+=$i;return $s;',9)));
$x=eval(gzuncompress(base64_decode("eNpTKbY1sFZIyy9S0FDJBDJVMm0MDQystbVVMjUVFFSKtW1VMq2LUktKi/KAPGsAGq0MXg==")));
print_r($x);
