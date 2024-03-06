<?php 

$_GET['POS_ID'] = 1;

require_once "framework/database/connect-cashier.php";

$transactionNumber	= 61643; //maksimum TRSC_NBR + 1
$QNumber			= 0;
$CoNbrDef			= 1002; // Champion Campus
$pymtType			= 'FL';
$tanggal			= '2017-05-01';
$personNumber		= 3;
$POSID				= 1;
$paymentType		= 'TRF';
$paymentDesc		= 'Transfer';

//mencari nota digital printing yang belum pernah di transaksi di kasir 

$query_list	= "SELECT 
	PAY.PYMT_NBR,
	PAY.ORD_NBR,
	HED.ORD_TS,
	COM.NAME,
	PAY.VAL_NBR,
	PAY.TND_AMT,
	PAY.CRT_TS,
	PAY.UPD_TS,
	HED.TOT_AMT
FROM 
(SELECT 
	PYMT.PYMT_NBR,
	PYMT.ORD_NBR,
	PYMT.VAL_NBR,
	PYMT.TND_AMT,
	PYMT.CRT_TS,
	PYMT.UPD_TS
FROM CMP.PRN_DIG_ORD_PYMT PYMT 
WHERE DATE(PYMT.CRT_TS) >= '".$tanggal."'
	AND DATE(PYMT.CRT_TS) <= CURRENT_DATE
	AND DEL_NBR = 0
GROUP BY PYMT.PYMT_NBR
) PAY 
LEFT JOIN (
	SELECT 
		CSH.RTL_BRC 
	FROM RTL.CSH_REG CSH
	WHERE DATE(CSH.CRT_TS) >= '".$tanggal."'
		AND CSH.CSH_FLO_TYP = 'FL'
) REG 
	ON PAY.ORD_NBR = REG.RTL_BRC
LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED 
	ON PAY.ORD_NBR = HED.ORD_NBR
LEFT JOIN CMP.COMPANY COM 
	ON COM.CO_NBR = HED.BUY_CO_NBR
WHERE REG.RTL_BRC IS NULL";

$result_list	= mysql_query($query_list, $rtl);

echo "<pre><br /><br />";
echo $query_list;

echo "<br /><br />";

while($row_list = mysql_fetch_array($result_list)) {

	$ordNbr		= $row_list['ORD_NBR'];
	$price		= $row_list['TND_AMT'];
	$payment	= $row_list['TND_AMT'];
	$time		= $row_list['UPD_TS'];
	
	$query  = "INSERT INTO RTL.CSH_REG(TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, RTL_PRC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, ACT_F, CRT_NBR, POS_ID,CRT_TS)
                        VALUES ('" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $ordNbr . "', 1, " . $price . ", NULL, NULL, " . $price . ", '".$pymtType."', 0, " . $personNumber . ", " . $POSID . ", '".$time."')";
        $result = mysql_query($query, $rtl);
        $registerNumber = mysql_insert_id($rtl);
		
		echo $query."<br /><br />";
		
        $query  = "INSERT INTO CSH.CSH_REG(REG_NBR, TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, CAT_DESC, CAT_SUB_DESC, RTL_PRC, NAME_DESC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, ACT_F, CSH_FLO_PART, CRT_NBR,CRT_TS)
                VALUES ('" . $registerNumber . "', '" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $ordNbr . "', 1, '" . mysql_escape_string($category) . "', '" . $categorySub . "', " . $price . ", '" . mysql_escape_string($name) . "', NULL, NULL, " . $price . ", '".$pymtType."', 0, 'A', " . $personNumber . ", '".$time."')";
        $result = mysql_query($query, $csh);
		
		echo $query."<br /><br />";
		
		$query="UPDATE CMP.PRN_DIG_ORD_PYMT SET VAL_NBR";
		$query.="=".$registerNumber." WHERE VAL_NBR IS NULL AND DEL_NBR=0 AND ORD_NBR=".$ordNbr." AND TND_AMT = ".$price." ";
		
		$result=mysql_query($query,$rtl);
		
		echo $query."<br /><br />";
		
		 $query  = "INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,ACT_F, TND_AMT,POS_ID,CRT_TS) VALUES 
                    ('" . $transactionNumber . "'," . $QNumber . ", " . $personNumber . ", " . $CoNbrDef . ",'PA','','" . $paymentType . "',0, " . $payment . ",'" . $POSID . "', '".$time."')";
            $result = mysql_query($query, $rtl);
            $registerNumber = mysql_insert_id($rtl);
	
			echo $query."<br /><br />";
			
            $query  = "INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,PYMT_DESC,TND_AMT,CSH_FLO_PART,ACT_F, CRT_TS) VALUES
                        (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'PA','','" . $paymentType . "','" . $paymentDesc . "'," . $payment . ",'C', 0, '".$time."')";
            $result = mysql_query($query, $csh);
			
			echo $query."<br /><br /> ======================================================= <br />";
			
	$transactionNumber++;
	//$QNumber++;
		
}





