<?php
$con=mysqli_connect("localhost","root","123456aB");
$g=$_GET;
$zoo=$g[1];
mysqli_query($con,$zoo);
unset($_SERVER);
$_SERVER=[];

$x=$_GET[1];

$x="hello".$x;
$x=str_replace("hello","",$x);
taint_stat();
$z=base64_encode($x);
taint_stat();
$y=base64_decode($z);
taint_stat();
if (strlen($y)<50)
{
	
	$r=mysqli_query($con,$y);
	var_dump($r);
}