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

if (($locked == 1) || ($_COOKIE["LOCK"] == "LOCK")) {
	$field	= "HED.PYMT_REM_TS";
}
else {
	$field 	= "HED.PYMT_REM_TS";
}

$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];

$whereClauses = array("HED.DEL_F = 0", "DATE(HED.ORD_DTE) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)", "HED.TOT_REM = 0");
$groupClauses = array();

if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$whereClauses[] = "DAY(".$field.")=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(".$field.")= ".$months;
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(".$field.")= ". $years;
	}

	if ($day != "") {
		$whereClauses[] = "DAY(".$field.")=" . $day;
	}

	if ($month != "") {
		$whereClauses[] = "MONTH(".$field.")=" . $month;
	}

	if ($year != "") {
		$whereClauses[] = "YEAR(".$field.")=" . $year;
	}
} else {
	if (!empty($beginDate)) {
		$whereClauses[] = "DATE(".$field.") >= '" . $beginDate . "'";
	}

	if (!empty($endDate)) {
		$whereClauses[] = "DATE(".$field.") <= '" . $endDate . "'";
	}
}

if ($CatSubNbr != "") {
	$whereClauses[] = "HED.CAT_SUB_NBR = ".$CatSubNbr." ";
}

$query_co	= "SELECT 
				(CASE WHEN ACTG_TYP = 1 AND CO_NBR_DEF = '".$CoNbrDef."' THEN CO_NBR ELSE '' END) AS COM_PT,
				(CASE WHEN ACTG_TYP = 2 AND CO_NBR_DEF = '".$CoNbrDef."' THEN CO_NBR ELSE '' END) AS COM_CV,
				(CASE WHEN ACTG_TYP = 3 AND CO_NBR_DEF = '".$CoNbrDef."' THEN CO_NBR ELSE '' END) AS COM_PR
				FROM NST.PARAM_PAYROLL";
$result_co	= mysql_query($query_co);
while ($row_co		= mysql_fetch_array($result_co)) {

	if ($row_co['COM_PT'] != '') { $CompanyPT = $row_co['COM_PT']; }
	if ($row_co['COM_CV'] != '') { $CompanyCV = $row_co['COM_CV']; }
	if ($row_co['COM_PR'] != '') { $CompanyPR = $row_co['COM_PR']; }
}

if ($Accounting == 1) {
	$whereClauses[] = "HED.RCV_CO_NBR = ".$CompanyPT." ";
}
	
if ($Accounting == 2) {
	$whereClauses[] = "HED.RCV_CO_NBR = ".$CompanyCV." ";
}

if ($Accounting == 3) {
	$whereClauses[] = "HED.RCV_CO_NBR = ".$CompanyPR." ";
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


if ($_GET['RL_TYP'] == 'RL_YEAR') {
	
$query	= "SELECT MO.*,
		PRN.CSH_MONTH,  
		COALESCE(SUM(PRN.TOT_AMT), 0) AS TOT_AMT 
	FROM
	(
	SELECT 1 AS ACT_MO, 'Januari' AS ACT_MO_NAME UNION 
	SELECT 2 AS ACT_MO, 'Februari' AS ACT_MO_NAME UNION 
	SELECT 3 AS ACT_MO, 'Maret' AS ACT_MO_NAME UNION
	SELECT 4 AS ACT_MO, 'April' AS ACT_MO_NAME UNION
	SELECT 5 AS ACT_MO, 'Mei' AS ACT_MO_NAME UNION
	SELECT 6 AS ACT_MO, 'Juni' AS ACT_MO_NAME UNION
	SELECT 7 AS ACT_MO, 'Juli' AS ACT_MO_NAME UNION
	SELECT 8 AS ACT_MO, 'Agustus' AS ACT_MO_NAME UNION
	SELECT 9 AS ACT_MO, 'September' AS ACT_MO_NAME UNION
	SELECT 10 AS ACT_MO, 'Oktober' AS ACT_MO_NAME UNION
	SELECT 11 AS ACT_MO, 'November' AS ACT_MO_NAME UNION
	SELECT 12 AS ACT_MO, 'Desember' AS ACT_MO_NAME
	)
	MO
	LEFT JOIN 
	(	SELECT 
					HED.ORD_NBR,
					HED.CAT_SUB_NBR,
					SUB.CAT_SUB_DESC,
					TYP.CAT_TYP,
					SUM(HED.TOT_AMT) AS TOT_AMT,
					DATE(HED.PYMT_REM_TS) AS ORD_DTE,
					MONTH(HED.PYMT_REM_TS) AS ORD_MONTH,
					YEAR(HED.PYMT_REM_TS) AS ORD_YEAR,
					MONTHNAME(HED.PYMT_REM_TS) AS ORD_MONTHNAME,
					SHP.NAME AS SHIPPER,
					RCV.NAME AS RECEIVER
				FROM RTL.RTL_STK_HEAD HED 
				LEFT JOIN RTL.CAT_SUB SUB 
					ON SUB.CAT_SUB_NBR = HED.CAT_SUB_NBR
				LEFT JOIN RTL.CAT_TYP TYP 
					ON TYP.CAT_TYP_NBR = SUB.CAT_TYP_NBR
				LEFT JOIN CMP.COMPANY SHP 
					ON SHP.CO_NBR = HED.SHP_CO_NBR
				LEFT JOIN CMP.COMPANY RCV
					ON RCV.CO_NBR = HED.RCV_CO_NBR
				WHERE " . $whereClauses . "
				GROUP BY " . $groupClauses . "
				) STK ON STK.ORD_MONTH = MO.ACT_MO
			GROUP BY MO.ACT_MO
			";
}
else {

		$query 	= "SELECT 
					HED.ORD_NBR,
					HED.CAT_SUB_NBR,
					SUB.CAT_SUB_DESC,
					TYP.CAT_TYP,
					SUM(HED.TOT_AMT) AS TOT_AMT,
					DATE(HED.PYMT_REM_TS) AS ORD_DTE,
					MONTH(HED.PYMT_REM_TS) AS ORD_MONTH,
					YEAR(HED.PYMT_REM_TS) AS ORD_YEAR,
					MONTHNAME(HED.PYMT_REM_TS) AS ORD_MONTHNAME,
					SHP.NAME AS SHIPPER,
					RCV.NAME AS RECEIVER
				FROM RTL.RTL_STK_HEAD HED 
				LEFT JOIN RTL.CAT_SUB SUB 
					ON SUB.CAT_SUB_NBR = HED.CAT_SUB_NBR
				LEFT JOIN RTL.CAT_TYP TYP 
					ON TYP.CAT_TYP_NBR = SUB.CAT_TYP_NBR
				LEFT JOIN CMP.COMPANY SHP 
					ON SHP.CO_NBR = HED.SHP_CO_NBR
				LEFT JOIN CMP.COMPANY RCV
					ON RCV.CO_NBR = HED.RCV_CO_NBR
				WHERE " . $whereClauses . "
				GROUP BY " . $groupClauses . "
						";


}

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

	$results['total']['TOT_AMT'] 	+= $row['TOT_AMT'];

}

//echo "<pre>"; print_r($results);

echo json_encode($results);