<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$Type			= $_GET['TYP'];

try {
	
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/retail-report.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}
//echo "<pre>";
//print_r($results);
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
				echo '<th class="sortable" style="text-align:center;">Nomor</th>';		
				echo '<th class="sortable" style="text-align:center;">'.$header.'</th>';
				echo '<th class="sortable" style="text-align:center;">Total</th>';
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
				$link = "location.href='retail-report-archive.php?ACTG=0&YEARS=".$data->CSH_YEAR."&GROUP=MONTH&CO_NBR=".$_GET['CO_NBR']."&TYP=".$Type."'";
				}
				else if($_GET['GROUP'] == "MONTH") { 
					$link = "location.href='retail-report-archive.php?ACTG=0&YEARS=".$data->CSH_YEAR."&MONTHS=".$data->CSH_MONTH."&GROUP=DAY&CO_NBR=".$_GET['CO_NBR']."&TYP=".$Type."'";
				}
					else if($_GET['GROUP'] == "DAY") { 
						$link = "location.href='retail-report-archive.php?ACTG=0&YEARS=".$data->CSH_YEAR."&MONTHS=".$data->CSH_MONTH."&DAYS=".$data->CSH_DAY."&GROUP=TRSC_NBR&CO_NBR=".$_GET['CO_NBR']."&TYP=".$Type."'";
					}
					else {
						$link = "";
					}
			?>
			
			<tr style="cursor:pointer" onclick="<?php echo $link; ?>">
				<td class="std" style="text-align:center"><?php echo $i;?></td>
				<td class="std" style="text-align:left;white-space:nowrap">
				<?php 
				if($_GET['GROUP'] == "YEAR") {	echo $data->CSH_YEAR;	}
					else if($_GET['GROUP'] == "MONTH") { echo $data->CSH_MONTHNAME;	}
						else if($_GET['GROUP'] == "DAY") { echo $data->CSH_DTE;	}
							else { echo $data->TRSC_NBR; }
					
				?>
				</td>
				<td class="std" style="text-align:right"><?php echo number_format($data->TND_AMT, 0, ',', '.');?></td>
			</td>
			</tr>
			<?php
			$i++;
		}
		?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">
			<td class="std" style="text-align:right;font-weight:bold;" colspan="2">Total:</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TND_AMT, 0, ',', '.');?></td>
		</tr>
	</tfoot>
	</table>


<?php buildPagination($results->pagination, "retail-report-archive-ls.php"); ?>
