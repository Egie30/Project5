<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

$queryCom	= "SELECT GROUP_CONCAT(CO_NBR) AS CO_NBR FROM NST.PARAM_COMPANY";
$resultCom	= mysql_query($queryCom);
$rowCom	= mysql_fetch_array($resultCom);
$CoEx 	= $rowCom['CO_NBR'];

$beginDate 		= $_GET['BEG_DT'];
$endDate 		= $_GET['END_DT'];
$days 			= $_GET['DAY'];
$months 		= $_GET['MONTH'];
$years 			= $_GET['YEAR'];
$companyNbr		= $_GET['CO_NBR'];
$searchQuery    = strtoupper($_REQUEST['s']);
$groups			= (array) $_GET['GROUP'];
$orders			= (array) $_GET['ORD_BY'];
$whereClauses	= array("HED.DEL_NBR=0", "TOT_REM > 0", "YEAR(HED.ORD_TS) > 2015","HED.BUY_CO_NBR NOT IN ($CoEx)");
$groupClauses 	= array();
$orderClauses   = array();

if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$whereClauses[] = "DAY(HED.ORD_TS)=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(HED.ORD_TS)= ".$months;
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(HED.ORD_TS)= ". $years;
	}
} else {
	if (!empty($beginDate)) {
		$whereClauses[] = "DATE(HED.ORD_TS) >= '" . $beginDate . "'";
	}

	if (!empty($endDate)) {
		$whereClauses[] = "DATE(HED.ORD_TS) <= '" . $endDate . "'";
	}
}

if ($companyNbr != "") {
	if($companyNbr == "TUNAI"){
		$whereClauses[] = "HED.BUY_CO_NBR IS NULL";
	}else{
		$whereClauses[] = "HED.BUY_CO_NBR = '" . $companyNbr . "'";
	}
}

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(PAY.PYMT_DTE)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(PAY.PYMT_DTE), MONTH(PAY.PYMT_DTE)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(PAY.PYMT_DTE), MONTH(PAY.PYMT_DTE), DAY(PAY.PYMT_DTE)";
				break;
			case "BUY_CO_NBR":
				$groupClauses[] = "HED.BUY_CO_NBR";
				break;
			case "ORD_TS":
				$groupClauses[] = "ORD_TS";
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

foreach ($orders as $field => $mode) {
	if (is_int($field)) {
		$field = $mode;
		$mode = "DESC";
	}
	
	switch (strtoupper($field)) {
		case "BUY_CO_NBR":
			$order = "HED.BUY_CO_NBR";
			break;
		case "BUY_CO_NAME":
			$order = "COM.NAME";
			break;
		case "TOT_REM":
			$order = "27";
			break;
		default:
			$order = "HED.ORD_NBR";
			break;
	}

	$orderClauses[] = $order . " " . $mode;
}

if ($searchQuery != "") {
	$searchQuery = explode(" ", $searchQuery);

	foreach ($searchQuery as $query) {
		$query = trim($query);

		if (empty($query)) {
			continue;
		}

		if (strrpos($query, '%') === false) {
			$query = '%' . $query . '%';
		}
		$whereClauses[] = "(
			HED.ORD_NBR LIKE '" . $query . "'
			OR REF_NBR LIKE '" . $query . "'
			OR ORD_TTL LIKE '" . $query . "'
			OR ORD_STT_DESC LIKE '" . $query . "'
			OR HED.BUY_CO_NBR LIKE '" . $query . "'
			OR COM.NAME LIKE '" . $query . "'
		)";
	}
}

$whereClauses = implode(" AND ", $whereClauses);
$groupClauses = implode(", ", $groupClauses);
$orderClauses = implode(", ", $orderClauses);
	
$query="SELECT 
	HED.ORD_NBR,
	REF_NBR,
	ORD_TTL,
	HED.ORD_STT_ID,
	ORD_STT_DESC,
	DUE_TS,
	PU_TS,
	CMP_TS,
	ORD_TS,
	DATE_ADD(CMP_TS,INTERVAL COALESCE(COM.PAY_TERM,0) DAY) AS PAST_DUE,
	JOB_LEN_TOT,
	DL_CNT,
	PU_CNT,
	NS_CNT,
	IVC_PRN_CNT,
	COUNT(HED.ORD_NBR) AS ORD_NBR_CNT, 
	YEAR(HED.ORD_TS) AS ORD_YEAR,
	MONTH(HED.ORD_TS) AS ORD_MONTH,
	HED.BUY_CO_NBR,
	COM.NAME AS NAME_CO,
	HED.BUY_PRSN_NBR,
	PPL.NAME AS NAME_PPL,
	PRN_CO_NBR,
	FEE_MISC,
	SUM(COALESCE(TOT_AMT,0)) AS TOT_AMT,
	SUM(COALESCE(PAY.TND_AMT,0)) AS PYMT_DOWN,
	SUM(COALESCE(TOT_AMT,0)) - SUM(COALESCE(PAY.TND_AMT,0)) AS TOT_REM,
	SPC_NTE
FROM CMP.PRN_DIG_ORD_HEAD HED 
	INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
	LEFT JOIN (
		SELECT 
			PYMT.ORD_NBR,
			SUM(COALESCE(PYMT.TND_AMT,0)) AS TND_AMT
		FROM CMP.PRN_DIG_ORD_PYMT PYMT
		WHERE PYMT.DEL_NBR = 0
		GROUP BY PYMT.ORD_NBR
	) PAY ON PAY.ORD_NBR = HED.ORD_NBR
WHERE " . $whereClauses . "
GROUP BY " .$groupClauses."
ORDER BY " . $orderClauses;

//echo "<pre>".$query;
$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'total' => array()
);
$result = mysql_query($query);

while($row = mysql_fetch_array($result)) {

	$results['data'][] = $row;
	$results['total']['TOT_AMT']		+= $row['TOT_AMT'];	
	$results['total']['PYMT_DOWN']		+= $row['PYMT_DOWN'];	
	$results['total']['TOT_REM']		+= $row['TOT_REM'];	
}
//print_r($query);
echo json_encode($results);