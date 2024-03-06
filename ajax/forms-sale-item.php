<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

$companyNumber  = $CoNbrDef;
$beginDate 		= $_GET['BEG_DT'];
$endDate 		= $_GET['END_DT'];
$days 			= $_GET['DAYS'];
$months 		= $_GET['MONTHS'];
$years 			= $_GET['YEARS'];
$day  			= $_GET['DAY'];
$month 			= $_GET['MONTH'];
$year 			= $_GET['YEAR'];
$consignment 	= $_GET['CNMT_F'];
$plusMode 		= $_GET['PLUS'];
$invoiceNumber 	= $_GET['IVC_NBR'];
$searchQuery	= strtoupper($_REQUEST['s']);
$groups 		= (array) $_GET['GROUP'];

if (!empty($_GET['CO_NBR'])) {
	$companyNumber = $_GET['CO_NBR'];
}

$detailClauses	= array("CSH.ACT_F=0","CSH.CSH_FLO_TYP='RT'");
$whereClauses 	= array("CSH.ACT_F=0","CSH.CSH_FLO_TYP='RT'");
$groupClauses 	= array();

$detailClauses[] 	= "CSH.CO_NBR=" . $companyNumber;
$whereClauses[] 	= "CSH.CO_NBR=" . $companyNumber;

if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$detailClauses[] 	= "DATE(CSH.CRT_TS)=CURRENT_DATE - INTERVAL " . $days . " DAY";
		$whereClauses[] 	= "DATE(CSH.CRT_TS)=CURRENT_DATE - INTERVAL " . $days . " DAY";
	}

	if ($months != "") {
		$detailClauses[] 	= "MONTH(CSH.CRT_TS)=MONTH(CURRENT_DATE - INTERVAL " . $months . " MONTH) AND YEAR(CSH.CRT_TS)=YEAR(CURRENT_DATE - INTERVAL " . $months . " MONTH)";
		$whereClauses[] 	= "MONTH(CSH.CRT_TS)=MONTH(CURRENT_DATE - INTERVAL " . $months . " MONTH) AND YEAR(CSH.CRT_TS)=YEAR(CURRENT_DATE - INTERVAL " . $months . " MONTH)";
	}

	if ($years != "") {
		$detailClauses[] 	= "YEAR(CSH.CRT_TS)=YEAR(CURRENT_DATE - INTERVAL " . $years . " MONTH)";
		$whereClauses[] 	= "YEAR(CSH.CRT_TS)=YEAR(CURRENT_DATE - INTERVAL " . $years . " MONTH)";
	}

	if ($day != "") {
		$detailClauses[] 	= "DAY(CSH.CRT_TS)=" . $day;
		$whereClauses[] 	= "DAY(CSH.CRT_TS)=" . $day;
	}

	if ($month != "") {
		$detailClauses[] 	= "MONTH(CSH.CRT_TS)=" . $month;
		$whereClauses[] 	= "MONTH(CSH.CRT_TS)=" . $month;
	}

	if ($year != "") {
		$detailClauses[] 	= "YEAR(CSH.CRT_TS)=" . $year;
		$whereClauses[] 	= "YEAR(CSH.CRT_TS)=" . $year;
	}
} else {
	if (!empty($beginDate)) {
		$detailClauses[] 	= "DATE(CSH.CRT_TS) >= '" . $beginDate . "'";
		$whereClauses[] 	= "DATE(CSH.CRT_TS) >= '" . $beginDate . "'";
	}

	if (!empty($endDate)) {
		$detailClauses[] 	= "DATE(CSH.CRT_TS) <= '" . $endDate . "'";
		$whereClauses[] 	= "DATE(CSH.CRT_TS) <= '" . $endDate . "'";
	}
}

if ($plusMode == 1) {
	$whereClausesPlusMode = array();

	if ((int) getDbParam("PKP_TAX_F") == 1) {
		$whereClausesPlusMode[] = "CSH.TAX_F=1";
	}

	if ((int) getDbParam("PKP_CARD") == 1) {
		$whereClausesPlusMode[] = "CSH.REG_NBR_PLUS IS NOT NULL AND CSH.TRSC_NBR_PLUS IS NOT NULL";
	}

	$whereClauses[] = sprintf("(%s)", implode(" OR ", $whereClausesPlusMode));
}

if ($plusMode == 2) {
	$whereClausesPlusMode = array();

	if ((int) getDbParam("PKP_TAX_F") == 1) {
		$whereClausesPlusMode[] = "CSH.TAX_F=0";
	}

	if ((int) getDbParam("PKP_CARD") == 1) {
		$whereClausesPlusMode[] = "CSH.REG_NBR_PLUS IS NULL AND CSH.TRSC_NBR_PLUS IS NULL";
	}

	$whereClauses[] = sprintf("(%s)", implode(" AND ", $whereClausesPlusMode));
}

if ($consignment == 1) {
	$whereClauses[] = "CSH.INV_CNMT_F=1";
}

if ($consignment == 2) {
	$whereClauses[]		= "CSH.INV_CNMT_F=0";
}

if ($_GET['INV_NBR'] != "") {
	$detailClauses[]	= "CSH.INV_NBR = '" . $_GET['INV_NBR'] . "'";
	$whereClauses[]		= "CSH.INV_NBR = '" . $_GET['INV_NBR'] . "'";
}

if ($_GET['RTL_BRC'] != "") {
	$detailClauses[]	= "CSH.RTL_BRC = '" . $_GET['RTL_BRC'] . "'";
	$whereClauses[]		= "CSH.RTL_BRC = '" . $_GET['RTL_BRC'] . "'";
}

if ($_GET['SPL_NBR'] != "") {
	$detailClauses[]	= "CSH.SPL_NBR = '" . $_GET['SPL_NBR'] . "'";
	$whereClauses[]		= "CSH.SPL_NBR = '" . $_GET['SPL_NBR'] . "'";
}

if ($_GET['CAT_NBR'] != "") {
	$detailClauses[]	= "CSH.CAT_NBR = '" . $_GET['CAT_NBR'] . "'";
	$whereClauses[]		= "CSH.CAT_NBR = '" . $_GET['CAT_NBR'] . "'";
}

if ($_GET['CAT_SUB_NBR'] != "") {
	$detailClauses[]	= "CSH.CAT_SUB_NBR = '" . $_GET['CAT_SUB_NBR'] . "'";
	$whereClauses[]		= "CSH.CAT_SUB_NBR = '" . $_GET['CAT_SUB_NBR'] . "'";
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
		
		if (in_array("CAT_SUB_NBR", $groups)) {
			$whereClauses[] = "(
				CSH.INV_NBR LIKE '" . $query . "'
				OR INV.NAME LIKE '" . $query . "'
				OR CSH.RTL_BRC LIKE '" . $query . "'
				OR CSH.CAT_SUB_NBR LIKE '" . $query . "'
				OR SUB.CAT_SUB_DESC LIKE '" . $query . "'
			)";
		} elseif (in_array("SPL_NBR", $groups)) {
			$whereClauses[] = "(
				CSH.INV_NBR LIKE '" . $query . "'
				OR INV.NAME LIKE '" . $query . "'
				OR CSH.RTL_BRC LIKE '" . $query . "'
				OR INV.CO_NBR LIKE '" . $query . "'
				OR SPL.NAME LIKE '" . $query . "'
			)";
		} else {
			$whereClauses[] = "(
				CSH.INV_NBR LIKE '" . $query . "'
				OR INV.NAME LIKE '" . $query . "'
				OR CSH.RTL_BRC LIKE '" . $query . "'
				OR INV.CO_NBR LIKE '" . $query . "'
				OR SPL.NAME LIKE '" . $query . "'
				OR CSH.CAT_SUB_NBR LIKE '" . $query . "'
				OR SUB.CAT_SUB_DESC LIKE '" . $query . "'
			)";
		}
	}
}

if (count($groups) > 0) {
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(CRT_TS)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(CRT_TS), MONTH(CRT_TS)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(CRT_TS), MONTH(CRT_TS), DAY(CRT_TS)";
				break;
			case "CAT_SUB_NBR":
				$groupClauses[] = "CAT_SUB_NBR";
				break;
			case "SPL_NBR":
				$groupClauses[] = "SPL_NBR";
				break;
			case "TRSC_NBR":
				$groupClauses[] = "CSH.TRSC_NBR";
				break;
			case "POS_ID":
				$groupClauses[] = "CSH.POS_ID";
				break;
			default:
				$groupClauses[] = "RTL_BRC";
				break;
		}
	}
} else {
	$groupClauses = array("RTL_BRC");
}

$detailClauses = implode(" AND ", $detailClauses);
$whereClauses = implode(" AND ", $whereClauses);
$groupClauses = implode(", ", $groupClauses);

$query = "SELECT
	CSH.TRSC_NBR,
	MAX(CSH.TRSC_NBR_PLUS) AS TRSC_NBR_PLUS,
	COALESCE(CSH.CO_NBR,0) AS CO_NBR,
	CSH.RTL_BRC,
	SUM(CASE WHEN CSH.RTL_Q >= 0 THEN CSH.RTL_Q ELSE 0 END) AS RTL_Q,
	SUM(CASE WHEN CSH.RTL_Q < 0 THEN CSH.RTL_Q ELSE 0 END) AS RTR_Q,
	SUM(TTL.BRT_AMT) AS BRT_AMT,
	SUM(TTL.DISC_PCT_AMT + TTL.DISC_AMT + (TRSC.DISC_FLO_AMT * TTL.BRT_AMT)) AS DISC_AMT,
	SUM(TTL.BRT_AMT - (TTL.DISC_PCT_AMT + TTL.DISC_AMT + (TRSC.DISC_FLO_AMT * TTL.BRT_AMT))) AS NETT_AMT,
	SUM(PYMT.PYMT_TYP_CSH) * COUNT(DISTINCT CSH.TRSC_NBR) / COUNT(*) AS PYMT_TYP_CSH,
	SUM(PYMT.PYMT_TYP_DEB) * COUNT(DISTINCT CSH.TRSC_NBR) / COUNT(*) AS PYMT_TYP_DEB,
	SUM(PYMT.PYMT_TYP_CHK) * COUNT(DISTINCT CSH.TRSC_NBR) / COUNT(*) AS PYMT_TYP_CHK,
	SUM(PYMT.PYMT_TYP_WRE) * COUNT(DISTINCT CSH.TRSC_NBR) / COUNT(*) AS PYMT_TYP_WRE,
	SUM(PYMT.PYMT_TYP_CRT) * COUNT(DISTINCT CSH.TRSC_NBR) / COUNT(*) AS PYMT_TYP_CRT,
	SUM(PYMT.PYMT_TYP_TRF) * COUNT(DISTINCT CSH.TRSC_NBR) / COUNT(*) AS PYMT_TYP_TRF,
	SUM(PYMT.PYMT_TYP_VCR) * COUNT(DISTINCT CSH.TRSC_NBR) / COUNT(*) AS PYMT_TYP_VCR,
	SUM(PYMT.CHG_AMT) * COUNT(DISTINCT CSH.TRSC_NBR) / COUNT(*) AS CHG_AMT,
	CSH.ACT_F,
	COALESCE(INV.NAME, 'Unknown') AS INV_NAME,
	INV.INV_PRC,
	CSH.RTL_PRC,
	INV.CO_NBR,
	COALESCE(SPL.NAME, 'Unknown') AS SPL_NAME,
	CASE WHEN SPL.ADDRESS <> '' THEN CONCAT(SPL.ADDRESS, ', ', SPL_CTY.CITY_NM) ELSE SPL_CTY.CITY_NM END AS SPL_ADDR,
	INV.CAT_NBR,
	COALESCE(CAT.CAT_DESC, 'Unknown') AS CAT_DESC,
	INV.CAT_SUB_NBR,
	COALESCE(SUB.CAT_SUB_DESC, 'Unknown') AS CAT_SUB_DESC,
	INV.CAT_DISC_NBR,
	COALESCE(DSC.CAT_DISC_DESC, 'Unknown') AS CAT_DISC_DESC,
	COALESCE(CSH.POS_ID, 0) AS POS_ID,
	COALESCE(CSH.CRT_NBR, -1) AS CRT_NBR,
	COALESCE(CRT.NAME, 'Unknown') AS CRT_NAME,
	MAX(CSH.CRT_TS) AS CRT_TS
FROM RTL.CSH_REG CSH
	INNER JOIN (
		SELECT CSH.REG_NBR, CSH.CO_NBR, CSH.POS_ID,
			SUM(CASE 
				WHEN CSH.CSH_FLO_TYP='RT' THEN CSH.TND_AMT 
				WHEN CSH.CSH_FLO_TYP='FL' THEN CSH.TND_AMT 
				ELSE 0 END
			) AS BRT_AMT,
			SUM(CASE WHEN CSH.CSH_FLO_TYP='RT' THEN COALESCE((CSH.DISC_PCT/100)*CSH.TND_AMT, 0) ELSE 0 END) AS DISC_PCT_AMT,
			SUM(CASE WHEN CSH.CSH_FLO_TYP='RT' THEN COALESCE(CSH.DISC_AMT, 0) ELSE 0 END) AS DISC_AMT,
			SUM(CASE WHEN CSH.CSH_FLO_TYP='RT' THEN ABS(CSH.RTL_Q) ELSE 0 END) AS RTL_AMT
		FROM RTL.CSH_REG CSH
		WHERE " . $detailClauses . "
		GROUP BY CSH.REG_NBR, CSH.CO_NBR, CSH.POS_ID
	) TTL ON TTL.REG_NBR=CSH.REG_NBR AND TTL.CO_NBR=CSH.CO_NBR AND TTL.POS_ID=CSH.POS_ID
	LEFT OUTER JOIN (
		SELECT TRSC_NBR, CO_NBR, POS_ID,
			SUM(CASE WHEN CSH_FLO_TYP='PA' AND PYMT_TYP='CSH' THEN TND_AMT ELSE 0 END) AS PYMT_TYP_CSH,
			SUM(CASE WHEN CSH_FLO_TYP='PA' AND PYMT_TYP='DEB' THEN TND_AMT ELSE 0 END) AS PYMT_TYP_DEB,
			SUM(CASE WHEN CSH_FLO_TYP='PA' AND PYMT_TYP='CHK' THEN TND_AMT ELSE 0 END) AS PYMT_TYP_CHK,
			SUM(CASE WHEN CSH_FLO_TYP='PA' AND PYMT_TYP='WRE' THEN TND_AMT ELSE 0 END) AS PYMT_TYP_WRE,
			SUM(CASE WHEN CSH_FLO_TYP='PA' AND PYMT_TYP='CRT' THEN TND_AMT ELSE 0 END) AS PYMT_TYP_CRT,
			SUM(CASE WHEN CSH_FLO_TYP='PA' AND PYMT_TYP='TRF' THEN TND_AMT ELSE 0 END) AS PYMT_TYP_TRF,
			SUM(CASE WHEN CSH_FLO_TYP='PA' AND PYMT_TYP='VCR' THEN TND_AMT ELSE 0 END) AS PYMT_TYP_VCR,
			SUM(CASE WHEN CSH_FLO_TYP='CH' THEN TND_AMT ELSE 0 END) AS CHG_AMT
		FROM RTL.CSH_REG
		GROUP BY TRSC_NBR, CO_NBR, POS_ID
	) PYMT ON PYMT.TRSC_NBR=CSH.TRSC_NBR AND PYMT.CO_NBR=CSH.CO_NBR AND PYMT.POS_ID=CSH.POS_ID
	LEFT OUTER JOIN (
		SELECT TRSC_NBR, CO_NBR, POS_ID,
			SUM(CASE WHEN CSH_FLO_TYP='PN' THEN TND_AMT / (SELECT SUM(TND_AMT) AS TND_AMT FROM RTL.CSH_REG WHERE CSH_FLO_TYP='RT' AND TRSC_NBR=CSH.TRSC_NBR GROUP BY TRSC_NBR) ELSE 0 END) AS PN_AMT,
			SUM(CASE WHEN CSH_FLO_TYP='SU' THEN TND_AMT / (SELECT SUM(TND_AMT) AS TND_AMT FROM RTL.CSH_REG WHERE CSH_FLO_TYP='RT' AND TRSC_NBR=CSH.TRSC_NBR GROUP BY TRSC_NBR) ELSE 0 END) AS SU_AMT,
			SUM(CASE WHEN CSH_FLO_TYP='DS' THEN TND_AMT / (SELECT SUM(TND_AMT) AS TND_AMT FROM RTL.CSH_REG WHERE CSH_FLO_TYP='RT' AND TRSC_NBR=CSH.TRSC_NBR GROUP BY TRSC_NBR) ELSE 0 END) AS DISC_FLO_AMT
		FROM RTL.CSH_REG CSH
		WHERE " . $detailClauses . "
		GROUP BY TRSC_NBR
	) TRSC ON TRSC.TRSC_NBR=CSH.TRSC_NBR AND TRSC.CO_NBR=CSH.CO_NBR AND TRSC.POS_ID=CSH.POS_ID
	LEFT OUTER JOIN RTL.INVENTORY INV ON CSH.RTL_BRC <> '' AND CSH.RTL_BRC=INV.INV_BCD
	LEFT OUTER JOIN CMP.COMPANY SPL ON INV.CO_NBR=SPL.CO_NBR
	LEFT OUTER JOIN CMP.CITY SPL_CTY ON SPL.CITY_ID=SPL_CTY.CITY_ID
	LEFT OUTER JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR
	LEFT OUTER JOIN RTL.CAT CAT ON SUB.CAT_NBR=CAT.CAT_NBR
	LEFT OUTER JOIN RTL.CAT_DISC DSC ON INV.CAT_DISC_NBR=DSC.CAT_DISC_NBR
	LEFT OUTER JOIN CMP.PEOPLE CRT ON CSH.CRT_NBR=CRT.PRSN_NBR
WHERE " . $whereClauses . "
GROUP BY " . $groupClauses . "
ORDER BY " . $groupClauses;

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
	$row['CRT_NAME'] = dispNameScreen($row['CRT_NAME']);

	$results['data'][] = $row;

	$results['total']['TRSC_AMT'] += $row['TRSC_AMT'];
	$results['total']['RTL_Q'] += $row['RTL_Q'];
	$results['total']['RTR_Q'] += $row['RTR_Q'];
	$results['total']['INV_PRC_AMT'] += $row['INV_PRC_AMT'];
	$results['total']['BRT_AMT'] += $row['BRT_AMT'];
	$results['total']['DISC_AMT'] += $row['DISC_AMT'];
	$results['total']['NETT_AMT'] += $row['NETT_AMT'];
}

echo json_encode($results);