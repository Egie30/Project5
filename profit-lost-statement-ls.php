<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

//echo $_GET['BK_NBR'];

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/profit-lost.php";

	$resultsProfitLoss = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

?>
<table class="table-accounting">
	<tbody>

		<tr>
		<td class="std" style="text-align:left;font-weight:bold" colspan="3">Penjualan Bersih</td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right"> <?php echo number_format($resultsProfitLoss->data->REVENUE,0,',','.');?></td>
		</tr>
		

		<tr style="cursor:pointer" onclick="location.href='hpp.php?BK_NBR=<?php echo $_GET['BK_NBR'];?>&ACTG=<?php echo $_GET['ACTG'];?>&RL_TYP=<?php echo $_GET['RL_TYP']; ?>'">
		<td class="std" style="text-align:left;font-weight:bold" colspan="3">HPP</td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right"> <?php echo number_format($resultsProfitLoss->data->HPP,0,',','.');?></td>
		</tr>
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black">-</td>
		</tr>
		<tr>
		<td class="std" style="text-align:left;font-weight:bold" colspan="3">LABA KOTOR</td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right;font-weight:bold"> <?php echo number_format($resultsProfitLoss->data->GROSS_PROFIT,0,',','.');?></td>
		</tr>
			
		<tr>
		<td class="std" style="text-align:left;font-weight:bold" colspan="3">PENGELUARAN</td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right;font-weight:bold"></td>
		</tr>
				
		<tr>
		<td class="std" style="text-align:left;"></td>
		<td class="std" style="text-align:left;">Gaji Karyawan Tidak Langsung</td>
		<td class="std" style="text-align:right"> <?php echo number_format($resultsProfitLoss->data->BTKTL,0,',','.');?></td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:right"></td>
		</tr>

		<?php
		
		if(isset($resultsProfitLoss->cost)) {
			foreach($resultsProfitLoss->cost as $cost) {
			
			if($cost->COST != 0) {
		?>
		
		<tr>
		
		<td class="std" style="text-align:left;"></td>
		<td class="std" style="text-align:left;"><?php echo $cost->CD_SUB_DESC; ?></td>
		<td class="std" style="text-align:right"> <?php echo number_format($cost->COST,0,',','.'); ?></td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:left;"></td>
		</tr>
		
		<?php 
		} 
		}	
		}
		
		if(isset($resultsProfitLoss->exp)) {
			foreach($resultsProfitLoss->exp as $exp) {
			
			if($exp->EXP != 0) {
		?>
		
		<tr>
		
		<td class="std" style="text-align:left;"></td>
		<td class="std" style="text-align:left;"><?php echo $exp->CD_SUB_DESC; ?></td>
		<td class="std" style="text-align:right"> <?php echo number_format($exp->EXP,0,',','.'); ?></td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:left;"></td>
		</tr>
		
		<?php 
		} 
		}	
		}

		
		if(isset($resultsProfitLoss->data->UTILITY)) {
			foreach($resultsProfitLoss->data->UTILITY as $utility) {
			
		
			$utilitySub		= 'TOT_SUB_'.$utility->UTL_TYP;
			
			if($resultsProfitLoss->data->ROUTINE->total->$utilitySub != 0) {
		?>
		<tr>
		
		<td class="std" style="text-align:left;"></td>
		<td class="std" style="text-align:left;"><?php echo $utility->UTL_DESC; ?></td>
		<td class="std" style="text-align:right"> <?php echo number_format((0.1 * $resultsProfitLoss->data->ROUTINE->total->$utilitySub),0,',','.'); ?></td>
		<td class="std" style="text-align:right"></td>
		<td class="std" style="text-align:left;"></td>
		</tr>
		
		<?php 
		} 
		}	
		}
		
	
		if(isset($resultsProfitLoss->data->EXPENSE)) {
			foreach($resultsProfitLoss->data->EXPENSE as $expense) {
			$expenseSub		= 'TOT_SUB_'.$expense->EXP_TYP;
			
			if($resultsProfitLoss->data->CASH->total->$expenseSub != 0) {
		?>
		
		<tr>
		<td class="std" style="text-align:left"></td>
		<td class="std" style="text-align:left;"><?php echo $expense->EXP_DESC; ?></td>
		<td class="std" style="text-align:right"> <?php echo number_format($resultsProfitLoss->data->CASH->total->$expenseSub,0,',','.'); ?></td>
		<td class="std" style="text-align:right" colspan="2"></td>
		</tr>
		
		<?php } } }
		
		foreach($resultsProfitLoss->data->CASH as $ArrayCost) {
				foreach($ArrayCost as $cost) {
				
				print_r($ArrayCost);
				echo "<br />";
				
				if($cost->TOT_AMT > 0) {
		?>
		<tr>
		<td class="std" style="text-align:left;"></td>
		<td class="std" style="text-align:left;"><?php echo $cost->CAT_SUB_DESC; ?></td>
		<td class="std" style="text-align:right"> <?php echo number_format($cost->TOT_AMT,0,',','.'); ?></td>
		<td class="std" style="text-align:right" colspan="2"></td>
		</tr>
		
		<?php } } } 
				
		?>
				
				<tr>
				<td class="std" style="text-align:left"></td>
				<td class="std" style="text-align:right"></td>
				<td class="std" style="text-align:right"></td>
				<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black">+</td>
				<td class="std" style="text-align:right"></td>
				</tr>
				<tr>
				<td class="std" style="text-align:left"></td>
				<td class="std" style="text-align:left"></td>
				<td class="std" style="text-align:left;font-weight:bold">Total Pengeluaran</td>
				<td class="std" style="text-align:right;font-weight:bold"> <?php echo number_format($resultsProfitLoss->data->TOTAL_COST,0,',','.') ?></td>
				<td class="std" style="text-align:right"></td>
				</tr>

		
		<tr>
			<td class="std" style="text-align:left" colspan=4></td>
			<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black">-</td>
			</tr>
			<tr>
			<td class="std" style="text-align:left;font-weight:bold" colspan="3">LABA/RUGI</td>
			<td class="std" style="text-align:right"></td>
			<td class="std" style="text-align:right;font-weight:bold"> <?php echo number_format($resultsProfitLoss->data->PROFIT_LOSS,0,',','.');?></td>
			</tr>
	</tbody>
</table>