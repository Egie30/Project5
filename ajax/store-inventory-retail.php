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

if (($Accounting == 0) || ($Accounting == 2)){
	
$whereClauses 	= array("HED.DEL_F=0");

if (!empty($beginDate)) {
	$whereClauses[] 	= "DATE(HED.DL_TS) >= '" . $beginDate . "'";
}

if (!empty($endDate)) {
	$whereClauses[] 	= "DATE(HED.DL_TS) <= '" . $endDate . "'";
}

$whereClauses 	= implode(" AND ", $whereClauses);


$query = "SELECT 
	STK.ORD_DTE,
	STK.ORD_YEAR,
	STK.ORD_MONTH,
	STK.ORD_DAY,
	STK.ORD_MONTHNAME,
	STK.ORD_DET_NBR,
	STK.ORD_NBR,
	SUM(STK.RCV_Q) AS RCV_Q,
	SUM(STK.RTR_Q) AS RTR_Q,
	SUM(STK.RCV_TOT_AMT) AS RCV_TOT_AMT,
	SUM(STK.RTR_TOT_AMT) AS RTR_TOT_AMT,
	SUM(REG.RTL_Q) AS RTL_Q,
	SUM(REG.TND_AMT) AS TND_AMT,
	COALESCE(SUM(STK.RCV_Q) - SUM(STK.RTR_Q) - SUM(REG.RTL_Q)) AS BALANCE_Q,
	COALESCE(SUM(STK.RCV_TOT_AMT) - SUM(STK.RTR_TOT_AMT) - SUM(REG.TND_AMT)) AS BALANCE_AMT
FROM (SELECT
			DATE(HED.DL_TS) AS ORD_DTE,
			YEAR(HED.DL_TS) AS ORD_YEAR,
			MONTH(HED.DL_TS) AS ORD_MONTH,
			DAY(HED.DL_TS) AS ORD_DAY,
			MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME,
			DET.ORD_DET_NBR,
			DET.INV_NBR,
			HED.ORD_NBR, 
			SUM(CASE WHEN (HED.IVC_TYP = 'RC' AND HED.EXP_TYP = 'RTL') THEN DET.ORD_Q ELSE 0 END) AS RCV_Q,
			SUM(CASE WHEN (HED.IVC_TYP = 'RT' AND HED.EXP_TYP = 'RTL') THEN DET.ORD_Q ELSE 0 END) AS RTR_Q,
			SUM(CASE WHEN (HED.IVC_TYP = 'RC' AND HED.EXP_TYP = 'RTL') THEN DET.TOT_SUB ELSE 0 END) AS RCV_TOT_AMT,
			SUM(CASE WHEN (HED.IVC_TYP = 'RT' AND HED.EXP_TYP = 'RTL') THEN DET.TOT_SUB ELSE 0 END) AS RTR_TOT_AMT
		FROM RTL.RTL_STK_HEAD HED
		LEFT JOIN RTL.RTL_STK_DET DET
			ON DET.ORD_NBR = HED.ORD_NBR
		LEFT JOIN CMP.COMPANY SPL
			ON SPL.CO_NBR = HED.SHP_CO_NBR
		LEFT JOIN CMP.COMPANY RCV
			ON RCV.CO_NBR = HED.RCV_CO_NBR
		WHERE ".$whereClauses."
		GROUP BY DET.INV_NBR
) STK
LEFT JOIN
(SELECT 
	CSH.INV_NBR,
	CSH.REG_NBR,
	CSH.TRSC_NBR,
	SUM(CSH.RTL_Q) AS RTL_Q,
	SUM(CSH.RTL_Q * CSH.RTL_PRC) AS TND_AMT
FROM RTL.CSH_REG CSH
	WHERE CSH.ACT_F = 0 AND CSH.CSH_FLO_TYP = 'RT'
		AND DATE(CRT_TS) >= '".$beginDate."'
		AND DATE(CRT_TS) <= '".$endDate."'
	GROUP BY CSH.INV_NBR
) REG ON REG.INV_NBR = STK.INV_NBR";
	
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

	
}
}
echo json_encode($results);
