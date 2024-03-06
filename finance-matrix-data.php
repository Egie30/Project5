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

//OMSET
if($beginDate==date('Y-m-01')){
	$queryIN = "SELECT SUM(TOT_SUB) AS TOT_OMSET FROM (
			SELECT DET.TOT_SUB, HED.ORD_STT_ID 
			FROM CMP.PRN_DIG_ORD_DET DET 
				LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR
					AND HED.DEL_NBR=0 AND DET.DEL_NBR=0 AND HED.ORD_STT_ID IN (SELECT ORD_STT_ID FROM CMP.PRN_DIG_STT WHERE ORD_STT_ORD > 7)
			WHERE DATE(ORD_TS) BETWEEN '".$beginDate."' AND '".$endDate."'
			UNION ALL 
			SELECT DET.TOT_SUB, HED.ORD_STT_ID 
			FROM CMP.PRN_DIG_ORD_DET DET 
				LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR
				AND HED.DEL_NBR=0 AND DET.DEL_NBR=0 AND HED.ORD_STT_ID IN (SELECT ORD_STT_ID FROM CMP.PRN_DIG_STT WHERE ORD_STT_ORD > 7) 
			WHERE DATE(ORD_TS) = CURRENT_DATE
		) T";
} else {
	$queryIN = "SELECT SUM(TOT_SUB) AS TOT_OMSET FROM (
			SELECT DET.TOT_SUB, HED.ORD_STT_ID 
			FROM CMP.PRN_DIG_ORD_DET DET 
				LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR
					AND HED.DEL_NBR=0 AND DET.DEL_NBR=0 AND HED.ORD_STT_ID IN (SELECT ORD_STT_ID FROM CMP.PRN_DIG_STT WHERE ORD_STT_ORD > 7)
			WHERE DATE(ORD_TS) BETWEEN '".$beginDate."' AND '".$endDate."'
		) T";
}
//echo $queryIN;
$resultIN = mysql_query($queryIN);
$rowIN	  = mysql_fetch_array($resultIN);

$queryNST = "SELECT GROUP_CONCAT(CO_NBR_CMPST) AS CO_NBR_CMPST FROM NST.PARAM_PAYROLL WHERE CO_NBR IN (271)";
$resultNST = mysql_query($queryNST);
$rowNST	  = mysql_fetch_array($resultNST);

$queryPAY = "SELECT SUM(PAY_AMT) AS TOT_PAYROLL FROM (
		SELECT PRL.PRSN_NBR, PRL.PAY_AMT, PPL.CO_NBR 
		FROM PAY.PAYROLL PRL 
		LEFT JOIN CMP.PEOPLE PPL ON PRL.PRSN_NBR = PPL.PRSN_NBR
		WHERE PRL.PYMT_DTE BETWEEN '".$beginDate."' AND '".$endDate."'
			AND PPL.CO_NBR IN (".$rowNST['CO_NBR_CMPST'].")
		) T";
$resultPAY = mysql_query($queryPAY);
$rowPAY	  = mysql_fetch_array($resultPAY);

$queryEXP = "SELECT SUM(TOT_SUB) AS TOT_EXP FROM CMP.EXPENSE WHERE DATE(CRT_TS) BETWEEN '".$beginDate."' AND '".$endDate."'";
$resultEXP = mysql_query($queryEXP);
$rowEXP	  = mysql_fetch_array($resultEXP);

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
	'query' => $queryIN
);
$result = mysql_query($query);

while($row = mysql_fetch_array($result)) {
	$results['data'][] 		= $row;
	$results['total']['TOT'] 	+= $row['TOT'];
	$results['total']['TOT_OMSET'] 	= $rowIN['TOT_OMSET'];
	$results['total']['TOT_PAYROLL']= $rowPAY['TOT_PAYROLL'];
	$results['total']['TOT_EXP']	= $rowEXP['TOT_EXP'];
	$results['total']['TOT_LABA'] 	= $rowIN['TOT_OMSET']-$rowPAY['TOT_PAYROLL']-$rowEXP['TOT_EXP']-$results['total']['TOT'];
}

//echo '<pre>';
//print_r($results);
//echo '</pre>';

echo simple_crypt(json_encode($results));
?>