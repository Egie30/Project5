<?php
/** PHPExcel */
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/functions/dotmatrix.php";
require_once "framework/phpExcel/Classes/PHPExcel.php";

$_GET['BK_NBR'];

try {
    
    ob_start();
    include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/balance-report.php";

    $results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
    ob_end_clean();
}



if($_GET['BK_NBR'] != '') {
	$query 		= "SELECT MONTHNAME(BEG_DTE) AS BK_MONTH, YEAR(BEG_DTE) AS BK_YEAR FROM RTL.ACCTG_BK WHERE BK_NBR = ".$_GET['BK_NBR'];
	$result		= mysql_query($query);
	$row		= mysql_fetch_array($result);
	
	$filename 	= "Laporan Neraca ".$row['BK_MONTH']." ".$row['BK_YEAR'];
}


// Create new PHPExcel object
$object = new PHPExcel();
 
// Set properties
$object->getProperties()->setCreator("Tempo")
               ->setLastModifiedBy("Tempo")
               ->setCategory("Approve by ");

$object->getActiveSheet()->getColumnDimension('A')->setWidth(50);


$title = 'Neraca';


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

$obj = $object->setActiveSheetIndex(0);


$worksheetDraw = new PHPExcel_Worksheet_Drawing();
$worksheetDraw->setName('Logo');
$worksheetDraw->setDescription('Logo');
$worksheetDraw->setPath('./img/logo.png');
$worksheetDraw->setCoordinates('A1');
$worksheetDraw->setResizeProportional(true);
$worksheetDraw->setHeight(50);
$worksheetDraw->setWorksheet($obj);


$col = chr('66');
foreach($results->book as $book) {
	
	$object->getActiveSheet()->getColumnDimension($col)->setWidth(30);
	$col++;
}



//add data
$counter	= 5;
$row		= 0;
$row_a		= 0;

$rows		= 0;
$row_p		= 0;

$obj->mergeCells("A1:B3");

$obj->setCellValue("A".$counter,$filename);

$counter++;

$obj->setCellValue("A".$counter,$reportBy);


$counter += 3;

$obj = $object->setActiveSheetIndex(0);

$obj->setCellValue("A".$counter,"Aktiva")->mergeCells("A".$counter.":D".$counter);
$obj->setCellValue("E".$counter,"Passiva")->mergeCells("E".$counter.":H".$counter);

$counter++;

$obj->setCellValue("A".$counter,"Tipe");
$obj->setCellValue("B".$counter,"Kode");
$obj->setCellValue("C".$counter,"Deskripsi");
$obj->setCellValue("D".$counter,"Total");
$obj->setCellValue("E".$counter,"Tipe");
$obj->setCellValue("F".$counter,"Kode");
$obj->setCellValue("G".$counter,"Deskripsi");
$obj->setCellValue("H".$counter,"Total");

$counter++;

$row = $counter;

foreach($results->activa as $key=>$report) {
	$obj->setCellValue("A".$row,$key);
	$row_a = $row+1;
	
					foreach($report as $data=>$value) {
						
						if($value->BALANCE != ''){
						$obj->setCellValue("B".$row_a,$value->ACC_NBR);
						$obj->setCellValue("C".$row_a,$value->CD_SUB_DESC);
					
						$obj->setCellValue("D".$row_a,$value->BALANCE)
								->getStyle("D".$row_a)
								->getNumberFormat()
								->setFormatCode('#,##0');
								
						$row_a++;
						$row++;
						}
					}
	$row++;				
}
	
	
$rows = $counter;

foreach($results->passiva as $key=>$report) {
	$obj->setCellValue("E".$rows,$key);
	$row_p = $rows+1;
	
					foreach($report as $data=>$value) {
						
						if($value->BALANCE != ''){
						$obj->setCellValue("F".$row_p,$value->ACC_NBR);
						$obj->setCellValue("G".$row_p,$value->CD_SUB_DESC);
					
						$obj->setCellValue("H".$row_p,$value->BALANCE)
								->getStyle("H".$row_p)
								->getNumberFormat()
								->setFormatCode('#,##0');
								
						$row_p++;
						$rows++;
						}
					}
	$rows++;				
}
				
$rows++;
			
$obj->setCellValue("E".$rows,"Laba / Rugi");
$obj->setCellValue("H".$rows,$results->profit)
								->getStyle("H".$rows)
								->getNumberFormat()
								->setFormatCode('#,##0');
			
if($row > $rows)	{ $counter = $row; } else { $counter = $rows; }

$counter++;

$obj->setCellValue("A".$counter,"Total Aktiva")->mergeCells("A".$counter.":C".$counter);
$obj->setCellValue("D".$counter,$results->total->ACTIVA)
								->getStyle("D".$counter)
								->getNumberFormat()
								->setFormatCode('#,##0');

$obj->setCellValue("E".$counter,"Total Passiva")->mergeCells("E".$counter.":G".$counter);
$obj->setCellValue("H".$counter,$results->total->PASSIVA)
								->getStyle("H".$counter)
								->getNumberFormat()
								->setFormatCode('#,##0');

					
// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
header('Cache-Control: max-age=0');
 
$objWriter = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$objWriter->save('php://output');
exit;


?>