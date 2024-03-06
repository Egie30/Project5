<?php
	include "framework/functions/default.php"; /*NEW*/
	include "framework/database/connect.php";
	include "framework/security/default.php";
	include "framework/functions/crypt.php";

	$Accounting 	= getSecurity($_SESSION['userID'],"Accounting");
	$JatahCuti 		= 8;
	$revF           = $_GET['REV_F'];

	if 	($_SESSION['personNBR']==''){
			echo "<script>parent.parent.location='login.php';</script>";
			exit;
	}

	if ($_GET['FLTR_DATE']==''){
		$_GET['FLTR_DATE']=date('Y-m-d');
	}

	$filter_date = $_GET['FLTR_DATE'];

    $Details= json_decode(simple_crypt(file_get_contents('http://printing.champs.asia/scorecard-data.php?FLTR_DATE='.$filter_date),'d'));

    $display = "";
    $thRev   = "<th colspan='3'>Revenue</th>";
    $colspanHead = "7";

    if ($revF ==1)
    {
    	$display = "display:none;";
    	$thRev   = "<th width='15%' colspan='2'>Revenue</th>";
    	$colspanHead = "6";
    	$volwidth = "width='15%'";
    }
    else
    {
    	$volwidth = "width='10%'";
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
<style type="text/css">
	table.tablesorter thead tr th{
		color: #000;
		font-family:  'HelveticaNeue', 'Helvetica Neue', Helvetica, Arial, sans-serif;
		font-size: 9pt;
	}
	td{
		padding: 0.5px 0.5px 0.5px 6px;
		font-family:  'HelveticaNeue', 'Helvetica Neue', Helvetica, Arial, sans-serif ;
		font-size: 8pt;
	}
	div.print-digital-red{
		padding: 0.5px 0px 0.5px 0px;
	}
	label{
		font-size: 8pt;
	}
</style>
</head>

<body style="margin: 2em 1em 2em 1em;">
<div id="mainResult">
	<?php 
		$Month= array('Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
		$m = date('m',strtotime($filter_date));
		$y =date('Y', strtotime($filter_date));
		echo "<label style='font-weight:700;margin-left:6px;'>".strtoupper($Month[$m-1])." ".$y." SCORECARD</label>";

	?>
	<table id="mainTable" class="tablesorter" style="width:100%;margin-top: 5px;">
		<thead>
			<tr>
				<th colspan = "<?php echo $colspanHead;?>" style="text-align: left;">Campus</th>
				<th style="border-bottom: 0px solid #cacbcf;"></th>
				<th colspan = "<?php echo $colspanHead;?>" style="text-align: left;">Printing</th>
			</tr>
			<tr>
				<th width="10%" style="text-align: left;">Mesin</th>
				<th width="12%" style="text-align: left;">Periode</th>
				<th <?php echo $volwidth ?> colspan="2">Volume</th>
				<?php echo $thRev; ?>

				<th style="border-bottom: 0px solid #cacbcf;"></th>
				
				<th width="10%" style="text-align: left;">Mesin</th>
				<th width="12%" style="text-align: left;">Periode</th>
				<th <?php echo $volwidth ?> colspan="2">Volume</th>
				<?php echo $thRev; ?>
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
							SELECT 'LABSVCS' AS PRN_DIG_EQP, SUM(LABSVCS) AS VOL,SUM(REV_LABSVCS) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
							UNION ALL
							SELECT 'LABSVCS' AS PRN_DIG_EQP, SUM(LABSVCS) AS VOL,SUM(REV_LABSVCS) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
							UNION ALL
							SELECT 'LABSVCS' AS PRN_DIG_EQP, SUM(LABSVCS) AS VOL,SUM(REV_LABSVCS) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
							UNION ALL
							SELECT 'LABSVCS' AS PRN_DIG_EQP, SUM(LABSVCS) AS VOL,SUM(REV_LABSVCS) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))				
							) SCD";
			$result   = mysql_query($query);
			$Ket	  = array('Bulan ini', 'Bulan Lalu', '2 Bulan Lalu', 'Bulan Sama Tahun Lalu');
			$i=0;$j=0;
			while($row=mysql_fetch_array($result)){ 
				if ($i==0){
					$volOld='';
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
				echo "<td style='text-align:right;".$border.";".$display."'>".number_format($row['REV'], 0, ",", ".")."</td>";
				echo "<td style='text-align:right;".$border."'>".number_format($rev, 1, ",", ".")."%</td>";
				echo "<td style='text-align:center;".$border."'><div class='".$classRevCbng."'>".$revPrcCbg."</div></td>";

				echo "<td></td>";
				
				if ($prnDigEqp=='' || $prnDigEqp !=$row['PRN_DIG_EQP']){
					echo "<td rowspan='4' style='vertical-align:top; border-bottom: 1px solid #cacbcf;'>".$row['PRN_DIG_EQP_DESC']."</td>";					
				}
				echo "<td style='".$border."'>".$Ket[$i]."</td>";
				echo "<td style='text-align:right;".$border."'>".number_format($Details->data[$j]->VOL, 0, ",", ".")."</td>";
				echo "<td style='text-align:center;".$border."'><div class='".$classCbng."'>".$volPcnCbng."</div></td>";
				echo "<td style='text-align:right;".$border.";".$display."'>".number_format($Details->data[$j]->REV, 0, ",", ".")."</td>";
				echo "<td style='text-align:right;".$border."'>".number_format($revCbg, 1, ",", ".")."%</td>";
				echo "<td style='text-align:center;".$border."'><div class='".$classRevPctCbng."'>".$revPctCbng."</div></td>";

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
</body>
</html>


