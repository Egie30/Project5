<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

$companyNumber  = $CoNbrDef;
$searchQuery    = strtoupper($_REQUEST['s']);
$groups 		= (array) $_GET['GROUP'];
$whereClauses 	= array("HED.DEL_NBR = 0");
$groupClauses 	= array();

if ($_GET['BUY_CO_NBR'] != '') {
	$whereClauses[]	= "HED.BUY_CO_NBR = '".$_GET['BUY_CO_NBR']."' ";
}

if ($_GET['BUY_PRSN_NBR'] != '') {
	$whereClauses[]	= "HED.BUY_PRSN_NBR = '".$_GET['BUY_PRSN_NBR']."' ";
}

if ($_GET['PRN_DIG_STT'] != '') {
	$whereClauses[]	= "HED.PRN_DIG_STT = '".$_GET['PRN_DIG_STT']."' ";
}

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "BUY_CO_NBR":
				$groupClauses[] = "HED.BUY_CO_NBR";
				break;
			case "BUY_PRSN_NBR":
				$groupClauses[] = "HED.BUY_PRSN_NBR";
				break;
			case "PRN_DIG_STT":
				$groupClauses[] = "HED.PRN_DIG_STT";
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
	DATE(HED.ORD_TS) AS ORD_DTE,
	DATE(HED.ORD_TS) AS CSH_DTE,
	YEAR(HED.ORD_TS) AS CSH_YEAR,
	MONTH(HED.ORD_TS) AS CSH_MONTH,
	DAY(HED.ORD_TS) AS CSH_DAY,
	MONTHNAME(HED.ORD_TS) AS CSH_MONTHNAME,
	HED.ORD_NBR,
	COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
	HED.ORD_STT_ID,
	STT.ORD_STT_DESC,
	COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
	HED.BUY_CO_NBR,
	(CASE 
		WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
		WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
		ELSE 'Tunai' END 
	) AS BUY_NAME
FROM CMP.PRN_DIG_ORD_HEAD HED
	INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
WHERE " . $whereClauses . "
GROUP BY " . $groupClauses . " ";

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
}
echo json_encode($results);