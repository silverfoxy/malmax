#!/usr/bin/env php
<?php
$disable=true;
if ($argc<2)
	die("Usage: {$argv[0]} extension_name\n");
$phpini=`php -i | grep php.ini`;
$phpini=trim(substr($phpini,strpos($phpini,"File => ")+8));
// var_dump($phpini);
copy($phpini, $phpini.".bak");
$content=file_get_contents($phpini);
$extension=$argv[1];
// var_dump(preg_match("/^extension=(.*?){$extension}.so/mi",  $content,$matches));
// var_dump($matches);
if ($disable)
	$content=preg_replace("/^extension=(.*?){$extension}.so/mi", ";extension=$1{$extension}.so", $content);
else
	$content=preg_replace("/^;extension=(.*?){$extension}.so/mi", "extension=$1{$extension}.so", $content);
// var_dump($content);
file_put_contents($phpini, $content);
