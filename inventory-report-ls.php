<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

try {
	$_GET['GROUP'] = 'RTL_BRC';

	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/forms-sale-item.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

echo "<pre>";
print_r($results);

if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}
?>
<table class="table-accounting tablesorter">
	<thead>
		<tr>
			<th class="sortable">No.</th>
			<th class="sortable">Suplier</th>
			<th class="sortable">Nama</th>
			<th class="sortable">Departemen</th>
			<th class="sortable">Sub Departemen</th>
			<th class="sortable">Barcode</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Nota</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Item</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Retur Item</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Harga Beli</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Harga Jual</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Subtotal Beli</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Subtotal</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Disc</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Net</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i = $_GET['page'] > 1 ? ($_GET['page'] - 1) * $_GET['LIMIT'] : 0;
		$i++;
		foreach ($results->data as $data) {
			?>
			<tr style="cursor:pointer" <?php echo $alt; ?> onclick="location.href='inventory-report-list.php?CO_NBR=<?php echo $_GET['CO_NBR'];?>&CNMT_F=<?php echo $_GET['CNMT_F'];?>&SPL_NBR=<?php echo $_GET['SPL_NBR'];?>&CAT_SUB_NBR=<?php echo $_GET['CAT_SUB_NBR'];?>&BEG_DT=<?php echo $_GET['BEG_DT'];?>&END_DT=<?php echo $_GET['END_DT'];?>&PLUS=<?php echo $_GET['PLUS'];?>&RTL_BRC=<?php echo $data->RTL_BRC;?>&GROUP=TRSC_NBR'">
				<td class="std" style="text-align:center"><?php echo $i;?></td>

				<td class="std" style="text-align:left;white-space:nowrap">
				<?php if ($data->SPL_NAME != "Unknown") { ?>
					<?php echo $data->SPL_NAME;?>
				<?php } else { ?>
					<div class="label red"><?php echo $data->SPL_NAME;?></div>
				<?php } ?>
				</td>
				
				<td class="std" style="text-align:left;white-space:nowrap">
				<?php if ($data->INV_NAME != "Unknown") { ?>
					<?php echo $data->INV_NAME;?>
				<?php } else { ?>
					<div class="label red"><?php echo $data->INV_NAME;?></div>
				<?php } ?>
				</td>
				
				<td class="std" style="text-align:left;white-space:nowrap;"><?php echo $data->CAT_DESC;?></td>
				<td class="std" style="text-align:left;white-space:nowrap;"><?php echo $data->CAT_SUB_DESC;?></td>
				<td class="std" style="text-align:center"><?php echo $data->RTL_BRC;?></td>
				<td class="std" style="text-align:center"><?php echo number_format($data->TRSC_AMT, 0, ',', '.');?></td>
				<td class="std" style="text-align:center"><?php echo number_format($data->RTL_Q, 0, ',', '.');?></td>
				<td class="std" style="text-align:center"><?php echo number_format($data->RTR_Q, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->INV_PRC, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->INV_RTL_PRC, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->INV_PRC_AMT, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->BRT_AMT, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->DISC_AMT, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->NETT_AMT, 0, ',', '.');?></td>
			</tr>
			<?php
			if($alt==""){$alt="class='alt'";}else{$alt="";}
			$i++;
		}
		?>
	</tbody>
	<tfoot>
		<tr style="font-wight:bold;">
			<td class="std" style="text-align:right" colspan="7">Total:</td>
			<td class="std" style="text-align:center"><?php echo number_format($results->total->RTL_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:center"><?php echo number_format($results->total->RTR_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right">-</td>
			<td class="std" style="text-align:right">-</td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->INV_PRC_AMT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->BRT_AMT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->DISC_AMT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->NETT_AMT, 0, ',', '.');?></td>
				
		</tr>
	</tfoot>
</table>

<?php buildPagination($results->pagination, "inventory-report-ls.php");?>