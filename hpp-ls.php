<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";


$query = "SELECT (BEG_DTE - INTERVAL 1 DAY) AS BEFORE_DTE, BEG_DTE, END_DTE
	FROM RTL.ACCTG_BK
	WHERE ACT_F=1";

if ($_GET['BK_NBR'] != "") {
	$query .= " AND BK_NBR=" . $_GET['BK_NBR'];
}

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/hpp.php";

	$resultsCOGS = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

//echo "<pre>"; print_r($resultsCOGS);

?>

<br />
<table class="table-accounting">
	<tbody>
		<tr>
		<td class="std" style="text-align:left; font-weight:bold;" colspan="4">HARGA POKOK PENDAPATAN</td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="width:30%; text-align:left; font-weight:bold; border-bottom:2px solid black" >Harga Pokok Produksi</td>
		<td class="std" style="text-align:left" colspan="2"></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left">Persediaan Awal Bahan Baku</td>
		<td class="std" style="text-align:right"> <?php echo number_format($resultsCOGS->data->BEGIN_MAIN,0,',','.');?></td>
		<td class="std" style="text-align:right"></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left">Persediaan Awal Bahan Penolong</td>
		<td class="std" style="text-align:right"> <?php echo number_format($resultsCOGS->data->BEGIN_SUB,0,',','.');?></td>
		<td class="std" style="text-align:right"></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left">Pembelian Bahan Baku dan Penolong</td>
		<td class="std" style="text-align:right;border-bottom:2px solid black"> <?php echo number_format($resultsCOGS->data->PROCUREMENT,0,',','.');?></td>
		<td class="std" style="text-align:right"></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left">Barang Tersedia untuk Diproduksi</td>
		<td class="std" style="text-align:right;"> <?php echo number_format($resultsCOGS->data->BTUD,0,',','.');?></td>
		<td class="std" style="text-align:right"></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left">Persediaan Akhir Bahan Baku</td>
		<td class="std" style="text-align:right;"> - <?php echo number_format($resultsCOGS->data->END_MAIN,0,',','.');?></td>
		<td class="std" style="text-align:right"></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left">Persediaan Akhir Bahan Penolong</td>
		<td class="std" style="text-align:right;border-bottom:2px solid black"> - <?php echo number_format($resultsCOGS->data->END_SUB,0,',','.');?></td>
		<td class="std" style="text-align:right"></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left;font-weight:bold;">Bahan yang Terpakai</td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($resultsCOGS->data->USED,0,',','.');?></td>
		</tr>
		
		<tr><td colspan="4"><br /></td></tr>
		
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left;font-weight:bold;">Biaya Tenaga Kerja Langsung</td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($resultsCOGS->data->BTKL,0,',','.');?></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="width:30%; text-align:left; font-weight:bold; border-bottom:2px solid black">Biaya Overhead Pabrik</td>
		<td class="std" style="text-align:left" colspan="2"></td>
		</tr>
				
		<tr>

		<td class="std" style="text-align:right"></td>
		
		<?php 
		
		
		foreach($resultsCOGS->data->OVERHEAD->data as $overhead) {
			if($overhead->TOT_AMT != 0) {
		?>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left"><?php echo $overhead->CAT_SUB_DESC; ?></td>
		<td class="std" style="text-align:right"> <?php echo number_format($overhead->TOT_AMT,0,',','.');?></td>
		<td class="std" style="text-align:right"></td>
		</tr>
	
		<?php } } ?>

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
		<td class="std" style="text-align:right"> <?php echo number_format(($resultsCOGS->data->ROUTINE->total->$utilitySub * 0.9),0,',','.'); ?></td>
		<td class="std" style="text-align:right"></td>
		</tr>
		
		<?php } } }	?>
		
		<tr>
		<td class="std" style="text-align:left;"></td>
		<td class="std" style="text-align:left;">Click Charge</td>
		<td class="std" style="text-align:right"> <?php echo number_format($resultsCOGS->data->CLICK,0,',','.'); ?></td>
		<td class="std" style="text-align:right"></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left;font-weight:bold;">Total Biaya Overhead Pabrik</td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($resultsCOGS->data->TOTAL_OVERHEAD,0,',','.');?></td>
		</tr>
		
				
		<tr>
		<td class="std" style="text-align:right" colspan="3"></td>
		<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black"> + </td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left;font-weight:bold;" colspan="3">Harga Pokok Produksi</td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($resultsCOGS->data->HPP,0,',','.');?></td>
		</tr>
		
		<tr><td colspan="4"><br /></td></tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left;">Persediaan Awal Barang Jadi</td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black"> - </td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left">Barang Tersedia Untuk Dijual<td>
		<td class="std" style="text-align:right"> <?php echo number_format($resultsCOGS->data->HPP,0,',','.');?> </td>
		<td class="std" style="text-align:right"></td>
		</tr>
				
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left;">Persediaan Akhir Barang Jadi</td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black"> - </td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:left;font-weight:bold;" colspan="3">HARGA POKOK PENDAPATAN</td>
		<td class="std" style="text-align:right;font-weight:bold;"> <?php echo number_format($resultsCOGS->data->HPP,0,',','.');?></td>
		</tr>
		
	</tbody>
</table>