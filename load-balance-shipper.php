<?php
include "framework/database/connect.php";


ini_set('max_execution_time', 0);

$shipper	= mysql_connect($OLTP,"root","Pr0reliance");
$receiver	= mysql_connect($OLTA,"root","Pr0reliance");

$oltp_cmp	= "CMP";
$oltp_rtl	= "RTL";
$oltp_cdw	= "CDW";

$olta_cmp	= "CMP";
$olta_rtl	= "RTL";
$olta_cdw	= "CDW";

echo "<pre>";

//========================= load balance nota digital printing ====================//

	
	$query = "SELECT COALESCE(MAX(ORD_TS),'0000-00-00') AS DTE FROM ".$olta_cmp.".PRN_DIG_ORD_HEAD WHERE DEL_NBR = 0";
	$result = mysql_query($query, $receiver);
	$row 	= mysql_fetch_array($result);
	$date_olta = $row['DTE'];

	
	$query  = "SELECT COALESCE(MAX(PAID_DT),'0000-00-00') AS DTE FROM ".$oltp_cmp.".PRN_DIG_ORD_HEAD WHERE DEL_NBR = 0";
	$result = mysql_query($query, $shipper);
	$row 	= mysql_fetch_array($result);
	$date_oltp = $row['DTE'];
	
	echo $date_oltp." -- ".$date_olta."<br /><br />";
	
	
	if ($date_oltp > $date_olta) {
		
	
	$tbl_nm		= 'PRN_DIG_ORD_HEAD';
	$col_nm		= 'ORD_NBR';
	
		$queryHead	= "SELECT *
			FROM ".$oltp_cmp.".PRN_DIG_ORD_HEAD
			WHERE ORD_NBR_PLUS IS NOT NULL 
				AND ORD_NBR_PLUS != 0
				AND DEL_NBR = 0
				AND PAID_DT > '".$date_olta."' ";
				
		$resultHead	= mysql_query($queryHead, $shipper);
			
		echo $queryHead."<br />";
		
		while ($rowHead = mysql_fetch_array($resultHead)) {
			$arrayOrder[]		= $rowHead;
			$arrayOrderNbr[]	= $rowHead['ORD_NBR'];
		}
		
		$dataOrder = urlencode(json_encode($arrayOrder));

		CurlData($olta_cmp,$tbl_nm,$col_nm,$dataOrder,$OLTA); //curl data detail nota digital printing 	
		
		$OrderNbr = implode(", ", $arrayOrderNbr);
		
		
		print_r($OrderNbr);
		
		echo "<br /><br />";
		
		//================== data order detail =======================//
		
		
	$tbl_nm		= 'PRN_DIG_ORD_DET';
	$col_nm		= 'ORD_DET_NBR';
		
		$queryDetail	= "SELECT DET.*, HED.ORD_NBR_PLUS, HED.PAID_DT
			FROM ".$oltp_cmp.".PRN_DIG_ORD_DET DET
			JOIN ".$oltp_cmp.".PRN_DIG_ORD_HEAD HED
				ON DET.ORD_NBR = HED.ORD_NBR
			WHERE DET.ORD_NBR IN (".$OrderNbr.") AND DET.DEL_NBR = 0 AND HED.DEL_NBR = 0";
		
		$resultDetail	= mysql_query($queryDetail, $shipper);
			
		echo $queryDetail."<br /><br />";
		
		while ($rowDetail = mysql_fetch_array($resultDetail)) {
			$arrayDetailNbr[]	= $rowDetail;
		}
		
		$dataOrderDetail = urlencode(json_encode($arrayDetailNbr));

		//print_r($dataOrderDetail);
		
		CurlData($olta_cmp,$tbl_nm,$col_nm,$dataOrderDetail,$OLTA); //curl data detail nota digital printing 
		
		//================== data order payment =======================//
	
	
	$tbl_nm		= 'PRN_DIG_ORD_PYMT';
	$col_nm		= 'PYMT_NBR';
		
		$queryPayment	= "SELECT PYMT.*, HED.ORD_NBR_PLUS, HED.PAID_DT
			FROM ".$oltp_cmp.".PRN_DIG_ORD_PYMT PYMT
			JOIN ".$oltp_cmp.".PRN_DIG_ORD_HEAD HED
				ON PYMT.ORD_NBR = HED.ORD_NBR
			WHERE PYMT.ORD_NBR IN (".$OrderNbr.") AND PYMT.DEL_NBR = 0 AND HED.DEL_NBR = 0";
			
		$resultPayment	= mysql_query($queryPayment, $shipper);
	
		echo $queryPayment."<br /><br />";
		
		while ($rowPayment = mysql_fetch_array($resultPayment)) {
			$arrayPaymentNbr[]	= $rowPayment;
		}
		
		$dataOrderPayment = urlencode(json_encode($arrayPaymentNbr));
		
		CurlData($olta_cmp,$tbl_nm,$col_nm,$dataOrderPayment,$OLTA); //curl data payment nota digital printing 

	//==============================================================//
	
	$tbl_nm		= 'CSH_REG';
	$col_nm		= 'REG_NBR';
	
	$queryCsh	= "SELECT
			REG.*,
			(CASE WHEN REG.CSH_FLO_TYP = 'FL' THEN HED.ORD_NBR_PLUS ELSE NULL END) AS ORD_NBR_PLUS,
			HED.PAID_DT
		FROM RTL.CSH_REG REG 
		INNER JOIN
		(SELECT 
			CSH.TRSC_NBR,
			CSH.RTL_BRC
		FROM RTL.CSH_REG CSH
		WHERE CSH.RTL_BRC IN (".$OrderNbr.")
			AND CSH.CSH_FLO_TYP = 'FL'
		) CSH
			ON REG.TRSC_NBR = CSH.TRSC_NBR
		LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED
			ON HED.ORD_NBR = CSH.RTL_BRC";
	
	//echo $queryCsh."<br /><br />";
	
	$resultCsh	= mysql_query($queryCsh, $shipper);
	
	while ($rowCsh = mysql_fetch_array($resultCsh)) {
			$arrayCshNbr[]	= $rowCsh;
	}
		
		$dataCsh = urlencode(json_encode($arrayCshNbr));
		
		//print_r($dataCsh);

		CurlData($olta_rtl,$tbl_nm,$col_nm,$dataCsh,$OLTA);
}




	//==============================================================//	
	
/*
	$tbl_nm		= 'PAYROLL';
	$col_nm		= 'PYMT_DTE,PRSN_NBR';
		
	$_GET['ACTG']		= 2;
	$_GET['GROUP']		= 'PRSN_NBR';
	
	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/payroll.php";

		$resultsPayroll = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
	foreach($resultsPayroll->data as $data) {	
		$arrayPayroll[]	= $data;
	}
	
	print_r($resultsPayroll);
	
	$dataPayroll = urlencode(json_encode($arrayPayroll));
		
	print_r($dataPayroll);

	CurlData($olta_cmp,$tbl_nm,$col_nm,$dataPayroll,$OLTA);
	
	//==============================================================//	
	
	$tbl_nm		= 'EXPENSE';
	$col_nm		= 'EXP_NBR';
		
	$_GET['ACTG']		= 2;
	unset($_GET['GROUP']);
	
	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/cost-cash.php";

		$resultsCash = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
	foreach($resultsCash->data as $data) {	
		$arrayCash[]	= $data;
	}
	
	$dataCash = urlencode(json_encode($arrayCash));
		
	print_r($dataCash);

	CurlData($olta_cmp,$tbl_nm,$col_nm,$dataCash,$OLTA);
	
	//==============================================================//	
	
	$tbl_nm		= 'UTILITY';
	$col_nm		= 'UTL_NBR';
		
	$_GET['ACTG']		= 2;
	unset($_GET['GROUP']);
	
	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/cost-routine.php";

		$resultsRoutine = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
	foreach($resultsRoutine->data as $data) {	
		$arrayRoutine[]	= $data;
	}
	
	$dataRoutine = urlencode(json_encode($arrayRoutine));
		
	print_r($dataRoutine);

	CurlData($olta_cmp,$tbl_nm,$col_nm,$dataRoutine,$OLTA);
	
	
	
	
	
	*/
	
	
	//==============================================================//	
	

	function CurlData($database, $table, $col_nm, $DataCurl, $OLTA) {
		$url 	= "http://".$OLTA."/load-balance-receiver.php";
		//$url 	= "http://localhost/champion-printing/load-balance-receiver.php";
		$ch	= curl_init();
		
		$ch=curl_init($url);
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('database'=>$database,'table'=>$table,'key'=>$col_nm,'data'=>$DataCurl));

		$output = curl_exec($ch);
		
		if ($output === FALSE) {
			echo "<span style='color:red'>cURL Error: " . curl_error($ch)."<br/><br/></span>";
		} else {
			var_dump($output);
			echo "<br/><br/>";
		}
		
		curl_close($ch);
	}
	
	
	/*
	
	============== QUERY PAYROLL ================
	
	SELECT 
	SUM(PAY.PAY_AMT)
FROM CMP.PAYROLL PAY
LEFT JOIN CMP.PEOPLE PPL
	ON PAY.PRSN_NBR = PPL.PRSN_NBR
WHERE PPL.CO_NBR = 2996
	AND MONTH(PAY.PYMT_DTE) = 2
	AND YEAR(PAY.PYMT_DTE) = 2017
	
	
	
	============== QUERY PENGELUARAN RUTIN ================
	
		SELECT 
			SUM(UTL.TOT_SUB)
		FROM CMP.UTILITY UTL
		WHERE DATE(UTL.CRT_TS) >= '2017-01-01'
			AND DATE(UTL.CRT_TS) <= '2017-01-31'
	

	
	============== QUERY PENGELUARAN KAS ================
	
		SELECT 
			SUM(EXP.TOT_SUB)
		FROM CMP.EXPENSE EXP
		WHERE DATE(EXP.CRT_TS) >= '2017-01-01'
			AND DATE(EXP.CRT_TS) <= '2017-01-31'
	=========================================
	
	INSERT IGNORE INTO RTL.CSH_REG_COPY
SELECT
			REG.*,
			(CASE WHEN REG.CSH_FLO_TYP = 'FL' THEN HED.ORD_NBR_PLUS ELSE NULL END) AS ORD_NBR_PLUS,
			HED.PAID_DT
		FROM RTL.CSH_REG REG 
		INNER JOIN
		(SELECT 
			CSH.TRSC_NBR,
			CSH.RTL_BRC
		FROM RTL.CSH_REG CSH
		WHERE CSH.RTL_BRC IN (SELECT ORD_NBR FROM CMP.PRN_DIG_ORD_HEAD WHERE ORD_NBR_PLUS IS NOT NULL)
			AND CSH.CSH_FLO_TYP = 'FL'
		) CSH
			ON REG.TRSC_NBR = CSH.TRSC_NBR
		LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED
			ON HED.ORD_NBR = CSH.RTL_BRC
			
	==================================================
	
	INSERT IGNORE INTO RTL.CSH_REG
SELECT
			REG_NBR,
			TRSC_NBR,
			Q_NBR,
			REG_NBR_PLUS,
			TRSC_NBR_PLUS,
			CO_NBR,
			ORD_NBR_PLUS,
			RTL_Q,
			RTL_PRC,
			DISC_AMT,
			DISC_PCT,
			TND_AMT,
			ORD_NBR,
			CSH_FLO_TYP,
			PYMT_TYP,
			ACT_F,
			PAID_DT,
			CRT_NBR,
			INV_NBR,
			EXP_NBR,
			POS_ID
FROM RTL.CSH_REG_COPY

	*/
?>