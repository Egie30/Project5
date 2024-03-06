<?php

require_once "framework/functions/default.php";
require_once "framework/database/connect.php";
setlocale(LC_ALL, 'id_ID');

$template = "";
$paper = "A4";
$orientation = "portrait";
$orderNumber = mysql_escape_string($_REQUEST['ORD_NBR']);


$query = "SELECT
    HED.ORD_TS,
    HED.ORD_TTL,
    HED.TOT_AMT,
    HED.TAX_AMT,
    HED.TOT_REM,
    HED.FEE_MISC,
    COALESCE(NULLIF(PPL.NAME, ''), 'Tunai') AS PERSON,
    COM.NAME AS COMPANY,
    CHB.NAME AS CREATIVEHUB,
    CONCAT(CHB.ADDRESS,' ',CIT.CITY_NM) AS ALAMAT
FROM CMP.RTL_ORD_HEAD HED
	LEFT OUTER JOIN CMP.RTL_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
	LEFT OUTER JOIN CMP.COMPANY CHB ON HED.CHB_CO_NBR=CHB.CO_NBR
	LEFT OUTER JOIN CMP.CITY CIT ON CHB.CITY_ID=CIT.CITY_ID 
WHERE HED.ORD_NBR=" . $orderNumber . " AND HED.DEL_NBR=0";
$data = mysql_fetch_assoc(mysql_query($query));

if ($data) {
    $query = "SELECT *
              FROM CMP.RTL_ORD_DET DET
              LEFT OUTER JOIN CMP.RTL_ORD_TYP TYP ON TYP.RTL_ORD_TYP = DET.ORD_TYP
              LEFT OUTER JOIN CMP.RTL_ORD_TYP_CAT CAT ON TYP.CAT_ID = CAT.CAT_ID
              LEFT OUTER JOIN CMP.WIFI_VCHR WIFI ON WIFI.REF_NBR = DET.ORD_DET_NBR
              WHERE DET.ORD_NBR=" . $orderNumber . " 
              AND DET.DEL_NBR=0 
              AND DET.ORD_DET_NBR_PAR IS NULL";
    $result = mysql_query($query);
    $count = mysql_num_rows($result);

    // Increment IVC_PRN_CNT
    $query = "UPDATE CMP.RTL_ORD_HEAD HED SET HED.IVC_PRN_CNT = HED.IVC_PRN_CNT + 1 
	WHERE HED.ORD_NBR=" . $orderNumber . " AND HED.DEL_NBR=0";
    mysql_query($query);

    // Payment query
    $query_payment = "SELECT SUM(PYM.TND_AMT) AS PAYMENT FROM CMP.RTL_ORD_PYMT PYM WHERE PYM.DEL_NBR=0 AND PYM.ORD_NBR=" . $orderNumber;
    $payment = mysql_fetch_assoc(mysql_query($query_payment))['PAYMENT'];

    // Bank
    $queryBnk = "SELECT 
		COM.BNK_ACCT_NM, 
		COM.BNK_ACCT_NBR, 
		COM.BNK_CO_NBR, 
		COM_BNK.NAME AS NAME_BNK 
	FROM CMP.COMPANY COM 
		LEFT JOIN CMP.COMPANY COM_BNK ON COM.BNK_CO_NBR = COM_BNK.CO_NBR
	WHERE COM.CO_NBR = 6284"; // Champion Creative Hub, CV
    $resultBnk = mysql_query($queryBnk);
    $rowBnk = mysql_fetch_array($resultBnk);

    $AccountName = $rowBnk['BNK_ACCT_NM'];
    $AccountBank = $rowBnk['NAME_BNK'];
    $AccountNbr = $rowBnk['BNK_ACCT_NBR'];
} else {
    // print_r($orderNumber);
    // var_dump($query);
    echo "Tidak ada data.";
    exit();
}
$logo = "img/creativehub/logo (Small).png";
try {
    ob_start();
    include "creativehub-invoice-pdf-layout.php";
    $template = ob_get_clean();
} catch (\Exception $ex) {
    ob_end_clean();
}


if (isset($_REQUEST['PAPER'])) {
    $paper = $_REQUEST["PAPER"];
}

if (isset($_REQUEST['orientation'])) {
    $orientation = $_REQUEST["orientation"];
}

if (get_magic_quotes_gpc()) {
    $template = stripslashes($template);
}

/* Set true to see red box around element */
const DEBUG_LAYOUT = false;
const DOMPDF_ENABLE_HTML5PARSER = true;

require_once "framework/dompdf/dompdf_config.inc.php";
$options = [];

$pdf = new DOMPDF();
$pdf->set_options($options);
$pdf->load_html($template);
$pdf->set_paper($paper, $orientation);
$pdf->render();
$pdf->stream("creativehub-invoice-" . $orderNumber . ".pdf", array("Attachment" => false, 'compress' => 0));

exit(0);