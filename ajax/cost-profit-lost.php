<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";


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

$whereClauses = array("HED.DEL_F=0", "DATE(HED.DL_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)");
$groupClauses = array();

if($IvcTyp != ""){
	$whereClauses[] = "HED.IVC_TYP = '" . $IvcTyp ."' ";
}
else {
	$whereClauses[] = "HED.IVC_TYP IN ('RC') ";
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
		
}

if ($Accounting == 3) {
	
	$whereClauses[] = "((HED.IVC_TYP = 'RC' AND HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 0)
		OR (HED.IVC_TYP = 'RT' AND HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 0))";
	}

if($_GET['TYP'] == 'RL') {
	$whereClauses[] = "SUM(CASE WHEN (STK.CAT_NBR NOT IN (1,10) AND STK.HED_CAT_NBR IN (1,10)) THEN STK.TOT_SUB ELSE 0 END) > 0";
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
				STK.ORD_NBR,
				STK.INV_DESC,
				STK.INV_NAME,
				STK.CAT_SUB_NBR,
				STK.CAT_SUB_DESC,
				STK.CAT_NBR,
				STK.CAT_DESC,
				STK.HED_CAT_SUB_NBR,
				STK.HED_CAT_SUB_DESC,
				STK.HED_CAT_NBR,
				STK.HED_CAT_DESC,
				SUM(CASE WHEN (STK.CAT_NBR IN (1,10) AND STK.HED_CAT_NBR IN (1,10)) THEN STK.TOT_SUB ELSE 0 END) AS HPP,
				SUM(CASE WHEN (STK.CAT_NBR NOT IN (1,10) AND STK.HED_CAT_NBR IN (1,10,12)) THEN STK.TOT_SUB ELSE 0 END) AS RL,
				SUM(CASE WHEN (STK.CAT_NBR NOT IN (1,10,12) AND STK.HED_CAT_NBR NOT IN (1,10,12))  THEN STK.TOT_SUB ELSE 0 END) AS RETAIL,
				STK.ORD_YEAR,
				STK.ORD_MONTH,
				STK.ORD_DAY,
				STK.ORD_MONTHNAME
			FROM
			(SELECT 
				DET.ORD_DET_NBR,
				HED.ORD_NBR,
				DET.INV_NBR,
				INV.NAME AS INV_NAME,
				DET.INV_DESC,
				COALESCE(SUB.CAT_SUB_NBR,0) AS CAT_SUB_NBR,
				COALESCE(SUB.CAT_SUB_DESC,0) AS CAT_SUB_DESC,
				COALESCE(CAT.CAT_NBR,0) AS CAT_NBR,
				COALESCE(CAT.CAT_DESC,0) AS CAT_DESC,
				COALESCE(HEDSUB.CAT_SUB_NBR) AS HED_CAT_SUB_NBR,
				COALESCE(HEDSUB.CAT_SUB_DESC) AS HED_CAT_SUB_DESC,
				COALESCE(HEDCAT.CAT_NBR) AS HED_CAT_NBR,
				COALESCE(HEDCAT.CAT_DESC) AS HED_CAT_DESC,
				DATE(HED.DL_TS),
				SUM(HED.TOT_AMT) AS TOT_AMT,
				SUM(DET.TOT_SUB) AS TOT_SUB,
				DATE(HED.DL_TS) AS ORD_DTE,
				YEAR(HED.DL_TS) AS ORD_YEAR,
				MONTH(HED.DL_TS) AS ORD_MONTH,
				DAY(HED.DL_TS) AS ORD_DAY,
				MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME
			FROM RTL.RTL_STK_DET DET
			JOIN RTL.RTL_STK_HEAD HED
				ON DET.ORD_NBR = HED.ORD_NBR
			LEFT JOIN RTL.INVENTORY INV
				ON DET.INV_NBR = INV.INV_NBR
			LEFT JOIN RTL.CAT_SUB SUB
				ON SUB.CAT_SUB_NBR = INV.CAT_SUB_NBR
			LEFT JOIN RTL.CAT CAT
				ON SUB.CAT_NBR = CAT.CAT_NBR
			LEFT JOIN RTL.CAT_SUB HEDSUB
				ON HEDSUB.CAT_SUB_NBR = HED.CAT_SUB_NBR
			LEFT JOIN RTL.CAT HEDCAT
				ON HEDCAT.CAT_NBR = HEDSUB.CAT_NBR
			WHERE ".$whereClauses."
			GROUP BY ".$groupClauses."
			) STK
			GROUP BY ".$groupClauses."
		) COST ON COST.ORD_MONTH = MO.ACT_MO
		GROUP BY MO.ACT_MO";
	}
	else {


	$query = "SELECT 
		STK.ORD_NBR,
		STK.INV_DESC,
		STK.INV_NAME,
		STK.CAT_SUB_NBR,
		STK.CAT_SUB_DESC,
		STK.CAT_NBR,
		STK.CAT_DESC,
		STK.HED_CAT_SUB_NBR,
		STK.HED_CAT_SUB_DESC,
		STK.HED_CAT_NBR,
		STK.HED_CAT_DESC,
		SUM(CASE WHEN (STK.CAT_NBR IN (1,10) AND STK.HED_CAT_NBR IN (1,10)) THEN STK.TOT_SUB ELSE 0 END) AS HPP,
		SUM(CASE WHEN (STK.CAT_NBR NOT IN (1,10) AND STK.HED_CAT_NBR IN (1,10,12)) THEN STK.TOT_SUB ELSE 0 END) AS RL,
		SUM(CASE WHEN (STK.CAT_NBR NOT IN (1,10,12) AND STK.HED_CAT_NBR NOT IN (1,10,12))  THEN STK.TOT_SUB ELSE 0 END) AS RETAIL,
		STK.ORD_YEAR,
		STK.ORD_MONTH,
		STK.ORD_DAY,
		STK.ORD_MONTHNAME
	FROM
	(SELECT 
		DET.ORD_DET_NBR,
		HED.ORD_NBR,
		DET.INV_NBR,
		INV.NAME AS INV_NAME,
		DET.INV_DESC,
		COALESCE(SUB.CAT_SUB_NBR,0) AS CAT_SUB_NBR,
		COALESCE(SUB.CAT_SUB_DESC,0) AS CAT_SUB_DESC,
		COALESCE(CAT.CAT_NBR,0) AS CAT_NBR,
		COALESCE(CAT.CAT_DESC,0) AS CAT_DESC,
		COALESCE(HEDSUB.CAT_SUB_NBR) AS HED_CAT_SUB_NBR,
		COALESCE(HEDSUB.CAT_SUB_DESC) AS HED_CAT_SUB_DESC,
		COALESCE(HEDCAT.CAT_NBR) AS HED_CAT_NBR,
		COALESCE(HEDCAT.CAT_DESC) AS HED_CAT_DESC,
		DATE(HED.DL_TS),
		SUM(HED.TOT_AMT) AS TOT_AMT,
		SUM(DET.TOT_SUB) AS TOT_SUB,
		DATE(HED.DL_TS) AS ORD_DTE,
		YEAR(HED.DL_TS) AS ORD_YEAR,
		MONTH(HED.DL_TS) AS ORD_MONTH,
		DAY(HED.DL_TS) AS ORD_DAY,
		MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME
	FROM RTL.RTL_STK_DET DET
	JOIN RTL.RTL_STK_HEAD HED
		ON DET.ORD_NBR = HED.ORD_NBR
	LEFT JOIN RTL.INVENTORY INV
		ON DET.INV_NBR = INV.INV_NBR
	LEFT JOIN RTL.CAT_SUB SUB
		ON SUB.CAT_SUB_NBR = INV.CAT_SUB_NBR
	LEFT JOIN RTL.CAT CAT
		ON SUB.CAT_NBR = CAT.CAT_NBR
	LEFT JOIN RTL.CAT_SUB HEDSUB
		ON HEDSUB.CAT_SUB_NBR = HED.CAT_SUB_NBR
	LEFT JOIN RTL.CAT HEDCAT
		ON HEDCAT.CAT_NBR = HEDSUB.CAT_NBR
	WHERE ".$whereClauses."
	GROUP BY DET.ORD_DET_NBR
	) STK
	GROUP BY STK.HED_CAT_SUB_NBR ";

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

	$results['total']['HPP'] 		+= $row['HPP'];
	$results['total']['RL'] 		+= $row['RL'];
	$results['total']['RETAIL'] 	+= $row['RETAIL'];
	
}

echo json_encode($results);