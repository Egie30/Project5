<?php
require_once __DIR__ . "/../../framework/database/connect.php";
require_once __DIR__ . "/../../framework/functions/default.php";

$_GET['LIMIT'] 	= -1;
$Actg			= $_GET['ACTG'];

try {
	$_GET['CD_CAT_NBR'] = 3;

	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "balance-report.php";

	$resultsBalance = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

//print_r($resultsBalance);

unset($_GET['CD_CAT_NBR']);


try {
	$_GET['CD_CAT_NBR'] = 7;

	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "balance-report.php";

	$resultsPrive = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}


$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'total' => array()
);

$results['data']['BALANCE']	= $resultsBalance->total->PASSIVA;
$results['data']['PROFIT_LOSS']	= $resultsBalance->total->PROFIT_LOSS;
$results['data']['PRIVE']	= $resultsPrive->total->PRIVE;
$results['data']['PROFIT_PRIVE']	= $resultsBalance->total->PROFIT_LOSS - $resultsPrive->total->PRIVE;
$results['data']['EQUITY']			= $resultsBalance->total->PASSIVA + $results['data']['PROFIT_PRIVE'];

echo json_encode($results);