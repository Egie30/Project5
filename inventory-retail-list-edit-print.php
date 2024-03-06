<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	include "framework/functions/dotmatrix.php";
	
	$InvNbr=$_GET['INV_NBR'];
	$query="SELECT NAME,INV_BCD,PRC FROM RTL.INVENTORY WHERE INV_NBR=".$InvNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<style type="text/css">
	@page {margin:0cm;margin-bottom:0cm;margin-top:0cm;}
</style>

</head>

<body style='margin:0;padding:0;width:100%;font-family:arial'>
	<div style='text-align:center;width:100%;padding-top:0px'>
	<table style='width:7.5cm;background-color:#dddddd;margin-left:auto;margin-right:auto;'>
		<tr style='height:3cm'>
			<td style='width=1.5cm'>
				<div style='margin-left:-0.75cm;width:1.5cm'>
				<img src='framework/barcode/retail-barcode.php?STRING=<?php echo $row['INV_BCD'];?>' style='-webkit-transform:rotate(90deg);-moz-transform:rotate(90deg);width:3cm;height:1.5cm;'><br />
				</div>
			</td>
			<td style='width=6cm;align:center;'>
				<div style='margin-left:.7cm;font-size:9pt;-webkit-transform:rotate(180deg);-moz-transform:rotate(180deg);'><?php echo $row['INV_BCD']; ?></div>
				<div style='margin-left:.7cm;-webkit-transform:rotate(180deg);-moz-transform:rotate(180deg);vertical-align:top'>Rp. <span style='font-size:30pt;'><b><?php echo number_format($row['PRC'],0,",","."); ?></b></span></div>
				<div style='margin-left:.7cm;font-size:9pt;-webkit-transform:rotate(180deg);-moz-transform:rotate(180deg);'><?php echo $row['NAME']; ?></div>
			</td>
		</tr>
	</table>
	</div>
	<script>window.print()</script>
</body>
