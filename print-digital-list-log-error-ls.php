<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";

	if($_GET['LOG_TYP']!=''){
		$where 	= "AND TYP.LOG_ERROR_TYP_NBR=".$_GET['LOG_TYP'];
	}

	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	
	//Take care of leading zeros on the order number
	if(is_numeric($searchQuery)){
		$searchQuery=$searchQuery+0;
	}
	
	$query 	= "SELECT 
					LOG.ORD_NBR,
					TYP.LOG_ERROR_DESC,
					HED.ORD_TTL,
					HED.TOT_AMT,
					PPL.NAME AS NAME_PPL,
					COM.NAME AS NAME_CO,
					ORD_TS,
					ORD_STT_DESC
				FROM CDW.LOG_ERROR_ORD LOG
				LEFT JOIN CMP.LOG_ERROR_TYP TYP ON LOG.LOG_ERROR_TYP_NBR = TYP.LOG_ERROR_TYP_NBR
				LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED ON LOG.ORD_NBR = HED.ORD_NBR
				LEFT JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID = STT.ORD_STT_ID
				LEFT JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
				LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR 
				WHERE (HED.ORD_TTL LIKE '%".$searchQuery."%' OR PPL.NAME LIKE '%".$searchQuery."%' OR COM.NAME LIKE '%".$searchQuery."%' OR HED.ORD_NBR LIKE '%".$searchQuery."%') 
					$where
				ORDER BY ORD_NBR DESC";
	//echo $query;
	$result = mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>

<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th class="sortable" style="text-align:center;">No. Nota</th>
			<th>Tanggal Nota</th>
			<th>Judul</th>
			<th>Pemesan</th>
			<th style="width:7%;">Total Nota</th>
			<th style="text-align:center;">Status Nota</th>
			<th>Log Error</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='print-digital-edit.php?ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
			echo "<td style='text-align:right'>".$row['ORD_NBR']."</td>";
			echo "<td style='text-align:center'>".parseDateShort($row['ORD_TS'])."</td>";
			echo "<td>".$row['ORD_TTL']."</td>";
			echo "<td>".$row['NAME_PPL']." ".$row['NAME_CO']."</td>";
			echo "<td style='text-align:right'>".number_format($row['TOT_AMT'],0,',','.')."</td>";
			echo "<td style='text-align:center'>".$row['ORD_STT_DESC']."</td>";
			echo "<td>".$row['LOG_ERROR_DESC']."</td>";
			echo "</tr>";
		}
	?>
	</tbody>
</table>
