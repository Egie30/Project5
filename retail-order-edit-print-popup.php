<?php
	@header("Connection: close\r\n"); 
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$OrdNbr		= $_GET['ORD_NBR'];
	$OrdDetNbr	= $_GET['ORD_DET_NBR'];
	$changed	= false;
	$addNew		= false;
	
	//Get order head information
	$query="SELECT RCV_CO_NBR, ORD_TTL FROM RTL.RTL_ORD_HEAD WHERE ORD_NBR=".$OrdNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$orderTitel	= $row['ORD_TTL'];
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />
	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
</head>
<body>
<script>
	//parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();
</script>
<span class='fa fa-times toolbar' style='margin-left:10px' onclick="slideFormOut();"></span></a>
<form enctype="multipart/form-data" action="#" method="post" style="width:450px;" onSubmit="return checkform();">
	<table>
		<tr>
			<td>Keterangan</td>
			<td><input id="ORD_TTL" name="ORD_TTL" value="<?php echo $orderTitel; ?>" type="text" style="width:300px;" /></td>
		</tr>
	</table>
	<br />
	<input class="process" id="process" type="submit" value="Simpan" onclick="
		parent.document.getElementById('content').contentDocument.getElementById('rightpane').src='retail-order-edit-pdf.new.php?ORD_NBR=<?php echo $OrdNbr; ?>&IVC_TYP=<?php echo $IvcTyp; ?>&TYPE=PDF';
		slideFormOut();"/>
</form>
</body>
</html>