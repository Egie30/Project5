<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$Actg		= $_GET['ACTG'];
$bookNumber = $_GET['BK_NBR'];

try {
	$_GET['BK_NBR'] = $bookNumber;
	$_GET['PLUS'] = $_SESSION['PLUS_MODE'];
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/trial-balance.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

?>
<table class="table-accounting">
	<thead>
		<tr>
			<th class="sortable" rowspan="2">Tipe</th>
			<th class="sortable" rowspan="2">Kode</th>
			<th class="sortable" rowspan="2">Deskripsi</th>
			<th class="sortable" colspan="2">Saldo Awal</th>
			<th class="sortable" colspan="2">Transaksi</th>
			<th class="sortable" colspan="2">Saldo</th>
		</tr>
		<tr>
			<th class="sortable">Debit</th>
			<th class="sortable">Kredit</th>
			<th class="sortable">Debit</th>
			<th class="sortable">Kredit</th>
			<th class="sortable">Debit</th>
			<th class="sortable">Kredit</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($results->data as $result) { ?>
		<tr style="cursor:pointer;" onclick="location.href='general-ledger.php?CD_CAT_NBR=<?php echo $result->CD_CAT_NBR;?>&BK_NBR=<?php echo $bookNumber; ?>&ACTG=<?php echo $Actg; ?>'">
		<td class="std" style="text-align:left;font-weight:bold;" colspan="3"><?php echo $result->CD_CAT_DESC;?></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($result->DEB,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($result->CRT,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($result->GL_DEB,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($result->GL_CRT,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($result->DEB + $resultJournal->GL_DEB,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($result->CRT + $resultJournal->GL_CRT,0,',','.');?></td>
		</tr>
		<?php foreach ($result->ACCOUNT as $resultAccount) { ?>
			<tr style="cursor:pointer;" onclick="location.href='general-ledger.php?CD_NBR=<?php echo $resultAccount->CD_NBR;?>&BK_NBR=<?php echo $_GET['BK_NBR']; ?>'">
			<td class="std" style="text-align:center"></td>
			<td class="std" style="text-align:left;font-weight:bold;" colspan="2"><?php echo $resultAccount->CD_DESC;?></td>
			<td class="std" style="text-align:right;font-weight:bold;">-</td>
			<td class="std" style="text-align:right;font-weight:bold;">-</td>
			<td class="std" style="text-align:right;font-weight:bold;">-</td>
			<td class="std" style="text-align:right;font-weight:bold;">-</td>
			<td class="std" style="text-align:right;font-weight:bold;">-</td>
			<td class="std" style="text-align:right;font-weight:bold;">-</td>
			</tr>
			<?php foreach ($resultAccount->ACCOUNT as $resultAccountSub) { ?>
				<tr style="cursor:pointer;" onclick="location.href='general-ledger.php?CD_SUB_NBR=<?php echo $resultAccountSub->CD_SUB_NBR;?>&BK_NBR=<?php echo $_GET['BK_NBR']; ?>'">
				<td class="std" style="text-align:center"></td>
				<td class="std" style="text-align:left"><?php echo $resultAccountSub->ACC_NBR;?></td>
				<td class="std" style="text-align:left"><?php echo $resultAccountSub->CD_DESC . ' - ' . $resultAccountSub->CD_SUB_DESC;?></td>
				<td class="std" style="text-align:right"> <?php echo number_format($resultAccountSub->DEB,0,',','.');?></td>
				<td class="std" style="text-align:right"> <?php echo number_format($resultAccountSub->CRT,0,',','.');?></td>
				<td class="std" style="text-align:right"> <?php echo number_format($resultAccountSub->GL_DEB,0,',','.');?></td>
				<td class="std" style="text-align:right"> <?php echo number_format($resultAccountSub->GL_CRT,0,',','.');?></td>
				<td class="std" style="text-align:right"> <?php echo number_format($resultAccountSub->DEB + $resultAccountSub->GL_DEB,0,',','.');?></td>
				<td class="std" style="text-align:right"> <?php echo number_format($resultAccountSub->CRT + $resultAccountSub->GL_CRT,0,',','.');?></td>
				</tr>
			<?php } ?>
		<?php } ?>
	<?php } ?>
	</tbody>
	<tfoot>
		<tr>
				<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black" colspan="10"></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:center;font-weight:bold;" colspan="3">Total</td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($results->total->BEGIN_DEB,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($results->total->BEGIN_CRT,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($results->total->ACC_DEB,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($results->total->ACC_CRT,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($results->total->BALANCE_DEB,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($results->total->BALANCE_CRT,0,',','.');?></td>
		</tr>
	</tfoot>
</table>