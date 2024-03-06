<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	
	$SecurityAct = getSecurity($_SESSION['userID'],"Accounting");

	if(($locked==1)||($_COOKIE["LOCK"] == "LOCK")){ 
		$displaylock = "display:none;"; 
		$displayclass= "leftmenusel"; 
		$note 		 = "";
	} else { 
		$displayclass= "leftmenu"; 
		$note 		 = "(Terlapor)";
	}	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src="framework/functions/default.js"></script>

</head>

<body class="sub">

<?php 
	if ($SecurityAct == 2) {
	?>
	<div style="<?php echo $displaylock; ?>" class="leftmenusel" onclick="changeSiblingUrl('content','store-inventory-matter.php?ACTG=0&GROUP=PRN_DIG_TYP');selLeftMenu(this);">Inventaris Toko Bahan/Tipe</div>
	<?php 
	}
	
	if (($SecurityAct == 3) || ($SecurityAct == 7)) {
	?>
	<div style="<?php echo $displaylock; ?>" class="leftmenusel" onclick="changeSiblingUrl('content','bank-statement.php?ACTG=0');selLeftMenu(this);">Rekening Koran</div>
	<?php
	}
	
	if ($SecurityAct <= 1) {
	?>
	<div style="<?php echo $displaylock; ?>" class="leftmenusel" onclick="changeSiblingUrl('content','bank-statement.php?ACTG=0');selLeftMenu(this);">Rekening Koran</div>
	<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','store-inventory-matter.php?ACTG=0&GROUP=PRN_DIG_TYP');selLeftMenu(this);">Inventaris Toko Bahan/Tipe</div>	
<?php } ?>

<?php 
//if ($locked==0) {

if (($SecurityAct == 0) || ($SecurityAct == 2)){ ?>

<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','checkout-report.php?ACTG=0');selLeftMenu(this);">Check Out</div>

<div class="<?php echo $displayclass; ?>" onclick="changeSiblingUrl('content','procurement-report.php?ACTG=0&GROUP=ORD_NBR&TYP=FULL&IVC_TYP=RC');selLeftMenu(this);">Laporan Pembelian</div>
<?php 
}
//} ?>

<!--
<div class="leftmenu" onclick="changeSiblingUrl('content','procurement-report-item.php?ACTG=0&GROUP=ORD_NBR&TYP=FULL&IVC_TYP=RC');selLeftMenu(this);">Laporan Pembelian / Item</div>
-->

<?php 
if (($SecurityAct == 0)){
?>

<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','store-inventory-accounting.php?ACTG=0&TYP=ACTG');selLeftMenu(this);">Inventaris Toko Accounting</div>

<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','checkout-report.php?ACTG=0&TYP=ACTG');selLeftMenu(this);">Check Out Accounting</div>


<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','procurement-report.php?ACTG=0&GROUP=ORD_NBR&TYP=ACTG&IVC_TYP=RC');selLeftMenu(this);">Laporan Pembayaran Pembelian</div>

<div class="<?php echo $displayclass; ?>" onclick="changeSiblingUrl('content','procurement-report.php?ACTG=0&GROUP=ORD_NBR&TYP=ACTG_TAX&IVC_TYP=RC');selLeftMenu(this);">Laporan Pembelian (Faktur Pajak)</div>
<!-- <div class="leftmenu" onclick="changeSiblingUrl('content','omzet-report.php?ACTG=0&GROUP=MONTH');selLeftMenu(this);">Laporan Omset</div> -->
<div class="leftmenu" onclick="changeSiblingUrl('content','retail-order-report-archive.php?ACTG=0&GROUP=ORD_NBR&TYP=ORD');selLeftMenu(this);">Omset Retail</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','calendar-order-report-archive.php?ACTG=0&GROUP=ORD_NBR&TYP=ORD');selLeftMenu(this);">Omset Kalender</div>
<!--
<div class="leftmenu" onclick="changeSiblingUrl('content','prn-dig-report-archive.php?ACTG=0&GROUP=ORD_NBR&TYP=TAX');selLeftMenu(this);">Omset Digital Printing <?php echo $note; ?></div>

<div class="leftmenu" onclick="changeSiblingUrl('content','prn-dig-report-archive.php?ACTG=0&GROUP=MONTH&TYP=ACTG');selLeftMenu(this);">Omset Digital Printing (Accounting)</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','prn-dig-report-archive.php?ACTG=0&GROUP=MONTH&TYP=FULL');selLeftMenu(this);">Omset Digital Printing (Lunas)</div>


<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','prn-dig-report-archive.php?ACTG=0&GROUP=PYMT_NBR&TYP=PAY');selLeftMenu(this);">Omset Digital Printing (Uang Masuk)</div>

<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','prn-dig-report-archive.php?ACTG=0&GROUP=ORD_NBR&TYP=ORD');selLeftMenu(this);">Omset Digital Printing (Order)</div>

<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','prn-dig-report-archive.php?ACTG=0&GROUP=ORD_NBR&TYP=TAX_IVC');selLeftMenu(this);">Omset Digital Printing (Faktur Pajak)</div>
-->
<!--
<div class="leftmenu" onclick="changeSiblingUrl('content','store-inventory-monthly.php?ACTG=0&RL_TYP=RL_YEAR');selLeftMenu(this);">Arsip Pembelian dan Checkout</div>


<div class="leftmenu" onclick="changeSiblingUrl('content','purchase-report-archive.php?ACTG=0&GROUP=MONTH&IVC_TYP=RC&TYP=PRNDIG');selLeftMenu(this);">Pembelian Digital Printing</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','purchase-report-pay-archive.php?ACTG=0&GROUP=MONTH&IVC_TYP=RC&TYP=PRNDIG');selLeftMenu(this);">Pembelian Digital Printing (Pembayaran)</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','purchase-report-full-archive.php?ACTG=0&GROUP=MONTH&IVC_TYP=RC&TYP=PRNDIG&PAY_TYP=FULL');selLeftMenu(this);">Pembelian Digital Printing (Lunas)</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','purchase-report-archive.php?ACTG=0&GROUP=MONTH&IVC_TYP=RC&CAT_SUB_TYP=COST');selLeftMenu(this);">Pembelian Jasa</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','purchase-report-pay-archive.php?ACTG=0&GROUP=MONTH&IVC_TYP=RC&CAT_SUB_TYP=COST&RC_TYP=PAY');selLeftMenu(this);">Pembelian Jasa (Pembayaran)</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','purchase-report-full-archive.php?ACTG=0&GROUP=MONTH&IVC_TYP=RC&CAT_SUB_TYP=COST&RC_TYP=FULL');selLeftMenu(this);">Pembelian Jasa (Lunas)</div>
-->

<div class="leftmenu" onclick="changeSiblingUrl('content','procurement-report.php?ACTG=0&GROUP=CAT_SUB_NBR&IVC_TYP=RT');selLeftMenu(this);">Laporan Retur Pembelian</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','cost-routine-archive.php?ACTG=0&GROUP=MONTH');selLeftMenu(this);">Pengeluaran Rutin</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','cost-cash-archive.php?ACTG=0&GROUP=MONTH');selLeftMenu(this);">Pengeluaran Kas</div>

<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','payroll-archive.php?ACTG=0&GROUP=MONTH');selLeftMenu(this);">Payroll</div>

<!--
<div class="leftmenu" onclick="changeSiblingUrl('content','purchase-report-archive.php?ACTG=0&GROUP=MONTH&IVC_TYP=RC&TYP=RTL');selLeftMenu(this);">Pembelian (Retail)</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','purchase-report-archive.php?ACTG=0&GROUP=MONTH&IVC_TYP=RT&TYP=RTL');selLeftMenu(this);">Retur Pembelian (Retail)</div>
-->

<div class="leftmenu" onclick="changeSiblingUrl('content','retail-report-archive.php?ACTG=0&GROUP=MONTH');selLeftMenu(this);">Omset Retail</div>

<?php } 


if (($SecurityAct <= 0)){ ?>
<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','bank-statement-report.php?ACTG=0');selLeftMenu(this);">Laporan Rekening Koran</div>
<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','accounts-payable-report.php?ACTG=0&GROUP=MONTH');selLeftMenu(this);">Laporan Hutang Dagang</div>

<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','accounts-receivable-report.php?ACTG=0&GROUP=MONTH');selLeftMenu(this);">Laporan Piutang Dagang</div>
<?php } ?>

<?php if ($SecurityAct <= 0) { ?>
<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','omset-freelance-report.php?ACTG=0&GROUP=MONTH');selLeftMenu(this);">Laporan Omset Freelance</div>

<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','procurement-sync-report.php?ACTG=0&GROUP=ORD_NBR&TYP=FULL&IVC_TYP=RC');selLeftMenu(this);">Sync Pembelian</div>

<div style="<?php echo $displaylock; ?>" class="leftmenu" onclick="changeSiblingUrl('content','prn-dig-report-sync-archive.php?ACTG=0&GROUP=ORD_NBR&TYP=ORD');selLeftMenu(this);">Sync Digital Printing</div>
<?php } ?>

</body>
</html>