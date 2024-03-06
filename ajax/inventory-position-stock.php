<?php
require_once "/../framework/database/connect.php";
require_once "/../framework/pagination/pagination.php";

if (empty($_GET['END_DT'])) {
	$_GET['END_DT'] = date("Y-m-d");
}

$companyNumber = $CoNbrDef;
$endDate       = $_GET['END_DT'];
$searchQuery   = strtoupper($_REQUEST['s']);
$group         = $_GET['GROUP'];

if ($_GET['CO_NBR'] != "") {
	$companyNumber = $_GET['CO_NBR'];
}

$whereClauses = array("INV.DEL_NBR=0");

if ($_GET['SPL_NBR'] != '') {
	$whereClauses[] = "INV.CO_NBR=" . $_GET['SPL_NBR'];
}

if ($_GET['CAT_SUB_NBR'] != '') {
	$whereClauses[] = "INV.CAT_SUB_NBR=" . $_GET['CAT_SUB_NBR'];
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

		switch (strtoupper($group)) {
			case "SPL_NBR":
				$whereClauses[] = "(
					INV.CO_NBR LIKE '" . $query . "'
					OR SPL.NAME LIKE '" . $query . "'
					OR SPL.CO_ID LIKE '" . $query . "'
					OR SPL.KEYWORDS LIKE '" . $query . "'
				)";
				break;
			case "CAT_SUB_NBR":
				$whereClauses[] = "(
					INV.CAT_SUB_NBR LIKE '" . $query . "'
					OR CAT.CAT_DESC LIKE '" . $query . "'
					OR SUB.CAT_SUB_DESC LIKE '" . $query . "'
				)";
				break;
			default:
				$whereClauses[] = "(
					INV.CO_NBR LIKE '" . $query . "'
					OR SPL.NAME LIKE '" . $query . "'
					OR INV.INV_NBR LIKE '" . $query . "'
					OR INV.INV_BCD LIKE '" . $query . "'
					OR INV.NAME LIKE '" . $query . "'
				)";
				break;
		}
	}
}

$whereClauses = implode(" AND ", $whereClauses);

switch (strtoupper($group)) {
	case "SPL_NBR":
		$groupClause = "INV.CO_NBR";
		break;
	case "CAT_SUB_NBR":
		$groupClause = "INV.CAT_SUB_NBR";
		break;
	case "INV_BCD":
		$groupClause = "INV.INV_BCD";
		break;
	case "INV_NBR":
	default:
		$groupClause = "INV.INV_NBR";
		break;
}

$query = "SELECT
	COUNT(DISTINCT INV.INV_NBR) AS ITM_Q,
	INV.INV_NBR,
    COALESCE(INV.NAME, 'Unknown') AS INV_NAME,
    COALESCE(INV.INV_PRC, 0) AS INV_PRC,
    COALESCE(INV.PRC, 0) AS PRC,
    INV.INV_BCD,
    INV.CO_NBR AS SPL_NBR,
    COALESCE(SPL.NAME, 'Unknown') AS SPL_NAME,
    COALESCE(CASE WHEN SPL.ADDRESS <> '' THEN CONCAT(SPL.ADDRESS, ', ', SPLCTY.CITY_NM) ELSE SPLCTY.CITY_NM END, 'Unknown') AS SPL_ADDR,
    CAT.CAT_NBR,
    COALESCE(CAT.CAT_DESC, 'Unknown') AS CAT_DESC,
    SUB.CAT_SUB_NBR,
    COALESCE(SUB.CAT_SUB_DESC, 'Unknown') AS CAT_SUB_DESC,
    SLF.CAT_SHLF_NBR,
    COALESCE(SLF.CAT_SHLF_DESC, 'Unknown') AS CAT_SHLF_DESC,
    STK.RCV_CO_NBR,
    COALESCE(LOC.NAME, 'Unknown') AS RCV_NAME,
    SUM(COALESCE(STK.RCV_Q, 0) + COALESCE(STK.COR_Q, 0) - COALESCE(STK.TRF_Q, 0) - COALESCE(STK.RTR_Q, 0) - COALESCE(CSH.RTL_Q, 0) - COALESCE(STK.SLS_Q, 0) - COALESCE(MOV.MOV_Q, 0)) AS BALANCE,
    SUM((COALESCE(STK.RCV_Q, 0) + COALESCE(STK.COR_Q, 0) - COALESCE(STK.TRF_Q, 0) - COALESCE(STK.RTR_Q, 0) - COALESCE(CSH.RTL_Q, 0) - COALESCE(STK.SLS_Q, 0) - COALESCE(MOV.MOV_Q, 0)) * INV.INV_PRC) AS BALANCE_PRC,
    SUM((COALESCE(STK.RCV_Q, 0) + COALESCE(STK.COR_Q, 0) - COALESCE(STK.TRF_Q, 0) - COALESCE(STK.RTR_Q, 0) - COALESCE(CSH.RTL_Q, 0) - COALESCE(STK.SLS_Q, 0) - COALESCE(MOV.MOV_Q, 0)) * INV.PRC) AS BALANCE_RTL_PRC,
    SUM(COALESCE(STK.RCV_Q, 0)) AS RCV_Q,
    SUM(COALESCE(STK.TRF_Q, 0)) AS TRF_Q,
    SUM(COALESCE(STK.RTR_Q, 0)) AS RTR_Q,
    SUM(COALESCE(STK.RTRSPL_Q, 0)) AS RTRMTS_Q,
    SUM(COALESCE(STK.RTRSPL_Q, 0)) AS RTRSPL_Q,
    SUM(COALESCE(STK.COR_Q, 0)) AS COR_Q,
    SUM(COALESCE(STK.SLS_Q, 0)) AS SLS_Q,
    SUM(COALESCE(MOV.MOV_Q, 0)) AS MOV_Q,
    SUM(COALESCE(CSH.RTL_Q, 0) + COALESCE(STK.SLS_Q, 0)) AS RTL_Q,
    SUM(COALESCE(STK.RCV_TOT_SUB, 0)) AS RCV_TOT_SUB,
    SUM(COALESCE(STK.TRF_TOT_SUB, 0)) AS TRF_TOT_SUB,
    SUM(COALESCE(STK.RTR_TOT_SUB, 0)) AS RTR_TOT_SUB,
    SUM(COALESCE(STK.RTRMTS_TOT_SUB, 0)) AS RTRMTS_TOT_SUB,
    SUM(COALESCE(STK.RTRSPL_TOT_SUB, 0)) AS RTRSPL_TOT_SUB,
    SUM(COALESCE(STK.COR_TOT_SUB, 0)) AS COR_TOT_SUB,
    SUM(COALESCE(STK.SLS_TOT_SUB, 0)) AS SLS_TOT_SUB,
    SUM(COALESCE(CSH.TND_AMT, 0) + COALESCE(STK.SLS_TOT_SUB, 0)) AS RTL_TOT_SUB
FROM RTL.INVENTORY INV
	LEFT OUTER JOIN (
		SELECT
			INV_NBR,
			$companyNumber AS RCV_CO_NBR,
			SUM(CASE WHEN HED.RCV_CO_NBR=$companyNumber AND IVC_TYP IN ('RC', 'XF') THEN DET.ORD_Q ELSE 0 END) AS RCV_Q,
			SUM(CASE WHEN HED.SHP_CO_NBR=$companyNumber AND IVC_TYP IN ('XF') THEN DET.ORD_Q ELSE 0 END) AS TRF_Q,
			SUM(CASE WHEN HED.SHP_CO_NBR=$companyNumber AND IVC_TYP IN ('RT') THEN DET.ORD_Q ELSE 0 END) AS RTR_Q,
			SUM(CASE WHEN HED.SHP_CO_NBR=$companyNumber AND HED.RCV_CO_NBR=$WhseNbrDef AND IVC_TYP IN ('RT') THEN DET.ORD_Q ELSE 0 END) AS RTRMTS_Q,
			SUM(CASE WHEN HED.SHP_CO_NBR=$companyNumber AND HED.RCV_CO_NBR!=$WhseNbrDef AND IVC_TYP IN ('RT') THEN DET.ORD_Q ELSE 0 END) AS RTRSPL_Q,
			SUM(CASE WHEN HED.SHP_CO_NBR=$companyNumber AND IVC_TYP IN ('CR') THEN DET.ORD_Q ELSE 0 END) AS COR_Q,
			SUM(CASE WHEN HED.SHP_CO_NBR=$companyNumber AND IVC_TYP IN ('SL') THEN DET.ORD_Q ELSE 0 END) AS SLS_Q,

			SUM(CASE WHEN HED.RCV_CO_NBR=$companyNumber AND IVC_TYP IN ('RC', 'XF') THEN DET.TOT_SUB ELSE 0 END) AS RCV_TOT_SUB,
			SUM(CASE WHEN HED.SHP_CO_NBR=$companyNumber AND IVC_TYP IN ('XF') THEN DET.TOT_SUB ELSE 0 END) AS TRF_TOT_SUB,
			SUM(CASE WHEN HED.SHP_CO_NBR=$companyNumber AND IVC_TYP IN ('RT') THEN DET.TOT_SUB ELSE 0 END) AS RTR_TOT_SUB,
			SUM(CASE WHEN HED.SHP_CO_NBR=$companyNumber AND HED.RCV_CO_NBR=$WhseNbrDef AND IVC_TYP IN ('RT') THEN DET.TOT_SUB ELSE 0 END) AS RTRMTS_TOT_SUB,
			SUM(CASE WHEN HED.SHP_CO_NBR=$companyNumber AND HED.RCV_CO_NBR!=$WhseNbrDef AND IVC_TYP IN ('RT') THEN DET.TOT_SUB ELSE 0 END) AS RTRSPL_TOT_SUB,
			SUM(CASE WHEN HED.SHP_CO_NBR=$companyNumber AND IVC_TYP IN ('CR') THEN DET.TOT_SUB ELSE 0 END) AS COR_TOT_SUB,
			SUM(CASE WHEN HED.SHP_CO_NBR=$companyNumber AND IVC_TYP IN ('SL') THEN DET.TOT_SUB ELSE 0 END) AS SLS_TOT_SUB,

			SUM(COALESCE(DET.ORD_Q, 0)) AS ORD_Q,
			SUM(COALESCE(DET.DISC_PCT, 0)) AS DISC_PCT,
			SUM(COALESCE(DET.DISC_AMT, 0)) AS DISC_AMT,
			SUM(COALESCE(DET.FEE_MISC, 0)) AS FEE_MISC
		FROM RTL.RTL_STK_DET DET
			LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR
		WHERE HED.DEL_F = 0 AND DATE(HED.CRT_TS) <= '$endDate' AND (
				(HED.RCV_CO_NBR=$companyNumber AND IVC_TYP IN ('RC', 'XF'))
				OR (HED.SHP_CO_NBR=$companyNumber AND IVC_TYP IN ('XF'))
				OR (HED.SHP_CO_NBR=$companyNumber AND IVC_TYP IN ('RT'))
				OR (HED.SHP_CO_NBR=$companyNumber AND HED.RCV_CO_NBR=$WhseNbrDef AND IVC_TYP IN ('RT'))
				OR (HED.SHP_CO_NBR=$companyNumber AND HED.RCV_CO_NBR!=$WhseNbrDef AND IVC_TYP IN ('RT'))
				OR (HED.SHP_CO_NBR=$companyNumber AND IVC_TYP IN ('CR'))
				OR (HED.SHP_CO_NBR=$companyNumber AND IVC_TYP IN ('SL'))
			)
		GROUP BY DET.INV_NBR
	) STK ON INV.INV_NBR=STK.INV_NBR
	LEFT OUTER JOIN (
		SELECT
			SUM(RTL_Q) AS RTL_Q,
			INV_NBR,
			RTL_BRC,
			CO_NBR,
			SUM(TND_AMT) AS TND_AMT,
			SUM((DISC_PCT / 100) * TND_AMT) AS DISC_PCT_AMT,
			SUM(DISC_AMT) AS DISC_AMT
		FROM RTL.CSH_REG CSH
		WHERE ACT_F=0 AND RTL_BRC <> '' AND CSH_FLO_TYP IN ('RT') AND CO_NBR=$companyNumber AND DATE(CRT_TS) <= '$endDate'
		GROUP BY INV_NBR
	) CSH ON CSH.INV_NBR=INV.INV_NBR
	LEFT OUTER JOIN(
		SELECT 
			INV.INV_BCD AS INV_BCD,
			DET.INV_NBR,
			SUM(MOV_Q) AS MOV_Q,
			RCV_CO_NBR 
		FROM RTL.INV_MOV MOV 
			INNER JOIN RTL.RTL_STK_DET DET ON DET.ORD_DET_NBR=MOV.ORD_DET_NBR 
			INNER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
			LEFT OUTER JOIN RTL.INVENTORY INV ON INV.INV_NBR=DET.INV_NBR  
		WHERE MOV.ORD_DET_NBR!='' AND MOV.DEL_NBR=0 AND HED.DEL_F = 0 AND HED.RCV_CO_NBR=$companyNumber
		GROUP BY INV.INV_NBR
	) MOV ON INV.INV_NBR=MOV.INV_NBR
	LEFT OUTER JOIN RTL.CAT CAT ON INV.CAT_NBR=CAT.CAT_NBR
	LEFT OUTER JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR
	LEFT OUTER JOIN RTL.CAT_SHLF SLF ON INV.CAT_DISC_NBR=SLF.CAT_SHLF_NBR
	LEFT OUTER JOIN CMP.COMPANY SPL ON INV.CO_NBR=SPL.CO_NBR
	LEFT OUTER JOIN CMP.CITY SPLCTY ON SPL.CITY_ID=SPLCTY.CITY_ID
	LEFT OUTER JOIN CMP.COMPANY LOC ON STK.RCV_CO_NBR=LOC.CO_NBR
WHERE " . $whereClauses . "
GROUP BY " . $groupClause . "
ORDER BY " . $groupClause;
//echo "<pre/>".$query;
$pagination = pagination($query, 1000);

$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'pagination' => $pagination,
	'total' => array()
);
$result = mysql_query($pagination['query']);

while($row = mysql_fetch_assoc($result)) {
	$results['data'][] = $row;

	$results['total']['RCV_Q'] += $row['RCV_Q'];
	$results['total']['TRF_Q'] += $row['TRF_Q'];
	$results['total']['RTR_Q'] += $row['RTR_Q'];
	$results['total']['RTL_Q'] += $row['RTL_Q'];
	$results['total']['COR_Q'] += $row['COR_Q'];
	$results['total']['MOV_Q'] += $row['MOV_Q'];
	$results['total']['ITM_Q'] += $row['ITM_Q'];
	$results['total']['BALANCE'] += $row['BALANCE'];
	$results['total']['BALANCE_PRC'] += $row['BALANCE_PRC'];
	$results['total']['BALANCE_RTL_PRC'] += $row['BALANCE_RTL_PRC'];
}

echo json_encode($results);