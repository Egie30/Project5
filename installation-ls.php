<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";

	$IvcTyp = $_GET['IVC_TYP'];
	
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));

	$query = "SELECT HED.ORD_NBR,
					ORD_DTE,
					REF_NBR,
					SHP_CO_NBR,
					RCV_CO_NBR,
					SHP.NAME AS SHP_NAME,
					RCV.NAME AS RCV_NAME,
					HED.FEE_MISC,
					TOT_AMT,
					PYMT_DOWN,
					PYMT_REM,
					TOT_REM,
					DL_TS,
					SPC_NTE,
					HED.CRT_TS,
					HED.CRT_NBR,
					HED.UPD_TS,HED.UPD_NBR,
					DATEDIFF(DATE_ADD(ORD_DTE,INTERVAL COALESCE(SHP.PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS SHP_PAST_DUE,
				  	HED.CAT_SUB_NBR,
				    SUB.CAT_SUB_DESC,
				   (CASE WHEN HED.ACTG_TYP = 0 THEN '' ELSE HED.ACTG_TYP END) AS ACTG_TYP,
					DATE(HED.PYMT_REM_TS) AS PYMT_REM_DTE
			FROM $RTL.RTL_STK_HEAD HED
					LEFT OUTER JOIN $CMP.COMPANY SHP ON HED.SHP_CO_NBR=SHP.CO_NBR
					LEFT OUTER JOIN $CMP.COMPANY RCV ON HED.RCV_CO_NBR=RCV.CO_NBR	
					LEFT JOIN $RTL.CAT_SUB SUB ON HED.CAT_SUB_NBR = SUB.CAT_SUB_NBR
			WHERE (REF_NBR LIKE  '%".$searchQuery."%' OR SHP.NAME LIKE '%".$searchQuery."%' OR RCV.NAME LIKE '%".$searchQuery."%' OR HED.ORD_NBR LIKE '%".$searchQuery."%' OR ORD_DTE LIKE '%".$searchQuery."%' ) AND DEL_F = 0  AND HED.CAT_SUB_NBR = 273 ORDER BY HED.ORD_NBR DESC";
	$result=mysql_query($query,$cloud);
	// echo "<pre>".$query;
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
				<th style="text-align:right;">Kategori</th>
				<th style="text-align:right;">Referensi</th>
				<th>Pengirim</th>
				<th>Penerima</th>
				<th>Terima</th>
				<th>Nota</th>
				<?php 
				{ 
					echo "<th>Lunas</th>";
				} ?>
				<th style="text-align:right;">Jumlah</th>
				<?php ?>
					<th style="text-align:right;">Sisa</th>
				<?php
					if($_GET['SEL']=="DEB")
					{
						echo "<th>Jatuh Tempo</th>";
					}
				?>
			</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt style='cursor:pointer;'".chr(34).">";
				echo "<td style='text-align:right'>".$row['ORD_NBR']."</td>";
				echo "<td style='text-align:left'>".$row['CAT_SUB_DESC']."</td>";
				echo "<td style='text-align:right'>".$row['REF_NBR']."</td>";
				echo "<td>".$row['SHP_NAME']."</td>";
				echo "<td>".$row['RCV_NAME']."</td>";
				echo "<td style='text-align:center'>".parseDate($row['DL_TS'])."</td>";
				echo "<td style='text-align:center'>".parseDate($row['ORD_DTE'])."</td>";
				echo "<td>".parseDate($row['PYMT_REM_DTE'])."</td>";
				echo "<td style='text-align:right;'>".number_format($row['TOT_AMT'],0,',','.')."</td>";
				if($_GET['SEL']=="DEB")
				{
				echo "<td style='text-align:right'>".parseDate($row['PAST_DUE'])."</td>";
				}
				echo "<td style='text-align:right;'> ".number_format($row['TOT_REM'],0,',','.')."</td>";
				echo "<td style='text-align:center;'> ".$row['ACTG_TYP']."</td>";
				echo "</tr>";
		}
	?>
	</tbody>
</table>