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


$_GET['GROUP']			= 'CAT_SUB_NBR';
$_GET['MONTHS']			= $row['BK_MONTH'];
$_GET['YEARS']			= $row['BK_YEAR'];
$_GET['CAT_TYP_NBR']	= "4";

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../procurement-report.php";

	$resultsCost = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}


unset($_GET['GROUP']);
unset($_GET['MONTHS']);
unset($_GET['YEARS']);
unset($_GET['CAT_TYP_NBR']);

//echo "<pre>";
//print_r($resultsCost);

$results['data']['PRINT_DIGITAL']	= $resultsPrint;
$results['data']['HPP']				= $resultsCOGS->data->HPP;

$results['data']['BTKTL']			= $resultsCOGS->data->BTKTL;

$results['data']['COST']			= $resultsCost;
$results['data']['TOT_COST']		= $resultsCost->total->TOT_AMT;

$results['data']['ROUTINE'] 		= $resultsRoutine;
$results['data']['UTILITY'] 		= $resultsRoutine->utility;

$results['data']['CASH'] 			= $resultsCash;
$results['data']['EXPENSE'] 		= $resultsCash->expense;
$results['data']['TOT_CASH'] 		= $resultsCash->total->TOT_SUB;

$results['data']['TOTAL_COST'] 		= $results['data']['BTKTL'] +  (0.1 * $resultsRoutine->total->TOT_SUB) + $results['data']['TOT_CASH'] + $results['data']['COST']->total->TOT_AMT;

$results['data']['GROSS_PROFIT']	= $resultsPrint->total->TOT_AMT - $results['data']['HPP'];

$results['data']['PROFIT_LOSS']		= $results['data']['GROSS_PROFIT'] - $results['data']['TOTAL_COST'];

//echo "<pre>";
//print_r($resultsCOGS);

echo json_encode($results);