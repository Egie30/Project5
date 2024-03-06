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
$paidDate		= $_GET['PAID_DT'];
$ReportType		= $_GET['RPT_TYP'];

$searchQuery    = strtoupper($_REQUEST['s']);
$groups 		= (array) $_GET['GROUP'];

$whereClauses = array("HED.DEL_NBR = 0", "DATE(HED.ORD_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)");
$groupClauses = array();


if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$whereClauses[] = "DAY(PAY.MAX_CRT_TS)=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(PAY.MAX_CRT_TS)= ".$months;
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(PAY.MAX_CRT_TS)= ". $years;
	}

	if ($day != "") {
		$whereClauses[] = "DAY(PAY.MAX_CRT_TS)=" . $day;
	}

	if ($month != "") {
		$whereClauses[] = "MONTH(PAY.MAX_CRT_TS)=" . $month;
	}

	if ($year != "") {
		$whereClauses[] = "YEAR(PAY.MAX_CRT_TS)=" . $year;
	}
} else {
	if (!empty($beginDate)) {
		$whereClauses[] = "DATE(PAY.MAX_CRT_TS) >= '" . $beginDate . "'";
	}

	if (!empty($endDate)) {
		$whereClauses[] = "DATE(PAY.MAX_CRT_TS) <= '" . $endDate . "'";
	}
}

if ($Accounting == 0) {
	$whereClauses[]	= "(HED.BUY_CO_NBR IS NULL OR (HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY)))";
}

if ($Accounting == 1) {
	$whereClauses[] = "HED.TAX_APL_ID IN ('I', 'A')";
}

if ($Accounting == 2) {
	$whereClauses[] = "((HED.TAX_APL_ID NOT IN ('I', 'A') AND COM.TAX_F = 1 AND HED.BUY_CO_NBR IS NOT NULL AND (HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY)) )
						OR (HED.TAX_APL_ID NOT IN ('I', 'A') AND HED.BUY_CO_NBR IS NULL)
						OR (COM.TAX_F = 0 AND CSH.PYMT_TYP IN ('DEB', 'CRT') AND (HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY))))";
}

if ($Accounting == 3) {
	$whereClauses[] = "(HED.TAX_APL_ID NOT IN ('I', 'A') AND COM.TAX_F = 0 AND HED.BUY_CO_NBR IS NOT NULL AND (HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY)) AND CSH.PYMT_TYP NOT IN ('DEB', 'CRT'))";
}


if ($paidDate != "") {
	$whereClauses[]	= "DATE(PAY.MAX_CRT_TS) >= '".$paidDate."' ";
}


$whereClauses[] = "HED.PRN_CO_NBR =" . $companyNumber;

if($ReportType != 'PYMT') {
	$whereClauses[]	= "PAY.TND_AMT >= HED.TOT_AMT";
}

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(PAY.MAX_CRT_TS)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(PAY.MAX_CRT_TS), MONTH(PAY.MAX_CRT_TS)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(PAY.MAX_CRT_TS), MONTH(PAY.MAX_CRT_TS), DAY(PAY.MAX_CRT_TS)";
				break;
			case "ORD_NBR":
				$groupClauses[] = "HED.ORD_NBR";
				break;
			case "PYMT_NBR":
				$groupClauses[] = "PAY.PYMT_NBR";
				$field_group1	= "PYMT.PYMT_NBR";
				break;
			default:
				$groupClauses[] = "HED.ORD_NBR";
				$field_group1	= "HED.ORD_NBR";
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
SELECT DATE(HED.ORD_TS) AS ORD_DTE,
			DATE(PAY.MAX_CRT_TS) AS CSH_DTE,
			YEAR(PAY.MAX_CRT_TS) AS CSH_YEAR,
			MONTH(PAY.MAX_CRT_TS) AS CSH_MONTH,
			DAY(PAY.MAX_CRT_TS) AS CSH_DAY,
			MONTHNAME(PAY.MAX_CRT_TS) AS CSH_MONTHNAME,
			HED.ORD_NBR,";

if ($locked == 0) {
$query.="HED.ORD_NBR_PLUS,";
}

if ($curl == 1) {
$query.="HED.*,";
}

$query.= "
			COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
			COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
			COALESCE(SUM(PAY.TND_AMT), 0) AS TND_AMT,
			(CASE WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
			WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
			ELSE 'Tunai' END 
			) AS BUY_NAME,
			PAY.PYMT_NBR,
			PAY.MAX_CRT_TS,
			CSH.PYMT_TYP,
			SUM(HED.TOT_REM) AS TOT_REM
	FROM (					SELECT 
					CSH.TRSC_NBR,
					SUM(CSH.TND_AMT) AS TND_AMT,
					CSH.PYMT_TYP,
					CSH.RTL_BRC
						FROM (SELECT 
										CSH.TRSC_NBR,
										SUM(CSH.TND_AMT) AS TND_AMT,
										PYMT.PYMT_TYP,
										CSH.RTL_BRC
									FROM 
											(	SELECT
														TRSC_NBR,
														CSH_FLO_TYP,
														PYMT_TYP,
														SUM(TND_AMT) AS TND_AMT,
														RTL_BRC
													FROM RTL.CSH_REG 
													WHERE ACT_F = 0
														AND CSH_FLO_TYP = 'FL'
														AND DATE(CRT_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)
													GROUP BY TRSC_NBR, RTL_BRC
													) CSH
												LEFT JOIN (
													SELECT
														REG.TRSC_NBR,
														REG.CSH_FLO_TYP,
														REG.PYMT_TYP,
														SUM(REG.TND_AMT) AS TND_AMT,
														PYMT.PYMT_TYP_ORD
													FROM RTL.CSH_REG REG
													LEFT JOIN RTL.PYMT_TYP AS PYMT
														ON REG.PYMT_TYP = PYMT.PYMT_TYP
													WHERE REG.ACT_F = 0
														AND REG.CSH_FLO_TYP = 'PA'
														AND DATE(REG.CRT_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)
													GROUP BY REG.TRSC_NBR
													ORDER BY PYMT.PYMT_TYP_ORD
												) PYMT ON CSH.TRSC_NBR = PYMT.TRSC_NBR
										GROUP BY CSH.TRSC_NBR, CSH.RTL_BRC
										ORDER BY PYMT.PYMT_TYP_ORD
								) CSH
								GROUP BY CSH.RTL_BRC
							) CSH
						LEFT JOIN CMP.PRN_DIG_ORD_HEAD_ARC_ALL HED
							ON CSH.RTL_BRC = HED.ORD_NBR
						LEFT JOIN CMP.COMPANY COM
							ON HED.BUY_CO_NBR = COM.CO_NBR
						LEFT JOIN CMP.PEOPLE PPL
							ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
						LEFT JOIN (
							SELECT 
								PYMT.PYMT_NBR,
								PYMT.ORD_NBR,
								SUM(PYMT.TND_AMT) AS TND_AMT,
								PYMT.CRT_TS,
								MAX(PYMT.CRT_TS) AS MAX_CRT_TS,
								COUNT(PYMT_NBR) AS CNT
							FROM CMP.PRN_DIG_ORD_PYMT_ARC_ALL PYMT
							JOIN CMP.PRN_DIG_ORD_HEAD_ARC_ALL HED
								ON PYMT.ORD_NBR = HED.ORD_NBR
							WHERE PYMT.DEL_NBR = 0
								AND HED.DEL_NBR = 0
								AND DATE(PYMT.CRT_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)
							GROUP BY ".$field_group1."
						) PAY ON PAY.ORD_NBR = HED.ORD_NBR
					WHERE  " . $whereClauses . "
				GROUP BY " . $groupClauses . "
	) PRN ON PRN.CSH_MONTH = MO.ACT_MO
	GROUP BY MO.ACT_MO
	";
}
else {

$query = "
SELECT DATE(HED.ORD_TS) AS ORD_DTE,
			DATE(PAY.MAX_CRT_TS) AS CSH_DTE,
			YEAR(PAY.MAX_CRT_TS) AS CSH_YEAR,
			MONTH(PAY.MAX_CRT_TS) AS CSH_MONTH,
			DAY(PAY.MAX_CRT_TS) AS CSH_DAY,
			MONTHNAME(PAY.MAX_CRT_TS) AS CSH_MONTHNAME,
			HED.ORD_NBR,";

if ($locked == 0) {
$query.="HED.ORD_NBR_PLUS,";
}

if ($curl == 1) {
$query.="HED.*,";
}

$query.= "COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
			COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
			COALESCE(SUM(PAY.TND_AMT), 0) AS TND_AMT,
			(CASE WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
			WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
			ELSE 'Tunai' END 
			) AS BUY_NAME,
			PAY.PYMT_NBR,
			PAY.MAX_CRT_TS,
			CSH.PYMT_TYP,
			SUM(HED.TOT_REM) AS TOT_REM
	FROM (	
				SELECT 
					CSH.TRSC_NBR,
					SUM(CSH.TND_AMT) AS TND_AMT,
					CSH.PYMT_TYP,
					CSH.RTL_BRC
						FROM (SELECT 
										CSH.TRSC_NBR,
										SUM(CSH.TND_AMT) AS TND_AMT,
										PYMT.PYMT_TYP,
										CSH.RTL_BRC
									FROM 
											(	SELECT
														TRSC_NBR,
														CSH_FLO_TYP,
														PYMT_TYP,
														SUM(TND_AMT) AS TND_AMT,
														RTL_BRC
													FROM RTL.CSH_REG 
													WHERE ACT_F = 0
														AND CSH_FLO_TYP = 'FL'
														AND DATE(CRT_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)
													GROUP BY TRSC_NBR, RTL_BRC
													) CSH
												LEFT JOIN (
													SELECT
														REG.TRSC_NBR,
														REG.CSH_FLO_TYP,
														REG.PYMT_TYP,
														SUM(REG.TND_AMT) AS TND_AMT,
														PYMT.PYMT_TYP_ORD
													FROM RTL.CSH_REG REG
													LEFT JOIN RTL.PYMT_TYP AS PYMT
														ON REG.PYMT_TYP = PYMT.PYMT_TYP
													WHERE REG.ACT_F = 0
														AND REG.CSH_FLO_TYP = 'PA'
														AND DATE(REG.CRT_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)
													GROUP BY REG.TRSC_NBR
													ORDER BY PYMT.PYMT_TYP_ORD
												) PYMT ON CSH.TRSC_NBR = PYMT.TRSC_NBR
										GROUP BY CSH.TRSC_NBR, CSH.RTL_BRC
										ORDER BY PYMT.PYMT_TYP_ORD
								) CSH
								GROUP BY CSH.RTL_BRC
				) CSH
						LEFT JOIN CMP.PRN_DIG_ORD_HEAD_ARC_ALL HED
							ON CSH.RTL_BRC = HED.ORD_NBR
						LEFT JOIN CMP.COMPANY COM
							ON HED.BUY_CO_NBR = COM.CO_NBR
						LEFT JOIN CMP.PEOPLE PPL
							ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
						LEFT JOIN (
							SELECT 
								PYMT.PYMT_NBR,
								PYMT.ORD_NBR,
								SUM(PYMT.TND_AMT) AS TND_AMT,
								PYMT.CRT_TS,
								MAX(PYMT.CRT_TS) AS MAX_CRT_TS,
								COUNT(PYMT_NBR) AS CNT
							FROM CMP.PRN_DIG_ORD_PYMT_ARC_ALL PYMT
							JOIN CMP.PRN_DIG_ORD_HEAD_ARC_ALL HED
								ON PYMT.ORD_NBR = HED.ORD_NBR
							WHERE PYMT.DEL_NBR = 0
								AND HED.DEL_NBR = 0
								AND DATE(PYMT.CRT_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)
							GROUP BY PYMT.ORD_NBR
						) PAY ON PAY.ORD_NBR = HED.ORD_NBR
					WHERE  " . $whereClauses . "
				GROUP BY " . $groupClauses . " ";
}

//echo "<pre>".$query;

$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'pagination' => $pagination,
	'total' => array()
);

$result = mysql_query($query);

while($row = mysql_fetch_array($result)) {

	$results['data'][] = $row;

	$results['total']['TOT_AMT'] 	+= $row['TOT_AMT'];

}

//echo "<pre>"; print_r($results);

/*
SELECT DATE(HED.ORD_TS) AS ORD_DTE,
			DATE(PAY.MAX_CRT_TS) AS CSH_DTE,
			YEAR(PAY.MAX_CRT_TS) AS CSH_YEAR,
			MONTH(PAY.MAX_CRT_TS) AS CSH_MONTH,
			DAY(PAY.MAX_CRT_TS) AS CSH_DAY,
			MONTHNAME(PAY.MAX_CRT_TS) AS CSH_MONTHNAME,
			HED.ORD_NBR,HED.ORD_NBR_PLUS,COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
			COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
			COALESCE(SUM(PAY.TND_AMT), 0) AS TND_AMT,
			(CASE WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
			WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
			ELSE 'Tunai' END 
			) AS BUY_NAME,
			PAY.PYMT_NBR,
			PAY.MAX_CRT_TS,
			CSH.PYMT_TYP,
			SUM(HED.TOT_REM) AS TOT_REM
	FROM (	
				SELECT 
					CSH.TRSC_NBR,
					SUM(CSH.TND_AMT) AS TND_AMT,
					CSH.PYMT_TYP,
					CSH.RTL_BRC
						FROM (SELECT 
										CSH.TRSC_NBR,
										SUM(CSH.TND_AMT) AS TND_AMT,
										PYMT.PYMT_TYP,
										CSH.RTL_BRC
									FROM 
											(	SELECT
														TRSC_NBR,
														CSH_FLO_TYP,
														PYMT_TYP,
														SUM(TND_AMT) AS TND_AMT,
														RTL_BRC
													FROM RTL.CSH_REG 
													WHERE ACT_F = 0
														AND CSH_FLO_TYP = 'FL'
														AND DATE(CRT_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)
													GROUP BY TRSC_NBR
													) CSH
												LEFT JOIN (
													SELECT
														REG.TRSC_NBR,
														REG.CSH_FLO_TYP,
														REG.PYMT_TYP,
														SUM(REG.TND_AMT) AS TND_AMT,
														PYMT.PYMT_TYP_ORD
													FROM RTL.CSH_REG REG
													LEFT JOIN RTL.PYMT_TYP AS PYMT
														ON REG.PYMT_TYP = PYMT.PYMT_TYP
													WHERE REG.ACT_F = 0
														AND REG.CSH_FLO_TYP = 'PA'
														AND DATE(REG.CRT_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)
													GROUP BY REG.TRSC_NBR
													ORDER BY PYMT.PYMT_TYP_ORD
												) PYMT ON CSH.TRSC_NBR = PYMT.TRSC_NBR
										GROUP BY CSH.TRSC_NBR
										ORDER BY PYMT.PYMT_TYP_ORD
								) CSH
								GROUP BY CSH.TRSC_NBR
				) CSH
						LEFT JOIN CMP.PRN_DIG_ORD_HEAD_ARC_ALL HED
							ON CSH.RTL_BRC = HED.ORD_NBR
						LEFT JOIN CMP.COMPANY COM
							ON HED.BUY_CO_NBR = COM.CO_NBR
						LEFT JOIN CMP.PEOPLE PPL
							ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
						LEFT JOIN (
							SELECT 
								PYMT.PYMT_NBR,
								PYMT.ORD_NBR,
								SUM(PYMT.TND_AMT) AS TND_AMT,
								PYMT.CRT_TS,
								MAX(PYMT.CRT_TS) AS MAX_CRT_TS,
								COUNT(PYMT_NBR) AS CNT
							FROM CMP.PRN_DIG_ORD_PYMT_ARC_ALL PYMT
							JOIN CMP.PRN_DIG_ORD_HEAD_ARC_ALL HED
								ON PYMT.ORD_NBR = HED.ORD_NBR
							WHERE PYMT.DEL_NBR = 0
								AND HED.DEL_NBR = 0
								AND DATE(PYMT.CRT_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)
							GROUP BY PYMT.PYMT_NBR
						) PAY ON PAY.ORD_NBR = HED.ORD_NBR
					WHERE  HED.DEL_NBR = 0 AND DATE(HED.ORD_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC) AND (HED.BUY_CO_NBR IS NULL OR (HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY))) AND HED.PRN_CO_NBR =271
				GROUP BY CSH.TRSC_NBR
*/

echo json_encode($results);