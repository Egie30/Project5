<?php
include "framework/database/connect.php";

$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	
$query = "SELECT 			
	PPL.PRSN_NBR,
	NAME,
	POS_DESC,
	SUM(CASE WHEN VRFD_F = 1 THEN DIST ELSE 0 END) AS TOT_DIST,
	SUM(CASE WHEN MONTH(ORIG_TS) = MONTH(CURRENT_DATE) AND VRFD_F = 1 AND YEAR(ORIG_TS) = YEAR(CURRENT_DATE) THEN DIST ELSE 0 END) AS CUR_TOT_DIST
FROM CMP.AUTH_TRVL TRL
	INNER JOIN CMP.PEOPLE PPL ON TRL.PRSN_NBR=PPL.PRSN_NBR 
	INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP 
WHERE TERM_DTE IS NULL AND CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_PAYROLL) 
	AND(NAME LIKE '%".$searchQuery."%' OR PPL.PRSN_NBR LIKE '%".$searchQuery."%' OR POS_DESC LIKE '%".$searchQuery."%')
	AND PPL.DEL_NBR = 0
GROUP BY PPL.PRSN_NBR ORDER BY 2";

$result=mysql_query($query);
if(mysql_num_rows($result)==0){
	echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
	exit;
}
?>
<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th>No.</th>
			<th>Nama</th>
			<th>Jabatan</th>
			<th>Jarak</th>
			<th>Total</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$alt="";
	while($row=mysql_fetch_array($result)){
		echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='travel-list-edit.php?PRSN_NBR=".$row['PRSN_NBR']."';".chr(34).">";
		echo "<td class='listable' align=center>".$row['PRSN_NBR']."</a></td>";
		echo "<td class='listable' align='left'>".$row['NAME']."</td>";
		echo "<td class='listable' align='left'>".$row['POS_DESC']."</td>";
		echo "<td class='listable' style='text-align:right'>".number_format($row['TOT_DIST'],1,",",".")." km</td>";
		echo"<td class='listable' align='right'>".number_format($row['CUR_TOT_DIST'],1,",",".")." km</td>";
		echo "</tr>";
	}
	?>
	</tbody>
</table>