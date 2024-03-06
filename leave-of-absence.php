<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	include "framework/functions/default.php";

	$Accounting 		= getSecurity($_SESSION['userID'],"Accounting");
	$upperSec 			= getSecurity($_SESSION['userID'],"Executive");

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
		<?php echo'<a href="leave-of-absence-edit.php?LOA_NBR=0"><span class="fa fa-plus toolbar" style="cursor:pointer" onclick="location.href="></span></a>'; ?>
		
		<select id="RCV_DATE" name="RCV_DATE" style="width:150px" class="chosen-select">
		<?php
			if ($upperSec <= 5){
				$whereClause= "";
			}else{
				$whereClause= " AND PRSN_NBR=".$_SESSION['personNBR'];
			}

			$query_dte	= "SELECT  LOA_BEG_DTE,CONCAT(MONTH(LOA_BEG_DTE),' ',YEAR(LOA_BEG_DTE)) AS DTE,
				CONCAT(CASE 
					WHEN MONTH(LOA_BEG_DTE)='1' THEN 'Januari'
					WHEN MONTH(LOA_BEG_DTE)='2' THEN 'Februari'
					WHEN MONTH(LOA_BEG_DTE)='3' THEN 'Maret'
					WHEN MONTH(LOA_BEG_DTE)='4' THEN 'April'
					WHEN MONTH(LOA_BEG_DTE)='5' THEN 'Mei'
					WHEN MONTH(LOA_BEG_DTE)='6' THEN 'Juni'
					WHEN MONTH(LOA_BEG_DTE)='7' THEN 'Juli'
					WHEN MONTH(LOA_BEG_DTE)='8' THEN 'Agustus'
					WHEN MONTH(LOA_BEG_DTE)='9' THEN 'September'
					WHEN MONTH(LOA_BEG_DTE)='10' THEN 'Oktober'
					WHEN MONTH(LOA_BEG_DTE)='11' THEN 'November'
					WHEN MONTH(LOA_BEG_DTE)='12' THEN 'Desember'
				END,' ',YEAR(LOA_BEG_DTE)) AS DTE_DESC
			FROM PAY.LOA 
			WHERE DEL_NBR=0 ".$whereClause."
			GROUP BY YEAR(LOA_BEG_DTE),MONTH(LOA_BEG_DTE)";
			genCombo($query_dte, "DTE", "DTE_DESC", $filter_date,"Filter Bulan Tahun");
		?>
		</select>
		<span class="fa fa-calendar toolbar fa-lg" id="filter-by-date" style="padding-left:5px;margin-bottom:12px;cursor:pointer"
				onclick="location.href='leave-of-absence.php?FLTR_DATE='+document.getElementById('RCV_DATE').value"></span>
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
			if ($upperSec <= 5){
				$group 		 = "GROUP BY LOA.PRSN_NBR";
			}else{
				$whereClause = "AND LOA.PRSN_NBR=".$_SESSION['personNBR'];
				$group 		 = "GROUP BY LOA.LOA_NBR";
			}
			$query="SELECT 
							LOA.LOA_NBR,
							LOA.LOA_BEG_DTE,
							LOA.LOA_END_DTE,
							LOA.LOA_RSN,
							LOA.PRSN_NBR,
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
			$alt="";
			$i=1;
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

<script>liveReqInit('livesearch','liveRequestResults','leave-of-absence-ls.php?LOA_M=<?php echo $month;?>&LOA_Y=<?php echo $year;?>','','mainResult');</script>
</body>
</html>			
