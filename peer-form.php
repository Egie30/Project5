<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	include "framework/functions/default.php";

	$Accounting 		= getSecurity($_SESSION['userID'],"Accounting");

	if ($_GET['FLTR_DATE']==''){
		$_GET['FLTR_DATE']=date('n Y');
	}

	$filter_date=str_replace("+"," ",$_GET['FLTR_DATE']);
	if ($filter_date!="") {
		$data		= explode(" ",$filter_date);
		$month	= $data[0];
		$year	= $data[1];
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>	

<style>
.DesEllips {
    width: 120px;
    overflow:hidden; 
    white-space:nowrap; 
    text-overflow: ellipsis;
}
</style>
</head>
<body>

<div class="toolbar">
	<p class="toolbar-left">
		<?php if((paramCloud()==1)){ echo'<a href="peer-form-edit.php?PEER_FORM_NBR=0"><span class="fa fa-plus toolbar" style="cursor:pointer" onclick="location.href="></span></a>';} ?>
		
		<select id="RCV_DATE" name="RCV_DATE" style="width:150px" class="chosen-select">
		<?php
			if ($Accounting<8){
				$whereClause= "";
			}else{
				$whereClause= " AND CRT_NBR=".$_SESSION['personNBR'];
			}

			$query_dte	= "SELECT  PEER_DTE,CONCAT(MONTH(PEER_DTE),' ',YEAR(PEER_DTE)) AS DTE,
				CONCAT(CASE 
					WHEN MONTH(PEER_DTE)='1' THEN 'Januari'
					WHEN MONTH(PEER_DTE)='2' THEN 'Februari'
					WHEN MONTH(PEER_DTE)='3' THEN 'Maret'
					WHEN MONTH(PEER_DTE)='4' THEN 'April'
					WHEN MONTH(PEER_DTE)='5' THEN 'Mei'
					WHEN MONTH(PEER_DTE)='6' THEN 'Juni'
					WHEN MONTH(PEER_DTE)='7' THEN 'Juli'
					WHEN MONTH(PEER_DTE)='8' THEN 'Agustus'
					WHEN MONTH(PEER_DTE)='9' THEN 'September'
					WHEN MONTH(PEER_DTE)='10' THEN 'Oktober'
					WHEN MONTH(PEER_DTE)='11' THEN 'November'
					WHEN MONTH(PEER_DTE)='12' THEN 'Desember'
				END,' ',YEAR(PEER_DTE)) AS DTE_DESC
			FROM PAY.PEER_FORM 
			WHERE DEL_NBR=0 ".$whereClause."
			GROUP BY YEAR(PEER_DTE),MONTH(PEER_DTE)";
			genCombo($query_dte, "DTE", "DTE_DESC", $filter_date,"Filter Bulan Tahun");
		?>
		</select>
		<span class="fa fa-calendar toolbar fa-lg" id="filter-by-date" style="padding-left:5px;margin-bottom:12px;cursor:pointer"
				onclick="location.href='peer-form.php?FLTR_DATE='+document.getElementById('RCV_DATE').value"></span>
	</p>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
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
			if ($Accounting<8){
				$group 		 = "GROUP BY PRF.PRSN_NBR";
			}else{
				$whereClause = "AND CRT_NBR=".$_SESSION['personNBR'];
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
						// echo "<pre>".$query."</pre>";
			$result = mysql_query($query);
			$alt="";
			$i=1;
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
				echo "</tr>";
				$i++;
			}
		?>
		</tbody>
	</table>

</div>

<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>

<script>liveReqInit('livesearch','liveRequestResults','peer-form-ls.php?PEER_M=<?php echo $month;?>&PEER_Y=<?php echo $year;?>','','mainResult');</script>
</body>
</html>			
