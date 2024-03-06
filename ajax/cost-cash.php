<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

/*
Don't forget to update TAX_F on CMP.COMPANY
*/

$Accounting		= $_GET['ACTG'];
$companyNumber  = $CoNbrDef;
$beginDate 		= $_GET['BEG_DT'];
$endDate 		= $_GET['END_DT'];
$days 			= $_GET['DAYS'];
$months 		= $_GET['MONTHS'];
$years 			= $_GET['YEARS'];
$day 			= $_GET['DAY'];
$month			= $_GET['MONTH'];
$year			= $_GET['YEAR'];
$consignment 	= $_GET['CNMT_F'];
$plusMode 		= $_GET['PLUS'];
//$invoiceNumber 	= $_GET['IVC_NBR'];
$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];

if (!empty($_GET['CO_NBR'])) {
	$companyNumber = $_GET['CO_NBR'];
}
	
if (($Accounting == 0) || ($Accounting == 2)){
	
$whereClauses = array("EXP.EXP_CO_NBR=" . $companyNumber." ", "DATE(EXP.CRT_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)");
$groupClauses = array();


	
if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$whereClauses[] = "DAY(EXP.CRT_TS)=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(EXP.CRT_TS)= ".$months;
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(EXP.CRT_TS)= ". $years;
	}

	if ($day != "") {
		$whereClauses[] = "DAY(EXP.CRT_TS)=" . $day;
	}

	if ($month != "") {
		$whereClauses[] = "MONTH(EXP.CRT_TS)=" . $month;
	}

	if ($year != "") {
		$whereClauses[] = "YEAR(EXP.CRT_TS)=" . $year;
	}
} else {
	if (!empty($beginDate)) {
		$whereClauses[] = "DATE(EXP.CRT_TS) >= '" . $beginDate . "'";
	}

	if (!empty($endDate)) {
		$whereClauses[] = "DATE(EXP.CRT_TS) <= '" . $endDate . "'";
	}
}


/*
if ($Accounting == 1) {
	$whereClauses[] = "HED.TAX_APL_ID IN ('I', 'A')	AND HED.BUY_CO_NBR IS NOT NULL";
}

if ($Accounting == 2) {
	$whereClauses[] = "((HED.TAX_APL_ID NOT IN ('I', 'A') AND COM.TAX_F = 1 AND HED.BUY_CO_NBR IS NOT NULL)
						OR (HED.TAX_APL_ID NOT IN ('I', 'A') AND HED.BUY_CO_NBR IS NULL))";
}

if ($Accounting == 3) {
	$whereClauses[] = "(HED.TAX_APL_ID NOT IN ('I', 'A') AND COM.TAX_F = 0 AND HED.BUY_CO_NBR IS NOT NULL)";
}
*/

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(EXP.CRT_TS)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(EXP.CRT_TS), MONTH(EXP.CRT_TS)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(EXP.CRT_TS), MONTH(EXP.CRT_TS), DAY(EXP.CRT_TS)";
				break;
			case "EXP_TYP":
				$groupClauses[] = "EXP.EXP_TYP";
				break;
			default:
				$groupClauses[] = "EXP.EXP_NBR";
				break;
		}
	}
		
	$groupClause = implode(", ", $groupClauses);
} else {
	$groupClause = "EXP.EXP_NBR";
}

$whereClauses = implode(" AND ", $whereClauses);
$groupClauses = implode(", ", $groupClauses);

$results = array(
	'parameter' => $_GET,
	'expense' => array(),
	'data' => array(),
	'pagination' => $pagination,
	'total' => array()
);


$queryExpense = "SELECT EXP_TYP, EXP_DESC FROM CMP.EXP_TYP ORDER BY EXP_TYP ASC";
$result = mysql_query($queryExpense);
			
while($row = mysql_fetch_array($result)) {
	$results['expense'][] = array(
		'EXP_TYP' => $row['EXP_TYP'],
		'EXP_DESC' => $row['EXP_DESC']
	);
}


// Handle human errors
$results['expense'][] = array(
	'EXP_TYP' => 'Unknown',
	'EXP_DESC' => 'Unknown'
);

if ($_GET['RL_TYP'] == 'RL_YEAR') {

$query = "SELECT MO.*,
		EXPS.EXP_MONTH,  
		COALESCE(SUM(EXPS.TOT_SUB), 0) AS TOT_SUB 
	FROM
	(
	SELECT 1 AS ACT_MO, 'Januari' AS ACT_MO_NAME UNION 
	SELECT 2 AS ACT_MO, 'Februari' AS ACT_MO_NAME UNION 
	SELECT 3 AS ACT_MO, 'Maret' AS ACT_MO_NAME UNION
	SELECT 4 AS ACT_MO, 'April' AS ACT_MO_NAME UNION
	SELECT 5 AS ACT_MO, 'Mei' AS ACT_MO_NAME UNION
	SELECT 6 AS ACT_MO, 'Juni' AS ACT_MO_NAME UNION
	SELECT 7 AS ACT_MO, 'Juli' AS ACT_MO_NAME UNION
	SELECT 8 AS ACT_MO, 'Agustus' AS ACT_MO_NAME UNION
	SELECT 9 AS ACT_MO, 'September' AS ACT_MO_NAME UNION
	SELECT 10 AS ACT_MO, 'Oktober' AS ACT_MO_NAME UNION
	SELECT 11 AS ACT_MO, 'November' AS ACT_MO_NAME UNION
	SELECT 12 AS ACT_MO, 'Desember' AS ACT_MO_NAME
	)
	MO
	LEFT JOIN 
	(SELECT EXP_NBR,
		EXP.EXP_TYP,
		DATE(CRT_TS) AS EXP_DTE,
		DAY(CRT_TS) AS EXP_DAY,
		MONTH(CRT_TS) AS EXP_MONTH,
		YEAR(CRT_TS) AS EXP_YEAR,
		MONTHNAME(CRT_TS) AS EXP_MONTHNAME,
		DATE(CRT_TS) AS DTE,
		PPL.NAME AS PPL_NAME,
		COM.NAME AS COM_NAME,
		EXP_DESC,
		TYP.CD_SUB_NBR_DEB,
		TYP.CD_SUB_NBR_CRT,
		SUM(TOT_SUB) AS TOT_SUB
			FROM CMP.EXPENSE EXP
			INNER JOIN CMP.EXP_TYP TYP ON EXP.EXP_TYP=TYP.EXP_TYP
			LEFT OUTER JOIN CMP.PEOPLE PPL ON EXP.PRSN_NBR=PPL.PRSN_NBR
			LEFT OUTER JOIN CMP.COMPANY COM ON EXP.CO_NBR=COM.CO_NBR
				WHERE ".$whereClauses."
					GROUP BY ".$groupClauses."
					ORDER BY EXP.EXP_NBR ASC
	) EXPS ON EXPS.EXP_MONTH = MO.ACT_MO
	GROUP BY MO.ACT_MO";
	
}
else {
	
	$query = "SELECT EXP_NBR,
		EXP.EXP_TYP,
		DATE(CRT_TS) AS EXP_DTE,
		DAY(CRT_TS) AS EXP_DAY,
		MONTH(CRT_TS) AS EXP_MONTH,
		YEAR(CRT_TS) AS EXP_YEAR,
		MONTHNAME(CRT_TS) AS EXP_MONTHNAME,
		DATE(CRT_TS) AS DTE,
		PPL.NAME AS PPL_NAME,
		COM.NAME AS COM_NAME,
		EXP_DESC,
		TYP.CD_SUB_NBR_DEB,
		TYP.CD_SUB_NBR_CRT,
		";


			foreach ($results['expense'] as $key => $expense) {
				if ($key == count($results['expense']) - 1) {
					// Don't generate unknown sub category automatically
					break;
				}
				
				$expenseType = $expense['EXP_TYP'];

				$query .= "
					SUM(CASE WHEN EXP.EXP_TYP = '" . $expenseType . "' THEN EXP.TOT_SUB ELSE 0 END) AS TOT_SUB_" . $expenseType . ",
				";
			}
		$query	.= "SUM(TOT_SUB) AS TOT_SUB
			FROM CMP.EXPENSE EXP
			INNER JOIN CMP.EXP_TYP TYP ON EXP.EXP_TYP=TYP.EXP_TYP
			LEFT OUTER JOIN CMP.PEOPLE PPL ON EXP.PRSN_NBR=PPL.PRSN_NBR
			LEFT OUTER JOIN CMP.COMPANY COM ON EXP.CO_NBR=COM.CO_NBR
				WHERE ".$whereClauses."
					GROUP BY ".$groupClauses."
					ORDER BY EXP.EXP_NBR ASC ";
}

//echo "<pre>".$query;

$pagination = pagination($query, 1000);

$result = mysql_query($pagination['query']);

while($row = mysql_fetch_array($result)) {

	$results['data'][] = $row;

	$results['total']['TOT_SUB'] 	+= $row['TOT_SUB'];	
	
	foreach ($results['expense'] as $key => $expense) {
		if ($key == count($results->expense) - 1) {
						// Don't generate unknown sub category automatically
						break;
			}
		$expenseType 		= $expense['EXP_TYP'];

		$results['total']['TOT_SUB_' . $expenseType] += $row['TOT_SUB_' . $expenseType];

	}
}
}

echo json_encode($results);