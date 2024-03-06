<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
?>
<table class="table-accounting">
	<thead style="color:blue;">
		<tr>
			<th>Tanggal</th>
			<th>Referensi</th>
			<th>Deskripsi</th>
			<th>Total Debit</th>
			<th>Total Kredit</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$_GET['LIMIT'] = -1;

		try {
			$_GET['GROUP'] = array("DAY");

			ob_start();
			include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/journal.php";

			$resultsDaily = json_decode(ob_get_clean());
		} catch (\Exception $ex) {
			ob_end_clean();
		}

		foreach ($resultsDaily->data as $resultDaily) { ?>
			<tr style="cursor:pointer;" onclick="location.href='general-journal.php?CD_SUB_NBR=<?php echo $resultDaily->CD_SUB_NBR;?>&DTE=<?php echo $resultDaily->CRT_DTE;?>&BK_NBR=<?php echo $_GET['BK_NBR']; ?>'">
			<td class="std" style="text-align:left;font-weight:bold"><?php echo parseNormalDate($resultDaily->CRT_DTE);?></td>
			<td class="std" style="text-align:left"></td>
			<td class="std" style="text-align:left"></td>
			<td class="std" style="text-align:right;font-weight:bold">Rp. <?php echo number_format($resultDaily->GL_DEB,0,',','.');?></td>
			<td class="std" style="text-align:right;font-weight:bold">Rp. <?php echo number_format($resultDaily->GL_CRT,0,',','.');?></td>
			</tr>
			<?php 
			try {
				$_GET['CD_SUB_NBR'] = $resultDaily->CD_SUB_NBR;
				$_GET['GROUP'] = array("GL_NBR");

				ob_start();
				include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/journal.php";

				$results = json_decode(ob_get_clean());
			} catch (\Exception $ex) {
				ob_end_clean();
			}

			foreach ($results->data as $result) { ?>
				<tr style="cursor:pointer;" onclick="location.href='general-journal-edit.php?GL_NBR=<?php echo $result->GL_NBR;?>'">
				<td class="std" style="text-align:center"></td>
				<td class="std" style="text-align:left"><?php echo $result->REF;?></td>
				<td class="std" style="text-align:left"><?php echo $result->GL_DESC;?></td>
				<td class="std" style="text-align:right">Rp. <?php echo number_format($result->GL_DEB,0,',','.');?></td>
				<td class="std" style="text-align:right">Rp. <?php echo number_format($result->GL_CRT,0,',','.');?></td>
				</tr>
			<?php } ?>
        <?php } ?>
	</tbody>
</table>