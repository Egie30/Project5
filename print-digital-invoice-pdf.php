<?php
ini_set("include_path", '/home/sbsjewel/php:' . ini_get("include_path") ); // Let me here
require_once "framework/database/connect.php";
require_once "framework/functions/dotmatrix.php";
require_once "framework/dompdf/dompdf_config.inc.php";

$template 		= "";
$paper 			= "A4";
$orientation	= "portrait";
$Type 			= $_GET['TYPE'];
$salestype 		= $_GET['TYP'];
$orderNumber 	= $_GET['ORD_NBR'];

if($salestype == "EST"){
	$headtable 		= "CMP.PRN_DIG_ORD_HEAD_EST";
	$detailtable	= "CMP.PRN_DIG_ORD_DET_EST";
	$paymenttable	= "CMP.PRN_DIG_ORD_PYMT_EST";
}else{
	$headtable 		= "CMP.PRN_DIG_ORD_HEAD";
	$detailtable	= "CMP.PRN_DIG_ORD_DET";
	$paymenttable	= "CMP.PRN_DIG_ORD_PYMT";
}

$_GET['GROUP'] 	= 'ORD_NBR';

$Title		='invoice';
$FromCompany="From :";
$Customer	="Customer :";
$BillTo		="Bill to :";

$query = "SELECT HED.ORD_NBR,
	ORD_TS,
	BUY_PRSN_NBR,
	PPL.NAME AS NAME_PPL,
	COM.NAME AS NAME_CO,
	COM.ADDRESS AS ADDRESS_CO,
	COM.ZIP AS ZIP_CO,
	COM.PHONE AS PHONE_CO,
	COM.EMAIL AS EMAIL,
	BUY_CO_NBR,
	CNS_CO_NBR,
	BIL_CO_NBR,
	BILCOM.NAME AS BIL_COM,
	BILCOM.ADDRESS AS BIL_ADDRESS,
	BILCOM.ZIP AS BIL_ZIP,
	BILCOM.PHONE AS BIL_PHONE,
	REF_NBR,
	ORD_TTL,
	PRN_CO_NBR,
	PRNCOM.NAME AS PRN_COM,
	PRNCOM.ADDRESS AS PRN_ADDRESS,
	PRNCOM.ZIP AS PRN_ZIP,
	PRNCOM.PHONE AS PRN_PHONE,
	PRNCOM.BNK_ACCT_NM AS PRN_BNK_ACCT_NM,
	PRNCOM.BNK_ACCT_NBR AS PRN_BNK_ACCT_NBR,
	PRNCOM.BNK_CO_NBR AS PRN_BNK_CO_NBR,
	COM_BNK.NAME AS NAME_BNK, 
	FEE_MISC,
	TAX_APL_ID,
	TAX_AMT,
	TOT_AMT,
	PYMT_DOWN,
	PYMT_REM,
	VAL_PYMT_DOWN,
	VAL_PYMT_REM,
	TOT_REM,
	SPC_NTE,
	JOB_LEN_TOT,
	SUM(PYMT.TND_AMT) AS TOT_PYMT,
	HED.ACTG_TYP,
	CRT.NAME AS CRT_NAME,
	POS_DESC ";
	if($salestype == "EST") {
		$query .= ",BO_HEAD_DESC, BO_BODY_DESC, BO_FOOT_DESC";
	} 
	$query .="
FROM ". $headtable ." HED
	LEFT OUTER JOIN ". $paymenttable ." PYMT ON HED.ORD_NBR=PYMT.ORD_NBR
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
	INNER JOIN CMP.PEOPLE CRT ON CRT.PRSN_NBR=HED.CRT_NBR
	INNER JOIN CMP.POS_TYP TYP ON TYP.POS_TYP=CRT.POS_TYP
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
	LEFT OUTER JOIN CMP.COMPANY PRNCOM ON HED.PRN_CO_NBR=PRNCOM.CO_NBR
	LEFT OUTER JOIN CMP.COMPANY BILCOM ON HED.BIL_CO_NBR=BILCOM.CO_NBR
	LEFT OUTER JOIN CMP.COMPANY COM_BNK ON PRNCOM.BNK_CO_NBR=COM_BNK.CO_NBR
WHERE HED.ORD_NBR='" . $orderNumber . "' AND PYMT.DEL_NBR=0";

$result = mysql_query($query);
$row 	= mysql_fetch_array($result);

$OrdTtl 	= $row['ORD_TTL'];
$RefNbr 	= $row['REF_NBR'];
$ActgTyp 	= $row['ACTG_TYP'];

//Tergantung CoNbrPkp
if($row['ACTG_TYP']==1){
	$queryCo 	= "SELECT COM.NAME, COM.ADDRESS, COM.ZIP, COM.EMAIL, COM.PHONE, COM.BNK_ACCT_NM, COM.BNK_ACCT_NBR, COM.BNK_CO_NBR, COM_BNK.NAME AS NAME_BNK 
					FROM CMP.COMPANY COM 
					LEFT JOIN CMP.COMPANY COM_BNK ON COM.BNK_CO_NBR = COM_BNK.CO_NBR
					WHERE COM.CO_NBR = $CoNbrPkp";
	$resultCo 	= mysql_query($queryCo);
	$rowCo 		= mysql_fetch_array($resultCo);

	$PrnName 	= $rowCo['NAME'];
	$PrnAddress = $rowCo['ADDRESS'];
	$PrnZip 	= $rowCo['ZIP'];
	$PrnPhone 	= $rowCo['PHONE'];;//$rowCo['PHONE'];
	$PrnEmail	= $rowCo['EMAIL'];

	$AccountName= $rowCo['BNK_ACCT_NM'];
	$AccountBank= $rowCo['NAME_BNK'];
	$AccountNbr = $rowCo['BNK_ACCT_NBR'];
} else {
	$PrnName 	= $row['PRN_COM'];
	$PrnAddress = $row['PRN_ADDRESS'];
	$PrnZip 	= $row['PRN_ZIP'];
	$PrnPhone 	= $row['PRN_PHONE'];

	$AccountName= "";
	$AccountBank= "";
	$AccountNbr = "";
}

if($row['ACTG_TYP']==2){
	$queryBnk 	= "SELECT COM.BNK_ACCT_NM, COM.BNK_ACCT_NBR, COM.BNK_CO_NBR, COM_BNK.NAME AS NAME_BNK 
					FROM CMP.COMPANY COM 
					LEFT JOIN CMP.COMPANY COM_BNK ON COM.BNK_CO_NBR = COM_BNK.CO_NBR
					WHERE COM.CO_NBR = 2997";
	$resultBnk 	= mysql_query($queryBnk);
	$rowBnk		= mysql_fetch_array($resultBnk);

	$AccountName= $rowBnk['BNK_ACCT_NM'];
	$AccountBank= $rowBnk['NAME_BNK'];
	$AccountNbr = $rowBnk['BNK_ACCT_NBR'];
}

$BuyName 	= $row['NAME_CO'];
$BuyAddress = $row['ADDRESS_CO'];
$BuyZip 	= $row['ZIP_CO'];
$BuyPhone 	= $row['PHONE_CO'];

$BilName 	= $row['BIL_COM'];
$BilAddress = $row['BIL_ADDRESS'];
$BilZip 	= $row['BIL_ZIP'];
$BilPhone 	= $row['BIL_PHONE'];

$SpcNte 	= $row['SPC_NTE'];
$RefNumber 	= $row['REF_NBR'];
$OrdDate 	= $row['ORD_TS'];

$TotPymt 	= $row['TOT_PYMT'];
$TotAmt 	= $row['TOT_AMT'];
$TotRem 	= $row['TOT_REM'];
$TaxAmt 	= $row['TAX_AMT'];
$FeeMisc 	= $row['FEE_MISC'];
$PymtRem 	= $row['PYMT_REM'];
$HeadLttr	= $row['BO_HEAD_DESC'];
$BodyLttr	= $row['BO_BODY_DESC'];
$FootLttr	= $row['BO_FOOT_DESC'];
$CrtName	= $row['CRT_NAME'];
$positionType= $row['POS_DESC'];
$LetterHead = $row['LETTER_HEAD'];
$BuyPrsnName    = $row['NAME_PPL'];

$NameOfr		= $_GET['NAME_OFR'];
$NameCom		= $_GET['NAME_COM'];
$TitleTop		= $_GET['TITLE_TOP'];
$TitleBottom		= $_GET['TITLE_BOTTOM'];
$_GET['TAX_AMT'] 	= $TaxAmt;

if($Type == "PDF"){
	if($row['ACTG_TYP']==1){
		$img ="img/print/pusatcampus.png";
	} else {
		$img ="img/print/campus.png";
	}
}
if($Type == "TEXT") {
	if($_GET["LETTER_HEAD_P"] == 'true'){
		$img ="img/print/printing2.png";
	}else if($_GET["LETTER_HEAD_C"] == 'true'){
		$img ="img/print/campus1.png";
	}else if($_GET["LETTER_HEAD"] == 'true'){
		$img ="img/print/default.png";
	}else if($_GET["LETTER_HEAD_SU"] == 'true'){
		$img ="img/print/default.png";
	}else if($_GET["LETTER_HEAD_SE"] == 'true'){
		$img ="img/print/default.png";
	}
}
	try {
		ob_start();
		if($Type == "TEXT") {
			include __DIR__ . DIRECTORY_SEPARATOR . "print-digital-invoice-print-offer.php";
		}else{
			include __DIR__ . DIRECTORY_SEPARATOR . "print-digital-invoice-print.php";
		}

		$template = ob_get_clean();
	} catch (\Exception $ex) {
		ob_end_clean();
	}


if (isset($_GET['PAPER'])) {
	$paper = $_GET["PAPER"];
}

if (isset($_GET['orientation'])) {
	$orientation = $_GET["orientation"];
}

if ( get_magic_quotes_gpc() ) {
	$template = stripslashes($template);
}

$pdf = new DOMPDF();
$pdf->load_html($template);
$pdf->set_paper($paper, $orientation);
$pdf->render();
$pdf->stream("print-digital-invoice-" . $orderNumber . ".pdf", array("Attachment" => false));

exit(0);