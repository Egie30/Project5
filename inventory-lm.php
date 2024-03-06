<?php
	include "framework/database/connect.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src="framework/functions/default.js"></script>

</head>

<body class="sub">
<div class="leftmenusel" onclick="changeSiblingUrl('content','inventory-list.php');selLeftMenu(this);">Daftar Stock Barang</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-activity.php');selLeftMenu(this);">Aktivitas Stock</div>
<?php
	$query="SELECT WHSE_NBR,WHSE_DESC FROM CMP.WHSE_LOC ORDER BY 2";
	$result=mysql_query($query);
	while($row=mysql_fetch_array($result))
	{
		echo "<div class='leftmenu' onclick=".chr(34)."changeSiblingUrl('content','inventory-activity.php?WHSE=".$row['WHSE_NBR']."');selLeftMenu(this);".chr(34).">Stock ".$row['WHSE_DESC']."</div>";
	}
?>
<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-report-whse.php');selLeftMenu(this);">Laporan Gudang Lengkap</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','inventory-report-defc.php');selLeftMenu(this);">Stock Tipis/Habis</div>

</body>
</html>
