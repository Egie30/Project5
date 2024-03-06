<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

/*
Don't forget to update TAX_F on CMP.COMPANY
*/

if (empty($_GET['END_DT'])) {
	$_GET['END_DT'] = date("Y-m-d");
}

$beginDate 		= $_GET['BEG_DT'];
$endDate 		= $_GET['END_DT'];

$companyNumber	= $_GET['CO_NBR'];
$IvcTyp			= $_GET['IVC_TYP'];
$Accounting		= $_GET['ACTG'];
$PrnDigType		= $_GET['PRN_DIG_TYP'];
$PrnDigEqp		= $_GET['PRN_DIG_EQP'];
$CatSubNbr		= $_GET['CAT_SUB_NBR'];


$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];

$whereClauses 	= array("HED.DEL_F=0", "INV.CAT_NBR IN (1,10)", "INV.CAT_SUB_NBR != '202'", "DATE(HED.DL_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_GLBL)");
$whereMovement 	= array("HED.DEL_F=0", "INV.CAT_NBR IN (1,10)", "INV.CAT_SUB_NBR != '202'", "MOV.DEL_NBR = 0", "DATE(MOV.CRT_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_GLBL)", "DATE(HED.DL_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_GLBL)");

if (!empty($beginDate)) {
	$whereClauses[] 	= "DATE(HED.DL_TS) >= '" . $beginDate . "'";
	$whereMovement[] 	= "(DATE(HED.DL_TS) >= '" . $beginDate . "' AND DATE(MOV.CRT_TS) >= '" . $beginDate . "')";
}

if (!empty($endDate)) {
	$whereClauses[] 	= "DATE(HED.DL_TS) <= '" . $endDate . "'";
	$whereMovement[] 	= "(DATE(HED.DL_TS) <= '" . $endDate . "' AND DATE(MOV.CRT_TS) <= '" . $endDate . "')";
}


if (!empty($companyNumber)) {
	$whereClauses[] 	= "(
				(HED.RCV_CO_NBR=".$companyNumber." AND IVC_TYP IN ('RC', 'XF'))
				OR (HED.SHP_CO_NBR=".$companyNumber." AND IVC_TYP IN ('XF'))
				OR (HED.SHP_CO_NBR=".$companyNumber." AND IVC_TYP IN ('RT'))
				OR (HED.SHP_CO_NBR=".$companyNumber." AND IVC_TYP IN ('CR'))
				OR (HED.SHP_CO_NBR=".$companyNumber." AND IVC_TYP IN ('SL')))";
	
	$whereMovement[] 	= "(
				(HED.RCV_CO_NBR=".$companyNumber." AND IVC_TYP IN ('RC', 'XF')))";
}


if (!empty($PrnDigType)) {
	$whereClauses[] 	= "INV.PRD_PRC_TYP = '" . $PrnDigType . "'";
	$whereMovement[] 	= "INV.PRD_PRC_TYP = '" . $PrnDigType . "'";
}

if (!empty($PrnDigEqp)) {
	$whereClauses[] 	= "EQP.PRN_DIG_EQP = '" . $PrnDigEqp . "'";
	$whereMovement[] 	= "EQP.PRN_DIG_EQP = '" . $PrnDigEqp . "'";
}

if (!empty($CatSubNbr)) {
	$whereClauses[] 	= "INV.CAT_SUB_NBR = '" . $CatSubNbr . "'";
	$whereMovement[] 	= "INV.CAT_SUB_NBR = '" . $CatSubNbr . "'";
}

if ($Accounting == 0) {
	$whereClauses[] 	= "(HED.IVC_TYP = 'RC' OR HED.IVC_TYP = 'RT' OR HED.IVC_TYP = 'XF' OR HED.IVC_TYP = 'CR')";
	$whereMovement[] 	= "(HED.IVC_TYP = 'RC' OR HED.IVC_TYP = 'XF')";
}

if ($Accounting == 1) {
	$whereClauses[] 	= "HED.TAX_APL_ID IN ('I', 'A')";
	$whereMovement[] 	= "HED.TAX_APL_ID IN ('I', 'A')";
}

if ($Accounting == 2) {
	$whereClauses[] 	= "((HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 1 AND IVC_TYP = 'RC') OR (HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 1 AND IVC_TYP = 'RT'))";
	
	$whereMovement[] 	= "((HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 1 AND IVC_TYP = 'RC') OR (HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 1 AND IVC_TYP = 'RT'))";
}

if ($Accounting == 3) {
	$whereClauses[] 	= "((HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 0 AND IVC_TYP = 'RC') OR (HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 0 AND IVC_TYP = 'RT'))";
	
	$whereMovement[] 	= "((HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 0 AND IVC_TYP = 'RC') OR (HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 0 AND IVC_TYP = 'RT'))";
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

		$where = "WHERE 
			STK.INV_NBR LIKE '" . $query . "'
			OR STK.INV_NAME LIKE '" . $query . "'
		";
	}
}


$whereClauses 	= implode(" AND ", $whereClauses);
$whereMovement 	= implode(" AND ", $whereMovement);


if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "PRN_DIG_TYP":
				$groupClauses[] = "STK.PRN_DIG_TYP";
				break;
			case "PRN_DIG_EQP":
				$groupClauses[] = "STK.PRN_DIG_EQP";
				break;
			case "CAT_SUB_NBR":
				$groupClauses[] = "STK.CAT_SUB_NBR";
				break;
			case "INV_NBR":
				$groupClauses[] = "STK.INV_NBR";
				break;
			default:
				$groupClauses[] = "STK.PRN_DIG_TYP";
				break;
		}
	}
		
	$groupClause = implode(", ", $groupClauses);
} else {
	$groupClause = "STK.PRN_DIG_TYP";
}

$query = "SELECT 
	STK.ORD_DTE,
	STK.ORD_YEAR,
	STK.ORD_MONTH,
	STK.ORD_DAY,
	STK.ORD_MONTHNAME,
	STK.ORD_DET_NBR,
	STK.ORD_NBR,
	STK.INV_NBR,
	STK.INV_NAME,
	STK.CAT_NBR,
	STK.CAT_DESC,
	STK.CAT_SUB_NBR,
	STK.CAT_SUB_DESC,
	STK.PRN_DIG_TYP,
	STK.PRN_DIG_EQP,
	STK.PRN_DIG_EQP_DESC,
	STK.PRN_DIG_DESC,
	SUM(COALESCE(STK.RCV_Q,0)) AS RCV_Q,
	SUM(COALESCE(STK.XF_IN_Q,0)) AS XF_IN_Q,
	SUM(COALESCE(STK.RTR_Q,0)) AS RTR_Q,
	SUM(COALESCE(STK.XF_OUT_Q,0)) AS XF_OUT_Q,
	SUM(COALESCE(STK.COR_Q,0)) AS COR_Q,
	SUM(COALESCE(MOV.MOV_Q,0)) AS MOV_Q,
	SUM(COALESCE(STK.RCV_TOT_SUB,0)) AS RCV_TOT_SUB,
	SUM(COALESCE(STK.RTR_TOT_SUB,0)) AS RTR_TOT_SUB,
	SUM(COALESCE(STK.COR_TOT_SUB,0)) AS COR_TOT_SUB,
	SUM(COALESCE(MOV.MOV_TOT_SUB,0)) AS MOV_TOT_SUB,
	SUM(COALESCE(STK.XF_OUT_TOT_SUB,0)) AS XF_OUT_TOT_SUB,
	SUM(COALESCE(STK.RCV_Q,0) + COALESCE(STK.XF_IN_Q,0) - COALESCE(STK.RTR_Q,0) + COALESCE(STK.COR_Q,0) - COALESCE(STK.XF_OUT_Q,0) - COALESCE(MOV.MOV_Q,0)) AS BALANCE_Q,
	SUM(COALESCE(STK.RCV_TOT_SUB,0) + COALESCE(STK.XF_IN_TOT_SUB,0) - COALESCE(STK.RTR_TOT_SUB,0) + COALESCE(STK.COR_TOT_SUB,0) - COALESCE(STK.XF_OUT_TOT_SUB,0) - COALESCE(MOV.MOV_TOT_SUB,0)) AS BALANCE_AMT
FROM (SELECT
			DATE(HED.DL_TS) AS ORD_DTE,
			YEAR(HED.DL_TS) AS ORD_YEAR,
			MONTH(HED.DL_TS) AS ORD_MONTH,
			DAY(HED.DL_TS) AS ORD_DAY,
			MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME,
			EQP.PRN_DIG_EQP,
			EQP.PRN_DIG_EQP_DESC,
			DET.ORD_DET_NBR,
			DET.INV_NBR,
			INV.NAME AS INV_NAME,
			HED.ORD_NBR, 
			CAT.CAT_NBR,
			CAT.CAT_DESC,
			SUB.CAT_SUB_NBR,
			SUB.CAT_SUB_DESC,
			TYP.PRN_DIG_TYP,
			TYP.PRN_DIG_DESC,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' AND HED.RCV_CO_NBR = ".$companyNumber." THEN DET.ORD_Q ELSE 0 END) AS RCV_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'XF' AND HED.RCV_CO_NBR = ".$companyNumber." THEN DET.ORD_Q ELSE 0 END) AS XF_IN_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'RT' AND HED.SHP_CO_NBR = ".$companyNumber." THEN DET.ORD_Q ELSE 0 END) AS RTR_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN DET.ORD_Q ELSE 0 END) AS COR_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'XF' AND HED.SHP_CO_NBR = ".$companyNumber." THEN DET.ORD_Q ELSE 0 END) AS XF_OUT_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' AND HED.RCV_CO_NBR = ".$companyNumber." THEN DET.TOT_SUB ELSE 0 END) AS RCV_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'XF' AND HED.RCV_CO_NBR = ".$companyNumber." THEN DET.TOT_SUB ELSE 0 END) AS XF_IN_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'RT' AND HED.SHP_CO_NBR = ".$companyNumber." THEN DET.TOT_SUB ELSE 0 END) AS RTR_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN DET.TOT_SUB ELSE 0 END) AS COR_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'XF' AND HED.SHP_CO_NBR = ".$companyNumber." THEN DET.TOT_SUB ELSE 0 END) AS XF_OUT_TOT_SUB
		FROM RTL.RTL_STK_DET DET
		INNER JOIN RTL.RTL_STK_HEAD HED
			ON DET.ORD_NBR = HED.ORD_NBR
		INNER JOIN RTL.INVENTORY INV
			ON DET.INV_NBR = INV.INV_NBR
		LEFT JOIN CMP.PRN_DIG_TYP TYP
			ON INV.PRD_PRC_TYP = TYP.PRN_DIG_TYP
		LEFT JOIN CMP.PRN_DIG_EQP EQP
			ON EQP.PRN_DIG_EQP = TYP.PRN_DIG_EQP
		INNER JOIN RTL.CAT_SUB SUB
			ON INV.CAT_SUB_NBR = SUB.CAT_SUB_NBR
		INNER JOIN RTL.CAT CAT
			ON CAT.CAT_NBR = SUB.CAT_NBR
		INNER JOIN CMP.COMPANY SPL
			ON SPL.CO_NBR = HED.SHP_CO_NBR
		INNER JOIN CMP.COMPANY RCV
			ON RCV.CO_NBR = HED.RCV_CO_NBR
		WHERE ".$whereClauses."
		GROUP BY DET.ORD_DET_NBR
) STK
LEFT JOIN
(SELECT MOV.ORD_DET_NBR,
	SUM(COALESCE(MOV.MOV_Q,0)) AS MOV_Q,
	SUM(COALESCE(MOV.MOV_Q * MOV.DET_INV_PRC,0)) AS MOV_TOT_SUB
FROM RTL.INV_MOV MOV
	JOIN RTL.RTL_STK_DET DET 
		ON MOV.ORD_DET_NBR = DET.ORD_DET_NBR
	LEFT JOIN RTL.RTL_STK_HEAD HED
		ON DET.ORD_NBR = HED.ORD_NBR
	LEFT JOIN RTL.INVENTORY INV
		ON DET.INV_NBR = INV.INV_NBR
	LEFT JOIN CMP.PRN_DIG_TYP TYP
		ON INV.PRD_PRC_TYP = TYP.PRN_DIG_TYP
	LEFT JOIN RTL.CAT_SUB SUB
		ON INV.CAT_SUB_NBR = SUB.CAT_SUB_NBR
	LEFT JOIN RTL.CAT CAT
		ON CAT.CAT_NBR = SUB.CAT_NBR
	LEFT JOIN CMP.COMPANY SPL
		ON SPL.CO_NBR = HED.SHP_CO_NBR
	LEFT JOIN CMP.COMPANY RCV
		ON RCV.CO_NBR = HED.RCV_CO_NBR
	LEFT JOIN CMP.PRN_DIG_EQP EQP
			ON EQP.PRN_DIG_EQP = TYP.PRN_DIG_EQP
	WHERE ".$whereMovement."
	GROUP BY MOV.ORD_DET_NBR
) MOV ON MOV.ORD_DET_NBR = STK.ORD_DET_NBR
".$where."
GROUP BY ".$groupClause."
ORDER BY ".$groupClause." DESC
";
	
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
	$results['total']['RTR_Q'] 		+= $row['RTR_Q'];
	$results['total']['COR_Q'] 		+= $row['COR_Q'];
	$results['total']['XF_IN_Q'] 	+= $row['XF_IN_Q'];
	$results['total']['XF_OUT_Q'] 	+= $row['XF_OUT_Q'];
	$results['total']['MOV_Q'] 		+= $row['MOV_Q'];
	
	$results['total']['RCV_TOT_SUB']+= $row['RCV_TOT_SUB'];
	$results['total']['RTR_TOT_SUB']+= $row['RTR_TOT_SUB'];
	$results['total']['COR_TOT_SUB']+= $row['COR_TOT_SUB'];
	
	$results['total']['BALANCE_Q'] 	+= $row['BALANCE_Q'];
	$results['total']['BALANCE_AMT']+= $row['BALANCE_AMT'];
	
}

echo json_encode($results);