<?php 
include "framework/functions/default.php";
include "framework/database/connect.php";
include "framework/functions/crypt.php";

if (empty($_GET['YEAR'])) {
	$Year = date('Y');
} else {
	$Year = $_GET['YEAR'];
}

if (empty($_GET['MONTH'])) {
	$Month = date('m');
} else {
	$Month = $_GET['MONTH'];
}

$groups = (array) $_GET['GROUP'];

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "ACCT_EXEC_NBR":
				$groupClauses[] = "COM.ACCT_EXEC_NBR";
				break;
			case "BUY_CO_NBR":
				$groupClauses[] = "HED.BUY_CO_NBR";
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

$query = "SELECT
	COUNT(HED.ORD_NBR) AS CNT_ORD_NBR,
	HED.ORD_NBR,
	HED.ORD_TS,
	HED.ORD_TTL,
	HED.ORD_STT_ID,
	COM.ACCT_EXEC_NBR,
	HED.BUY_CO_NBR,
	COM.NAME AS BUY_CO_NAME,
	DATE(HED.DUE_TS) AS DUE_DTE,
	HED.PRN_CO_NBR,
	COM.NAME AS PRN_CO_NAME,
	HED.TOT_AMT,
	HED.TOT_REM,
	PYMT.PYMT_TYP,
	PYMT.TND_AMT,
	PYMT.BNK_CO_NBR,
	PYMT.VAL_NBR
FROM CMP.PRN_DIG_ORD_HEAD HED
	LEFT OUTER JOIN CMP.PRN_DIG_ORD_PYMT PYMT ON HED.ORD_NBR = PYMT.ORD_NBR AND PYMT.DEL_NBR=0
	INNER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
WHERE HED.DEL_NBR =0
	AND COM.ACCT_EXEC_NBR !=0
	AND (HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY) OR HED.BUY_CO_NBR IS NULL) 
	AND MONTH(HED.ORD_TS) =  '".$Month."' AND YEAR(HED.ORD_TS) =  '".$Year."'
GROUP BY ".$groupClause."
ORDER BY COM.NAME ASC";

$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'pagination' => $pagination,
	'total' => array(),
	'query' => $query
);
$result = mysql_query($query);

while($row = mysql_fetch_array($result)) {
	$results['data'][] 		= $row;
	$results['total']['TOT_AMT'] 	+= $row['TOT_AMT'];
	$results['total']['TOT_AMT'] 	+= $row['TOT_AMT'];
	$results['total']['TND_AMT'] 	+= $row['TND_AMT'];
}
/*
echo '<pre>';
print_r($results);
echo '</pre>';
*/
echo simple_crypt(json_encode($results));
?>