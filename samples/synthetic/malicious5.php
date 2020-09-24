<?php
$email=$_POST['email'];
for ($i=0;$i<1000;++$i)
	mail($email, "Verification", "Please reply with YES or click <a href='virus.com/virus'>Here</a> 
		to start receiving messages.");