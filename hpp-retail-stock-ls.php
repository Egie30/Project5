<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$query = "SELECT (BEG_DTE - INTERVAL 1 DAY) AS BEFORE_DTE, BEG_DTE, END_DTE
	FROM RTL.ACCTG_BK
	WHERE ACT_F=1";

if ($_GET['BK_NBR'] != "") {
	$query .= " AND BK_NBR=" . $_GET['BK_NBR'];
}

$result = mysql_query($query);
$row = mysql_fetch_array($result);
$bookBeforeDate = $row['BEFORE_DTE'];
$bookBeginDate = $row['BEG_DTE'];
$bookEndDate = $row['END_DTE'];

$_GET['BEG_DT'] = $bookBeginDate;
$_GET['END_DT'] = $bookEndDate;

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/hpp.php";

	$resultsCOGS = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

//print_r($resultsCOGS);

?>
<table class="table-accounting">
	<tbody>
		<tr>
		<tr style="cursor:pointer;" onclick="location.href='retail-stock-report.php?IVC_TYP=RC&BEG_DT=<?php echo $_GET['BEG_DT'];?>&END_DT=<?php echo $_GET['END_DT'];?>'">
		<td class="std" style="text-align:left">Pembelian</td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right"> <?php echo number_format($resultsCOGS->data->RECEIVING,0,',','.');?></td>
		<td class="std" style="text-align:right"></td>
		</tr>
		<tr style="cursor:pointer;" onclick="location.href='retail-stock-report.php?IVC_TYP=RC&BEG_DT=<?php echo $_GET['BEG_DT'];?>&END_DT=<?php echo $_GET['END_DT'];?>'">
		<td class="std" style="text-align:left">Retur Pembelian</td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right"> <?php echo number_format($resultsCOGS->data->RETUR,0,',','.');?></td>
		<td class="std" style="text-align:right"></td>
		</tr>

		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black" colspan="2">-</td>
		<td class="std" style="text-align:right"></td>
		</tr>
		<tr>
		<td class="std" style="text-align:center;font-weight:bold" colspan="2">Pembelian Bersih</td>
		<td class="std" style="text-align:right"> <?php echo number_format($resultsCOGS->data->RECEIVING_NETT,0,',','.');?></td>
		<td class="std" style="text-align:right"></td>
		</tr>

		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black">-</td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left;font-weight:bold" colspan="3">Barang Terpakai</td>
		<td class="std" style="text-align:right;font-weight:bold"> <?php echo number_format($resultsCOGS->data->RECEIVING_NETT,0,',','.');?></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left;font-weight:bold" colspan="3">Biaya Produksi</td>
		<td class="std" style="text-align:right"></td>
		</tr>
		
		<?php
		
		if (($_GET['ACTG'] == 0) || ($_GET['ACTG'] == 2)) {
			echo '<tr>
			<td class="std" style="text-align:left;" colspan="4">Pengeluaran Rutin</td>			
			</tr>';
		}
		?>
		
		<tr>
		<td class="std" style="text-align:left;"></td>
		<td class="std" style="text-align:left;">Gaji Karyawan</td>
		<td class="std" style="text-align:right"> <?php echo number_format($resultsCOGS->data->PAYROLL,0,',','.');?></td>
		<td class="std" style="text-align:right"></td>
		</tr>
		
		<?php 
		
		if(isset($resultsCOGS->data->UTILITY)) {
			foreach($resultsCOGS->data->UTILITY as $utility) {
			$utilitySub		= 'TOT_SUB_'.$utility->UTL_TYP;
			
			if($resultsCOGS->data->ROUTINE->total->$utilitySub != 0) {
		?>
		<tr>
		<td class="std" style="text-align:left;"></td>
		<td class="std" style="text-align:left;"><?php echo $utility->UTL_DESC; ?></td>
		<td class="std" style="text-align:right"> <?php echo number_format((0.9 * $resultsCOGS->data->ROUTINE->total->$utilitySub),0,',','.'); ?></td>
		<td class="std" style="text-align:right"></td>
		</tr>
		
		<?php } } } ?>
				
		<tr>
		<td class="std" style="text-align:left;" colspan="3">Retail Stock</td>
		<td class="std" style="text-align:right"></td>
		</tr>

		<tr>
		<td class="std" style="text-align:left;"></td>
		<td class="std" style="text-align:left;">Biaya Click Charge</td>
		<td class="std" style="text-align:right"> <?php echo number_format($resultsCOGS->data->CLICK,0,',','.');?></td>
		<td class="std" style="text-align:right"></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black" colspan="2">+</td>
		<td class="std" style="text-align:right"></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left;font-weight:bold">Total Pengeluaran</td>
		<td class="std" style="text-align:right;font-weight:bold"> <?php echo number_format($resultsCOGS->data->TOTAL_COST,0,',','.');?></td>
		<td class="std" style="text-align:left"></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left;font-weight:bold" colspan="3">Harga Pokok Produksi</td>
		<td class="std" style="text-align:right;font-weight:bold"> <?php echo number_format($resultsCOGS->data->COGS,0,',','.');?></td>
		</tr>
		
	</tbody>
</table>