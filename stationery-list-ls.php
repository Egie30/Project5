<?php
	include "framework/database/connect.php";

	//Process location
	$whse=$_GET['WHSE'];
	if($whse!=""){$whse=" AND LOG.WHSE_NBR=".$whse;}

	echo $whse;

	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$query="SELECT STA_NBR,STA_TYP_DESC,CONCAT(NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE) AS NAME,UNIT,CTN_NBR
			FROM CMP.STATIONERY STA INNER JOIN 
				 CMP.STA_TYP TYP ON STA.STA_TYP=TYP.STA_TYP INNER JOIN
				 CMP.STA_COLR CLR ON STA.COLR_NBR=CLR.COLR_NBR
			WHERE (STA_NBR LIKE '%".$searchQuery."%' OR NAME LIKE '%".$searchQuery."%')
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

<table id="searchTable" class="sortable-onload-5-6r rowstyle-alt colstyle-alt no-arrow searchTable">
	<thead>
			<tr>
				<th class="sortable" style="text-align:right;">No.</th>
				<th class="sortable">Jenis</th>
				<th class="sortable">Nama</th>
				<th class="sortable">Unit</th>
				<th class="sortable">Isi</th>
			</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='stationery-list-edit.php?STA_NBR=".$row['STA_NBR']."';".chr(34).">";
			echo "<td class='std-first' align=right>".$row['STA_NBR']."</td>";
			echo "<td class='std'>".$row['STA_TYP_DESC']."</td>";
			echo "<td class='std'>".$row['NAME']."</td>";
			echo "<td class='std'>".$row['UNIT']."</td>";
			echo "<td class='std' align='right'>".$row['CTN_NBR']."</td>";
			echo "</tr>";
			if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
	?>
	</tbody>
</table>