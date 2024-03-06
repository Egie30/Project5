<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

/*
Don't forget to update TAX_F on ".$cmp.".COMPANY
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

	
$whereClauses = array("UTL.UTL_CO_NBR=" . $companyNumber." ","DATE(UTL.UTL_DTE) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)");
$groupClauses = array();

if (($Accounting == 0) || ($Accounting == 2)){
	
if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$whereClauses[] = "DAY(UTL.UTL_DTE)=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(UTL.UTL_DTE)= ".$months;
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(UTL.UTL_DTE)= ". $years;
	}

	if ($day != "") {
		$whereClauses[] = "DAY(UTL.UTL_DTE)=" . $day;
	}

	if ($month != "") {
		$whereClauses[] = "MONTH(UTL.UTL_DTE)=" . $month;
	}

	if ($year != "") {
		$whereClauses[] = "YEAR(UTL.UTL_DTE)=" . $year;
	}
} else {
	if (!empty($beginDate)) {
		$whereClauses[] = "DATE(UTL.UTL_DTE) >= '" . $beginDate . "'";
	}

	if (!empty($endDate)) {
		$whereClauses[] = "DATE(UTL.UTL_DTE) <= '" . $endDate . "'";
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
				$groupClauses[] = "YEAR(UTL.UTL_DTE)";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(UTL.UTL_DTE), MONTH(UTL.UTL_DTE)";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(UTL.UTL_DTE), MONTH(UTL.UTL_DTE), DAY(UTL.UTL_DTE)";
				break;
			default:
				$groupClauses[] = "UTL.UTL_NBR";
				break;
		}
	}
		
	$groupClause = implode(", ", $groupClauses);
} else {
	$groupClause = "UTL.UTL_NBR";
}

$whereClauses = implode(" AND ", $whereClauses);
$groupClauses = implode(", ", $groupClauses);

$results = array(
	'parameter' => $_GET,
	'utility' => array(),
	'data' => array(),
	'pagination' => $pagination,
	'total' => array()
);

$queryCatSub = "SELECT UTL_TYP, UTL_DESC FROM ".$cmp.".UTL_TYP WHERE UTL_TYP NOT IN ('?INTERNET') ORDER BY UTL_TYP ASC";
$result = mysql_query($queryCatSub);
			
while($row = mysql_fetch_array($result)) {
	$results['utility'][] = array(
		'UTL_TYP' => $row['UTL_TYP'],
		'UTL_DESC' => $row['UTL_DESC']
	);
}

// Handle human errors
$results['utility'][] = array(
	'UTL_TYP' => 'Unknown',
	'UTL_DESC' => 'Unknown'
);

if ($_GET['RL_TYP'] == 'RL_YEAR') {
	
$query = "SELECT MO.*,
		UTL.UTL_MONTH,  
		COALESCE(SUM(UTL.TOT_SUB), 0) AS TOT_SUB 
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
	(
	SELECT UTL_NBR,
				DATE(UTL_DTE) AS UTL_DTE,
				DAY(UTL_DTE) AS UTL_DAY,
				MONTH(UTL_DTE) AS UTL_MONTH,
				YEAR(UTL_DTE) AS UTL_YEAR,
				MONTHNAME(UTL_DTE) AS UTL_MONTHNAME,
				PPL.NAME AS PPL_NAME,
				COM.NAME AS COM_NAME,
				UTL_DESC,
				CD_SUB_NBR_DEB,
				CD_SUB_NBR_CRT,
				SUM(TOT_SUB) AS TOT_SUB
					  FROM CMP.UTILITY UTL INNER JOIN
					       CMP.UTL_TYP TYP ON UTL.UTL_TYP=TYP.UTL_TYP LEFT OUTER JOIN
					       CMP.PEOPLE PPL ON UTL.PRSN_NBR=PPL.PRSN_NBR LEFT OUTER JOIN
					       CMP.COMPANY COM ON UTL.CO_NBR=COM.CO_NBR
					 WHERE ".$whereClauses." 
					 GROUP BY ".$groupClauses."
					ORDER BY DATE(UTL.UTL_DTE) ASC
	) UTL ON UTL.UTL_MONTH = MO.ACT_MO
	GROUP BY MO.ACT_MO";
}
else {

$query = "SELECT UTL_NBR,
				DATE(UTL_DTE) AS UTL_DTE,
				DAY(UTL_DTE) AS UTL_DAY,
				MONTH(UTL_DTE) AS UTL_MONTH,
				YEAR(UTL_DTE) AS UTL_YEAR,
				MONTHNAME(UTL_DTE) AS UTL_MONTHNAME,
				PPL.NAME AS PPL_NAME,
				COM.NAME AS COM_NAME,
				UTL_DESC,
				CD_SUB_NBR_DEB,
				CD_SUB_NBR_CRT,
				";
			
			foreach ($results['utility'] as $key => $utility) {
				if ($key == count($results['utility']) - 1) {
					// Don't generate unknown sub category automatically
					break;
				}
				
				$utilityType = $utility['UTL_TYP'];

				$query .= "
					SUM(CASE WHEN UTL.UTL_TYP = '" . $utilityType . "' THEN UTL.TOT_SUB ELSE 0 END) AS TOT_SUB_" . $utilityType . ",
				";
			}
				
				// Be sure the id is absolute
			$query	.= "SUM(TOT_SUB) AS TOT_SUB
					  FROM CMP.UTILITY UTL INNER JOIN
					       CMP.UTL_TYP TYP ON UTL.UTL_TYP=TYP.UTL_TYP LEFT OUTER JOIN
					       CMP.PEOPLE PPL ON UTL.PRSN_NBR=PPL.PRSN_NBR LEFT OUTER JOIN
					       CMP.COMPANY COM ON UTL.CO_NBR=COM.CO_NBR
					 WHERE ".$whereClauses."
					 GROUP BY ".$groupClauses."
					ORDER BY DATE(UTL.UTL_DTE) ASC ";
}
			
//echo "<pre>".$query;

$pagination = pagination($query, 1000);


$result = mysql_query($pagination['query']);

while($row = mysql_fetch_array($result)) {

	$results['data'][] = $row;
	$results['total']['TOT_SUB'] 	+= $row['TOT_SUB'];	
	
	foreach ($results['utility'] as $key => $utility) {
		if ($key == count($results->utility) - 1) {
						// Don't generate unknown sub category automatically
						break;
			}
		$utilityType 		= $utility['UTL_TYP'];

		$results['total']['TOT_SUB_' . $utilityType] += $row['TOT_SUB_' . $utilityType];

	}
}

}

echo json_encode($results);