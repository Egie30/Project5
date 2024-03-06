<?php
//$link=mysql_connect("localhost", "root", "");
require_once "framework/database/connect.php";

$queryNbr   = "SELECT CO_NBR_DEF FROM NST.PARAM_LOC";
//echo $queryNbr;
$resultNbr  = mysql_query($queryNbr);
$dataNbr    = mysql_fetch_array($resultNbr);
$CoNbrDef   = $dataNbr['CO_NBR_DEF'];
//echo $CoNbrDef;
//echo "<br/>-------------------------------------------------<br/>";

$queryDate           = "SELECT * FROM CDW.UPD_LAST";
//echo $queryDate."<br/>";
$resultDate          = mysql_query($queryDate);
$dataDate            = mysql_fetch_array($resultDate);
$ID_PRN_DIG_DSH_BRD  = $dataDate['PRN_DIG_DSH_BRD'];
$ID_PRN_TAX_APL      = $dataDate['PRN_TAX_APL'];
$DATE_OWN_CO_NBR     = $dataDate['RTL_TAX_APL'];
//echo $ID_PRN_DIG_DSH_BRD."==".$ID_PRN_TAX_APL."==".$DATE_OWN_CO_NBR;
//echo "<br/>-------------------------------------------------<br/>";

$waktu               = date("Y-m-d H:i:s");
mysql_query("UPDATE CDW.UPD_LAST SET RTL_TAX_APL='$waktu', PRN_DIG_DSH_BRD='$ID_PRN_DIG_DSH_BRD', PRN_TAX_APL='$ID_PRN_TAX_APL' 
			WHERE PRN_DIG_DSH_BRD='$ID_PRN_DIG_DSH_BRD'");

//echo "<br/>-------------------------------------------------<br/>";
//ambil data local HEAD
$query1head         = "SELECT * FROM RTL.RTL_STK_HEAD WHERE TAX_APL_ID IN ('A','I') AND UPD_TS>'$DATE_OWN_CO_NBR'";
//echo $query1head;
$result1head			= mysql_query($query1head);
$url = "http://champion.id/nestor/retail-stock-post.php";

$curlHandle = curl_init();
while ($rowshead = mysql_fetch_array($result1head)):
	$curl_data      = "";
    $HEAD_ORD_NBR        = $rowshead['ORD_NBR'];
	$HEAD_ORD_DTE        = $rowshead['ORD_DTE'];
	$HEAD_RCV_CO_NBR     = $rowshead['RCV_CO_NBR'];
	$HEAD_REF_NBR        = $rowshead['REF_NBR'];
	$HEAD_SHP_CO_NBR     = $rowshead['SHP_CO_NBR'];
	$HEAD_IVC_TYP        = $rowshead['IVC_TYP'];
	$HEAD_FEE_MISC       = $rowshead['FEE_MISC'];
	$HEAD_DISC_PCT       = $rowshead['DISC_PCT'];
	$HEAD_DISC_AMT       = $rowshead['DISC_AMT'];
	$HEAD_TOT_AMT        = $rowshead['TOT_AMT'];
	$HEAD_PYMT_DOWN      = $rowshead['PYMT_DOWN'];
	$HEAD_PYMT_REM       = $rowshead['PYMT_REM'];
	$HEAD_TOT_REM        = $rowshead['TOT_REM'];
	$HEAD_DL_TS          = $rowshead['DL_TS'];
	$HEAD_SPC_NTE        = $rowshead['SPC_NTE'];
	$HEAD_IVC_PRN_CNT    = $rowshead['IVC_PRN_CNT'];
	$HEAD_DEL_F          = $rowshead['DEL_F'];
	$HEAD_CRT_TS         = $rowshead['CRT_TS'];
	$HEAD_CRT_NBR        = $rowshead['CRT_NBR'];
	$HEAD_UPD_TS         = $rowshead['UPD_TS'];
	$HEAD_UPD_NBR        = $rowshead['UPD_NBR'];
	$HEAD_TAX_APL_ID     = $rowshead['TAX_APL_ID'];
	$HEAD_TAX_AMT        = $rowshead['TAX_AMT'];
	$HEAD_SLS_PRSN_NBR   = $rowshead['SLS_PRSN_NBR'];
	$HEAD_SLS_TYP_ID     = $rowshead['SLS_TYP_ID'];
	$HEAD_VAL_PYMT_DOWN  = $rowshead['VAL_PYMT_DOWN'];
	$HEAD_VAL_PYMT_REM   = $rowshead['VAL_PYMT_REM'];
	$HEAD_CAT_SUB_NBR    = $rowshead['CAT_SUB_NBR'];
	$HEAD_OWN_CO_NBR     = $CoNbrDef;
	
	if (($HEAD_ORD_NBR!=0)||($HEAD_ORD_NBR!="")){
		$curl_data   = "HEAD_ORD_NBR=$HEAD_ORD_NBR&HEAD_ORD_DTE=$HEAD_ORD_DTE&HEAD_RCV_CO_NBR=$HEAD_RCV_CO_NBR&HEAD_REF_NBR=$HEAD_REF_NBR&HEAD_SHP_CO_NBR=$HEAD_SHP_CO_NBR&HEAD_IVC_TYP=$HEAD_IVC_TYP&HEAD_FEE_MISC=$HEAD_FEE_MISC&HEAD_DISC_PCT=$HEAD_DISC_PCT&HEAD_DISC_AMT=$HEAD_DISC_AMT&HEAD_TOT_AMT=$HEAD_TOT_AMT&HEAD_PYMT_DOWN=$HEAD_PYMT_DOWN&HEAD_PYMT_REM=$HEAD_PYMT_REM&HEAD_TOT_REM=$HEAD_TOT_REM&HEAD_DL_TS=$HEAD_DL_TS&HEAD_SPC_NTE=$HEAD_SPC_NTE&HEAD_IVC_PRN_CNT=$HEAD_IVC_PRN_CNT&HEAD_DEL_F=$HEAD_DEL_F&HEAD_CRT_TS=$HEAD_CRT_TS&HEAD_CRT_NBR=$HEAD_CRT_NBR&HEAD_UPD_TS=$HEAD_UPD_TS&HEAD_UPD_NBR=$HEAD_UPD_NBR&HEAD_TAX_APL_ID=$HEAD_TAX_APL_ID&HEAD_TAX_AMT=$HEAD_TAX_AMT&HEAD_SLS_PRSN_NBR=$HEAD_SLS_PRSN_NBR&HEAD_SLS_TYP_ID=$HEAD_SLS_TYP_ID&HEAD_VAL_PYMT_DOWN=$HEAD_VAL_PYMT_DOWN&HEAD_VAL_PYMT_REM=$HEAD_VAL_PYMT_REM&HEAD_CAT_SUB_NBR=$HEAD_CAT_SUB_NBR&HEAD_OWN_CO_NBR=$HEAD_OWN_CO_NBR";
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
$query1det		= "SELECT * FROM RTL.RTL_STK_DET DET LEFT JOIN RTL.RTL_STK_HEAD HEAD ON DET.ORD_NBR=HEAD.ORD_NBR 
					WHERE HEAD.TAX_APL_ID IN ('A','I') AND DET.UPD_TS>'$DATE_OWN_CO_NBR'";
//echo $query1det;
$result1det		= mysql_query($query1det);
$curlHandleDet = curl_init();
while ($rowsdet = mysql_fetch_array($result1det)):
	$curl_data_det="";
    $ORD_DET_NBR          = $rowsdet['ORD_DET_NBR'];
	$ORD_NBR              = $rowsdet['ORD_NBR'];
	$INV_NBR              = $rowsdet['INV_NBR'];
	$INV_DESC             = $rowsdet['INV_DESC'];
	$ORD_Q                = $rowsdet['ORD_Q'];
	$ORD_X                = $rowsdet['ORD_X'];
	$ORD_Y                = $rowsdet['ORD_Y'];
	$ORD_Z                = $rowsdet['ORD_Z'];
	$INV_PRC              = $rowsdet['INV_PRC'];
	$FEE_MISC             = $rowsdet['FEE_MISC'];
	$DISC_PCT             = $rowsdet['DISC_PCT'];
	$DISC_AMT             = $rowsdet['DISC_AMT'];
	$TOT_SUB              = $rowsdet['TOT_SUB'];
	$CRT_TS               = $rowsdet['CRT_TS'];
	$CRT_NBR              = $rowsdet['CRT_NBR'];
	$UPD_TS               = $rowsdet['UPD_TS'];
	$UPD_NBR              = $rowsdet['UPD_NBR'];
	$OWN_CO_NBR           = $CoNbrDef;
	
	if (($ORD_DET_NBR!=0)||($ORD_DET_NBR!="")){
		$curl_data_det="ORD_DET_NBR=$ORD_DET_NBR&ORD_NBR=$ORD_NBR&INV_NBR=$INV_NBR&INV_DESC=$INV_DESC&ORD_Q=$ORD_Q&ORD_X=$ORD_X&ORD_Y=$ORD_Y&ORD_Z=$ORD_Z&INV_PRC=$INV_PRC&FEE_MISC=$FEE_MISC&DISC_PCT=$DISC_PCT&DISC_AMT=$DISC_AMT&TOT_SUB=$TOT_SUB&CRT_TS=$CRT_TS&CRT_NBR=$CRT_NBR&UPD_TS=$UPD_TS&UPD_NBR=$UPD_NBR&OWN_CO_NBR=$OWN_CO_NBR";
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