<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

$Accounting		= $_GET['ACTG'];
$companyNumber  = $CoNbrDef;
$beginDate 		= $_GET['BEG_DT'];
$endDate 		= $_GET['END_DT'];
$days 			= $_GET['DAYS'];
$months 		= $_GET['MONTHS'];
$years 			= $_GET['YEARS'];
$day 			= $_GET['DAY'];
$month			= $_GET['MONTH'];
$year			= $_GET['YEAR'];
$consignment 	= $_GET['CNMT_F'];
$plusMode 		= $_GET['PLUS'];
$searchQuery    = strtoupper($_REQUEST['s']);
$groups 		= (array) $_GET['GROUP'];

$whereClauses = array("HED.DEL_F = 0", "DATE(HED.ORD_DTE) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)","(HED.RCV_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY) OR HED.RCV_CO_NBR IS NULL)");
$groupClauses = array();

$whereClauses[] = "HED.SHP_CO_NBR =" . $companyNumber;

if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$whereClauses[] = "DAY(HED.ORD_DTE)=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(HED.ORD_DTE)= ".$months;
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(HED.ORD_DTE)= ". $years;
	}

	if ($day != "") {
		$whereClauses[] = "DAY(HED.ORD_DTE)=" . $day;
	}

	if ($month != "") {
		$whereClauses[] = "MONTH(HED.ORD_DTE)=" . $month;
	}

	if ($year != "") {
		$whereClauses[] = "YEAR(HED.ORD_DTE)=" . $year;
	}
} else {
	
	if($_GET['TYP'] == 'TAX_IVC'){
		if (!empty($beginDate)) {
			$whereClauses[] = "TAX_IVC_DTE >= '" . $beginDate . "'";
		}

		if (!empty($endDate)) {
			$whereClauses[] = "TAX_IVC_DTE <= '" . $endDate . "'";
		}
	}else{
		if (!empty($beginDate)) {
			$whereClauses[] = "DATE(HED.ORD_DTE) >= '" . $beginDate . "'";
		}

		if (!empty($endDate)) {
			$whereClauses[] = "DATE(HED.ORD_DTE) <= '" . $endDate . "'";
		}
	}
	
}

if ($Accounting != 0) {
	$whereClauses[]	= "HED.ACTG_TYP = ".$Accounting." ";
}

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(HED.ORD_DTE)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(HED.ORD_DTE), MONTH(HED.ORD_DTE)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(HED.ORD_DTE), MONTH(HED.ORD_DTE), DAY(HED.ORD_DTE)";
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
	DATE(HED.ORD_DTE) AS ORD_DTE,
	DATE(HED.ORD_DTE) AS CSH_DTE,
	YEAR(HED.ORD_DTE) AS CSH_YEAR,
	MONTH(HED.ORD_DTE) AS CSH_MONTH,
	DAY(HED.ORD_DTE) AS CSH_DAY,
	MONTHNAME(HED.ORD_DTE) AS CSH_MONTHNAME,
	HED.ORD_NBR,
	COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
	COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
	(CASE 
		WHEN HED.RCV_CO_NBR != '' THEN RCV.NAME 
		ELSE 'Tunai' 
	END) AS BUY_NAME,
	TAX_IVC_NBR,
	TAX_IVC_DTE
FROM RTL.RTL_ORD_HEAD HED
	LEFT OUTER JOIN CMP.COMPANY SHP ON SHP.CO_NBR = HED.SHP_CO_NBR
	LEFT OUTER JOIN CMP.COMPANY RCV ON RCV.CO_NBR = HED.RCV_CO_NBR
WHERE " . $whereClauses . "
GROUP BY " . $groupClause . " ";

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