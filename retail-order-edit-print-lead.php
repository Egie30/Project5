<?php
	@header("Connection: close\r\n"); 
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$OrdNbr=$_GET['ORD_NBR'];
	$BrcLst=$_GET['BRCLST'];//$_GET['BRCLST'];
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8"/>
	
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />
	
	<script>
	function getValue() {
		var ckHrg = document.getElementById("HRG").checked;
			if (ckHrg){return 'Y'; }else{	return 'N'; };
	}
	</script>
</head>
<body>

<span class='fa fa-times toolbar' style="cursor:pointer;margin-left:10px;" src="img/close.png" onclick="parent.document.getElementById('retailStockBarcodeWhite').style.display='none';parent.document.getElementById('fade').style.display='none'"></span></a>

<form enctype="multipart/form-data" action="#" method="post" style="width:265px;height:100px;" onSubmit="return checkform();">
	<div style="display:none"><input id="ORD_DET_NBR" name="ORD_DET_NBR" value="<?echo $OrdDetNbr ?>" /></div>
	<table>
		<tr>
			<td colspan='2'>
     		<input type="checkbox" id="HRG" name="HRG" value="Y" checked>&nbsp Tampilkan Harga
			<hr>
			</td>
		</tr>
		<tr>
			<td>Jumlah label kosong</td>
			<td>
				<input id="LEAD" name="LEAD" type="text" style="width:50px;" value=0 />
			</td>
		</tr>
	</table>
	<br />
	<input class="process" type="button" value="Tampilan" onclick="parent.document.getElementById('content').src=
	'retail-order-edit-print-barcode.php?ORD_NBR=<?php echo $OrdNbr; ?>&BRCLST=<?php echo $BrcLst; ?>&HRG='+getValue()+'&LEAD='+document.getElementById('LEAD').value;
	parent.document.getElementById('retailStockBarcodeWhite').style.display='none';
	parent.document.getElementById('fade').style.display='none';"/>
</body>
</html>


