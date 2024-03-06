<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";


/*
Don't forget to update TAX_F on CMP.COMPANY
*/

if (empty($_GET['BEG_DT'])) {
	$_GET['BEG_DT'] = '2016-01-01';
}

if (empty($_GET['END_DT'])) {
	$_GET['END_DT'] = date("Y-m-d");
}


$beginDate 		= $_GET['BEG_DT'];
$endDate 		= $_GET['END_DT'];

$IvcTyp			= $_GET['IVC_TYP'];
$Accounting		= $_GET['ACTG'];
$days 			= $_GET['DAYS'];
$months 		= $_GET['MONTHS'];
$years 			= $_GET['YEARS'];
$day 			= $_GET['DAY'];
$month			= $_GET['MONTH'];
$year			= $_GET['YEAR'];
$consignment 	= $_GET['CNMT_F'];
$plusMode 		= $_GET['PLUS'];
$searchQuery    = strtoupper($_REQUEST['s']);


$whereClauses 	= array("HED.DEL_F=0");
$whereMovement 	= array("HED.DEL_F=0", "MOV.DEL_NBR = 0");

if (!empty($beginDate)) {
	$whereClauses[] 	= "DATE(HED.DL_TS) >= '" . $beginDate . "'";
	$whereMovement[] 	= "(DATE(HED.DL_TS) >= '" . $beginDate . "' AND DATE(MOV.CRT_TS) >= '" . $beginDate . "')";
}

if (!empty($endDate)) {
	$whereClauses[] 	= "DATE(HED.DL_TS) <= '" . $endDate . "'";
	$whereMovement[] 	= "(DATE(HED.DL_TS) <= '" . $endDate . "' AND DATE(MOV.CRT_TS) <= '" . $endDate . "')";
}

if ($Accounting == 1) {
	$whereClauses[] 	= "HED.TAX_APL_ID IN ('I', 'A')";
	$whereMovement[] 	= "HED.TAX_APL_ID IN ('I', 'A')";
}

if ($Accounting == 2) {
	$whereClauses[] 	= "((HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 1 AND IVC_TYP = 'RC') OR (HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 1 AND IVC_TYP = 'RT'))";
	
	$whereMovement[] 	= "HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 1";
}

if ($Accounting == 3) {
	$whereClauses[] 	= "((HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 0 AND IVC_TYP = 'RC') OR (HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 0 AND IVC_TYP = 'RT'))";
	
	$whereMovement[] 	= "HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 0";
}

$whereClauses 	= implode(" AND ", $whereClauses);
$whereMovement 	= implode(" AND ", $whereMovement);


$query = "SELECT 
	STK.ORD_DTE,
	STK.ORD_YEAR,
	STK.ORD_MONTH,
	STK.ORD_DAY,
	STK.ORD_MONTHNAME,
	STK.ORD_DET_NBR,
	STK.ORD_NBR,
	SUM(COALESCE(STK.RCV_Q,0)) AS RCV_Q,
	SUM(COALESCE(STK.RTR_Q,0)) AS RTR_Q,
	SUM(COALESCE(STK.RCV_TOT_AMT,0)) AS RCV_TOT_AMT,
	SUM(COALESCE(STK.RTR_TOT_AMT,0)) AS RTR_TOT_AMT,
	SUM(COALESCE(MOV.MOV_Q,0)) AS MOV_Q,
	SUM(COALESCE(MOV.MOV_TOT_AMT,0)) AS MOV_TOT_AMT,
	SUM(COALESCE(STK.RCV_Q,0) - COALESCE(STK.RTR_Q,0) - COALESCE(MOV.MOV_Q,0)) AS BALANCE_Q,
	SUM(COALESCE(STK.RCV_TOT_AMT,0) - COALESCE(STK.RTR_TOT_AMT,0) - COALESCE(MOV.MOV_TOT_AMT,0)) AS BALANCE_AMT
FROM (SELECT
			DATE(HED.DL_TS) AS ORD_DTE,
			YEAR(HED.DL_TS) AS ORD_YEAR,
			MONTH(HED.DL_TS) AS ORD_MONTH,
			DAY(HED.DL_TS) AS ORD_DAY,
			MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME,
			DET.ORD_DET_NBR,
			DET.INV_NBR,
			HED.ORD_NBR, 
			SUM(CASE WHEN (HED.IVC_TYP = 'RC') THEN DET.ORD_Q ELSE 0 END) AS RCV_Q,
			SUM(CASE WHEN (HED.IVC_TYP = 'RT') THEN DET.ORD_Q ELSE 0 END) AS RTR_Q,
			SUM(CASE WHEN (HED.IVC_TYP = 'RC') THEN DET.TOT_SUB ELSE 0 END) AS RCV_TOT_AMT,
			SUM(CASE WHEN (HED.IVC_TYP = 'RT') THEN DET.TOT_SUB ELSE 0 END) AS RTR_TOT_AMT
		FROM RTL.RTL_STK_HEAD HED
		LEFT JOIN RTL.RTL_STK_DET DET
			ON DET.ORD_NBR = HED.ORD_NBR
		LEFT JOIN CMP.COMPANY SPL
			ON SPL.CO_NBR = HED.SHP_CO_NBR
		LEFT JOIN CMP.COMPANY RCV
			ON RCV.CO_NBR = HED.RCV_CO_NBR
		WHERE ".$whereClauses."
		GROUP BY DET.ORD_DET_NBR
) STK
LEFT JOIN
(SELECT MOV.ORD_DET_NBR,
	SUM(MOV.MOV_Q) AS MOV_Q,
	SUM(MOV.MOV_Q * DET.INV_PRC) AS MOV_TOT_AMT
FROM RTL.INV_MOV MOV
	JOIN RTL.RTL_STK_DET DET 
		ON MOV.ORD_DET_NBR = DET.ORD_DET_NBR
	LEFT JOIN RTL.RTL_STK_HEAD HED
		ON DET.ORD_NBR = HED.ORD_NBR
	LEFT JOIN CMP.COMPANY SPL
		ON SPL.CO_NBR = HED.SHP_CO_NBR
	WHERE ".$whereMovement."
	GROUP BY MOV.ORD_DET_NBR
) MOV ON MOV.ORD_DET_NBR = STK.ORD_DET_NBR";
	
//echo "<pre>".$query;

$pagination = pagination($query, 1000);

$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'pagination' => $pagination,
	'total' => array()
);
$result = mysql_query($pagination['query']);

while($row = mysql_fetch_array($result)) {

	$results['data'][] = $row;

	$results['total']['TOT_AMT'] 	+= $row['TOT_AMT'];
	$results['total']['PYMT_DOWN'] 	+= $row['PYMT_DOWN'];
	$results['total']['PYMT_REM'] 	+= $row['PYMT_REM'];
	$results['total']['TOT_REM'] 	+= $row['TOT_REM'];
	
	
}

echo json_encode($results);
