<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$searchQuery = trim(strtoupper(urldecode($_REQUEST['s'])));
$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);
	$whereClause="";
	foreach($searchQ as $searchQuery)
	{
		$whereClause.="(CONCAT(CO_ID,CAL_ID,CAL_TYP) LIKE '%".$searchQuery."%' OR CAL_DESC LIKE '%".$searchQuery."%') AND ";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	//Search for kalender
	$query="SELECT CONCAT(CO_ID,CAL_ID,CAL_TYP) AS CAL_CODE,LST.CAL_NBR,CAL_DESC,CAL_PRC_BLK,CAL_PRC_PRN,SUM(CASE WHEN ORD_TYP='ORD' THEN ORD_Q ELSE 0 END)-SUM(CASE WHEN ORD_TYP='RCV' THEN ORD_Q ELSE 0 END)+SUM(CASE WHEN ORD_TYP='RET' THEN ORD_Q ELSE 0 END) AS SHP,SUM(CASE WHEN ORD_TYP='RCV' AND BUY_CO_NBR=1 THEN ORD_Q ELSE 0 END)-SUM(CASE WHEN ORD_TYP='REQ' AND SEL_CO_NBR=1 THEN ORD_Q ELSE 0 END)+SUM(CASE WHEN ORD_TYP='RET' AND SEL_CO_NBR=1 THEN ORD_Q ELSE 0 END) AS CMP
			FROM CMP.CAL_LST LST LEFT OUTER JOIN CMP.CAL_ORD_DET DET ON DET.CAL_NBR=LST.CAL_NBR LEFT OUTER JOIN CMP.CAL_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR LEFT OUTER JOIN CMP.COMPANY CMP ON LST.CO_NBR=CMP.CO_NBR
			WHERE ACTIVE_F IS TRUE AND ".$whereClause." AND LST.UPD_DTE BETWEEN ".getFiscalYear()."
			GROUP BY CONCAT(CO_ID,CAL_ID,CAL_TYP),CAL_NBR,CAL_DESC
			ORDER BY 3 DESC";
	//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>

<table id="searchTable" class="sortable-onload-5-6r rowstyle-alt colstyle-alt no-arrow searchTable">
	<thead>
		<tr>
			<th class="sortable" style="text-align:right;">Kode.</th>
				<th class="sortable">Deskripsi</th>
				<th class="sortable">Blanko</th>
				<th class="sortable">Cetak</th>
				<th class="sortable">Pesan</th>
				<th class="sortable" style="border-right:0px;">Stock</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='calendar-list-edit.php?CAL_NBR=".$row['CAL_NBR']."';".chr(34).">";
			echo "<td>".$row['CAL_CODE']."</td>";
			echo "<td>".$row['CAL_DESC']."</td>";
			echo "<td style='text-align:right'>".number_format($row['CAL_PRC_BLK'],0,",",".")."</td>";
			echo "<td style='text-align:right'>".number_format($row['CAL_PRC_PRN'],0,",",".")."</td>";
			echo "<td style='text-align:right'><a href='calendar-report-act.php?CAL_NBR=".$row['CAL_NBR']."'>".number_format($row['SHP'],0,",",".")."</td>";
			echo "<td style='text-align:right'><a href='calendar-report-act.php?CAL_NBR=".$row['CAL_NBR']."'>".number_format($row['CMP'],0,",",".")."</td>";
			echo "</tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
	?>
	</tbody>
</table>
