<?php
	include "framework/functions/default.php"; /*NEW*/
	if($_GET['DEL_L']!=""){
		include "framework/database/connect-cloud.php";
	}else{
		include "framework/database/connect.php";
	}
	include "framework/security/default.php";

	$executive 		= getSecurity($_SESSION['userID'],"Executive");
	$finance 		= getSecurity($_SESSION['userID'],"Finance");
	$PPlFinance     = array(706,368);

	if 	($_SESSION['personNBR']==''){
			echo "<script>parent.parent.location='login.php';</script>";
			exit;
	}

	$PrsnNbr=$_GET['PRSN_NBR'];
	
	if($cloud!=false){
		if($_GET['DEL_L']!=""){
			$query="UPDATE $PAY.EMPL_CRDT SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP WHERE PRSN_NBR=".$_GET['DEL_L']." AND PYMT_DTE='".$_GET['PYMT_DTE']."'";
	   		$result=mysql_query($query, $cloud);
			$query=str_replace($PAY,"PAY",$query);
			$result=mysql_query($query,$local);
		}
	}


	if ($_GET['FLTR_DATE']==''){
		$_GET['FLTR_DATE']=date('n Y');
	}

	$filter_date=str_replace("+"," ",$_GET['FLTR_DATE']);
	if ($filter_date!="") {
		$data	= explode(" ",$filter_date);
		$month	= $data[0];
		$year	= $data[1];
	}

	$query = "SELECT CRDT_APV_FIN FROM PAY.EMPL_CRDT WHERE PRSN_NBR=".$_SESSION['personNBR']." AND PYMT_DTE=CURDATE()";
	$result= mysql_query($query);
	$row_cek = mysql_fetch_array($result);
	
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
		<?php if((paramCloud()==1) && (((!in_array($_SESSION['personNBR'], $PPlFinance)) && $executive !=0 && ($row_cek['CRDT_APV_FIN']=='')) || (in_array($_SESSION['personNBR'], $PPlFinance) || $executive <1))){?> <a href="kasbontes.php?PRSN_NBR=0&PYMT_DTE=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a> <?php } ?>
	
		<select id="RCV_DATE" name="RCV_DATE" style="width:150px" class="chosen-select">
		<?php			
			$whereClause  = " AND PRSN_NBR=".$_SESSION['personNBR'];
			$whereClause .= " AND YEAR(PYMT_DTE)>=2018 ";

			if ($executive<1 || in_array($_SESSION['personNBR'], $PPlFinance)){
				$whereClause= "";
				
				if (in_array($_SESSION['personNBR'], $PPlFinance)){
					$whereClause = " AND YEAR(PYMT_DTE)>=2018 ";
				}
			}

			$query_dte	= "SELECT  PYMT_DTE, CONCAT(MONTH(PYMT_DTE),' ',YEAR(PYMT_DTE)) AS DTE,
				CONCAT(CASE 
					WHEN MONTH(PYMT_DTE)='1' THEN 'Januari'
					WHEN MONTH(PYMT_DTE)='2' THEN 'Februari'
					WHEN MONTH(PYMT_DTE)='3' THEN 'Maret'
					WHEN MONTH(PYMT_DTE)='4' THEN 'April'
					WHEN MONTH(PYMT_DTE)='5' THEN 'Mei'
					WHEN MONTH(PYMT_DTE)='6' THEN 'Juni'
					WHEN MONTH(PYMT_DTE)='7' THEN 'Juli'
					WHEN MONTH(PYMT_DTE)='8' THEN 'Agustus'
					WHEN MONTH(PYMT_DTE)='9' THEN 'September'
					WHEN MONTH(PYMT_DTE)='10' THEN 'Oktober'
					WHEN MONTH(PYMT_DTE)='11' THEN 'November'
					WHEN MONTH(PYMT_DTE)='12' THEN 'Desember'
				END,' ',YEAR(PYMT_DTE)) AS DTE_DESC
			FROM PAY.EMPL_CRDT 
			WHERE DEL_NBR=0 ".$whereClause." 
			GROUP BY YEAR(PYMT_DTE),MONTH(PYMT_DTE)
			ORDER BY YEAR(PYMT_DTE) DESC, MONTH(PYMT_DTE) DESC";
			genCombo($query_dte, "DTE", "DTE_DESC", $filter_date,"");
		?>
		</select>
		<span class="fa fa-calendar toolbar fa-lg" id="filter-by-date" style="padding-left:5px;margin-bottom:12px;cursor:pointer"
				onclick="location.href='kas-bon.php?FLTR_DATE='+document.getElementById('RCV_DATE').value"></span>
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
				<th>Tanggal</th>
				<?php if (!in_array($_SESSION['personNBR'], $PPlFinance) && $executive!=0) {?>
				<th>Bon</th>
				<th>Periode Cicilan</th>
				<th style="width: 25%;">Alasan</th>
				<?php }else{?>
				<th>Tipe Pencairan</th>
				<?php } ?>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
		<?php
			if (!in_array($_SESSION['personNBR'], $PPlFinance)){
				$whereClause = " AND EPC.PRSN_NBR=".$_SESSION['personNBR'];
				$groupBy 	 = " GROUP BY EPC.PRSN_NBR,PYMT_DTE";
			}
			if ($executive<1 || in_array($_SESSION['personNBR'], $PPlFinance)){ 
				$groupBy     = " GROUP BY EPC.PRSN_NBR";
				$whereClause = "";
			}

			$query="SELECT 
						EPC.PRSN_NBR,
						EPC.PYMT_DTE, 
						PPL.NAME AS PPL_NAME, 
						COM.NAME AS CO_NAME,
						POS.POS_DESC,
						EPC.CRDT_AMT,
						EPC.PYMT_NBR,
						EPC.CRDT_PRNC,
						EPC.CRDT_RSN,
						APV.NAME AS APV_NAME,
						CASE WHEN EPC.CRDT_APV =0 THEN 'Belum Disetujui' ELSE 'Disetujui' END AS CRDT_APV,
						COALESCE(SUM(CASE WHEN EPC.CRDT_APV =0 THEN 1 ELSE 0 END),0) AS CNT_DIS,
						COALESCE(SUM(CASE WHEN EPC.CRDT_APV =1 THEN 1 ELSE 0 END),0) AS CNT_APV,
						MAX(PYMT_DTE) AS MAX_PYMT_DTE,
						CASE WHEN EPC.DSBRS_TYP ='TRF' OR EPC.DSBRS_TYP IS NULL THEN 'Transfer' ELSE 'Payroll' END AS DSBRS_TYP 
					FROM PAY.EMPL_CRDT EPC
					LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=EPC.PRSN_NBR
					LEFT OUTER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP 
					LEFT OUTER JOIN CMP.COMPANY COM ON PPL.CO_NBR=COM.CO_NBR
					LEFT OUTER JOIN CMP.PEOPLE APV ON APV.PRSN_NBR=EPC.CRDT_APV_NBR 
					WHERE PPL.TERM_DTE IS NULL  
						AND EPC.DEL_NBR=0
						AND MONTH(PYMT_DTE)=".$month."
						AND YEAR(PYMT_DTE)=".$year."
						".$whereClause." ".$groupBy." ";
			
			$result=mysql_query($query);
			$alt="";
			$i=1;
			while($row=mysql_fetch_array($result))
			{ 
				$link = "kasbontes.php?PRSN_NBR=".$row['PRSN_NBR']."&PYMT_DTE=".$row['MAX_PYMT_DTE']."&FLTR_DATE=".$filter_date;
				if (!in_array($_SESSION['personNBR'], $PPlFinance)){
					$pymtDte = $row['PYMT_DTE'];
				}
				if ($executive<1 || in_array($_SESSION['personNBR'], $PPlFinance)) {
					$pymtDte = $row['MAX_PYMT_DTE'];
				}

				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='".$link."';".chr(34).">";
				echo "<td style='text-align:right'>".$i."</td>";
				echo "<td style='text-align:right'>".$row['PRSN_NBR']."</td>";
				echo "<td>".$row['PPL_NAME']."</td>";
				echo "<td>".$row['CO_NAME']."</td>";
				echo "<td style='text-align:center'>".$pymtDte."</td>";				
				
				if (!in_array($_SESSION['personNBR'], $PPlFinance) && $executive!=0){
					echo "<td style='text-align:right'>".number_format($row['CRDT_AMT'], 0, ",", ".")."</td>";
					echo "<td style='text-align:right'>".$row['PYMT_NBR']."</td>";
					echo "<td>".$row['CRDT_RSN']."</td>";	
				}else {
					echo "<td>".$row['DSBRS_TYP']."</td>";
				}
				echo "<td>".$row['CRDT_APV']."</td>";
				
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

<script>liveReqInit('livesearch','liveRequestResults','kas-bon-ls.php?PYMT_DTE_M=<?php echo $month;?>&PYMT_DTE_Y=<?php echo $year;?>','','mainResult');</script>

</body>
</html>


