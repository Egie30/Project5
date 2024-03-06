<?php
include "framework/database/connect.php";
include "framework/database/connect-cloud.php";
include "framework/functions/default.php";

$query = "SELECT CO_NBR_DEF, OUT_CMSN_PCT FROM NST.PARAM_LOC";
$result = mysql_query($query,$local);
$rowPL  = mysql_fetch_array($result);

$companyNumber 	= $rowPL['CO_NBR_DEF'];
$outsourcePcr 	= $rowPL['OUT_CMSN_PCT'];

//Mengambil configurasi tanggal payroll bedasarkan tanggal kemarin
$query = "SELECT  
	PAY_CONFIG_NBR,
	PAY_BEG_DTE,
	PAY_END_DTE 
FROM PAY.PAY_CONFIG_DTE 
WHERE (CURRENT_DATE - INTERVAL 1 DAY) >= PAY_BEG_DTE AND (CURRENT_DATE - INTERVAL 1 DAY) <= PAY_END_DTE";
$result = mysql_query($query,$local);
$row    = mysql_fetch_array($result);

$PayConfigNbr = $row['PAY_CONFIG_NBR'];
$PayBegDte    = $row['PAY_BEG_DTE'];
$PayEndDte    = $row['PAY_END_DTE'];

//Untuk menghapus data CDW yang memiliki PAY_CONFIG_NBR hari kemarin
$query  = "DELETE FROM CDW.PAY_OUT_CMSN WHERE PRN_CO_NBR = ".$companyNumber." AND PAY_CONFIG_NBR=".$PayConfigNbr;
$result = mysql_query($query);

$query = "SELECT 
	STK.ORD_DET_NBR,
	STK.ORD_NBR,
	STK.INV_NBR,
	STK.OUT_CMN_F,
	STK.ORD_Q,
	STK.BUY_PRC,
	DET.ORD_NBR AS PRN_ORD_NBR,
	DET.ORD_DET_NBR AS PRN_ORD_DET_NBR,
	HED.PRN_CO_NBR,
	HED.BUY_CO_NBR,
	COM.NAME,
	COM.ACCT_EXEC_NBR,
	DET.DET_TTL,
	DET.ORD_Q,
	SELL_TOT_SUB,
	(CASE
		WHEN
			DATE(PAY.MAX_CRT_TS) >= DATE(HED.ORD_TS)
			AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
			THEN (((SELL_TOT_SUB - STK.BUY_PRC) * 5)/100)* (100 / 100)
		WHEN
			DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
			AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
			THEN (((SELL_TOT_SUB - STK.BUY_PRC) * 5)/100) * (80 / 100)
		WHEN
			DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
			AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
			THEN (((SELL_TOT_SUB - STK.BUY_PRC) * 5)/100)* (60 / 100)
		WHEN
			DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
			AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
			THEN (((SELL_TOT_SUB - STK.BUY_PRC) * 5)/100) * (40 / 100)
		WHEN
			DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
			AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
			THEN (((SELL_TOT_SUB - STK.BUY_PRC) * 5)/100) * (20 / 100)
		WHEN
			DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
			THEN (((SELL_TOT_SUB - STK.BUY_PRC) * 5)/100) * (0 / 100)
	END) AS TOT_CMSN_DET,
	'$outsourcePcr' AS CMSN_PCT,
	'$PayConfigNbr' AS PAY_CONFIG_NBR
FROM CMP.PRN_DIG_ORD_HEAD HED 
	LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
	INNER JOIN (
		SELECT
			DET.ORD_DET_NBR,
			ORD_NBR,
			DET_TTL,
			DET.ORD_Q,
			COALESCE(DET.TOT_SUB, 0) AS DET_TOT_SUB,
			SUM(COALESCE(DET.TOT_SUB, 0)) AS SELL_TOT_SUB
		FROM CMP.PRN_DIG_ORD_DET DET
		WHERE DEL_NBR = 0
		GROUP BY DET.ORD_DET_NBR
	)DET ON DET.ORD_NBR = HED.ORD_NBR
	INNER JOIN (
		SELECT
			ORD_DET_NBR, 
			DET.ORD_NBR,
			HED.ORD_DTE,
			ORD_DET_NBR_REF,
			RCV.OUT_CMN_F,
			DET.INV_NBR,
			DET.INV_PRC,
			DET.ORD_Q,
			DET.TOT_SUB AS BUY_PRC,
			DET.UPD_TS
		FROM RTL.RTL_STK_DET DET
			INNER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR = HED.ORD_NBR
			LEFT OUTER JOIN CMP.COMPANY RCV ON HED.SHP_CO_NBR = RCV.CO_NBR
		WHERE HED.DEL_F = 0 
			AND HED.IVC_TYP = 'RC'
			AND RCV.OUT_CMN_F = 1
			AND ORD_DET_NBR_REF != ''
		GROUP BY DET.ORD_DET_NBR
	) STK ON STK.ORD_DET_NBR_REF = DET.ORD_DET_NBR
	INNER JOIN (
		SELECT
			PYMT_NBR,
			ORD_NBR,
			SUM(TND_AMT) AS TND_AMT,
			CRT_TS,
			MAX(CRT_TS) AS MAX_CRT_TS
		FROM CMP.PRN_DIG_ORD_PYMT PYMT
		WHERE DEL_NBR = 0 AND VAL_NBR IS NOT NULL
		GROUP BY ORD_NBR
	) PAY ON HED.ORD_NBR = PAY.ORD_NBR
WHERE 
	HED.DEL_NBR = 0 
	AND DATE(PAY.MAX_CRT_TS) >= '$PayBegDte'
	AND DATE(PAY.MAX_CRT_TS) <= '$PayEndDte'
GROUP BY DET.ORD_DET_NBR";
//echo "<pre>".$query."<br>";
$result = mysql_query($query,$local);
if (mysql_num_rows($result)>0){
	while($row  = mysql_fetch_array($result)){
	$sql = "INSERT IGNORE INTO $CDW.PAY_OUT_CMSN(
		ORD_DET_NBR,
		ORD_NBR,
		INV_NBR,
		OUT_CMN_F,
		ORD_Q,
		INV_PRC,
		PRN_ORD_NBR,
		PRN_ORD_DET_NBR,
		BUY_CO_NBR,
		BUY_CO_NAME,
		ACCT_EXEC_NBR,
		DET_TTL,
		PRN_ORD_Q,
		PRC,
		TOT_CMSN,
		OUT_CMSN_PCT,
		PAY_CONFIG_NBR,
		PRN_CO_NBR
	)VALUES (
		'".$row['ORD_DET_NBR']."',
		'".$row['ORD_NBR']."',
		'".$row['INV_NBR']."',
		'".$row['OUT_CMN_F']."',
		'".$row['ORD_Q']."', 
		'".$row['BUY_PRC']."', 
		'".$row['PRN_ORD_NBR']."',
		'".$row['PRN_ORD_DET_NBR']."',
		'".$row['BUY_CO_NBR']."',
		'".$row['NAME']."',
		'".$row['ACCT_EXEC_NBR']."',
		'".$row['DET_TTL']."',
		'".$row['ORD_Q']."',
		'".$row['SELL_TOT_SUB']."',
		'".$row['TOT_CMSN_DET']."',
		'".$row['CMSN_PCT']."',
		'".$row['PAY_CONFIG_NBR']."',
		'".$row['PRN_CO_NBR']."')";
	$results = mysql_query($sql, $cloud);
	//echo "<pre>".$sql."<br>";
	}
}
?>