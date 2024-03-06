<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	include "framework/functions/default.php";

	$Accounting 	= getSecurity($_SESSION['userID'],"Accounting");

	$month			= $_GET['PEER_M'];
	$year 			= $_GET['PEER_Y'];

	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);
	$whereClause="";
	foreach($searchQ as $searchQuery)
	{
		$whereClause.=" AND (PEER_FORM_NBR LIKE '%".$searchQuery."%' OR 
						PRF.PRSN_NBR LIKE '%".$searchQuery."%' OR 
						PPL.NAME LIKE '%".$searchQuery."%' 
						) AND";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);

	if ($Accounting<8){
		$group 		 = "GROUP BY PRF.PRSN_NBR";
	}else{
		$whereClause .= " AND CRT_NBR=".$_SESSION['personNBR'];
		$group 		 = "GROUP BY PRF.PEER_FORM_NBR";
	}
	
	$query="SELECT 
				PRF.PEER_FORM_NBR,
				PRF.PEER_DTE,
				PRF.PEER_RSN,
				PRF.PEER_CMNT,
				CASE WHEN PRF.PEER_APV_F =0 THEN 'Disapprove' ELSE 'Approved' END AS PEER_APV_F,
				PRF.PRSN_NBR AS RCV_NBR,
				PTY.PEER_TYP_DESC,
				PPL.NAME AS RCV_NAME,
				POS.POS_DESC,
				PP.NAME AS FRM_NAME,
				SUM(CASE WHEN PRF.PEER_APV_F = 0 THEN 1 ELSE 0 END) AS CNT_DSPV,
				SUM(CASE WHEN PRF.PEER_APV_F = 1 THEN 1 ELSE 0 END) AS CNT_PV
			FROM PAY.PEER_FORM PRF
			LEFT OUTER JOIN PAY.PEER_TYP PTY ON PTY.PEER_TYP_NBR=PRF.PEER_TYP
			LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR = PRF.PRSN_NBR
			LEFT OUTER JOIN CMP.PEOPLE PP ON PP.PRSN_NBR = PRF.CRT_NBR
			LEFT OUTER JOIN CMP.POS_TYP POS ON POS.POS_TYP = PPL.POS_TYP
			WHERE PRF.DEL_NBR =0 AND MONTH(PEER_DTE)=".$month." AND YEAR(PEER_DTE)=".$year." ".$whereClause."
			".$group."
			ORDER BY PRF.UPD_TS DESC";
	
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

			<?php if ($Accounting<8){?>
				<th>Disapprove</th>
				<th>Approve</th>
			<?php } ?>
			
			<th>Tanggal</th>
			
			<?php if (!($Accounting<8)){?> 		
				<th>Jenis Peer</th>
				<th>Alasan</th>
				<th>Komentar</th>
				<th>Status</th>
			<?php }?>
		</tr>
	</thead>
	<tbody>
	<?php
		$i=1;
		$alt="";
		while($row=mysql_fetch_array($result)){
			if ($Accounting<8){
				$link = "location.href='peer-form-detail.php?PRSN_NBR=".$row['RCV_NBR']."&PEER_M=".$month."&PEER_Y=".$year."';";
			}else{
				$link = "location.href='peer-form-edit.php?PEER_FORM_NBR=".$row['PEER_FORM_NBR']."';";
			}
			
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34).$link.chr(34).">";				
			echo "<td class='std-first' style='text-align:right;'>".$i."</td>";
			echo "<td class='std' style='text-align:right;'>".$row['RCV_NBR']."</td>";			
			echo "<td class='std'>".$row['RCV_NAME']."</td>";

			if ($Accounting<8){
				echo "<td class='std' style='text-align:right;'>".$row['CNT_DSPV']."</td>";
				echo "<td class='std' style='text-align:right;'>".$row['CNT_PV']."</td>";
			}

			echo "<td class='std' style='text-align:right;'>".$row['PEER_DTE']."</td>";

			if (!($Accounting<8)){
				echo "<td class='std'>".$row['PEER_TYP_DESC']."</td>";
				echo "<td class='std'><div class='DesEllips'>".$row['PEER_RSN']."</div></td>";
				echo "<td class='std'><div class='DesEllips'>".$row['PEER_CMNT']."</div></td>";
				echo "<td class='std'>".$row['PEER_APV_F']."</td>";
			}
			$i++;
		}
	?>
	</tbody>
	</table>
	