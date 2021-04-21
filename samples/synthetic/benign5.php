<?php
$email=$_POST['email'];
mail($email, "Verification", "Please reply with YES to start receiving messages");