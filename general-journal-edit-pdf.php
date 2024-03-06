<?php
//ini_set("include_path", '/home/sbsjewel/php:' . ini_get("include_path") ); // Let me here
require_once "framework/database/connect.php";
require_once "framework/functions/dotmatrix.php";
//require_once "Numbers/Words.php"; // Pear packages - Switched off Numbers_Words_Locale_en_US::367
//require_once "Numbers/Words/locale/lang.id.php";
require_once "framework/dompdf/dompdf_config.inc.php";

$glNumber		= $_GET['GL_NBR'];
$printNumber	= $_GET['PRN_NBR'];

$template = "";
$paper = "letter";
$orientation = "portrait";

	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "general-journal-print.php";

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
$pdf->load_html($template, 'UTF-8');
$pdf->set_paper($paper, $orientation);
$pdf->render();
$pdf->stream("Bukti Pengeluaran Kas.pdf", array("Attachment" => false));

exit(0);