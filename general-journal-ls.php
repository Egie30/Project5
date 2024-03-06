<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$bookNumber	= $_GET['BK_NBR'];
$Actg		= $_GET['ACTG'];

$_GET['ACTG'] 	= $Actg;
$_GET['BK_NBR']	= $bookNumber;

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/journal.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

?>
<br />
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<?php 
			if($locked == 0) { echo '<th class="sortable" style="text-align:center;">Rekening</th>'; }
			?>
			<th class="sortable">Kode</th>
			<th class="sortable">Referensi</th>
			<th class="sortable">Deskripsi</th>
			<th class="sortable sortable-date-dmy">Tanggal Jurnal</th>
			<th class="sortable sortable-sortDutchCurrencyValues" style="text-align:right;">Debit</th>
			<th class="sortable sortable-sortDutchCurrencyValues" style="text-align:right;">Kredit</th>
		</tr>
	</thead>
	<tbody>
		<?php

		$i	= 1;
		
		foreach ($results->data as $result) { 
			
			$i++;
			?>
			<tr style="cursor:pointer;" onclick="location.href='general-journal-edit.php?BK_NBR=<?php echo $bookNumber; ?>&GL_NBR=<?php echo $result->GL_NBR;?>&ACTG=<?php echo $Actg; ?>';">
			<?php 
			if($locked == 0) { echo '<td style="text-align:center;">'.$result->ACTG_TYP.'</td>'; }
			?>
			<td class="std" style="text-align:center"><?php echo $result->GL_NBR;?></td>
			<td class="std" style="text-align:left"><?php echo $result->REF;?></td>
			<td class="std" style="text-align:left"><?php echo $result->GL_DESC;?></td>
			
			<td class="std" style="text-align:center"><?php echo ($result->GL_DTE != "") ? parseNormalDate($result->GL_DTE) : "-";?></td>
			<td class="std" style="text-align:right"> <?php echo number_format($result->GL_DEB,0,',','.');?></td>
			<td class="std" style="text-align:right"> <?php echo number_format($result->GL_CRT,0,',','.');?></td>
			
			</tr>
		<?php  } 
		
		?>
		
	</tbody>
</table>

