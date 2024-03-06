<?php
	if($_GET['DEL_L']!=""){
		include "framework/database/connect-cloud.php";
	}else{
		include "framework/database/connect.php";
	}
	include "framework/security/default.php";
	
	$Security		= getSecurity($_SESSION['userID'],"Finance");
	
	if($cloud!=false){
		//Process location
		$whse=$_GET['WHSE'];
		if($whse!=""){$whse=" WHERE LOG.WHSE_NBR=".$whse;}

		//Process delete entry
		if($_GET['DEL_L']!="")
		{
			$query="UPDATE $RTL.INVENTORY SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP WHERE INV_NBR=".$_GET['DEL_L'];
			$result=mysql_query($query,$cloud);
			$query=str_replace($RTL,"RTL",$query);
			$result=mysql_query($query,$local);
		}
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<script src="framework/database/jquery.min.js"></script>

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>

</head>

<body>

<div class="toolbar">
	<p class="toolbar-left"><a href="inventory-retail-list-edit.php?INV_NBR=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a></p>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">

	<table id="mainTable" class="tablesorter searchTable" style="width: 1295px;">
		<thead>
			<tr>
				<th nowrap  style="text-align:right;vertical-align:middle;width:20%;">No</th>
				<th style="width:10%;">Kategory</th>
				<th>Sub</th>
				<th>Nama</th>
				<th>Supplier</th>
				<th>Barcode</th>
				<th style="vertical-align:middle;">Diskon</th>
				<th>Rak</th>
				<th>Harga</th>
				<?php if($Security <= 1) { ?>
				<th>Faktur</th>
				<?php } ?>
				<th>Jual</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$query="SELECT 
					INV_NBR,
					(CASE 
						WHEN CONCAT(INV.NAME,' ',CASE WHEN COLR_DESC IS NULL OR COLR_DESC = '' THEN '' ELSE COLR_DESC END,' ',CASE WHEN THIC IS NULL OR THIC = '' THEN '' ELSE THIC END,' ',CASE WHEN SIZE IS NULL OR SIZE = '' THEN '' ELSE SIZE END,' ',CASE WHEN WEIGHT IS NULL OR WEIGHT = '' THEN '' ELSE WEIGHT END) IS NULL THEN INV.NAME 
						ELSE CONCAT(TRIM(INV.NAME),' ',CASE WHEN COLR_DESC IS NULL OR COLR_DESC = '' THEN '' ELSE TRIM(COLR_DESC) END,' ',CASE WHEN THIC IS NULL OR THIC = '' THEN '' ELSE TRIM(THIC) END,' ',CASE WHEN SIZE IS NULL OR SIZE = '' THEN '' ELSE TRIM(SIZE) END,' ',CASE WHEN WEIGHT IS NULL OR WEIGHT = '' THEN '' ELSE TRIM(WEIGHT) END)
					END) AS NAME,
					CAT_DESC,
					COM.NAME AS CO_NAME,
					CAT_SUB_DESC,
					INV.INV_BCD,
					INV_PRC,
					CAT_DISC_DESC,
					CAT_SHLF_DESC,
					CAT_PRC_DESC,
					PRC
				FROM RTL.INVENTORY INV 
					LEFT OUTER JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR AND SUB.DEL_NBR=0 
					LEFT OUTER JOIN RTL.CAT CAT ON SUB.CAT_NBR=CAT.CAT_NBR AND CAT.DEL_NBR=0 
					LEFT OUTER JOIN RTL.CAT_DISC DSC ON INV.CAT_DISC_NBR=DSC.CAT_DISC_NBR 
					LEFT OUTER JOIN RTL.CAT_SHLF SLF ON INV.CAT_DISC_NBR=SLF.CAT_SHLF_NBR 
					LEFT OUTER JOIN RTL.CAT_PRC PRC ON INV.CAT_PRC_NBR=PRC.CAT_PRC_NBR 
					LEFT OUTER JOIN CMP.INV_COLR CLR ON INV.COLR_NBR= CLR.COLR_NBR 
					LEFT OUTER JOIN CMP.COMPANY COM ON INV.CO_NBR=COM.CO_NBR
				WHERE INV.DEL_NBR=0
				ORDER BY INV.UPD_TS DESC";
						//echo $query;
				$result=mysql_query($query);
				$rowcol="a";
				$alt="";
				while($row=mysql_fetch_array($result))
				{
					if($Security <= 1) {
					echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-retail-list-edit.php?INV_NBR=".$row['INV_NBR']."';".chr(34).">";
					}else{
					echo "<tr $alt>";
					}
					echo "<td nowrap class='std-first' align=right>".$row['INV_NBR']."</td>";
					echo "<td nowrap class='std'>".$row['CAT_DESC']."</td>";
					echo "<td nowrap class='std'>".$row['CAT_SUB_DESC']."</td>";
					echo "<td nowrap class='std'>".$row['NAME']."</td>";
					echo "<td nowrap class='std'>".$row['CO_NAME']."</td>";
					echo "<td class='std'>".$row['INV_BCD']."</td>";
					echo "<td nowrap class='std'>".$row['CAT_DISC_DESC']."</td>";
					echo "<td nowrap class='std'>".$row['CAT_SHLF_DESC']."</td>";
					echo "<td class='std'>".$row['CAT_PRC_DESC']."</td>";
					if($Security <= 1) {
					echo "<td class='std' style='text-align:right;'>".number_format($row['INV_PRC'],0,',','.')."</td>";
					}
					echo "<td class='std' style='text-align:right;'>".number_format($row['PRC'],0,',','.')."</td>";
					echo "</tr>";
					if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
				}
			?>
		</tbody>
	</table>

</div>

<?php
	if($_GET['WHSE']!=""){$whse="?WHSE=".$_GET['WHSE'];}else{$whse="";}
?>

<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>

<script>liveReqInit('livesearch','liveRequestResults','inventory-retail-list-ls.php<?php echo $whse; ?>','','mainResult');</script>
</body>
</html>
