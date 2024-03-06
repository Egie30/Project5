<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

/*
Don't forget to update TAX_F on CMP.COMPANY
*/

$CatSubNbr		= $_GET['CAT_SUB_NBR'];
$CatNbr			= $_GET['CAT_NBR'];
$CatSubTyp		= $_GET['CAT_SUB_TYP'];

$Type			= $_GET['TYP'];
$IvcTyp			= $_GET['IVC_TYP'];
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
$groups = (array) $_GET['GROUP'];

if (!empty($_GET['CO_NBR'])) {
	$companyNumber = $_GET['CO_NBR'];
}

$whereClauses = array("DATE(HED.DL_TS) >= (SELECT BEG_RPT FROM NST.PARAM_LOC)");
$groupClauses = array();

if($IvcTyp != ""){
	$whereClauses[] = "HED.IVC_TYP = '" . $IvcTyp ."' ";
}
else {
	$whereClauses[] = "HED.IVC_TYP IN ('RC', 'RT') ";
}

if($Type != '') {
	if($Type == 'PRNDIG') {	$whereClauses[] = "CAT.CAT_NBR IN (1,10)"; $whereClauses[] = "SUB.CAT_SUB_NBR NOT IN (202)"; }
	if($Type == 'RTL') {	$whereClauses[] = "CAT.CAT_NBR NOT IN(9, 10, 12)"; }
}


if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$whereClauses[] = "DAY(HED.DL_TS)=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(HED.DL_TS)= ".$months;
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(HED.DL_TS)= ". $years;
	}
	
	if ($day != "") {
		$whereClauses[] = "DAY(HED.DL_TS)=" . $day;
	}

	if ($month != "") {
		$whereClauses[] = "MONTH(HED.DL_TS)=" . $month;
	}

	if ($year != "") {
		$whereClauses[] = "YEAR(HED.DL_TS)=" . $year;
	}
} else {
	if (!empty($beginDate)) {
		$whereClauses[] = "DATE(HED.DL_TS) >= '" . $beginDate . "'";
	}

	if (!empty($endDate)) {
		$whereClauses[] = "DATE(HED.DL_TS) <= '" . $endDate . "'";
	}
	
}



if(!empty($CatNbr)) {
	$whereClauses[] = "CAT.CAT_NBR IN (".$CatNbr.") ";
}

if ($Accounting == 1) {
	$whereClauses[] = "HED.TAX_APL_ID IN ('I', 'A')";
}

if ($Accounting == 2) {
	
	$whereClauses[] = "((HED.IVC_TYP = 'RC' AND HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 1)
		OR (HED.IVC_TYP = 'RT' AND HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 1))";
		
	/*
	if($IvcTyp == 'RC') {
		$whereClauses[] = "HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 1";
	}
	else if ($IvcTyp == 'RT') {
		$whereClauses[] = "HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 1";
	}
	*/
}

if ($Accounting == 3) {
	
	$whereClauses[] = "((HED.IVC_TYP = 'RC' AND HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 0)
		OR (HED.IVC_TYP = 'RT' AND HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 0))";
		
	/*
	if($IvcTyp == 'RC') {
		$whereClauses[] = "HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 0";
	}
	else if($IvcTyp == 'RT') {
		$whereClauses[] = "HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 0";
	}
	*/
}



if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(HED.DL_TS)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(HED.DL_TS), MONTH(HED.DL_TS)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(HED.DL_TS), MONTH(HED.DL_TS), DAY(HED.DL_TS)";
				break;
			case "CAT_SUB_NBR":
				$groupClauses[] = "SUB.CAT_SUB_NBR";
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

if(!empty($CatSubTyp)) {
	if($CatSubTyp == 'CLICK') {
		$whereClauses[] = "INV.CAT_SUB_NBR = ".$CatSubNbr." ";
		$innerJoin		= "INV.CAT_SUB_NBR = SUB.CAT_SUB_NBR";
	}
	else if ($CatSubTyp == 'COST') {
		$whereClauses[] = "(CAT.CAT_NBR IN (12,16,17) OR SUB.CAT_SUB_NBR = 241) ";
		$innerJoin		= "HED.CAT_SUB_NBR = SUB.CAT_SUB_NBR";
	}
}
else {
	if(!empty($CatSubNbr)) {
		$whereClauses[] = "SUB.CAT_SUB_NBR = ".$CatSubNbr." ";
		$innerJoin		= "INV.CAT_SUB_NBR = SUB.CAT_SUB_NBR";
	}
	
	$innerJoin		= "INV.CAT_SUB_NBR = SUB.CAT_SUB_NBR";
}


$whereClauses = implode(" AND ", $whereClauses);
$groupClauses = implode(", ", $groupClauses);

if($_GET['RL_TYP'] == 'RL_YEAR') {
	
	$query	= "SELECT MO.*,
		RCV.ORD_MONTH,  
		COALESCE(SUM(RCV.RCV_Q), 0) AS RCV_Q,
		COALESCE(SUM(RCV.RCV_TOT_SUB), 0) AS RCV_TOT_SUB,
		COALESCE(SUM(RCV.RTR_Q), 0) AS RTR_Q,
		COALESCE(SUM(RCV.RTR_TOT_SUB), 0) AS RTR_TOT_SUB,
		COALESCE(SUM(RCV.COR_Q), 0) AS COR_Q,
		COALESCE(SUM(RCV.COR_TOT_SUB), 0) AS COR_TOT_SUB
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
			HED.ORD_NBR,
			HED.SPL_NAME,
			HED.RCV_NAME,
			DATE(HED.DL_TS) AS ORD_DTE,
			YEAR(HED.DL_TS) AS ORD_YEAR,
			MONTH(HED.DL_TS) AS ORD_MONTH,
			DAY(HED.DL_TS) AS ORD_DAY,
			MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME,
			HED.CAT_SUB_NBR,
			HED.CAT_SUB_DESC,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN HED.ORD_Q ELSE 0 END),0) AS RCV_Q,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN HED.TOT_SUB ELSE 0 END),0) AS RCV_TOT_SUB,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'RT' THEN HED.ORD_Q ELSE 0 END),0) AS RTR_Q,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'RT' THEN HED.TOT_SUB ELSE 0 END),0) AS RTR_TOT_SUB,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN HED.ORD_Q ELSE 0 END),0) AS COR_Q,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN HED.TOT_SUB ELSE 0 END),0) AS COR_TOT_SUB,
			COALESCE(SUM(HED.TOT_AMT),0) AS TOT_AMT,
			COALESCE(SUM(HED.TOT_REM),0) AS TOT_REM
		FROM (
			SELECT 
				HED.ORD_NBR,
				HED.DL_TS,
				HED.TOT_AMT,
				HED.TOT_REM,
				HED.IVC_TYP,
				SUB.CAT_SUB_NBR,
				SUB.CAT_SUB_DESC,
				HED.TAX_APL_ID,
				HED.SHP_CO_NBR,
				HED.RCV_CO_NBR,
				COALESCE(SUM(DET.ORD_Q),0) AS ORD_Q,
				COALESCE(SUM(DET.TOT_SUB),0) AS TOT_SUB,
				HED.PYMT_DOWN,
				HED.PYMT_REM,
				SPL.NAME AS SPL_NAME,
				RCV.NAME AS RCV_NAME
			FROM RTL.RTL_STK_DET DET
			LEFT JOIN RTL.RTL_STK_HEAD HED
				ON DET.ORD_NBR = HED.ORD_NBR
			INNER JOIN RTL.INVENTORY INV
				ON DET.INV_NBR = INV.INV_NBR
			INNER JOIN RTL.CAT_SUB SUB
				ON ".$innerJoin."
			INNER JOIN RTL.CAT CAT
				ON CAT.CAT_NBR = SUB.CAT_NBR
			LEFT JOIN CMP.PRN_DIG_TYP TYP
				ON INV.PRD_PRC_TYP = TYP.PRN_DIG_TYP
			INNER JOIN CMP.COMPANY SPL
				ON SPL.CO_NBR = HED.SHP_CO_NBR
			INNER JOIN CMP.COMPANY RCV
				ON RCV.CO_NBR = HED.RCV_CO_NBR
			WHERE ".$whereClauses."
			GROUP BY HED.ORD_NBR
		) HED
		INNER JOIN CMP.COMPANY SPL
			ON SPL.CO_NBR = HED.SHP_CO_NBR
		INNER JOIN CMP.COMPANY RCV
			ON RCV.CO_NBR = HED.RCV_CO_NBR
		GROUP BY ".$groupClauses."
		) RCV ON RCV.ORD_MONTH = MO.ACT_MO
	GROUP BY MO.ACT_MO";
}
else {

/*
$query = "SELECT
			HED.ORD_NBR,
			SPL.NAME AS SPL_NAME,
			RCV.NAME AS RCV_NAME,
			DATE(HED.DL_TS) AS ORD_DTE,
			YEAR(HED.DL_TS) AS ORD_YEAR,
			MONTH(HED.DL_TS) AS ORD_MONTH,
			DAY(HED.DL_TS) AS ORD_DAY,
			MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME,
			SUB.CAT_SUB_NBR,
			SUB.CAT_SUB_DESC,
			COALESCE(SUM(DET.INV_PRC),0) AS DET_INV_PRC,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN DET.ORD_Q ELSE 0 END),0) AS RCV_Q,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN DET.TOT_SUB ELSE 0 END),0) AS RCV_TOT_SUB,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'RT' THEN DET.ORD_Q ELSE 0 END),0) AS RTR_Q,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'RT' THEN DET.TOT_SUB ELSE 0 END),0) AS RTR_TOT_SUB,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN DET.ORD_Q ELSE 0 END),0) AS COR_Q,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN DET.TOT_SUB ELSE 0 END),0) AS COR_TOT_SUB,
			COALESCE(SUM(HED.TOT_AMT),0) AS TOT_AMT,
			COALESCE(SUM(HED.TOT_REM),0) AS TOT_REM
		FROM RTL.RTL_STK_DET DET
		INNER JOIN (
			SELECT 
				ORD_NBR,
				DL_TS,
				TOT_AMT,
				TOT_REM,
				IVC_TYP,
				CAT_SUB_NBR,
				TAX_APL_ID,
				SHP_CO_NBR,
				RCV_CO_NBR
			FROM RTL.RTL_STK_HEAD
			WHERE DEL_F = 0
				AND DATE(DL_TS) >= (SELECT BEG_RPT FROM NST.PARAM_LOC)
			GROUP BY ORD_NBR
		) HED
			ON DET.ORD_NBR = HED.ORD_NBR
		INNER JOIN RTL.INVENTORY INV
			ON DET.INV_NBR = INV.INV_NBR
		INNER JOIN RTL.CAT_SUB SUB
			ON ".$innerJoin."
		INNER JOIN RTL.CAT CAT
			ON CAT.CAT_NBR = SUB.CAT_NBR
		INNER JOIN CMP.COMPANY SPL
			ON SPL.CO_NBR = HED.SHP_CO_NBR
		INNER JOIN CMP.COMPANY RCV
			ON RCV.CO_NBR = HED.RCV_CO_NBR
		LEFT JOIN CMP.PRN_DIG_TYP TYP
			ON INV.PRD_PRC_TYP = TYP.PRN_DIG_TYP
		WHERE ".$whereClauses."
		GROUP BY ".$groupClauses." ";
*/

$query = "SELECT
			HED.ORD_NBR,
			HED.SPL_NAME,
			HED.RCV_NAME,
			DATE(HED.DL_TS) AS ORD_DTE,
			YEAR(HED.DL_TS) AS ORD_YEAR,
			MONTH(HED.DL_TS) AS ORD_MONTH,
			DAY(HED.DL_TS) AS ORD_DAY,
			MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME,
			HED.CAT_SUB_NBR,
			HED.CAT_SUB_DESC,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN HED.ORD_Q ELSE 0 END),0) AS RCV_Q,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN HED.TOT_SUB ELSE 0 END),0) AS RCV_TOT_SUB,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'RT' THEN HED.ORD_Q ELSE 0 END),0) AS RTR_Q,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'RT' THEN HED.TOT_SUB ELSE 0 END),0) AS RTR_TOT_SUB,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN HED.ORD_Q ELSE 0 END),0) AS COR_Q,
			COALESCE(SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN HED.TOT_SUB ELSE 0 END),0) AS COR_TOT_SUB,
			COALESCE(SUM(HED.TOT_AMT),0) AS TOT_AMT,
			COALESCE(SUM(HED.TOT_REM),0) AS TOT_REM
		FROM (
			SELECT 
				HED.ORD_NBR,
				HED.DL_TS,
				HED.TOT_AMT,
				HED.TOT_REM,
				HED.IVC_TYP,
				SUB.CAT_SUB_NBR,
				SUB.CAT_SUB_DESC,
				HED.TAX_APL_ID,
				HED.SHP_CO_NBR,
				HED.RCV_CO_NBR,
				COALESCE(SUM(DET.ORD_Q),0) AS ORD_Q,
				COALESCE(SUM(DET.TOT_SUB),0) AS TOT_SUB,
				HED.PYMT_DOWN,
				HED.PYMT_REM,
				SPL.NAME AS SPL_NAME,
				RCV.NAME AS RCV_NAME
			FROM RTL.RTL_STK_DET DET
			LEFT JOIN RTL.RTL_STK_HEAD HED
				ON DET.ORD_NBR = HED.ORD_NBR
			INNER JOIN RTL.INVENTORY INV
				ON DET.INV_NBR = INV.INV_NBR
			INNER JOIN RTL.CAT_SUB SUB
				ON ".$innerJoin."
			INNER JOIN RTL.CAT CAT
				ON CAT.CAT_NBR = SUB.CAT_NBR
			LEFT JOIN CMP.PRN_DIG_TYP TYP
				ON INV.PRD_PRC_TYP = TYP.PRN_DIG_TYP
			INNER JOIN CMP.COMPANY SPL
				ON SPL.CO_NBR = HED.SHP_CO_NBR
			INNER JOIN CMP.COMPANY RCV
				ON RCV.CO_NBR = HED.RCV_CO_NBR
			WHERE ".$whereClauses."
			GROUP BY HED.ORD_NBR
		) HED
		
		GROUP BY ".$groupClauses."";
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

	$results['total']['RCV_Q'] 		+= $row['RCV_Q'];
	$results['total']['RCV_TOT_SUB'] += $row['RCV_TOT_SUB'];
	
	$results['total']['RTR_Q'] 		+= $row['RTR_Q'];
	$results['total']['RTR_TOT_SUB'] += $row['RTR_TOT_SUB'];
	
	$results['total']['COR_Q'] 		+= $row['COR_Q'];
	$results['total']['COR_TOT_SUB'] += $row['COR_TOT_SUB'];
	
	$results['total']['TOT_AMT'] 	+= $row['TOT_AMT'];
	$results['total']['PYMT_DOWN'] 	+= $row['PYMT_DOWN'];
	$results['total']['PYMT_REM'] 	+= $row['PYMT_REM'];
	$results['total']['TOT_REM'] 	+= $row['TOT_REM'];
	
}

echo json_encode($results);