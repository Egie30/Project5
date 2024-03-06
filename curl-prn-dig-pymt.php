<?php
require_once "framework/database/connect.php";
$query_head              = "SELECT HED.* FROM CMP.PRN_DIG_ORD_HEAD HED 
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
												WHERE  HED.DEL_NBR = 0 AND DATE(HED.ORD_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_GLBL) 
												AND HED.TAX_APL_ID IN ('I', 'A') AND PAY.TND_AMT >= HED.TOT_AMT";
//AND HED.UPD_TS>'$DATE_OWN_CO_NBR
echo $query_head."<br/>"; 
$result_head         = mysql_query($query_head);

$url = "http://champion.id/nestor/prn-dig-pymt-post.php";

$curlHandle = curl_init();
$jumlah=0;
$jumlah2=0;
while ($rowshead = mysql_fetch_array($result_head)):
$jumlah2=$jumlah2+1;
	$curl_data      	= "";
    $HED_ORD_NBR        = $rowshead['ORD_NBR'];
	$PYMT_OWN_CO_NBR     = $CoNbrDef;
	
	$query_pymt	= "SELECT PYMT.* FROM CMP.PRN_DIG_ORD_PYMT PYMT WHERE PYMT.ORD_NBR=$HED_ORD_NBR";
	echo "$jumlah2 ".$query_pymt."<br/>";
	$result_pymt= mysql_query($query_pymt);
	while ($rowspymt = mysql_fetch_array($result_pymt)):
		$PYMT_NBR		= $rowspymt['PYMT_NBR'];
		$ORD_NBR		= $rowspymt['ORD_NBR'];
		$PYMT_TYP		= $rowspymt['PYMT_TYP'];
		$TND_AMT		= $rowspymt['TND_AMT'];
		$REF			= $rowspymt['REF'];
		$PYMT_SCHED_DTE = $rowspymt['PYMT_SCHED_DTE'];
		$BNK_CO_NBR		= $rowspymt['BNK_CO_NBR'];
		$VAL_NBR		= $rowspymt['VAL_NBR'];
		$DEL_NBR		= $rowspymt['DEL_NBR'];
		$CRT_NBR		= $rowspymt['CRT_NBR'];
		$CRT_TS			= $rowspymt['CRT_TS'];
		$PYMT_OWN_CO_NBR = $CoNbrDef;
	endwhile;

	if (($PYMT_NBR!=0)||($PYMT_NBR!="")){
		$jumlah=$jumlah+1;
		$curl_data   = "PYMT_NBR=$PYMT_NBR&ORD_NBR=$ORD_NBR&PYMT_TYP=$PYMT_TYP&TND_AMT=$TND_AMT&REF=$REF&PYMT_SCHED_DTE=$PYMT_SCHED_DTE&BNK_CO_NBR=$BNK_CO_NBR&VAL_NBR=$VAL_NBR&DEL_NBR=$DEL_NBR&CRT_NBR=$CRT_NBR&CRT_TS=$CRT_TS&OWN_CO_NBR=$PYMT_OWN_CO_NBR";
		echo "$jumlah ".$curl_data."<br/><br/>";
		curl_setopt($curlHandle, CURLOPT_URL, $url);
		curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $curl_data);
		curl_setopt($curlHandle, CURLOPT_HEADER, 0);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
		curl_setopt($curlHandle, CURLOPT_POST, 1);
		curl_exec($curlHandle);
	}
endwhile;
curl_close($curlHandle);

?>