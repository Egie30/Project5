<?php
require_once "/../../framework/database/connect.php";
require_once "/../../framework/functions/default.php";

$bookNumber		= $_GET['BK_NBR'];
$Accounting		= $_GET['ACTG'];

$resultsProfit = array(
	'parameter' => $_GET,
	'cost' => array(),
	'exp' => array(),
	'data' => array(),
	'total' => array()
);


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



try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../cost-cash.php";

	$resultsCash = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "hpp.php";

	$resultsCOGS = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

$resultsProfit['data']['HPP']				= $resultsCOGS->data->HPP;

$whereClauses = array("DET.DEL_NBR = 0", "HED.DEL_NBR = 0");

$whereClauses[] = "CD.CD_SUB_NBR NOT IN (1,3)";
$whereClauses[] = "HED.BK_NBR = ".$bookNumber." ";

if($Accounting != 0) {
	$whereClauses[] = "HED.ACTG_TYP = ".$Accounting." ";
}

$whereClauses = implode(" AND ", $whereClauses);

	$GLTypePPN	= 21; //PPN keluaran

	$query_ppn	= "SELECT CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypePPN.""; //PPN Masukan
	$result_ppn	= mysql_query($query_ppn);
	$row_ppn	= mysql_fetch_array($result_ppn);

	$CdSubPPN	= $row_ppn['CD_SUB_NBR'];
	
$query		= "SELECT
	DET.GL_NBR,
	TYP.GL_TYP,
	TYP.GL_DESC,
	CD.CD_SUB_NBR,
	CD.CD_SUB_DESC,
	SUM(CASE WHEN TYP.GL_TYP = 'REV-PRN-DIG' THEN DET.CRT ELSE 0 END) AS REVENUE,
	SUM(CASE WHEN TYP.GL_TYP = 'PAY-RL' THEN DET.DEB ELSE 0 END) AS PAY_RL,
	SUM(CASE WHEN TYP.GL_TYP = 'COST' THEN DET.DEB ELSE 0 END) AS COST,
	SUM(CASE WHEN TYP.GL_TYP = 'EXP' THEN DET.DEB ELSE 0 END) AS EXP,
	SUM(CASE WHEN CD.CD_SUB_NBR = ".$CdSubPPN." THEN COALESCE(DET.CRT,0) ELSE 0 END) AS PPN_OUT,
	SUM(DET.DEB) AS DEB,
	SUM(DET.CRT) AS CRT
FROM RTL.ACCTG_GL_DET DET 
LEFT JOIN RTL.ACCTG_GL_HEAD HED
	ON HED.GL_NBR = DET.GL_NBR
LEFT JOIN RTL.ACCTG_GL_TYP TYP 
	ON HED.GL_TYP_NBR = TYP.GL_TYP_NBR
INNER JOIN (
						SELECT SUB.CD_SUB_NBR, SUB.CD_SUB_ACC_NBR, SUB.CD_SUB_DESC, ACC.CD_NBR, ACC.CD_ACC_NBR, ACC.CD_DESC, CAT.CD_CAT_NBR, CAT.CD_CAT_DESC,
							CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
							CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC
						FROM RTL.ACCTG_CD_SUB SUB
							INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
							INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
						GROUP BY SUB.CD_SUB_NBR
					) CD ON CD.CD_SUB_NBR=DET.CD_SUB_NBR
WHERE ".$whereClauses."
GROUP BY DET.CD_SUB_NBR
";

//echo "<pre>".$query;

$result	= mysql_query($query);
while($row = mysql_fetch_array($result)) {

	$resultsProfit[]	= $row;
	
	$results['data']['REVENUE'] 	+= $row['REVENUE'];
	$results['data']['PAY_RL'] 		+= $row['PAY_RL'];
	$results['data']['COST'] 		+= $row['COST'];
	$results['data']['EXP'] 		+= $row['EXP'];
	$results['data']['PPN_OUT'] 	+= $row['PPN_OUT'];
	
	if($row['COST'] != 0) {
		$results['cost'][] = array(
			'CD_SUB_DESC' => $row['CD_SUB_DESC'],
			'COST' => $row['COST']
		);
	}
	
	if($row['EXP'] != 0) {
		$results['exp'][] = array(
			'CD_SUB_DESC' => $row['CD_SUB_DESC'],
			'EXP' => $row['EXP']
		);
	}
	
}



//echo "<pre>"; print_r($results['data']['COST']);

$results['data']['REVENUE']			= $results['data']['REVENUE'] - $results['data']['PPN_OUT'];

$results['data']['HPP']				= $resultsCOGS->data->HPP;
$results['data']['GROSS_PROFIT']	= $results['data']['REVENUE'] - $results['data']['HPP'];
$results['data']['BTKTL']			= $results['data']['PAY_RL'];

$results['data']['ROUTINE'] 		= $resultsRoutine;
$results['data']['UTILITY'] 		= $resultsRoutine->utility;

$results['data']['CASH'] 			= $resultsCash;
$results['data']['EXPENSE'] 		= $resultsCash->expense;
$results['data']['TOT_CASH'] 		= $resultsCash->total->TOT_SUB;

$results['data']['TOTAL_COST'] 		= $results['data']['BTKTL'] +  (0.1 * $resultsRoutine->total->TOT_SUB) + $results['data']['TOT_CASH'] + $results['data']['COST'] + $results['data']['EXP'];

$results['data']['PROFIT_LOSS']		= $results['data']['GROSS_PROFIT'] - $results['data']['TOTAL_COST'];


echo json_encode($results);
