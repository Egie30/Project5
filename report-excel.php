<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/functions/dotmatrix.php";
require_once "framework/phpExcel/Classes/PHPExcel.php";

function getNameFromNumber($index) 
{
    $numeric = $index % 26;
    $letter = chr(65 + $numeric);
    $indexVal = intval($index / 26);

    if ($indexVal > 0) 
    {
        return getNameFromNumber($indexVal - 1) . $letter;
    } 
    else 
    {
        return $letter;
    }
}

$beginDate = $_GET['BEG_DT'];
$endDate = $_GET['END_DT'];

//Company
if ($CoNbrDef != "") {
    $query  = "SELECT NAME FROM RTL.COMPANY WHERE CO_NBR='" . $CoNbrDef . "'";
    $result = mysql_query($query);
    $row    = mysql_fetch_array($result);
    
    $company = $row['NAME'];
}

$query  = "SELECT NAME FROM RTL.PEOPLE PPL INNER JOIN RTL.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='" . $_SESSION['userID'] . "'";
$result = mysql_query($query);
$row    = mysql_fetch_array($result);
$operator   = $row['NAME'];

$reportBy   = "Dicetak oleh: " . dispNameScreen($operator);

$reports = include __DIR__ . DIRECTORY_SEPARATOR . strtolower($_GET['RPT_TYP']) . ".php";

$reports = array_merge(array(
        'column' => array(),
        'data' => array(),
        'total' => array(),
        'styles' => array(),
        'conditionalStyles' => array(),
    ),
    $reports
);

// Create new PHPExcel object
$excel = new PHPExcel();

// Set document properties
$excel  ->getProperties()
        ->setCompany($company)
        ->setCreator($operator)
        ->setLastModifiedBy($operator)
        ->setModified(time())
        ->setTitle($reports['title'])
        ->setSubject($reports['title'])
        ->setDescription($reports['title']);

$baseWorksheet = $excel->getActiveSheet()->copy();

$excel->removeSheetByIndex(0);

// Set default font
$baseWorksheet->getDefaultStyle()->getFont()->setName('Arial');
$baseWorksheet->getDefaultStyle()->getFont()->setSize(10);

$worksheetDraw = new PHPExcel_Worksheet_Drawing();

if($_GET['TYP'] != 'FICT') 
{
    $worksheetDraw->setName('Logo');
    $worksheetDraw->setDescription('Logo');
    $worksheetDraw->setPath('./img/logo.png');
    $worksheetDraw->setCoordinates('A1');
    $worksheetDraw->setResizeProportional(true);
    $worksheetDraw->setHeight(50);
    $worksheetDraw->setWorksheet($baseWorksheet);
}

if ($beginDate != "" && $endDate !="") 
{
    $reportDate = "Periode: " . date('d/m/Y', strtotime($beginDate)) . " - " . date('d/m/Y', strtotime($endDate));
} 
elseif ($beginDate != "") 
{
    $reportDate = "Mulai Periode: " . date('d/m/Y', strtotime($beginDate));
} 
elseif ($endDate != "") 
{
    $reportDate = "Akhir Periode: " . date('d/m/Y', strtotime($endDate));
} 
else 
{
    if ($_GET['YEAR']) 
    {
        
    }

    if ($_GET['MONTH']) 
    {
        
    }

    if ($_GET['DAY']) 
    {
        
    }
}

$headerRow = 6;
$columns = array_values($reports['column']);
$maximumColumn = end($columns);
$maximumOffsetColumn = $columns[count($columns) - 2];

$baseWorksheet
    ->mergeCells('A1:' . $maximumColumn . '3')
    ->setCellValue('A5', $reports['title'])
    ->mergeCells('A5:' . $maximumColumn . '5')
    ->getDefaultStyle()->getAlignment()->applyFromArray(
        array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    );

//$baseWorksheet->setCellValue("A8" ,"CAMPUS");
//$baseWorksheet->setCellValue("F8" ,"PRINTING");


$baseWorksheet->getDefaultStyle("A8")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$baseWorksheet->getDefaultStyle("F8")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

if ($beginDate == "" && $endDate =="") {
    if ($_GET['YEAR'] != "") {
        $baseWorksheet
            ->setCellValue("A" . $headerRow, "Tahun: " . $_GET['YEAR'])
            ->mergeCells("A" . $headerRow . ':' . $maximumColumn . $headerRow);
        $headerRow++;
    }

    if ($_GET['MONTH'] != "") {
        $baseWorksheet
            ->setCellValue("A" . $headerRow, "Bulan: " . $_GET['MONTH'])
            ->mergeCells("A" . $headerRow . ':' . $maximumColumn . $headerRow);
        $headerRow++;
    }

    if ($_GET['DAY'] != "") {
        $baseWorksheet
            ->setCellValue("A" . $headerRow, "Hari: " . $_GET['DAY'])
            ->mergeCells("A" . $headerRow . ':' . $maximumColumn . $headerRow);
        $headerRow++;
    }
} else {
    $baseWorksheet
        ->setCellValue("A" . $headerRow, $reportDate)
            ->mergeCells("A" . $headerRow . ':' . $maximumColumn . $headerRow);
    $headerRow++;
}

$baseWorksheet
    ->setCellValue("A" . $headerRow, $reportBy)
    ->mergeCells("A" . $headerRow . ':' . $maximumColumn . $headerRow);

$headerRow += 3;
$startContent = $headerRow + 1;

$baseWorksheet->freezePane('A' . ($headerRow + 1));

$multipleWorksheet = true;

if (!is_array($reports['worksheet']) || empty($reports['worksheet'])) {
    $multipleWorksheet = false;
    $reports['worksheet'] = array($reports['title']);
}

foreach ($reports['column'] as $index => $column) {
    $baseWorksheet->setCellValue($column . $headerRow, $reports['titles'][$index]);
    $baseWorksheet->getColumnDimension($column)->setWidth($reports['columnDimension'][$index]);
}

foreach ($reports['worksheet'] as $sheetIndex => $worksheetTitle) {
    $worksheet = $baseWorksheet->copy();

    $contentRow = $headerRow + 1;

    $worksheetTitle = preg_replace('~[^\p{L}\p{N}]++~u', ' ', $worksheetTitle);

    while (strlen($worksheetTitle) > 31) {
        $worksheetTitle = substr($worksheetTitle, 0, strripos($worksheetTitle, ' '));
    }

    $worksheet
        ->setTitle($worksheetTitle)
        ->setCodeName($sheetIndex);

    $excel->addSheet($worksheet);

    $worksheet->getStyle("A" . $headerRow . ":" . $maximumColumn . $headerRow)->applyFromArray(
        array(
            "alignment" => array(
                "horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                "vertical" => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ), 
            "font" => array(
                "bold" => true
            )
        )
    );

    $worksheet->getStyle("A8")->getFont()->setBold(true);
    $worksheet->getStyle("F8")->getFont()->setBold(true);
    $worksheet->getStyle("A5")->getFont()->setBold(true);
    $worksheet->getStyle("A6")->getFont()->setBold(true);

    $reportsData = $reports['data'];

    if ($multipleWorksheet) {
        $reportsData = $reports['data'][$sheetIndex];
    }

    foreach ($reportsData as $report) {
        $worksheet->fromArray($report, NULL, 'A' . $contentRow);

        foreach ($reports['styles'] as $column => $style) {
            $worksheet->getStyle($column . $contentRow)->applyFromArray($style);
        }

        foreach ($reports['conditionalStyles'] as $column => $style) {
            $worksheet->getStyle($column . $contentRow)->setConditionalStyles(array_merge(
                $worksheet->getStyle($column . $contentRow)->getConditionalStyles(),
                $style
            ));
        }

        $contentRow++;
    }

    $reportsTotal = $reports['total'];

    if ($multipleWorksheet) {
        $reportsTotal = $reports['total'][$sheetIndex];
    }

    $highestRow = $worksheet->getHighestRow('A') + 1;

    $worksheet->mergeCells('A' . $highestRow . ':' . $maximumColumn . $highestRow);

    $highestRow++;

    foreach ($reportsTotal as $report) {
        $worksheet->setCellValue('A' . $highestRow, $report['title']);
        $worksheet->setCellValue($maximumColumn . $highestRow, str_replace(array('{start}', '{end}'), array($startContent, $contentRow), $report['formula']));

        $worksheet->getStyle('A' . $highestRow . ':' . $maximumColumn . $highestRow)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
            'numberformat' => array(
                'code' => '#,##0'
            ),
            'font' => array(
                'bold' => false
            )
        ));

        $worksheet->mergeCells('A' . $highestRow . ':' . $maximumOffsetColumn . $highestRow);

        $highestRow++;
    }
}

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $reports['title'] . '.xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

ob_end_clean();

$objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$objWriter->save('php://output');
exit;;