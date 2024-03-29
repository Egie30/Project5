<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

/*
Don't forget to update TAX_F on CMP.COMPANY
*/

$StockMonth		= $_GET['STK_MONTH'];
$StockYear		= $_GET['STK_YEAR'];


if (empty($StockMonth)) {
		$query		= "SELECT MONTH(BEG_RPT) AS STK_MONTH FROM NST.PARAM_LOC";
		$result		= mysql_query($query);
		$row		= mysql_fetch_array($result);
		
		$_GET['STK_MONTH'] = $row['STK_MONTH'];
	}
	else { $StockMonth = $StockMonth; }

if (empty($StockYear)) {
		$query		= "SELECT YEAR(BEG_RPT) AS STK_YEAR FROM NST.PARAM_LOC";
		$result		= mysql_query($query);
		$row		= mysql_fetch_array($result);
		
		$_GET['STK_YEAR'] = $row['STK_YEAR'];
	}
	else { $StockYear= $StockYear; }


$query_day		= "SELECT LAST_DAY('".$StockYear."-".$StockMonth."-01') AS STK_DTE";
$result_day		= mysql_query($query_day);
$row_day		= mysql_fetch_array($result_day);

$dateStock		= $row_day['STK_DTE'];
	
	
	
echo $dateStock;

$IvcTyp			= $_GET['IVC_TYP'];
$Accounting		= $_GET['ACTG'];
$PrnDigType		= $_GET['PRN_DIG_TYP'];
$CatSubNbr		= $_GET['CAT_SUB_NBR'];

$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];

$CatSubType		= $_GET['CAT_SUB_TYP'];

$whereClauses 	= array("HED.DEL_F=0", "INV.CAT_NBR IN (1,10)", "DATE(HED.DL_TS) >= (SELECT BEG_RPT FROM NST.PARAM_LOC)");
$whereClausesAll= array("HED.DEL_F=0", "INV.CAT_NBR IN (1,10)", "HED.IVC_TYP = 'RC'", "DATE(HED.DL_TS) >= (SELECT BEG_RPT FROM NST.PARAM_LOC)");
$whereMovement	= array("MOV.DEL_NBR=0", "INV.CAT_NBR IN (1,10)", "DATE(MOV.CRT_TS) >= (SELECT BEG_RPT FROM NST.PARAM_LOC)", "DATE(HED.DL_TS) >= (SELECT BEG_RPT FROM NST.PARAM_LOC)");

/*
$whereClauses 	= array("HED.DEL_F=0", "INV.CAT_SUB_NBR IN (185)");
$whereClausesAll= array("HED.DEL_F=0", "INV.CAT_SUB_NBR IN (185)", "HED.IVC_TYP = 'RC'");
$whereMovement	= array("MOV.DEL_NBR=0", "INV.CAT_SUB_NBR IN (185)");
*/

if ((!empty($StockMonth)) && (!empty($StockYear))){

	$whereClauses[] 	= "DATE(HED.DL_TS) <= '" . $dateStock . "' ";
	$whereClausesAll[] 	= "DATE(HED.DL_TS) <= '" . $dateStock . "' ";
	$whereMovement[] 	= "DATE(MOV.CRT_TS) <= '" . $dateStock . "' ";
}

if (!empty($StockMonth)) {
	$whereClauses[] 	= "MONTH(HED.DL_TS) = '" . $StockMonth . "' ";
	$whereClausesAll[] 	= "MONTH(HED.DL_TS) = '" . $StockMonth . "' ";
	$whereMovement[] 	= "MONTH(MOV.CRT_TS) = '" . $StockMonth . "' ";
}

if (!empty($StockYear)) {
	$whereClauses[] 	= "YEAR(HED.DL_TS) = '" . $StockYear . "' ";
	$whereClausesAll[] 	= "YEAR(HED.DL_TS) = '" . $StockYear . "' ";
	$whereMovement[] 	= "YEAR(MOV.CRT_TS) = '" . $StockYear . "' ";
}

if ($Accounting == 0) {
	$whereClauses[] 	= "(HED.IVC_TYP = 'RC' OR HED.IVC_TYP = 'RT' OR HED.IVC_TYP = 'XF' OR HED.IVC_TYP = 'CR')";
}

if ($Accounting == 1) {
	$whereClauses[] 	= "HED.TAX_APL_ID IN ('I', 'A')";
}

if ($Accounting == 2) {
	$whereClauses[] 	= "((HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 1 AND IVC_TYP = 'RC') OR (HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 1 AND IVC_TYP = 'RT'))";
}

if ($Accounting == 3) {
	$whereClauses[] 	= "((HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 0 AND IVC_TYP = 'RC') OR (HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 0 AND IVC_TYP = 'RT'))";
}

$whereClauses[] 	= "INV.CAT_SUB_NBR != '202' ";
$whereClausesAll[] 	= "INV.CAT_SUB_NBR != '202' ";
$whereMovement[] 	= "INV.CAT_SUB_NBR != '202' ";


$whereClauses 	= implode(" AND ", $whereClauses);
$whereClausesAll= implode(" AND ", $whereClausesAll);
$whereMovement 	= implode(" AND ", $whereMovement);


if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "PRN_DIG_TYP":
				$groupClauses[] = "STK.PRN_DIG_TYP";
				break;
			case "CAT_SUB_NBR":
				$groupClauses[] = "STK.CAT_SUB_NBR";
				break;
			case "MONTH":
				$groupClauses[] = "RCV_PART.ORD_MONTH";
				break;
			default:
				$groupClauses[] = "STK.INV_NBR";
				break;
		}
	}
		
	$groupClause = "GROUP BY ".implode(", ", $groupClauses);
} else {
	$groupClause = "STK.INV_NBR";
}


if($_GET['RL_TYP'] == 'RL_YEAR') {
	
	$query = "SELECT MO.*, 
				STK.ORD_MONTH, 
				STK.ORD_MONTHNAME,
				STK.MOV_TOTAL,
				SUM(STK.RCV_Q) AS RCV_Q,
				SUM(STK.RTR_Q) AS RTR_Q,
				SUM(STK.COR_Q) AS COR_Q,
				SUM(STK.RCV_TOT_SUB) AS RCV_TOT_SUB,
				SUM(STK.RTR_TOT_SUB) AS RTR_TOT_SUB,
				SUM(STK.COR_TOT_SUB) AS COR_TOT_SUB,
				SUM(STK.MOV_QTY) AS MOV_QTY,
				SUM(STK.MOV_TOT_SUB) AS MOV_TOT_SUB,
				COALESCE(SUM(STK.BALANCE_Q), 0) AS BALANCE_Q,
				COALESCE(SUM(STK.BALANCE_AMT), 0) AS BALANCE_AMT
			FROM 
			(
				SELECT 1 AS ACT_MO, 'Januari' AS ACT_MO_NAME, '".$StockYear."' AS ACT_YEAR, LAST_DAY('".$StockYear."-01-01') AS LAST_DAY_MO UNION 
				SELECT 2 AS ACT_MO, 'Februari' AS ACT_MO_NAME, '".$StockYear."' AS ACT_YEAR, LAST_DAY('".$StockYear."-02-01') AS LAST_DAY_MO UNION 
				SELECT 3 AS ACT_MO, 'Maret' AS ACT_MO_NAME, '".$StockYear."' AS ACT_YEAR, LAST_DAY('".$StockYear."-03-01') AS LAST_DAY_MO UNION
				SELECT 4 AS ACT_MO, 'April' AS ACT_MO_NAME, '".$StockYear."' AS ACT_YEAR, LAST_DAY('".$StockYear."-04-01') AS LAST_DAY_MO UNION
				SELECT 5 AS ACT_MO, 'Mei' AS ACT_MO_NAME, '".$StockYear."' AS ACT_YEAR, LAST_DAY('".$StockYear."-05-01') AS LAST_DAY_MO UNION
				SELECT 6 AS ACT_MO, 'Juni' AS ACT_MO_NAME, '".$StockYear."' AS ACT_YEAR, LAST_DAY('".$StockYear."-06-01') AS LAST_DAY_MO UNION
				SELECT 7 AS ACT_MO, 'Juli' AS ACT_MO_NAME, '".$StockYear."' AS ACT_YEAR, LAST_DAY('".$StockYear."-07-01') AS LAST_DAY_MO UNION
				SELECT 8 AS ACT_MO, 'Agustus' AS ACT_MO_NAME, '".$StockYear."' AS ACT_YEAR, LAST_DAY('".$StockYear."-08-01') AS LAST_DAY_MO UNION
				SELECT 9 AS ACT_MO, 'September' AS ACT_MO_NAME, '".$StockYear."' AS ACT_YEAR, LAST_DAY('".$StockYear."-09-01') AS LAST_DAY_MO UNION
				SELECT 10 AS ACT_MO, 'Oktober' AS ACT_MO_NAME, '".$StockYear."' AS ACT_YEAR, LAST_DAY('".$StockYear."-10-01') AS LAST_DAY_MO UNION
				SELECT 11 AS ACT_MO, 'November' AS ACT_MO_NAME, '".$StockYear."' AS ACT_YEAR, LAST_DAY('".$StockYear."-11-01') AS LAST_DAY_MO UNION
				SELECT 12 AS ACT_MO, 'Desember' AS ACT_MO_NAME, '".$StockYear."' AS ACT_YEAR, LAST_DAY('".$StockYear."-12-01') AS LAST_DAY_MO)
				MO
				LEFT JOIN 
	(
SELECT 
	RCV_PART.ORD_MONTH,
	RCV_PART.ORD_MONTHNAME,
	RCV_PART.ORD_YEAR,
	RCV_PART.LAST_DAY_STK,
	RCV_PART.LAST_DAY_DTE,
	RCV_PART.PRN_DIG_EQP,
	SUM(RCV_PART.RCV_Q) AS RCV_Q,
	SUM(RCV_PART.RTR_Q) AS RTR_Q,
	SUM(RCV_PART.COR_Q) AS COR_Q,
	SUM(RCV_PART.RCV_TOT_SUB) AS RCV_TOT_SUB,
	SUM(RCV_PART.RTR_TOT_SUB) AS RTR_TOT_SUB,
	SUM(RCV_PART.COR_TOT_SUB) AS COR_TOT_SUB,
	SUM(MOV.MOV_QTY) AS MOV_QTY,
	SUM(MOV.MOV_TOT_SUB) AS MOV_TOT_SUB,
	(RCV_PART.RCV_Q / RCV_ALL.RCV_Q) AS PART,
	SUM((RCV_PART.RCV_Q / RCV_ALL.RCV_Q) * MOV.MOV_QTY) AS MOV_Q,
	SUM((RCV_PART.RCV_Q / RCV_ALL.RCV_Q) * MOV.MOV_TOT_SUB) AS MOV_TOTAL,
	SUM(COALESCE(RCV_PART.RCV_Q,0) - COALESCE(RCV_PART.RTR_Q,0) + COALESCE(RCV_PART.COR_Q,0) - COALESCE(((RCV_PART.RCV_Q / RCV_ALL.RCV_Q) * MOV.MOV_QTY),0)) AS BALANCE_Q,
	SUM(COALESCE(RCV_PART.RCV_TOT_SUB,0) - COALESCE(RCV_PART.RTR_TOT_SUB,0) + COALESCE(RCV_PART.COR_TOT_SUB,0) - COALESCE(((RCV_PART.RCV_Q / RCV_ALL.RCV_Q) * MOV.MOV_TOT_SUB),0)) AS BALANCE_AMT
FROM (
SELECT
			DATE(HED.DL_TS) AS ORD_DTE,
			YEAR(HED.DL_TS) AS ORD_YEAR,
			MONTH(HED.DL_TS) AS ORD_MONTH,
			DAY(HED.DL_TS) AS ORD_DAY,
			EQP.PRN_DIG_EQP,
			MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME,
			LAST_DAY(CONCAT(YEAR(HED.DL_TS),'-',MONTH(HED.DL_TS),'-01')) AS LAST_DAY_STK,
			CONCAT(YEAR(HED.DL_TS),'-',MONTH(HED.DL_TS),'-01') AS LAST_DAY_DTE,
			SUM(DET.INV_PRC) AS DET_INV_PRC,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN DET.ORD_Q ELSE 0 END) AS RCV_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN DET.TOT_SUB ELSE 0 END) AS RCV_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'RT' THEN DET.ORD_Q ELSE 0 END) AS RTR_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'RT' THEN DET.TOT_SUB ELSE 0 END) AS RTR_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN DET.ORD_Q ELSE 0 END) AS COR_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN DET.TOT_SUB ELSE 0 END) AS COR_TOT_SUB
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
		LEFT JOIN CMP.PRN_DIG_TYP TYP
			ON INV.PRD_PRC_TYP = TYP.PRN_DIG_TYP
		LEFT JOIN CMP.PRN_DIG_EQP EQP
			ON EQP.PRN_DIG_EQP = TYP.PRN_DIG_EQP
		WHERE ".$whereClauses."
		GROUP BY YEAR(HED.DL_TS), MONTH(HED.DL_TS)
) RCV_PART 
LEFT JOIN (
SELECT
			DATE(HED.DL_TS) AS ORD_DTE,
			YEAR(HED.DL_TS) AS ORD_YEAR,
			MONTH(HED.DL_TS) AS ORD_MONTH,
			DAY(HED.DL_TS) AS ORD_DAY,
			MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME,
			SUM(DET.INV_PRC) AS DET_INV_PRC,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN DET.ORD_Q ELSE 0 END) AS RCV_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN DET.TOT_SUB ELSE 0 END) AS RCV_TOT_SUB
		FROM RTL.RTL_STK_DET DET
		LEFT JOIN RTL.RTL_STK_HEAD HED
			ON DET.ORD_NBR = HED.ORD_NBR
		LEFT JOIN RTL.INVENTORY INV
			ON DET.INV_NBR = INV.INV_NBR
		LEFT JOIN RTL.CAT_SUB SUB
			ON INV.CAT_SUB_NBR = SUB.CAT_SUB_NBR
		LEFT JOIN RTL.CAT CAT
			ON CAT.CAT_NBR = SUB.CAT_NBR
		LEFT JOIN CMP.COMPANY SPL
			ON SPL.CO_NBR = HED.SHP_CO_NBR
		LEFT JOIN CMP.COMPANY RCV
			ON RCV.CO_NBR = HED.RCV_CO_NBR
		LEFT JOIN CMP.PRN_DIG_TYP TYP
			ON INV.PRD_PRC_TYP = TYP.PRN_DIG_TYP
		WHERE ".$whereClausesAll."
		GROUP BY YEAR(HED.DL_TS), MONTH(HED.DL_TS)
) RCV_ALL ON (RCV_PART.ORD_MONTH = RCV_ALL.ORD_MONTH AND RCV_PART.ORD_YEAR = RCV_ALL.ORD_YEAR)
LEFT JOIN (
	SELECT MOV.ORD_DET_NBR,
		MONTH(MOV.CRT_TS) AS MOV_MONTH,
		SUM(COALESCE(MOV.MOV_Q,0)) AS MOV_QTY,
		SUM(COALESCE(MOV.MOV_Q * MOV.DET_INV_PRC,0)) AS MOV_TOT_SUB,
		INV.INV_NBR,
		INV.NAME,
		TYP.PRN_DIG_TYP
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
		WHERE ".$whereMovement."
	GROUP BY YEAR(MOV.CRT_TS), MONTH(MOV.CRT_TS)
) MOV ON MOV.MOV_MONTH = RCV_PART.ORD_MONTH
GROUP BY RCV_PART.ORD_MONTH
) STK ON STK.ORD_MONTH = MO.ACT_MO
GROUP BY MO.ACT_MO";

}
else {
$query = "SELECT 
	RCV_PART.ORD_MONTH,
	RCV_PART.ORD_MONTHNAME,
	RCV_PART.ORD_YEAR,
	RCV_PART.PRN_DIG_TYP,
	SUM(RCV_PART.RCV_Q) AS RCV_Q,
	SUM(RCV_PART.RTR_Q) AS RTR_Q,
	SUM(RCV_PART.COR_Q) AS COR_Q,
	SUM(RCV_PART.RCV_TOT_SUB) AS RCV_TOT_SUB,
	SUM(RCV_PART.RTR_TOT_SUB) AS RTR_TOT_SUB,
	SUM(RCV_PART.COR_TOT_SUB) AS COR_TOT_SUB,
	SUM(MOV.MOV_QTY) AS MOV_QTY,
	SUM(MOV.MOV_TOT_SUB) AS MOV_TOT_SUB,
	(RCV_PART.RCV_Q / RCV_ALL.RCV_Q) AS PART,
	SUM((RCV_PART.RCV_Q / RCV_ALL.RCV_Q) * MOV.MOV_QTY) AS MOV_Q,
	SUM((RCV_PART.RCV_Q / RCV_ALL.RCV_Q) * MOV.MOV_TOT_SUB) AS MOV_TOTAL,
	SUM(COALESCE(RCV_PART.RCV_Q,0) - COALESCE(RCV_PART.RTR_Q,0) + COALESCE(RCV_PART.COR_Q,0) - COALESCE(((RCV_PART.RCV_Q / RCV_ALL.RCV_Q) * MOV.MOV_QTY),0)) AS BALANCE_Q,
	SUM(COALESCE(RCV_PART.RCV_TOT_SUB,0) - COALESCE(RCV_PART.RTR_TOT_SUB,0) + COALESCE(RCV_PART.COR_TOT_SUB,0) - COALESCE(((RCV_PART.RCV_Q / RCV_ALL.RCV_Q) * MOV.MOV_TOT_SUB),0)) AS BALANCE_AMT
FROM (
SELECT
			DATE(HED.DL_TS) AS ORD_DTE,
			YEAR(HED.DL_TS) AS ORD_YEAR,
			MONTH(HED.DL_TS) AS ORD_MONTH,
			DAY(HED.DL_TS) AS ORD_DAY,
			TYP.PRN_DIG_TYP,
			MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME,
			SUM(DET.INV_PRC) AS DET_INV_PRC,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN DET.ORD_Q ELSE 0 END) AS RCV_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN DET.TOT_SUB ELSE 0 END) AS RCV_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'RT' THEN DET.ORD_Q ELSE 0 END) AS RTR_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'RT' THEN DET.TOT_SUB ELSE 0 END) AS RTR_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN DET.ORD_Q ELSE 0 END) AS COR_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN DET.TOT_SUB ELSE 0 END) AS COR_TOT_SUB
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
		LEFT JOIN CMP.PRN_DIG_TYP TYP
			ON INV.PRD_PRC_TYP = TYP.PRN_DIG_TYP
		LEFT JOIN CMP.PRN_DIG_EQP EQP
			ON EQP.PRN_DIG_EQP = TYP.PRN_DIG_EQP
		WHERE ".$whereClauses."
		GROUP BY YEAR(HED.DL_TS), MONTH(HED.DL_TS)
		ORDER BY YEAR(HED.DL_TS), MONTH(HED.DL_TS)
) RCV_PART 
LEFT JOIN (
SELECT
			DATE(HED.DL_TS) AS ORD_DTE,
			YEAR(HED.DL_TS) AS ORD_YEAR,
			MONTH(HED.DL_TS) AS ORD_MONTH,
			DAY(HED.DL_TS) AS ORD_DAY,
			MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME,
			SUM(DET.INV_PRC) AS DET_INV_PRC,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN DET.ORD_Q ELSE 0 END) AS RCV_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN DET.TOT_SUB ELSE 0 END) AS RCV_TOT_SUB
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
		LEFT JOIN CMP.PRN_DIG_TYP TYP
			ON INV.PRD_PRC_TYP = TYP.PRN_DIG_TYP
		WHERE ".$whereClausesAll."
		GROUP BY YEAR(HED.DL_TS), MONTH(HED.DL_TS)
		ORDER BY YEAR(HED.DL_TS), MONTH(HED.DL_TS)
) RCV_ALL ON (RCV_PART.ORD_MONTH = RCV_ALL.ORD_MONTH AND RCV_PART.ORD_YEAR = RCV_ALL.ORD_YEAR)
LEFT JOIN (
	SELECT MOV.ORD_DET_NBR,
		MONTH(MOV.CRT_TS) AS MOV_MONTH,
		SUM(COALESCE(MOV.MOV_Q,0)) AS MOV_QTY,
		SUM(COALESCE(MOV.MOV_Q * MOV.DET_INV_PRC,0)) AS MOV_TOT_SUB,
		INV.INV_NBR,
		INV.NAME,
		TYP.PRN_DIG_TYP
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
		WHERE ".$whereMovement."
	GROUP BY YEAR(MOV.CRT_TS), MONTH(MOV.CRT_TS)
	ORDER BY YEAR(MOV.CRT_TS), MONTH(MOV.CRT_TS)
) MOV ON MOV.MOV_MONTH = RCV_PART.ORD_MONTH
";

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
	$results['total']['RTR_Q'] 		+= $row['RTR_Q'];
	$results['total']['COR_Q'] 		+= $row['COR_Q'];
	$results['total']['MOV_Q'] 		+= $row['MOV_Q'];
	$results['total']['MOV_QTY']	+= $row['MOV_QTY'];
	
	$results['total']['RCV_TOT_SUB']+= $row['RCV_TOT_SUB'];
	$results['total']['RTR_TOT_SUB']+= $row['RTR_TOT_SUB'];
	$results['total']['COR_TOT_SUB']+= $row['COR_TOT_SUB'];
	$results['total']['MOV_TOTAL']	+= $row['MOV_TOTAL'];
	
	$results['total']['BALANCE_Q'] 	+= $row['BALANCE_Q'];
	$results['total']['BALANCE_AMT']+= $row['BALANCE_AMT'];
	
}

echo json_encode($results);
