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

$_GET['STK_MONTH']		= $row_bk['BK_MONTH_BEG'];
$_GET['STK_YEAR']		= $row_bk['BK_YEAR_BEG'];


try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../store-inventory-monthly.php";

	$resultsBegin = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

//echo "<pre>";
//print_r($resultsBegin);

unset($_GET['STK_MONTH']);
unset($_GET['STK_YEAR']);

$_GET['STK_MONTH']		= $row_bk['BK_MONTH'];
$_GET['STK_YEAR']		= $row_bk['BK_YEAR'];

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../store-inventory-monthly.php";

	$resultsEnd = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

unset($_GET['END_DT']);

$_GET['GROUP']		= 'MONTH';
$_GET['MONTHS']		= $row_bk['BK_MONTH'];
$_GET['YEARS']		= $row_bk['BK_YEAR'];

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../payroll.php";

	$resultsPayroll = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

unset($_GET['GROUP']);
unset($_GET['MONTHS']);
unset($_GET['YEARS']);
unset($_GET['BEG_DT']);
unset($_GET['END_DT']);

$_GET['GROUP']		= 'MONTH';
$_GET['MONTHS']		= $row_bk['BK_MONTH'];
$_GET['YEARS']		= $row_bk['BK_YEAR'];


try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../cost-routine.php";

	$resultsRoutine = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

$_GET['CAT_SUB_TYP']	= 'CLICK';
$_GET['IVC_TYP']		= 'RC';
$_GET['TYP']			= 'PRN_DIG';
$_GET['CAT_SUB_NBR'] 	= 202;

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../purchase-report.php";

	$resultsClick = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

unset($_GET['CAT_SUB_TYP']);
unset($_GET['CAT_SUB_NBR']);
unset($_GET['GROUP']);
unset($_GET['TYP']);

$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'total' => array()
);


$results['data']['RCV_BEGIN']		= $resultsBegin->total->RCV_TOT_SUB;
$results['data']['RTR_BEGIN']		= $resultsBegin->total->RTR_TOT_SUB;
$results['data']['BEGIN'] 			= $resultsBegin->total->BALANCE_AMT;

$results['data']['RCV_END']			= $resultsEnd->total->RCV_TOT_SUB;
$results['data']['RTR_END']			= $resultsEnd->total->RTR_TOT_SUB;
$results['data']['END'] 			= $resultsEnd->total->BALANCE_AMT;

$results['data']['RECEIVING']		= $resultsEnd->total->RCV_TOT_SUB - $resultsBegin->total->RCV_TOT_SUB;
$results['data']['RETUR']			= $resultsEnd->total->RTR_TOT_SUB - $resultsBegin->total->RTR_TOT_SUB;

$results['data']['RECEIVING_NETT']	= $results['data']['RECEIVING'] - $results['data']['RETUR'];
$results['data']['BTUD']			= $resultsBegin->total->BALANCE_AMT + $results['data']['RECEIVING_NETT'];

$results['data']['STK_MOV']			= $results['data']['BTUD'] - $resultsEnd->total->BALANCE_AMT;;

$results['data']['PAYROLL'] 		= $resultsPayroll->total->PAY_AMT * 0.9;

$results['data']['ROUTINE'] 		= $resultsRoutine;
$results['data']['UTILITY'] 		= $resultsRoutine->utility;
$results['data']['ROUTINE_TOT_SUB'] = $resultsRoutine->total->TOT_SUB * 0.9;

//echo "<pre>";
//print_r($results['data']['UTILITY']);

$results['data']['CLICK'] 			= $resultsClick->total->RCV_TOT_SUB;


$results['data']['TOTAL_COST']		= $results['data']['PAYROLL'] + $results['data']['ROUTINE_TOT_SUB'] + $results['data']['CLICK'];


if ($_GET['RL_TYP'] == 'IVC') {
	$results['data']['COGS']			= $results['data']['RECEIVING_NETT'] + $results['data']['TOTAL_COST'];
}
else {
	$results['data']['COGS']			= $results['data']['STK_MOV'] + $results['data']['TOTAL_COST'];	
}

//echo "<pre>";
//print_r($resultsRoutine);

echo json_encode($results);