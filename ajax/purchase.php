<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

/*
Don't forget to update TAX_F on CMP.COMPANY
*/
//echo $_GET['YEARS'];

$CatSubNbr		= $_GET['CAT_SUB_NBR'];
$CatNbr			= $_GET['CAT_NBR'];
$CatSubTyp		= $_GET['CAT_SUB_TYP'];
$PayType		= $_GET['PAY_TYP'];

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

$whereClauses 	= array("DATE(HED.DL_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)");
$whereCashiers	= array();
$groupClauses 	= array();

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


if ($PayType	== 'DOWN') {  $field = 'HED.PYMT_DOWN_TS'; }
	else if ($PayType	== 'REM') { $field = 'HED.PYMT_REM_TS'; }
		else if ($PayType	== 'REM') { $field = 'HED.PYMT_REM_TS'; }
			else { $field = 'HED.PYMT_REM_TS'; }
	
if($PayType == 'FULL') {
		$whereClauses[] = "PYMT_REM_TS IS NOT NULL";
}
	
if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$whereClauses[] = "DAY(".$field.")=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(".$field.")= ".$months." ";
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(".$field.")= ". $years." ";
	}
	
	if ($day != "") {
		$whereClauses[] = "DAY(".$field.")=" . $day." ";
	}

	if ($month != "") {
		$whereClauses[] = "MONTH(".$field.")=" . $month." ";
	}

	if ($year != "") {
		$whereClauses[] = "YEAR(".$field.")=" . $year." ";
	}
} else {
	if (!empty($beginDate)) {
		$whereClauses[] = "DATE(".$field.") >= '" . $beginDate . "' ";
	}

	if (!empty($endDate)) {
		$whereClauses[] = "DATE(".$field.") <= '" . $endDate . "' ";
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


$whereCashiers 	= implode(" AND ", $whereCashiers);
$whereClauses 	= implode(" AND ", $whereClauses);
$groupClauses 	= implode(", ", $groupClauses);


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
( SELECT 
				HED.ORD_NBR,
				DATE(".$field.") AS ORD_DTE,
				YEAR(".$field.") AS ORD_YEAR,
				MONTH(".$field.") AS ORD_MONTH,
				DAY(".$field.") AS ORD_DAY,
				MONTHNAME(".$field.") AS ORD_MONTHNAME,
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
				RCV.NAME AS RCV_NAME,
				HED.VAL_PYMT_DOWN,
				HED.VAL_PYMT_REM,
				SUM(CASE WHEN HED.PYMT_DOWN_TS THEN PYMT_DOWN ELSE 0 END) AS TND_AMT_DOWN,
				SUM(CASE WHEN HED.PYMT_REM_TS THEN PYMT_REM ELSE 0 END) AS TND_AMT_REM,
				SUM(COALESCE(HED.TOT_AMT,0)) AS TND_AMT
			FROM RTL.RTL_STK_DET DET
			LEFT JOIN RTL.RTL_STK_HEAD HED
				ON DET.ORD_NBR = HED.ORD_NBR
			INNER JOIN RTL.INVENTORY INV
				ON DET.INV_NBR = INV.INV_NBR
			INNER JOIN RTL.CAT_SUB SUB
				ON INV.CAT_SUB_NBR = SUB.CAT_SUB_NBR
			INNER JOIN RTL.CAT CAT
				ON CAT.CAT_NBR = SUB.CAT_NBR
			LEFT JOIN CMP.PRN_DIG_TYP TYP
				ON INV.PRD_PRC_TYP = TYP.PRN_DIG_TYP
			INNER JOIN CMP.COMPANY SPL
				ON SPL.CO_NBR = HED.SHP_CO_NBR
			INNER JOIN CMP.COMPANY RCV
				ON RCV.CO_NBR = HED.RCV_CO_NBR
			WHERE ".$whereClauses."
			GROUP BY ".$groupClauses."
		
) RCV ON RCV.ORD_MONTH = MO.ACT_MO
	GROUP BY MO.ACT_MO";
}
else {

$query = "SELECT 
				HED.ORD_NBR,
				DATE(".$field.") AS ORD_DTE,
				YEAR(".$field.") AS ORD_YEAR,
				MONTH(".$field.") AS ORD_MONTH,
				DAY(".$field.") AS ORD_DAY,
				MONTHNAME(".$field.") AS ORD_MONTHNAME,
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
				RCV.NAME AS RCV_NAME,
				HED.VAL_PYMT_DOWN,
				HED.VAL_PYMT_REM,
				SUM(CASE WHEN HED.PYMT_DOWN_TS THEN PYMT_DOWN ELSE 0 END) AS TND_AMT_DOWN,
				SUM(CASE WHEN HED.PYMT_REM_TS THEN PYMT_REM ELSE 0 END) AS TND_AMT_REM,
				SUM(COALESCE(HED.TOT_AMT,0)) AS TND_AMT
			FROM RTL.RTL_STK_DET DET
			LEFT JOIN RTL.RTL_STK_HEAD HED
				ON DET.ORD_NBR = HED.ORD_NBR
			INNER JOIN RTL.INVENTORY INV
				ON DET.INV_NBR = INV.INV_NBR
			INNER JOIN RTL.CAT_SUB SUB
				ON INV.CAT_SUB_NBR = SUB.CAT_SUB_NBR
			INNER JOIN RTL.CAT CAT
				ON CAT.CAT_NBR = SUB.CAT_NBR
			LEFT JOIN CMP.PRN_DIG_TYP TYP
				ON INV.PRD_PRC_TYP = TYP.PRN_DIG_TYP
			INNER JOIN CMP.COMPANY SPL
				ON SPL.CO_NBR = HED.SHP_CO_NBR
			INNER JOIN CMP.COMPANY RCV
				ON RCV.CO_NBR = HED.RCV_CO_NBR
			WHERE ".$whereClauses."
			GROUP BY ".$groupClauses." ";
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
	
	$results['total']['TND_AMT_DOWN'] 	+= $row['TND_AMT_DOWN'];
	$results['total']['TND_AMT_REM'] 	+= $row['TND_AMT_REM'];
	$results['total']['TND_AMT'] 		+= $row['TND_AMT'];
	
}

echo json_encode($results);