<?php
	include "framework/database/connect.php";
	
	try {		
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/cash-day-report.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
	if(count($results->data)==0){
		echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}

?>
	<table class="table-accounting tablesorter">
	<thead>
		<tr>
		
			<th class="sortable" style="text-align:right;">No.</th>
			<th class="sortable">Tanggal</th>
			<th class="sortable">Shift</th>
			<th class="sortable">Cek/Giro</th>
			<th class="sortable">Uang Kontan</th>
			<th class="sortable">Uang di Laci</th>
			<th class="sortable">Total</th>
			<th class="sortable">Kas Register</th>
			<th class="sortable">Koreksi</th>
			<th class="sortable">Verifikasi</th>

		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";

			foreach ($results->data as $result) {
		
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='cash-day-report-edit.php?CSH_DAY_DTE=".$result->CSH_DAY_DTE."&S_NBR=".$result->S_NBR."&CSH_DAY_NBR=".$result->CSH_DAY_NBR."&TYP=".$_GET['TYP']."';".chr(34).">";
				echo "<td style='text-align:center'>".$result->CSH_DAY_NBR."</td>";
				echo "<td>".$result->CSH_DAY_DTE."</td>";
				echo "<td style='text-align:center;'>".$result->S_NBR."</td>";
				echo "<td style='text-align:right'>".number_format($result->CHK_AMT,0,'.','.')."</td>";
				echo "<td style='text-align:right'>".number_format($result->CSH_AMT,0,'.','.')."</td>";
				echo "<td style='text-align:right'>".number_format($result->CSH_IN_DRWR,0,'.','.')."</td>";
				echo "<td style='text-align:right'>".number_format($result->TOT_AMT,0,'.','.')."</td>";
				echo "<td style='text-align:right'>".number_format($result->CSH_REG,0,'.','.')."</td>";
				echo "<td style='text-align:right'>".number_format($result->CORRTN_AMT,0,'.','.')."</td>";
			?>
				
				<td style='text-align:center'><input disabled name='VRFD_F' id='VRFD_F'  type='checkbox' class='regular-checkbox'  <?php if($result->VRFD_F =="1"){ echo "checked"; } ?> />&nbsp;
				<label for='VRFD_F'></label></td>
			
			<?php 
				//echo "<td>".$result->VRFD_F."</td>";
				echo "</tr>";
				$CHK_AMT_TOT +=$result->CHK_AMT;
				$CSH_AMT_TOT +=$result->CSH_AMT;
				$CSH_IN_DRWR_TOT +=$result->CSH_IN_DRWR;
				$TOT_AMT_TOT +=$result->TOT_AMT;
				$CSH_REG_TOT +=$result->CSH_REG;
				$CORRTN_AMT_TOT +=$result->CORRTN_AMT;

			}
			if ($typ =='CAR'){
				echo "<tfoot>";
				echo "<tr class='tr-total'>";
					echo "<td colspan=3 style='text-align:right;font-weight:bold;'>Total</td>";
					//echo "<td style='text-align:right;font-weight:bold;'>".number_format($CHK_AMT_TOT,0,'.','.')."	</td>";
					echo "<td></td>";
					echo "<td style='text-align:right;font-weight:bold;'>".number_format($CSH_AMT_TOT,0,'.','.')."</td>";
					//echo "<td style='text-align:right;font-weight:bold;'>".number_format($CSH_IN_DRWR_TOT,0,'.','.')."</td>";
					echo "<td></td>";
					//echo "<td style='text-align:right;font-weight:bold;'>".number_format($TOT_AMT_TOT,0,'.','.')."</td>";
					echo "<td></td>";
					//echo "<td style='text-align:right;font-weight:bold;'>".number_format($CSH_REG_TOT,0,'.','.')."</td>";
					echo "<td></td>";
					//echo "<td style='text-align:right;font-weight:bold;'>".number_format($CORRTN_AMT_TOT,0,'.','.')."</td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
				echo "</tr>";
				echo "</tfoot>";
			}
	?>
	</tbody>
</table>
<?php buildPagination($results->pagination, "cash-day-report-ls.php");?>
