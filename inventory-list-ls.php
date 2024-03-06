<?php
	include "framework/database/connect.php";

	//Process location
	$whse=$_GET['WHSE'];
	if($whse!=""){$whse=" AND LOG.WHSE_NBR=".$whse;}

	echo $whse;

	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$query="SELECT INV_NBR,INV_TYP_DESC,CONCAT(NAME,' ',COLR_DESC,' ',THIC,' ',SIZE,' ',WEIGHT) AS NAME,UNIT,CTN_NBR
			FROM CMP.INVENTORY INV INNER JOIN
			CMP.INV_TYP TYP ON INV.INV_TYP=TYP.INV_TYP INNER JOIN
			CMP.INV_COLR CLR ON INV.COLR_NBR=CLR.COLR_NBR
			WHERE (INV_NBR LIKE '%".$searchQuery."%' OR NAME LIKE '%".$searchQuery."%')
			ORDER BY UPD_DTE DESC
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
				<th>Jenis</th>
				<th>Nama</th>
				<th>Unit</th>
				<th>Isi</th>
			</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-list-edit.php?INV_NBR=".$row['INV_NBR']."';".chr(34).">";
			echo "<td class='std-first' align=right>".$row['INV_NBR']."</td>";
			echo "<td class='std'>".$row['INV_TYP_DESC']."</td>";
			echo "<td class='std'>".$row['NAME']."</td>";
			echo "<td class='std'>".$row['UNIT']."</td>";
			echo "<td class='std' align='right'>".$row['CTN_NBR']."</td>";
			echo "</tr>";
			if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
		}
	?>
	</tbody>
</table>