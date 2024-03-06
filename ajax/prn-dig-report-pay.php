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
$PaymentType	= $_GET['PYMT_TYP'];

$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];

$whereClauses = array("HED.DEL_NBR = 0", "PAY.DEL_NBR = 0 ","DATE(HED.ORD_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)","(HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY WHERE ACTG_TYP IS NULL) OR HED.BUY_CO_NBR IS NULL)");
$groupClauses = array();

$whereClauses[] = "HED.PRN_CO_NBR =" . $companyNumber;

if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$whereClauses[] = "DAY(PAY.CRT_TS)=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(PAY.CRT_TS)= ".$months;
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(PAY.CRT_TS)= ". $years;
	}

	if ($day != "") {
		$whereClauses[] = "DAY(PAY.CRT_TS)=" . $day;
	}

	if ($month != "") {
		$whereClauses[] = "MONTH(PAY.CRT_TS)=" . $month;
	}

	if ($year != "") {
		$whereClauses[] = "YEAR(PAY.CRT_TS)=" . $year;
	}
} else {
	if (!empty($beginDate)) {
		$whereClauses[] = "DATE(PAY.CRT_TS) >= '" . $beginDate . "'";
	}

	if (!empty($endDate)) {
		$whereClauses[] = "DATE(PAY.CRT_TS) <= '" . $endDate . "'";
	}
}

if ($Accounting != 0) {
	$whereClauses[]	= "HED.ACTG_TYP = ".$Accounting." ";
}

/*
if ($Accounting == 0) {
	$whereClauses[]	= "(HED.BUY_CO_NBR IS NULL OR HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_PAYROLL))";
}

if ($Accounting == 1) {
	$whereClauses[] = "HED.TAX_APL_ID IN ('I', 'A')	AND HED.BUY_CO_NBR IS NOT NULL";
}

if ($Accounting == 2) {
	$whereClauses[] = "((HED.TAX_APL_ID NOT IN ('I', 'A') AND COM.TAX_F = 1 AND HED.BUY_CO_NBR IS NOT NULL)
						OR (HED.TAX_APL_ID NOT IN ('I', 'A') AND HED.BUY_CO_NBR IS NULL))";
}

if ($Accounting == 3) {
	$whereClauses[] = "(HED.TAX_APL_ID NOT IN ('I', 'A') AND COM.TAX_F = 0 AND HED.BUY_CO_NBR IS NOT NULL)";
}
*/

if ($PaymentType != '') {
	$whereClauses[]	= "PAY.PYMT_TYP = '".$PaymentType."' ";
}

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(PAY.CRT_TS)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(PAY.CRT_TS), MONTH(PAY.CRT_TS)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(PAY.CRT_TS), MONTH(PAY.CRT_TS), DAY(PAY.CRT_TS)";
				break;
			case "PYMT_NBR":
				$groupClauses[] = "PAY.PYMT_NBR";
				break;
			case "PYMT_TYP":
				$groupClauses[] = "PAY.PYMT_TYP";
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


$query = "SELECT * FROM(
	SELECT 
		DATE(HED.ORD_TS) AS ORD_DTE,
		PAY.PYMT_NBR,
		DATE(PAY.CRT_TS) AS CSH_DTE,
		YEAR(PAY.CRT_TS) AS CSH_YEAR,
		MONTH(PAY.CRT_TS) AS CSH_MONTH,
		DAY(PAY.CRT_TS) AS CSH_DAY,
		MONTHNAME(PAY.CRT_TS) AS CSH_MONTHNAME,
		HED.ORD_NBR,
		COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
		COALESCE(SUM(PAY.TND_AMT), 0) AS TOT_AMT,
		(CASE WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
		WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
		ELSE 'Tunai' END
		) AS BUY_NAME,
		PAY.PYMT_TYP,
		BS.BNK_STMT_DTE,
		BS.BNK_STMT_NBR,
		'PRD' AS ORGN
	FROM CMP.PRN_DIG_ORD_HEAD HED
		INNER JOIN CMP.PRN_DIG_ORD_PYMT PAY ON PAY.ORD_NBR = HED.ORD_NBR	
		LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
		LEFT JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
		LEFT JOIN RTL.BNK_STMT BS ON PAY.BNK_STMT_NBR = BS.BNK_STMT_NBR		
	WHERE " . $whereClauses . " ";

if ($_GET['DEPOSIT_F'] != 1) {
	$query.= " GROUP BY " . $groupClauses . " ";
}

$query.= "
	
	UNION ALL
	
	SELECT 
		DATE(HED.ORD_TS) AS ORD_DTE,
		PAY.PYMT_NBR,
		DATE(PAY.CRT_TS) AS CSH_DTE,
		YEAR(PAY.CRT_TS) AS CSH_YEAR,
		MONTH(PAY.CRT_TS) AS CSH_MONTH,
		DAY(PAY.CRT_TS) AS CSH_DAY,
		MONTHNAME(PAY.CRT_TS) AS CSH_MONTHNAME,
		HED.ORD_NBR,
		COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
		COALESCE(SUM(PAY.TND_AMT), 0) AS TOT_AMT,
		(CASE WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
		WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
		ELSE 'Tunai' END
		) AS BUY_NAME,
		PAY.PYMT_TYP,
		BS.BNK_STMT_DTE,
		BS.BNK_STMT_NBR,
		'ARC' AS ORGN
	FROM CMP.PRN_DIG_ORD_HEAD_ARC HED
		INNER JOIN CMP.PRN_DIG_ORD_PYMT_ARC PAY ON PAY.ORD_NBR = HED.ORD_NBR	
		LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
		LEFT JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
		LEFT JOIN RTL.BNK_STMT BS ON PAY.BNK_STMT_NBR = BS.BNK_STMT_NBR		
	WHERE " . $whereClauses . " ";

if ($_GET['DEPOSIT_F'] != 1) {
	$query.= " GROUP BY " . $groupClauses . " ";
}

$query.= " 
)AS ALL_ORD
ORDER BY PYMT_NBR";

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

echo json_encode($results);

/*
UPDATE CMP.PRN_DIG_ORD_PYMT_ARC_ALL PYMT
LEFT JOIN 
(
	SELECT 
		REG.REG_NBR,
		REG.TRSC_NBR,
		REG.RTL_BRC,
		SUM(REG.TND_AMT) AS TND_AMT,
		TRSC.PYMT_TYP
	FROM
	(SELECT
		CSH.REG_NBR,
		CSH.TRSC_NBR,
		CSH.RTL_BRC,
		CSH.TND_AMT
	FROM RTL.CSH_REG CSH 
	WHERE CSH.CSH_FLO_TYP = 'FL'
		AND CSH.ACT_F = 0
		#AND MONTH(CSH.CRT_TS) = 1
		AND YEAR(CSH.CRT_TS) = 2018
	GROUP BY CSH.REG_NBR
	) REG 
	JOIN 
	(SELECT
		CSH.REG_NBR,
		CSH.TRSC_NBR,
		CSH.RTL_BRC,
		CSH.PYMT_TYP
	FROM RTL.CSH_REG CSH 
	WHERE CSH.CSH_FLO_TYP = 'PA'
		AND CSH.ACT_F = 0
		#AND MONTH(CSH.CRT_TS) = 1
		AND YEAR(CSH.CRT_TS) = 2018
	GROUP BY CSH.TRSC_NBR 
	) TRSC
	ON REG.TRSC_NBR = TRSC.TRSC_NBR
	GROUP BY REG.REG_NBR
) CASHIER
	ON PYMT.VAL_NBR = CASHIER.REG_NBR
SET PYMT.PYMT_TYP = CASHIER.PYMT_TYP
	WHERE PYMT.DEL_NBR = 0
*/

/*
#customer company

SELECT 
	SUM(PYMT.TND_AMT) AS TOT_AMT
FROM CMP.PRN_DIG_ORD_PYMT_ARC_ALL PYMT
LEFT JOIN CMP.PRN_DIG_ORD_HEAD_ARC_ALL HED 
	ON HED.ORD_NBR = PYMT.ORD_NBR 
LEFT JOIN CMP.COMPANY COM
	ON COM.CO_NBR = HED.BUY_CO_NBR 
WHERE DATE(PYMT.CRT_TS) >= '2017-01-01'
	AND DATE(PYMT.CRT_TS) <= '2017-12-31'
	AND DATE(HED.ORD_TS) >= '2017-01-01'
	AND HED.BUY_CO_NBR NOT IN (1002,271,1)
	AND HED.TAX_APL_ID NOT IN ('I', 'A')
	AND HED.DEL_NBR = 0
	AND PYMT.DEL_NBR = 0
	AND COM.TAX_F = 1
	*/
	
/*
#customer retail

SELECT 
	SUM(PYMT.TND_AMT) AS TOT_AMT
FROM CMP.PRN_DIG_ORD_PYMT_ARC_ALL PYMT
LEFT JOIN CMP.PRN_DIG_ORD_HEAD_ARC_ALL HED 
	ON HED.ORD_NBR = PYMT.ORD_NBR 
WHERE DATE(PYMT.CRT_TS) >= '2017-01-01'
	AND DATE(PYMT.CRT_TS) <= '2017-12-31'
	AND DATE(HED.ORD_TS) >= '2017-01-01'
	AND HED.BUY_CO_NBR IS NULL
	AND HED.TAX_APL_ID NOT IN ('I', 'A')
	AND HED.DEL_NBR = 0
	AND PYMT.DEL_NBR = 0
	*/

?>