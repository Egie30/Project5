<?php
require_once "framework/database/connect.php";
require_once "framework/functions/dotmatrix.php";
require_once "framework/dompdf/dompdf_config.inc.php";
require_once "framework/functions/default.php";

date_default_timezone_set("Asia/Jakarta");

$TrnspNbr		= $_GET['TRNSP_NBR'];
$template		= "";
$paper			= "A5";
$orientation	= "portrait";

//Increment print count
$query="UPDATE CMP.TRNSP_HEAD SET SLP_PRN_CNT=SLP_PRN_CNT+1 WHERE TRNSP_NBR=".$TrnspNbr;
$resultb=mysql_query($query);

$queryActg 	= "SELECT ACTG_TYP FROM CMP.TRNSP_HEAD  WHERE TRNSP_NBR = '".$TrnspNbr."'";
$resultActg = mysql_query($queryActg);
$rowActg 	= mysql_fetch_array($resultActg);
	
if($rowActg['ACTG_TYP']==1){
	$query 	= "SELECT 
		NAME,ADDRESS,ZIP,PHONE,CITY_NM,EMAIL 
	FROM CMP.COMPANY COM 
		LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID 
	WHERE CO_NBR = $CoNbrPkp";
} else {
	$query 	= "SELECT NAME,ADDRESS,ZIP,PHONE,CITY_NM,EMAIL 
	FROM CMP.COMPANY COM 
		LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID 
	WHERE CO_NBR = $CoNbrDef";
}
$result=mysql_query($query);
$CmpDef=mysql_fetch_array($result);

$shipperName	= $CmpDef['NAME'];
$shipperAddress	= $CmpDef['ADDRESS'];
$shipperCity	= $CmpDef['CITY_NM'];
$shipperZip		= $CmpDef['ZIP'];
$shipperPhone	= $CmpDef['PHONE'];
$shipperEmail 	= $CmpDef['EMAIL'];

$query="SELECT 
	TRNSP_NBR,
	THD.ORD_NBR,
	DATE_FORMAT(TRNSP_TS,'%d-%m-%Y') AS TRNSP_DT,
	TRNSP_TS,
	DATE_FORMAT(ORD_TS,'%d-%m-%Y') AS ORD_DT,
	ORD_TS,
	STT.TRNSP_STT_ID,
	STT.TRNSP_STT_DESC,
	BUY_PRSN_NBR,
	PPL.NAME AS NAME_PPL,
	COM.NAME AS NAME_COM,
	RCV_CO_NBR,
	THD.REF_NBR,
	THD.ORD_TTL,
	THD.DUE_TS,
	THD.SPC_NTE,
	SLP_PRN_CNT,
	OHD.IVC_PRN_CNT,
	THD.TRNSP_DESC
FROM CMP.TRNSP_HEAD THD
	INNER JOIN CMP.TRNSP_STT STT ON THD.TRNSP_STT_ID=STT.TRNSP_STT_ID
	LEFT OUTER JOIN CMP.PRN_DIG_ORD_HEAD OHD ON THD.ORD_NBR=OHD.ORD_NBR
	LEFT OUTER JOIN CMP.PEOPLE PPL ON OHD.BUY_PRSN_NBR=PPL.PRSN_NBR
	LEFT OUTER JOIN CMP.COMPANY COM ON THD.RCV_CO_NBR=COM.CO_NBR
	LEFT OUTER JOIN CDW.PRN_DIG_TOP_CUST TOP ON OHD.BUY_CO_NBR=TOP.NBR
WHERE TRNSP_NBR=".$TrnspNbr;
$result = mysql_query($query);
$row = mysql_fetch_array($result);

$printTransCount	= leadZero($row['SLP_PRN_CNT'],2);
$transportDate 		= $row['TRNSP_DT'];
$transportStatus 	= $row['TRNSP_STT_ID'];
$transportDesc	 	= $row['TRNSP_DESC'];

$orderTitle			= $row['ORD_TTL'];
$orderNumber 		= $row['ORD_NBR'];
$orderDate 			= $row['ORD_DT'];
$printorderCount 	= leadZero($row['IVC_PRN_CNT'],2);

$dueDate		= parseDateShort($row['DUE_TS']);
$dueHour		= parseHour($row['DUE_TS']);
$dueMinute		= parseMinute($row['DUE_TS']);

if(trim($row['NAME_PPL']." ".$row['NAME_COM'])==""){
	$name="Tunai";
}else{
	$name=trim($row['NAME_PPL']." ".$row['NAME_COM']);
}


$orderStatus 	= $row['ORD_STT_DESC'];
$totalRemain 	= $row['TOT_REM'];
$totalAmount 	= $row['TOT_AMT'];
$RcvPeople		= $row['NAME_PPL'];
$RcvCompany		= $row['NAME_COM'];
$RcvCoNbr		= $row['RCV_CO_NBR'];
$RcvAddress		= $row['RCV_ADDRESS'];

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "transport-edit-print-pdf-print.php";
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
$pdf->stream("transport-" . $TrnspNbr . ".pdf", array("Attachment" => false));

exit(0);