<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
		
	$CoNbr 	= $_GET['CO_NBR'];
	
	$Security=getSecurity($_SESSION['userID'],"DigitalPrint");
	
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$query="SELECT LED.CO_NBR,LED.LEAD_NBR,STG_DESC,ACT_DESC,RAT_DESC,ACT_TS,ACT_NTE,UPD_TS FROM CMP.LEAD_DET LED INNER JOIN CMP.LEAD_STG STG ON LED.LEAD_STG=STG.STG_TYP 
			INNER JOIN CMP.LEAD_ACT ACT ON LED.LEAD_ACT=ACT.ACT_TYP INNER JOIN CMP.LEAD_RAT RAT ON LED.LEAD_RAT=RAT.RAT_TYP WHERE CO_NBR=".$CoNbr."
			AND DEL_NBR=0 AND (STG_DESC LIKE '%".$searchQuery."%' OR ACT_DESC LIKE '%".$searchQuery."%' OR RAT_DESC LIKE '%".$searchQuery."%' OR ACT_NTE LIKE '%".$searchQuery."%')
			ORDER BY 2";
	
	
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>
<br />
<table id="searchTable" class="tablesorter searchTable">
	<thead>
			<tr>
				<th style="text-align:right;">No.</th>
				<th>Tanggal</th>
				<th>Stage</th>
				<th>Aktivitas</th>
				<th>Rating</th>
				<th>Keterangan</th>
			</tr>
		</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
			{
				if($Security <= 1) { $target = "onclick=".chr(34)."location.href='lead-management-act-edit.php?LEAD_NBR=".$row['LEAD_NBR']."&CO_NBR=".$CoNbr."';".chr(34)." "; }
					else { $target = ""; }
					
				echo "<td style='text-align:right'>".$row['LEAD_NBR']."</td>";
				echo "<td>".$row['ACT_TS']."</td>";
				echo "<td>".$row['STG_DESC']."</td>";
				echo "<td>".$row['ACT_DESC']."</td>";
				echo "<td>".$row['RAT_DESC']."</td>";
				echo "<td>".$row['ACT_NTE']."</td>";
				echo "</tr>";
			}
	?>
	</tbody>
</table>
