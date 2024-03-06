<?php
	include "framework/functions/default.php"; /*NEW*/
	include "framework/database/connect.php";
	include "framework/security/default.php";

	$Accounting 	= getSecurity($_SESSION['userID'],"Accounting");
	$JatahCuti 		= 8;

	if 	($_SESSION['personNBR']==''){
			echo "<script>parent.parent.location='login.php';</script>";
			exit;
	}
	

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

</head>

<body>

<?php
	if(($_GET['DEL_A']!="")&&(!$cloud)){
		echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";			
	}	
?>

<div class="toolbar">
	<p class="toolbar-left">
		<?php if((paramCloud()==1)){?> <a href="time-off-edit.php?TM_OFF_NBR=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a> <?php } ?>
	
		<select id="RCV_DATE" name="RCV_DATE" style="width:150px" class="chosen-select">
		<?php			
			$whereClause= " AND PRSN_NBR=".$_SESSION['personNBR'];

			if ($Accounting<8){
				$whereClause= "";
			}

			$query_dte	= "SELECT  TM_OFF_BEG_DTE,CONCAT(MONTH(TM_OFF_BEG_DTE),' ',YEAR(TM_OFF_BEG_DTE)) AS DTE,
				CONCAT(CASE 
					WHEN MONTH(TM_OFF_BEG_DTE)='1' THEN 'Januari'
					WHEN MONTH(TM_OFF_BEG_DTE)='2' THEN 'Februari'
					WHEN MONTH(TM_OFF_BEG_DTE)='3' THEN 'Maret'
					WHEN MONTH(TM_OFF_BEG_DTE)='4' THEN 'April'
					WHEN MONTH(TM_OFF_BEG_DTE)='5' THEN 'Mei'
					WHEN MONTH(TM_OFF_BEG_DTE)='6' THEN 'Juni'
					WHEN MONTH(TM_OFF_BEG_DTE)='7' THEN 'Juli'
					WHEN MONTH(TM_OFF_BEG_DTE)='8' THEN 'Agustus'
					WHEN MONTH(TM_OFF_BEG_DTE)='9' THEN 'September'
					WHEN MONTH(TM_OFF_BEG_DTE)='10' THEN 'Oktober'
					WHEN MONTH(TM_OFF_BEG_DTE)='11' THEN 'November'
					WHEN MONTH(TM_OFF_BEG_DTE)='12' THEN 'Desember'
				END,' ',YEAR(TM_OFF_BEG_DTE)) AS DTE_DESC
			FROM PAY.TM_OFF 
			WHERE DEL_NBR=0 ".$whereClause."
			GROUP BY YEAR(TM_OFF_BEG_DTE),MONTH(TM_OFF_BEG_DTE)";
			genCombo($query_dte, "DTE", "DTE_DESC", $filter_date,"");
		?>
		</select>
		<span class="fa fa-calendar toolbar fa-lg" id="filter-by-date" style="padding-left:5px;margin-bottom:12px;cursor:pointer"
				onclick="location.href='time-off.php?FLTR_DATE='+document.getElementById('RCV_DATE').value"></span>
	</p>

	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter" style="width:100%">
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
			if (!($Accounting<8)){
				$whereClause = " AND TMO.PRSN_NBR=".$_SESSION['personNBR'];
				$groupBy 	 = " GROUP BY TM_OFF_NBR";
			}else{
				$groupBy     = " GROUP BY PRSN_NBR";
			}
			$query="SELECT 
						TMO.TM_OFF_NBR,
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
						".$whereClause." ".$groupBy." ";
			
			$result=mysql_query($query);
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
</div>

<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>

<script>liveReqInit('livesearch','liveRequestResults','time-off-ls.php?TM_OFF_M=<?php echo $month;?>&TM_OFF_Y=<?php echo $year;?>','','mainResult');</script>

</body>
</html>


