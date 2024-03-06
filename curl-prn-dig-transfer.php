<?php
require_once "framework/database/connect.php";
//$link=mysql_connect("localhost", "root", "");
//mysql_select_db("champion_PTCDW");

$queryDate           = mysql_query("SELECT UPD.* FROM CDW.UPD_LAST UPD");
$dataDate            = mysql_fetch_array($queryDate);
$ID_PRN_DIG_DSH_BRD  = $dataDate['PRN_DIG_DSH_BRD'];
$ID_RTL_TAX_APL      = $dataDate['RTL_TAX_APL'];
$DATE_OWN_CO_NBR     = $dataDate['PRN_TAX_APL'];

$waktu               = date("Y-m-d H:i:s");
mysql_query("UPDATE CDW.UPD_LAST UPD SET UPD.PRN_TAX_APL='$waktu', UPD.PRN_DIG_DSH_BRD='$ID_PRN_DIG_DSH_BRD', UPD.RTL_TAX_APL='$ID_RTL_TAX_APL' 
			WHERE UPD.PRN_DIG_DSH_BRD='$ID_PRN_DIG_DSH_BRD'");
//mysql_close();

//ambil data local HEAD
//$query1head         = mysql_query("SELECT * FROM PRN_DIG_ORD_HEAD HEAD WHERE HEAD.TAX_APL_ID IN ('A','I') AND HEAD.UPD_TS>'$DATE_OWN_CO_NBR'");
$query              = "SELECT HED.* FROM CMP.PRN_DIG_ORD_HEAD HED 
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
												AND HED.TAX_APL_ID IN ('I', 'A') AND PAY.TND_AMT >= HED.TOT_AMT AND HED.UPD_TS>'$DATE_OWN_CO_NBR'";
$result1head         = mysql_query($query);
//echo "<pre>".$query;
$url = "http://champion.id/nestor/prn-dig-post.php";

$curlHandle = curl_init();
while ($rowshead = mysql_fetch_array($result1head)):
	$curl_data      = "";
    $HED_ORD_NBR        = $rowshead['ORD_NBR'];
	$HED_ORD_TS         = $rowshead['ORD_TS'];
	$HED_ORD_STT_ID     = $rowshead['ORD_STT_ID'];
	$HED_BUY_PRSN_NBR   = $rowshead['BUY_PRSN_NBR'];
	$HED_BUY_CO_NBR     = $rowshead['BUY_CO_NBR'];
	$HED_CNS_CO_NBR     = $rowshead['CNS_CO_NBR'];
	$HED_BIL_CO_NBR     = $rowshead['BIL_CO_NBR'];
	$HED_REF_NBR        = $rowshead['REF_NBR'];
	$HED_ORD_TTL        = $rowshead['ORD_TTL'];
	$HED_DUE_TS         = $rowshead['DUE_TS'];
	$HED_PRN_CO_NBR     = $rowshead['PRN_CO_NBR'];
	$HED_SLS_PRSN_NBR   = $rowshead['SLS_PRSN_NBR'];
	$HED_FEE_MISC       = $rowshead['FEE_MISC'];
	$HED_TAX_APL_ID     = $rowshead['TAX_APL_ID'];
	$HED_TAX_AMT        = $rowshead['TAX_AMT'];
	$HED_TOT_AMT        = $rowshead['TOT_AMT'];
	$HED_PYMT_DOWN      = $rowshead['PYMT_DOWN'];
	$HED_PYMT_REM       = $rowshead['PYMT_REM'];
	$HED_TOT_REM        = $rowshead['TOT_REM'];
	$HED_VAL_PYMT_DOWN  = $rowshead['VAL_PYMT_DOWN'];
	$HED_VAL_PYMT_REM   = $rowshead['VAL_PYMT_REM'];
	$HED_CMP_TS         = $rowshead['CMP_TS'];
	$HED_PU_TS          = $rowshead['PU_TS'];
	$HED_PAY_DUE_DT     = $rowshead['PAY_DUE_DT'];
	$HED_SPC_NTE        = $rowshead['SPC_NTE'];
	$HED_IVC_PRN_CNT    = $rowshead['IVC_PRN_CNT'];
	$HED_JOB_LEN_TOT    = $rowshead['JOB_LEN_TOT'];
	$HED_DL_CNT         = $rowshead['DL_CNT'];
	$HED_PU_CNT         = $rowshead['PU_CNT'];
	$HED_NS_CNT         = $rowshead['NS_CNT'];
	$HED_DEL_NBR        = $rowshead['DEL_NBR'];
	$HED_CRT_TS         = $rowshead['CRT_TS'];
	$HED_CRT_NBR        = $rowshead['CRT_NBR'];
	$HED_UPD_TS         = $rowshead['UPD_TS'];
	$HED_UPD_NBR        = $rowshead['UPD_NBR'];
	$HED_OWN_CO_NBR     = $CoNbrDef;
	
	if (($HED_ORD_NBR!=0)||($HED_ORD_NBR!="")){
		$curl_data   = "HED_ORD_NBR=$HED_ORD_NBR&HED_ORD_TS=$HED_ORD_TS&HED_ORD_STT_ID=$HED_ORD_STT_ID&HED_BUY_PRSN_NBR=$HED_BUY_PRSN_NBR&HED_BUY_CO_NBR=$HED_BUY_CO_NBR&HED_CNS_CO_NBR=$HED_CNS_CO_NBR&HED_BIL_CO_NBR=$HED_BIL_CO_NBR&HED_REF_NBR=$HED_REF_NBR&HED_ORD_TTL=$HED_ORD_TTL&HED_DUE_TS=$HED_DUE_TS&HED_PRN_CO_NBR=$HED_PRN_CO_NBR&HED_SLS_PRSN_NBR=$HED_SLS_PRSN_NBR&HED_FEE_MISC=$HED_FEE_MISC&HED_TAX_APL_ID=$HED_TAX_APL_ID&HED_TAX_AMT=$HED_TAX_AMT&HED_TOT_AMT=$HED_TOT_AMT&HED_PYMT_DOWN=$HED_PYMT_DOWN&HED_PYMT_REM=$HED_PYMT_REM&HED_TOT_REM=$HED_TOT_REM&HED_VAL_PYMT_DOWN=$HED_VAL_PYMT_DOWN&HED_VAL_PYMT_REM=$HED_VAL_PYMT_REM&HED_CMP_TS=$HED_CMP_TS&HED_PU_TS=$HED_PU_TS&HED_PAY_DUE_DT=$HED_PAY_DUE_DT&HED_SPC_NTE=$HED_SPC_NTE&HED_IVC_PRN_CNT=$HED_IVC_PRN_CNT&HED_JOB_LEN_TOT=$HED_JOB_LEN_TOT&HED_DL_CNT=$HED_DL_CNT&HED_PU_CNT=$HED_PU_CNT&HED_NS_CNT=$HED_NS_CNT&HED_DEL_NBR=$HED_DEL_NBR&HED_CRT_TS=$HED_CRT_TS&HED_CRT_NBR=$HED_CRT_NBR&HED_UPD_TS=$HED_UPD_TS&HED_UPD_NBR=$HED_UPD_NBR&HED_OWN_CO_NBR=$HED_OWN_CO_NBR";
		//echo $curl_data."<br/><br/>";
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

//ambil data local DET

$query="SELECT DET.* FROM CMP.PRN_DIG_ORD_DET DET 
						LEFT JOIN PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
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
						WHERE HED.DEL_NBR = 0 AND DATE(HED.ORD_TS) >= (SELECT BEG_ACCTG FROM NST.PARAM_GLBL) 
						AND HED.TAX_APL_ID IN ('I', 'A') AND PAY.TND_AMT >= HED.TOT_AMT AND HED.UPD_TS>'$DATE_OWN_CO_NBR'";
$query1det = mysql_query($query);
//echo "<pre>".$query;
$curlHandleDet = curl_init();
while ($rowsdet = mysql_fetch_array($query1det)):
	$curl_data_det="";
    $ORD_DET_NBR          = $rowsdet['ORD_DET_NBR'];
	$ORD_NBR              = $rowsdet['ORD_NBR'];
	$ORD_DET_NBR_PAR      = $rowsdet['ORD_DET_NBR_PAR'];
	$PRN_DIG_TYP          = $rowsdet['PRN_DIG_TYP'];
	$PRN_DIG_PRC          = $rowsdet['PRN_DIG_PRC'];
	$ORD_Q                = $rowsdet['ORD_Q'];
	$DET_TTL              = $rowsdet['DET_TTL'];
	$FIL_LOC              = $rowsdet['FIL_LOC'];
	$FIL_ATT              = $rowsdet['FIL_ATT'];
	$PRN_LEN              = $rowsdet['PRN_LEN'];
	$PRN_WID              = $rowsdet['PRN_WID'];
	$FIN_BDR_TYP          = $rowsdet['FIN_BDR_TYP'];
	$FIN_BDR_WID          = $rowsdet['FIN_BDR_WID'];
	$FIN_LOP_WID          = $rowsdet['FIN_LOP_WID'];
	$GRM_CNT_TOP          = $rowsdet['GRM_CNT_TOP'];
	$GRM_CNT_BTM          = $rowsdet['GRM_CNT_BTM'];
	$GRM_CNT_LFT          = $rowsdet['GRM_CNT_LFT'];
	$GRM_CNT_RGT          = $rowsdet['GRM_CNT_RGT'];
	$PRFO_F               = $rowsdet['PRFO_F'];
	$BK_TO_BK_F           = $rowsdet['BK_TO_BK_F'];
	$ROLLED_F             = $rowsdet['ROLLED_F'];
	$FEE_MISC             = $rowsdet['FEE_MISC'];
	$FAIL_CNT             = $rowsdet['FAIL_CNT'];
	$DISC_PCT             = $rowsdet['DISC_PCT'];
	$DISC_AMT             = $rowsdet['DISC_AMT'];
	$VAL_ADD_AMT          = $rowsdet['VAL_ADD_AMT'];
	$TOT_SUB              = $rowsdet['TOT_SUB'];
	$JOB_LEN              = $rowsdet['JOB_LEN'];
	$PRN_CMP_Q            = $rowsdet['PRN_CMP_Q'];
	$FIN_CMP_Q            = $rowsdet['FIN_CMP_Q'];
	$HND_OFF_TYP          = $rowsdet['HND_OFF_TYP'];
	$HND_OFF_TS           = $rowsdet['HND_OFF_TS'];
	$HND_OFF_NBR          = $rowsdet['HND_OFF_NBR'];
	$SORT_BAY_ID          = $rowsdet['SORT_BAY_ID'];
	$SORT_BAY_TS          = $rowsdet['SORT_BAY_TS'];
	$SORT_BAY_NBR         = $rowsdet['SORT_BAY_NBR'];
	$DEL_NBR              = $rowsdet['DEL_NBR'];
	$CRT_TS               = $rowsdet['CRT_TS'];
	$CRT_NBR              = $rowsdet['CRT_NBR'];
	$UPD_TS               = $rowsdet['UPD_TS'];
	$UPD_NBR              = $rowsdet['UPD_NBR'];
	$OWN_CO_NBR           = $CoNbrDef;
	
	if (($ORD_DET_NBR!=0)||($ORD_DET_NBR!="")){
		$curl_data_det="ORD_DET_NBR=$ORD_DET_NBR&ORD_NBR=$ORD_NBR&ORD_DET_NBR_PAR=$ORD_DET_NBR_PAR&PRN_DIG_TYP=$PRN_DIG_TYP&PRN_DIG_PRC=$PRN_DIG_PRC&ORD_Q=$ORD_Q&DET_TTL=$DET_TTL&FIL_LOC=$FIL_LOC&FIL_ATT=$FIL_ATT&PRN_LEN=$PRN_LEN&PRN_WID=$PRN_WID&FIN_BDR_TYP=$FIN_BDR_TYP&FIN_BDR_WID=$FIN_BDR_WID&FIN_LOP_WID=$FIN_LOP_WID&GRM_CNT_TOP=$GRM_CNT_TOP&GRM_CNT_BTM=$GRM_CNT_BTM&GRM_CNT_LFT=$GRM_CNT_LFT&GRM_CNT_RGT=$GRM_CNT_RGT&PRFO_F=$PRFO_F&BK_TO_BK_F=$BK_TO_BK_F&ROLLED_F=$ROLLED_F&FEE_MISC=$FEE_MISC&FAIL_CNT=$FAIL_CNT&DISC_PCT=$DISC_PCT&DISC_AMT=$DISC_AMT&VAL_ADD_AMT=$VAL_ADD_AMT&TOT_SUB=$TOT_SUB&JOB_LEN=$JOB_LEN&PRN_CMP_Q=$PRN_CMP_Q&FIN_CMP_Q=$FIN_CMP_Q&HND_OFF_TYP=$HND_OFF_TYP&HND_OFF_TS=$HND_OFF_TS&HND_OFF_NBR=$HND_OFF_NBR&SORT_BAY_ID=$SORT_BAY_ID&SORT_BAY_TS=$SORT_BAY_TS&SORT_BAY_NBR=$SORT_BAY_NBR&DEL_NBR=$DEL_NBR&CRT_TS=$CRT_TS&CRT_NBR=$CRT_NBR&UPD_TS=$UPD_TS&UPD_NBR=$UPD_NBR&OWN_CO_NBR=$OWN_CO_NBR";
		//echo $curl_data_det."<br/><br/>";
		curl_setopt($curlHandleDet, CURLOPT_URL, $url);
		curl_setopt($curlHandleDet, CURLOPT_POSTFIELDS, $curl_data_det);
		curl_setopt($curlHandleDet, CURLOPT_HEADER, 0);
		curl_setopt($curlHandleDet, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlHandleDet, CURLOPT_TIMEOUT,30);
		curl_setopt($curlHandleDet, CURLOPT_POST, 1);
		curl_exec($curlHandleDet);
	}
	
endwhile;
curl_close($curlHandleDet);
?>