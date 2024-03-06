<?php
	include "framework/database/connect.php";

	//Process location
	$whse=$_GET['WHSE'];
	if($whse!=""){$whse=" AND LOG.WHSE_NBR=".$whse;}

	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$query="SELECT LOG_NBR,MOV_DTE,CONCAT(NAME,' ',COLR_DESC,' ',THIC,' ',SIZE,' ',WEIGHT) AS NAME,MOV_DESC,MOV_CNT,WHSE_DESC
			FROM CMP.INV_LOG LOG INNER JOIN
			CMP.INV_MOV MOV ON LOG.MOV_TYP=MOV.MOV_TYP INNER JOIN
			CMP.INVENTORY INV ON LOG.INV_NBR=INV.INV_NBR INNER JOIN
			CMP.INV_TYP TYP ON INV.INV_TYP=TYP.INV_TYP INNER JOIN
			CMP.INV_COLR CLR ON INV.COLR_NBR=CLR.COLR_NBR INNER JOIN
			CMP.WHSE_LOC LOC ON LOG.WHSE_NBR=LOC.WHSE_NBR
			WHERE (LOG_NBR LIKE '%".$searchQuery."%' OR NAME LIKE '%".$searchQuery."%') $whse
			ORDER BY LOG.UPD_DTE DESC
			LIMIT 0,100";
	//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0)
	{
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>

<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th style="text-align:right;">No.</th>
			<th>Tanggal</th>
			<?php
				if($whse==""){echo "<th>Gudang</th>";}
			?>
			<th>Jenis Barang</th>
			<th>Status</th>
			<th>Jumlah</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-activity-edit.php?LOG_NBR=".$row['LOG_NBR']."';".chr(34).">";
					echo "<td class='std-first' align=right>".$row['LOG_NBR']."</td>";
					echo "<td class='std-first'>".$row['MOV_DTE']."</td>";
					if($whse==""){echo "<td class='std'>".$row['WHSE_DESC']."</td>";}
					echo "<td class='std'>".$row['NAME']."</td>";
					echo "<td class='std'>".$row['MOV_DESC']."</td>";
					echo "<td class='std' align='right'>".$row['MOV_CNT']."</td>";
					echo "</tr>";
					if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";};
		}
	?>
	</tbody>
</table>
