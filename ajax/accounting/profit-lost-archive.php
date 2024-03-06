<?php
require_once __DIR__ . "/../../framework/database/connect.php";
require_once __DIR__ . "/../../framework/functions/default.php";
require_once __DIR__ . "/../../framework/pagination/pagination.php";

/*
Don't forget to update TAX_F on ".$cmp.".COMPANY
*/

$bookNumber		= $_GET['BK_NBR'];
$Accounting		= $_GET['ACTG'];
$companyNumber  = $CoNbrDef;
$plusMode 		= $_GET['PLUS'];

$query_book		= "SELECT MONTH(BEG_DTE) AS ACT_MONTH, 
					YEAR(BEG_DTE) AS ACT_YEAR 
				FROM RTL.ACCTG_BK
				WHERE BK_NBR = ".$bookNumber." ";
				
$result_book	= mysql_query($query_book);
$row_book		= mysql_fetch_array($result_book);

$ActMonth		= $row_book['ACT_MONTH'];
$ActYear		= $row_book['ACT_YEAR'];
	
$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'print_digital' => array(),
	'retail' => array(),
	'retail_stock' => array(),
	'routine' => array(),
	'cash' => array(),
	'payroll' => array(),
	'PROFIT' => array(),
	'total' => array()
);



$wherePrint		= array();
$whereRetail 	= array();
$whereStock 	= array();
$whereRoutine 	= array();
$whereCash 		= array();
$wherePayroll	= array("PAY.DEL_NBR = 0", "PPL.DEL_NBR = 0");

$totalPrintDigital	= 0;
$totalPembelian		= 0;
$totalReturPembelian= 0;
$totalPayroll		= 0;
$totalRoutine		= 0;
$totalCash			= 0;
$totalRetail		= 0;

//===================== DIGITAL PRINTING ===========================

$wherePrint[]	= "HED.DEL_NBR = 0";

if (!empty($_GET['CO_NBR'])) {
	$companyNumber = $_GET['CO_NBR'];
}

if (isset($ActMonth)) {
	$wherePrint[] = "MONTH(PAY.MAX_CRT_TS) = '" . $ActMonth . "'";
}

if (isset($ActYear)) {
	$wherePrint[] = "YEAR(PAY.MAX_CRT_TS) = '" . $ActYear . "'";
}
	
if($companyNumber == 1002) {	$rtl = "RTL_CAMPUS";	$cmp = "CMP_CAMPUS";	}
	else {	$rtl = "RTL";	$cmp = "CMP"; }


$wherePrint[] = "HED.PRN_CO_NBR=" . $companyNumber;

if ($Accounting == 1) {
	$wherePrint[] = "HED.TAX_APL_ID IN ('I', 'A')	AND HED.BUY_CO_NBR IS NOT NULL";
}

if ($Accounting == 2) {
	$wherePrint[] = "((HED.TAX_APL_ID NOT IN ('I', 'A') AND COM.TAX_F = 1 AND HED.BUY_CO_NBR IS NOT NULL)
						OR (HED.TAX_APL_ID NOT IN ('I', 'A') AND HED.BUY_CO_NBR IS NULL))";
}

if ($Accounting == 3) {
	$wherePrint[] = "(HED.TAX_APL_ID NOT IN ('I', 'A') AND COM.TAX_F = 0 AND HED.BUY_CO_NBR IS NOT NULL)";
}

$wherePrint[]	= "PAY.TND_AMT >= HED.TOT_AMT";

$wherePrint = implode(" AND ", $wherePrint);

$queryPrint = "SELECT DATE(HED.ORD_TS) AS ORD_DTE,
			DATE(PAY.MAX_CRT_TS) AS CSH_DTE,
			YEAR(PAY.MAX_CRT_TS) AS CSH_YEAR,
			MONTH(PAY.MAX_CRT_TS) AS CSH_MONTH,
			DAY(PAY.MAX_CRT_TS) AS CSH_DAY,
			MONTHNAME(PAY.MAX_CRT_TS) AS CSH_MONTHNAME,
			HED.ORD_NBR,
			COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
			COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
			COALESCE(SUM(PAY.TND_AMT), 0) AS TND_AMT,
			(CASE WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
			WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
			ELSE 'Tunai' END 
			) AS BUY_NAME,
			PAY.MAX_CRT_TS
		FROM CMP.PRN_DIG_ORD_HEAD HED
			LEFT JOIN (
				SELECT 
					PYMT.PYMT_NBR,
					PYMT.ORD_NBR,
					SUM(PYMT.TND_AMT) AS TND_AMT,
					PYMT.CRT_TS,
					MAX(PYMT.CRT_TS) AS MAX_CRT_TS
				FROM CMP.PRN_DIG_ORD_PYMT PYMT
				WHERE PYMT.DEL_NBR = 0
				GROUP BY PYMT.ORD_NBR
			) PAY ON PAY.ORD_NBR = HED.ORD_NBR
			LEFT JOIN CMP.COMPANY COM
				ON HED.BUY_CO_NBR = COM.CO_NBR
			LEFT JOIN CMP.PEOPLE PPL
				ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
				WHERE  " . $wherePrint . "
			";

$resultPrint	= mysql_query($queryPrint);
$rowPrint		= mysql_fetch_array($resultPrint);

$results['print_digital'] 	= $rowPrint;
$totalPrintDigital			= $rowPrint['TOT_AMT'];


//===================== PENJUALAN LAIN-LAIN (RETAIL) ===========================

if (($Accounting == 0) || ($Accounting == 2)) {
	
$whereRetail[]	= "CSH.ACT_F = 0";
$whereRetail[]	= "CSH.CSH_FLO_TYP = 'RT' ";

if (!empty($_GET['CO_NBR'])) {
	$companyNumber = $_GET['CO_NBR'];
}

if (isset($ActMonth)) {
	$whereRetail[] = "MONTH(CSH.CRT_TS) = '" . $ActMonth . "'";
}

if (isset($ActYear)) {
	$whereRetail[] = "YEAR(CSH.CRT_TS) = '" . $ActYear . "'";
}
	
if($companyNumber == 1002) {	$rtl = "RTL_CAMPUS";	$cmp = "CMP_CAMPUS";	}
	else {	$rtl = "RTL";	$cmp = "CMP"; }


$whereRetail[] = "CSH.CO_NBR = " . $companyNumber;

$whereRetail = implode(" AND ", $whereRetail);

$queryRetail = "SELECT DATE(CSH.CRT_TS) AS CSH_DTE,
			YEAR(CSH.CRT_TS) AS CSH_YEAR,
			MONTH(CSH.CRT_TS) AS CSH_MONTH,
			DAY(CSH.CRT_TS) AS CSH_DAY,
			MONTHNAME(CSH.CRT_TS) AS CSH_MONTHNAME,
			CSH.TRSC_NBR, 
			CSH.TRSC_NBR_PLUS,
			COALESCE(SUM(CSH.TND_AMT), 0) AS TND_AMT,
			SPL.NAME AS SPL_NAME
		FROM ".$rtl.".CSH_REG CSH
			LEFT OUTER JOIN ".$rtl.".INVENTORY INV
				ON CSH.INV_NBR = INV.INV_NBR
			LEFT OUTER JOIN ".$cmp.".COMPANY SPL
				ON INV.CO_NBR = SPL.CO_NBR
				WHERE " . $whereRetail . " ";
				
$resultRetail	= mysql_query($queryRetail);
$rowRetail		= mysql_fetch_array($resultRetail);

$results['retail'] = $rowRetail;

$totalRetail		= $rowRetail['TND_AMT'];

}

//=============================== PEMBELIAN ==============================


$whereStock[] = "HED.DEL_F=0";
$whereStock[] = "HED.EXP_TYP = 'PRNDIG' ";

if (isset($ActMonth)) {
	$whereStock[] = "MONTH(HED.DL_TS) = '" . $ActMonth . "'";
}

if (isset($ActYear)) {
	$whereStock[] = "YEAR(HED.DL_TS) = '" . $ActYear . "'";
}

if ($Accounting == 1) {
	//semua nota dengan PPN
	$whereStock[] = "HED.TAX_APL_ID IN ('I', 'A')";
}

if ($Accounting == 2) {
	//semua nota yang tanpa PPN dan suppliernya PKP
	$whereStock[] = "((HED.IVC_TYP = 'RC' AND HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 1) OR (HED.IVC_TYP = 'RT' AND HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 1))";
}

if ($Accounting == 3) {
	//semua nota yang tanpa PPN dan suppliernya tanpa PKP
	$whereStock[] = "((HED.IVC_TYP = 'RC' AND HED.TAX_APL_ID NOT IN ('I', 'A') AND SPL.TAX_F = 0) OR (HED.IVC_TYP = 'RT' AND HED.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 0))";
}


$whereStock = implode(" AND ", $whereStock);

$queryStock = "SELECT DATE(HED.DL_TS) AS ORD_DTE,
			YEAR(HED.DL_TS) AS ORD_YEAR,
			MONTH(HED.DL_TS) AS ORD_MONTH,
			DAY(HED.DL_TS) AS ORD_DAY,
			MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME,
			HED.ORD_NBR, 
			SPL.NAME AS SPL_NAME,
			RCV.NAME AS RCV_NAME,
			COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
			COALESCE(SUM(HED.PYMT_DOWN), 0) AS PYMT_DOWN,
			COALESCE(SUM(HED.PYMT_REM), 0) AS PYMT_REM,
			COALESCE(SUM(HED.TOT_REM), 0) AS TOT_REM,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' THEN HED.TOT_AMT ELSE 0 END) AS RCV_TOT_AMT,
			SUM(CASE WHEN HED.IVC_TYP = 'RT' THEN HED.TOT_AMT ELSE 0 END) AS RTR_TOT_AMT
		FROM RTL.RTL_STK_HEAD HED
			LEFT OUTER JOIN 
			(SELECT DET.ORD_NBR,
					SUM(DET.TOT_SUB) AS TOT_SUB
				FROM RTL.RTL_STK_DET DET
				LEFT OUTER JOIN RTL.RTL_STK_HEAD HED
					ON DET.ORD_NBR = HED.ORD_NBR
				LEFT OUTER JOIN RTL.INVENTORY INV
					ON INV.INV_NBR = DET.INV_NBR
				WHERE HED.DEL_F = 0	
				GROUP BY DET.ORD_NBR
				) DET ON DET.ORD_NBR = HED.ORD_NBR
			LEFT OUTER JOIN CMP.COMPANY SPL
				ON HED.SHP_CO_NBR = SPL.CO_NBR
			LEFT OUTER JOIN CMP.COMPANY RCV
				ON HED.RCV_CO_NBR = RCV.CO_NBR
				WHERE " . $whereStock . "
				";
/*		
$queryStock = "SELECT DATE(HEAD.DL_TS) AS ORD_DTE,
			YEAR(HEAD.DL_TS) AS ORD_YEAR,
			MONTH(HEAD.DL_TS) AS ORD_MONTH,
			DAY(HEAD.DL_TS) AS ORD_DAY,
			MONTHNAME(HEAD.DL_TS) AS ORD_MONTHNAME,
			HEAD.ORD_NBR, 
			SPL.CO_NBR AS SPL_NBR,
			SPL.NAME AS SPL_NAME,
			RCV.CO_NBR AS RCV_NBR,
			RCV.NAME AS RCV_NAME,
			SUM(CASE WHEN HEAD.IVC_TYP = 'RC' AND HEAD.RCV_CO_NBR = ".$companyNumber." THEN HEAD.TOT_AMT ELSE 0 END) AS RCV_TOT_AMT,
			SUM(CASE WHEN HEAD.IVC_TYP = 'RT' AND HEAD.SHP_CO_NBR = ".$companyNumber." THEN HEAD.TOT_AMT ELSE 0 END) AS RTR_TOT_AMT
		FROM ".$rtl.".RTL_STK_HEAD HEAD
			LEFT OUTER JOIN ".$cmp.".COMPANY SPL
				ON HEAD.SHP_CO_NBR = SPL.CO_NBR
			LEFT OUTER JOIN ".$cmp.".COMPANY RCV
				ON HEAD.RCV_CO_NBR = RCV.CO_NBR
				WHERE " . $whereStock . "
				";
*/

//echo "<pre>".$queryStock;
			
$resultStock	= mysql_query($queryStock);
$rowStock		= mysql_fetch_array($resultStock);

$results['retail_stock'] = $rowStock;				
$totalPembelian			= $rowStock['RCV_TOT_AMT'];
$totalReturPembelian	= $rowStock['RTR_TOT_AMT'];

//============================ PENGELUARAN RUTIN ==============================

if (($Accounting == 0) || ($Accounting == 2)) {
	
	if (isset($ActMonth)) {
		$whereRoutine[] = "MONTH(UTL.CRT_TS) = '" . $ActMonth . "'";
	}

	if (isset($ActYear)) {
		$whereRoutine[] = "YEAR(UTL.CRT_TS) = '" . $ActYear . "'";
	}
	
	$whereRoutine = implode(" AND ", $whereRoutine);

	$queryRoutine = "SELECT UTL_NBR,
				DATE(CRT_TS) AS UTL_DTE,
				DAY(CRT_TS) AS UTL_DAY,
				MONTH(CRT_TS) AS UTL_MONTH,
				YEAR(CRT_TS) AS UTL_YEAR,
				MONTHNAME(CRT_TS) AS UTL_MONTHNAME,
				TYP.UTL_DESC,
				SUM(UTL.TOT_SUB) AS TOT_SUB
					FROM CMP.UTILITY UTL 
					LEFT JOIN
						CMP.UTL_TYP TYP ON UTL.UTL_TYP=TYP.UTL_TYP
					WHERE ".$whereRoutine."	
					GROUP BY TYP.UTL_TYP
					ORDER BY TYP.UTL_TYP
					";
			
	$resultRoutine	= mysql_query($queryRoutine);
	while($rowRoutine = mysql_fetch_array($resultRoutine)) {
		$results['routine'][] = $rowRoutine;				
		$totalRoutine	+= $rowRoutine['TOT_SUB'];
	}
}


//echo "<pre>".$queryRoutine;


//============================ PENGELUARAN KAS ==============================

if (($Accounting == 0) || ($Accounting == 2)) {
	
	if (isset($ActMonth)) {
		$whereCash[] = "MONTH(EXP.CRT_TS) = '" . $ActMonth . "'";
	}

	if (isset($ActYear)) {
		$whereCash[] = "YEAR(EXP.CRT_TS) = '" . $ActYear . "'";
	}
	
	$whereCash = implode(" AND ", $whereCash);

	$queryCash = "SELECT EXP_NBR,
		DATE(CRT_TS) AS EXP_DTE,
		DAY(CRT_TS) AS EXP_DAY,
		MONTH(CRT_TS) AS EXP_MONTH,
		YEAR(CRT_TS) AS EXP_YEAR,
		MONTHNAME(CRT_TS) AS EXP_MONTHNAME,
		DATE(CRT_TS) AS DTE,
		TYP.EXP_DESC,
		SUM(EXP.TOT_SUB) AS TOT_SUB
			FROM ".$cmp.".EXPENSE EXP
			INNER JOIN ".$cmp.".EXP_TYP TYP ON EXP.EXP_TYP=TYP.EXP_TYP
				WHERE ".$whereCash."
				GROUP BY TYP.EXP_TYP
				ORDER BY TYP.EXP_TYP
				";
				
	$resultCash	= mysql_query($queryCash);
	while($rowCash = mysql_fetch_array($resultCash)) {
		$results['cash'][] = $rowCash;	
		$totalCash	+= $rowCash['TOT_SUB'];		
	}
		
}

//============================ GAJI KARYAWAN ==============================

//Proreliance ==> masuk ke PT (Laba Rugi Champion Printing) ==> CO_NBR Proreliance = 997
//The Common Grounds ==> masuk ke PT (Laba Rugi Champion Campus) ==> CO_NBR The Common Grounds = 1099

if (($Accounting == 0) || ($Accounting == 1) || ($Accounting == 2)) {
	
	$Proreliance 	= 997;
	
	if (isset($ActMonth)) {
		$wherePayroll[] = "MONTH(PAY.UPD_TS) = '" . $ActMonth . "'";
	}

	if (isset($ActYear)) {
		$wherePayroll[] = "YEAR(PAY.UPD_TS) = '" . $ActYear . "'";
	}
	
	if ($Accounting == 0) {
		$wherePayroll[] = "(PPL.CO_NBR = ".$Proreliance." OR PPL.CO_NBR = ".$CoNbrDef.") ";
	}
	
	if ($Accounting == 1) {
		$wherePayroll[] = "PPL.CO_NBR = ".$Proreliance." ";
	}

	if ($Accounting == 2) {
		$wherePayroll[] = "PPL.CO_NBR = ".$CoNbrDef." ";
	}
		
	$wherePayroll = implode(" AND ", $wherePayroll);

	$queryPayroll = "SELECT 
					SUM(PAY.PAY_AMT) AS PAY_AMT
				FROM CMP.PAYROLL PAY
				LEFT JOIN CMP.PEOPLE PPL
					ON PAY.PRSN_NBR = PPL.PRSN_NBR
				WHERE ".$wherePayroll."
				";
				
	$resultPayroll	= mysql_query($queryPayroll);
	$rowPayroll = mysql_fetch_array($resultPayroll);
	$results['payroll'] = $rowPayroll;	
	$totalPayroll	= $rowPayroll['PAY_AMT'];
	//echo "<pre>".$queryPayroll;
	
}

/*
try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../cost-cash.php";

	$resultsCash = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}
*/


$results['data']['CASH'] 			= $resultsCash;
$results['data']['EXPENSE'] 		= $resultsCash->expense;

$results['PROFIT']['BRUTO']	= $totalPrintDigital - $totalPembelian - $totalReturPembelian - $totalRoutine - $totalPayroll - $totalCash;

$results['PROFIT']['NETTO']	= $results['PROFIT']['BRUTO'] + $totalRetail;

//echo $totalRetail;

echo json_encode($results);