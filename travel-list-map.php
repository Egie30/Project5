<?php
include "framework/database/connect.php";
include "framework/functions/default.php";
$travelNumber		= $_GET['AUTH_TRVL_NBR'];
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

<div style="height:100%;width:100%;overflow:auto">
<span class='fa fa-times toolbar' style="cursor:pointer;" src="img/close.png" onclick="parent.document.getElementById('printDigitalPopupEdit').style.display='none';parent.document.getElementById('fade').style.display='none'"></span></a>

<?php
	$query="SELECT 
		ORIG_LAT,
		ORIG_LNG,
		DEST_LAT,
		DEST_LNG,
		ORIG_TS,
		DEST_TS
	FROM CMP.AUTH_TRVL
	WHERE AUTH_TRVL_NBR=".$travelNumber;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<body>

<table>
	<tr>
		<td><b>Origin :<?php echo $row['ORIG_LAT'];?> , <?php echo $row['ORIG_LNG'];?></b></td>
		<td align="right"><b><?php echo $row['ORIG_TS'];?></b></td>
	</tr>
	<tr>
		<td colspan="2">
		<img id="map" src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $row['ORIG_LAT'];?>,<?php echo $row['ORIG_LNG'];?>&zoom=17&size=400x200&scale=2&markers=size:small%7Ccolor:red%7C<?php echo $row['ORIG_LAT'];?>,<?php echo $row['ORIG_LNG'];?>&key=AIzaSyArBlVIq10YHbnJKHU0b7tCgU-oom9DDq8" width="100%" frameborder="0" style="border:0">
		</td>
	</tr>
	<tr>
		<td><b>Destination :<?php echo $row['DEST_LAT'];?> , <?php echo $row['DEST_LNG'];?></b></td>
		<td align="right"><b><?php echo $row['DEST_TS'];?></b></td>
	</tr>
	<tr>
		<td colspan="2">
		<img id="map" src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $row['DEST_LAT'];?>,<?php echo $row['DEST_LNG'];?>&zoom=17&size=400x200&scale=2&markers=size:small%7Ccolor:red%7C<?php echo $row['DEST_LAT'];?>,<?php echo $row['DEST_LNG'];?>&key=AIzaSyArBlVIq10YHbnJKHU0b7tCgU-oom9DDq8" width="100%" frameborder="0" style="border:0">
		</td>
	</tr>
</table>
</div>
</body>
</html>


