<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";

$companyNumber	= $_GET['CO_NBR'];
$beginDate		= $_GET['BEG_DT'];
$endDate		= $_GET['END_DT'];
$Type			= $_GET['TYP'];

$_GET['GROUP']	= 'CRT_TS';

try {

	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/inventory-move-report.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

//echo "<pre>"; print_r($results->data);

if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tab/tabs.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/accounting.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	
	<style>
	.tr-detail{
		display:none;
	}
	.tr-detail td{
		border-top:1px solid #ddd;
	}
	
	.tr-total td{
		border-top:1px solid #A9A9A9;
	}
	
	
	</style>

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/tab/tabs.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<script type="text/javascript">jQuery.noConflict();</script>
</head>

<body>

<table id="mainTable" class="tablesorter searchTable">
	
	<thead>
		<tr>
	<?php 
			
				echo '<th style="width:10%" class="sortable" style="text-align:center;">Barcode Checkout</th>';		
				echo '<th class="sortable" style="text-align:center;">No. Barang</th>';	
				echo '<th class="sortable" style="text-align:center;">Nama</th>';				
				echo '<th class="sortable" style="text-align:center;">Pengirim</th>';
				echo '<th class="sortable" style="text-align:center;">Penerima</th>';
				echo '<th class="sortable" style="text-align:center;">Nota</th>';
				echo '<th class="sortable" style="text-align:center;">Tgl Nota</th>';
				echo '<th class="sortable" style="text-align:center;">Waktu Checkout</th>';
				echo '<th class="sortable" style="text-align:center;">Jumlah</th>';
	?>
	
		</tr>		
	</thead>
	
	<tbody>

		<?php
						
			foreach ($results->data as $data) {
							
				echo "<tr style='cursor:pointer;' onclick=".chr(34)."location.href='retail-stock-edit.php?ORD_NBR=".$data->ORD_NBR."';".chr(34).">";
								
				echo '<td class="std" style="text-align:center;">'.$data->ORD_DET_NBR.'</td>';
				echo '<td class="std" style="text-align:left;">'.$data->INV_NBR.'</td>';
				echo '<td class="std" style="text-align:left;">'.$data->INV_NAME.'</td>';
				echo '<td class="std" style="text-align:left;">'.$data->SHP_NAME.'</td>';
				echo '<td class="std" style="text-align:left;">'.$data->RCV_NAME.'</td>';
				echo '<td class="std" style="text-align:center;">'.$data->ORD_NBR.'</td>';
				
				echo '<td class="std" style="text-align:center;">'.$data->ORD_DT.'</td>';
				echo '<td class="std" style="text-align:center;">'.$data->CRT_TS.'</td>';

				echo '<td class="std" style="text-align:right">'.number_format($data->MOV_Q, 0, ',', '.').'</td>';		
				
				echo '</tr>';
				
			}
			
	
	echo '</tbody>';
	echo '<tfoot>';
		echo '<tr style="border-top:1px solid grey">';
			echo '<td style="font-weight:bold;text-align:right" colspan=8>Total</td>';
			echo '<td style="font-weight:bold;text-align:right">'.$results->total->MOV_Q.'</td>';
		echo '</tr>';
	echo '</tfoot>';
	
	echo '</table>';
	

	//buildPagination($results->pagination, "inventory-move-report-ls.php"); 
	?>
	
	
	
</body>
</html>

