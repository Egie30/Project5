<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$Actg	= $_GET['ACTG'];

?>

<table class="table-accounting">
	<thead>
		<tr>
			<th class="sortable">Tipe</th>
			<th class="sortable">Kode Rekening</th>
			<th class="sortable">Nama Rekening</th>
			<th class="sortable">Total Debit</th>
			<th class="sortable">Total Kredit</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$_GET['LIMIT'] = -1;

		try {
			$_GET['GROUP'] = array("CD_CAT_NBR");
			ob_start();
			include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/journal.php";

			$resultsCategory = json_decode(ob_get_clean());
		} catch (\Exception $ex) {
			ob_end_clean();
		}

        foreach ($resultsCategory->data as $resultCategory) { ?>
			<tr style="cursor:pointer;" onclick="location.href='general-ledger-detail.php?CD_CAT_NBR=<?php echo $resultCategory->CD_CAT_NBR;?>&BK_NBR=<?php echo $_GET['BK_NBR']; ?>&ACTG=<?php echo $Actg; ?>'">
			<td class="std" style="text-align:left;font-weight:bold"><?php echo $resultCategory->CD_CAT_DESC;?></td>
			<td class="std" style="text-align:left"></td>
			<td class="std" style="text-align:left"></td>
			<td class="std" style="text-align:right;font-weight:bold"> <?php echo number_format($resultCategory->GL_DEB,0,',','.');?></td>
			<td class="std" style="text-align:right;font-weight:bold"> <?php echo number_format($resultCategory->GL_CRT,0,',','.');?></td>
			</tr>
			<?php 
			try {
				$_GET['CD_CAT_NBR'] = $resultCategory->CD_CAT_NBR;;
				$_GET['GROUP'] = array("CD_SUB_NBR");

				ob_start();
				include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/journal.php";

				$results = json_decode(ob_get_clean());
			} catch (\Exception $ex) {
				ob_end_clean();
			}

			foreach ($results->data as $result) { ?>
				<tr style="cursor:pointer;" onclick="location.href='general-ledger-detail.php?CD_SUB_NBR=<?php echo $result->CD_SUB_NBR;?>&BK_NBR=<?php echo $_GET['BK_NBR']; ?>'">
				<td class="std" style="text-align:center"></td>
				<td class="std" style="text-align:left"><?php echo $result->ACC_NBR;?></td>
				<td class="std" style="text-align:left"><?php echo $result->CD_DESC . ' - ' . $result->CD_SUB_DESC;?></td>
				<td class="std" style="text-align:right"> <?php echo number_format($result->GL_DEB,0,',','.');?></td>
				<td class="std" style="text-align:right"> <?php echo number_format($result->GL_CRT,0,',','.');?></td>
				</tr>
			<?php } ?>
			<tr>
				<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black" colspan="5"></td>
			</tr>	
		<?php } ?>
		
		
	</tbody>
</table>