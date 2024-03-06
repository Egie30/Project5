<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";

$companyNumber	= $_GET['CO_NBR'];
$beginDate		= $_GET['BEG_DT'];
$endDate		= $_GET['END_DT'];
$Type			= $_GET['TYP'];
$CatTypeNumber 	= $_GET['CAT_TYP_NBR'];

//$_GET['GROUP']	= 'INV_NBR';
$_GET['TYP']	= $Type;

try {

	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/checkout-report.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

//echo "<pre>"; print_r($results->query);

if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}


?>

<br />

<table class="table-accounting tablesorter">
	
	<thead>
		<tr>
	<?php 
			
				echo '<th style="width:10%" class="sortable" style="text-align:center;">No. Barang</th>';		
				echo '<th class="sortable" style="text-align:center;">Nama</th>';	
								
				echo '<th class="sortable" style="text-align:center;">Pengirim</th>';
				echo '<th class="sortable" style="text-align:center;">Penerima</th>';
				echo '<th class="sortable" style="text-align:center;">Nota</th>';
				echo '<th class="sortable" style="text-align:center;">Tgl Nota</th>';
				echo '<th class="sortable" style="text-align:center;">Mulai Checkout</th>';
				echo '<th class="sortable" style="text-align:center;">Selesai Checkout</th>';
				echo '<th class="sortable" style="text-align:center;">Jumlah</th>';
				echo '<th class="sortable" style="text-align:center;">Harga Beli</th>';
	?>
	
		</tr>		
	</thead>
	
	<tbody>

		<?php
						
			foreach ($results->data as $data) {
							
				echo "<tr style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-move-report-detail.php?INV_NBR=".$data->INV_NBR."&BEG_DT=".$beginDate."&END_DT=".$endDate."&TYP=".$Type."';".chr(34).">";
								
				echo '<td class="std" style="text-align:center;">'.$data->INV_NBR.'</td>';
				echo '<td class="std" style="text-align:left;">'.$data->INV_NAME.'</td>';
				echo '<td class="std" style="text-align:left;">'.$data->SHP_NAME.'</td>';
				echo '<td class="std" style="text-align:left;">'.$data->RCV_NAME.'</td>';
				echo '<td class="std" style="text-align:center;">'.$data->ORD_NBR.'</td>';
				
				echo '<td class="std" style="text-align:center;">'.$data->ORD_DT.'</td>';
				echo '<td class="std" style="text-align:center;">'.$data->BEG_DT.' '.$data->BEG_TM.'</td>';
				echo '<td class="std" style="text-align:center;">'.$data->END_DT.' '.$data->END_TM.'</td>';

				echo '<td class="std" style="text-align:right">'.number_format($data->MOV_Q, 0, ',', '.').'</td>';
				echo '<td class="std" style="text-align:right">'.number_format($data->MOV_INV_PRC, 0, ',', '.').'</td>';				
				
				echo '</tr>';
				
			}
			
	
	echo '</tbody>';
	
	echo '<tfoot>';
	echo '<tr style="border-top:1px solid grey">';
		echo '<td colspan=9 style="font-weight:bold; text-align:center;" >Total</td>';
		echo '<td colspan=9 style="font-weight:bold; text-align:right;" >'.number_format($results->total->MOV_INV_PRC, 0, ',', '.').'</td>';
	echo '</tr>';
	echo '</tfoot>';
	
	echo '</table>';
	

	//buildPagination($results->pagination, "inventory-move-report-ls.php"); 
	?>

