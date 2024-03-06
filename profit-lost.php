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

$_GET['GROUP']		= 'MONTH';
$_GET['MONTHS']		= $row_bk['BK_MONTH'];
$_GET['YEARS']		= $row_bk['BK_YEAR'];

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../prn-dig-report-full.php";

	$resultsPrint = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

unset($_GET['GROUP']);

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "hpp.php";

	$resultsCOGS = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

$_GET['GROUP']		= 'MONTH';

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../cost-cash.php";

	$resultsCash = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}


try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../cost-routine.php";

	$resultsRoutine = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}


try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../payroll.php";

	$resultsPayroll = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

unset($_GET['GROUP']);

$_GET['TYP']		= 'RL';
$_GET['IVC_TYP']	= 'RC';

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../cost-profit-lost.php";

	$resultsCost = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'total' => array()
);


$results['data']['PRINT_DIGITAL']	= $resultsPrint;
$results['data']['COGS']			= $resultsCOGS->data->COGS;

$results['data']['ROUTINE'] 		= $resultsRoutine;
$results['data']['UTILITY'] 		= $resultsRoutine->utility;

$results['data']['CASH'] 			= $resultsCash;
$results['data']['EXPENSE'] 		= $resultsCash->expense;

$results['data']['PAYROLL'] 		= $resultsPayroll->total->PAY_AMT * 0.1;

$results['data']['COST'] 			= $resultsCost;

$results['data']['TOTAL_COST'] 			= (0.1 * ($resultsPayroll->total->PAY_AMT + $resultsRoutine->total->TOT_SUB )) + $resultsCash->total->TOT_SUB + $resultsCost->total->RL;

$results['data']['GROSS_PROFIT']	= $resultsPrint->total->TOT_AMT - $results['data']['COGS'];

$results['data']['PROFIT_LOSS']		= $results['data']['GROSS_PROFIT'] - $results['data']['TOTAL_COST'];

//echo "<pre>";
//print_r($resultsCOGS);

echo json_encode($results);