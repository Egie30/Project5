<?php
require_once "framework/database/connect-cashier.php";
require_once "framework/security/default.php";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript">parent.Pace.restart();</script>
</head>

<body>

<div style="height:480px; overflow:auto">
		<span class='fa fa-times' style="cursor:pointer" onclick="document.getElementById('popupLogin').style.display = 'none';
			document.getElementById('fadeCashier').style.display = 'none';"/></span>

<form enctype="multipart/form-data" action="cashier-listing.php?POS_ID=<?php echo $POSID ?>&TRSC_NBR=<?php echo $_GET['TRSC_NBR'] ?>&BCD_NBR=<?php echo $_GET['BCD_NBR'] ?>&ACTION=<?php echo $_GET['ACTION']; ?>" method="GET" style="width: 100%; box-sizing: border-box;"  autofocus>
<table>
	<input type="hidden" name="POS_ID" value="<?php echo $POSID; ?>"/>
	<input type="hidden" name="TRSC_NBR" value="<?php echo $_GET['TRSC_NBR']; ?>"/>
	<input type="hidden" name="BCD_NBR" value="<?php echo $_GET['BCD_NBR']; ?>"/>
	<input type="hidden" name="ACTION" value="<?php echo $_GET['ACTION']; ?>"/>
	<tr>
		<td colspan="2"><b>Penghapusan Transaksi Hanya Bisa Dilakukan Atas Seijin Finance/Kepala Kasir<b/></td>
	</tr>
	<tr>
		<td colspan="2">
			<span>Username</span><br/>
			<input id="SPV_ID" name="SPV_ID" type="text" style="width:300px;" />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<span>Password</span><br/>
			<input id="SPV_PWD" name="SPV_PWD" type="password" style="width:300px;" />
		</td>
	</tr>
</table>
<br />
	 <input class="process" style="cursor:pointer;" type="submit" value="Masuk" />
	 <input type="submit" class="process" name="Batal" value="Batal" onclick="cashier-listing.php?POS_ID=<?php echo $POSID; ?>&TRSC_NBR=<?php echo $_GET['TRSC_NBR']; ?>" />
</body>
</html>