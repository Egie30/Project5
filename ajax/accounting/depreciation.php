<?php

require_once __DIR__ . "/../../framework/database/connect.php";
require_once __DIR__ . "/../../framework/functions/default.php";
require_once __DIR__ . "/../../framework/pagination/pagination.php";

$bookNumber		= $_GET['BK_NBR'];
$plusMode		= $_GET['PLUS'];
$code			= $_GET['CODE'];

$searchQuery    = strtoupper($_REQUEST['s']);

$whereClauses   = array("DEPN.DEL_NBR = 0");


if ($searchQuery != "") {
	$searchQuery = explode(" ", $searchQuery);

	foreach ($searchQuery as $query) {
		$query = trim($query);

		if (empty($query)) {
			continue;
		}

		if (strrpos($query, '%') === false) {
			$query = '%' . $query . '%';
		}
		$whereClauses[] = "(
			TB.CD_SUB_NBR LIKE '" . $query . "'
			OR CD.CD_DESC LIKE '" . $query . "'
			OR CD.CD_SUB_DESC LIKE '" . $query . "'
			OR TB.TB_NBR LIKE '" . $query . "'
			OR GL.GL_NBR LIKE '" . $query . "'
			OR DEPN.DEPN_NBR LIKE '" . $query . "'
		)";
	}
}



if ($bookNumber != '') {
	$whereClauses[] = "DEPN.BK_NBR = ".$bookNumber;
}


if ($plusMode == 1) {
	if ((int) getDbParam("PKP_TAX_F") == 1) {
		$whereClauses[] = "(TB.TAX_F=1 OR GL.TAX_F = 1)";
	}
}
if ($plusMode == 2) {
	if ((int) getDbParam("PKP_TAX_F") == 1) {
		$whereClauses[] = "(TB.TAX_F=0 OR GL.TAX_F = 0)";
	}
}

if($code != '') {
if($code == 'TB') {
	$whereClauses[]	= "TB.CODE='TB' ";
}
else if($code == 'GL') {
	$whereClauses[]	= "GL.CODE='GL' ";
} 
}


$whereClauses = implode(" AND ", $whereClauses);


$query = "SELECT DEPN.DEPN_NBR,
		DEPN.PUR_DT,
		DEPN.FA_VAL,
		DEPN.DEPN_VAL,
		DEPN.DEPN_TOT,
		DM.DEPN_CAT_NBR,
		DM.DEPN_CAT_DESC,
		DM.DEPN_GRP_DESC,
		DM.EPL,
		DM.DEPN_STR,
		DM.DEPN_DOWN,
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
		DEPN.DEL_NBR,
		DEPN.UPD_NBR,
		DEPN.UPD_TS,
		DEPN.CNT_DT,
		COALESCE(TB.TB_NBR, GL.GL_NBR) AS CODE_NBR,
		COALESCE(TB.CODE, GL.CODE) AS CODE
	FROM RTL.ACCTG_DEPN DEPN
	LEFT OUTER JOIN RTL.ACCTG_DEPN_METH DM
			ON DEPN.DEPN_CAT_NBR = DM.DEPN_CAT_NBR
		LEFT OUTER JOIN 
		(	SELECT DET.CD_SUB_NBR, 
				HED.GL_NBR, 
				HED.DEPN_NBR,
				'GL' AS CODE,
				HED.TAX_F
			FROM RTL.ACCTG_GL_DET DET
				LEFT OUTER JOIN RTL.ACCTG_GL_HEAD HED
					ON HED.GL_NBR = DET.GL_NBR
				LEFT OUTER JOIN RTL.ACCTG_CD_SUB SUB
					ON DET.CD_SUB_NBR = SUB.CD_SUB_NBR
				LEFT OUTER JOIN RTL.ACCTG_CD CD
					ON SUB.CD_NBR = CD.CD_NBR
			WHERE CD.CD_NBR IN ('2')
				AND	HED.DEL_NBR = 0
				AND DET.DEL_NBR = 0
				AND HED.DEPN_NBR != 0
		) GL ON GL.DEPN_NBR = DEPN.DEPN_NBR
		LEFT OUTER JOIN
		( SELECT TB.TB_NBR, 
				TB.CD_SUB_NBR, 
				TB.DEPN_NBR,
				'TB' AS CODE,
				TB.DEL_NBR,
				TB.TAX_F
			FROM RTL.ACCTG_TB TB
				LEFT OUTER JOIN RTL.ACCTG_CD_SUB SUB
					ON TB.CD_SUB_NBR = SUB.CD_SUB_NBR
				LEFT OUTER JOIN RTL.ACCTG_CD CD
					ON SUB.CD_NBR = CD.CD_NBR
			WHERE CD.CD_NBR IN ('2')
				AND TB.DEL_NBR = 0
				AND TB.DEPN_NBR != 0
		) TB ON TB.DEPN_NBR=DEPN.DEPN_NBR
		LEFT OUTER JOIN (
			SELECT SUB.CD_SUB_NBR, SUB.CD_SUB_ACC_NBR, SUB.CD_SUB_DESC, ACC.CD_NBR, ACC.CD_ACC_NBR, ACC.CD_DESC, CAT.CD_CAT_NBR, CAT.CD_CAT_DESC,
				CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
				CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC
			FROM RTL.ACCTG_CD_SUB SUB
				INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
				INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
		) CD ON (CD.CD_SUB_NBR = GL.CD_SUB_NBR OR CD.CD_SUB_NBR = TB.CD_SUB_NBR)
	WHERE  ".$whereClauses." GROUP BY DEPN.DEPN_NBR ORDER BY 1 ASC";

	
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
	$results['data'][] = $row;

}

echo json_encode($results);
?>