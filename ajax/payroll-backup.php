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


//============================ GAJI KARYAWAN ==============================

//Proreliance ==> masuk ke PT (Laba Rugi Champion Printing) ==> CO_NBR Proreliance = 997
//The Common Grounds ==> masuk ke PT (Laba Rugi Champion Campus) ==> CO_NBR The Common Grounds = 1099

if (!empty($_GET['CO_NBR'])) {
	$companyNumber = $_GET['CO_NBR'];
}

$whereClauses	= array("PAY.DEL_NBR = 0", "PPL.DEL_NBR = 0", "DATE(PAY.PYMT_DTE) >= (SELECT BEG_RPT FROM NST.PARAM_LOC)");
$groupClauses 	= array();


$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'pagination' => $pagination,
	'total' => array()
);



if (($Accounting == 0) || ($Accounting == 1) || ($Accounting == 2)) {
	
	$Proreliance 	= 997;
	$CommonGrounds 	= 1099;
	
	if (isset($months)) {
		$whereClauses[] = "MONTH(PAY.PYMT_DTE) = '" . $months . "'";
	}

	if (isset($years)) {
		$whereClauses[] = "YEAR(PAY.PYMT_DTE) = '" . $years . "'";
	}
	
	if ($Accounting == 0) {
		if($CoNbrDef == 271) {
			$whereClauses[] = "(PPL.CO_NBR = ".$Proreliance." OR PPL.CO_NBR = ".$CoNbrDef.") ";
		}
		else if($CoNbrDef == 1002) {
			$whereClauses[] = "(PPL.CO_NBR = ".$CommonGrounds." OR PPL.CO_NBR = ".$CoNbrDef.") ";
		}
	}
	
	if ($Accounting == 1) {
		if($CoNbrDef == 271) {
			$whereClauses[] = "PPL.CO_NBR = ".$Proreliance." ";
		} 
		else {
			$whereClauses[] = "PPL.CO_NBR = ".$CommonGrounds." ";
		}
		
	}

	if ($Accounting == 2) {
		$whereClauses[] = "PPL.CO_NBR = ".$CoNbrDef." ";
	}
	
	if (count($groups) > 0) {
		$groupClauses = array();
		
		while(count($groups) > 0) {
			$group = strtoupper(array_shift($groups));
			
			switch ($group) {
				case "YEAR":
					$groupClauses[] = "YEAR(PAY.PYMT_DTE)";
					break;
				case "MONTH":
					$groupClauses[] = "YEAR(PAY.PYMT_DTE), MONTH(PAY.PYMT_DTE)";
					break;
				case "DAY":
					$groupClauses[] = "YEAR(PAY.PYMT_DTE), MONTH(PAY.PYMT_DTE), DAY(PAY.PYMT_DTE)";
					break;
				default:
					$groupClauses[] = "PAY.PYMT_DTE";
					break;
			}
		}
			
		$groupClause = implode(", ", $groupClauses);
	} else {
		$groupClause = "PAY.PYMT_DTE";
	}


	$groupClauses = implode(", ", $groupClauses);
	$whereClauses = implode(" AND ", $whereClauses);
	

if ($_GET['RL_TYP'] == 'RL_YEAR') {
	
	$query = "SELECT MO.*,
		PAY.PAY_MONTH,  
		COALESCE(SUM(PAY.PAY_AMT), 0) AS PAY_AMT 
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
	(SELECT 
					DATE(PAY.PYMT_DTE) AS PAY_DTE,
					YEAR(PAY.PYMT_DTE) AS PAY_YEAR,
					MONTH(PAY.PYMT_DTE) AS PAY_MONTH,
					DAY(PAY.PYMT_DTE) AS PAY_DAY,
					MONTHNAME(PAY.PYMT_DTE) AS PAY_MONTHNAME,
					SUM(PAY.PAY_AMT) AS PAY_AMT
				FROM CMP.PAYROLL PAY
				LEFT JOIN CMP.PEOPLE PPL
					ON PAY.PRSN_NBR = PPL.PRSN_NBR
				WHERE ".$whereClauses."
				GROUP BY ".$groupClauses."
			) PAY ON PAY.PAY_MONTH = MO.ACT_MO
	GROUP BY MO.ACT_MO";
		
	}
	else {
	$query = "SELECT 
					DATE(PAY.PYMT_DTE) AS PAY_DTE,
					YEAR(PAY.PYMT_DTE) AS PAY_YEAR,
					MONTH(PAY.PYMT_DTE) AS PAY_MONTH,
					DAY(PAY.PYMT_DTE) AS PAY_DAY,
					MONTHNAME(PAY.PYMT_DTE) AS PAY_MONTHNAME,
					SUM(PAY.PAY_AMT) AS PAY_AMT
				FROM CMP.PAYROLL PAY
				LEFT JOIN CMP.PEOPLE PPL
					ON PAY.PRSN_NBR = PPL.PRSN_NBR
				WHERE ".$whereClauses."
				GROUP BY ".$groupClauses."
				";
	}

	echo "<pre>".$query;
	
	$pagination = pagination($query, 1000);
		
	$result = mysql_query($pagination['query']);

	while($row = mysql_fetch_array($result)) {

		$results['data'][] = $row;
		$results['total']['PAY_AMT'] 	+= $row['PAY_AMT'];	
		
	}
	
	
}
	
//print_r($results);

echo json_encode($results);