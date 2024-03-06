<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

/*
Don't forget to update TAX_F on CMP.COMPANY
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
$curl			= $_GET['CURL'];
$CatSubNbr		= $_GET['CAT_SUB_NBR'];
$CatTypeNbr		= $_GET['CAT_TYP_NBR'];
$Type			= $_GET['TYP'];
$InvoiceType	= $_GET['IVC_TYP'];
$PaymentType	= $_GET['PYMT_TYP'];
$Category		= $_GET['CAT_NBR'];

$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];

$whereClauses = array("INV.DEL_NBR = 0", "DATE(HED.ORD_DTE) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)", "HED.IVC_TYP = '".$InvoiceType."' ");
$groupClauses = array();


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
	if (!empty($beginDate)) {
		$whereClauses[] = "DATE(HED.ORD_DTE) >= '" . $beginDate . "'";
	}

	if (!empty($endDate)) {
		$whereClauses[] = "DATE(HED.ORD_DTE) <= '" . $endDate . "'";
	}
}

if ($CatSubNbr != "") {
	$whereClauses[] = "HED.CAT_SUB_NBR = ".$CatSubNbr." ";
}


if ($Accounting != 0) {
	$whereClauses[] = "HED.ACTG_TYP = ".$Accounting." ";
}
	
if ($CatTypeNbr != 0) {
	$whereClauses[] = "TYP.CAT_TYP_NBR IN (".$CatTypeNbr.") ";
}

if ($PaymentType != "") {
	$whereClauses[] = "HED.PYMT_TYP = '".$PaymentType."' ";
}

if ($Category != 0) {
	$whereClauses[] = "HED.CAT_NBR = ".$Category." ";
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
			case "ORD_NBR":
				$groupClauses[] = "HED.ORD_NBR";
				break;
			case "CAT_SUB_NBR":
				$groupClauses[] = "HED.CAT_SUB_NBR";
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

		$query 	= "SELECT 
					DET.INV_NBR,
					INV.CAT_NBR,
					INV.NAME,
					DET.ORD_Q,
					SUM(COALESCE(DET.TOT_SUB,0)) AS TOT_SUB
				FROM (
				SELECT 
					DET.INV_NBR,
					DET.ORD_Q,
					SUM(COALESCE(DET.TOT_SUB,0)) AS TOT_SUB
				FROM RTL.RTL_STK_DET DET
				JOIN RTL.RTL_STK_HEAD HED 
					ON DET.ORD_NBR = HED.ORD_NBR
				WHERE HED.DEL_F = 0
					AND DATE(HED.ORD_DTE) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)
					AND HED.IVC_TYP = '".$InvoiceType."'
					AND HED.ORD_DTE >= '".$beginDate."'
					AND HED.ORD_DTE <= '".$endDate."'
					AND HED.RCV_CO_NBR = ".$CoNbrDef."
				GROUP BY DET.INV_NBR 
				) DET
				LEFT JOIN RTL.INVENTORY INV
					ON DET.INV_NBR = INV.INV_NBR
				LEFT JOIN RTL.CAT 
					ON CAT.CAT_NBR = INV.CAT_NBR
				WHERE ".$whereClauses."
						";

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

	$results['total']['TOT_SUB'] 	+= $row['TOT_SUB'];

}

//echo "<pre>"; print_r($results);

echo json_encode($results);

