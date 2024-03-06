<?php
ini_set("include_path", '/home/sbsjewel/php:' . ini_get("include_path"));
require_once "framework/database/connect.php";
require_once "framework/dompdf/dompdf_config.inc.php";



$ORD_NBR = $_GET['ORD_NBR'];
$query	= "SELECT HEAD.ORD_TS,
       HEAD.SHP_PRSN_NBR,
       (HEAD.TOT_AMT - SLSDET.TOT_SUB) AS TOT_AMT,
       DATE_ADD(HEAD.DL_TS, INTERVAL 1 YEAR) AS VALID,
       ODET.INV_BCD,
       CS.CAT_SUB_DESC,
       ODET.ORD_NBR
FROM RTL_STK_HEAD HEAD
LEFT JOIN RTL_STK_DET DET ON DET.ORD_NBR = HEAD.ORD_NBR
LEFT JOIN INVENTORY INV ON INV.INV_NBR = DET.INV_NBR
LEFT JOIN RTL_ORD_DET ODET ON ODET.INV_NBR = DET.INV_NBR
LEFT JOIN RTL_ORD_HEAD OHED ON OHED.ORD_NBR = ODET.ORD_NBR
LEFT JOIN CAT_SUB CS ON CS.CAT_SUB_NBR = INV.CAT_SUB_NBR
LEFT JOIN RTL_ORD_HEAD SLS ON SLS.REF_NBR = HEAD.ORD_NBR
LEFT JOIN RTL_ORD_DET SLSDET ON SLSDET.ORD_NBR = SLS.ORD_NBR
WHERE HEAD.ORD_NBR = ".$ORD_NBR;

#TIPE MDSE CAT_SUB_DESC
#KODE MDSE BARCODE
$result = mysql_query($query);
$row = mysql_fetch_array($result);

$query = "SELECT NAME FROM PEOPLE WHERE PRSN_NBR = " . $row['SHP_PRSN_NBR'];
$rs = mysql_query($query);
$row_person = mysql_fetch_array($rs);

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
	<style>
		@page {
			padding: 0;
			margin: 10px 0 0 10px;;
			size: 19.5cm 15.8cm;
		}

		body {
			padding:0;
			margin: 0px;
		}

		* {
			font-family: Arial, Helvetica, sans-serif;
			color: #553F32;
		}

		.page {
			width: 100%;
		}

		.vertical-header {
			height: 100%;
			width: 4cm;
			position: absolute;
		}
		table {
			width: 100%;
			border-collapse: collapse;
			border: 1px solid black;
		}
		tr, td {
			
			height:5cm;
			
		}
		.vertical-text {
			position: absolute;
			transform: rotate(270deg);
		}
	</style>
</head>
<body>
<div class="page">
	<table border=0>
		<tr>
			<td style="width:359px;border: 1px solid black;">
				Satu<br>
				Dua<br>
				Tiga<br>
				Empat<br>
				Lima<br>
				Enam<br>
				Tujuh<br>
				delapan<br>
				sembilan
			</td>
			<td style="width:7.5px;border: 1px solid black;">&nbsp;</td>
			<td style="width:359px;border: 1px solid black;">
				Satu<br>
				Dua<br>
				Tiga<br>
				Empat<br>
				Lima<br>
				Enam<br>
				Tujuh<br>
				delapan<br>
				sembilan
			</td>
		</tr>

		<tr>
			<td style="border: 1px solid black;">
				Satu<br>
				Dua<br>
				Tiga<br>
				Empat<br>
				Lima<br>
				Enam<br>
				Tujuh<br>
				delapan<br>
				sembilan
			</td>
			<td style="border: 1px solid black;">&nbsp;</td>
			<td style="border: 1px solid black;">
				Satu<br>
				Dua<br>
				Tiga<br>
				Empat<br>
				Lima<br>
				Enam<br>
				Tujuh<br>
				delapan<br>
				sembilan
			</td>
		</tr>

		<tr>
			<td style="border: 1px solid black;">
				Satu<br>
				Dua<br>
				Tiga<br>
				Empat<br>
				Lima<br>
				Enam<br>
				Tujuh<br>
				delapan<br>
				sembilan
			</td>
			<td style="border: 1px solid black;">&nbsp;</td>
			<td style="border: 1px solid black;">
				Satu<br>
				Dua<br>
				Tiga<br>
				Empat<br>
				Lima<br>
				Enam<br>
				Tujuh<br>
				delapan<br>
				sembilan
			</td>
		</tr>
	</table>
</div>
</body>
</html>
<?php
$html 			= ob_get_clean();
$customPaper 	= array(0,0,360,360);

ob_end_clean();

$pdf = new DOMPDF();
$pdf->load_html($html);
$pdf->set_paper('A4');
$pdf->render();
$pdf->stream("form.pdf", array("Attachment" => false));
?>