<?php
require_once 'framework/database/connect.php';
require_once 'framework/functions/default.php';
require_once 'framework/functions/dotmatrix.php';
require_once "framework/phpExcel/Classes/PHPExcel.php";

if (empty($_GET['CO_NBR'])) {
    $_GET['CO_NBR'] = $CoNbrDef;
}

$title = 'Laporan Penjualan';

if ($_GET['page'] != "") {
    $title .= " - Halaman #" . $_GET['page'];
}

$reports = array(
    'title' => $title,
    'column' =>          array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'),
    'columnDimension' => array(  10,  40,  40,  40,  15,  15,  15,  10,  15,  15,  20),
    'titles' => array(
        'No',
        'Judul',
        'Pengirim',
        'Penerima',
        'Tgl Order',
        'Tgl Nota',
        'Status',
        'Jumlah',
        'Total',
        'Sisa',
        'Pembuat'
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
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
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
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
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
        'K' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
    ),
    'data' => array(),
    'total' => array(
        array(
            'title' => 'Total Jenis Item',
            'formula' => '=SUM(H{start}:H{end})',
        ),
        array(
            'title' => 'Total Item Masuk',
            'formula' => '=SUM(I{start}:I{end})',
        ),
		array(
            'title' => 'Total Item Keluar',
            'formula' => '=SUM(J{start}:J{end})',
        )
    )
);



try {
    ob_start();
    include __DIR__ . DIRECTORY_SEPARATOR . "ajax/retail-order.php";

    $results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
    ob_end_clean();
}

foreach ($results->data as $result) {
    $dataArray = array(
		$result->ORD_NBR,
		html_entity_decode($result->ORD_TTL, ENT_QUOTES, "UTF-8"),
		html_entity_decode($result->SHIPPER, ENT_QUOTES, "UTF-8"),
		html_entity_decode($result->RECEIVER, ENT_QUOTES, "UTF-8"),
        $result->ORD_DTE,
        $result->DL_DTE,
        $result->ORD_STT_DESC,
        $result->ORD_Q,
		$result->TOT_AMT,
		$result->TOT_REM,
		$result->CRT_NAME
    );

    $reports['data'][] = $dataArray;
}

return $reports;