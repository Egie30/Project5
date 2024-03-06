<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	$SecurityFin 	= getSecurity($_SESSION['userID'],"Finance");
	$Security 	= getSecurity($_SESSION['userID'],"Executive");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src="framework/functions/default.js"></script>

</head>

<body class="sub">



<div class="leftmenusel" onclick="changeSiblingUrl('content','category.php');selLeftMenu(this);">Daftar Kategori</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','category-sub.php');selLeftMenu(this);">Daftar Sub Kategori</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','category-shelf.php');selLeftMenu(this);">Daftar Rak</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','category-discount.php');selLeftMenu(this);">Daftar Kelompok Diskon</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','category-price.php');selLeftMenu(this);">Daftar Golongan Harga</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-retail-list.php');selLeftMenu(this);">Daftar Stock Barang</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','product.php');selLeftMenu(this);">Daftar Product</div>

<?php if ($SecurityFin<2) { ?>
<div class="leftmenu" id="retail-po" onclick="changeSiblingUrl('content','retail-stock.php?IVC_TYP=PO');selLeftMenu(this);">Order</div>
<div class="leftmenu" id="retail-rc" onclick="changeSiblingUrl('content','retail-stock.php?IVC_TYP=RC');selLeftMenu(this);">Pembelian</div>
<div class="leftmenu" id="retail-xf" onclick="changeSiblingUrl('content','retail-stock.php?IVC_TYP=XF');selLeftMenu(this);">Mutasi</div>
<?php } ?>
<?php if ($SecurityFin < 2 || $Security == 7) { ?>
<div style="display:none;" class="leftmenu" id="retail-sl" onclick="changeSiblingUrl('content','retail-stock.php?IVC_TYP=SL');selLeftMenu(this);">Sales</div>
<?php } ?>
<?php if ($SecurityFin<2) { ?>
<div class="leftmenu" id="retail-cr" onclick="changeSiblingUrl('content','retail-stock.php?IVC_TYP=CR');selLeftMenu(this);">Koreksi</div>
<div class="leftmenu" id="retail-rt" onclick="changeSiblingUrl('content','retail-stock.php?IVC_TYP=RT&CO_NBR=<?php echo $CoNbrDef;?>');selLeftMenu(this);">Retur</div>
<div class="leftmenu" id="retail-pg" onclick="changeSiblingUrl('content','retail-stock.php?IVC_TYP=PG');selLeftMenu(this);">Rusak</div>
<div class="leftmenu" id="retail-pg" onclick="changeSiblingUrl('content','retail-stock.php?IVC_TYP=TS');selLeftMenu(this);">Transit</div>
<?php } ?>

<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-audit.php');selLeftMenu(this);">Stock Opname</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-move.php');selLeftMenu(this);">Check Out</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','retail-stock-barcode-custom-print.php');selLeftMenu(this);">Barcode</div>

</body>
</html>