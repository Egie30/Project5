<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$IvcTyp	= $_GET['IVC_TYP'];
	$type	= $_GET['TYP'];
	
	if($type == "EST"){
		$headtable 	= "RTL.RTL_ORD_HEAD_EST";
		$detailtable= "RTL.RTL_ORD_DET_EST";
	}else{
		$headtable 	= "RTL.RTL_ORD_HEAD";
		$detailtable= "RTL.RTL_ORD_DET";
	}
	
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));

	$query="SELECT 
		HED.ORD_NBR,
		ORD_DTE,
		IVC_DESC,
		ORD_Q_TOT,
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
		CRT.NAME AS CRT_NAME,
		HED.UPD_TS,
		HED.UPD_NBR,DATEDIFF(DATE_ADD(ORD_DTE,INTERVAL COALESCE(SHP.PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS SHP_PAST_DUE
	FROM ". $headtable ." HED 
		LEFT OUTER JOIN(
			SELECT 
				HED.ORD_NBR, 
				SUM(ORD_Q) AS ORD_Q_TOT
			FROM ". $headtable ." HED 
				INNER JOIN RTL.RTL_STK_DET DET ON HED.ORD_NBR=DET.ORD_NBR
			GROUP BY DET.ORD_NBR ASC
		) AS DET ON HED.ORD_NBR=DET.ORD_NBR
		LEFT OUTER JOIN RTL.IVC_TYP IVC ON HED.IVC_TYP=IVC.IVC_TYP
		LEFT OUTER JOIN CMP.COMPANY SHP ON HED.SHP_CO_NBR=SHP.CO_NBR
		LEFT OUTER JOIN CMP.COMPANY RCV ON HED.RCV_CO_NBR=RCV.CO_NBR
		LEFT OUTER JOIN CMP.PEOPLE CRT ON HED.CRT_NBR=CRT.PRSN_NBR
	WHERE (REF_NBR LIKE '%".$searchQuery."%' OR SHP.NAME LIKE '%".$searchQuery."%' OR RCV.NAME LIKE '%".$searchQuery."%' OR HED.ORD_NBR LIKE '%".$searchQuery."%' OR ORD_DTE LIKE '%".$searchQuery."%' OR ORD_Q_TOT LIKE '%".$searchQuery."%') AND HED.IVC_TYP='".$IvcTyp."' AND DEL_F=0 ORDER BY HED.UPD_TS DESC";
	$result=mysql_query($query);
	//echo $query;
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>

<table id="searchTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
	<thead>
			<tr>
				<th class="sortable" style="text-align:right;">No.</th>
				<th class="sortable" style="text-align:right;">Item</th>
				<th class="sortable">Pengirim</th>
				<th class="sortable">Penerima</th>
				<th class="sortable">Tgl Order</th>
				<th class="sortable">Nota</th>
				<th class="sortable" style="text-align:right;">Jumlah</th>
				<th class="sortable" style="text-align:right;">Pembuat</th>
			</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='retail-order-edit.php?IVC_TYP=".$IvcTyp."&ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
				echo "<td style='text-align:right'>".$row['ORD_NBR']."</td>";
				if($IvcTyp=="RT"){
					echo "<td style='text-align:right'>- ".$row['ORD_Q_TOT']."</td>";
				}
				else{
					echo "<td style='text-align:right'> ".$row['ORD_Q_TOT']."</td>";
				}	
				echo "<td>".$row['SHP_NAME']."</td>";
				echo "<td>".$row['RCV_NAME']."</td>";
				//echo "<td>".$row['REF_NBR']." ".$row['NAME_CO']."</td>";
				echo "<td style='text-align:center'>".parseDate($row['DL_TS'])."</td>";
				echo "<td style='text-align:center'>".parseDate($row['ORD_DTE'])."</td>";
				if($IvcTyp=="RT"){
					echo "<td style='text-align:right;'> - ".number_format($row['TOT_AMT'],0,',','.')."</td>";
				}
				else{
					echo "<td style='text-align:right;'>".number_format($row['TOT_AMT'],0,',','.')."</td>";
				}
				if($_GET['SEL']=="DEB"){
					echo "<td style='text-align:right'>".parseDate($row['PAST_DUE'])."</td>";
				}
				echo "<td>".$row['CRT_NAME']."</td>";
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}		
		}
		//echo "<tr><td style='text-align:right;font-weight:bold'>Total</td><td style='text-align:right'>".number_format($tot,0,'.',',')."</td><td colspan=4>";			
		//echo "<td style='text-align:right;font-weight:bold' colspan=7>Total</td><td style='text-align:right'>".number_format($sub,0,'.',',')."</td></tr>";			
		
	?>
	</tbody>
</table>
