<?php
	include "framework/database/connect.php";
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);
	$whereClause="";
	foreach($searchQ as $searchQuery)
	{
		$whereClause.="(CAT_SUB_NBR LIKE '%".$searchQuery."%' OR CAT_SUB_DESC LIKE '%".$searchQuery."%' OR CAT_DESC LIKE '%".$searchQuery."%') AND ";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	
	$query=mysql_query("SELECT SUB.CAT_SUB_NBR,
								SUB.CAT_SUB_DESC, 
								CAT.CAT_DESC,
								TYP.CAT_TYP, 
								CONCAT(CDCAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', CDSUB.CD_SUB_DESC) AS ACC_DESC
							FROM RTL.CAT_SUB SUB 
								INNER JOIN RTL.CAT CAT ON CAT.CAT_NBR=SUB.CAT_NBR
								LEFT JOIN RTL.CAT_TYP TYP ON SUB.CAT_TYP_NBR = TYP.CAT_TYP_NBR
								LEFT JOIN RTL.ACCTG_CD_SUB CDSUB ON SUB.CD_SUB_NBR = CDSUB.CD_SUB_NBR
								LEFT JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=CDSUB.CD_NBR
								LEFT JOIN RTL.ACCTG_CD_CAT CDCAT ON CDCAT.CD_CAT_NBR=ACC.CD_CAT_NBR
						WHERE SUB.DEL_NBR=0 AND (".$whereClause.")
						ORDER BY 2 DESC");
	if(mysql_num_rows($query)==0){
		echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}		
	
?>
<table id="mainTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th style="text-align:center;">No</th>
			<th>Kategori</th>
			<th>Deskripsi</th>
			<th>Tipe</th>
			<th>Akun</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$alt="";
	while($row=mysql_fetch_array($query)){
		echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='category-sub-edit.php?CAT_SUB_NBR=".$row['CAT_SUB_NBR']."';".chr(34).">";
		echo "<td style='text-align:center;'>".$row['CAT_SUB_NBR']."</td>";
		echo "<td class='std'>".$row['CAT_DESC']."</td>";
		echo "<td style='text-align:left;'>".$row['CAT_SUB_DESC']."</td>";
		echo "<td class='std'>".$row['CAT_TYP']."</td>";
		echo "<td class='std'>".$row['ACC_DESC']."</td>";
		echo "</tr>";
		}
	?>
	</tbody>
	</table>
	