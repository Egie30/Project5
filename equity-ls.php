<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$bookNumber	= $_GET['BK_NBR'];

try {
	
	ob_start();
	
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/equity.php";

	$resultsEquity = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

//print_r($resultsEquity);

?>
	<table class="table-accounting">
		<tbody>
			<tr style="cursor:pointer;" onclick="location.href='trial-balance.php?CD_CAT_NBR=3'">
			<td class="std" style="text-align:left;font-weight:bold;">Modal Usaha Awal</td>
			<td class="std" style="text-align:left"></td>
			<td class="std" style="text-align:right;font-weight:bold;">Rp. <?php echo number_format($resultsEquity->data->BALANCE,0,',','.');?></td>
			</tr>

			<tr style="cursor:pointer;" onclick="location.href='equity.php'">
			<td class="std" style="text-align:left;font-weight:bold;">Laba Usaha</td>
			<td class="std" style="text-align:right;font-weight:bold;">Rp. <?php echo number_format($resultsEquity->data->PROFIT_LOSS,0,',','.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"></td>
			</tr>

			<tr>
			<td class="std" style="text-align:left;font-weight:bold;">Prive</td>
			<td class="std" style="text-align:right;font-weight:bold;">Rp. <?php echo number_format($resultsEquity->data->PRIVE,0,',','.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"></td>
			</tr>

			<tr>
			<td class="std" style="text-align:left;font-weight:bold;"></td>
			<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black">-</td>
			<td class="std" style="text-align:right;font-weight:bold;"></td>
			</tr>

			<tr>
			<td class="std" style="text-align:center;font-weight:bold;">Laba Usaha - Prive</td>
			<td class="std" style="text-align:right;font-weight:bold;">Rp. <?php echo number_format($resultsEquity->data->PROFIT_PRIVE,0,',','.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"></td>
			</tr>

			<tr>
			<td class="std" style="text-align:left;font-weight:bold;"></td>
			<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black" colspan="2">-</td>
			</tr>

			<tr>
			<td class="std" style="text-align:left;font-weight:bold;">Modal Usaha Akhir</td>
			<td class="std" style="text-align:right;font-weight:bold;"></td>
			<td class="std" style="text-align:right;font-weight:bold;">Rp. <?php echo number_format($resultsEquity->data->EQUITY,0,',','.');?></td>
			</tr>
		</tbody>
	</table>

