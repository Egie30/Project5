<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	
	$Security		= getSecurity($_SESSION['userID'],"Finance");
	$searchQuery 	= trim(strtoupper(urldecode($_REQUEST[s])));

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
	WHERE INV.DEL_NBR=0 AND (
		INV_NBR LIKE '%".$searchQuery."%' 
		OR INV.NAME LIKE '%".$searchQuery."%' 
		OR CAT_DESC LIKE '%".$searchQuery."%' 
		OR CAT_SUB_DESC LIKE '%".$searchQuery."%' 
		OR COM.NAME LIKE '%".$searchQuery."%' 
		OR INV_BCD LIKE '%".$searchQuery."%' 
		OR CAT_DISC_DESC LIKE '%".$searchQuery."%' 
		OR CAT_SHLF_DESC LIKE '%".$searchQuery."%' 
		OR CAT_PRC_DESC LIKE '%".$searchQuery."%'
		OR THIC LIKE '%".$searchQuery."%'
		OR SIZE LIKE '%".$searchQuery."%'
		OR CASE 
			WHEN COLR_DESC = '' THEN CONCAT(TRIM(INV.NAME),' ',TRIM(THIC),' ',TRIM(SIZE),' ',TRIM(WEIGHT)) LIKE '%".$searchQuery."%'
			WHEN THIC = '' THEN CONCAT(TRIM(INV.NAME),' ',TRIM(SIZE),' ',TRIM(WEIGHT)) LIKE '%".$searchQuery."%'
			ELSE CONCAT(INV.NAME,' ',THIC,' ',SIZE,' ',WEIGHT) LIKE '%".$searchQuery."%'
		END
	)
	ORDER BY INV.UPD_TS DESC";
	//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0)
	{
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>

<table id="searchTable" class="tablesorter searchTable" style=" width: 1295px;">
	<thead>
		<tr>
			<th style="text-align:right;">No.</th>
			<th>Kategory</th>
			<th>Sub</th>
			<th>Nama</th>
			<th>Supplier</th>
			<th>Barcode</th>
			<th>Diskon</th>
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
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			if($Security <= 1) {
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-retail-list-edit.php?INV_NBR=".$row['INV_NBR']."';".chr(34).">";
			}else{
			echo "<tr $alt>";
			}
			echo "<td class='std-first' align=right>".$row['INV_NBR']."</td>";
			echo "<td class='std'>".$row['CAT_DESC']."</td>";
			echo "<td class='std'>".$row['CAT_SUB_DESC']."</td>";
			echo "<td class='std'>".$row['NAME']."</td>";
			echo "<td class='std'>".$row['CO_NAME']."</td>";
			echo "<td class='std'>".$row['INV_BCD']."</td>";
			echo "<td class='std'>".$row['CAT_DISC_DESC']."</td>";
			echo "<td class='std'>".$row['CAT_SHLF_DESC']."</td>";
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