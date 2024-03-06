<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

$beginDate 		= $_GET['BEG_DT'];
$endDate 		= $_GET['END_DT'];
$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];

$whereClauses = array("HED.DEL_NBR = 0","(HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY) OR HED.BUY_CO_NBR IS NULL)","PRN.BUS_TYP = 'STN'");
$groupClauses = array();

if ($_GET['STN_NBR'] != "") {
	$whereClauses[] = "DET.STN_NBR=" . $_GET['STN_NBR'];
}

if ($_GET['ORD_NBR'] != "") {
	$whereClauses[] = "HED.ORD_NBR='" . $_GET['ORD_NBR'] . "'";
	$detailWhereClauses[] = "DET.ORD_NBR='" . $_GET['ORD_NBR'] . "'";
}

if ($beginDate != "") {
	$whereClauses[] = "DATE(HED.ORD_TS) >= '" . $_GET['BEG_DT'] . "'";
}

if ($endDate != "") {
	$whereClauses[] = "DATE(HED.ORD_TS) <= '" . $_GET['END_DT'] . "'";
}

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(HED.ORD_TS)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(HED.ORD_TS), MONTH(HED.ORD_TS)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(HED.ORD_TS), MONTH(HED.ORD_TS), DAY(HED.ORD_TS)";
				break;
			default:
				$groupClauses[] = "HED.ORD_NBR";
				break;
		}
	}
		
	$groupClause = implode(", ", $groupClauses);
} else {
	$groupClause = "HED.ORD_NBR";
}

$whereClauses = implode(" AND ", $whereClauses);
$groupClauses = implode(", ", $groupClauses);

$query = "SELECT 
	HED.ORD_NBR,
	HED.ORD_STT_ID,
	ORD_STT_DESC,
	DATE(HED.ORD_TS) AS ORD_DTE,
	DATE(HED.CMP_TS) AS CMP_DTE,
	HED.DUE_TS,
	COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
	(CASE
		WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
		WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
		ELSE 'Tunai' END 
	) AS BUY_NAME,
	JOB_LEN_TOT,
	COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
	COALESCE(SUM(HED.TOT_REM), 0) AS TOT_REM,
	HED.SPC_NTE,
	HED.DL_CNT,
	HED.PU_CNT,
	HED.NS_CNT,
	HED.IVC_PRN_CNT
FROM CMP.PRN_DIG_ORD_HEAD HED
	INNER JOIN CMP.COMPANY PRN ON HED.PRN_CO_NBR = PRN.CO_NBR
	INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
WHERE " . $whereClauses . "
GROUP BY " . $groupClause;
//echo "<pre>".$query;
//exit();
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
	$results['total']['TOT_REM'] 	+= $row['TOT_REM'];
}

echo json_encode($results);