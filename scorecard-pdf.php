<?php
ini_set("include_path", '/home/sbsjewel/php:' . ini_get("include_path") ); // Let me here
require_once "framework/database/connect.php";
require_once "framework/functions/dotmatrix.php";
require_once "framework/dompdf/dompdf_config.inc.php";

$template 		= "";
$paper 			= "A4";
$orientation	= "landscape";

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "scorecard-pdf-print.php";
	$template = ob_get_clean();
} catch (\Exception $ex) {
	ob_end_clean();
}


if ( get_magic_quotes_gpc() ) {
	$template = stripslashes($template);
}
// echo $template;exit();
$pdf = new DOMPDF();
$pdf->load_html($template);
$pdf->set_paper($paper, $orientation);
$pdf->render();
$pdf->stream("scorecard-" . $_GET['FLTR_DATE'] . ".pdf", array("Attachment" => false));

exit(0);