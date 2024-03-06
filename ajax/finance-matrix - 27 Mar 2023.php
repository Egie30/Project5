<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

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
WHERE PAY_ACT_F =1 
	AND MONTH(PAY_END_DTE) = ".date('m', strtotime($beginDate))." 
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
			SELECT COALESCE(SUM(PAY.TND_AMT), 0) AS TOT_SUB
			FROM CMP.PRN_DIG_ORD_PYMT PAY 
				LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED ON PAY.ORD_NBR=HED.ORD_NBR AND HED.DEL_NBR=0
			WHERE PAY.DEL_NBR = 0 
				AND DATE(PAY.CRT_TS) >= '" . $beginDate . "' AND DATE(PAY.CRT_TS) <= '" . $endDate . "'
				AND (HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY WHERE ACTG_TYP IS NULL) OR HED.BUY_CO_NBR IS NULL)
		) T";
} else {
	$queryIN = "SELECT SUM(TOT_SUB) AS TOT_OMSET FROM (
			SELECT COALESCE(SUM(PAY.TND_AMT), 0) AS TOT_SUB
			FROM CMP.PRN_DIG_ORD_PYMT PAY 
				LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED ON PAY.ORD_NBR=HED.ORD_NBR AND HED.DEL_NBR=0
			WHERE PAY.DEL_NBR = 0
				AND DATE(PAY.CRT_TS) >= '" . $beginDate . "' AND DATE(PAY.CRT_TS) <= '" . $endDate . "'
				AND (HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY WHERE ACTG_TYP IS NULL) OR HED.BUY_CO_NBR IS NULL)
		) T";
}
$resultIN = mysql_query($queryIN);
$rowIN	  = mysql_fetch_array($resultIN);

$queryNST = "SELECT GROUP_CONCAT(CO_NBR_CMPST) AS CO_NBR_CMPST FROM NST.PARAM_PAYROLL WHERE CO_NBR IN (1002,3110)";
$resultNST = mysql_query($queryNST);
$rowNST	  = mysql_fetch_array($resultNST);

$queryPAY = "SELECT 
	SUM(PAY_AMT) AS TOT_PAYROLL,
	SUM(BASE_TOT) AS BASE_PAYROLL ,
	SUM(ADD_TOT) AS ADD_PAYROLL ,
	SUM(OT_TOT) AS OT_PAYROLL 
FROM (
	SELECT 
		PRL.PRSN_NBR, 
		PRL.PAY_AMT,
		PRL.BASE_TOT,
		PRL.ADD_TOT,
		PRL.OT_TOT,
		PPL.CO_NBR 
	FROM PAY.PAYROLL PRL 
	LEFT JOIN CMP.PEOPLE PPL ON PRL.PRSN_NBR = PPL.PRSN_NBR
	WHERE PRL.PYMT_DTE >= '" . $beginDate . "' AND PRL.PYMT_DTE <= '" . $endDate . "' AND PPL.CO_NBR IN (".$rowNST['CO_NBR_CMPST'].")
) T";
$resultPAY = mysql_query($queryPAY);
$rowPAY	  = mysql_fetch_array($resultPAY);

$queryEXP = "SELECT SUM(TOT_SUB) AS TOT_EXP FROM CMP.EXPENSE WHERE DATE(CRT_TS) BETWEEN '".$beginDate."' AND '".$endDate."' AND DATE(CRT_TS) >= '" . $beginDate . "' AND DATE(CRT_TS) <= '" . $endDate . "'";
$resultEXP = mysql_query($queryEXP);
$rowEXP	  = mysql_fetch_array($resultEXP);

$query = "SELECT SUM(TOT_SUB) AS TOT, CAT_SUB_NBR, CAT_SUB_DESC, CAT_TYP_NBR, CAT_TYP FROM (
	SELECT DET.TOT_SUB, SUB.CAT_SUB_NBR, SUB.CAT_SUB_DESC, CAT.CAT_TYP_NBR, CAT.CAT_TYP
	FROM RTL.RTL_STK_DET DET 
		LEFT JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR = HED.ORD_NBR 
			AND HED.IVC_TYP='RC' AND HED.DEL_F=0
		LEFT JOIN RTL.CAT_SUB SUB ON HED.CAT_SUB_NBR=SUB.CAT_SUB_NBR
		LEFT JOIN RTL.CAT_TYP CAT ON SUB.CAT_TYP_NBR=CAT.CAT_TYP_NBR
	WHERE HED.ORD_DTE >= '" . $beginDate . "' AND HED.ORD_DTE <= '" . $endDate . "'
) T
GROUP BY ".$groupClause."
ORDER BY ".$groupClause." DESC
";
	

$pagination = pagination($query, 1000);

$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'pagination' => $pagination,
	'total' => array(),
	'query' => $queryIN
);
$result = mysql_query($pagination['query']);

while($row = mysql_fetch_array($result)) {
	$results['data'][] 		= $row;
	$results['total']['TOT'] 	+= $row['TOT'];
	$results['total']['TOT_OMSET'] 	= $rowIN['TOT_OMSET'];
	$results['total']['TOT_PAYROLL']= $rowPAY['TOT_PAYROLL'];
	$results['total']['BASE_PAYROLL']= $rowPAY['BASE_PAYROLL'];
	$results['total']['ADD_PAYROLL']= $rowPAY['ADD_PAYROLL'];
	$results['total']['OT_PAYROLL']= $rowPAY['OT_PAYROLL'];
	$results['total']['TOT_EXP']	= $rowEXP['TOT_EXP'];
	$results['total']['TOT_LABA'] 	= $rowIN['TOT_OMSET']-$rowPAY['TOT_PAYROLL']-$rowEXP['TOT_EXP']-$results['total']['TOT'];
}

echo json_encode($results);
