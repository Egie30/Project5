<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";

	$Security=getSecurity($_SESSION['userID'],"Report");	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src="framework/functions/default.js"></script>

</head>

<body class="sub">
<div class="leftmenusel" onclick="changeSiblingUrl('content','forms-sale-day.php?DAYS=0');selLeftMenu(this);">Penjualan Hari ini</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','forms-sale-day.php?DAYS=1');selLeftMenu(this);">Penjualan Kemarin</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','forms-sale-month.php?MONTHS=0');selLeftMenu(this);">Penjualan Bulan ini</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','forms-sale-month.php?MONTHS=1');selLeftMenu(this);">Penjualan Bulan lalu</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','forms-sale-all.php');selLeftMenu(this);">Arsip Penjualan</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-report.php?CO_NBR=<?php echo $CoNbrDef?>');selLeftMenu(this);">Laporan Penjualan</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-report.php?CO_NBR=<?php echo $CoNbrDef?>&RPT_TP=SPL');selLeftMenu(this);">Laporan Penjualan/Suplier</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-report.php?CO_NBR=<?php echo $CoNbrDef?>&RPT_TP=SCT');selLeftMenu(this);">Laporan Penjualan/Sub Category</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-position-tes2.php?DEF_CO=<?php echo $CoNbrDef?>&CO_NBR=<?php echo $CoNbrDef?>');selLeftMenu(this);">Inventaris Toko</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-position.php?CO_NBR=<?php echo $CoNbrDef?>&RPT_TP=SPL');selLeftMenu(this);">Inventaris Toko/Suplier</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-position.php?CO_NBR=<?php echo $CoNbrDef?>&RPT_TP=SCT');selLeftMenu(this);">Inventaris Toko/Sub Category</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-position-tes2.php?CO_NBR=1021');selLeftMenu(this);">Inventaris Gudang</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','retail-stock-report.php?IVC_TYP=RC');selLeftMenu(this);">Laporan Pembelian</div>


<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-card.php?DEF_CO=<?php echo $CoNbrDef?>&CO_NBR=<?php echo $CoNbrDef?>');selLeftMenu(this);">Kartu Stock</div>
<!--<div class="leftmenu" onclick="changeSiblingUrl('content','retail-stock-spl.php?IVC_TYP=RC');selLeftMenu(this);">Laporan Pembelian/Suplier</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','retail-stock-scat.php?IVC_TYP=RC');selLeftMenu(this);">Laporan Pembelian/Sub Category</div>
-->

<div class="leftmenu" onclick="changeSiblingUrl('content','retail-stock-report.php?IVC_TYP=XF');selLeftMenu(this);">Laporan Mutasi</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','retail-stock-report.php?IVC_TYP=RT');selLeftMenu(this);">Laporan Retur</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','retail-stock-report.php?IVC_TYP=CR');selLeftMenu(this);">Laporan Koreksi</div>

<!--TODO: Security filter-->
<div class="leftmenu" onclick="changeSiblingUrl('content', 'commission-broker-report.php');selLeftMenu(this);">Laporan Komisi Meter</div>
<div class="leftmenu" onclick="changeSiblingUrl('content', 'commission-canvaser-report.php');selLeftMenu(this);">Laporan Komisi Revenue</div>

</body>
</html>
