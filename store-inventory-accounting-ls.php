<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";
	
$SecurityAct = getSecurity($_SESSION['userID'],"Accounting");

$companyNumber	= $_GET['CO_NBR'];
$endDate		= $_GET['END_DT'];

$groupDetail	= $_GET['GROUP'];


$_GET['GROUP']	= 'PRN_DIG_TYP';
$groupType		= $_GET['GROUP'];


try {
	
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/store-inventory-accounting.php";

	$resultsType = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

unset($_GET['GROUP']);

$_GET['GROUP']	= 'INV_NBR';
$group			= $_GET['GROUP'];

try {
		
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/store-inventory-accounting.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}


?>
<br />

<table>
	<thead>
		<tr>
			<?php 
				echo '<th style="width:10%" class="sortable" style="text-align:center;">Kode Inventory</th>';		
				echo '<th class="sortable" style="text-align:center;">Nama Inventory</th>';	
								
				echo '<th class="sortable" style="text-align:center;">Beli</th>';
				echo '<th class="sortable" style="text-align:center;">Mutasi Masuk</th>';
				echo '<th class="sortable" style="text-align:center;">Retur</th>';
				echo '<th class="sortable" style="text-align:center;">Mutasi Keluar</th>';
				echo '<th class="sortable" style="text-align:center;">Checkout</th>';
				echo '<th class="sortable" style="text-align:center;">Koreksi</th>';
				echo '<th class="sortable" style="text-align:center;">Stok</th>';
				if($SecurityAct == 0) {
				echo '<th class="sortable" style="text-align:center;">Total Stok</th>';
				}
			?>
			
			
		</tr>
	</thead>
	<tbody>

		<?php
		
		$i = 0;
		
		foreach ($resultsType->data as $dataType) { 
			
			if (($i % 2) == 0) {
				$style	= 'background-color:#eee;';
			}
			else {
				$style	= 'background-color:white';
			}
			
			echo "<tr style='cursor:pointer;".$style."' class='tr-master'>";
					
				if($dataType->PRN_DIG_DESC == '') {
					echo '<td class="std" style="text-align:left;" colspan=2>Lain-Lain</td>';
				}
				else {
					echo '<td class="std" style="text-align:left;" colspan=2>'.$dataType->PRN_DIG_DESC.'</td>';
					}
			
			
			echo '<td class="std" style="text-align:right;">'.number_format($dataType->RCV_Q, 0, ',', '.').'</td>';
			echo '<td class="std" style="text-align:right;">'.number_format($dataType->XF_IN_Q, 0, ',', '.').'</td>';
			echo '<td class="std" style="text-align:right;">'.number_format($dataType->RTR_Q, 0, ',', '.').'</td>';
			echo '<td class="std" style="text-align:right;">'.number_format($dataType->XF_OUT_Q, 0, ',', '.').'</td>';
			echo '<td class="std" style="text-align:right;">'.number_format($dataType->MOV_Q, 0, ',', '.').'</td>';
			echo '<td class="std" style="text-align:right;">'.number_format($dataType->COR_Q, 0, ',', '.').'</td>';
			echo '<td class="std" style="text-align:right;">'.number_format($dataType->BALANCE_Q, 0, ',', '.').'</td>';
			
			if($SecurityAct == 0) {
				echo '<td class="std" style="text-align:right;">'.number_format($dataType->BALANCE_AMT, 0, ',', '.').'</td>';
			}
			echo '</tr>';
									
			foreach ($results->data as $data) {
			
			if($dataType->PRN_DIG_TYP == $data->PRN_DIG_TYP) {
				
				echo "<tr class='tr-detail' style='border-top:1px solid #ddd;border-bottom:1px solid #ddd;'>";
						
				echo '<td class="std" style="text-align:center; width:10%;">'.$data->INV_NBR.'</td>';
				echo '<td class="std" style="text-align:left;">'.$data->INV_NAME.'</td>';
									
				echo '<td class="std" style="text-align:right">'.number_format($data->RCV_Q, 0, ',', '.').'</td>';
				echo '<td class="std" style="text-align:right">'.number_format($data->XF_IN_Q, 0, ',', '.').'</td>';
				echo '<td class="std" style="text-align:right">'.number_format($data->RTR_Q, 0, ',', '.').'</td>';
				echo '<td class="std" style="text-align:right">'.number_format($data->XF_OUT_Q, 0, ',', '.').'</td>';
				echo '<td class="std" style="text-align:right">'.number_format($data->MOV_Q, 0, ',', '.').'</td>';
				echo '<td class="std" style="text-align:right">'.number_format($data->COR_Q, 0, ',', '.').'</td>';
				echo '<td class="std" style="text-align:right;">'.number_format($data->BALANCE_Q, 0, ',', '.').'</td>';
				if($SecurityAct == 0) {
				echo '<td class="std" style="text-align:right;">'.number_format($data->BALANCE_AMT, 0, ',', '.').'</td>';
				}
				
				echo '</tr>';
			
			}
			}
		$i++;
	}
	
	echo '</tbody>';
	
	echo '<tfoot>';
	echo '<tr class="tr-total">';
				
	echo '<td class="std" style="text-align:right;font-weight:bold;" colspan="2">Total:</td>';
	echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($results->total->RCV_Q, 0, ',', '.').'</td>';
	echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($results->total->XF_IN_Q, 0, ',', '.').'</td>';
	echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($results->total->RTR_Q, 0, ',', '.').'</td>';
	echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($results->total->XF_OUT_Q, 0, ',', '.').'</td>';
	echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($results->total->MOV_Q, 0, ',', '.').'</td>';
	echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($results->total->COR_Q, 0, ',', '.').'</td>';
	echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($results->total->BALANCE_Q, 0, ',', '.').'</td>';
	if($SecurityAct == 0) {
		echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($results->total->BALANCE_AMT, 0, ',', '.').'</td>';
	}
	echo '</tr>';
	echo '</tfoot>';
	
	
	echo '</table>';

	buildPagination($results->pagination, "store-inventory-accounting-ls.php"); 
	?>

