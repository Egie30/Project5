<?php
	include "../framework/database/connect.php";
	include "../framework/security/default.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<title>Nestor Mobile</title>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link type="text/css" rel="apple-touch-icon-precomposed" href="img/icon.png?v=1" />
<link rel="apple-touch-startup-image" href="startup-portrait.png" />
<link rel="apple-touch-startup-image" sizes="768x1004" href="startup-portrait.png" />


<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

<script type="text/javascript" src="../framework/functions/default.js"></script>

</head>

<body>

<table class="main" style='height:100%;'>
	<tr style='height:100%;'>
		<td class='leftmenu' style='width:54px'>
			<div>
				<img src="img/home.png" class='leftmenusel' onclick="changeSiblingUrl('content','summary.php');selLeftMenuMobile(this);"><br/><br/><br/>

				<img src="img/barcode.png" class='leftmenu' onclick="document.getElementById('content').focus();document.getElementById('content').contentDocument.getElementById('listener').focus();selLeftMenuMobileStick(this);"><br/><br/><br/>

				<img src="img/clock.png" class='leftmenu'   onclick="changeSiblingUrl('content','clock-machine.php');selLeftMenuMobile(this);"><br/>
			
				<img src="img/search.png" class='leftmenu'  onclick="changeSiblingUrl('content','lookup.php');selLeftMenuMobile(this);"><br/>
				
				<img src="img/handle.png" class='leftmenu'  onclick="changeSiblingUrl('content','stage.php');selLeftMenuMobile(this);"><br/>

				<img src="img/tag.png" class='leftmenu'     onclick="changeSiblingUrl('content','audit.php');selLeftMenuMobile(this);"><br/>

				<img src="img/roll.png" class='leftmenu'    onclick="changeSiblingUrl('content','inventory.php');selLeftMenuMobile(this);"><br/>
			</div>
		</td>
		<td class='content'>
			<iframe id="content" borderframe=0 src="summary.php"></iframe>
		</td>
	</tr>
	<tr class="footer">
		<td class="leftmenu">
		</td>
		<td class="footer">
			<p class="bottom-right">Nestor Mobile version 2.0.0 Copyright &copy; 2012-2013 proreliance.com&nbsp;&nbsp;</p>
		</td>
	</tr>
</table>

</body>