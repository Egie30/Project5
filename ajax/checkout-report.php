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
$orderDetNumber	= $_GET['ORD_DET_NBR'];
$inventoryNumber= $_GET['INV_NBR'];
$Type			= $_GET['TYP'];
$CatTypeNumber	= $_GET['CAT_TYP_NBR'];


//$invoiceNumber 	= $_GET['IVC_NBR'];
$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];
	
$whereClauses 	= array("MOV.DEL_NBR = 0", "INV.DEL_NBR = 0", "HED.DEL_F = 0", "SHP.DEL_NBR = 0", "RCV.DEL_NBR = 0");
$whereDetail	= array();

if ($Type == 'ACTG') {
	$whereClauses[] 	= "DATE(MOV.CRT_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)";
	$whereClauses[] 	= "DATE(HED.ORD_DTE) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)";
	$whereClauses[] 	= "(HED.TOT_REM = 0 OR HEADER.TOT_REM = 0)";
}
else {
	$whereClauses[] 	= "DATE(MOV.CRT_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_GLBL)";
	$whereClauses[] 	= "DATE(HED.ORD_DTE) >= (SELECT BEG_ACCTG FROM NST.PARAM_GLBL)";
}

if ($orderDetNumber != "") {
	$whereClauses[] 	= "MOV.ORD_DET_NBR = ".$orderDetNumber." ";
}

if ($inventoryNumber != "") {
	$whereClauses[] 	= "INV.INV_NBR = ".$inventoryNumber." ";
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
			INV.NAME LIKE '" . $query . "'
			OR SHP.NAME LIKE '" . $query . "'
			)
		";
	}
}

$groupClauses = array();

if (!empty($_GET['CO_NBR'])) {
	$companyNumber = $_GET['CO_NBR'];
	$whereClauses[] = "HED.RCV_CO_NBR = ".$companyNumber."";
}


if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$whereClauses[] = "DAY(MOV.CRT_TS)=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(MOV.CRT_TS)= ".$months;
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(MOV.CRT_TS)= ". $years;
	}

	if ($day != "") {
		$whereClauses[] = "DAY(MOV.CRT_TS)=" . $day;
	}

	if ($month != "") {
		$whereClauses[] = "MONTH(MOV.CRT_TS)=" . $month;
	}

	if ($year != "") {
		$whereClauses[] = "YEAR(MOV.CRT_TS)=" . $year;
	}
} else {
	if (!empty($beginDate)) {
		$whereClauses[] = "DATE(MOV.CRT_TS) >= '" . $beginDate . "'";
	}

	if (!empty($endDate)) {
		$whereClauses[] = "DATE(MOV.CRT_TS) <= '" . $endDate . "'";
	}
}


if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(MOV.CRT_TS)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(MOV.CRT_TS), MONTH(MOV.CRT_TS)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(MOV.CRT_TS), MONTH(MOV.CRT_TS), DAY(MOV.CRT_TS)";
				break;
			case "CRT_TS":
				$groupClauses[] = "MOV.CRT_TS";
				break;
			case "INV_NBR":
				$groupClauses[] = "INV.INV_NBR";
				break;
			case "ORD_DET_NBR":
				$groupClauses[] = "MOV.ORD_DET_NBR";
				break;
			default:
				$groupClauses[] = "INV.INV_NBR";
				break;
		}
	}
		
	$groupClauses = implode(", ", $groupClauses);
} else {
	$groupClauses = "INV.INV_NBR";
}


if ($Accounting != 0) {
	$whereDetail[]	= "STK.ACTG_TYP = ".$Accounting." ";
}

if (!empty($CatTypeNumber)) {
	$whereDetail[]	= "SUB.CAT_TYP_NBR = ".$CatTypeNumber."";
}


$whereClauses 	= implode(" AND ", $whereClauses);
$whereDetail 	= implode(" AND ", $whereDetail);

if ($whereDetail != "") {
	$whereDetail	= " WHERE ".$whereDetail;
}

$query = "SELECT 
	STK.ORD_DET_NBR,
	STK.IVC_TYP,
	STK.REF_NBR,
	STK.HEADER_IVC_TYP,
	STK.ACTG_TYP,
	STK.INV_NBR,
	COALESCE(SUM(STK.MOV_Q),0) AS MOV_Q,
	COALESCE(SUM(STK.MOV_INV_PRC),0) AS MOV_INV_PRC,
	STK.CRT_TS,
	STK.MIN_CRT_TS,
	STK.MAX_CRT_TS,
	STK.BEG_DT,
	STK.END_DT,
	STK.BEG_TM,
	STK.END_TM,
	STK.ORD_NBR,
	STK.ORD_DTE,
	STK.ORD_DT,
	STK.INV_NAME,
	STK.PPL_NAME,
	STK.SHP_NAME, 
	STK.RCV_NAME
FROM 
(
SELECT 
	MOV.ORD_DET_NBR,
	HED.IVC_TYP,
	HED.REF_NBR,
	HEADER.IVC_TYP AS HEADER_IVC_TYP,
	(CASE WHEN HEADER.IVC_TYP IS NULL THEN HED.ACTG_TYP ELSE HEADER.ACTG_TYP END) AS ACTG_TYP,
	(CASE WHEN HEADER.IVC_TYP IS NULL THEN HED.CAT_SUB_NBR ELSE HEADER.CAT_SUB_NBR END) AS CAT_SUB_NBR,
	INV.INV_NBR,
	COALESCE(SUM(MOV.MOV_Q),0) AS MOV_Q,
	COALESCE(SUM(MOV.MOV_Q * MOV.DET_INV_PRC),0) AS MOV_INV_PRC,
	MOV.CRT_TS,
	MIN(MOV.CRT_TS) AS MIN_CRT_TS,
	MAX(MOV.CRT_TS) AS MAX_CRT_TS,
	DATE_FORMAT(MIN(MOV.CRT_TS),'%d-%m-%Y') AS BEG_DT,
	DATE_FORMAT(MAX(MOV.CRT_TS),'%d-%m-%Y') AS END_DT,
	TIME(MIN(MOV.CRT_TS)) AS BEG_TM,
	TIME(MAX(MOV.CRT_TS)) AS END_TM,
	HED.ORD_NBR,
	HED.ORD_DTE,
	DATE_FORMAT(HED.ORD_DTE,'%d-%m-%Y') AS ORD_DT,
	INV.NAME AS INV_NAME,
	PPL.NAME AS PPL_NAME,
	SHP.NAME AS SHP_NAME, 
	RCV.NAME AS RCV_NAME
FROM RTL.INV_MOV MOV
LEFT JOIN RTL.RTL_STK_DET DET 
	ON MOV.ORD_DET_NBR = DET.ORD_DET_NBR
LEFT JOIN RTL.INVENTORY INV 
	ON INV.INV_NBR = DET.INV_NBR 
LEFT JOIN RTL.RTL_STK_HEAD HED 
	ON DET.ORD_NBR = HED.ORD_NBR 
LEFT JOIN RTL.RTL_STK_HEAD HEADER 
	ON HED.REF_NBR = HEADER.ORD_NBR
LEFT JOIN CMP.COMPANY SHP 
	ON SHP.CO_NBR = HED.SHP_CO_NBR 
LEFT JOIN CMP.COMPANY RCV 
	ON RCV.CO_NBR = HED.RCV_CO_NBR
LEFT JOIN CMP.PEOPLE PPL 
	ON PPL.PRSN_NBR = MOV.CRT_NBR
WHERE ".$whereClauses."
GROUP BY MOV.ORD_DET_NBR
) STK  
LEFT JOIN RTL.CAT_SUB SUB 
	ON SUB.CAT_SUB_NBR = STK.CAT_SUB_NBR
LEFT JOIN RTL.CAT_TYP TYP 
	ON TYP.CAT_TYP_NBR = SUB.CAT_TYP_NBR
".$whereDetail."
GROUP BY STK.INV_NBR";

//echo "<pre>".$query;

$pagination = pagination($query, 10000);

$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'pagination' => $pagination,
	'total' => array(),
	'query' => $query,
);

$result = mysql_query($pagination['query']);
//echo mysql_num_rows($result);
while($row = mysql_fetch_array($result)) {

	$results['data'][] 	= $row;
	
	$results['total']['MOV_Q']			+= $row['MOV_Q'];
	$results['total']['MOV_INV_PRC']	+= $row['MOV_INV_PRC'];
	
}

//echo "<pre>"; print_r($results);

echo json_encode($results);