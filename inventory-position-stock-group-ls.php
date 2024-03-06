<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/inventory-position-stock.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}
?>
<table id="mainTable" class="table-accounting tablesorter">
	<thead>
		<tr>
			<th class="sortable">No.</th>
			<?php if ($_GET['GROUP'] == 'SPL_NBR') { ?>
			<th class="sortable">Kode Suplier</th>
			<th class="sortable">Suplier</th>
			<th class="sortable">Alamat Suplier</th>
			<?php } ?>
			<?php if ($_GET['GROUP'] == 'CAT_SUB_NBR') { ?>
			<th class="sortable">Departemen</th>
			<th class="sortable">Sub Departemen</th>
			<?php } ?>
			<th class="sortable sortable-sortDutchCurrencyValues">Jenis Item</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Masuk</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Keluar</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Retur</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Jual</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Assembly</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Disassembly</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Checkout</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Koreksi</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Stock</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Total Stock</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Total Jual</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i = $_GET['page'] > 1 ? ($_GET['page'] - 1) * $_GET['LIMIT'] : 0;
		$i++;

		foreach ($results->data as $data) {
			$onclick = "inventory-position-stock.php?CO_NBR=" . $_GET['CO_NBR'] . "&END_DT=" . $_GET['END_DT'];

			if ($_GET['GROUP'] == 'SPL_NBR') {
				$onclick .= "&SPL_NBR=" . $data->SPL_NBR;
			} elseif ($_GET['GROUP'] == 'CAT_SUB_NBR') {
				$onclick .= "&CAT_SUB_NBR=" . $data->CAT_SUB_NBR;
			}
			?>
			<tr class="clickable" style="cursor:pointer" onclick="location.href='<?php echo $onclick;?>'">
			<td class="std" style="text-align:center"><?php echo $i;?>.</td>
			<?php if ($_GET['GROUP'] == 'SPL_NBR') { ?>
			<td class="std" style="text-align:center;"><?php echo $data->SPL_NBR;?></td>
			<td class="std" style="text-align:left;white-space:nowrap;"><?php echo $data->SPL_NAME;?></td>
			<td class="std" style="text-align:left;white-space:nowrap;"><?php echo $data->SPL_ADDR;?></td>
			<?php } ?>
			<?php if ($_GET['GROUP'] == 'CAT_SUB_NBR') { ?>
			<td class="std" style="text-align:left;white-space:nowrap;"><?php echo $data->CAT_DESC;?></td>
			<td class="std" style="text-align:left;white-space:nowrap;"><?php echo $data->CAT_SUB_DESC;?></td>
			<?php } ?>
			<td class="std" style="text-align:right"><?php echo number_format($data->ITM_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->RCV_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->TRF_Q * -1, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->RTR_Q * -1, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->RTL_Q * -1, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->AS_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->DS_Q * -1, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->OUT_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->COR_Q, 0, ',', '.');?></td>

			<?php if ($data->BALANCE < 0) { ?>
				<td class="std" style="text-align:right"><div style="display:block" class="label red"><?php echo $data->BALANCE;?></div></td>
			<?php } else { ?>
				<td class="std" style="text-align:right"><?php echo number_format($data->BALANCE, 0, ',', '.');?></td>
			<?php } ?>

			<?php if ($data->BALANCE_PRC < 0) { ?>
				<td class="std" style="text-align:right"><div style="display:block" class="label red"><?php echo $data->BALANCE_PRC;?></div></td>
			<?php } else { ?>
				<td class="std" style="text-align:right"><?php echo number_format($data->BALANCE_PRC, 0, ',', '.');?></td>
			<?php } ?>

			<?php if ($data->BALANCE_RTL_PRC < 0) { ?>
				<td class="std" style="text-align:right"><div style="display:block" class="label red"><?php echo $data->BALANCE_RTL_PRC;?></div></td>
			<?php } else { ?>
				<td class="std" style="text-align:right"><?php echo number_format($data->BALANCE_RTL_PRC, 0, ',', '.');?></td>
			<?php } ?>
			</tr>
			<?php
			$i++;
		}
		?>
	</tbody>
	<tfoot>
		<tr>
			<?php
			$totalColspan = 1;
			
			if ($_GET['GROUP'] == 'SPL_NBR') {
				$totalColspan += 3;
			}

			if ($_GET['GROUP'] == 'CAT_SUB_NBR') {
				$totalColspan += 2;
			} ?>
			<td class="std" style="text-align:right" colspan="<?php echo $totalColspan?>">Total:</td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->ITM_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->RCV_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->TRF_Q * -1, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->RTR_Q * -1, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->RTL_Q * -1, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->AS_Q * -1, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->DS_Q * -1, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->OUT_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->COR_Q, 0, ',', '.');?></td>

			<?php if ($results->total->BALANCE < 0) { ?>
				<td class="std" style="text-align:right" data-order="<?php echo $results->total->BALANCE;?>"><div style="display:block" class="label red"><?php echo $results->total->BALANCE;?></div></td>
			<?php } else { ?>
				<td class="std" style="text-align:right" data-order="<?php echo $results->total->BALANCE;?>"><?php echo number_format($results->total->BALANCE, 0, ',', '.');?></td>
			<?php } ?>

			<?php if ($results->total->BALANCE_PRC < 0) { ?>
				<td class="std" style="text-align:right" data-order="<?php echo $results->total->BALANCE_PRC;?>"><div style="display:block" class="label red"><?php echo $results->total->BALANCE_PRC;?></div></td>
			<?php } else { ?>
				<td class="std" style="text-align:right" data-order="<?php echo $results->total->BALANCE_PRC;?>"><?php echo number_format($results->total->BALANCE_PRC, 0, ',', '.');?></td>
			<?php } ?>

			<?php if ($results->total->BALANCE_RTL_PRC < 0) { ?>
				<td class="std" style="text-align:right" data-order="<?php echo $results->total->BALANCE_RTL_PRC;?>"><div style="display:block" class="label red"><?php echo $results->total->BALANCE_RTL_PRC;?></div></td>
			<?php } else { ?>
				<td class="std" style="text-align:right" data-order="<?php echo $results->total->BALANCE_RTL_PRC;?>"><?php echo number_format($results->total->BALANCE_RTL_PRC, 0, ',', '.');?></td>
			<?php } ?>
		</tr>
	</tfoot>
</table>

<?php buildPagination($results->pagination, "inventory-position-stock-group-ls.php");?>