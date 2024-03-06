<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

if ($lock == 1) {
	try {
		$_GET["GROUP"] = "INV_NBR";
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/inventory-position.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
}
else if (($_SESSION['PLUS_MODE'] == 1) || ($_GET['PLUS'] == 1)) {
	try {
		$_GET["GROUP"] = "INV_NBR";
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/inventory-position-plus.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
}
else {
	try {
		$_GET["GROUP"] = "INV_NBR";
		
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/inventory-position.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
}

//print_r($results);

if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}
?>
<table class="std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable">
	<thead>
		<tr>
			<th class="sortable" style="text-align:right;">No.</th>
			<th class="sortable">Nama</th>
			<th class="sortable">Supplier</th>
			<th class="sortable">Barcode</th>
			<th class="sortable">Departemen</th>
			<th class="sortable">Sub Departemen</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Faktur</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Harga</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Lokasi</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Masuk</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Keluar</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Retur</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Jual</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Koreksi</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Stock</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Total Stock</th>
			<th class="sortable sortable-sortDutchCurrencyValues">Total Jual</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($results->data as $data) {
			?>
			<tr style="cursor:pointer" onclick="location.href='inventory-position-detail.php?CO_NBR=<?php echo $_GET['CO_NBR'];?>&INV_NBR=<?php echo $data->INV_NBR;?>&SPL_NBR=<?php echo $_GET['SPL_NBR'];?>&CAT_SUB_NBR=<?php echo $_GET['CAT_SUB_NBR'];?>&PLUS=<?php echo $_GET['PLUS'];?>&END_DT=<?php echo $endDate; ?>'">
			<td class="std" style="text-align:right"><?php echo $data->INV_NBR ;?></td>
			<td class="std" style="text-align:left;white-space:nowrap;"><?php echo $data->INV_NAME ;?></td>
			<td class="std" style="text-align:left;white-space:nowrap;"><?php echo $data->SPL_NAME ;?></td>
			<td class="std" style="text-align:center"><?php echo $data->PRE_INV_BCD ;?></td>
			<td class="std" style="text-align:left;white-space:nowrap;"><?php echo $data->CAT_DESC ;?></td>
			<td class="std" style="text-align:left;white-space:nowrap;"><?php echo $data->CAT_SUB_DESC ;?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->INV_PRC, 0, ',', '.') ;?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->PRC, 0, ',', '.') ;?></td>
			<td class="std" style="text-align:left;white-space:nowrap;"><?php echo $data->RCV_NAME ;?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->RCV_Q, 0, ',', '.') ;?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->TRF_Q * -1, 0, ',', '.') ;?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->RTR_Q * -1, 0, ',', '.') ;?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->RTL_Q * -1, 0, ',', '.') ;?></td>
			<td class="std" style="text-align:right"><?php echo number_format($data->COR_Q, 0, ',', '.') ;?></td>
			
			<td class="std" style="text-align:right">
			<?php if ($data->BALANCE < 0) { ?>
				<div class="label red"><?php echo $data->BALANCE ;?></div>
			<?php } else { ?>
				<?php echo number_format($data->BALANCE, 0, ',', '.') ;?>
			<?php } ?>
			</td>

			<td class="std" style="text-align:right">
			<?php if ($data->BALANCE_PRC < 0) { ?>
				<div class="label red"><?php echo $data->BALANCE_PRC ;?></div>
			<?php } else { ?>
				<?php echo number_format($data->BALANCE_PRC, 0, ',', '.') ;?>
			<?php } ?>
			</td>

			<td class="std" style="text-align:right">
			<?php if ($data->BALANCE_RTL_PRC < 0) { ?>
				<div class="label red"><?php echo $data->BALANCE_RTL_PRC ;?></div>
			<?php } else { ?>
				<?php echo number_format($data->BALANCE_RTL_PRC, 0, ',', '.') ;?>
			<?php } ?>
			</td>
			</tr>
			<?php
		}
		?>
	</tbody>
	<tfoot>
		<tr>
			<td class="std" style="text-align:right" colspan="9">Total:</td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->RCV_Q, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->TRF_Q * -1, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->RTR_Q * -1, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->RTL_Q * -1, 0, ',', '.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($results->total->COR_Q, 0, ',', '.');?></td>

			<td class="std" style="text-align:right">
			<?php if ($data->BALANCE < 0) { ?>
				<div class="label red"><?php echo $results->total->BALANCE;?></div>
			<?php } else { ?>
				<?php echo number_format($results->total->BALANCE, 0, ',', '.');?>
			<?php } ?>
			</td>

			<td class="std" style="text-align:right">
			<?php if ($data->BALANCE_PRC < 0) { ?>
				<div class="label red">Rp. <?php echo $results->total->BALANCE_PRC;?></div>
			<?php } else { ?>
				Rp. <?php echo number_format($results->total->BALANCE_PRC, 0, ',', '.');?>
			<?php } ?>
			</td>

			<td class="std" style="text-align:right">
			<?php if ($data->BALANCE_RTL_PRC < 0) { ?>
				<div class="label red">Rp. <?php echo $results->total->BALANCE_RTL_PRC;?></div>
			<?php } else { ?>
				Rp. <?php echo number_format($results->total->BALANCE_RTL_PRC, 0, ',', '.');?>
			<?php } ?>
			</td>
		</tr>
	</tfoot>
</table>

<?php buildPagination($results->pagination, "inventory-position-ls.php");?>