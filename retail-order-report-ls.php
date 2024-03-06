<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$_GET['CO_NBR']	= $CoNbrDef;
$beginDate		= $_GET['BEG_DT'];
$endDate		= $_GET['END_DT'];

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/retail-order.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}
//echo "<pre>";
//print_r($results);

if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}			
?>

<table class="table-accounting tablesorter">
	<thead>
		<tr>
			<th class="sortable" style="text-align:right;">No.</th>
			<th class="sortable" style="text-align:center;">Judul</th>
			<th class="sortable">Pengirim</th>
			<th class="sortable">Penerima</th>
			<th class="sortable">Tgl Order</th>
			<th class="sortable">Tgl Nota</th>
			<th class="sortable">Status</th>
			<th class="sortable">Jumah</th>
			<th class="sortable">Total</th>
			<th class="sortable">Sisa</th>
			<th class="sortable" style="text-align:center;">Pembuat</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i = $_GET['page'] > 1 ? ($_GET['page'] - 1) * $_GET['LIMIT'] : 0;
		$i++;

		foreach ($results->data as $data) {
		?>
		<tr>
			<td class="std"><?php echo $data->ORD_NBR;?></td>
			<td class="std"><?php echo $data->ORD_TTL;?></td>
			<td class="std"><?php echo $data->SHIPPER;?></td>
			<td class="std"><?php echo $data->RECEIVER;?></td>
			<td class="std"><?php echo $data->ORD_DTE;?></td>
			<td class="std"><?php echo $data->DL_DTE;?></td>
			<td class="std"><?php echo $data->ORD_STT_DESC;?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->ORD_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->TOT_AMT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->TOT_REM, 0, ',', '.');?></td>
			<td class="std"><?php echo $data->CRT_NAME;?></td>
		</tr>
		<?php
		$i++;
		}
		?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">	
			<td class="std" style="text-align:right;font-weight:bold;" colspan="7">Total:</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->ORD_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_AMT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_REM, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"></td>
		</tr>
	</tfoot>
	</table>


<?php buildPagination($results->pagination, "retail-order-report-ls.php"); ?>
