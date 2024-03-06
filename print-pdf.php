<?php
include "framework/security/default.php";

$UpperSec		= getSecurity($_SESSION['userID'],"Accounting");
$salestype 		= $_GET['TYP'];
$formattedOrderNumber = leadZero($orderNumber, 7);
$paper = array(0, 0, 21 * (72/2.54), 29.7 * (72/2.54));

if($salestype == "EST"){
	$headtable 	= "CMP.PRN_DIG_ORD_HEAD_EST";
	$detailtable= "CMP.PRN_DIG_ORD_DET_EST";
}else{
	$headtable 	= "CMP.PRN_DIG_ORD_HEAD";
	$detailtable= "CMP.PRN_DIG_ORD_DET";
}

$query="SELECT NAME FROM PEOPLE PPL INNER JOIN POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='".$_SESSION['userID']."'";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$name=$row['NAME'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>Print Digital Invoice- <?php echo $orderNumber; ?></title>
<link rel="stylesheet" href="http://necolas.github.io/normalize.css/latest/normalize.css">
<style type="text/css">
@page {
	margin-top: 0px;
}
body {
	font-family: Calibri, sans-serif;
	margin: 175px .0cm;
	text-align: left;
	font-size:12px;
	line-height: 18px;
	margin-bottom: 80px;
	padding-top: 250px;
	font-weight: normal;
}

table {
	border-collapse: collapse;
	border: none;
}

#header {
	font-family: arial, sans-serif;
	padding-bottom: 10px;
}

#header h1 {
	margin: 0;
	font-size:28px;
}

#body table{
	margin-bottom: 15px;
}

#body table:last-child{
	margin-bottom: 0;
}

#header, #footer {
	position: fixed;
	left: 0;
	right: 0;
}

#header {
	top: 0;
}

#footer {
	font-size:10px;
	bottom: 70px; /*bottom: 20px;*/
}

.border-gold {
	border-color: rgb(92,51,23);
}

.border-black {
	border-color: #000000;
}

.text-center {
	text-align:center;
}

.text-left {
	text-align:left;
}

.text-right {
	text-align:right;
}
</style>
</head>
<body>
<header id="header">
	<table width="100%">
		<tr>
			<td width="32%"><img src="<?php echo $img; ?>" height="410px" style="padding-top:-3px;"/></td>
			<td width="2%">&nbsp;</td>
			<td width="65%" style="padding-top:205px;">
				<div style="font-size:33px;"><?php echo $Title; ?></div>
				<div style="font-size:10px;"><?php echo $Tanggal; ?></div>
				<div style="font-size:10px;"><?php echo $co_nama; ?></div>
				<div style="font-size:10px;"><?php echo $name_cetak; ?></div>
				<div style="font-size:10px;"><?php echo $tanggal_cetak; ?></div>
			</td>
		</tr>
	</table>
</header>

<section id="body">
	<table width="100%" >
		<thead>
			<tr>
				<th class="text-center" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Seller.</th>
				<th class="text-center" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Category</th>
				<th class="text-right"  style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">QTY</th>
				<th class="text-right"  style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Purchase Price</th>
				<th class="text-right"  style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Sales</th>
				<th class="text-right"  style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Profit</th>
				<th class="text-right"  style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Percentage</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</section>
</body>
</html>