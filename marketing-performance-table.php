<?php
	include "framework/database/connect.php";
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";

	$Security 		= getSecurity($_SESSION['userID'],"AddressBook");
	$PayConfigNbr	= $_GET['PAY_CONFIG_NBR'];
	$PrsnNbr	 	= $_GET['PRSN_NBR'];
	$filterOption	= $_GET['FLR_OPT'];
	$FLR_MPR_PPL	= $_GET['FLR_MPR_PPL'];
	$Typ			= $_GET['TYP'];
	
	if ($PayConfigNbr==''){
		$query = "SELECT 
			PAY_CONFIG_NBR,
			PAY_BEG_DTE
		FROM PAY.PAY_CONFIG_DTE
		WHERE PAY_BEG_DTE <= CURRENT_DATE AND PAY_END_DTE >= CURRENT_DATE ";
		$result = mysql_query($query, $local);
		$rowDte = mysql_fetch_array($result);

		$PayConfigNbr = $rowDte['PAY_CONFIG_NBR'];
		$date         = $rowDte['PAY_BEG_DTE'];
	}else{
		$query = "SELECT PAY_BEG_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_CONFIG_NBR = ".$PayConfigNbr;
		$result= mysql_query($query, $local);
		$rowDte= mysql_fetch_array($result);

		$date  = $rowDte['PAY_BEG_DTE'];
	}
	
	//echo "dsfdsfdsfds : ".$date." -- ".$PayConfigNbr;
	
	$query = "SELECT 
					RWD_M_BASE_Q,
					RWD_S_BASE_Q,
					RWD_M_INC_Q,
					RWD_S_INC_Q,
					RWD_M_INC_PCT,
					RWD_S_INC_PCT,
					CO_NBR_DEF
				FROM NST.PARAM_LOC";
	$result = mysql_query($query, $local);
	$rowPL  = mysql_fetch_array($result);
	//Base lama Base Meter : 1650 | Base Lembar:2250
	$RwdMBaseQ = $rowPL['RWD_M_BASE_Q'];
	$RwdSBaseQ = $rowPL['RWD_S_BASE_Q'];
	$RwdMIncQ  = $rowPL['RWD_M_INC_Q'];
	$RwdSIncQ  = $rowPL['RWD_S_INC_Q'];
	$RwdMIncPct= $rowPL['RWD_M_INC_PCT'];
	$RwdSIncPct= $rowPL['RWD_S_INC_PCT'];
	$CoNbrDef  = $rowPL['CO_NBR_DEF'];

	$query = "SELECT 
					ROUND(M_AVG) AS M_AVG,
					ROUND(S_AVG) AS S_AVG,
					FLOOR(M_Q) AS M_Q,
					FLOOR(S_Q) AS S_Q
				FROM $CDW.PAY_RWD PAR 
				WHERE PRSN_NBR =".$PrsnNbr." 
					AND PAR.PAY_CONFIG_NBR = ".$PayConfigNbr." 
					AND PAR.OWN_CO_NBR = ".$CoNbrDef." ";
	//echo "<pre>".$query."<br><br>";
	$result = mysql_query($query, $cloud);
	$rowDet = mysql_fetch_array($result);

	$avgMeterCamp	= $rowDet['M_AVG'];
	$avgLembarCamp	= $rowDet['S_AVG'];
	$mQtyCamp		= $rowDet['M_Q'];
	$sQtyCamp		= $rowDet['S_Q'];

	$query  = "SELECT 
					COALESCE(ROUND(M_AVG_ALL),0) AS M_AVG_ALL, 
					COALESCE(ROUND(S_AVG_ALL),0) AS S_AVG_ALL
				FROM $CDW.PAY_RWD RWD
				RIGHT JOIN (
					SELECT 
						DTE.PAY_CONFIG_NBR
					FROM $PAY.PAY_CONFIG_DTE DTE
					WHERE PAY_BEG_DTE <= (DATE_FORMAT('".$date."', '%Y-%m-01') - INTERVAL 1 MONTH)
						AND PAY_END_DTE >=(DATE_FORMAT('".$date."', '%Y-%m-01') - INTERVAL 1 MONTH)
				) CON ON RWD.PAY_CONFIG_NBR = CON.PAY_CONFIG_NBR
				WHERE RWD.OWN_CO_NBR = ".$CoNbrDef." 
				LIMIT 1";
	//echo "<pre>".$query."<br><br>";
	$result = mysql_query($query, $cloud);
	$rowDet = mysql_fetch_array($result);
	$avgAllOldMeterCamp  = $rowDet['M_AVG_ALL'];
	$avgAllOldLembarCamp = $rowDet['S_AVG_ALL'];
	
	$query = "SELECT 
					M_AVG AS M_COM_AVG,
					S_AVG AS S_COM_AVG
				FROM $CDW.PAY_RWD PAR 
				WHERE (PRSN_NBR = 0 OR PRSN_NBR IS NULL) 
					AND PAR.PAY_CONFIG_NBR = ".$PayConfigNbr." 
					AND PAR.OWN_CO_NBR = ".$CoNbrDef." ";
	//echo "<pre>".$query."<br><br>";
	$result = mysql_query($query, $cloud);
	$rowDet = mysql_fetch_array($result);
	$comAvgMeterCamp	= $rowDet['M_COM_AVG'];  
	$comAvgLembarCamp	= $rowDet['S_COM_AVG']; 

	$indexMeterCamp = $avgMeterCamp / $comAvgMeterCamp;
	$indexLembarCamp = $avgLembarCamp / $comAvgLembarCamp;
	
	$Bon_meter_camp  = FLOOR(($mQtyCamp - $RwdMBaseQ) / 100 * $indexMeterCamp * 1);
	$Bon_lembar_camp = FLOOR(($sQtyCamp - $RwdSBaseQ) / 1500 * $indexLembarCamp * 1);
	
	if ($Bon_meter_camp <=0){ $Bon_meter_camp = 0;}
	if ($Bon_lembar_camp <=0){ $Bon_lembar_camp = 0;}
	
	$tot_bon_camp = $Bon_meter_camp + $Bon_lembar_camp;

	if ($tot_bon_camp <=0){ $tot_bon_camp = 0;} 
	
	
	//Printing
	$query = "SELECT 
					ROUND(M_AVG) AS M_AVG,
					ROUND(S_AVG) AS S_AVG,
					FLOOR(M_Q) AS M_Q,
					FLOOR(S_Q) AS S_Q
				FROM $CDW.PAY_RWD PAR 
				WHERE PRSN_NBR =".$PrsnNbr." 
					AND PAR.PAY_CONFIG_NBR = ".$PayConfigNbr." 
					AND PAR.OWN_CO_NBR != ".$CoNbrDef." ";
	//echo "<pre>".$query."<br><br>";
	$result = mysql_query($query, $cloud);
	$rowDet = mysql_fetch_array($result);

	$avgMeterPrint	= $rowDet['M_AVG'];
	$avgLembarPrint	= $rowDet['S_AVG'];
	$mQtyPrint		= $rowDet['M_Q'];
	$sQtyPrint		= $rowDet['S_Q'];
	
	$query  = "SELECT 
					COALESCE(ROUND(M_AVG_ALL),0) AS M_AVG_ALL, 
					COALESCE(ROUND(S_AVG_ALL),0) AS S_AVG_ALL
				FROM $CDW.PAY_RWD RWD
				RIGHT JOIN (
					SELECT 
						DTE.PAY_CONFIG_NBR
					FROM $PAY.PAY_CONFIG_DTE DTE
					WHERE PAY_BEG_DTE <= (DATE_FORMAT('".$date."', '%Y-%m-01') - INTERVAL 1 MONTH)
						AND PAY_END_DTE >=(DATE_FORMAT('".$date."', '%Y-%m-01') - INTERVAL 1 MONTH)
				) CON ON RWD.PAY_CONFIG_NBR = CON.PAY_CONFIG_NBR
				WHERE RWD.OWN_CO_NBR != ".$CoNbrDef." 
				LIMIT 1";
	//echo "<pre>".$query."<br><br>";
	$result = mysql_query($query, $cloud);
	$rowDet = mysql_fetch_array($result);
	$avgAllOldMeterPrint  = $rowDet['M_AVG_ALL'];
	$avgAllOldLembarPrint = $rowDet['S_AVG_ALL'];
	
	$query = "SELECT 
					M_AVG AS M_COM_AVG,
					S_AVG AS S_COM_AVG
				FROM $CDW.PAY_RWD PAR 
				WHERE (PRSN_NBR = 0 OR PRSN_NBR IS NULL) 
					AND PAR.PAY_CONFIG_NBR = ".$PayConfigNbr." 
					AND PAR.OWN_CO_NBR != ".$CoNbrDef." ";
	//echo "<pre>".$query."<br><br>";
	$result = mysql_query($query, $cloud);
	$rowDet = mysql_fetch_array($result);
	$comAvgMeterPrint	= $rowDet['M_COM_AVG'];  
	$comAvgLembarPrint	= $rowDet['S_COM_AVG']; 

	$indexMeterPrint  = $avgMeterPrint / $comAvgMeterPrint;
	$indexLembarPrint = $avgLembarPrint / $comAvgLembarPrint;
	
	$Bon_meter_print  = FLOOR(($mQtyPrint - $RwdMBaseQ) / 100 * $indexMeterPrint * 1);
	$Bon_lembar_print = FLOOR(($sQtyPrint - $RwdSBaseQ) / 1500 * $indexLembarPrint * 1);
	
	if ($Bon_meter_print <=0){ $Bon_meter_print = 0;}
	if ($Bon_lembar_print <=0){ $Bon_lembar_print = 0;}
	
	$tot_bon_print = $Bon_meter_print + $Bon_lembar_print;

	if ($tot_bon_print <=0){ $tot_bon_print = 0;}
	
	$tot_bon_camp_print = $tot_bon_camp + $tot_bon_print;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />

<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
<script type="text/javascript" src="framework/functions/default.js"></script>

<style>
	table.tablesorter thead tr .headerTd{
		border-bottom:1px solid #cacbcf;
	}
</style>
</head>

<body>
	<div class="toolbar">
		<div class="combobox"></div>
		
		<div class="toolbar-text">
			<p class="toolbar-left">
				<select id="PAY_CONFIG_NBR" name="PAY_CONFIG_NBR" class="chosen-select" style="width:300px;" >
				<!-- <select id="PAY_CONFIG_NBR" name="PAY_CONFIG_NBR" class="chosen-select" style="width:300px;"> -->
					<?php 
						$query = "SELECT PAY_CONFIG_NBR, DATE_FORMAT(PAY_BEG_DTE, '%M %Y') AS MY_PAY_RWD FROM PAY.PAY_CONFIG_DTE";

						genCombo($query,"PAY_CONFIG_NBR","MY_PAY_RWD",$PayConfigNbr);
					?>
				</select>
				<span style="padding-left: 10px;font-weight: 700;">Bonus: <?php echo $tot_bon_camp_print." %"; ?></span>
			</p> 
		</div>
	</div>
	<div style="width:100%;padding-top: 10px;" id="mainResult">
	
	<?php		
		if($CoNbrDef == '1002') {
			echo "<h3 style='text-align:center;' >Champion Campus</h3>";
		} else {
			echo "<h3 style='text-align:center;' >Champion Printing</h3>";
		}
	?>
	
	<table id="mainTable" class="tablesorter">
		<thead>
			<tr>
				<th>Nomor</th>
				<th>Perusahaan</th>
				<th style="width:10%">Quantity Meter</th>
				<th style="width:10%">Quantity Lembar</th>
				<th style="width:10%">Tot HJ Meter</th>
				<th style="width:10%;">Tot HJ Lembar</th>				
			</tr>
		</thead>
		<tbody>
			<?php
			if($Typ=="ACCOUNT"){
				$query = "SELECT COM.NAME, COM.CO_NBR, RWD.*
							FROM $CMP.COMPANY COM 
							LEFT OUTER JOIN (
							SELECT 
								SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67') 
								 THEN QTY_DET 
								 ELSE 0 
								 END) AS QTY_METER,
								 SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085')
								 THEN QTY_DET
								 ELSE 0
								 END) AS QTY_SHEETS,
								 SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67')
								 THEN TOT_SUB
								 ELSE 0
								 END) AS REV_METER,
								 SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085')
								 THEN TOT_SUB
								 ELSE 0
								 END) AS REV_SHEETS,
								 BUY_CO_NBR
							FROM $CDW.PAY_RWD_DET RWD
							WHERE RWD.PAY_CONFIG_NBR =".$PayConfigNbr." AND RWD.PRSN_NBR = ".$PrsnNbr." AND RWD.OWN_CO_NBR = ".$CoNbrDef." 
							GROUP BY RWD.BUY_CO_NBR
						) RWD ON COM.CO_NBR = RWD.BUY_CO_NBR
						WHERE COM.ACCT_EXEC_NBR = ".$PrsnNbr."	
				";
			} else {
				$query = "SELECT COM.CO_NBR, COM.NAME, 
								 SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67') 
								 THEN QTY_DET 
								 ELSE 0 
								 END) AS QTY_METER,
								 SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085')
								 THEN QTY_DET
								 ELSE 0
								 END) AS QTY_SHEETS,
								 SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67')
								 THEN TOT_SUB
								 ELSE 0
								 END) AS REV_METER,
								 SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085')
								 THEN TOT_SUB
								 ELSE 0
								 END) AS REV_SHEETS,
								 BUY_CO_NBR
							FROM $CDW.PAY_RWD_DET RWD 
							LEFT OUTER JOIN $CMP.COMPANY COM ON COM.CO_NBR = RWD.BUY_CO_NBR
							WHERE RWD.PAY_CONFIG_NBR =".$PayConfigNbr." AND RWD.PRSN_NBR = ".$PrsnNbr." AND RWD.OWN_CO_NBR = ".$CoNbrDef." 
							GROUP BY RWD.BUY_CO_NBR";
			}
				//if($Typ=="ACCOUNT"){echo 'Account : <pre>'.$query.'</pre>';}
				//if($Typ!="ACCOUNT"){echo 'Bukan Account : <pre>'.$query.'</pre>';}
				$result = mysql_query($query, $cloud);

				while ($row= mysql_fetch_array($result)) {
					$OwnCoDesc = 'YES';
					echo "<tr $alt style='cursor:pointer;' onclick='detailData(".$row['BUY_CO_NBR'].",".$PayConfigNbr.",".$PrsnNbr.",\"".$Typ."\",\"".$OwnCoDesc."\")' >";
					echo "<td>".$row['CO_NBR']."</td>";
					echo "<td>".$row['NAME']."</td>";
					echo "<td style='text-align:right;'>".number_format($row['QTY_METER'],3,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['QTY_SHEETS'],3,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['REV_METER'],0,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['REV_SHEETS'],0,',','.')."</td>";
					echo "</tr>";

					$totQMeterCampus   += $row['QTY_METER'];
					$totQSheetCampus   += $row['QTY_SHEETS'];
					$totRevMeterCampus += $row['REV_METER'];
					$totRevSheetCampus += $row['REV_SHEETS'];
				}
				echo "<tr>";
				echo "<td colspan='2' style='background-color: #fff;border-top: 1px solid #cacbcf;'><b>Total</b></td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($totQMeterCampus,3,',','.')."</b></td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($totQSheetCampus,3,',','.')."</b></td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($totRevMeterCampus,0,',','.')."</b></td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($totRevSheetCampus,0,',','.')."</b></td>";
				echo "</tr>";
			?>
		
		</tbody>
	</table>

	<table style="width:500px;padding:0px;margin-bottom:10px; <?php if($Typ=="ACCOUNT"){ echo "display:none;";} ?>">
		<tr>
			<td style='padding:0px;width:380px'>
				<div class='total' style='width:80%'>
					<table>
						<tr class='total'>
							<td style='padding-left:7px;width:90%'>
								AVG all bulan lalu (Meter) 
							</td>
							<td style="text-align:right;">
								<input name="M_AVG_ALL_OLD" id="M_AVG_ALL_OLD" value="<?php echo number_format($avgAllOldMeterCamp,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								AVG all bulan lalu (Lembar)ac
							</td>
							<td style="text-align:right;">
								<input name="S_AVG_ALL_OLD" id="S_AVG_ALL_OLD" value="<?php echo number_format($avgAllOldLembarCamp,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								Harga jual rata-rata (Meter)
							</td>
							<td style="text-align:right;">
								<input name="M_AVG" id="M_AVG" value="<?php echo number_format($avgMeterCamp,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								Harga jual rata-rata (Lembar)
							</td>
							<td style="text-align:right;">
								<input name="S_AVG" id="S_AVG" value="<?php echo number_format($avgLembarCamp,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								Target Meter
							</td>
							<td style="text-align:right;">
								<input name="S_AVG" id="S_AVG" value="<?php echo number_format($RwdMBaseQ,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								Target Lembar
							</td>
							<td style="text-align:right;">
								<input name="S_AVG" id="S_AVG" value="<?php echo number_format($RwdSIncQ,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								Harga jual rata-rata Perusahaan (Meter)
							</td>
							<td style="text-align:right;">
								<input name="COM_M_AVG" id="COM_M_AVG" value="<?php echo number_format($comAvgMeterCamp,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								Harga jual rata-rata Perusahaan (Lembar)
							</td>
							<td style="text-align:right;">
								<input name="COM_S_AVG" id="COM_S_AVG" value="<?php echo number_format($comAvgLembarCamp,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
					</table>
				</div>
			</td>
		</td>
	</table>
	
	<br>
	<?php		
		if($CoNbrDef != '1002') {
			echo "<h3 style='text-align:center;' >Champion Campus</h3>";
		} else {
			echo "<h3 style='text-align:center;' >Champion Printing</h3>";
		}
	?>
	<table id="mainTable2" class="tablesorter">
		<thead>
			<tr>
				<th>Nomor</th>
				<th>Perusahaan</th>
				<th style="width:10%">Quantity Meter</th>
				<th style="width:10%">Quantity Lembar</th>
				<th style="width:10%">Tot HJ Meter</th>
				<th style="width:10%;">Tot HJ Lembar</th>				
			</tr>
		</thead>
		<tbody>
			<?php
			if($Typ=="ACCOUNT"){
				$query = "SELECT COM.NAME, COM.CO_NBR, RWD.*
							FROM $CMP.COMPANY COM 
							LEFT OUTER JOIN (
							SELECT 
								SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67') 
								 THEN QTY_DET 
								 ELSE 0 
								 END) AS QTY_METER,
								 SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085')
								 THEN QTY_DET
								 ELSE 0
								 END) AS QTY_SHEETS,
								 SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67')
								 THEN TOT_SUB
								 ELSE 0
								 END) AS REV_METER,
								 SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085')
								 THEN TOT_SUB
								 ELSE 0
								 END) AS REV_SHEETS,
								 BUY_CO_NBR
							FROM $CDW.PAY_RWD_DET RWD
							WHERE RWD.PAY_CONFIG_NBR =".$PayConfigNbr." AND RWD.PRSN_NBR = ".$PrsnNbr." AND RWD.OWN_CO_NBR != ".$CoNbrDef." 
							GROUP BY RWD.BUY_CO_NBR
						) RWD ON COM.CO_NBR = RWD.BUY_CO_NBR
						WHERE COM.ACCT_EXEC_NBR = ".$PrsnNbr."	
				";
			} else {
				$query = "SELECT COM.CO_NBR, COM.NAME, 
								 SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67') 
								 THEN QTY_DET 
								 ELSE 0 
								 END) AS QTY_METER,
								 SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085')
								 THEN QTY_DET
								 ELSE 0
								 END) AS QTY_SHEETS,
								 SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67')
								 THEN TOT_SUB
								 ELSE 0
								 END) AS REV_METER,
								 SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085')
								 THEN TOT_SUB
								 ELSE 0
								 END) AS REV_SHEETS,
								 BUY_CO_NBR
							FROM $CDW.PAY_RWD_DET RWD 
							LEFT OUTER JOIN $CMP.COMPANY COM ON COM.CO_NBR = RWD.BUY_CO_NBR
							WHERE RWD.PAY_CONFIG_NBR =".$PayConfigNbr." AND RWD.PRSN_NBR = ".$PrsnNbr." AND RWD.OWN_CO_NBR != ".$CoNbrDef." 
							GROUP BY RWD.BUY_CO_NBR";
			}
				//if($Typ=="ACCOUNT"){echo 'Account : <pre>'.$query.'</pre>';}
				//if($Typ!="ACCOUNT"){echo 'Bukan Account : <pre>'.$query.'</pre>';}
				$result = mysql_query($query, $cloud);

				while ($row= mysql_fetch_array($result)) {
					$OwnCoDesc = 'NO';
					echo "<tr $alt style='cursor:pointer;' onclick='detailData(".$row['BUY_CO_NBR'].",".$PayConfigNbr.",".$PrsnNbr.",\"".$Typ."\",\"".$OwnCoDesc."\")' >";
					echo "<td>".$row['CO_NBR']."</td>";
					echo "<td>".$row['NAME']."</td>";
					echo "<td style='text-align:right;'>".number_format($row['QTY_METER'],3,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['QTY_SHEETS'],3,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['REV_METER'],0,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['REV_SHEETS'],0,',','.')."</td>";
					echo "</tr>";

					$totQMeterPrinting   += $row['QTY_METER'];
					$totQSheetPrinting   += $row['QTY_SHEETS'];
					$totRevMeterPrinting += $row['REV_METER'];
					$totRevSheetPrinting += $row['REV_SHEETS'];
				}
				echo "<tr>";
				echo "<td colspan='2' style='background-color: #fff;border-top: 1px solid #cacbcf;'><b>Total</b></td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($totQMeterPrinting,3,',','.')."</b></td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($totQSheetPrinting,3,',','.')."</b></td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($totRevMeterPrinting,0,',','.')."</b></td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($totRevSheetPrinting,0,',','.')."</b></td>";
				echo "</tr>";
			?>
		
		</tbody>
	</table>
	<table style="width:500px;padding:0px;margin-bottom:10px; <?php if($Typ=="ACCOUNT"){ echo "display:none;";} ?>">
		<tr>
			<td style='padding:0px;width:380px'>
				<div class='total' style='width:80%'>
					<table>
						<tr class='total'>
							<td style='padding-left:7px;width:90%'>
								AVG all bulan lalu (Meter) 
							</td>
							<td style="text-align:right;">
								<input name="M_AVG_ALL_OLD" id="M_AVG_ALL_OLD" value="<?php echo number_format($avgAllOldMeterPrint,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								AVG all bulan lalu (Lembar)ac
							</td>
							<td style="text-align:right;">
								<input name="S_AVG_ALL_OLD" id="S_AVG_ALL_OLD" value="<?php echo number_format($avgAllOldLembarPrint,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								Harga jual rata-rata (Meter)
							</td>
							<td style="text-align:right;">
								<input name="M_AVG" id="M_AVG" value="<?php echo number_format($avgMeterPrint,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								Harga jual rata-rata (Lembar)
							</td>
							<td style="text-align:right;">
								<input name="S_AVG" id="S_AVG" value="<?php echo number_format($avgLembarPrint,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								Target Meter
							</td>
							<td style="text-align:right;">
								<input name="S_AVG" id="S_AVG" value="<?php echo number_format($RwdMBaseQ,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								Target Lembar
							</td>
							<td style="text-align:right;">
								<input name="S_AVG" id="S_AVG" value="<?php echo number_format($RwdSIncQ,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								Harga jual rata-rata Perusahaan (Meter)
							</td>
							<td style="text-align:right;">
								<input name="COM_M_AVG" id="COM_M_AVG" value="<?php echo number_format($comAvgMeterPrint,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
						<tr class="total">
							<td style='padding-left:7px;'>
								Harga jual rata-rata Perusahaan (Lembar)
							</td>
							<td style="text-align:right;">
								<input name="COM_S_AVG" id="COM_S_AVG" value="<?php echo number_format($comAvgLembarPrint,0,',','.'); ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
						</tr>
					</table>
				</div>
			</td>
		</td>
	</table>
</div>

<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
			$("#mainTable2").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>

<script type="text/javascript">
	$(document).ready(function(){
		$("#PAY_CONFIG_NBR").on("change",function(){
			var PayConfigNbr = $("#PAY_CONFIG_NBR").val();
			var url = "marketing-performance-table.php?TYP=<?php echo $Typ; ?>&PRSN_NBR=<?php echo $PrsnNbr; ?>&PAY_CONFIG_NBR="+PayConfigNbr;
			$("#tableDetail").load(url);
		});
	});
</script>
</body>
</html>