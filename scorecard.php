<?php
	include "framework/functions/default.php"; /*NEW*/
	include "framework/database/connect.php";
	include "framework/security/default.php";
	include "framework/functions/crypt.php";

	$Finance 	= getSecurity($_SESSION['userID'],"Finance");
	$Executive 	= getSecurity($_SESSION['userID'],"Executive");

	if 	($_SESSION['personNBR']==''){
			echo "<script>parent.parent.location='login.php';</script>";
			exit;
	}

	if ($Finance >2 && $Executive >6){
		echo "<script>parent.parent.location='login.php';</script>";
		exit;
	}

	if ($_GET['FLTR_DATE']==''){
		$_GET['FLTR_DATE']=date('Y-m-d');
		if ($_GET['FLTR_DATE']==date('Y-m-01',strtotime($_GET['FLTR_DATE']))){
			$_GET['FLTR_DATE']=date('Y-m-d',strtotime('-1 day', strtotime($_GET['FLTR_DATE'])));
		}
	}

	$filter_date = $_GET['FLTR_DATE'];

    $Details= json_decode(simple_crypt(file_get_contents('http://findiconic.nestoronline.com/scorecard-data.php?FLTR_DATE='.$filter_date),'d'));
	echo "<pre>";
	print_r($Details);
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
<script type = "text/javascript" src = "framework/mootools/mootools-latest.min.js"></script>
<script type = "text/javascript" src = "framework/mootools/mootools-latest-more.js"></script>
<script type = "text/javascript" src = "framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
<script type = "text/javascript" src = "framework/jquery/jquery-latest.min.js"></script>
<script type = "text/javascript" src = "framework/uri/src/URI.min.js"></script>
<script type = "text/javascript" src = "framework/functions/default.js"></script>	
<style type="text/css">
	table.tablesorter thead tr th{
		color: #000;
	}
</style>
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
			$query_dte	= "SELECT  DTE AS DATE_DTE,CONCAT(MONTH(DTE),' ',YEAR(DTE)) AS DTE,
				CONCAT(CASE 
					WHEN MONTH(DTE)='1' THEN 'Januari'
					WHEN MONTH(DTE)='2' THEN 'Februari'
					WHEN MONTH(DTE)='3' THEN 'Maret'
					WHEN MONTH(DTE)='4' THEN 'April'
					WHEN MONTH(DTE)='5' THEN 'Mei'
					WHEN MONTH(DTE)='6' THEN 'Juni'
					WHEN MONTH(DTE)='7' THEN 'Juli'
					WHEN MONTH(DTE)='8' THEN 'Agustus'
					WHEN MONTH(DTE)='9' THEN 'September'
					WHEN MONTH(DTE)='10' THEN 'Oktober'
					WHEN MONTH(DTE)='11' THEN 'November'
					WHEN MONTH(DTE)='12' THEN 'Desember'
				END,' ',YEAR(DTE)) AS DTE_DESC
			FROM CDW.PRN_DIG_DSH_BRD  
			GROUP BY YEAR(DTE),MONTH(DTE)
			ORDER BY YEAR(DTE) DESC ,MONTH(DTE) DESC";
			genCombo($query_dte, "DATE_DTE", "DTE_DESC", $filter_date,"");
		?>
		</select>
		<span class="fa fa-calendar toolbar fa-lg" id="filter-by-date" style="padding-left:5px;margin-bottom:12px;cursor:pointer"
				onclick="location.href='scorecard.php?FLTR_DATE='+document.getElementById('RCV_DATE').value"></span>
	</p>
	<?php if ($Executive<1){ ?>
	<p class="toolbar-right">
		<a title="Print PDF" onclick="parent.parent.document.getElementById('printDigitalReasonContent').src='scorecard-option-print.php?FLTR_DATE=<?php echo $filter_date; ?>';parent.parent.document.getElementById('printDigitalReason').style.display='block';parent.parent.document.getElementById('fade').style.display='block';">
			<span class='fa fa-file-pdf-o toolbar' style="cursor:pointer"></span></a>
		<!-- <a href="scorecard-pdf.php?FLTR_DATE=<?php echo $filter_date; ?>"><span class='fa fa-file-pdf-o toolbar' style="cursor:pointer"></span></a> -->
		<a title="Export to Excel" href="#" id="EXPORT_EXCEL">
				<span class="fa fa-file-excel-o toolbar" style="padding:5px;cursor:pointer"></span>
				<input type="hidden" id="livesearch" class="livesearch" style="margin-top:5px;"/>
		</a>
	</p>
	<?php } ?>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter" style="width:100%;">
		<thead>
			<?php if ($Executive<1){ ?>
			<tr>
				<th colspan = "7" style="text-align: left;">Campus</th>
				<th style="border-bottom: 0px solid #cacbcf;"></th>
				<th colspan = "7" style="text-align: left;">Printing</th>
			</tr>
			<?php }?>
			<tr>
				<th style="text-align: left;">Mesin</th>
				<th style="text-align: left;">Periode</th>
				<th colspan="2">Volume</th>
				<?php if ($Executive <1){?>
				<th colspan="3">Revenue</th>
				<?php } else {?>
				<th colspan="2">Revenue</th>
				<?php } ?>

				<?php if ($Executive <1){?>
				<th style="border-bottom: 0px solid #cacbcf;"></th>
				
				<th style="text-align: left;">Mesin</th>
				<th style="text-align: left;">Periode</th>
				<th colspan="2">Volume</th>
				<th colspan="3">Revenue</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
		<?php
			$query = " SELECT SCD.PRN_DIG_EQP, 
							  (CASE WHEN PRN_DIG_EQP='FLJ320P' THEN 'Outdoor'
							  		WHEN PRN_DIG_EQP='KMC6501' THEN 'A3+'
							   		WHEN PRN_DIG_EQP='RVS640' THEN 'Indoor'
							   		WHEN PRN_DIG_EQP='AJ1800F' THEN 'Direct Fabric'
							   		WHEN PRN_DIG_EQP='MVJ1624' THEN 'Heat Transfer'
							   		WHEN PRN_DIG_EQP='KMC8000' THEN 'R2S'
							   		WHEN PRN_DIG_EQP='KMC1085' THEN 'R2P'
							   		WHEN PRN_DIG_EQP='HPL375' THEN 'Latex'
									WHEN PRN_DIG_EQP='LABSVCS ' THEN 'Labor Service'
							   		ELSE 'Head Press'
							   	END) AS PRN_DIG_EQP_DESC, 
							  VOL, 
							  REV 
						FROM (
							SELECT 'FLJ320P' AS PRN_DIG_EQP, SUM(FLJ320P) AS VOL,SUM(REV_FLJ320P) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
							UNION ALL
							SELECT 'FLJ320P' AS PRN_DIG_EQP, SUM(FLJ320P) AS VOL,SUM(REV_FLJ320P) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
							UNION ALL
							SELECT 'FLJ320P' AS PRN_DIG_EQP, SUM(FLJ320P) AS VOL,SUM(REV_FLJ320P) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
							UNION ALL
							SELECT 'FLJ320P' AS PRN_DIG_EQP, SUM(FLJ320P) AS VOL,SUM(REV_FLJ320P) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
							UNION ALL
							SELECT 'KMC6501' AS PRN_DIG_EQP, SUM(KMC6501) AS VOL,SUM(REV_KMC6501) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
							UNION ALL
							SELECT 'KMC6501' AS PRN_DIG_EQP, SUM(KMC6501) AS VOL,SUM(REV_KMC6501) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
							UNION ALL
							SELECT 'KMC6501' AS PRN_DIG_EQP, SUM(KMC6501) AS VOL,SUM(REV_KMC6501) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
							UNION ALL
							SELECT 'KMC6501' AS PRN_DIG_EQP, SUM(KMC6501) AS VOL,SUM(REV_KMC6501) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
							UNION ALL
							SELECT 'RVS640' AS PRN_DIG_EQP, SUM(RVS640) AS VOL,SUM(REV_RVS640) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
							UNION ALL
							SELECT 'RVS640' AS PRN_DIG_EQP, SUM(RVS640) AS VOL,SUM(REV_RVS640) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
							UNION ALL
							SELECT 'RVS640' AS PRN_DIG_EQP, SUM(RVS640) AS VOL,SUM(REV_RVS640) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
							UNION ALL
							SELECT 'RVS640' AS PRN_DIG_EQP, SUM(RVS640) AS VOL,SUM(REV_RVS640) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
							UNION ALL
							SELECT 'AJ1800F' AS PRN_DIG_EQP, SUM(AJ1800F) AS VOL,SUM(REV_AJ1800F) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
							UNION ALL
							SELECT 'AJ1800F' AS PRN_DIG_EQP, SUM(AJ1800F) AS VOL,SUM(REV_AJ1800F) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
							UNION ALL
							SELECT 'AJ1800F' AS PRN_DIG_EQP, SUM(AJ1800F) AS VOL,SUM(REV_AJ1800F) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
							UNION ALL
							SELECT 'AJ1800F' AS PRN_DIG_EQP, SUM(AJ1800F) AS VOL,SUM(REV_AJ1800F) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
							UNION ALL
							SELECT 'MVJ1624' AS PRN_DIG_EQP, SUM(MVJ1624) AS VOL,SUM(REV_MVJ1624) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
							UNION ALL
							SELECT 'MVJ1624' AS PRN_DIG_EQP, SUM(MVJ1624) AS VOL,SUM(REV_MVJ1624) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
							UNION ALL
							SELECT 'MVJ1624' AS PRN_DIG_EQP, SUM(MVJ1624) AS VOL,SUM(REV_MVJ1624) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
							UNION ALL
							SELECT 'MVJ1624' AS PRN_DIG_EQP, SUM(MVJ1624) AS VOL,SUM(REV_MVJ1624) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
							UNION ALL
							SELECT 'KMC8000' AS PRN_DIG_EQP, SUM(KMC8000) AS VOL,SUM(REV_KMC8000) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
							UNION ALL
							SELECT 'KMC8000' AS PRN_DIG_EQP, SUM(KMC8000) AS VOL,SUM(REV_KMC8000) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
							UNION ALL
							SELECT 'KMC8000' AS PRN_DIG_EQP, SUM(KMC8000) AS VOL,SUM(REV_KMC8000) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
							UNION ALL
							SELECT 'KMC8000' AS PRN_DIG_EQP, SUM(KMC8000) AS VOL,SUM(REV_KMC8000) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
							UNION ALL
							SELECT 'KMC1085' AS PRN_DIG_EQP, SUM(KMC1085) AS VOL,SUM(REV_KMC1085) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
							UNION ALL
							SELECT 'KMC1085' AS PRN_DIG_EQP, SUM(KMC1085) AS VOL,SUM(REV_KMC1085) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
							UNION ALL
							SELECT 'KMC1085' AS PRN_DIG_EQP, SUM(KMC1085) AS VOL,SUM(REV_KMC1085) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
							UNION ALL
							SELECT 'KMC1085' AS PRN_DIG_EQP, SUM(KMC1085) AS VOL,SUM(REV_KMC1085) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
							UNION ALL
							SELECT 'HPL375' AS PRN_DIG_EQP, SUM(HPL375) AS VOL,SUM(REV_HPL375) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
							UNION ALL
							SELECT 'HPL375' AS PRN_DIG_EQP, SUM(HPL375) AS VOL,SUM(REV_HPL375) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
							UNION ALL
							SELECT 'HPL375' AS PRN_DIG_EQP, SUM(HPL375) AS VOL,SUM(REV_HPL375) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
							UNION ALL
							SELECT 'HPL375' AS PRN_DIG_EQP, SUM(HPL375) AS VOL,SUM(REV_HPL375) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
							UNION ALL
							SELECT 'ATX67' AS PRN_DIG_EQP, SUM(ATX67) AS VOL,SUM(REV_ATX67) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
							UNION ALL
							SELECT 'ATX67' AS PRN_DIG_EQP, SUM(ATX67) AS VOL,SUM(REV_ATX67) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
							UNION ALL
							SELECT 'ATX67' AS PRN_DIG_EQP, SUM(ATX67) AS VOL,SUM(REV_ATX67) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
							UNION ALL
							SELECT 'ATX67' AS PRN_DIG_EQP, SUM(ATX67) AS VOL,SUM(REV_ATX67) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
							UNION ALL
							SELECT 'LABSVCS' AS PRN_DIG_EQP, SUM(LABSVCS) AS VOL,SUM(REV_LABSVCS ) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
							UNION ALL
							SELECT 'LABSVCS' AS PRN_DIG_EQP, SUM(LABSVCS) AS VOL,SUM(REV_LABSVCS ) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
							UNION ALL
							SELECT 'LABSVCS' AS PRN_DIG_EQP, SUM(LABSVCS) AS VOL,SUM(REV_LABSVCS ) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
							UNION ALL
							SELECT 'LABSVCS' AS PRN_DIG_EQP, SUM(LABSVCS) AS VOL,SUM(REV_LABSVCS ) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
							) SCD";
			//echo "<pre>".$query;
			$result   = mysql_query($query);
			$Ket	  = array('Bulan ini', 'Bulan Lalu', '2 Bulan Lalu', 'Bulan Sama Tahun Lalu');
			$i=0;$j=0;
			while($row=mysql_fetch_array($result)){ 
				if ($i==0){
					$volOld='';
					//$revOldPct1='';
				}
				if ($volOld!=''){
					if ($row['VOL']!=0){
						$volPercen  = (($volOld-$row['VOL'])/$row['VOL'])*100;
					}else {
						$volPercen  = 0;
					}
					
					if ($Details->data[$j]->VOL!=0){
						$volPcnCbng = (($volOldCbng-$Details->data[$j]->VOL)/$Details->data[$j]->VOL)*100;
					}else{
						$volPcnCbng = 0;
					}

					if($row['REV']!=0){
						$revPrc     = (($revOld-$row['REV'])/$row['REV'])*100;
						$revPrcCbg  = (($revOld-$row['REV'])/$row['REV'])*100;
					}else{
						$revPrc    = 0;
						$revPrcCbg = 0;
					}

					if ($Details->data[$j]->REV!=0){
						$revPctCbng = (($revOldCbng-$Details->data[$j]->REV)/$Details->data[$j]->REV)*100; 
					}else {
						$revPctCbng = 0;
					}
					
					
					if ($volPercen <=0){$class = "print-digital-red";}else{$class='';}
					if ($volPcnCbng<=0){$classCbng = "print-digital-red";}else{$classCbng='';}
					if ($revPrcCbg <=0){$classRevCbng = "print-digital-red";}else{$classRevCbng='';}
					if ($revPctCbng<=0){$classRevPctCbng = "print-digital-red";}else{$classRevPctCbng='';}
					
					if ($volPercen==0){$class='';$volPercen='';}
					if ($volPcnCbng==0){$classCbng='';$volPcnCbng='';}
					if ($revPrcCbg==0){$classRevCbng='';$revPrcCbg='';}
					if ($revPctCbng==0){$classRevPctCbng='';$revPctCbng='';}

					if ($volPercen !=''){$volPercen=number_format($volPercen, 2, ",", ".").'%';}
					if ($volPcnCbng!=''){$volPcnCbng=number_format($volPcnCbng, 2, ",", ".").'%';}
					if ($revPrcCbg !=''){$revPrcCbg=number_format($revPrcCbg, 2, ",", ".").'%';}
					if ($revPctCbng !=''){$revPctCbng=number_format($revPctCbng, 2, ",", ".").'%';}
					
				}else{
					$volPercen       = '';
					$volPcnCbng      = '';
					$revPrcCbg       = '';
					$revPctCbng      = '';
					$class           = '';
					$classCbng       = '';
					$classRevCbng    = '';
					$classRevPctCbng = '';

				}
				//Hitung Revenue
				if ($row['REV']!=0){
					$rev      = ($row['REV']/($row['REV']+$Details->data[$j]->REV))*100;
				}else{
					$rev =0;
				}
				
				if ($Details->data[$j]->REV!=0){
					$revCbg   = ($Details->data[$j]->REV/($Details->data[$j]->REV+$row['REV']))*100;
				}else{
					$revCbg =0;
				}

				if ($i==3){$border = "border-bottom: 1px solid #cacbcf;";}else{$border='';}
				echo "<tr>";
				if ($prnDigEqp=='' || $prnDigEqp !=$row['PRN_DIG_EQP']){
					echo "<td rowspan='4' style='vertical-align:top;border-bottom: 1px solid #cacbcf;'>".$row['PRN_DIG_EQP_DESC']."</td>";					
				}
				echo "<td style='".$border."'>".$Ket[$i]."</td>";	
				echo "<td style='text-align:right;".$border."'>".number_format($row['VOL'], 0, ",", ".")."</td>";
				echo "<td style='text-align:center;".$border."'><div class='".$class."'>".$volPercen."</div></td>";
				if ($Executive <1){
					echo "<td style='text-align:right;".$border."'>".number_format($row['REV'], 0, ",", ".")."</td>";
				}
				echo "<td style='text-align:right;".$border."'>".number_format($rev, 1, ",", ".")."%</td>";
				echo "<td style='text-align:center;".$border."'><div class='".$classRevCbng."'>".$revPrcCbg."</div></td>";

				if ($Executive <1){
					echo "<td></td>";
				
					if ($prnDigEqp=='' || $prnDigEqp !=$row['PRN_DIG_EQP']){
						echo "<td rowspan='4' style='vertical-align:top; border-bottom: 1px solid #cacbcf;'>".$row['PRN_DIG_EQP_DESC']."</td>";
					}
					echo "<td style='".$border."'>".$Ket[$i]."</td>";
					echo "<td style='text-align:right;".$border."'>".number_format($Details->data[$j]->VOL, 0, ",", ".")."</td>";
					echo "<td style='text-align:center;".$border."'><div class='".$classCbng."'>".$volPcnCbng."</div></td>";
					echo "<td style='text-align:right;".$border."'>".number_format($Details->data[$j]->REV, 0, ",", ".")."</td>";
					echo "<td style='text-align:right;".$border."'>".number_format($revCbg, 1, ",", ".")."%</td>";
					echo "<td style='text-align:center;".$border."'><div class='".$classRevPctCbng."'>".$revPctCbng."</div></td>";
				}
				

				echo "</tr>";
				if ($i==0){
					$volOld     = $row['VOL'];
					$revOld     = $row['REV'];
					$volOldCbng = $Details->data[$j]->VOL;
					$revOldCbng = $Details->data[$j]->REV;
					
				}
				$prnDigEqp = $row['PRN_DIG_EQP'];
				$i++;$j++;
				if ($i==4){$i=0;}
			}
		?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
	function setDefaultQuery(url) 
	{
		url.setQuery(URI.parseQuery(location.search));
		return url;
	}

	document.getElementById("EXPORT_EXCEL").onclick = function() 
	{
		var url = setDefaultQuery(new URI("report-excel.php"));
		
		window.scrollTo(0,0);

		url.setQuery("s", document.getElementById("livesearch").value);
		url.setQuery("page", jQuery("#pagination-container").length > 0 ? jQuery("#pagination-container").attr("data-page") : 1);
		url.setQuery("RPT_TYP", "scorecard-excel");
		
		URI.removeQuery(url, "GROUP");
		
		window.open(url.build().toString(), "_blank");

	};
</script>

<script type="text/javascript">
	var url = setDefaultQuery(new URI("scorecard.php"));

	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
</script>
</body>
</html>


