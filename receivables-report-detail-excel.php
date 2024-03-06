<?php
require_once 'framework/database/connect.php';
require_once 'framework/functions/default.php';
require_once 'framework/functions/dotmatrix.php';
require_once "framework/phpExcel/Classes/PHPExcel.php";

$title = 'Receivables Report';

if ($_GET['CO_NBR'] != '') {
    $query  = "SELECT NAME FROM COMPANY WHERE CO_NBR='" . $_GET['CO_NBR'] . "'";
    $result = mysql_query($query);
    $row    = mysql_fetch_array($result);

    $title .= ' - ' . $row['NAME'];
}

if ($_GET['page'] != "") {
    $title .= " - Halaman #" . $_GET['page'];
}

$reports = array(
    'title' => $title,
    'column' =>          array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'),
    'columnDimension' => array(  10,  40,  40,  15,  15,  15,  15,  15,  15,  15),
    'titles' => array(
        'No',
        'Judul',
        'Pemesan',
        'Pesan',
        'Status',
        'Janji',
        'Jadi',
        'Jatuh Tempo',
        'Jumlah',
        'Sisa'
    ),
    'styles' => array(
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
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
        'E' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
        'F' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
        'G' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
        'H' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
        'I' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
        'J' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
    ),
    'data' => array(),
    'total' => array(
        array(
            'title' => 'Total Jumlah',
            'formula' => '=SUM(I{start}:I{end})',
        ),
		array(
            'title' => 'Total Sisa',
            'formula' => '=SUM(J{start}:J{end})',
        )
    )
);



try {
	$_GET["GROUP"]	= "ORD_NBR";
	$_GET["ORD_BY"]	= "ORD_TS";
	
    ob_start();
    include __DIR__ . DIRECTORY_SEPARATOR . "ajax/receivables.php";

    $results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
    ob_end_clean();
}

foreach ($results->data as $result) {
    $dataArray = array(
		$result->ORD_NBR,
		html_entity_decode($result->ORD_TTL, ENT_QUOTES, "UTF-8"),
		html_entity_decode($result->NAME_PPL." ".$result->NAME_CO, ENT_QUOTES, "UTF-8"),
		html_entity_decode(parseDateShort($result->ORD_TS), ENT_QUOTES, "UTF-8"),
        $result->ORD_STT_DESC,
		parseDateShort($result->DUE_TS)." ". parseHour($result->DUE_TS) ." ". parseMinute($result->DUE_TS),
        parseDateShort($result->CMP_TS),
        parseDateShort($result->PAST_DUE),
		$result->TOT_AMT,
		$result->TOT_REM
    );

    $reports['data'][] = $dataArray;
}

return $reports;