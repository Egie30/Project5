<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$StockYear		= $_GET['STK_YEAR'];

$_GET['RL_TYP']	= 'RL_YEAR';

if (empty($StockYear)) {
		$_GET['STK_YEAR'] = date("Y");
	}
	else { $_GET['STK_YEAR'] = $StockYear; }

try {
	
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/store-inventory-monthly.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}

echo '<h3> Tahun '.$StockYear.'</h3>';

?>


<table class="table-accounting tablesorter">
	<thead>
		<tr>
			<?php 
				echo '<th class="sortable">No.</th>';		
				echo '<th class="sortable">Bulan</th>';		
				echo '<th class="sortable">Beli (Qty)</th>';		
				echo '<th class="sortable">Beli (Rp)</th>';		
				echo '<th class="sortable">Retur (Qty)</th>';
				echo '<th class="sortable">Retur (Rp)</th>';
				echo '<th class="sortable">Check Out (Qty)</th>';
				echo '<th class="sortable">Check Out (Rp)</th>';
				echo '<th class="sortable">Koreksi (Qty)</th>';
				echo '<th class="sortable">Koreksi (Rp)</th>';
			?>
			
			
		</tr>
	</thead>
	<tbody>
		<?php
		$i = 1;
		
		foreach ($results->data as $data) {
		
		if($data->ORD_MONTHNAME != '') {
			?>
			<tr>
				<td class="std" style="text-align:left"><?php echo $i;?></td>
				<td class="std" style="text-align:left"><?php echo $data->ORD_MONTHNAME;?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->RCV_Q, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->RCV_TOT_SUB, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->RTR_Q, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->RTR_TOT_SUB, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->MOV_QTY, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->MOV_TOTAL, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->COR_Q, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->COR_TOT_SUB, 0, ',', '.');?></td>
			</tr>
			<?php
			$i++;
			}
		}
		?>
	</tbody>

	<tfoot>
		<tr style="border-top:1px solid grey">
			<td class="std" style="text-align:right;font-weight:bold;" colspan=2>Total</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->RCV_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->RCV_TOT_SUB, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->RTR_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->RTR_TOT_SUB, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->MOV_QTY, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->MOV_TOTAL, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->COR_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->COR_TOT_SUB, 0, ',', '.');?></td>
		</tr>
	</tfoot>

	</table>


<?php 
buildPagination($results->pagination, "store-inventory-monthly-ls.php"); 
?>

