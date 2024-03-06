<?php

require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";
require_once "framework/phpExcel/Classes/PHPExcel.php";

$ReportType = mysql_escape_string($_GET['RPT_TYP']);
$Year = mysql_escape_string($_GET['YEAR']);
$Month = mysql_escape_string($_GET['MONTH']);
$buyCoNbr = mysql_escape_string($_GET['BUY_CO_NBR']);
$buyPrsnNbr = mysql_escape_string($_GET['BUY_PRSN_NBR']);
$title = 'Creativehub Receivables ';

$reports = array(
    'title'              => $title,
    'column'             => array('A', 'B', 'C', 'D', 'E', 'F'),
    'columnDimension'    => array(10, 10, 60, 15, 15, 15),
    'columnDimensionPdf' => array(1, 3, 8, 2, 2.5, 3),
    'titles'             => array(
        'No.',
        'Nota',
        'Nama',
        'Total',
        'Pembayaran',
        'Sisa',
    ),
    'styles'             => array(
        'A' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            )
        ),
        'B' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            )
        ),
        'C' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            )
        ),
        'D' => array(
            'alignment'    => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
        'E' => array(
            'alignment'    => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
        'F' => array(
            'alignment'    => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        )
    ),
    'data'               => array(),
    'total'              => array()
);

function dec($s)
{
    return html_entity_decode($s, ENT_QUOTES, "UTF-8");
}

$whereDte = $whereString = "";
if ($Month != '' && $Year != '') {
    $whereDte = " AND MONTH(ORD_TS)='" . $Month
        . "' AND YEAR(ORD_TS)='" . $Year . "' ";
}

if ($buyCoNbr != "") {
    $whereString = "BUY_CO_NBR=" . $buyCoNbr;
    if ($buyPrsnNbr != "") {
        $whereString .= " AND BUY_PRSN_NBR=" . $buyPrsnNbr;
    } else {
        $whereString .= " AND BUY_PRSN_NBR IS NULL";
    }
} elseif ($buyPrsnNbr != "") {
    $whereString = "BUY_PRSN_NBR=" . $buyPrsnNbr;
}
if (($buyPrsnNbr == "0") && ($buyCoNbr == "0")) {
    $whereString = "(BUY_CO_NBR IS NULL AND BUY_PRSN_NBR IS NULL)";
}

if (($buyPrsnNbr != "") || ($buyCoNbr != "")) {
    $query = "SELECT 
                    COUNT(HED.ORD_NBR) AS NBR_ORD, 
                    YEAR(ORD_TS) AS ORD_YEAR,
                    MONTH(ORD_TS) AS ORD_MONTH,
                    COM.NAME AS NAME_CO,
                    PPL.NAME AS NAME_PPL,
                    COM.CO_NBR AS BUY_CO_NBR,
                    PPL.PRSN_NBR AS BUY_PRSN_NBR,
                    SUM(TOT_AMT) AS TOT_AMT,
                    COALESCE(SUM(PAY.TND_AMT),0) AS PYMT_DOWN,
                    SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0) AS TOT_REM 
                    FROM CMP.RTL_ORD_HEAD HED 
                    INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
                    LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
                    LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
                    LEFT JOIN (
                        SELECT 
                        PYMT.ORD_NBR,
                        COALESCE(SUM(PYMT.TND_AMT),0) AS TND_AMT
                        FROM CMP.RTL_ORD_PYMT PYMT
                        WHERE PYMT.DEL_NBR = 0
                        GROUP BY PYMT.ORD_NBR
                    ) PAY ON PAY.ORD_NBR = HED.ORD_NBR
                    WHERE TOT_REM>0 
                        AND $whereString 
                        AND HED.DEL_NBR=0
                        $whereDte
                    GROUP BY YEAR(ORD_TS),MONTH(ORD_TS),COM.NAME,PPL.NAME,COM.CO_NBR,PPL.PRSN_NBR HAVING (SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0))>0 ORDER BY 2";
} else {
    $query = "SELECT 
                    COUNT(HED.ORD_NBR) AS NBR_ORD, 
                    MIN(DATE(ORD_TS)) AS ORD_TS_MIN,
                    MAX(DATE(ORD_TS)) AS ORD_TS_MAX,
                    COM.NAME AS NAME_CO,
                    PPL.NAME AS NAME_PPL,
                    COM.CO_NBR AS BUY_CO_NBR,
                    PPL.PRSN_NBR AS BUY_PRSN_NBR,
                    SUM(TOT_AMT) AS TOT_AMT,
                    COALESCE(SUM(PAY.TND_AMT),0) AS PYMT_DOWN,
                    SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0) AS TOT_REM 
                    FROM CMP.RTL_ORD_HEAD HED 
                    INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
                    LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
                    LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR 
                    LEFT JOIN (
                        SELECT 
                        PYMT.ORD_NBR,
                        COALESCE(SUM(PYMT.TND_AMT),0) AS TND_AMT
                        FROM CMP.RTL_ORD_PYMT PYMT
                        WHERE PYMT.DEL_NBR = 0
                        GROUP BY PYMT.ORD_NBR
                    ) PAY ON PAY.ORD_NBR = HED.ORD_NBR
                    WHERE TOT_REM>0 
                        AND HED.DEL_NBR=0 
                        $whereDte
                    GROUP BY COM.NAME,PPL.NAME,COM.CO_NBR,PPL.PRSN_NBR HAVING (SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0))>0 ORDER BY 8 DESC";
}

$result = mysql_query($query);
$i = 1;
while ($row = mysql_fetch_array($result)) {
    if (($row['BUY_CO_NBR'] == "") && ($row['BUY_PRSN_NBR']) == "") {
        $nama = "Tunai";
    } else {
        $nama = $row['NAME_CO'] . " " . $row['NAME_PPL'];
    }
    $dataArray = array(
        $i,
        dec($row['NBR_ORD']),
        dec(trim($nama)),
        dec($row['TOT_AMT']),
        dec($row['PYMT_DOWN']),
        dec($row['TOT_REM']),
    );

    $reports['data'][] = $dataArray;
    $i++;
}

return $reports;