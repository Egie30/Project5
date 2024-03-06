<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	include "framework/functions/default.php";

	$Accounting 	= getSecurity($_SESSION['userID'],"Accounting");
	$upperSec 		= getSecurity($_SESSION['userID'],"Executive");

	$month			= $_GET['LOA_M'];
	$year 			= $_GET['LOA_Y'];

	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);
	$whereClause="";
	foreach($searchQ as $searchQuery)
	{
		$whereClause.=" AND (LOA_NBR LIKE '%".$searchQuery."%' OR 
						LOA.PRSN_NBR LIKE '%".$searchQuery."%' OR 
						PPL.NAME LIKE '%".$searchQuery."%' 
						) AND";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);

	if ($upperSec <= 5){
		$group 		 = "GROUP BY LOA.PRSN_NBR";
	}else{
		$whereClause .= " AND LOA.PRSN_NBR=".$_SESSION['personNBR'];
		$group 		 = "GROUP BY LOA.LOA_NBR";
	}
	
	$query="SELECT 
				LOA.LOA_NBR,
				LOA.PRSN_NBR,
				LOA.LOA_BEG_DTE,
				LOA.LOA_END_DTE,
				LOA.LOA_RSN,
				CASE WHEN LOA.LOA_F =0 THEN 'Disapprove' ELSE 'Approved' END AS LOA_F,
				SUM(CASE WHEN LOA.LOA_F = 0 THEN 1 ELSE 0 END) AS CNT_DPPV, 
				SUM(CASE WHEN LOA.LOA_F = 1 THEN 1 ELSE 0 END) AS CNT_APPV, 
				PPL.NAME AS FRM_NAME
			FROM PAY.LOA 
			LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR = LOA.PRSN_NBR
			WHERE LOA.DEL_NBR =0 AND MONTH(LOA_BEG_DTE)=".$month." AND YEAR(LOA_BEG_DTE)=".$year." ".$whereClause."
			".$group."
			ORDER BY LOA.UPD_TS DESC";
	// echo "<br><pre>".$query."</pre>";
	$result = mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}		
	
?>
<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th style="width: 5%;">No.</th>
			<th style="width: 7%;">NIK</th>
			<th>Nama</th>
			<?php if ($upperSec <= 5){ ?>
				<th>Approved</th>
				<th>Disapprove</th>
			<?php }?>
			<?php if (!($upperSec <= 5)){?> 
				<th>Tanggal Awal</th>
				<th>Tanggal Akhir</th>		
				<th>Alasan</th>
				<th>Status</th>
			<?php }?>
		</tr>
	</thead>
	<tbody>
	<?php
		$i=1;
		$alt="";
		while($row=mysql_fetch_array($result)){
			if ($upperSec <= 5){
				$link = "location.href='leave-of-absence-detail.php?PRSN_NBR=".$row['PRSN_NBR']."&LOA_M=".$month."&LOA_Y=".$year."';";
			}else{
				$link = "location.href='leave-of-absence-edit.php?LOA_NBR=".$row['LOA_NBR']."';";
			}
			
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34).$link.chr(34).">";				
			echo "<td class='std-first' style='text-align:right;'>".$i."</td>";
			echo "<td class='std' style='text-align:right;'>".$row['PRSN_NBR']."</td>";
			echo "<td class='std'>".$row['FRM_NAME']."</td>";
			if ($upperSec <= 5){
				echo "<td class='std' style='text-align:right;'>".$row['CNT_APPV']."</td>";
				echo "<td class='std' style='text-align:right;'>".$row['CNT_DPPV']."</td>";	
			}

			if (!($upperSec <= 5)){
				echo "<td class='std' style='text-align:right;'>".$row['LOA_BEG_DTE']."</td>";
				echo "<td class='std' style='text-align:right;'>".$row['LOA_END_DTE']."</td>";
				echo "<td class='std'><div class='DesEllips'>".$row['LOA_RSN']."</div></td>";
				echo "<td class='std'>".$row['LOA_F']."</td>";
			}
			$i++;
		}
	?>
	</tbody>
	</table>
	