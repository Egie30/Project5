<?php
	include "framework/database/connect.php";
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);
	$whereClause="";
	foreach($searchQ as $searchQuery)
	{
		$whereClause.="(UTL.UTL_NBR LIKE '%".$searchQuery."%' OR PPL.NAME LIKE '%".$searchQuery."%' OR COM.NAME LIKE '%".$searchQuery."%' OR REF_NBR_INT LIKE '%".$searchQuery."%' OR REF_NBR_EXT LIKE '%".$searchQuery."%') AND ";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	
	$query="SELECT UTL_NBR,DATE(UTL_DTE) AS DTE,PPL.NAME AS PPL_NAME,COM.NAME AS COM_NAME,UTL_DESC,TOT_SUB
			FROM CMP.UTILITY UTL INNER JOIN
			CMP.UTL_TYP TYP ON UTL.UTL_TYP=TYP.UTL_TYP LEFT OUTER JOIN
			CMP.PEOPLE PPL ON UTL.PRSN_NBR=PPL.PRSN_NBR LEFT OUTER JOIN
			CMP.COMPANY COM ON UTL.CO_NBR=COM.CO_NBR
			WHERE UTL.UTL_CO_NBR='".$CoNbrDef."' AND ".$whereClause."
			ORDER BY 1 DESC";
	//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>
<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th class="sortable" style="text-align:right;">No.</th>
			<th class="sortable">Tanggal</th>
			<th class="sortable">Petugas</th>
			<th class="sortable">Client</th>
			<th class="sortable">Pengeluaran</th>
			<th class="sortable" style="border-right:0px;">Jumlah</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='utility-edit.php?UTL_NBR=".$row['UTL_NBR']."';".chr(34).">";
			echo "<td style='text-align:right'>".$row['UTL_NBR']."</td>";
			echo "<td>".$row['DTE']."</td>";
			echo "<td>".$row['PPL_NAME']."</td>";
			echo "<td>".$row['COM_NAME']."</td>";
			echo "<td>".$row['UTL_DESC']."</td>";
			echo "<td style='text-align:right'>".number_format($row['TOT_SUB'],0,'.','.')."</td>";
			echo "</tr>";
		}
	?>
	</tbody>
</table>
