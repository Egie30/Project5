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

if (!empty($companyNumber)) {
	$companyNumber		= $_GET['CO_NBR'];
}
else {
	$companyNumber	= $CoNbrDef;
}


$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];

$whereClauses 	= array("HED.DEL_F=0", "INV.CAT_SUB_NBR NOT IN (183,217,223)", "(TYP.CAT_TYP_NBR IS NULL OR TYP.CAT_TYP_NBR != 4)");
$whereCashier 	= array("INV.DEL_NBR = 0", "CSH.ACT_F = 0", "CSH.CSH_FLO_TYP = 'RT'");

if (!empty($endDate)) {
	$whereClauses[] 	= "DATE(HED.DL_TS) <= '" . $endDate . "' ";
	$whereCashier[] 	= "DATE(CSH.CRT_TS) <= '" . $endDate . "' ";
}



if (!empty($companyNumber)) {
	$whereClauses[] 	= "(
				(HED.RCV_CO_NBR=".$companyNumber." AND IVC_TYP IN ('RC', 'XF'))
				OR (HED.SHP_CO_NBR=".$companyNumber." AND IVC_TYP IN ('XF'))
				OR (HED.SHP_CO_NBR=".$companyNumber." AND IVC_TYP IN ('RT'))
				OR (HED.SHP_CO_NBR=".$companyNumber." AND IVC_TYP IN ('CR'))
				OR (HED.SHP_CO_NBR=".$companyNumber." AND IVC_TYP IN ('SL')))";
	
	$whereCashier[] 	= "CSH.CO_NBR =".$companyNumber." ";
}

if (!empty($CatSubNbr)) {
	$whereClauses[] 	= "INV.CAT_SUB_NBR = '" . $CatSubNbr . "'";
	$whereCashier[] 	= "INV.CAT_SUB_NBR = '" . $CatSubNbr . "'";
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

		$where = "
			WHERE (STK.INV_NBR LIKE '" . $query . "'
			OR STK.INV_NAME LIKE '" . $query . "'
			OR STK.CAT_SUB_DESC LIKE '" . $query . "'
			OR CSH.RTL_BRC LIKE '" . $query . "')
		";
	}
}


$whereClauses 	= implode(" AND ", $whereClauses);
$whereCashier 	= implode(" AND ", $whereCashier);


if (count($groups) > 0) {

	$groupClauses = array();
		
		while(count($groups) > 0) {
			$group = strtoupper(array_shift($groups));
			
			switch ($group) {
				case "CAT_SUB_NBR":
					$groupClauses[] = "STK.CAT_SUB_NBR";
					break;
				case "INV_NBR":
					$groupClauses[] = "STK.INV_NBR";
					break;
				default:
					$groupClauses[] = "STK.CAT_SUB_NBR";
					break;
			}
		}
	
			
	$groupClauses = implode(", ", $groupClauses);
	
} else {
	$groupClauses = "STK.CAT_SUB_NBR";
}


if ($searchQuery != "") {
		$groupClauses	= "STK.INV_NBR";
}
	
$query = "SELECT 
	STK.ORD_DTE,
	STK.ORD_YEAR,
	STK.ORD_MONTH,
	STK.ORD_DAY,
	STK.ORD_MONTHNAME,
	STK.INV_NBR,
	STK.ORD_NBR,
	STK.INV_NAME,
	STK.INV_PRC,
	STK.PRC,
	STK.CAT_NBR,
	CSH.RTL_BRC AS BARCODE,
    STK.CAT_SUB_DESC AS SUB_KATEGORY,
    STK.NAME AS SUPLIER,
	STK.CAT_SUB_NBR,
	STK.CAT_SUB_DESC,
	SUM(COALESCE(STK.RCV_Q,0)) AS RCV_Q,
	SUM(COALESCE(STK.XF_IN_Q,0)) AS XF_IN_Q,
	SUM(COALESCE(STK.RTR_Q,0)) AS RTR_Q,
	SUM(COALESCE(STK.XF_OUT_Q,0)) AS XF_OUT_Q,
	SUM(COALESCE(STK.COR_Q,0)) AS COR_Q,
	SUM(COALESCE(STK.SLS_Q,0)) AS SLS_Q,
	SUM(COALESCE(CSH.RTL_Q,0)) AS RTL_Q,
	SUM(COALESCE(STK.RCV_TOT_SUB,0)) AS RCV_TOT_SUB,
	SUM(COALESCE(STK.RTR_TOT_SUB,0)) AS RTR_TOT_SUB,
	SUM(COALESCE(STK.COR_TOT_SUB,0)) AS COR_TOT_SUB,
	SUM(COALESCE(STK.XF_OUT_TOT_SUB,0)) AS XF_OUT_TOT_SUB,
	SUM(COALESCE(STK.SLS_TOT_SUB,0)) AS SLS_TOT_SUB,
	SUM(COALESCE(CSH.RTL_TOT_SUB,0)) AS RTL_TOT_SUB,
	SUM(COALESCE(STK.RCV_Q,0) + COALESCE(STK.XF_IN_Q,0) - COALESCE(STK.RTR_Q,0) + COALESCE(STK.COR_Q,0) - COALESCE(STK.XF_OUT_Q,0) - COALESCE(STK.SLS_Q,0) - COALESCE(CSH.RTL_Q,0)) AS BALANCE_Q,
	SUM(COALESCE(STK.RCV_TOT_SUB,0) + COALESCE(STK.XF_IN_TOT_SUB,0) - COALESCE(STK.RTR_TOT_SUB,0) + COALESCE(STK.COR_TOT_SUB,0) - COALESCE(STK.XF_OUT_TOT_SUB,0) - COALESCE(STK.SLS_TOT_SUB,0) - COALESCE(CSH.RTL_TOT_SUB,0)) AS BALANCE_INV_PRC,
	SUM((COALESCE(STK.RCV_Q,0) + COALESCE(STK.XF_IN_Q,0) - COALESCE(STK.RTR_Q,0) + COALESCE(STK.COR_Q,0) - COALESCE(STK.XF_OUT_Q,0) - COALESCE(STK.SLS_Q,0) - COALESCE(CSH.RTL_Q,0)) * STK.PRC) AS BALANCE_PRC
FROM (SELECT
			DATE(HED.DL_TS) AS ORD_DTE,
			YEAR(HED.DL_TS) AS ORD_YEAR,
			MONTH(HED.DL_TS) AS ORD_MONTH,
			DAY(HED.DL_TS) AS ORD_DAY,
			MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME,
			DET.INV_NBR,
			INV.NAME AS INV_NAME,
			INV.INV_PRC,
			INV.PRC,
			HED.ORD_NBR, 
			CAT.CAT_NBR,
			CAT.CAT_DESC,
			SPL.NAME,
			SUB.CAT_SUB_NBR,
			HED.RCV_CO_NBR,
			HED.SHP_CO_NBR,
			SUB.CAT_SUB_DESC,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' AND HED.RCV_CO_NBR = ".$companyNumber." THEN DET.ORD_Q ELSE 0 END) AS RCV_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'XF' AND HED.RCV_CO_NBR = ".$companyNumber." THEN DET.ORD_Q ELSE 0 END) AS XF_IN_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'RT' AND HED.SHP_CO_NBR = ".$companyNumber." THEN DET.ORD_Q ELSE 0 END) AS RTR_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'CR' AND HED.RCV_CO_NBR = ".$companyNumber." THEN DET.ORD_Q ELSE 0 END) AS COR_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'XF' AND HED.SHP_CO_NBR = ".$companyNumber." THEN DET.ORD_Q ELSE 0 END) AS XF_OUT_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'SL' AND HED.SHP_CO_NBR = ".$companyNumber." THEN DET.ORD_Q ELSE 0 END) AS SLS_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' AND HED.RCV_CO_NBR = ".$companyNumber." THEN DET.TOT_SUB ELSE 0 END) AS RCV_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'XF' AND HED.RCV_CO_NBR = ".$companyNumber." THEN DET.TOT_SUB ELSE 0 END) AS XF_IN_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'RT' AND HED.SHP_CO_NBR = ".$companyNumber." THEN DET.TOT_SUB ELSE 0 END) AS RTR_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'CR' AND HED.RCV_CO_NBR = ".$companyNumber." THEN DET.TOT_SUB ELSE 0 END) AS COR_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'XF' AND HED.SHP_CO_NBR = ".$companyNumber." THEN DET.TOT_SUB ELSE 0 END) AS XF_OUT_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'SL' AND HED.SHP_CO_NBR = ".$companyNumber." THEN DET.TOT_SUB ELSE 0 END) AS SLS_TOT_SUB
		FROM RTL.RTL_STK_DET DET
		INNER JOIN RTL.RTL_STK_HEAD HED
			ON DET.ORD_NBR = HED.ORD_NBR
		INNER JOIN RTL.INVENTORY INV
			ON DET.INV_NBR = INV.INV_NBR
		INNER JOIN RTL.CAT_SUB SUB
			ON INV.CAT_SUB_NBR = SUB.CAT_SUB_NBR
		INNER JOIN RTL.CAT CAT
			ON CAT.CAT_NBR = SUB.CAT_NBR
		INNER JOIN CMP.COMPANY SPL
			ON SPL.CO_NBR = HED.SHP_CO_NBR
		INNER JOIN CMP.COMPANY RCV
			ON RCV.CO_NBR = HED.RCV_CO_NBR
		LEFT JOIN RTL.CAT_TYP TYP
			ON TYP.CAT_TYP_NBR = SUB.CAT_TYP_NBR
		WHERE ".$whereClauses."
		GROUP BY DET.INV_NBR
) STK
LEFT JOIN
(SELECT 	
		CSH.INV_NBR,
		SUM(CSH.RTL_Q) AS RTL_Q,
		SUM(CSH.RTL_Q * INV.INV_PRC) AS RTL_TOT_SUB,	
		SUB.CAT_SUB_NBR,
		CAT.CAT_NBR,
		CSH.RTL_BRC
	FROM RTL.CSH_REG CSH
	INNER JOIN RTL.INVENTORY INV 
		ON CSH.INV_NBR = INV.INV_NBR
	INNER JOIN RTL.CAT_SUB SUB 
		ON SUB.CAT_SUB_NBR = INV.CAT_SUB_NBR
	INNER JOIN RTL.CAT CAT
		ON CAT.CAT_NBR = SUB.CAT_NBR
	WHERE ".$whereCashier."
	GROUP BY INV.INV_NBR
) CSH ON CSH.INV_NBR = STK.INV_NBR
".$where."
GROUP BY STK.INV_NBR
ORDER BY STK.INV_NBR";

// echo "<pre>".$query;

$pagination = pagination($query, 100);

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
	$results['total']['SLS_Q'] 		+= $row['SLS_Q'];
	$results['total']['RTL_Q'] 		+= $row['RTL_Q'];
	
	$results['total']['RCV_TOT_SUB']	+= $row['RCV_TOT_SUB'];
	$results['total']['RTR_TOT_SUB']	+= $row['RTR_TOT_SUB'];
	$results['total']['COR_TOT_SUB']	+= $row['COR_TOT_SUB'];
	$results['total']['XF_IN_TOT_SUB']	+= $row['XF_IN_TOT_SUB'];
	$results['total']['XF_OUT_TOT_SUB']	+= $row['XF_OUT_TOT_SUB'];
	$results['total']['SLS_TOT_SUB']	+= $row['SLS_TOT_SUB'];
	$results['total']['RTL_TOT_SUB']	+= $row['RTL_TOT_SUB'];
	
	$results['total']['BALANCE_Q'] 			+= $row['BALANCE_Q'];
	$results['total']['BALANCE_INV_PRC']	+= $row['BALANCE_INV_PRC'];
	$results['total']['BALANCE_PRC']		+= $row['BALANCE_PRC'];
	$results['total']['INV_PRC']		+= $row['INV_PRC'];
	$results['total']['PRC']		+= $row['PRC'];
	
}

echo json_encode($results);
