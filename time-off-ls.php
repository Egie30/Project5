<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";

	$Accounting 	= getSecurity($_SESSION['userID'],"Accounting");
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	$month 		 = $_GET['TM_OFF_M'];
	$year 		 = $_GET['TM_OFF_Y'];

	if (!($Accounting<8)){
		$whereClause = " AND TMO.PRSN_NBR=".$_SESSION['personNBR'];
		$groupBy 	 = " GROUP BY TM_OFF_NBR";
	}else{
		$groupBy     = " GROUP BY PRSN_NBR";
	}
	
	$query="SELECT 
				TMO.PRSN_NBR, 
				PPL.NAME AS PPL_NAME, 
				COM.NAME AS CO_NAME,
				TM_OFF_BEG_DTE,
				TM_OFF_END_DTE,
				TM_OFF_RSN,
				COALESCE(DATEDIFF(TM_OFF_END_DTE,TM_OFF_BEG_DTE)+1,0) AS CNT_TM_OFF,
				CASE WHEN TMO.TM_OFF_F =0 THEN 'Disapprove' ELSE 'Approved' END AS TM_OFF_F,
				COALESCE(SUM(CASE WHEN TMO.TM_OFF_F =0 THEN 1 ELSE 0 END),0) AS CNT_DIS,
				COALESCE(SUM(CASE WHEN TMO.TM_OFF_F =1 THEN 1 ELSE 0 END),0) AS CNT_APV,
				MAX(DATE(CRT_TS)) AS CRT_TS
			FROM PAY.TM_OFF TMO
			LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=TMO.PRSN_NBR
			LEFT OUTER JOIN CMP.COMPANY COM ON PPL.CO_NBR=COM.CO_NBR
			WHERE TERM_DTE IS NULL 
				AND PPL.DEL_NBR=0 
				AND TMO.DEL_NBR=0 
				AND MONTH(TM_OFF_BEG_DTE)=".$month."
				AND YEAR(TM_OFF_BEG_DTE)=".$year."
				AND (
						TMO.PRSN_NBR LIKE '%".$searchQuery."%' OR 
						PPL.NAME LIKE '%".$searchQuery."%' OR 
						COM.NAME LIKE '%".$searchQuery."%' 
					)
				".$whereClause." ".$groupBy." ";
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>

<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th style="text-align:right;width:5%;">No.</th>
			<th style="text-align:right;width:5%;">NIK</th>
			<th style="width: 20%;">Nama</th>
			<th>Perusahaan</th>
				
			<?php if ($Accounting<8){?>				
				<th>Disapprove</th>
				<th>Approve</th>
				<th>Tanggal Terakhir</th>
			<?php }?>

			<?php if (!($Accounting<8)){?> 
				<th style="width: 25%;">Alasan</th>
				<th>Tanggal Awal</th>
				<th>Tanggal Akhir</th>
				<th>Sisa Cuti</th>
				<th>Status</th>
			<?php }?>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		$i=1;
		while($row=mysql_fetch_array($result))
		{
			if ($Accounting<8){
				$link = "time-off-detail.php?PRSN_NBR=".$row['PRSN_NBR']."&TM_OFF_M=".$month."&TM_OFF_Y=".$year;
			}else{
				$link = "time-off-edit.php?PRSN_NBR=".$row['PRSN_NBR']."&TM_OFF_NBR=".$row['TM_OFF_NBR'];
			}

			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='".$link."';".chr(34).">";
			echo "<td style='text-align:right'>".$i."</td>";
			echo "<td style='text-align:right'>".$row['PRSN_NBR']."</td>";
			echo "<td>".$row['PPL_NAME']."</td>";
			echo "<td>".$row['CO_NAME']."</td>";

			if ($Accounting<8){
				echo "<td style='text-align:right'>".$row['CNT_DIS']."</td>";
				echo "<td style='text-align:right'>".$row['CNT_APV']."</td>";
				echo "<td style='text-align:right'>".$row['CRT_TS']."</td>";
			}

			if (!($Accounting<8)){
				echo "<td>".$row['TM_OFF_RSN']."</td>";
				echo "<td style='text-align:right'>".$row['TM_OFF_BEG_DTE']."</td>";
				echo "<td style='text-align:right'>".$row['TM_OFF_END_DTE']."</td>";
				echo "<td style='text-align:right'>".$row['CNT_TM_OFF']."</td>";
				echo "<td style='text-align:right'>".$row['TM_OFF_F']."</td>";
			}
			echo "</tr>";
		$i++;}
	?>
	</tbody>
</table>
