<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$searchQuery    = strtoupper($_REQUEST['s']);
$group 			= $_GET['GROUP'];
$whereClauses    = array("0=0");

if ($_GET['PRSN_NBR'] != ""){
	$whereClauses[] = "ACCT_EXEC_NBR = '" . $_GET['PRSN_NBR'] . "'";
}

if ($_GET['BUY_CO_NBR'] != ""){
	$whereClauses[] = "BUY_CO_NBR = '" . $_GET['BUY_CO_NBR'] . "'";
}

if ($_GET['PAY_CONFIG_NBR'] != ""){
	$whereClauses[] = "PAY_CONFIG_NBR = '" . $_GET['PAY_CONFIG_NBR'] . "'";
}

switch (strtoupper($group)) {
	case "ORD_NBR":
		$groupClause = "ORD_NBR";
		break;
	case "BUY_CO_NBR":
		$groupClause = "BUY_CO_NBR";
		break;
	case "PYMT_NBR":
	default:
		$groupClause = "PYMT_NBR";
		break;
}

$whereClauses = implode(" AND ", $whereClauses);

$query = "SELECT
	PYMT_NBR,
	ORD_NBR,
	DATE(ORD_TS) AS ORD_DTE,
	REF_NBR,
	ORD_TTL,
	ORD_STT_ID,
	ORD_STT_DESC,
	ACCT_EXEC_NBR,
	BUY_CO_NBR,
	BUY_CO_NAME,
	DUE_DTE,
	BILL_DTE,
	PRN_CO_NBR,
	PRN_CO_NAME,
	SUM(COALESCE(TOT_AMT,0)) AS TOT_AMT,
	SUM(COALESCE(TOT_REM,0)) AS TOT_REM,
	PYMT_TYP,
	SUM(COALESCE(TND_AMT,0)) AS TND_AMT,
	SUM(CASE WHEN ACT_F = 0 THEN COALESCE(TND_AMT,0) ELSE 0 END) AS TND_AMT_APV,
	SUM(CASE WHEN ACT_F = 1 THEN COALESCE(TND_AMT,0) ELSE 0 END) AS TND_AMT_NAPV,
	BNK_CO_NBR,
	VAL_NBR,
	DEPN_PCT,
	SUM(COALESCE(BNS_AMT,0)) AS BNS_AMT,
	SUM(CASE WHEN ACT_F = 0 THEN COALESCE(BNS_AMT,0) ELSE 0 END) AS BNS_AMT_APV,
	SUM(CASE WHEN ACT_F = 1 THEN COALESCE(BNS_AMT,0) ELSE 0 END) AS BNS_AMT_NAPV,
	CRT_NBR,
	DATE(CRT_TS) AS CRT_DTE,
	UPD_TS,
	PAY_CONFIG_NBR,
	ACT_F
FROM CDW.MKG_BNS
WHERE ".$whereClauses." 
GROUP BY ".$groupClause."
ORDER BY PYMT_NBR ASC";
//echo "<pre>".$query;
//exit();

$results = array(
	'parameter' => $_GET,
	'data' => array()
);

$result = mysql_query($query);
while($row = mysql_fetch_assoc($result)) {
	$results['data'][] = $row;
	
	$results['total']['TOT_AMT']		+= $row['TOT_AMT'];
	$results['total']['TOT_REM']		+= $row['TOT_REM'];
	$results['total']['TND_AMT']		+= $row['TND_AMT'];
	$results['total']['TND_AMT_APV']	+= $row['TND_AMT_APV'];
	$results['total']['TND_AMT_NAPV']	+= $row['TND_AMT_NAPV'];
	$results['total']['BNS_AMT']		+= $row['BNS_AMT'];
	$results['total']['BNS_AMT_APV']	+= $row['BNS_AMT_APV'];
	$results['total']['BNS_AMT_NAPV']	+= $row['BNS_AMT_NAPV'];
}

//$results['total']['TOT_AMT']	+= $results['total']['TOT_AMT'];

echo json_encode($results);