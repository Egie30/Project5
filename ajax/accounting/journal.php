<?php
require_once __DIR__ . "/../../framework/database/connect.php";
require_once __DIR__ . "/../../framework/functions/default.php";
require_once __DIR__ . "/../../framework/pagination/pagination.php";

$searchQuery    = strtoupper($_REQUEST['s']);
$days 			= $_GET['DAYS'];
$months 		= $_GET['MONTHS'];
$years 			= $_GET['YEARS'];
$day  			= $_GET['DAY'];
$month 			= $_GET['MONTH'];
$year 			= $_GET['YEAR'];
$date 			= $_GET['DTE'];
$Actg			= $_GET['ACTG'];

$groups         = (array) $_GET['GROUP'];
$orders         = (array) $_GET['ORD_BY'];
$whereClauses   = array("HED.DEL_NBR=0");
$detailWhereClauses = array();

$plusMode 		= $_GET['PLUS'];

$whereClauses[] = "HED.BK_NBR = ".$_GET['BK_NBR'];


if ($_GET['GL_NBR'] != "") {
	$whereClauses[] = "HED.GL_NBR='" . $_GET['GL_NBR'] . "'";
	$detailWhereClauses[] = "DET.GL_NBR='" . $_GET['GL_NBR'] . "'";
}

if ($_GET['ACC_NBR'] != "") {
	$whereClauses[] = "DET.ACC_NBR='" . $_GET['ACC_NBR'] . "'";
}

if ($_GET['CD_CAT_NBR'] != "") {
	if (is_array($_GET['CD_CAT_NBR'])) {
		$whereClauses[] = "DET.CD_CAT_NBR IN (" . implode(", ", $_GET['CD_CAT_NBR']) . ")";
		$detailWhereClauses[] = "ACC.CD_CAT_NBR IN (" . implode(", ", $_GET['CD_CAT_NBR']) . ")";
	} else {
		$whereClauses[] = "DET.CD_CAT_NBR='" . $_GET['CD_CAT_NBR'] . "'";
		$detailWhereClauses[] = "ACC.CD_CAT_NBR='" . $_GET['CD_CAT_NBR'] . "'";
	}
}

if ($_GET['CD_ACC_NBR'] != "") {
	if (is_array($_GET['CD_ACC_NBR'])) {
		$whereClauses[] = "DET.CD_ACC_NBR IN (" . implode(", ", $_GET['CD_ACC_NBR']) . ")";
		$detailWhereClauses[] = "ACC.CD_ACC_NBR IN (" . implode(", ", $_GET['CD_ACC_NBR']) . ")";
	} else {
		$whereClauses[] = "DET.CD_ACC_NBR='" . $_GET['CD_ACC_NBR'] . "'";
		$detailWhereClauses[] = "ACC.CD_ACC_NBR='" . $_GET['CD_ACC_NBR'] . "'";
	}
}

if ($_GET['CD_NBR'] != "") {
	if (is_array($_GET['CD_NBR'])) {
		$whereClauses[] = "DET.CD_NBR IN (" . implode(", ", $_GET['CD_NBR']) . ")";
		$detailWhereClauses[] = "ACC.CD_NBR IN (" . implode(", ", $_GET['CD_NBR']) . ")";
	} else {
		$whereClauses[] = "DET.CD_NBR='" . $_GET['CD_NBR'] . "'";
		$detailWhereClauses[] = "ACC.CD_NBR='" . $_GET['CD_NBR'] . "'";
	}
}

if ($_GET['CD_SUB_ACC_NBR'] != "") {
	if (is_array($_GET['CD_SUB_ACC_NBR'])) {
		$whereClauses[] = "DET.CD_SUB_ACC_NBR IN (" . implode(", ", $_GET['CD_SUB_ACC_NBR']) . ")";
		$detailWhereClauses[] = "ACC.CD_SUB_ACC_NBR IN (" . implode(", ", $_GET['CD_SUB_ACC_NBR']) . ")";
	} else {
		$whereClauses[] = "DET.CD_SUB_ACC_NBR='" . $_GET['CD_SUB_ACC_NBR'] . "'";
		$detailWhereClauses[] = "ACC.CD_SUB_ACC_NBR='" . $_GET['CD_SUB_ACC_NBR'] . "'";
	}
}

if ($_GET['CD_SUB_NBR'] != "") {
	if (is_array($_GET['CD_SUB_NBR'])) {
		$whereClauses[] = "DET.CD_SUB_NBR IN (" . implode(", ", $_GET['CD_SUB_NBR']) . ")";
		$detailWhereClauses[] = "ACC.CD_SUB_NBR IN (" . implode(", ", $_GET['CD_SUB_NBR']) . ")";
	} else {
		$whereClauses[] = "DET.CD_SUB_NBR='" . $_GET['CD_SUB_NBR'] . "'";
		$detailWhereClauses[] = "ACC.CD_SUB_NBR='" . $_GET['CD_SUB_NBR'] . "'";
	}
}

if ($days != '') {
	$whereClauses[] = "DATE(HED.CRT_TS)=CURRENT_DATE - INTERVAL " . $days . " DAY";
}

if ($months != '') {
	$whereClauses[] = "MONTH(HED.CRT_TS)=MONTH(CURRENT_DATE - INTERVAL " . $months . " MONTH) AND YEAR(HED.CRT_TS)=YEAR(CURRENT_DATE - INTERVAL " . $months . " MONTH)";
}

if ($years != '') {
	$whereClauses[] = "YEAR(HED.CRT_TS)=YEAR(CURRENT_DATE - INTERVAL " . $years . " MONTH)";
}

if ($day != '') {
	$whereClauses[] = "DAY(HED.CRT_TS)=" . $day;
}

if ($month != '') {
	$whereClauses[] = "MONTH(HED.CRT_TS)=" . $month;
}

if ($year != '') {
	$whereClauses[] = "YEAR(HED.CRT_TS)=" . $year;
}

if ($date != '') {
	$whereClauses[] = "DATE(HED.CRT_TS)='" . $date . "' ";
}

if ($_GET['BEG_DT'] != "") {
	$whereClauses[] = "DATE(HED.CRT_TS) >= '" . $_GET['BEG_DT'] . "'";
}

if ($_GET['END_DT'] != "") {
	$whereClauses[] = "DATE(HED.CRT_TS) <= '" . $_GET['END_DT'] . "'";
}

if ($plusMode == 1) {
	if ((int) getDbParam("PKP_TAX_F") == 1) {
		$whereClauses[] = "TAX_F=1";
	}
}

if ($Actg != 0) {
	$whereClauses[] = "HED.ACTG_TYP = ".$Actg." ";
}

if ($locked == 1) {
	$whereClauses[] = "HED.ACTG_TYP = 2 ";
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

		$whereClauses[] = "(
			HED.REF LIKE '" . $query . "'
			OR HED.GL_DESC LIKE '" . $query . "'
		)";
	}
}

if (empty($detailWhereClauses)) {
	$detailWhereClauses[] = 1;
}

$whereClauses = implode(" AND ", $whereClauses);
$detailWhereClauses = implode(" AND ", $detailWhereClauses);

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(HED.CRT_TS)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(HED.CRT_TS), MONTH(HED.CRT_TS)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(HED.CRT_TS), MONTH(HED.CRT_TS), DAY(HED.CRT_TS)";
				break;
			case "CD_NBR":
				$groupClauses[] = "DET.CD_NBR";
				break;
			case "CD_CAT_NBR":
				$groupClauses[] = "DET.CD_CAT_NBR";
				break;
			case "CD_SUB_NBR":
				$groupClauses[] = "DET.CD_SUB_NBR";
				break;
			case "ACC_NBR":
				$groupClauses[] = "DET.ACC_NBR";
				break;
			default:
				$groupClauses[] = "HED.GL_NBR";
				break;
		}
	}
		
	$groupClause = implode(", ", $groupClauses);
} else {
	$groupClause = "HED.GL_NBR";
}

$orderClauses = array();

foreach ($orders as $field => $mode) {
	if (is_int($field)) {
		$field = $mode;
		$mode = "ASC";
	}

	switch (strtoupper($field)) {
		case "GL_NBR":
			$order = "HED.GL_NBR";
			break;
		case "ACC_NBR":
			$order = "DET.ACC_NBR";
			break;
		case "CD_NBR":
			$order = "DET.CD_NBR";
			break;
		case "CD_CAT_NBR":
			$order = "DET.CD_CAT_NBR";
			break;
		case "CD_SUB_NBR":
			$order = "DET.CD_SUB_NBR";
			break;
		default:
			$order = "DET.GL_DET_NBR";
			break;
	}

	$orderClauses[] = $order . " " . $mode;
}

$orderClauses = implode(", ", $orderClauses);

if (empty($orderClauses)) {
	$orderClauses = $groupClause;
}

$query = "SELECT
	HED.GL_NBR,
	HED.ACTG_TYP,
	COUNT(DISTINCT HED.GL_NBR) AS GL_AMT,
	COUNT(DISTINCT DET.GL_DET_NBR) AS GL_DET_AMT,
	COALESCE(DET.GL_DET_NBR, -1) AS GL_DET_NBR,
	COUNT(DISTINCT DET.CD_SUB_NBR) AS CD_SUB_AMT,
	COALESCE(DET.CD_SUB_NBR, -1) AS CD_SUB_NBR,
	SUM(COALESCE(DET.DEB, 0)) AS GL_DEB,
	SUM(COALESCE(DET.CRT, 0)) AS GL_CRT,
	SUM(COALESCE(DET.NETT, 0)) AS GL_NETT,
	HED.GL_DESC,
	HED.REF,
	DET.CD_NBR,
	DET.CD_ACC_NBR,
	DET.CD_DESC,
	DET.CD_SUB_NBR,
	DET.CD_SUB_ACC_NBR,
	DET.CD_SUB_DESC,
	DET.CD_CAT_NBR,
	DET.CD_CAT_DESC,
	DET.ACC_NBR,
	DET.ACC_DESC,
	HED.CRT_NBR,
	HED.UPD_NBR,
	IF (MAX(HED.GL_DTE), DATE(MAX(HED.GL_DTE)), '') AS GL_DTE,
	IF (MAX(HED.GL_DTE), DAY(MAX(HED.GL_DTE)), NULL) AS GL_DTE_DY,
	IF (MAX(HED.GL_DTE), MONTH(MAX(HED.GL_DTE)), NULL) AS GL_DTE_MO,
	IF (MAX(HED.GL_DTE), YEAR(MAX(HED.GL_DTE)), NULL) AS GL_DTE_YR,
	IF (MAX(HED.GL_DTE), UNIX_TIMESTAMP(MAX(HED.GL_DTE)), NULL) AS GL_DTE_TM,
	IF (MAX(HED.GL_DTE), HOUR(MAX(HED.GL_DTE)), NULL) AS GL_DTE_HR,
	IF (MAX(HED.GL_DTE), MINUTE(MAX(HED.GL_DTE)), NULL) AS GL_DTE_MNT,
	IF (MAX(HED.GL_DTE), TIME(MAX(HED.GL_DTE)), NULL) AS GL_DTE_TIME,
	DATE(MAX(HED.UPD_TS)) AS UPD_DTE,
	DAY(MAX(HED.UPD_TS)) AS UPD_DY,
	MONTH(MAX(HED.UPD_TS)) AS UPD_MO,
	YEAR(MAX(HED.UPD_TS)) AS UPD_YR,
	UNIX_TIMESTAMP(MAX(HED.UPD_TS)) AS UPD_TM,
	HOUR(MAX(HED.UPD_TS)) AS UPD_HR,
	MINUTE(MAX(HED.UPD_TS)) AS UPD_MNT,
	TIME(MAX(HED.UPD_TS)) AS UPD_TIME,
	MAX(HED.UPD_TS) AS UPD_TS,
	DATE(MAX(HED.CRT_TS)) AS CRT_DTE,
	DAY(MAX(HED.CRT_TS)) AS CRT_DY,
	MONTH(MAX(HED.CRT_TS)) AS CRT_MO,
	YEAR(MAX(HED.CRT_TS)) AS CRT_YR,
	UNIX_TIMESTAMP(MAX(HED.CRT_TS)) AS CRT_TM,
	HOUR(MAX(HED.CRT_TS)) AS CRT_HR,
	MINUTE(MAX(HED.CRT_TS)) AS CRT_MNT,
	TIME(MAX(HED.CRT_TS)) AS CRT_TIME,
	MAX(HED.CRT_TS) AS CRT_TS
FROM RTL.ACCTG_GL_HEAD HED
	LEFT OUTER JOIN (
		SELECT DET.GL_DET_NBR,
			DET.GL_NBR,
			ACC.ACC_NBR,
			ACC.ACC_DESC,
			ACC.CD_NBR,
			ACC.CD_ACC_NBR,
			ACC.CD_DESC,
			ACC.CD_SUB_NBR,
			ACC.CD_SUB_ACC_NBR,
			ACC.CD_SUB_DESC,
			ACC.CD_CAT_NBR,
			ACC.CD_CAT_DESC,
			COALESCE(DET.DEB, 0) AS DEB,
			COALESCE(DET.CRT, 0) AS CRT,
			CASE WHEN ACC.CD_CAT_NBR IN (1,5,6,9) THEN
				COALESCE(DET.DEB, 0) - COALESCE(DET.CRT, 0)
			ELSE 
				COALESCE(DET.CRT, 0) - COALESCE(DET.DEB, 0)
			END AS NETT,
			DET.UPD_TS,
			DET.UPD_NBR
		FROM RTL.ACCTG_GL_DET DET
			INNER JOIN(
				SELECT SUB.CD_SUB_NBR,
					SUB.CD_SUB_ACC_NBR,
					SUB.CD_SUB_DESC,
					ACC.CD_NBR,
					ACC.CD_ACC_NBR,
					ACC.CD_DESC,
					CAT.CD_CAT_NBR,
					CAT.CD_CAT_DESC,
					CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
					CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC,
					CONCAT(ACC.CD_ACC_NBR, '-', SUB.CD_SUB_ACC_NBR) AS ACC_SHORT_NBR,
					CONCAT(ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_SHORT_DESC
				FROM RTL.ACCTG_CD_SUB SUB
					INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
					INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
			) ACC ON ACC.CD_SUB_NBR=DET.CD_SUB_NBR
		WHERE " . $detailWhereClauses . "
		GROUP BY DET.GL_DET_NBR
	) DET ON HED.GL_NBR=DET.GL_NBR
WHERE " . $whereClauses . "
GROUP BY " . $groupClause . "
ORDER BY " . $orderClauses." DESC";

//echo "<pre>".$query;

$pagination = pagination($query, 1000);

$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'pagination' => $pagination,
	'total' => array()
);
$result = mysql_query($pagination['query']);

while($row = mysql_fetch_assoc($result)) {
	$row['GL_TOT'] = max($row['GL_DEB'], $row['GL_CRT']);
	
	$results['data'][] = $row;

	$results['total']['GL_AMT'] += $row['GL_AMT'];
	$results['total']['GL_DET_AMT'] += $row['GL_DET_AMT'];
	$results['total']['GL_TOT'] += $row['GL_TOT'];
	$results['total']['GL_DEB'] += $row['GL_DEB'];
	$results['total']['GL_CRT'] += $row['GL_CRT'];
	$results['total']['GL_NETT'] += $row['GL_NETT'];
}


echo json_encode($results);