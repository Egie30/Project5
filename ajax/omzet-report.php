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

if (($locked == 1) || ($_COOKIE["LOCK"] == "LOCK")) {
	$field	= "HED.ORD_TS";
}
else {
	$field 	= "HED.PAID_DT";
}

$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];

$whereClauses = array("HED.DEL_NBR = 0", "DATE(HED.ORD_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)", "".$field." IS NOT NULL","(HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY) OR HED.BUY_CO_NBR IS NULL)");
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

if ($Accounting != 0) {
	$whereClauses[] = "HED.ACTG_TYP = ".$Accounting." ";
}
	
$whereClauses[] = "HED.PRN_CO_NBR =" . $companyNumber;

 
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
	(
			SELECT 
					DATE(HED.ORD_TS) AS ORD_DTE,
					DATE(".$field.") AS CSH_DTE,
					YEAR(".$field.") AS CSH_YEAR,
					MONTH(".$field.") AS CSH_MONTH,
					DAY(".$field.") AS CSH_DAY,
					MONTHNAME(".$field.") AS CSH_MONTHNAME,
					HED.ORD_NBR,";
					
		if (($locked == 0) && ($_COOKIE["LOCK"] != "LOCK")){
		$query.="HED.ORD_NBR_PLUS,";
		}
				
		$query.= "COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
					COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
					(CASE WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
					WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
					ELSE 'Tunai' END 
					) AS BUY_NAME
		FROM CMP.PRN_DIG_ORD_HEAD HED 
		LEFT JOIN CMP.COMPANY COM
			ON HED.BUY_CO_NBR = COM.CO_NBR 
		LEFT JOIN CMP.PEOPLE PPL 
			ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR 
		WHERE " . $whereClauses . "
		GROUP BY " . $groupClauses . "
			) PRN ON PRN.CSH_MONTH = MO.ACT_MO
			GROUP BY MO.ACT_MO
			";
}
else {

		$query 	= "SELECT 
					DATE(HED.ORD_TS) AS ORD_DTE,
					DATE(".$field.") AS CSH_DTE,
					YEAR(".$field.") AS CSH_YEAR,
					MONTH(".$field.") AS CSH_MONTH,
					DAY(".$field.") AS CSH_DAY,
					MONTHNAME(".$field.") AS CSH_MONTHNAME,
					HED.ORD_NBR,";
					
		if ($locked == 0) {
		$query.="HED.ORD_NBR_PLUS,";
		}
				
		$query.= "COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
					COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
					(CASE WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
					WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
					ELSE 'Tunai' END 
					) AS BUY_NAME
		FROM CMP.PRN_DIG_ORD_HEAD HED 
		LEFT JOIN CMP.COMPANY COM
			ON HED.BUY_CO_NBR = COM.CO_NBR 
		LEFT JOIN CMP.PEOPLE PPL 
			ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR 
		WHERE " . $whereClauses . "
		GROUP BY " . $groupClauses . "
		";


}

/*
SELECT 
			DATE(HED.ORD_TS) AS ORD_DTE,
			DATE(".$field.") AS CSH_DTE,
			YEAR(".$field.") AS CSH_YEAR,
			MONTH(".$field.") AS CSH_MONTH,
			DAY(".$field.") AS CSH_DAY,
			MONTHNAME(".$field.") AS CSH_MONTHNAME,
			HED.ORD_NBR,
			HED.ORD_NBR_PLUS,
			COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
			COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
			(CASE WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
			WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
			ELSE 'Tunai' END 
			) AS BUY_NAME
FROM CMP.PRN_DIG_ORD_HEAD HED 
LEFT JOIN CMP.COMPANY COM
	ON HED.BUY_CO_NBR = COM.CO_NBR 
LEFT JOIN CMP.PEOPLE PPL 
	ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR 
WHERE HED.ACTG_TYP = 2
	AND ".$field." IS NOT NULL
GROUP BY YEAR(".$field."), MONTH(".$field.")
*/

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