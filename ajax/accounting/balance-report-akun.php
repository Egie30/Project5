<?php

require_once __DIR__ . "/../../framework/database/connect.php";
require_once __DIR__ . "/../../framework/functions/default.php";

$bookNumber		= $_GET['BK_NBR'];
$plusMode		= $_GET['PLUS'];
$Actg			= $_GET['ACTG'];

$results = array(
	'parameter' => $_GET,
	'activa' => array(),
	'passiva' => array(),
	'type' => array(),
	'total' => array()
);


try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "profit-lost.php";

	$resultsProfit = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}


$tbWhere		= array("TB.DEL_NBR = 0", "CD.CD_CAT_NBR IN ('1', '2', '3', '7')", "TB.BK_NBR = ".$bookNumber." ");
$where 			= array("DET.DEL_NBR = 0", "HED.DEL_NBR = 0", "CD.CD_CAT_NBR IN ('1', '2', '3', '7')", "HED.BK_NBR = ".$bookNumber."");
$whereClause	= array("BK.BK_NBR = ".$bookNumber." ");

	
if($_GET['CD_CAT_NBR'] != "") {
	$tbWhere[] 		= "CD.CD_CAT_NBR = ".$_GET['CD_CAT_NBR']." ";
	$where[] 		= "CD.CD_CAT_NBR = ".$_GET['CD_CAT_NBR']." ";
}

if($Actg != 0)  {
	$tbWhere[]		= "TB.ACTG_TYP = ".$Actg." ";
	$where[]		= "HED.ACTG_TYP = ".$Actg." ";
	$detailWhere 	= "AND HED.ACTG_TYP = ".$Actg." ";
}

$where 			= implode(" AND ", $where);
$tbWhere 		= implode(" AND ", $tbWhere);
$whereClause 	= implode(" AND ", $whereClause);

$query	= "SELECT 
			SUB.CD_SUB_NBR,
			CD.CD_DESC,
			SUB.CD_SUB_DESC,
			SUB.CD_NBR,
			CD.CD_CAT_NBR,
			CONCAT(CD.CD_CAT_NBR, '-', CD.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
			(CASE WHEN (CD.CD_CAT_NBR = 1) THEN 'A'
				WHEN (CD.CD_CAT_NBR = 2) THEN 'B'
				WHEN (CD.CD_CAT_NBR = 3) THEN 'C'
				WHEN (CD.CD_CAT_NBR = '7') THEN 'E'
				WHEN (CD.CD_CAT_NBR NOT IN ('1', '2', '3')) THEN 'D'
				ELSE 0 END) AS CODE,
			REPORT.BALANCE AS RPT_BALANCE, 
			TB.BALANCE AS TB_BALANCE,
			(COALESCE(REPORT.BALANCE, 0) + COALESCE(TB.BALANCE, 0)) AS BALANCE,
			(COALESCE(PROFIT.REV, 0) - COALESCE(PROFIT.HPP, 0) - COALESCE(PROFIT.COST, 0)) AS PROFIT_LOSS
		FROM RTL.ACCTG_CD_SUB SUB
			LEFT OUTER JOIN RTL.ACCTG_CD CD
				ON SUB.CD_NBR = CD.CD_NBR
			LEFT OUTER JOIN 
				(SELECT TB_NBR,
					TB.BK_NBR,
					CD.ACC_NBR,
					CD.ACC_DESC,
					CD.CD_CAT_NBR,
					CD.CD_CAT_DESC,
					CD.CD_NBR,
					CD.CD_ACC_NBR,
					CD.CD_DESC,
					CD.CD_SUB_NBR,
					CD.CD_SUB_ACC_NBR,
					CD.CD_SUB_DESC,
					SUM(COALESCE(TB.DEB,0)) AS DEB,
					SUM(COALESCE(TB.CRT,0)) AS CRT,
					(CASE WHEN (CD.CD_CAT_NBR = 1) THEN (SUM(TB.DEB) - SUM(TB.CRT))
									WHEN (CD.CD_CAT_NBR = 2) THEN (SUM(TB.CRT) - SUM(TB.DEB))
									WHEN (CD.CD_CAT_NBR = 3) THEN (SUM(TB.CRT) - SUM(TB.DEB)) 
									WHEN (CD.CD_CAT_NBR = 7) THEN (SUM(TB.DEB) - SUM(TB.CRT)) 
									ELSE 0 END) AS BALANCE,
					TB.DEL_NBR,
					TB.UPD_NBR,
					TB.UPD_TS
				FROM RTL.ACCTG_TB TB
					INNER JOIN RTL.ACCTG_BK BK ON BK.BK_NBR=TB.BK_NBR
					LEFT JOIN (
						SELECT SUB.CD_SUB_NBR, SUB.CD_SUB_ACC_NBR, SUB.CD_SUB_DESC, ACC.CD_NBR, ACC.CD_ACC_NBR, ACC.CD_DESC, CAT.CD_CAT_NBR, CAT.CD_CAT_DESC,
							CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
							CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC
						FROM RTL.ACCTG_CD_SUB SUB
							INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
							INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
						GROUP BY SUB.CD_SUB_NBR
					) CD ON CD.CD_SUB_NBR=TB.CD_SUB_NBR
				WHERE ".$tbWhere." GROUP BY CD.CD_SUB_NBR
				) TB ON TB.CD_SUB_NBR = SUB.CD_SUB_NBR
			LEFT OUTER JOIN (
				SELECT 
					HED.BK_NBR,
					CD.CD_SUB_NBR,
					CD.CD_SUB_DESC,
					CD.CD_NBR,
					CD.CD_DESC,
					(CASE WHEN (CD.CD_CAT_NBR = 1) THEN (SUM(DET.DEB) - SUM(DET.CRT))
						WHEN (CD.CD_CAT_NBR = 2) THEN (SUM(DET.CRT) - SUM(DET.DEB))
						WHEN (CD.CD_CAT_NBR = 3) THEN (SUM(DET.CRT) - SUM(DET.DEB))
						WHEN (CD.CD_CAT_NBR = 7) THEN (SUM(DET.DEB) - SUM(DET.CRT))
						ELSE 0 END) AS BALANCE,
					HED.TAX_F
				FROM RTL.ACCTG_GL_DET DET
					LEFT OUTER JOIN RTL.ACCTG_GL_HEAD HED
						ON HED.GL_NBR = DET.GL_NBR
					INNER JOIN (
						SELECT SUB.CD_SUB_NBR, SUB.CD_SUB_ACC_NBR, SUB.CD_SUB_DESC, ACC.CD_NBR, ACC.CD_ACC_NBR, ACC.CD_DESC, CAT.CD_CAT_NBR, CAT.CD_CAT_DESC,
							CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
							CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC
						FROM RTL.ACCTG_CD_SUB SUB
							INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
							INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
						GROUP BY SUB.CD_SUB_NBR
					) CD ON CD.CD_SUB_NBR=DET.CD_SUB_NBR
					WHERE ".$where."
					GROUP BY DET.CD_SUB_NBR
			) REPORT ON REPORT.CD_SUB_NBR = SUB.CD_SUB_NBR
			LEFT OUTER JOIN RTL.ACCTG_BK BK
				ON (REPORT.BK_NBR = BK.BK_NBR OR TB.BK_NBR = BK.BK_NBR)
			LEFT OUTER JOIN (
				SELECT 
					HED.BK_NBR,
					CD.CD_SUB_NBR,
					CD.CD_SUB_DESC,
					CD.CD_NBR,
					CD.CD_DESC,
					SUM(CASE WHEN (CD.CD_CAT_NBR = 4 AND CD.CD_ACC_NBR = 1) THEN (DET.CRT - DET.DEB) ELSE 0 END) AS REV,
					SUM(CASE WHEN (CD.CD_CAT_NBR = 5 AND CD.CD_ACC_NBR = 1) THEN (DET.DEB - DET.CRT) ELSE 0 END) AS HPP,
					SUM(CASE WHEN (CD.CD_CAT_NBR = 6) THEN (DET.DEB - DET.CRT) ELSE 0 END) AS COST
				FROM RTL.ACCTG_GL_DET DET
					LEFT OUTER JOIN RTL.ACCTG_GL_HEAD HED
						ON HED.GL_NBR = DET.GL_NBR
					INNER JOIN (
						SELECT SUB.CD_SUB_NBR, SUB.CD_SUB_ACC_NBR, SUB.CD_SUB_DESC, ACC.CD_NBR, ACC.CD_ACC_NBR, ACC.CD_DESC, CAT.CD_CAT_NBR, CAT.CD_CAT_DESC,
							CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
							CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC
						FROM RTL.ACCTG_CD_SUB SUB
							INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
							INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
						GROUP BY SUB.CD_SUB_NBR
					) CD ON CD.CD_SUB_NBR=DET.CD_SUB_NBR
					WHERE (
							(CD.CD_CAT_NBR = 4 AND CD.CD_ACC_NBR = 1)
							OR (CD.CD_CAT_NBR = 5 AND CD.CD_ACC_NBR = 1)
							OR (CD.CD_CAT_NBR = 6)
						)
						AND DET.DEL_NBR = 0
						AND HED.DEL_NBR = 0
						AND HED.BK_NBR = ".$bookNumber."
						".$detailWhere."
					GROUP BY HED.BK_NBR
			) PROFIT ON PROFIT.BK_NBR = BK.BK_NBR
			WHERE ".$whereClause."
			GROUP BY SUB.CD_SUB_NBR
			";

//echo "<pre>".$query;
			
$result	= mysql_query($query);
while($row = mysql_fetch_array($result)) {
	
	$results['total']['PROFIT_LOSS']		= $row['PROFIT_LOSS'];
	
	if($row['CODE'] == 'A') {
		$results['activa'][$row['CD_DESC']][] = $row;
		$results['total']['ACTIVA'] += $row['BALANCE'];
	}
	else if (($row['CODE'] == 'B') || ($row['CODE'] == 'C')) {
		$results['passiva'][$row['CD_DESC']][] = $row;
		$results['total']['PASSIVA'] += $row['BALANCE'];
	}
	else if ($row['CODE'] == 'E') {
		$results['total']['PRIVE'] += $row['BALANCE'];
	}
	
}


//$results['total']['PROFIT_LOSS'] 	= $resultsProfit->data->PROFIT_LOSS;
$results['total']['PASSIVA_NETT'] 	= $results['total']['PASSIVA'] + $results['total']['PROFIT_LOSS'];


echo json_encode($results);


?>