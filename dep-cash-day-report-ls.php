<?php
	include "framework/database/connect.php";
	
	try {		
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/dep-cash-day-report.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}

	if(count($results->data)==0){
		echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
	
	//echo "<pre>"; print_r($results);

?>
	<table class="table-accounting tablesorter">
	<thead>
		<tr>
		
			<th  style="text-align:center;">No.</th>
			<th  style="text-align:center;">Tanggal Setoran Bank</th>
			<th  style="text-align:center;">Setoran</th>
			<th  style="text-align:center;">Keterangan</th>
			<th  style="text-align:center;">Verifikasi</th>

		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
			foreach ($results->data as $result) {
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='dep-cash-day-report-detail.php?DEP_DTE=".$result->DEP_DTE."&CSH_DAY_NBR=".$result->CSH_DAY_NBR."&TYP=".$result->TYP."';".chr(34).">";
				echo "<td style='text-align:center'>".$result->CSH_DAY_NBR."</td>";
				echo "<td style='text-align:center;'>".$result->DEP_DTE."</td>";
				echo "<td style='text-align:right'>".number_format($result->AMT,0,'.','.')."</td>";
				echo "<td>".$result->KET."</td>";
	?>
				<td style='text-align:center'><input disabled name='VRFD_F' id='VRFD_F'  type='checkbox' class='regular-checkbox'  <?php if($result->VRFD_F =="1"){ echo "checked"; } ?> />&nbsp;
				<label for='VRFD_F'></label></td>
	<?php 
				echo "</tr>";

				$AMT_TOT += $result->AMT;
			}
			echo "<tfoot>";
				echo "<tr class='tr-total'>";
				echo "<td colspan=3 style='text-align:right;font-weight:bold;'>Total Setoran Rp. ".number_format($AMT_TOT,0,'.','.')."</td>";
				echo "<td></td>";
				echo "</tr>";
			echo "</tfoot>";

	?>
	</tbody>
</table>

<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>
<?php buildPagination($results->pagination, "cash-day-report-ls.php");?>

