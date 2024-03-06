<?php
require_once "/../../framework/database/connect.php";
require_once "/../../framework/functions/default.php";

$bookNumber		= $_GET['BK_NBR'];
$Accounting		= $_GET['ACTG'];

$query_bk	= "SELECT BK_NBR, 
				(BEG_DTE - INTERVAL 1 DAY) AS BEGIN, 
				BEG_DTE AS BEG_DT,
				END_DTE AS END_DT,
				MONTH(BEG_DTE) AS BK_MONTH,
				YEAR(BEG_DTE) AS BK_YEAR,
				MONTH(BEG_DTE - INTERVAL 1 MONTH) AS BK_MONTH_BEG,
				YEAR(BEG_DTE - INTERVAL 1 MONTH) AS BK_YEAR_BEG
			FROM RTL.ACCTG_BK WHERE BK_NBR = ".$bookNumber." ";
$result_bk 	= mysql_query($query_bk);
$row_bk		= mysql_fetch_array($result_bk);

$_GET['CAT_TYP_NBR']	= 1;
$_GET['END_DT']			= $row_bk['BEGIN'];

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../store-inventory-accounting.php";

	$resultsBeginMain = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

unset($_GET['CAT_TYP_NBR']);
unset($_GET['END_DT']);

$_GET['CAT_TYP_NBR']	= 2;
$_GET['END_DT']			= $row_bk['BEGIN'];

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../store-inventory-accounting.php";

	$resultsBeginSub = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

unset($_GET['CAT_TYP_NBR']);
unset($_GET['END_DT']);

$_GET['CAT_TYP_NBR']	= 1;
$_GET['END_DT']			= $row_bk['END_DT'];

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../store-inventory-accounting.php";

	$resultsEndMain = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}



unset($_GET['CAT_TYP_NBR']);
unset($_GET['END_DT']);

$_GET['CAT_TYP_NBR']	= 2;
$_GET['END_DT']			= $row_bk['END_DT'];

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../store-inventory-accounting.php";

	$resultsEndSub = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

unset($_GET['CAT_TYP_NBR']);
unset($_GET['END_DT']);

//Akun Pembelian dan Retur Pembelian


$queryAkun	= "SELECT GL_TYP_NBR, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR IN (6,7,13,14,20,22)";
$resultAkun	= mysql_query($queryAkun);
while($rowAkun	= mysql_fetch_array($resultAkun)) {
	
	$GlTypeNumber	= $rowAkun['GL_TYP_NBR'];
	
	if($GlTypeNumber == 6) { $CdSubNbrRCMain	= $rowAkun['CD_SUB_NBR']; }
	if($GlTypeNumber == 7) { $CdSubNbrRTMain	= $rowAkun['CD_SUB_NBR']; }
	if($GlTypeNumber == 13) { $CdSubNbrRCSub	= $rowAkun['CD_SUB_NBR']; }
	if($GlTypeNumber == 14) { $CdSubNbrRTSub	= $rowAkun['CD_SUB_NBR']; }
	if($GlTypeNumber == 20) { $CdSubPPNmain		= $rowAkun['CD_SUB_NBR']; }
	if($GlTypeNumber == 22) { $CdSubPPNsub		= $rowAkun['CD_SUB_NBR']; }
	
}

//echo $CdSubNbrRCMain."-".$CdSubNbrRTMain;


$CdSubNbrBTKL	= $rowPay['CD_SUB_NBR'];


//Biaya Tenaga Kerja Langsung (BTKL atau payroll HPP)

$queryPay	= "SELECT CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = 5";
$resultPay	= mysql_query($queryPay);
$rowPay		= mysql_fetch_array($resultPay);

$CdSubNbrBTKL	= $rowPay['CD_SUB_NBR'];

//Biaya Click Charge

$queryClick		= "SELECT CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = 8";
$resultClick	= mysql_query($queryClick);
$rowClick		= mysql_fetch_array($resultClick);

$CdSubNbrClick = $rowClick['CD_SUB_NBR'];

//biaya overhead 


unset($_GET['CAT_TYP_NBR']);

$_GET['GROUP']			= 'CAT_SUB_NBR';
$_GET['MONTHS']			= $row_bk['BK_MONTH'];
$_GET['YEARS']			= $row_bk['BK_YEAR'];
$_GET['CAT_TYP_NBR']	= 3;
$_GET['TYP']			= 'ACTG';
$_GET['IVC_TYP']		= 'RC';

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../procurement-report.php";

	$overhead = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

//echo "<pre>"; print_r($overhead);

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../cost-routine.php";

	$resultsRoutine = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}


//query pembelian, retur pembelian, BTKL

$whereClauses = array("DET.DEL_NBR = 0", "HED.DEL_NBR = 0");
$whereClauses[] = "HED.BK_NBR = ".$bookNumber." ";

if($Accounting != 0) {
	$whereClauses[] = "HED.ACTG_TYP = ".$Accounting." ";
}

$whereClauses = implode(" AND ", $whereClauses);

$queryOrder 		= "SELECT 
					HED.BK_NBR,
					DET.GL_NBR,
					CD.CD_SUB_NBR,
					CD.CD_SUB_DESC,
					CD.CD_NBR,
					CD.CD_DESC,
					SUM(CASE WHEN CD.CD_SUB_NBR = ".$CdSubNbrRCMain."
						THEN COALESCE(DET.DEB,0)
						ELSE 0 
						END) AS ORD_TOT_AMT_MAIN,
					SUM(CASE WHEN CD.CD_SUB_NBR = ".$CdSubNbrRCSub."
						THEN COALESCE(DET.DEB,0)
						ELSE 0 
						END) AS ORD_TOT_AMT_SUB,
					SUM(CASE WHEN CD.CD_SUB_NBR = ".$CdSubNbrRTMain."
						THEN COALESCE(DET.CRT,0)
						ELSE 0 
						END) AS RTR_TOT_AMT_MAIN,
					SUM(CASE WHEN CD.CD_SUB_NBR = ".$CdSubNbrRTSub."
						THEN COALESCE(DET.CRT,0)
						ELSE 0 
						END) AS RTR_TOT_AMT_SUB,
					SUM(CASE WHEN CD.CD_SUB_NBR = ".$CdSubNbrBTKL."
						THEN COALESCE(DET.DEB,0)
						ELSE 0 
						END) AS BTKL,
					SUM(CASE WHEN CD.CD_SUB_NBR = ".$CdSubNbrClick."
						THEN COALESCE(DET.DEB,0)
						ELSE 0 
						END) AS CLICK,
					SUM(CASE WHEN CD.CD_SUB_NBR = ".$CdSubPPNmain."
						THEN COALESCE(DET.DEB,0)
						ELSE 0 
						END) AS PPN_IN_MAIN,
					SUM(CASE WHEN CD.CD_SUB_NBR = ".$CdSubPPNsub."
						THEN COALESCE(DET.DEB,0)
						ELSE 0 
						END) AS PPN_IN_SUB,
					HED.TAX_F
				FROM RTL.ACCTG_GL_DET DET
					LEFT OUTER JOIN RTL.ACCTG_GL_HEAD HED
						ON HED.GL_NBR = DET.GL_NBR
					INNER JOIN (
						SELECT SUB.CD_SUB_NBR, SUB.CD_SUB_ACC_NBR, SUB.CD_SUB_DESC, ACC.CD_NBR, ACC.CD_ACC_NBR, ACC.CD_DESC, CAT.CD_CAT_NBR, CAT.CD_CAT_DESC,
							CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
							CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC
						FROM RTL.ACCTG_CD_SUB SUB
							INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
							INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
						GROUP BY SUB.CD_SUB_NBR
					) CD ON CD.CD_SUB_NBR=DET.CD_SUB_NBR
					WHERE ".$whereClauses."
					GROUP BY DET.GL_DET_NBR";

//echo "<pre>"; echo $queryOrder;
					
$resultOrder	= mysql_query($queryOrder);
while($rowOrder	= mysql_fetch_array($resultOrder)) {
	
	$results['total']['ORD_TOT_AMT_MAIN']	+= $rowOrder['ORD_TOT_AMT_MAIN'];
	$results['total']['ORD_TOT_AMT_SUB']	+= $rowOrder['ORD_TOT_AMT_SUB'];
	$results['total']['RTR_TOT_AMT_MAIN']	+= $rowOrder['RTR_TOT_AMT_MAIN'];
	$results['total']['RTR_TOT_AMT_SUB']	+= $rowOrder['RTR_TOT_AMT_SUB'];
	
	$results['total']['PPN_IN_MAIN']		+= $rowOrder['PPN_IN_MAIN'];
	$results['total']['PPN_IN_SUB']			+= $rowOrder['PPN_IN_SUB'];
	
	$results['total']['BTKL']				+= $rowOrder['BTKL'];
	$results['total']['CLICK']				+= $rowOrder['CLICK'];
}

$results['data']['BEGIN_MAIN']		= $resultsBeginMain->total->BALANCE_AMT;
$results['data']['BEGIN_SUB']		= $resultsBeginSub->total->BALANCE_AMT;

$results['data']['ORDER_MAIN']		= $results['total']['ORD_TOT_AMT_MAIN'];
$results['data']['ORDER_SUB']		= $results['total']['ORD_TOT_AMT_SUB'];
$results['data']['RETUR_MAIN']		= $results['total']['RTR_TOT_AMT_MAIN'];
$results['data']['RETUR_SUB']		= $results['total']['RTR_TOT_AMT_SUB'];

$results['data']['PPN_IN_MAIN']		= $results['total']['PPN_IN_MAIN'];
$results['data']['PPN_IN_SUB']		= $results['total']['PPN_IN_SUB'];

$results['data']['PROCUREMENT'] 	= $results['data']['ORDER_MAIN'] +  $results['data']['ORDER_SUB'] - $results['data']['RETUR_MAIN'] - $results['data']['RETUR_SUB'];

$results['data']['END_MAIN']		= $resultsEndMain->total->BALANCE_AMT;
$results['data']['END_SUB']			= $resultsEndSub->total->BALANCE_AMT;

$results['data']['BTUD']			= $results['data']['BEGIN_MAIN'] + $results['data']['BEGIN_SUB'] + $results['data']['PROCUREMENT'];

$results['data']['USED']			= $results['data']['BTUD'] - $results['data']['END_MAIN'] - $results['data']['END_SUB'];

$results['data']['OVERHEAD']		= $overhead;
$results['data']['TOT_OVERHEAD']	= $overhead->total->TOT_AMT;
$results['data']['UTILITY'] 		= $resultsRoutine->utility;
$results['data']['ROUTINE'] 		= $resultsRoutine;
$results['data']['TOT_ROUTINE'] 	= $resultsRoutine->total->TOT_SUB * 0.9;

$results['data']['BTKL']			= $results['total']['BTKL'];

$results['data']['CLICK'] 			= $results['total']['CLICK'];


$results['data']['TOTAL_OVERHEAD']	= $results['data']['TOT_OVERHEAD'] + $results['data']['TOT_ROUTINE'] + $results['data']['CLICK'];


$results['data']['HPP']				= $results['data']['USED'] + $results['data']['BTKL'] + $results['data']['TOTAL_OVERHEAD'];

$results['data']['HPP_MAIN']		= $results['data']['BEGIN_MAIN'] + $results['data']['ORDER_MAIN'] - $results['data']['RETUR_MAIN'] - $results['data']['END_MAIN'];

//$results['data']['HPP_MAIN']		= $results['data']['ORDER_MAIN'];

$results['data']['HPP_SUB']		= $results['data']['BEGIN_SUB'] + $results['data']['ORDER_SUB'] - $results['data']['RETUR_SUB'] - $results['data']['END_SUB'];

//echo "<pre>"; print_r($results);

echo json_encode($results);

?>