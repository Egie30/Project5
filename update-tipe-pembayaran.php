<?php
require_once "framework/database/connect-cashier.php";
require_once "framework/security/default.php";
require_once "framework/functions/default.php";
require_once "framework/functions/dotmatrix.php";

$csh = mysql_connect("192.168.1.11" , "root", "", true);

$transactionNumber	= 81759;
$QNumber			= 91;
$CoNbrDef			= 1002;
$personNumber		= 3;
$registerNumber		= 275875;

$beginDate			= '2019-05-01';
$endDate			= '2019-05-31';

$query_pymt	= "SELECT
	PYMT.PYMT_NBR,
	PYMT.TND_AMT,
	PYMT.CRT_TS,
	(CASE WHEN HED.BUY_CO_NBR IS NOT NULL THEN HED.BUY_CO_NBR ELSE 0 END) AS BUY_CO_NBR
FROM CMP.PRN_DIG_ORD_PYMT PYMT
JOIN CMP.PRN_DIG_ORD_HEAD HED 
	ON PYMT.ORD_NBR = HED.ORD_NBR
WHERE PYMT.CRT_TS >= '".$beginDate."'
	AND PYMT.CRT_TS <= '".$endDate."'
	AND PYMT.VAL_NBR IS NULL
	AND PYMT.DEL_NBR = 0
GROUP BY HED.BUY_CO_NBR
";

echo "<pre>".$query_pymt."<br />";

$result_pymt = mysql_query($query_pymt, $rtl);

while ($row_pymt = mysql_fetch_array($result_pymt)) {
	
	if($row_pymt['BUY_CO_NBR'] == 0) {
		$where	= " AND HED.BUY_CO_NBR IS NULL";
	}
	else {
		$where	= " AND HED.BUY_CO_NBR = ".$row_pymt['BUY_CO_NBR']." ";
	}
	
	$query_pymt_det	= "SELECT
		PYMT.PYMT_NBR,
		PYMT.ORD_NBR,
		PYMT.TND_AMT,
		PYMT.CRT_TS
	FROM CMP.PRN_DIG_ORD_PYMT PYMT
	JOIN CMP.PRN_DIG_ORD_HEAD HED 
		ON PYMT.ORD_NBR = HED.ORD_NBR
	WHERE PYMT.CRT_TS >= '".$beginDate."'
		AND PYMT.CRT_TS <= '".$endDate."'
		AND PYMT.VAL_NBR IS NULL
		AND PYMT.DEL_NBR = 0
		 ".$where." 
	GROUP BY PYMT.PYMT_NBR";

	
	echo "<pre>".$query_pymt_det."<br />";

	$result_pymt_det = mysql_query($query_pymt_det, $rtl);

	$total		= 0;
	
	while ($row_pymt_det = mysql_fetch_array($result_pymt_det)) {
	
		$barcode	= $row_pymt_det['ORD_NBR'];
		$price		= $row_pymt_det['TND_AMT'];
		$categoryDiscAmount	= null;
		$categoryDiscPct	= null;
		$netPrice			= $row_pymt_det['TND_AMT'];
		$inventoryNumber	= 0;
		$POSID				= 1;
		$category			= null;
		$categorySub		= null;
		$name				= null;
		
		$registerNumber++;
		
        $query  = "INSERT INTO RTL.CSH_REG(REG_NBR, TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, RTL_PRC, TND_AMT, CSH_FLO_TYP, CRT_NBR, POS_ID, CRT_TS, ACT_F)
                        VALUES (".$registerNumber.",'" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $barcode . "', 1, " . $price . "," . $netPrice . ", 'FL', " . $personNumber . ", " . $POSID . ", '".$endDate."' ,0)";
        $result = mysql_query($query, $rtl);
		
		echo $query."<br />";
		
		
        $query  = "INSERT INTO CSH.CSH_REG(REG_NBR, TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, RTL_PRC, TND_AMT, CSH_FLO_TYP, ACT_F, CSH_FLO_PART, CRT_NBR, CRT_TS)
                VALUES (" . $registerNumber . ", '" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $barcode . "', 1, " . $price . ", " . $netPrice . ", 'FL', 0, 'A', " . $personNumber . ", '".$endDate."' )";
		
		echo $query."<br />";
		
		$result = mysql_query($query, $csh);
		
		$total	+= $row_pymt_det['TND_AMT'];
	
	
		//update to null
        $query="UPDATE CMP.PRN_DIG_ORD_PYMT SET VAL_NBR = '" . $registerNumber . "', PYMT_TYP = 'TRF' WHERE DEL_NBR = 0 AND PYMT_NBR = ".$row_pymt_det['PYMT_NBR']." AND VAL_NBR IS NULL";
		
		$result = mysql_query($query, $rtl);
		
		echo $query."<br />";
	}
	
	$paymentType	= 'TRF';
	$paymentDesc	= 'Transfer';
	$payment		= $total;
	
	
	$registerNumber++;
		
	$query  = "INSERT INTO RTL.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,TND_AMT,POS_ID, CRT_TS, ACT_F)
	VALUES 
	(" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . ", " . $personNumber . ", " . $CoNbrDef . ",'PA','','" . $paymentType . "'," . $payment . ",'" . $POSID . "', '".$endDate."', 0)";
	$result = mysql_query($query, $rtl);
	
	echo $query."<br />";
	
	$query  = "INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,PYMT_DESC,TND_AMT,CSH_FLO_PART, CRT_TS, ACT_F) VALUES
    (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'PA','','" . $paymentType . "','" . $paymentDesc . "'," . $payment . ",'C', '".$endDate."', 0)";
    $result = mysql_query($query, $csh);
	
	echo $query."<br />";
	
	$QNumber++;
	$transactionNumber++;
	
}

	
?>