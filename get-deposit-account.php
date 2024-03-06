<?php

//echo "<pre>";

require_once "framework/database/connect.php";

function pembulatan($rupiah)
{

 $satuan = 500000;
 $string = substr($rupiah, -6);
 if($string < $satuan) {
	$akhir = $rupiah - $string;
 }
 else {
	$akhir = $rupiah - $string + $satuan;
	}

 return $akhir;
}


$POS_ID			= $_GET['POS_ID'];

if(!empty($_GET['CO_NBR'])) {
	$CoNbrDef		= $_GET['CO_NBR'];
}



$date 		= date('Y-m-d');

$query_begin	= "SELECT BEG_ACCTG FROM NST.PARAM_LOC";
$result_begin	= mysql_query($query_begin);
$row_begin 		= mysql_fetch_array($result_begin);
	
$beginDate		= $row_begin['BEG_ACCTG'];

//print_r($row_begin);

$query_dep		= "SELECT BEG_DEP FROM NST.PARAM_LOC";
$result_dep		= mysql_query($query_dep);
$row_dep		= mysql_fetch_array($result_dep);
	
//$beginDeposit	= $row_dep['BEG_DEP'];

$beginDeposit	= '2019-01-01';

//uang yang harus ada di kasir sebelum setoran

$query_min_csh	= "SELECT MIN_CSH_PT, MIN_CSH_CV, MIN_CSH_PR, MIN_CSH_AD FROM NST.PARAM_LOC";
$result_min_csh	= mysql_query($query_min_csh);
$row_min_csh	= mysql_fetch_array($result_min_csh);

$MinCashPT		= $row_min_csh['MIN_CSH_PT'];
$MinCashCV		= $row_min_csh['MIN_CSH_CV'];
$MinCashPR		= $row_min_csh['MIN_CSH_PR'];
$MinCashAD		= $row_min_csh['MIN_CSH_AD'];

$query_begin	= "SELECT BEG_ACCTG FROM NST.PARAM_LOC";
$result_begin	= mysql_query($query_begin);

$row_begin		= mysql_fetch_array($result_begin);

//$_GET['BEG_DT']		= $row_begin['BEG_ACCTG'];

$_GET['BEG_DT']		= '2019-01-01';

$ArrayActg	= array("1", "2", "3");

foreach($ArrayActg as $Actg) {

$_GET['ACTG'] 		= $Actg;
$_GET['PYMT_TYP']	= 'CSH';
$_GET['DEPOSIT_F']	= 1;

	try {
		
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/prn-dig-report-pay.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
		
	
	//echo "<pre>"; print_r($results);
	
	if($Actg == 1) { 	$RevenuePT	= $results->total->TOT_AMT; } 
	if($Actg == 2) { 	$RevenueCV	= $results->total->TOT_AMT; }
	if($Actg == 3) { 	$RevenuePR	= $results->total->TOT_AMT; }
	

}

//echo "REVENUE PT : ".$RevenuePT;



//omset hari ini
$query_amt		= "SELECT DATE(HED.ORD_TS) AS ORD_DTE,
			DATE(PAY.MAX_CRT_TS) AS CSH_DTE,
			YEAR(PAY.MAX_CRT_TS) AS CSH_YEAR,
			MONTH(PAY.MAX_CRT_TS) AS CSH_MONTH,
			DAY(PAY.MAX_CRT_TS) AS CSH_DAY,
			MONTHNAME(PAY.MAX_CRT_TS) AS CSH_MONTHNAME,
			HED.ORD_NBR,
			COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
			COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
			COALESCE(SUM(PAY.TND_AMT), 0) AS TND_AMT,
			(CASE WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
			WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
			ELSE 'Tunai' END 
			) AS BUY_NAME,
			PAY.MAX_CRT_TS,
			PYMT.PYMT_TYP,
			SUM(HED.TOT_REM) AS TOT_REM,
			
			COALESCE(SUM(CASE WHEN CSH.ACTG_TYP = 1 THEN CSH.TND_AMT ELSE 0 END),0) AS OMS_PT,
			
			COALESCE(SUM(CASE WHEN CSH.ACTG_TYP = 2 THEN CSH.TND_AMT ELSE 0 END),0) AS OMS_CV,
			
			COALESCE(SUM(CASE WHEN CSH.ACTG_TYP IN (3,0) THEN CSH.TND_AMT ELSE 0 END),0) AS OMS_PR
	FROM 
				( SELECT 
							CSH.TRSC_NBR,
							SUM(CSH.TND_AMT) AS TND_AMT,
							CSH.PYMT_TYP,
							CSH.RTL_BRC,
							MAX(CSH.CRT_TS) AS CRT_TS,
							CSH.BUY_CO_NBR,
							CSH.TAX_APL_ID,
							CSH.TAX_F,
							CSH.TAX_RPT,
							CSH.ACTG_TYP
						FROM ( SELECT 
										CSH.TRSC_NBR,
										PYMT.PYMT_TYP,
										CSH.RTL_BRC,
										CSH.CRT_TS,
										HED.BUY_CO_NBR,
										HED.TAX_APL_ID,
										(CASE WHEN HED.BUY_CO_NBR IS NULL THEN 2 ELSE COM.TAX_F END) AS TAX_F,
										PYMT.TAX_RPT,
										COM.ACTG_TYP,
										SUM(CASE WHEN PYMT.PYMT_TYP = 'CSH' THEN CSH.TND_AMT ELSE 0 END) AS TND_AMT
										#SUM(CSH.TND_AMT) AS TND_AMT
									FROM 
											(	SELECT
														TRSC_NBR,
														CSH_FLO_TYP,
														PYMT_TYP,
														SUM(TND_AMT) AS TND_AMT,
														RTL_BRC,
														CRT_TS
													FROM RTL.CSH_REG 
													WHERE ACT_F = 0
														AND CSH_FLO_TYP = 'FL'
														AND DATE(CRT_TS) >= '".$beginDeposit."'
													GROUP BY TRSC_NBR, RTL_BRC
													) CSH
												LEFT JOIN (
													SELECT
														REG.TRSC_NBR,
														REG.CSH_FLO_TYP,
														REG.PYMT_TYP,
														SUM(REG.TND_AMT) AS TND_AMT,
														PYMT.PYMT_TYP_ORD,
														(CASE WHEN PYMT.PYMT_TYP IN ('DEB', 'CRT') THEN 1 ELSE 0 END) AS TAX_RPT
													FROM RTL.CSH_REG REG
													LEFT JOIN RTL.PYMT_TYP AS PYMT
														ON REG.PYMT_TYP = PYMT.PYMT_TYP
													WHERE REG.ACT_F = 0
														AND REG.CSH_FLO_TYP = 'PA'
														AND DATE(REG.CRT_TS) >= '".$beginDeposit."'
													GROUP BY REG.TRSC_NBR
													ORDER BY PYMT.PYMT_TYP_ORD
												) PYMT ON CSH.TRSC_NBR = PYMT.TRSC_NBR
										LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED
											ON CSH.RTL_BRC = HED.ORD_NBR
										LEFT JOIN CMP.COMPANY COM
											ON HED.BUY_CO_NBR = COM.CO_NBR
										GROUP BY CSH.TRSC_NBR, CSH.RTL_BRC
										ORDER BY PYMT.PYMT_TYP_ORD ASC
						) CSH
						GROUP BY CSH.RTL_BRC
				) CSH
						LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED
							ON CSH.RTL_BRC = HED.ORD_NBR
						LEFT JOIN CMP.COMPANY COM
							ON HED.BUY_CO_NBR = COM.CO_NBR
						LEFT JOIN CMP.PEOPLE PPL
							ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
						LEFT JOIN (
							SELECT 
								PYMT.PYMT_NBR,
								PYMT.ORD_NBR,
								SUM(PYMT.TND_AMT) AS TND_AMT,
								PYMT.CRT_TS,
								MAX(PYMT.CRT_TS) AS MAX_CRT_TS,
								COUNT(PYMT_NBR) AS CNT
							FROM CMP.PRN_DIG_ORD_PYMT PYMT
							JOIN CMP.PRN_DIG_ORD_HEAD HED
								ON PYMT.ORD_NBR = HED.ORD_NBR
							WHERE PYMT.DEL_NBR = 0
								AND HED.DEL_NBR = 0
								AND DATE(PYMT.CRT_TS) >= '".$beginDeposit."'
								AND DATE(HED.ORD_TS) >= '".$beginDeposit."'
							GROUP BY PYMT.ORD_NBR
						) PAY ON PAY.ORD_NBR = HED.ORD_NBR
						LEFT JOIN (
							SELECT
								TRSC_NBR,
								CSH_FLO_TYP,
								PYMT_TYP,
								TND_AMT
							FROM RTL.CSH_REG 
							WHERE ACT_F = 0
								AND CSH_FLO_TYP = 'PA'
								AND DATE(CRT_TS) >= '".$beginDeposit."'
							GROUP BY TRSC_NBR
						) PYMT ON CSH.TRSC_NBR = PYMT.TRSC_NBR
					WHERE DATE(CSH.CRT_TS) >= '".$beginDate."'
						AND DATE(CSH.CRT_TS) = CURRENT_DATE
						AND HED.DEL_NBR = 0 
						AND DATE(HED.ORD_TS) >= '".$beginDate."'
						AND HED.PRN_CO_NBR = ".$CoNbrDef."
						#AND PAY.TND_AMT >= HED.TOT_AMT ";

//echo "<pre>".$query_amt;

$result_amt 	= mysql_query($query_amt);
$row_amt		= mysql_fetch_array($result_amt);

//print_r($row_amt);

$OmsetTodayPT	= $row_amt['OMS_PT'];
$OmsetTodayCV	= $row_amt['OMS_CV'];
$OmsetTodayPR	= $row_amt['OMS_PR'];

$query_add	= "SELECT 
			COALESCE(SUM(CSH.TND_AMT),0) AS OMS_AD
	FROM 
				( SELECT 
							CSH.TRSC_NBR,
							SUM(CSH.TND_AMT) AS TND_AMT,
							CSH.PYMT_TYP,
							CSH.RTL_BRC,
							MAX(CSH.CRT_TS) AS CRT_TS,
							CSH.BUY_CO_NBR,
							CSH.TAX_APL_ID,
							CSH.TAX_F,
							CSH.TAX_RPT
						FROM ( SELECT 
										CSH.TRSC_NBR,
										PYMT.PYMT_TYP,
										CSH.RTL_BRC,
										CSH.CRT_TS,
										HED.BUY_CO_NBR,
										HED.TAX_APL_ID,
										(CASE WHEN HED.BUY_CO_NBR IS NULL THEN 2 ELSE COM.TAX_F END) AS TAX_F,
										PYMT.TAX_RPT,
										SUM(CASE WHEN PYMT.PYMT_TYP = 'CSH' THEN CSH.TND_AMT ELSE 0 END) AS TND_AMT
										#SUM(CSH.TND_AMT) AS TND_AMT
									FROM 
											(	SELECT
														TRSC_NBR,
														CSH_FLO_TYP,
														PYMT_TYP,
														SUM(TND_AMT) AS TND_AMT,
														RTL_BRC,
														CRT_TS
													FROM RTL.CSH_REG 
													WHERE ACT_F = 0
														AND CSH_FLO_TYP = 'FL'
														AND DATE(CRT_TS) >= '".$beginDeposit."'
													GROUP BY TRSC_NBR, RTL_BRC
													) CSH
												LEFT JOIN (
													SELECT
														REG.TRSC_NBR,
														REG.CSH_FLO_TYP,
														REG.PYMT_TYP,
														SUM(REG.TND_AMT) AS TND_AMT,
														PYMT.PYMT_TYP_ORD,
														(CASE WHEN PYMT.PYMT_TYP IN ('DEB', 'CRT') THEN 1 ELSE 0 END) AS TAX_RPT
													FROM RTL.CSH_REG REG
													LEFT JOIN RTL.PYMT_TYP AS PYMT
														ON REG.PYMT_TYP = PYMT.PYMT_TYP
													WHERE REG.ACT_F = 0
														AND REG.CSH_FLO_TYP = 'PA'
														AND DATE(REG.CRT_TS) >= '".$beginDeposit."'
													GROUP BY REG.TRSC_NBR
													ORDER BY PYMT.PYMT_TYP_ORD
												) PYMT ON CSH.TRSC_NBR = PYMT.TRSC_NBR
										LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED
											ON CSH.RTL_BRC = HED.ORD_NBR
										LEFT JOIN CMP.COMPANY COM
											ON HED.BUY_CO_NBR = COM.CO_NBR
										GROUP BY CSH.TRSC_NBR, CSH.RTL_BRC
										ORDER BY PYMT.PYMT_TYP_ORD ASC
						) CSH
						GROUP BY CSH.RTL_BRC
				) CSH
						LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED
							ON CSH.RTL_BRC = HED.ORD_NBR
						LEFT JOIN CMP.COMPANY COM
							ON HED.BUY_CO_NBR = COM.CO_NBR
						LEFT JOIN CMP.PEOPLE PPL
							ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
						LEFT JOIN (
							SELECT 
								PYMT.PYMT_NBR,
								PYMT.ORD_NBR,
								SUM(PYMT.TND_AMT) AS TND_AMT,
								PYMT.CRT_TS,
								MAX(PYMT.CRT_TS) AS MAX_CRT_TS,
								COUNT(PYMT_NBR) AS CNT
							FROM CMP.PRN_DIG_ORD_PYMT PYMT
							JOIN CMP.PRN_DIG_ORD_HEAD HED
								ON PYMT.ORD_NBR = HED.ORD_NBR
							WHERE PYMT.DEL_NBR = 0
								AND HED.DEL_NBR = 0
								AND DATE(PYMT.CRT_TS) >= '".$beginDeposit."'
							GROUP BY PYMT.ORD_NBR
						) PAY ON PAY.ORD_NBR = HED.ORD_NBR
						LEFT JOIN (
							SELECT
								TRSC_NBR,
								CSH_FLO_TYP,
								PYMT_TYP,
								TND_AMT
							FROM RTL.CSH_REG 
							WHERE ACT_F = 0
								AND CSH_FLO_TYP = 'PA'
								AND DATE(CRT_TS) >= '".$beginDeposit."'
							GROUP BY TRSC_NBR
						) PYMT ON CSH.TRSC_NBR = PYMT.TRSC_NBR
					WHERE DATE(CSH.CRT_TS) <= CURRENT_DATE
						AND DATE(CSH.CRT_TS) >= '".$beginDeposit."'
						AND HED.DEL_NBR = 0 
						AND DATE(HED.ORD_TS) < '".$beginDate."'
						AND (HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY) OR HED.BUY_CO_NBR IS NULL)
						AND HED.PRN_CO_NBR = ".$CoNbrDef." ";

//echo "<pre> QUERY ADD : ".$query_add;

$result_add	= mysql_query($query_add);
$row_add	= mysql_fetch_array($result_add);

$OmsetAD	= $row_add['OMS_AD'];

//print_r($row_add);

$query_exp	= "SELECT 
					COALESCE(SUM(CSH.TND_AMT),0) AS EXP_AMT
				FROM RTL.CSH_REG CSH
				WHERE DATE(CSH.CRT_TS) >= '".$beginDeposit."'
					AND DATE(CSH.CRT_TS) <= CURRENT_DATE
					AND CSH.CSH_FLO_TYP = 'EX'
					AND CSH.ACT_F = 0";
					
$result_exp	= mysql_query($query_exp);
$row_exp	= mysql_fetch_array($result_exp);

$ExpenseAmount	= $row_exp['EXP_AMT'];


$query_ivc	= "SELECT 
					SUM(CSH.TND_AMT) AS TND_AMT,
					SUM(CASE WHEN CSH.ACTG_TYP = 1 THEN CSH.TND_AMT ELSE 0 END) AS IVC_AMT_PT,
					SUM(CASE WHEN CSH.ACTG_TYP = 2 THEN CSH.TND_AMT ELSE 0 END) AS IVC_AMT_CV,
					SUM(CASE WHEN CSH.ACTG_TYP = 3 THEN CSH.TND_AMT ELSE 0 END) AS IVC_AMT_PR
				FROM (
					SELECT 
						CSH.RCV_CO_NBR,
						SUM(CASE WHEN PYMT.PYMT_TYP = 'CSH' THEN CSH.TND_AMT ELSE 0 END) AS TND_AMT,
						CSH.ACTG_TYP
						#SUM(CSH.TND_AMT) AS TND_AMT
					FROM 
							( SELECT 
									CSH.TRSC_NBR,
									COALESCE(SUM(CSH.TND_AMT),0) AS TND_AMT,
									HED.RCV_CO_NBR,
									HED.ACTG_TYP
								FROM RTL.CSH_REG CSH
								LEFT JOIN RTL.RTL_STK_HEAD HED 
									ON CSH.RTL_BRC = HED.ORD_NBR
								WHERE DATE(CSH.CRT_TS) >= '".$beginDeposit."'
									AND DATE(CSH.CRT_TS) <= CURRENT_DATE
									AND DATE(HED.ORD_DTE) >= '".$beginDeposit."'
									AND DATE(HED.ORD_DTE) <= CURRENT_DATE
									AND CSH.CSH_FLO_TYP = 'IV'
									AND CSH.ACT_F = 0
								GROUP BY CSH.TRSC_NBR, CSH.RTL_BRC, HED.ACTG_TYP
							) CSH
							LEFT JOIN (
								SELECT 
									REG.TRSC_NBR,
									REG.PYMT_TYP
								FROM RTL.CSH_REG REG
								WHERE REG.CSH_FLO_TYP = 'PA'
									AND REG.ACT_F = 0
									AND DATE(REG.CRT_TS) >= '".$beginDeposit."'
									AND DATE(REG.CRT_TS) <= CURRENT_DATE
								GROUP BY REG.TRSC_NBR
							) PYMT
								ON CSH.TRSC_NBR = PYMT.TRSC_NBR
						GROUP BY CSH.RCV_CO_NBR, CSH.ACTG_TYP
				) CSH";
				
//echo "<pre>".$query_ivc;

$result_ivc	= mysql_query($query_ivc);
$row_ivc	= mysql_fetch_array($result_ivc);


$InvoiceAmountPT	= $row_ivc['IVC_AMT_PT'];
$InvoiceAmountCV	= $row_ivc['IVC_AMT_CV'];
$InvoiceAmountPR	= $row_ivc['IVC_AMT_PR'];

$query_retail	= "					SELECT 
						CSH.TRSC_NBR,
						SUM(CASE WHEN PYMT.PYMT_TYP = 'CSH' THEN CSH.TND_AMT ELSE 0 END) AS TND_AMT,
						SUM(CSH.TND_AMT) AS TND_AMT_TOTAL,
						PYMT.PYMT_TYP,
						DATE(CSH.CRT_TS) AS CSH_DTE
					FROM 
							( SELECT 
									CSH.TRSC_NBR,
									COALESCE(SUM(CSH.TND_AMT),0) AS TND_AMT,
									CRT_TS
								FROM RTL.CSH_REG CSH
								WHERE DATE(CSH.CRT_TS) >= '".$beginDeposit."'
									AND DATE(CSH.CRT_TS) <= CURRENT_DATE
									AND CSH.CSH_FLO_TYP = 'RT'
									AND CSH.ACT_F = 0
								GROUP BY CSH.TRSC_NBR
							) CSH
							LEFT JOIN (
								SELECT 
									REG.TRSC_NBR,
									REG.PYMT_TYP
								FROM RTL.CSH_REG REG
								WHERE REG.CSH_FLO_TYP = 'PA'
									AND REG.ACT_F = 0
									AND DATE(REG.CRT_TS) >= '".$beginDeposit."'
									AND DATE(REG.CRT_TS) <= CURRENT_DATE
								GROUP BY REG.TRSC_NBR
							) PYMT
								ON CSH.TRSC_NBR = PYMT.TRSC_NBR";

								

$result_retail	= mysql_query($query_retail);
$row_retail		= mysql_fetch_array($result_retail);

$RevenueRetail	= $row_retail['TND_AMT'];



$query_sales	= "SELECT 
					SUM(CSH.TND_AMT) AS TND_AMT
				FROM (
					SELECT 
						CSH.RCV_CO_NBR,
						SUM(CASE WHEN PYMT.PYMT_TYP = 'CSH' THEN CSH.TND_AMT ELSE 0 END) AS TND_AMT,
						CSH.ACTG_TYP
						#SUM(CSH.TND_AMT) AS TND_AMT
					FROM 
							( SELECT 
									CSH.TRSC_NBR,
									COALESCE(SUM(CSH.TND_AMT),0) AS TND_AMT,
									HED.RCV_CO_NBR,
									HED.ACTG_TYP
								FROM RTL.CSH_REG CSH
								LEFT JOIN RTL.RTL_STK_HEAD HED 
									ON CSH.RTL_BRC = HED.ORD_NBR
								WHERE DATE(CSH.CRT_TS) >= '".$beginDeposit."'
									AND DATE(CSH.CRT_TS) <= CURRENT_DATE
									AND DATE(HED.ORD_DTE) >= '".$beginDeposit."'
									AND DATE(HED.ORD_DTE) <= CURRENT_DATE
									AND CSH.CSH_FLO_TYP = 'GP'
									AND CSH.ACT_F = 0
								GROUP BY CSH.TRSC_NBR, CSH.RTL_BRC, HED.ACTG_TYP
							) CSH
							LEFT JOIN (
								SELECT 
									REG.TRSC_NBR,
									REG.PYMT_TYP
								FROM RTL.CSH_REG REG
								WHERE REG.CSH_FLO_TYP = 'PA'
									AND REG.ACT_F = 0
									AND DATE(REG.CRT_TS) >= '".$beginDeposit."'
									AND DATE(REG.CRT_TS) <= CURRENT_DATE
								GROUP BY REG.TRSC_NBR
							) PYMT
								ON CSH.TRSC_NBR = PYMT.TRSC_NBR
						GROUP BY CSH.RCV_CO_NBR, CSH.ACTG_TYP
				) CSH";
				
//echo "<pre>".$query_ivc;

$result_sales	= mysql_query($query_sales);
$row_sales		= mysql_fetch_array($result_sales);


$SalesAmount	= $row_sales['TND_AMT'];


$query_dep	= "SELECT 
					COALESCE(SUM(CASE WHEN ACCT = 'PT' THEN CSH.CSH_AMT ELSE 0 END),0) AS DEP_PT,
					COALESCE(SUM(CASE WHEN ACCT = 'CV' THEN CSH.CSH_AMT ELSE 0 END),0) AS DEP_CV,
					COALESCE(SUM(CASE WHEN ACCT = 'PR' THEN CSH.CSH_AMT ELSE 0 END),0) AS DEP_PR,
					COALESCE(SUM(CASE WHEN ACCT = 'AD' THEN CSH.CSH_AMT ELSE 0 END),0) AS DEP_AD
				FROM RTL.CSH_DAY CSH
				WHERE CSH.CSH_DAY_DTE >= '".$beginDeposit."'
					AND CSH.CSH_DAY_DTE <= CURRENT_DATE
					AND CSH.DEL_NBR = 0";

				
$result_dep	= mysql_query($query_dep);
$row_dep	= mysql_fetch_array($result_dep);

$depPT		= $row_dep['DEP_PT'];
$depCV		= $row_dep['DEP_CV'];
$depPR		= $row_dep['DEP_PR'];
$depAD		= $row_dep['DEP_AD'];

$satuan_csh 	= 1000000;

	if ((($RevenuePT + $OmsetTodayPT) - $depPT - $InvoiceAmountPT) >= $MinCashPT)	{ $DepositPT	= (($RevenuePT + $OmsetTodayPT) - $depPT - $InvoiceAmountPT) - ($MinCashPT); }	
		else { $DepositPT = 0; }

	if ((($RevenueCV + $OmsetTodayCV + $RevenueRetail + $SalesAmount) - $depCV - $ExpenseAmount - $InvoiceAmountCV) >= $MinCashCV)	{ $DepositCV	= (($RevenueCV + $OmsetTodayCV + $RevenueRetail + $SalesAmount) - $depCV - $ExpenseAmount - $InvoiceAmountCV) - ($MinCashCV); }	
		else { $DepositCV = 0; }
	
	if ((($RevenuePR + $OmsetTodayPR) - $depPR - $InvoiceAmountPR) >= $MinCashPR)	{ $DepositPR	= (($RevenuePR + $OmsetTodayPR) - $depPR - $InvoiceAmountPR) - ($MinCashPR); }	
		else { $DepositPR = 0; }
	
	if (($OmsetAD - $depAD) >= $MinCashAD)	{ $DepositAD	= ($OmsetAD - $depAD) - ($MinCashAD); }	
		else { $DepositAD = 0; }
		

$data		= array();

$data['REV_PT']		= $RevenuePT;
$data['REV_CV']		= $RevenueCV;
$data['REV_PR']		= $RevenuePR;

$data['OMSET_TODAY_PT']	= $OmsetTodayPT;
$data['OMSET_TODAY_CV']	= $OmsetTodayCV;
$data['OMSET_TODAY_PR']	= $OmsetTodayPR;
$data['OMSET_AD']	= $OmsetAD;

$data['RETAIL']		= $RevenueRetail;

$data['DEP_PT']		= $depPT;
$data['DEP_CV']		= $depCV;
$data['DEP_PR']		= $depPR;
$data['DEP_AD']		= $depAD;

$data['EXP_AMT']	= $ExpenseAmount;

$data['SLS_AMT']	= $SalesAmount;

$data['IVC_AMT_PT']	= $InvoiceAmountPT;
$data['IVC_AMT_CV']	= $InvoiceAmountCV;
$data['IVC_AMT_PR']	= $InvoiceAmountPR;

$data['PT']	= pembulatan($DepositPT);
$data['CV']	= pembulatan($DepositCV);
$data['PR']	= pembulatan($DepositPR);
//$data['AD']	= pembulatan($DepositAD);
$data['AD']	= 0;

//echo "<pre>"; print_r($data);
echo json_encode($data);


	
	
?>