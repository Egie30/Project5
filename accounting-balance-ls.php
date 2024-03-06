<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$bookNumber		= $_GET['BK_NBR'];

$searchQuery    = strtoupper($_REQUEST['s']);
$whereClauses   = array();
$plusMode 		= $_SESSION['PLUS_MODE'];
$Actg			= $_GET['ACTG'];

if ($searchQuery != "") {
	$searchQuery = explode(" ", $searchQuery);

	foreach ($searchQuery as $query) {
		$query = trim($query);

		if (empty($query)) {
			continue;
		}

		if (strrpos($query, '%') === false) {
			$query = '%' . $query . '%';
		}
		$whereClauses[] = "(
			TB.CD_SUB_NBR LIKE '" . $query . "'
			OR CD.CD_DESC LIKE '" . $query . "'
			OR CD.CD_SUB_DESC LIKE '" . $query . "'
		)";
	}
}

$whereClauses[] = "TB.BK_NBR=".$bookNumber;

$whereClauses[] = "TB.DEL_NBR=0";

if ($locked == 1) {
	$whereClauses[] = "ACTG_TYP = 2";
}

if($Actg != 0) {
$whereClauses[] = "TB.ACTG_TYP = ".$Actg." ";
}

$whereClauses = implode(" AND ", $whereClauses);

$query = "SELECT TB_NBR,
		TB.BK_NBR,
		CD.ACC_NBR,
		CD.ACC_DESC,
		CD.CD_CAT_NBR,
		CD.CD_CAT_DESC,
		CD.CD_NBR,
		CD.CD_ACC_NBR,
		CD.CD_DESC,
		CD.CD_SUB_NBR,
		CD.CD_SUB_ACC_NBR,
		CD.CD_SUB_DESC,
		SUM(COALESCE(TB.DEB,0)) AS DEB,
		SUM(COALESCE(TB.CRT,0)) AS CRT,
		TB.DEL_NBR,
		TB.UPD_NBR,
		TB.UPD_TS,
		TB.ACTG_TYP
	FROM RTL.ACCTG_TB TB
		INNER JOIN RTL.ACCTG_BK BK ON BK.BK_NBR=TB.BK_NBR
		INNER JOIN (
			SELECT SUB.CD_SUB_NBR, SUB.CD_SUB_ACC_NBR, SUB.CD_SUB_DESC, ACC.CD_NBR, ACC.CD_ACC_NBR, ACC.CD_DESC, CAT.CD_CAT_NBR, CAT.CD_CAT_DESC,
				CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
				CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC
			FROM RTL.ACCTG_CD_SUB SUB
				INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
				INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
			GROUP BY SUB.CD_SUB_NBR
		) CD ON CD.CD_SUB_NBR=TB.CD_SUB_NBR
	WHERE " . $whereClauses . " GROUP BY TB.TB_NBR ORDER BY 1 ASC";

//echo "<pre>".$query;

$result = mysql_query($query);


if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
    exit;
}

$totalDebit = 0;
$totalCredit = 0;
?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<?php 
			if($locked == 0) { echo '<th class="sortable" style="text-align:center;">Rekening</th>'; }
			?>
			<th class="sortable">Kode Rekening</th>
			<th class="sortable">Deskripsi</th>
			<th class="sortable">Debit</th>
			<th class="sortable">Kredit</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$i = 1;
	
	while ($row = mysql_fetch_array($result)) {
		$totalDebit += $row['DEB'];
		$totalCredit += $row['CRT'];
		
	?>
		<tr style="cursor:pointer;" onclick="location.href='accounting-balance-edit.php?BK_NBR=<?php echo $bookNumber; ?>&TB_NBR=<?php echo $row['TB_NBR'];?>&ACTG=<?php echo $Actg; ?>';">
			<?php 
			if($locked == 0) { echo '<td style="text-align:center;">'.$row['ACTG_TYP'].'</td>'; }
			?>
			
			<td style="text-align:left;"><?php echo $row['ACC_NBR'];?></td>
			<td style="text-align:left;"><?php echo $row['ACC_DESC'];?></td>
			<td style="text-align:right;"><?php echo number_format($row['DEB'], 0, ",", ".");?></td>
			<td style="text-align:right;"><?php echo number_format($row['CRT'], 0, ",", ".");?></td>
		</tr>
	<?php 
	$i++;
	} 
	?>
	</tbody>
	<tfoot>
			
			<tr style="background-color:#dddddd;">
			<?php if($locked == 0) { $colspan = "colspan=2"; } ?>
			<td class="std" style="text-align:left" <?php echo $colspan;	?>>
				<?php if ($totalDebit != $totalCredit) {
					echo "Total saldo awal tidak sama. Silahkan periksa kembali.";
				} ?>
			</td>
			
			
			<td class="std" style="text-align:right;font-weight:bold;">Total: </td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($totalDebit, 0, ",", ".");?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($totalCredit, 0, ",", ".");?></td>
		</tr>
	</tfoot>
</table>