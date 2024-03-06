<?php
require_once "framework/database/connect.php";
require_once "framework/functions/dotmatrix.php";
require_once "framework/dompdf/dompdf_config.inc.php";

date_default_timezone_set("Asia/Jakarta");

$orderNumber = $_GET['ORD_NBR'];
$invoiceType = $_GET['IVC_TYP'];
$Type = $_GET['TYPE'];

$template = "";
$paper = "A4";
$orientation = "portrait";

if($invoiceType=="SL"){
	$Title='nota sales';
	$FromCompany="From :";
	$ReceivingCompany="Bill to :";
}else if($invoiceType=="PO"){
	$Title='purchase order';
	$FromCompany="Vendor : ";
	$ReceivingCompany="Ship to : ";
	$BillTo		="Bill to :";
}else if($invoiceType=="XF"){
	$Title='nota mutasi';
	$FromCompany="Vendor : ";
	$ReceivingCompany="Ship to : ";
}else if($invoiceType=="CR"){
	$Title='nota koreksi';
	$FromCompany="Vendor : ";
	$ReceivingCompany="Ship to : ";
}else if($invoiceType=="RT"){
	$Title='nota retur';
	$FromCompany="Vendor : ";
	$ReceivingCompany="Ship to : ";
}else if($invoiceType=="RC"){
	$Title='nota pembelian';
	$FromCompany="Vendor : ";
	$ReceivingCompany="Ship to : ";
}

if($Type == "PDF")
{
	$img ="img/print/campus.png";
}

$querycom = "SELECT CMP.NAME,CMP.ADDRESS, CMP.EMAIL, CMP.ZIP, CMP.PHONE , CITY_NM
			 FROM CMP.PEOPLE PPL
			 LEFT OUTER JOIN CMP.COMPANY CMP ON PPL.CO_NBR=PPL.CO_NBR
			 LEFT OUTER JOIN CMP.CITY CTY ON CMP.CITY_ID=CTY.CITY_ID
			 WHERE PRSN_ID='".$_SESSION['userID']."'";
$resultcom = mysql_query($querycom);
$rowcom = mysql_fetch_array($resultcom);

$CoSlog = "If you can think it, we can print it.";
$CoName = $rowcom['NAME'];
$CoAddress = $rowcom['ADDRESS'];
$CoEmail = $rowcom['EMAIL'];
$CoPhone = $rowcom['PHONE'];
$CoCity = $rowcom['CITY_NM'];
$CoZip = $rowcom['ZIP'];
$CoMedia = "facebook.com/championprinting";

$query = "SELECT ORD_NBR,
				HED.SHP_CO_NBR,
				HED.RCV_CO_NBR, 
				DATE_FORMAT( HED.UPD_TS, '%d-%m-%Y' ) AS UPD_DT, 
				DATE_FORMAT(ORD_DTE,'%d %M %Y') AS ORD_TS, 
				FEE_MISC, 
				PYMT_DOWN, 
				PYMT_REM, 
				TOT_REM, 
				SHP.NAME AS SHP_NAME, 
				SHP.ADDRESS AS SHP_ADDRESS, 
				SHP.ZIP AS SHP_ZIP,
				SHPBNK.NAME AS SHP_BNK_NAME,
				SHP.BNK_ACCT_NBR AS SHP_BNK_ACCT_NBR,
				SHP.BNK_ACCT_NM AS SHP_BNK_ACCT_NM,
				RCV.ADDRESS AS RCV_ADDRESS, 
				RCV.NAME AS RCV_NAME,
				RCV.ZIP AS RCV_ZIP,
				RCVBNK.NAME AS RCV_BNK_NAME,
				RCV.BNK_ACCT_NM AS RCV_BNK_ACCT_NM,
				RCV.BNK_ACCT_NBR AS RCV_BNK_ACCT_NBR,				
				REF_NBR, 
				TOT_AMT,
				SPC_NTE,
				BILCOM.NAME AS BIL_COM,
				BILCOM.ADDRESS AS BIL_ADDRESS,
				BILCOM.ZIP AS BIL_ZIP,
				BILCOM.PHONE AS BIL_PHONE,
				SCTY.CITY_NM AS SCITY_NAME,
				RCTY.CITY_NM AS RCITY_NAME,
				SPRV.PROV_NM AS SPROV_NAME,
				RPRV.PROV_NM AS RPROV_NAME
			FROM RTL.RTL_STK_HEAD HED 
				LEFT OUTER JOIN CMP.COMPANY SHP ON HED.SHP_CO_NBR = SHP.CO_NBR
				LEFT OUTER JOIN CMP.COMPANY SHPBNK ON SHP.BNK_CO_NBR = SHPBNK.CO_NBR
				LEFT OUTER JOIN CMP.COMPANY RCV ON HED.RCV_CO_NBR = RCV.CO_NBR
				LEFT OUTER JOIN CMP.COMPANY RCVBNK ON RCV.BNK_CO_NBR = RCVBNK.CO_NBR
				LEFT OUTER JOIN CMP.COMPANY BILCOM ON HED.BIL_CO_NBR = BILCOM.CO_NBR
				LEFT OUTER JOIN CMP.CITY SCTY ON SHP.CITY_ID = SCTY.CITY_ID
				LEFT OUTER JOIN CMP.CITY RCTY ON RCV.CITY_ID = RCTY.CITY_ID
				LEFT OUTER JOIN CMP.PROV SPRV ON SCTY.PROV_ID = SPRV.PROV_ID
				LEFT OUTER JOIN CMP.PROV RPRV ON RCTY.PROV_ID = RPRV.PROV_ID
			WHERE HED.ORD_NBR ='" . $orderNumber . "'";
$result = mysql_query($query);
$row = mysql_fetch_array($result);

$ShpCoNbr 	= $row['SHP_CO_NBR'];
$ShpName 	= $row['SHP_NAME'];
$ShpAddress = $row['SHP_ADDRESS'];
$ShpZip 	= $row['SHP_ZIP'];
$ShpCity 	= $row['SCITY_NAME'];
$ShpProv 	= $row['SPROV_NAME'];
$ShpBank 	= $row['SHP_BNK_NAME'];
$ShpAcctNm	= $row['SHP_BNK_ACCT_NM'];
$ShpAcctNbr	= $row['SHP_BNK_ACCT_NBR'];

$BilName 	= $row['BIL_COM'];
$BilAddress = $row['BIL_ADDRESS'];
$BilZip 	= $row['BIL_ZIP'];
$BilPhone 	= $row['BIL_PHONE'];

$RcvCoNbr 	= $row['RCV_CO_NBR'];
$RcvAddress = $row['RCV_ADDRESS'];
$RcvName 	= $row['RCV_NAME'];
$RcvZip 	= $row['RCV_ZIP'];
$RcvCity 	= $row['RCITY_NAME'];
$RcvProv 	= $row['RPROV_NAME'];
$RcvBank 	= $row['RCV_BNK_NAME'];
$RcvAcctNm	= $row['RCV_BNK_ACCT_NM'];
$RcvAcctNbr	= $row['RCV_BNK_ACCT_NBR'];

$RefNumber 	= $row['REF_NBR'];
$OrdDate 	= $row['ORD_TS'];
$SpcNte 	= $row['SPC_NTE'];

if ($invoiceType) {
	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "retail-edit-print.php";
		$template = ob_get_clean();
	} catch (\Exception $ex) {
		ob_end_clean();
	}
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
$pdf->stream("retail-" . $Title . ".pdf", array("Attachment" => false));

exit(0);