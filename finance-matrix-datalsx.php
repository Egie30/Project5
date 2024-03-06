<?php 
include "framework/functions/default.php";
include "framework/database/connect.php";
include "framework/functions/crypt.php";

if (empty($_GET['DTE'])) {
	$_GET['BEG_DT'] = date('Y-m-01');
} else {
	$_GET['BEG_DT'] = $_GET['DTE'];
}

$beginDate 	= $_GET['BEG_DT'];
$endDate 	= date('Y-m-t', strtotime($_GET['BEG_DT']));

$query_date      = "SELECT 
	PAY_CONFIG_NBR, PAY_BEG_DTE, PAY_END_DTE 
FROM PAY.PAY_CONFIG_DTE
WHERE MONTH(PAY_END_DTE) = ".date('m', strtotime($beginDate))." 
	AND YEAR(PAY_END_DTE)= ".date('Y', strtotime($beginDate));
//echo $query_date;
$result_date	= mysql_query($query_date);
$row_date		= mysql_fetch_array($result_date);	
$beginDate		= $row_date['PAY_BEG_DTE'];
$endDate		= $row_date['PAY_END_DTE'];

$groups = (array) $_GET['GROUP'];

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "CAT_TYP_NBR":
				$groupClauses[] = "CAT_TYP_NBR";
				break;
			case "CAT_SUB_NBR":
				$groupClauses[] = "CAT_SUB_NBR";
				break;
			default:
				$groupClauses[] = "CAT_TYP_NBR";
				break;
		}
	}
		
	$groupClause = implode(", ", $groupClauses);
} else {
	$groupClause = "CAT_TYP_NBR";
}

$query = "SELECT SUM(TOT_SUB) AS TOT, CAT_SUB_NBR, CAT_SUB_DESC, CAT_TYP_NBR, CAT_TYP FROM (
	SELECT DET.TOT_SUB, SUB.CAT_SUB_NBR, SUB.CAT_SUB_DESC, CAT.CAT_TYP_NBR, CAT.CAT_TYP
	FROM RTL.RTL_STK_DET DET 
		LEFT JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR = HED.ORD_NBR 
			AND HED.IVC_TYP='RC' AND HED.DEL_F=0
		LEFT JOIN RTL.CAT_SUB SUB ON HED.CAT_SUB_NBR=SUB.CAT_SUB_NBR
		LEFT JOIN RTL.CAT_TYP CAT ON SUB.CAT_TYP_NBR=CAT.CAT_TYP_NBR
	WHERE HED.ORD_DTE BETWEEN '".$beginDate."' AND '".$endDate."'
) T
GROUP BY ".$groupClause."
ORDER BY ".$groupClause." DESC
";
	

$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'pagination' => $pagination,
	'total' => array(),
	'query' => $query
);
$result = mysql_query($query);

while($row = mysql_fetch_array($result)) {
	$results['data'][] 				= $row;
	$results['total']['TOT'] 		+= $row['TOT'];
}

/*
echo '<pre>';
print_r($results);
echo '</pre>';
*/

echo json_encode($results);
?>