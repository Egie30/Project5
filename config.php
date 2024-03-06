<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	$Security=getSecurity($_SESSION['userID'],"Executive");
	if(($Security==0)){
		?> <script>location='/phpmyadmin/';</script><?php
		exit;
	}else{
		?> <script>parent.location='login.php';</script><?php
		exit;
	}
?>
