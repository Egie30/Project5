<?php
require_once __DIR__ . "/../../framework/database/connect.php";
require_once __DIR__ . "/../../framework/functions/default.php";
require_once __DIR__ . "/../../framework/pagination/pagination.php";

$bookNumber		= $_GET['BK_NBR'];
$Accounting		= $_GET['ACTG'];


$query_bk	= "SELECT BK_NBR, 
				(BEG_DTE - INTERVAL 1 DAY) AS BEGIN, 
				BEG_DTE AS BEG_DT,
				END_DTE AS END_DT,
				MONTH(BEG_DTE) AS BK_MONTH,
				YEAR(BEG_DTE) AS BK_YEAR,
				MONTH(BEG_DTE - INTERVAL 1 MONTH) AS BK_MONTH_BEG,
				YEAR(BEG_DTE - INTERVAL 1 MONTH) AS BK_YEAR_BEG
			FROM RTL.ACCTG_BK WHERE BK_NBR = ".$bookNumber." ";
$result_bk 	= mysql_query($query_bk);
$row_bk		= mysql_fetch_array($result_bk);

$_GET['GROUP']		= 'MONTH';
$_GET['MONTHS']		= $row_bk['BK_MONTH'];
$_GET['YEARS']		= $row_bk['BK_YEAR'];



$whereClauses = array("DET.DEL_NBR = 0", "HED.DEL_NBR = 0", "CAT.CD_CAT_NBR = 1", "ACC.CD_NBR = 1");
$groupClauses = array();

$whereClauses[] = "HED.BK_NBR =" . $bookNumber;


if ($Accounting != 0) {
	$whereClauses[] = "HED.ACTG_TYP = ".$Accounting." ";
}

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(HED.GL_DTE)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(HED.GL_DTE), MONTH(HED.GL_DTE)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(HED.GL_DTE), MONTH(HED.GL_DTE), DAY(HED.GL_DTE)";
				break;
			default:
				$groupClauses[] = "DET.GL_DET_NBR";
				break;
		}
	}
		
	$groupClauses = implode(", ", $groupClauses);
} else {
	$groupClauses = "DET.GL_DET_NBR";
}

$whereClauses = implode(" AND ", $whereClauses);


$query = "SELECT 
		DET.GL_DET_NBR,
		DET.GL_NBR,
		HED.GL_DTE,
		HED.REF AS GL_REF,
		HED.GL_DESC,
		CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
		COALESCE(DET.DEB,0) AS DEB,
		COALESCE(DET.CRT,0) AS CRT
	FROM RTL.ACCTG_GL_DET DET
	LEFT JOIN RTL.ACCTG_GL_HEAD HED
		ON DET.GL_NBR = HED.GL_NBR
	LEFT JOIN RTL.ACCTG_CD_SUB SUB
		ON DET.CD_SUB_NBR = SUB.CD_SUB_NBR
	LEFT OUTER JOIN RTL.ACCTG_CD ACC 
		ON ACC.CD_NBR=SUB.CD_NBR
	LEFT OUTER JOIN RTL.ACCTG_CD_CAT CAT 
		ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
	WHERE ".$whereClauses."
	GROUP BY ".$groupClauses."
	ORDER BY HED.GL_DTE, HED.GL_NBR";

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

	$results['total']['DEB'] 	+= $row['DEB'];
	$results['total']['CRT'] 	+= $row['CRT'];
	
}

echo json_encode($results);
