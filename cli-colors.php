<?php
function strcolor($str,$fgcolor="white",$bgcolor=null)
{
 static $fgcolors = array('black' =>  '0;30',
 'dark gray' =>  '1;30',
 'blue' =>  '0;34',
 'light blue' =>  '1;34',
 'green' =>  '0;32',
 'light green' =>  '1;32',
 'cyan' =>  '0;36',
 'light cyan' =>  '1;36',
 'red' =>  '0;31',
 'light red' =>  '1;31',
 'purple' =>  '0;35',
 'light purple' =>  '1;35',
 'brown' =>  '0;33',
 'yellow' =>  '1;33',
 'light gray' =>  '0;37',
 'white' =>  '1;37');
 static $bgcolors = array( 
 'black' =>  '40',
 'red' =>  '41',
 'green' =>  '42',
 'yellow' =>  '43',
 'blue' =>  '44',
 'magenta' =>  '45',
 'cyan' =>  '46',
 'light gray' =>  '47',);

 $out="";

if (!isset($fgcolors[$fgcolor]))
	$fgcolor='white';
if (!isset($bgcolors[$bgcolor]))
	$bgcolor=null;

 
if ($fgcolor)
 $out .= "\033[{$fgcolors[$fgcolor]}m";
 if ($bgcolor) 
 $out .= "\033[{$bgcolors[$bgcolor]}m";
 
 $out .=  $str . "\033[0m";
 
 return $out;
}