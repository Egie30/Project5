<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	$Security=getSecurity($_SESSION['userID'],"DigitalPrint");
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");
	$Security2=getSecurity($_SESSION['userID'],"Finance");
	$accounting=getSecurity($_SESSION['userID'],"Accounting");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src="framework/functions/default.js"></script>

</head>

<body class="sub">
	<!--
	<div class="leftmenu" onclick="changeSiblingUrl('content','cash-register.php');selLeftMenu(this);">Cash Register</div>
	
	<div class="leftmenu" onclick="changeSiblingUrl('content','cash-flow-edit.php?RA=B&DIV=PRN');selLeftMenu(this);">Masukan Kas</div>
	<div class="leftmenu" onclick="changeSiblingUrl('content','cash-flow-edit.php?RA=E&DIV=PRN');selLeftMenu(this);">Setoran Kas</div>
	-->
	<div class="leftmenusel" onclick="changeSiblingUrl('content','cash-register-report.php');selLeftMenu(this);">Laporan Kas</div>
	<div class="leftmenu" onclick="changeSiblingUrl('content','utility.php');selLeftMenu(this);">Pengeluaran Rutin</div>
	<div class="leftmenu" onclick="changeSiblingUrl('content','expense.php');selLeftMenu(this);">Pengeluaran Kas</div>
	<div class="leftmenu" onclick="changeSiblingUrl('content','cap-voucher.php');selLeftMenu(this);">CAP Voucher</div>
	<div class="leftmenu" onclick="changeSiblingUrl('content','cash-day-report.php?TYP=CAD');selLeftMenu(this);">Kas Harian</div>
<?php if ($UpperSec<7) { ?>
	<div class="leftmenu" onclick="changeSiblingUrl('content','cash-day-report.php?TYP=CAR');selLeftMenu(this);">Laporan Kas Harian</div>
	<div class="leftmenu" onclick="changeSiblingUrl('content','dep-cash-day-report.php');selLeftMenu(this);">Buku Setoran</div>
	<div class="leftmenu" onclick="changeSiblingUrl('content','payroll-config-dte.php?TYP=FINANCE');selLeftMenu(this);">Potongan Kesalahan</div>
	<div class="leftmenu" onclick="changeSiblingUrl('content','receivables-report.php');selLeftMenu(this);">Finance Receivables</div>
	<div class="leftmenu" onclick="changeSiblingUrl('content','print-digital-status-report.php');selLeftMenu(this);">Finance Billing List</div>
<?php } ?>
	<!--
	<div class="leftmenu" onclick="changeSiblingUrl('content','cash-register-report-month.php');selLeftMenu(this);">Laporan Bulanan</div>
	-->
<?php 
if(($locked==1)||($_COOKIE["LOCK"] == "LOCK")){ $displaylock = "display:none;"; }
if($UpperSec <= 4){ ?> 
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','finance-matrix.php');selLeftMenu(this);">Finance-Matrix</div>
<?php } ?>
<?php 
if($UpperSec == 0){ ?> 
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','print-digital-receivables-reports.php');selLeftMenu(this);">Receivables</div>
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','print-digital-payable-reports.php');selLeftMenu(this);">Payable</div>
<?php } ?>
</body>
</html>