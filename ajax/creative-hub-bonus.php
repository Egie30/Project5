<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";

$query 	= "SELECT PAY_CONFIG_NBR, PAY_BEG_DTE, PAY_END_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_CONFIG_NBR = '" . $_GET['PAY_CONFIG_NBR'] . "'";
$result = mysql_query($query);
$row    = mysql_fetch_array($result);
$PayConfigNbr = $row['PAY_CONFIG_NBR'];
$PayBegDte    = $row['PAY_BEG_DTE'];
$PayEndDte    = $row['PAY_END_DTE'];

$searchQuery    = strtoupper($_REQUEST['s']);
$group 			= $_GET['GROUP'];
$whereClauses	= array("PYMT.DEL_NBR=0", "HED.DEL_NBR=0", "HED.ORD_STT_ID = 'CP'");

if ($_GET['PRSN_NBR'] != ""){
	$whereClauses[] = "HED.SLS_PRSN_NBR = '" . $_GET['PRSN_NBR'] . "'";
}

if ($_GET['BUY_CO_NBR'] != ""){
	$whereClauses[] = "BUY_CO_NBR = '" . $_GET['BUY_CO_NBR'] . "'";
}

if ($_GET['PAY_CONFIG_NBR'] != ""){
	$whereClauses[] = "DATE(CPJRN.CP_DTE) >= '". $PayBegDte ."'";
	$whereClauses[] = "DATE(CPJRN.CP_DTE) <= '". $PayEndDte ."'";
}

if ($_GET['TYP'] == "SPV"){
	$whereClauses[] = "PPL.POS_TYP IN ('SNM','SRM','RAM','NAM')";
}

switch (strtoupper($group)) {
	case "ORD_NBR":
		$groupClause = "ORD_NBR";
		break;
	case "BUY_CO_NBR":
		$groupClause = "BUY_CO_NBR";
		break;
	case "BUY_PRSN_NBR":
		$groupClause = "BUY_PRSN_NBR";
		break;
	case "SLS_PRSN_NBR":
		$groupClause = "HED.SLS_PRSN_NBR";
		break;
	case "PYMT_NBR":
	default:
		$groupClause = "PYMT_NBR";
		break;
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
			HED.ORD_NBR LIKE '" . $query . "'
			OR HED.REF_NBR LIKE '" . $query . "'
			OR HED.ORD_TTL LIKE '" . $query . "'
			OR STT.ORD_STT_DESC LIKE '" . $query . "'
			OR HED.BUY_PRSN_NBR LIKE '" . $query . "'
			OR BUY.NAME LIKE '" . $query . "'
			OR HED.BUY_CO_NBR LIKE '" . $query . "'
			OR COM.NAME LIKE '" . $query . "'
		)";
	}
}

$whereClauses = implode(" AND ", $whereClauses);

$queryGlbl 	= "SELECT COALESCE(MKG_MIN_AMT,0) AS MKG_MIN_AMT, COALESCE(BNS_AMT,0) AS BNS_AMT FROM NST.PARAM_GLBL";
$resultGlbl	= mysql_query($queryGlbl);
$rowGlbl 	= mysql_fetch_array($resultGlbl);
$minPayment	= $rowGlbl['MKG_MIN_AMT'];
$bonusMkt  	= $rowGlbl['BNS_AMT'];

$querySpv 	= "SELECT COALESCE(SPV_CMSN_PCT,0) AS SPV_CMSN_PCT FROM NST.PARAM_LOC";
$resultSpv	= mysql_query($querySpv);
$rowSpv 	= mysql_fetch_array($resultSpv);
$bonusSpv  	= 1/$rowSpv['SPV_CMSN_PCT'];

$query = "SELECT			
	PYMT.PYMT_NBR,		
	HED.ORD_NBR,		
	DATE(HED.ORD_TS) AS ORD_DTE,		
	HED.ORD_TS,		
	HED.REF_NBR,		
	HED.ORD_TTL,		
	HED.ORD_STT_ID,		
	STT.ORD_STT_DESC,
	DATE(HED.CMP_TS) AS CMP_DTE,
	PPL.NAME,		
	HED.BUY_CO_NBR,		
	COM.NAME AS BUY_CO_NAME,
	HED.BUY_PRSN_NBR,		
	BUY.NAME AS BUY_PRSN_NAME,
	CASE 
		WHEN HED.BUY_CO_NBR != '' THEN COM.NAME
		WHEN HED.BUY_PRSN_NBR != '' THEN BUY.NAME
		ELSE 'Tunai'
	END AS BUY_DESC,
	TOT_AMT,
	TOT_REM,
	SUM(PYMT.TND_AMT) AS TOT_PYMT,		
	SUM(PYMT.TND_AMT*(1/100)) AS TOT_BNS,		
	SUM(PYMT.TND_AMT*(1/100)) * ".$bonusSpv." AS TOT_BNS_SPV,	
	PYMT.CRT_NBR,		
	PYMT.CRT_TS		
FROM CMP.RTL_ORD_PYMT PYMT			
	INNER JOIN CMP.RTL_ORD_HEAD HED ON PYMT.ORD_NBR = HED.ORD_NBR		
	INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID = STT.ORD_STT_ID		
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR		
	INNER JOIN CMP.PEOPLE BUY ON HED.BUY_PRSN_NBR = BUY.PRSN_NBR		
	INNER JOIN CMP.PEOPLE PPL ON HED.SLS_PRSN_NBR = PPL.PRSN_NBR		
	INNER JOIN(		
		SELECT	
			 ORD_NBR,
			 DATE(MIN(CRT_TS)) AS CP_DTE
	   FROM CMP.RTL_ORD_JRN		
	   WHERE ORD_STT_ID = ('CP')		
	   GROUP BY ORD_NBR		
	)CPJRN ON PYMT.ORD_NBR = CPJRN.ORD_NBR		
WHERE ". $whereClauses ."
GROUP BY ".$groupClause;

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
	$results['total']['TND_AMT_NETT_APV']	+= $row['TND_AMT_NETT_APV'];
	$results['total']['TND_AMT_APV']	+= $row['TND_AMT_APV'];
	$results['total']['TND_AMT_NAPV']	+= $row['TND_AMT_NAPV'];
	$results['total']['BNS_AMT']		+= $row['BNS_AMT'];
	$results['total']['TOT_BNS']		+= $row['TOT_BNS'];
	$results['total']['TOT_BNS_SPV']	+= $row['TOT_BNS_SPV'];
	$results['total']['BNS_SPV_AMT']	+= $row['BNS_SPV_AMT'];
	$results['total']['BNS_AMT_APV']	+= $row['BNS_AMT_APV'];
	$results['total']['BNS_AMT_NAPV']	+= $row['BNS_AMT_NAPV'];
}

//$results['total']['TOT_AMT']	+= $results['total']['TOT_AMT'];

echo json_encode($results);