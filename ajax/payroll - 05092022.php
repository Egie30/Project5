<?php
require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

/*
Don't forget to update TAX_F on ".$cmp.".COMPANY
*/

$Accounting		= $_GET['ACTG'];
$PayActgType	= $_GET['PAY_ACTG_TYP'];

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
$pymtDate 		= $_GET['PYMT_DTE'];

$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];


//============================ GAJI KARYAWAN ==============================

//Proreliance ==> masuk ke PT (Laba Rugi Champion Printing) ==> CO_NBR Proreliance = 997
//The Common Grounds ==> masuk ke PT (Laba Rugi Champion Campus) ==> CO_NBR The Common Grounds = 1099

if (!empty($_GET['CO_NBR'])) {
	$companyNumber = $_GET['CO_NBR'];
}

$whereClauses	= array("PAY.DEL_NBR = 0", "PPL.DEL_NBR = 0", "DATE(PAY.PYMT_DTE) >= (SELECT BEG_RPT FROM NST.PARAM_LOC)", "PPL.CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_COMPANY WHERE CO_NBR_DEF = ".$CoNbrDef.")");
$groupClauses 	= array();


	if ($days != "") {
		$whereClauses[] = "DAY(PAY.PYMT_DTE)=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(PAY.PYMT_DTE)= ".$months;
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(PAY.PYMT_DTE)= ". $years;
	}
	
	if ($pymtDate != "") {
		$whereClauses[] = "PAY.PYMT_DTE= '". $pymtDate."' ";
	}
	
	
if($Accounting == 0) {
	$whereClauses[]		= "PAY.ACTG_TYP IN (1,2,3)";
}
else {
	$whereClauses[]		= "PAY.ACTG_TYP IN (".$Accounting.") ";
}


if($PayActgType != 0) {
	$whereClauses[]		= "PAY.PAY_ACTG_TYP IN (".$PayActgType.") ";
}
else {
	$whereClauses[]		= "PAY.PAY_ACTG_TYP IN (1,2) ";
}


$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'pagination' => $pagination,
	'total' => array()
);

	
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
				case "PRSN_NBR":
					$groupClauses[] = "PAY.PYMT_DTE, PAY.PRSN_NBR";
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
					PPL.PRSN_NBR,
					PPL.NAME,
					SUM(PAY.DSBRS_CRDT) AS DSBRS_CRDT,
					SUM(PAY.DEBT_MO) AS DEBT_MO,
					SUM(PAY.PAY_AMT) AS PAY_AMT
				FROM PAY.PAYROLL PAY
				LEFT JOIN CMP.PEOPLE PPL
					ON PAY.PRSN_NBR = PPL.PRSN_NBR
				LEFT JOIN CMP.POS_TYP POS
					ON POS.POS_TYP = PPL.POS_TYP
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
					PPL.PRSN_NBR,
					PPL.NAME,
					MONTHNAME(PAY.PYMT_DTE) AS PAY_MONTHNAME,
					SUM(PAY.DSBRS_CRDT) AS DSBRS_CRDT,
					SUM(PAY.DEBT_MO) AS DEBT_MO,
					SUM(PAY.PAY_AMT) AS PAY_AMT
				FROM PAY.PAYROLL PAY
				LEFT JOIN CMP.PEOPLE PPL
					ON PAY.PRSN_NBR = PPL.PRSN_NBR
				LEFT JOIN CMP.POS_TYP POS
					ON POS.POS_TYP = PPL.POS_TYP
				WHERE ".$whereClauses."
				GROUP BY ".$groupClauses."
				";
	}

	//echo "<pre>".$query;
	
	$pagination = pagination($query, 1000);
		
	$result = mysql_query($pagination['query']);

	while($row = mysql_fetch_array($result)) {

		$results['data'][] = $row;
		$results['total']['PAY_AMT'] 	+= $row['PAY_AMT'];	
		$results['total']['DSBRS_CRDT'] 	+= $row['DSBRS_CRDT'];	
		$results['total']['DEBT_MO'] 	+= $row['DEBT_MO'];	
		
	}
	
	

	
//print_r($results);

echo json_encode($results);

//Update data payroll lama untuk accounting 

/*
UPDATE PAY.PAYROLL PAY 
LEFT JOIN CMP.PEOPLE PPL
	ON PAY.PRSN_NBR = PPL.PRSN_NBR
LEFT JOIN NST.PARAM_COMPANY PRM 
	ON PRM.CO_NBR = PPL.CO_NBR 
LEFT JOIN CMP.POS_TYP POS
	ON POS.POS_TYP = PPL.POS_TYP
SET PAY.ACTG_TYP = PRM.ACTG_TYP
WHERE YEAR(PAY.PYMT_DTE) = 2018;

=================================================

UPDATE PAY.PAYROLL PAY 
LEFT JOIN CMP.PEOPLE PPL
	ON PAY.PRSN_NBR = PPL.PRSN_NBR
LEFT JOIN NST.PARAM_COMPANY PRM 
	ON PRM.CO_NBR = PPL.CO_NBR 
LEFT JOIN CMP.POS_TYP POS
	ON POS.POS_TYP = PPL.POS_TYP
SET PAY.PAY_ACTG_TYP = 1
WHERE SUBSTRING(POS.SEC_KEY,10,1) > 7
AND YEAR(PAY.PYMT_DTE) = 2018;

==============================================

UPDATE PAY.PAYROLL PAY 
LEFT JOIN CMP.PEOPLE PPL
	ON PAY.PRSN_NBR = PPL.PRSN_NBR
LEFT JOIN NST.PARAM_COMPANY PRM 
	ON PRM.CO_NBR = PPL.CO_NBR 
LEFT JOIN CMP.POS_TYP POS
	ON POS.POS_TYP = PPL.POS_TYP
SET PAY.PAY_ACTG_TYP = 2
WHERE SUBSTRING(POS.SEC_KEY,10,1) <= 7
AND YEAR(PAY.PYMT_DTE) = 2018
;
*/