<?php
	include "framework/database/connect.php";
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ     = explode(" ",$searchQuery);
	$whereClause = "";

	foreach($searchQ as $searchQuery){
		$whereClause.=" AND (PAY_CONFIG_NBR LIKE '%".$searchQuery."%' OR 
			PAY_BEG_DTE LIKE '%".$searchQuery."%' OR 
			PAY_END_DTE LIKE '%".$searchQuery."%') AND ";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	
	$query=mysql_query("SELECT PAY_CONFIG_NBR, 
							   PAY_BEG_DTE,
							   PAY_END_DTE,
							   (CASE WHEN PAY_ACT_F=0 THEN 'Tidak Aktif' ELSE 'Aktif' END) AS PAY_ACT_F
							FROM PAY.PAY_CONFIG_DTE
							WHERE YEAR(PAY_BEG_DTE)='".$year."' ".$whereClause." 
							ORDER BY 1 DESC");
	if(mysql_num_rows($query)==0){
		echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}		
	
?>
<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th>No.</th>
			<th>Tanggal Awal</th>
			<th>Tanggal Akhir</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$alt="";
	while($row=mysql_fetch_array($query)){
		$link = "location.href='payroll-config-dte-edit.php?PAY_CONFIG_NBR=".$row['PAY_CONFIG_NBR']."';";
		
		echo "<tr $alt style='cursor:pointer;' onclick=".chr(34).$link.chr(34).">";
		echo "<td class='std-first' style='text-align:right;'>".$row['PAY_CONFIG_NBR']."</td>";
		echo "<td class='std' style='text-align:center;'>".$row['PAY_BEG_DTE']."</td>";
		echo "<td class='std' style='text-align:center;'>".$row['PAY_END_DTE']."</td>";
		echo "<td class='std'>".$row['PAY_ACT_F']."</td>";
		echo "</tr>";
	}
	?>
	</tbody>
	</table>
	