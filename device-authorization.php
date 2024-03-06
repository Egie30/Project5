<?php
require_once "framework/database/connect.php";
require_once "framework/security/default.php";

$authorize = (bool) (isset($_COOKIE['DeviceAuth']) && $_COOKIE['DeviceAuth'] == 1);

if (isset($_GET['AUTH']) && $_GET['AUTH'] == 1) {
	setCookie("DeviceAuth",1,time()+7*24*3600);
	$authorize = true;
		
} elseif (isset($_GET['DE_AUTH']) && $_GET['DE_AUTH'] == 1) {
	setCookie("DeviceAuth","",time()-3600);
	$authorize = false;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

	
</head>
<body>
<h2>Device Authorization</h2>
<table>
	<tr>
		<td>
			<div>
				<?php if ($authorize) {?>
				Klik Deauthorize untuk membatalkan otorisasi perangkat<br><br>
				<a style="border:1px #cccccc solid;padding:5px;width:100px;margin-top:5px;margin-bottom:5px;cursor:pointer;border-radius:3px;text-decoration:none;color:black;outline:none;" href="?DE_AUTH=1">Deauthorize</a><br />
				<?php } else { ?>
				Klik Authorize untuk mengotorisasi perangkat<br><br>
				<a style="border:1px #cccccc solid;padding:5px;width:100px;margin-top:5px;margin-bottom:5px;cursor:pointer;border-radius:3px;text-decoration:none;color:black;outline:none;" href="?AUTH=1">Authorize</a><br />
				<?php }?>
				</a>
			</div>
		</td>
	</tr>
</table>
</body>
</html>


