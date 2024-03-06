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
//$invoiceNumber 	= $_GET['IVC_NBR'];
$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];

$whereClauses = array("HED.DEL_NBR = 0", "DATE(HED.ORD_TS) >= (SELECT BEG_ACCTG FROM 

NST.PARAM_LOC)", "(HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY WHERE 

ACTG_TYP IS NULL) OR HED.BUY_CO_NBR IS NULL)");

$groupClauses = array();

$whereClauses[] = "HED.PRN_CO_NBR =" . $companyNumber;

if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$whereClauses[] = "DAY(HED.PU_TS)=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(HED.PU_TS)= ".$months;
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(HED.PU_TS)= ". $years;
	}

	if ($day != "") {
		$whereClauses[] = "DAY(HED.PU_TS)=" . $day;
	}

	if ($month != "") {
		$whereClauses[] = "MONTH(HED.PU_TS)=" . $month;
	}

	if ($year != "") {
		$whereClauses[] = "YEAR(HED.PU_TS)=" . $year;
	}
} else {
	if (!empty($beginDate)) {
		$whereClauses[] = "DATE(HED.PU_TS) >= '" . $beginDate . "'";
	}

	if (!empty($endDate)) {
		$whereClauses[] = "DATE(HED.PU_TS) <= '" . $endDate . "'";
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
				$groupClauses[] = "YEAR(HED.PU_TS)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(HED.PU_TS), MONTH(HED.PU_TS)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(HED.PU_TS), MONTH(HED.PU_TS), DAY

(HED.PU_TS)";
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
			
//=========================================			


if ($_GET['RL_TYP'] == 'RL_YEAR') {
	
$query = "SELECT MO.*, 
	PRN.CSH_MONTH,  
	COALESCE(SUM(PRN.TOT_AMT), 0) AS TOT_AMT 
FROM (
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
	SELECT 12 AS ACT_MO, 'Desember' AS ACT_MO_NAME)
	MO
	LEFT JOIN (
		SELECT 
			DATE(HED.ORD_TS) AS ORD_DTE,
			DATE(HED.PU_TS) AS PU_DTE,
			DATE(HED.PU_TS) AS CSH_DTE,
			YEAR(HED.PU_TS) AS CSH_YEAR,
			MONTH(HED.PU_TS) AS CSH_MONTH,
			DAY(HED.PU_TS) AS CSH_DAY,
			MONTHNAME(HED.PU_TS) AS CSH_MONTHNAME,
			HED.ORD_NBR,
			COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
			COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
			(CASE 
				WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
				WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
				ELSE 'Tunai' END 
			) AS BUY_NAME
		FROM CMP.PRN_DIG_ORD_HEAD_ARC_ALL HED
			LEFT OUTER JOIN CMP.COMPANY COM
				ON HED.BUY_CO_NBR = COM.CO_NBR
			LEFT OUTER JOIN CMP.PEOPLE PPL
				ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
		WHERE " . $whereClauses . "
		GROUP BY " . $groupClauses . "
	) PRN ON PRN.CSH_MONTH = MO.ACT_MO
GROUP BY MO.ACT_MO";

}
else {

$query = "SELECT * FROM(
	SELECT 
		HED.PU_TS,
		DATE(HED.ORD_TS) AS ORD_DTE,
		DATE(HED.PU_TS) AS PU_DTE,
		DATE(HED.PU_TS) AS CSH_DTE,
		YEAR(HED.PU_TS) AS CSH_YEAR,
		MONTH(HED.PU_TS) AS CSH_MONTH,
		DAY(HED.PU_TS) AS CSH_DAY,
		MONTHNAME(HED.PU_TS) AS CSH_MONTHNAME,
		HED.ORD_NBR,
		COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
		SUM(COALESCE((
		CASE WHEN HED.TAX_APL_ID IN ('I','A')
				THEN (HED.TOT_AMT)/11 
			ELSE HED.TAX_AMT
			END
		),0)) AS TAX_AMT,
		SUM(COALESCE((
		CASE WHEN HED.TAX_APL_ID IN ('I','A')
				THEN (HED.TOT_AMT)/1.1 
			ELSE HED.TOT_AMT
			END
		),0)) AS SUBTOTAL,
		SUM(COALESCE(HED.TOT_AMT,0)) AS TOT_AMT,
		(CASE WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
		WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
		ELSE 'Tunai' END
		) AS BUY_NAME,
		HED.TAX_APL_ID,
		TAX.TAX_APL_DESC,
		'PRD' AS ORGN
	FROM CMP.PRN_DIG_ORD_HEAD HED
		LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
		LEFT JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
		LEFT JOIN CMP.TAX_APL TAX ON TAX.TAX_APL_ID = HED.TAX_APL_ID
	WHERE " . $whereClauses . "
	GROUP BY " . $groupClauses . " 

	UNION ALL

	SELECT 
		HED.PU_TS,
		DATE(HED.ORD_TS) AS ORD_DTE,
		DATE(HED.PU_TS) AS PU_DTE,
		DATE(HED.PU_TS) AS CSH_DTE,
		YEAR(HED.PU_TS) AS CSH_YEAR,
		MONTH(HED.PU_TS) AS CSH_MONTH,
		DAY(HED.PU_TS) AS CSH_DAY,
		MONTHNAME(HED.PU_TS) AS CSH_MONTHNAME,
		HED.ORD_NBR,
		COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
		SUM(COALESCE((
		CASE WHEN HED.TAX_APL_ID IN ('I','A')
				THEN (HED.TOT_AMT)/11 
			ELSE HED.TAX_AMT
			END
		),0)) AS TAX_AMT,
		SUM(COALESCE((
		CASE WHEN HED.TAX_APL_ID IN ('I','A')
				THEN (HED.TOT_AMT)/1.1 
			ELSE HED.TOT_AMT
			END
		),0)) AS SUBTOTAL,
		SUM(COALESCE(HED.TOT_AMT,0)) AS TOT_AMT,
		(CASE WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
		WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
		ELSE 'Tunai' END
		) AS BUY_NAME,
		HED.TAX_APL_ID,
		TAX.TAX_APL_DESC,
		'ARC' AS ORGN
	FROM CMP.PRN_DIG_ORD_HEAD_ARC HED
		LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
		LEFT JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
		LEFT JOIN CMP.TAX_APL TAX ON TAX.TAX_APL_ID = HED.TAX_APL_ID
	WHERE " . $whereClauses . "
	GROUP BY " . $groupClauses . "
)AS ORD 
#GROUP BY ORD_NBR
ORDER BY PU_TS";
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

	$results['total']['SUBTOTAL'] 	+= $row['SUBTOTAL'];
	$results['total']['TAX_AMT'] 	+= $row['TAX_AMT'];
	$results['total']['TOT_AMT'] 	+= $row['TOT_AMT'];
		
	
}

echo json_encode($results);

/*
SELECT DATE(HED.ORD_TS) AS ORD_DTE,
			DATE(HED.PU_TS) AS CSH_DTE,
			#DATE(HED.PAID_DT) AS PAID_DTE,
			HED.ORD_NBR,
			COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
			COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
			(CASE WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
			WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
			ELSE 'Tunai' END 
		) AS BUY_NAME
		FROM CMP.PRN_DIG_ORD_HEAD_ARC_ALL HED
			LEFT OUTER JOIN CMP.COMPANY COM
				ON HED.BUY_CO_NBR = COM.CO_NBR
			LEFT OUTER JOIN CMP.PEOPLE PPL
				ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
				WHERE HED.DEL_NBR = 0 AND DATE(HED.ORD_TS) >= (SELECT 

BEG_ACCTG FROM NST.PARAM_LOC) AND HED.PRN_CO_NBR =271 AND HED.ACTG_TYP = 2 
				AND MONTH(HED.PU_TS) = 1
				AND YEAR(HED.PU_TS) = 2018
			GROUP BY HED.ORD_NBR
*/