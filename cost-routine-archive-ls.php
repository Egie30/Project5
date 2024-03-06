<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

try {
	
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/cost-routine.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}

if($_GET['GROUP'] == "YEAR") {	$header = 'Tahun';	}
	else if($_GET['GROUP'] == "MONTH") { $header = 'Bulan';	$title = 'Tahun '.$_GET['YEARS'];}
		else if($_GET['GROUP'] == "DAY") { $header = 'Tanggal';	$title = 'Bulan '.$_GET['MONTHS'].' Tahun '.$_GET['YEARS']; }
			else { $header = 'Nomor Nota'; $title = 'Tanggal '.$_GET['DAYS'].' Bulan '.$_GET['MONTHS'].' Tahun '.$_GET['YEARS']; }

echo '<h3>'.$title.'</h3>';
			
?>


<table class="table-accounting tablesorter">
	<thead>
		<tr>
			<?php 
				echo '<th class="sortable">Nomor</th>';		
				echo '<th class="sortable">'.$header.'</th>';
				if($_GET['GROUP'] == 'UTL_NBR') { 
					echo '<th class="sortable">Petugas</th>';
					echo '<th class="sortable">Client</th>';
					echo '<th class="sortable">Pengeluaran</th>';
				}
				echo '<th class="sortable">Jumlah</th>';
				
				
				foreach ($results->utility as $key => $utility) {
					if ($key == count($results->utility) - 1) {
						// Don't generate unknown sub category automatically
						break;
					}
								
					$utilityDesc 	= $utility->UTL_DESC;
					
					echo '<th class="sortable">'.$utilityDesc.'</th>';			
				}

			?>
			
			
		</tr>
	</thead>
	<tbody>
		<?php
		$i = $_GET['page'] > 1 ? ($_GET['page'] - 1) * $_GET['LIMIT'] : 0;
		$i++;

		foreach ($results->data as $data) {
			$group = "";
			if($_GET['GROUP'] == "YEAR") { 
				$link = "location.href='cost-routine-archive.php?ACTG=0&YEARS=".$data->UTL_YEAR."&GROUP=MONTH&CO_NBR=".$_GET['CO_NBR']."'";
				}
				else if($_GET['GROUP'] == "MONTH") { 
					$link = "location.href='cost-routine-archive.php?ACTG=0&YEARS=".$data->UTL_YEAR."&MONTHS=".$data->UTL_MONTH."&GROUP=DAY&CO_NBR=".$_GET['CO_NBR']."'";
				}
					else if($_GET['GROUP'] == "DAY") { 
						$link = "location.href='cost-routine-archive.php?ACTG=0&YEARS=".$data->UTL_YEAR."&MONTHS=".$data->UTL_MONTH."&DAYS=".$data->UTL_DAY."&GROUP=UTL_NBR&CO_NBR=".$_GET['CO_NBR']."'";
					}
					else {
						$link = "location.href='utility-edit.php?ACTG=0&YEARS=".$data->UTL_YEAR."&MONTHS=".$data->UTL_MONTH."&DAYS=".$data->UTL_DAY."&UTL_NBR=".$data->UTL_NBR."&CO_NBR=".$_GET['CO_NBR']."'";
					}
			?>
			
			<tr style="cursor:pointer" onclick="<?php echo $link; ?>">
				<td class="std" style="text-align:center"><?php echo $i;?></td>
				<td class="std" style="text-align:left;white-space:nowrap">
				<?php 
				if($_GET['GROUP'] == "YEAR") {	echo $data->UTL_YEAR;	}
					else if($_GET['GROUP'] == "MONTH") { echo $data->UTL_MONTHNAME;	}
						else if($_GET['GROUP'] == "DAY") { echo $data->UTL_DTE;	}
							else { echo $data->UTL_NBR; }
					
				?>
				</td>
				<?php if($_GET['GROUP'] == 'UTL_NBR') { ?>
					<td class="std" style="text-align:left"><?php echo $data->PPL_NAME;?></td>
					<td class="std" style="text-align:left"><?php echo $data->COM_NAME;?></td>
					<td class="std" style="text-align:left"><?php echo $data->UTL_DESC;?></td>
				<?php } ?>
				
				<td class="std" style="text-align:right"><?php echo number_format($data->TOT_SUB, 0, ',', '.');?></td>
				
				<?php
				
				foreach ($results->utility as $key => $utility) {
					if ($key == count($results->utility) - 1) {
						// Don't generate unknown sub category automatically
						break;
					}
								
					$utilitySub		= 'TOT_SUB_'.$utility->UTL_TYP;
					
					echo '<td class="std" style="text-align:right">'.number_format($data->$utilitySub, 0, ',', '.').'</td>';			
				}
				
				?>
			</td>
			</tr>
			<?php
			$i++;
		}
		?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">
			<?php if($_GET['GROUP'] == 'UTL_NBR') { $span = 5; } else { $span = 2; }?>
			<td class="std" style="text-align:right;font-weight:bold;" colspan="<?php echo $span; ?>">Total:</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_SUB, 0, ',', '.');?></td>
			<?php
			foreach ($results->utility as $key => $utility) {
				if ($key == count($results->utility) - 1) {
						// Don't generate unknown sub category automatically
						break;
					}
					
				$utilitySub		= 'TOT_SUB_'.$utility->UTL_TYP;
					
				echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($results->total->$utilitySub, 0, ',', '.').'</td>';			
				}
			?>
		</tr>
	</tfoot>
	</table>


<?php buildPagination($results->pagination, "cost-routine-archive-ls.php"); ?>
