<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

$companyNumber  = $CoNbrDef;
$beginDate 		= $_GET['BEG_DT'];
$endDate 		= $_GET['END_DT'];
$searchQuery    = strtoupper($_REQUEST['s']);
$groups 		= (array) $_GET['GROUP'];
$whereClauses 	= array("HED.DEL_F = 0");
$groupClauses 	= array();

if (!empty($beginDate)) {
	$whereClauses[] = "DATE(ORD_DTE) >= '" . $beginDate . "'";
}

if (!empty($endDate)) {
	$whereClauses[] = "DATE(ORD_DTE) <= '" . $endDate . "'";
}

if ($_GET['IVC_TYP'] != "") {
	$whereClauses[] = "HED.IVC_TYP = '" . $_GET['IVC_TYP'] . "'";
}

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(ORD_DTE)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(ORD_DTE), MONTH(ORD_DTE)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(ORD_DTE), MONTH(ORD_DTE), DAY(ORD_DTE)";
				break;
			case "ORD_NBR":
				$groupClauses[] = "HED.ORD_NBR";
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

if ($searchQuery != "") {
	$searchQuery = explode(" ", $searchQuery);

	foreach ($searchQuery as $query) {
		$query = mysql_real_escape_string(trim($query));

		if (empty($query)) {
			continue;
		}

		if (strrpos($query, '%') === false) {
			$query = '%' . $query . '%';
		}

		$whereClauses[] = "(
			HED.REF_NBR LIKE '" . $query . "'
			OR HED.SHP_CO_NBR LIKE '" . $query . "'
			OR HED.RCV_CO_NBR LIKE '" . $query . "'
			OR SHP.NAME LIKE '" . $query . "'
			OR RCV.NAME LIKE '" . $query . "'
			OR HED.ORD_NBR LIKE '" . $query . "'
		)";
	}
}

$whereClauses = implode(" AND ", $whereClauses);
$groupClauses = implode(", ", $groupClause);

$query 	= "SELECT 
	HED.ORD_NBR,
	HED.ORD_TTL,
	DATE(HED.ORD_DTE) AS ORD_DTE,
	MONTH(HED.ORD_DTE) AS ORD_MONTH,
	YEAR(HED.ORD_DTE) AS ORD_YEAR,
	DATE(HED.DL_TS) AS DL_DTE,
	HED.SHP_CO_NBR,
	SHP.NAME AS SHIPPER,
	HED.RCV_CO_NBR,
	RCV.NAME AS RECEIVER,
	HED.ORD_STT_ID,
	STT.ORD_STT_DESC,
	HED.TAX_APL_ID,
	TAX.TAX_APL_DESC,
	HED.TAX_IVC_NBR,
	COALESCE(SUM(
	CASE 
		WHEN HED.TAX_APL_ID IN ('I','A') THEN (HED.TOT_AMT)/11 
		ELSE HED.TAX_AMT
		END
	),0) AS TAX_AMT,
	COALESCE(SUM(
		CASE WHEN HED.TAX_APL_ID IN ('I','A') THEN (HED.TOT_AMT)/1.1 
		ELSE HED.TOT_AMT
		END
	),0) AS SUBTOTAL,
	SUM(HED.FEE_MISC) AS FEE_MISC,
	SUM(HED.TOT_AMT) AS TOT_AMT,
	SUM(HED.TOT_REM) AS TOT_REM,
	HED.CRT_TS,
	HED.CRT_NBR,
	CRT.NAME AS CRT_NAME,
	DET.ORD_Q
FROM RTL.RTL_ORD_HEAD HED 
	LEFT OUTER JOIN (
		SELECT 
			ORD_NBR,
			COUNT(INV_NBR) AS INV_CNT,
			SUM(ORD_Q) AS ORD_Q,
			SUM(TOT_SUB) AS TOT_SUB
		FROM RTL.RTL_ORD_DET
		WHERE DEL_NBR = 0
		GROUP BY ORD_NBR
	) AS DET ON HED.ORD_NBR=DET.ORD_NBR
	LEFT JOIN RTL.ORD_STT STT ON HED.ORD_STT_ID = STT.ORD_STT_ID
	LEFT JOIN CMP.COMPANY SHP ON SHP.CO_NBR = HED.SHP_CO_NBR
	LEFT JOIN CMP.COMPANY RCV ON RCV.CO_NBR = HED.RCV_CO_NBR
	LEFT JOIN CMP.TAX_APL TAX ON TAX.TAX_APL_ID = HED.TAX_APL_ID
	LEFT JOIN CMP.PEOPLE CRT ON HED.CRT_NBR=CRT.PRSN_NBR
WHERE " . $whereClauses . "
GROUP BY " . $groupClause . "
ORDER BY HED.UPD_TS";

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

	$results['total']['ORD_Q'] 		+= $row['ORD_Q'];
	$results['total']['SUBTOTAL'] 	+= $row['SUBTOTAL'];
	$results['total']['TAX_AMT'] 	+= $row['TAX_AMT'];
	$results['total']['TOT_AMT'] 	+= $row['TOT_AMT'];
	$results['total']['TOT_REM'] 	+= $row['TOT_REM'];

}

echo json_encode($results);