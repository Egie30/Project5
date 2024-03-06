<?php

require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";
require_once "framework/phpExcel/Classes/PHPExcel.php";

$ReportType = mysql_escape_string($_GET['RPT_TYP']);
$Year = mysql_escape_string($_GET['YEAR']);
$Month = mysql_escape_string($_GET['MONTH']);
$BuyCoNbr = mysql_escape_string($_GET['BUY_CO_NBR']);
$BuyPrsnNbr = mysql_escape_string($_GET['BUY_PRSN_NBR']);
$where = mysql_escape_string($_GET['where']);
$title = 'Creativehub List';

//Process filter
$OrdSttId = mysql_escape_string($_GET['STT']);

function dec($s)
{
    return html_entity_decode($s, ENT_QUOTES, "UTF-8");
}

$reports = array(
    'title'              => $title,
    'column'             => array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'),
    'columnDimension'    => array(10, 50, 60, 15, 15, 15, 15, 15, 15, 15),
    'columnDimensionPdf' => array(1, 3, 8, 2, 2.5, 3, 3, 3, 3, 3),
    'titles'             => array(
        'No.',
        'Judul',
        'Pemesan',
        'Pesan',
        'Status',
        'Janji',
        'Jadi',
        'Jumlah',
        'Bayar',
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
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            )
        ),
        'C' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            )
        ),
        'D' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        ),
        'E' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        ),
        'F' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        ),
        'G' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        ),
        'H' => array(
            'alignment'    => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
        'I' => array(
            'alignment'    => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
        'J' => array(
            'alignment'    => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
    ),
    'data'               => array(),
    'total'              => array()
);

//Continue process filter
$activePeriod = 3;
$badPeriod = 12;
if ($OrdSttId == "ALL") {
    $where = "WHERE HED.ORD_STT_ID LIKE '%'";
} elseif ($OrdSttId == "CP") {
    $where = "WHERE HED.ORD_STT_ID='CP' 
    AND TIMESTAMPADD(MONTH,$activePeriod,ORD_BEG_TS)>=CURRENT_TIMESTAMP 
    AND HED.DEL_NBR=0";
} elseif ($OrdSttId == "DUE") {
    $where = "WHERE TOT_REM>0 
    AND DATE_ADD(CMP_TS,INTERVAL COALESCE(PAY_TERM,0) DAY)<=CURRENT_TIMESTAMP 
    AND HED.DEL_NBR=0";
} elseif ($OrdSttId == "COL") {
    $buyPrsnNbr = $_GET['BUY_PRSN_NBR'];
    $buyCoNbr = $_GET['BUY_CO_NBR'];
    if ($buyCoNbr != "") {
        $whereString = " AND BUY_CO_NBR=" . $buyCoNbr;
        if ($buyPrsnNbr != "") {
            $whereString .= " AND BUY_PRSN_NBR=" . $buyPrsnNbr;
        }
    } else {
        if ($buyPrsnNbr != "") {
            $whereString = " AND BUY_PRSN_NBR=" . $buyPrsnNbr;
        }
    }
    if (($buyPrsnNbr == "0") && ($buyCoNbr == "0")) {
        $whereString = " AND (BUY_CO_NBR IS NULL AND BUY_PRSN_NBR IS NULL)";
    }
    $where = "WHERE HED.DEL_NBR=0 " . $whereString . " 
    AND YEAR(ORD_BEG_TS)=" . $_GET['YEAR'] . " 
    AND MONTH(ORD_BEG_TS)=" . $_GET['MONTH'] . " 
    AND TOT_REM>0";
} elseif ($OrdSttId == "ACT") {
    $buyPrsnNbr = mysql_escape_string($_GET['BUY_PRSN_NBR']);
    $buyCoNbr = mysql_escape_string($_GET['BUY_CO_NBR']);
    $year = mysql_escape_string($_GET['YEAR']);
    $month = mysql_escape_string($_GET['MONTH']);

    if ($buyCoNbr != "") {
        $whereString = " AND BUY_CO_NBR=" . $buyCoNbr;
        if ($buyPrsnNbr != "") {
            $whereString .= " AND BUY_PRSN_NBR=" . $buyPrsnNbr;
        }
    } else {
        if ($buyPrsnNbr != "") {
            $whereString = " AND BUY_PRSN_NBR=" . $buyPrsnNbr;
        }
    }
    if (($buyPrsnNbr == "0") && ($buyCoNbr == "0")) {
        $whereString = " AND (BUY_CO_NBR IS NULL AND BUY_PRSN_NBR IS NULL)";
    }
    $where = "WHERE (HED.ORD_STT_ID!='CP' OR (HED.ORD_STT_ID='CP' 
    AND TIMESTAMPADD(MONTH,$activePeriod,ORD_BEG_TS)>=CURRENT_TIMESTAMP) 
    OR (TOT_REM>0 AND TIMESTAMPADD(MONTH,$badPeriod,ORD_BEG_TS)>=CURRENT_TIMESTAMP)) 
    AND HED.DEL_NBR=0";
} else {
    $where = "WHERE HED.ORD_STT_ID='" . $OrdSttId . "' AND HED.DEL_NBR=0";
}

$query = "SELECT HED.ORD_NBR,IVC_PRN_CNT,ORD_BEG_TS,HED.ORD_STT_ID,
        ORD_STT_DESC,BUY_PRSN_NBR,
        PPL.NAME AS NAME_PPL,
        COM.NAME AS NAME_CO,
        BUY_CO_NBR,REF_NBR,ORD_TTL,DUE_TS,FEE_MISC, FEE_MISC,
        TOT_AMT,PYMT_DOWN,PYMT_REM,TOT_REM,CMP_TS,PU_TS,SPC_NTE,
        HED.CRT_TS,HED.CRT_NBR,HED.UPD_TS,HED.UPD_NBR,CMP_TS,
        DATEDIFF(DATE_ADD(CMP_TS,INTERVAL COALESCE(COM.PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS PAST_DUE,
    COALESCE(FEE_MISC,0) AS FEE_MISC,
    COALESCE(TOT_AMT,0) AS TOT_AMT,
    COALESCE(TOT_REM,0) AS TOT_REM,
    COALESCE(TOT_SUB,0) AS TOT_SUB,
    COALESCE(TOT_SUB,0) + COALESCE(FEE_MISC,0) AS TTL_AMT,
    COALESCE(TND_AMT,0) AS TND_AMT,
    (COALESCE(TOT_SUB,0) + COALESCE(FEE_MISC,0)) - COALESCE(TND_AMT,0) AS TTL_REM
                    FROM CMP.RTL_ORD_HEAD HED
                    INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
                    LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
                    LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR 
                    LEFT OUTER JOIN(
        SELECT 
            ORD_NBR,
            SUM(TOT_SUB) AS TOT_SUB
        FROM CMP.RTL_ORD_DET
        WHERE DEL_NBR = 0
        GROUP BY ORD_NBR
    )DET ON HED.ORD_NBR = DET.ORD_NBR
    LEFT OUTER JOIN(
        SELECT 
            ORD_NBR,
            SUM(TND_AMT) AS TND_AMT
        FROM CMP.RTL_ORD_PYMT
        WHERE DEL_NBR = 0
        GROUP BY ORD_NBR
    )PYMT ON HED.ORD_NBR = PYMT.ORD_NBR 
    $where
                   ORDER BY ORD_NBR DESC";

// echo $query;
$result = mysql_query($query);
while ($row = mysql_fetch_array($result)) {
    $dataArray = array(
        dec($row['ORD_NBR']),
        dec($row['ORD_TTL']),
        dec($row['NAME_PPL'] . " " . $row['NAME_CO']),
        dec(parseDateShort($row['ORD_BEG_TS'])),
        dec($row['ORD_STT_DESC']),
        dec(parseDateShort($row['DUE_TS'])),
        dec(parseDateShort($row['CMP_TS'])),
        dec($row['TTL_AMT']),
        dec($row['TND_AMT']),
        dec($row['TTL_REM']),
    );

    $reports['data'][] = $dataArray;
}

return $reports;