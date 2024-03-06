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
$PaymentMethod	= $_GET['PYMT_METHOD'];


if (empty($_GET['BEG_DT'])) {
	$beginDate = date('Y-m-01');
}

if (empty($_GET['END_DT'])) {
	$endDate = date('Y-m-d');
}


$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];

$whereClauses = array("HED.DEL_F = 0", "DATE(HED.ORD_DTE) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)", "HED.IVC_TYP = '".$InvoiceType."' ");
$groupClauses = array();

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

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(".$field.")";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(".$field."), MONTH(".$field.")";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(".$field."), MONTH(".$field."), DAY(".$field.")";
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



$query	= "SELECT 
					HED.ORD_NBR,
					HED.ORD_DTE,
					DATE(HED.DL_TS) AS DL_DTE,
					HED.CAT_SUB_NBR,
					SUB.CAT_SUB_DESC,
					TYP.CAT_TYP,
					SUM(PYMT_DOWN) AS TOT_AMT,
					DATE(PYMT_DOWN_TS) AS PAID_DTE,
					SHP.NAME AS SHIPPER,
					RCV.NAME AS RECEIVER,
					SHP.NAME AS ORD_TTL,
					HED.TOT_REM,
					HED.ACTG_TYP,
					HED.PYMT_TYP,
					TYP.CAT_TYP_NBR,
					SUB.CD_SUB_NBR AS AKUN,
					PYMT.PYMT_DESC,
					TAX_IVC_NBR,
					TAX_IVC_DTE
				FROM RTL.RTL_STK_HEAD HED 
				LEFT JOIN RTL.CAT_SUB SUB 
					ON SUB.CAT_SUB_NBR = HED.CAT_SUB_NBR
				LEFT JOIN CMP.COMPANY SHP 
					ON SHP.CO_NBR = HED.SHP_CO_NBR
				LEFT JOIN CMP.COMPANY RCV
					ON RCV.CO_NBR = HED.RCV_CO_NBR
				LEFT JOIN RTL.PYMT_TYP PYMT
					ON PYMT.PYMT_TYP = HED.PYMT_TYP
				INNER JOIN RTL.CAT_TYP TYP 
					ON TYP.CAT_TYP_NBR = SUB.CAT_TYP_NBR
				WHERE HED.PYMT_DOWN IS NOT NULL
					AND DATE(HED.PYMT_DOWN_TS) >= '".$beginDate."'
					AND DATE(HED.PYMT_DOWN_TS) <= '".$endDate."'
					AND ".$whereClauses."
				GROUP BY HED.ORD_NBR
				
				UNION 
				
				SELECT 
					HED.ORD_NBR,
					HED.ORD_DTE,
					DATE(HED.DL_TS) AS DL_DTE,
					HED.CAT_SUB_NBR,
					SUB.CAT_SUB_DESC,
					TYP.CAT_TYP,
					SUM(PYMT_REM) AS TOT_AMT,
					DATE(PYMT_REM_TS) AS PAID_DTE,
					SHP.NAME AS SHIPPER,
					RCV.NAME AS RECEIVER,
					SHP.NAME AS ORD_TTL,
					HED.TOT_REM,
					HED.ACTG_TYP,
					HED.PYMT_TYP,
					TYP.CAT_TYP_NBR,
					SUB.CD_SUB_NBR AS AKUN,
					PYMT.PYMT_DESC,
					TAX_IVC_NBR,
					TAX_IVC_DTE
				FROM RTL.RTL_STK_HEAD HED 
				LEFT JOIN RTL.CAT_SUB SUB 
					ON SUB.CAT_SUB_NBR = HED.CAT_SUB_NBR
				LEFT JOIN CMP.COMPANY SHP 
					ON SHP.CO_NBR = HED.SHP_CO_NBR
				LEFT JOIN CMP.COMPANY RCV
					ON RCV.CO_NBR = HED.RCV_CO_NBR
				LEFT JOIN RTL.PYMT_TYP PYMT
					ON PYMT.PYMT_TYP = HED.PYMT_TYP
				INNER JOIN RTL.CAT_TYP TYP 
					ON TYP.CAT_TYP_NBR = SUB.CAT_TYP_NBR
				WHERE HED.PYMT_REM IS NOT NULL
					AND DATE(HED.PYMT_REM_TS) >= '".$beginDate."'
					AND DATE(HED.PYMT_REM_TS) <= '".$endDate."'
					AND ".$whereClauses."
				GROUP BY HED.ORD_NBR";

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

	//print_r($row);
	//echo "<br />";
	
	$results['data'][] = $row;

	$results['total']['TOT_AMT'] 	+= $row['TOT_AMT'];
	$results['total']['TOT_REM'] 	+= $row['TOT_REM'];

}


echo json_encode($results);

