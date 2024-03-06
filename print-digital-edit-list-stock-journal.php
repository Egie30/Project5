<?php
	@header("Connection: close\r\n"); 
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$OrdDetNbr=$_GET['ORD_DET_NBR'];
	$changed=false;
	//Process journal entry here
	if($_POST['ORD_DET_NBR']!="")
	{
		$query="INSERT INTO CMP.JRN_PRN_DIG_INV
		        (ORD_DET_NBR,INV_SKU,INV_Q,CRT_TS,CRT_NBR) VALUE
	   			(".$_POST['ORD_DET_NBR'].",
	   			".$_POST['INV_SKU'].",
	   			".$_POST['INV_Q'].",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
		//echo $query;
	   	$result=mysql_query($query);
	   	$changed=true;
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">


<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />

</head>

<body>

<img class="toolbar-left" style="cursor:pointer" src="img/close.png" onclick="parent.document.getElementById('printDigitalPopupJournal').style.display='none';parent.document.getElementById('fade').style.display='none'"></a></p>

<?php
	if($changed){
		echo "<script>parent.document.getElementById('printDigitalPopupJournal').style.display='none';parent.document.getElementById('fade').style.display='none'</script>";
	}
?>

<script>
	parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();
</script>

<form enctype="multipart/form-data" action="#" method="post" style="width:357px;height:135px;" onSubmit="return checkform();">
	<!-- Keeping the order detail number -->
	<div style="display:none"><input id="ORD_DET_NBR" name="ORD_DET_NBR" value="<?echo $OrdDetNbr ?>" /></div>
	<table>
		<tr>
			<td>SKU</td>
			<td>
				<input id="INV_SKU" name="INV_SKU" type="text" style="width:100px;" />
			</td>
		</tr>
		<tr>
			<td>Panjang (meter)</td>
			<td>
				<input id="INV_Q" name="INV_Q" type="text" style="width:100px;" />
			</td>
		</tr>
	</table>
	<br />
	<input class="process" type="submit" value="Potong"/>
</body>
</html>


