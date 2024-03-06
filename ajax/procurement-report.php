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
$curl			= $_GET['CURL'];
$CatSubNbr		= $_GET['CAT_SUB_NBR'];
$CatTypeNbr		= $_GET['CAT_TYP_NBR'];
$Type			= $_GET['TYP'];
$InvoiceType	= $_GET['IVC_TYP'];
$PaymentType	= $_GET['PYMT_TYP'];

if ($Type == 'ACTG') {
	$field		= "HED.PYMT_REM_TS";
	$field_total= "HED.TOT_AMT";
}
else if ($Type == 'ACTG_DOWN') { 
	$field		= "HED.PYMT_DOWN_TS";
	$field_total= "HED.PYMT_DOWN";
}
else if ($Type == 'ACTG_REM') { 
	$field		= "HED.PYMT_REM_TS";
	$field_total= "HED.PYMT_REM";
}
else if ($Type == 'ACTG_TAX') { 
	$field 		= "HED.TAX_IVC_DTE";
	$field_total= "HED.TOT_AMT";
}
else {
	$field 		= "HED.ORD_DTE";
	$field_total= "HED.TOT_AMT";
}

$searchQuery    = strtoupper($_REQUEST['s']);
$groups = (array) $_GET['GROUP'];

$whereClauses = array("HED.DEL_F = 0", "DATE(HED.ORD_DTE) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)", "HED.IVC_TYP = '".$InvoiceType."' ");
$groupClauses = array();

/*
if ($Type == 'ACTG') {
	$whereClauses[]	= "HED.TOT_REM = 0";
}
*/

if (empty($beginDate) && empty($endDate)) {
	if ($days != "") {
		$whereClauses[] = "DAY(".$field.")=".$days;
	}

	if ($months != "") {
		$whereClauses[] = "MONTH(".$field.")= ".$months;
	}

	if ($years != "") {
		$whereClauses[] = "YEAR(".$field.")= ". $years;
	}

	if ($day != "") {
		$whereClauses[] = "DAY(".$field.")=" . $day;
	}

	if ($month != "") {
		$whereClauses[] = "MONTH(".$field.")=" . $month;
	}

	if ($year != "") {
		$whereClauses[] = "YEAR(".$field.")=" . $year;
	}
} else {
	if (!empty($beginDate)) {
		$whereClauses[] = "DATE(".$field.") >= '" . $beginDate . "'";
	}

	if (!empty($endDate)) {
		$whereClauses[] = "DATE(".$field.") <= '" . $endDate . "'";
	}
}

if ($CatSubNbr != "") {
	$whereClauses[] = "HED.CAT_SUB_NBR = ".$CatSubNbr." ";
}

$query_co	= "SELECT 
				(CASE WHEN ACTG_TYP = 1 AND CO_NBR_DEF = '".$CoNbrDef."' THEN CO_NBR ELSE '' END) AS COM_PT,
				(CASE WHEN ACTG_TYP = 2 AND CO_NBR_DEF = '".$CoNbrDef."' THEN CO_NBR ELSE '' END) AS COM_CV,
				(CASE WHEN ACTG_TYP = 3 AND CO_NBR_DEF = '".$CoNbrDef."' THEN CO_NBR ELSE '' END) AS COM_PR
				FROM NST.PARAM_PAYROLL";
				
$result_co	= mysql_query($query_co);

while ($row_co	= mysql_fetch_array($result_co)) {

	if ($row_co['COM_PT'] != '') { $CompanyPT = $row_co['COM_PT']; }
	if ($row_co['COM_CV'] != '') { $CompanyCV = $row_co['COM_CV']; }
	if ($row_co['COM_PR'] != '') { $CompanyPR = $row_co['COM_PR']; }
}

if ($Accounting != 0) {
	$whereClauses[] = "HED.ACTG_TYP = ".$Accounting." ";
}
	
if ($CatTypeNbr != 0) {
	$whereClauses[] = "TYP.CAT_TYP_NBR IN (".$CatTypeNbr.") ";
}

if ($PaymentType != "") {
	$whereClauses[] = "HED.PYMT_TYP = '".$PaymentType."' ";
}

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "YEAR":
				$groupClauses[] = "YEAR(".$field.")";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(".$field."), MONTH(".$field.")";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(".$field."), MONTH(".$field."), DAY(".$field.")";
				break;
			case "ORD_NBR":
				$groupClauses[] = "HED.ORD_NBR";
				break;
			case "CAT_SUB_NBR":
				$groupClauses[] = "HED.CAT_SUB_NBR";
				break;
			default:
				$groupClauses[] = "HED.ORD_NBR";
				break;
		}
	}
		
	$groupClause = implode(", ", $groupClauses);
} else {
	$groupClause = "HED.ORD_NBR";
}

$whereClauses = implode(" AND ", $whereClauses);
$groupClauses = implode(", ", $groupClauses);


if ($_GET['RL_TYP'] == 'RL_YEAR') {
	
$query	= "SELECT MO.*,
		PRN.CSH_MONTH,  
		COALESCE(SUM(PRN.TOT_AMT), 0) AS TOT_AMT 
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
	(	SELECT 
					HED.ORD_NBR,
					HED.CAT_SUB_NBR,
					SUB.CAT_SUB_DESC,
					TYP.CAT_TYP,
					SUM(".$field_total.") AS TOT_AMT,
					DATE(".$field.") AS ORD_DTE,
					DATE(HED.DL_TS) AS DL_DTE,
					MONTH(".$field.") AS ORD_MONTH,
					YEAR(".$field.") AS ORD_YEAR,
					MONTHNAME(".$field.") AS ORD_MONTHNAME,
					DATE(HED.PYMT_REM_TS) AS PAID_DTE,
					SHP.NAME AS SHIPPER,
					RCV.NAME AS RECEIVER,
					HED.TOT_REM,
					HED.ACTG_TYP,
					HED.PYMT_TYP,
					TYP.CAT_TYP_NBR,
					SUB.CD_SUB_NBR AS AKUN,
					TAX_IVC_NBR,
					TAX_IVC_DTE
				FROM RTL.RTL_STK_HEAD HED 
				LEFT JOIN RTL.CAT_SUB SUB 
					ON SUB.CAT_SUB_NBR = HED.CAT_SUB_NBR
				LEFT JOIN CMP.COMPANY SHP 
					ON SHP.CO_NBR = HED.SHP_CO_NBR
				LEFT JOIN CMP.COMPANY RCV
					ON RCV.CO_NBR = HED.RCV_CO_NBR
				INNER JOIN RTL.CAT_TYP TYP 
					ON TYP.CAT_TYP_NBR = SUB.CAT_TYP_NBR
				WHERE " . $whereClauses . "
				GROUP BY " . $groupClauses . "
				ORDER BY TYP.CAT_TYP_NBR
				) STK ON STK.ORD_MONTH = MO.ACT_MO
			GROUP BY MO.ACT_MO
			";
}
else {

		$query 	= "SELECT 
					HED.ORD_NBR,
					HED.CAT_SUB_NBR,
					SUB.CAT_SUB_DESC,
					TYP.CAT_TYP,
					".$field_total." AS TOT_AMT,
					DATE(".$field.") AS ORD_DTE,
					DATE(HED.DL_TS) AS DL_DTE,
					MONTH(".$field.") AS ORD_MONTH,
					YEAR(".$field.") AS ORD_YEAR,
					MONTHNAME(".$field.") AS ORD_MONTHNAME,
					#COALESCE(SUM(CASE WHEN HED.TAX_APL_ID IN ('I','A')THEN (HED.TOT_AMT)/11 ELSE HED.TAX_AMT END),0) AS TAX_AMT,
					HED.TAX_AMT,
					COALESCE(SUM(DET.TOT_SUB),0) AS TOTAL_SUB,
					COALESCE(HED.FEE_MISC,0) AS HED_FEE_MISC,
					COALESCE(SUM(
					CASE WHEN HED.TAX_APL_ID IN ('I','A')
							THEN (HED.TOT_AMT)/1.1 
						ELSE HED.TOT_AMT
						END
					),0) AS SUBTOTAL,
					DATE(HED.PYMT_REM_TS) AS PAID_DTE,
					SHP.NAME AS SHIPPER,
					RCV.NAME AS RECEIVER,
					HED.TOT_REM,
					HED.ACTG_TYP,
					HED.PYMT_TYP,
					TYP.CAT_TYP_NBR,
					SUB.CD_SUB_NBR AS AKUN,
					PYMT.PYMT_DESC,			
					HED.TAX_APL_ID,
					TAX.TAX_APL_DESC,
					TAX_IVC_NBR,
					TAX_IVC_DTE
				FROM RTL.RTL_STK_HEAD HED 
				LEFT JOIN RTL.RTL_STK_DET DET 
					ON DET.ORD_NBR = HED.ORD_NBR
				LEFT JOIN RTL.CAT_SUB SUB 
					ON SUB.CAT_SUB_NBR = HED.CAT_SUB_NBR
				LEFT JOIN CMP.COMPANY SHP 
					ON SHP.CO_NBR = HED.SHP_CO_NBR
				LEFT JOIN CMP.COMPANY RCV
					ON RCV.CO_NBR = HED.RCV_CO_NBR
				LEFT JOIN RTL.PYMT_TYP PYMT
					ON PYMT.PYMT_TYP = HED.PYMT_TYP
				INNER JOIN RTL.CAT_TYP TYP 
					ON TYP.CAT_TYP_NBR = SUB.CAT_TYP_NBR
				LEFT JOIN CMP.TAX_APL TAX
					ON TAX.TAX_APL_ID = HED.TAX_APL_ID
				WHERE " . $whereClauses . "
				GROUP BY " . $groupClauses . "
				ORDER BY HED.DL_TS
						";


}

//echo "<pre>".$query;

$pagination = pagination($query, 1000);

$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'pagination' => $pagination,
	'total' => array()
);

$result = mysql_query($pagination['query']);

while($row = mysql_fetch_array($result)) {

	$results['data'][] = $row;

	$results['total']['TOTAL_SUB'] 	+= $row['TOTAL_SUB'];
	$results['total']['SUBTOTAL'] 	+= $row['SUBTOTAL'];
	$results['total']['HED_FEE_MISC'] += $row['HED_FEE_MISC'];
	$results['total']['TAX_AMT'] 	+= $row['TAX_AMT'];
	$results['total']['TOT_AMT'] 	+= $row['TOT_AMT'];
	$results['total']['TOT_REM'] 	+= $row['TOT_REM'];

}

//echo "<pre>"; print_r($results);

echo json_encode($results);

/*
UPDATE RTL.RTL_STK_HEAD HED
LEFT JOIN 
(
	SELECT 
		REG.REG_NBR,
		REG.TRSC_NBR,
		REG.RTL_BRC,
		SUM(REG.TND_AMT) AS TND_AMT,
		TRSC.PYMT_TYP
	FROM
	(SELECT
		CSH.REG_NBR,
		CSH.TRSC_NBR,
		CSH.RTL_BRC,
		CSH.TND_AMT
	FROM RTL.CSH_REG CSH 
	WHERE CSH.CSH_FLO_TYP = 'IV'
		AND CSH.ACT_F = 0
		AND MONTH(CSH.CRT_TS) = 1
		AND YEAR(CSH.CRT_TS) = 2017
	GROUP BY CSH.REG_NBR
	) REG 
	JOIN 
	(SELECT
		CSH.REG_NBR,
		CSH.TRSC_NBR,
		CSH.RTL_BRC,
		CSH.PYMT_TYP
	FROM RTL.CSH_REG CSH 
	WHERE CSH.CSH_FLO_TYP = 'PA'
		AND CSH.ACT_F = 0
		AND MONTH(CSH.CRT_TS) = 1
		AND YEAR(CSH.CRT_TS) = 2017
	GROUP BY CSH.TRSC_NBR 
	) TRSC
	ON REG.TRSC_NBR = TRSC.TRSC_NBR
	GROUP BY REG.REG_NBR
) CASHIER
	ON HED.VAL_PYMT_REM = CASHIER.REG_NBR
SET HED.PYMT_TYP = CASHIER.PYMT_TYP
WHERE MONTH(HED.PYMT_REM_TS) = 1
		AND YEAR(HED.PYMT_REM_TS) = 2017
		AND HED.DEL_F = 0
	*/
	
	
/*
SELECT
	*
FROM RTL.RTL_STK_HEAD
WHERE PYMT_REM_TS IS NOT NULL AND TOT_REM =0 AND PYMT_TYP IS NULL
AND DATE(ORD_DTE) >= '2017-01-01'

*/


/*
UPDATE RTL.RTL_STK_HEAD
SET PYMT_TYP = 'TRF'
WHERE PYMT_REM_TS IS NOT NULL AND TOT_REM =0 AND PYMT_TYP IS NULL
AND DATE(ORD_DTE) >= '2017-01-01'
*/