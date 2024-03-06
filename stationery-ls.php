<?php
	include "framework/database/connect.php";

	//Process location
	$whse=$_GET['WHSE'];
	if($whse!=""){$whse=" AND LOG.WHSE_NBR=".$whse;}

	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$query="SELECT LOG_NBR,MOV_DTE,CONCAT(NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE) AS NAME,MOV_DESC,MOV_CNT,WHSE_DESC
			FROM CMP.STA_LOG LOG INNER JOIN 
				 CMP.STA_MOV MOV ON LOG.MOV_TYP=MOV.MOV_TYP INNER JOIN 
				 CMP.STATIONERY STA ON LOG.STA_NBR=STA.STA_NBR INNER JOIN 
				 CMP.STA_TYP TYP ON STA.STA_TYP=TYP.STA_TYP INNER JOIN
				 CMP.STA_COLR CLR ON STA.COLR_NBR=CLR.COLR_NBR INNER JOIN
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
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='stationery-edit.php?LOG_NBR=".$row['LOG_NBR']."';".chr(34).">";
			echo "<td style='text-align:right'>".$row['LOG_NBR']."</td>";
			echo "<td>".$row['MOV_DTE']."</td>";
			echo "<td>".$row['NAME']."</td>";
			echo "<td>".$row['MOV_DESC']."</td>";
			echo "<td style='text-align:right'>".$row['MOV_CNT']."</td>";
			echo "</tr>";
			if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";};
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
	?>
	</tbody>
</table>
