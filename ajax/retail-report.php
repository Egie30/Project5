<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

/*
Don't forget to update TAX_F on ".$cmp.".COMPANY
*/

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
//$invoiceNumber 	= $_GET['IVC_NBR'];
$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];

if (!empty($_GET['CO_NBR'])) {
	$companyNumber = $_GET['CO_NBR'];
}

$whereClauses = array("CSH.ACT_F=0", "CSH.CSH_FLO_TYP = 'RT'", "CSH.RTL_BRC <> ''", "INV.CAT_NBR != 9");
$groupClauses = array();

$whereClauses[] = "CSH.CO_NBR=" . $companyNumber;

if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$whereClauses[] = "DAY(CSH.CRT_TS)=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(CSH.CRT_TS)= ".$months;
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(CSH.CRT_TS)= ". $years;
	}

	if ($day != "") {
		$whereClauses[] = "DAY(CSH.CRT_TS)=" . $day;
	}

	if ($month != "") {
		$whereClauses[] = "MONTH(CSH.CRT_TS)=" . $month;
	}

	if ($year != "") {
		$whereClauses[] = "YEAR(CSH.CRT_TS)=" . $year;
	}
} else {
	if (!empty($beginDate)) {
		$whereClauses[] = "DATE(CSH.CRT_TS) >= '" . $beginDate . "'";
	}

	if (!empty($endDate)) {
		$whereClauses[] = "DATE(CSH.CRT_TS) <= '" . $endDate . "'";
	}
}


if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(CSH.CRT_TS)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(CSH.CRT_TS), MONTH(CSH.CRT_TS)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(CSH.CRT_TS), MONTH(CSH.CRT_TS), DAY(CSH.CRT_TS)";
				break;
			default:
				$groupClauses[] = "CSH.TRSC_NBR";
				break;
		}
	}
		
	$groupClause = implode(", ", $groupClauses);
} else {
	$groupClause = "CSH.TRSC_NBR";
}

$whereClauses = implode(" AND ", $whereClauses);
$groupClauses = implode(", ", $groupClauses);

$query = "SELECT DATE(CSH.CRT_TS) AS CSH_DTE,
			YEAR(CSH.CRT_TS) AS CSH_YEAR,
			MONTH(CSH.CRT_TS) AS CSH_MONTH,
			DAY(CSH.CRT_TS) AS CSH_DAY,
			MONTHNAME(CSH.CRT_TS) AS CSH_MONTHNAME,
			CSH.TRSC_NBR, 
			CSH.TRSC_NBR_PLUS,
			COALESCE(SUM(CSH.TND_AMT), 0) AS TND_AMT,
			SPL.NAME AS SPL_NAME
		FROM RTL.CSH_REG CSH
			LEFT OUTER JOIN RTL.INVENTORY INV
				ON CSH.INV_NBR = INV.INV_NBR
			LEFT OUTER JOIN CMP.COMPANY SPL
				ON INV.CO_NBR = SPL.CO_NBR
				WHERE " . $whereClauses . "
				GROUP BY " . $groupClauses . " ";
			
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

	$results['total']['TND_AMT'] 	+= $row['TND_AMT'];
	$results['total']['PYMT_AMT'] 	+= $row['PYMT_AMT'];
	
	
}

echo json_encode($results);