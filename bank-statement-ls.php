<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
include "framework/security/default.php";

$security 			= getSecurity($_SESSION['userID'], "Accounting");

$searchQuery    = strtoupper($_REQUEST['s']);
$whereClauses   = array();
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
			BS.BNK_STMT_DTE LIKE '" . $query . "'
			OR BS.BNK_STMT_TYP LIKE '" . $query . "'
			OR BS.BNK_STMT_DESC LIKE '" . $query . "'
			OR BS.NTE LIKE '" . $query . "'
		)";
	}
}

//$whereClauses[] = "TB.BK_NBR=".$bookNumber;

$whereClauses[] = "BS.DEL_NBR = 0";

if ($Actg != 0) {
	$whereClauses[] = "BS.ACTG_TYP = ".$Actg." ";
}

$whereClauses = implode(" AND ", $whereClauses);

if(($locked==1)||($_COOKIE["LOCK"] == "LOCK")){ $displaylock = "display:none;"; }

$query = "SELECT 
		BS.BNK_STMT_NBR,
		BS.BNK_STMT_DTE,
		BS.BNK_STMT_TYP,
		BS.BNK_STMT_DESC,
		BS.BNK_STMT_AMT,
		BS.NTE,
		BS.VRFCTN_F,
		BS.ACTG_TYP
	FROM RTL.BNK_STMT BS
	WHERE " . $whereClauses . " 
	GROUP BY BS.BNK_STMT_NBR ORDER BY 1 ASC";

//echo "<pre>".$query;

$result = mysql_query($query);


if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
    exit;
}

?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="sortable">No.</th>
			<th class="sortable">Tanggal</th>
			<th class="sortable">Tipe</th>
			<?php 
				if($security <= 1) {	echo '<th class="sortable">Deskripsi</th>'; }
			?>
			
			<th class="sortable">Jumlah</th>
			<?php 
				if($security <= 0) {	echo '<th class="sortable">Verifikasi</th>'; }
			?>
			<th class="sortable">Catatan</th>
			<th style="<?php echo $displaylock; ?>" class="sortable">Rekening</th>
		</tr>
	</thead>
	<tbody>
	<?php while ($row = mysql_fetch_array($result)) {
		?>
		<tr style="cursor:pointer;" onclick="location.href='bank-statement-edit.php?BNK_STMT_NBR=<?php echo $row['BNK_STMT_NBR'];?>';">
			<td style="text-align:left;"><?php echo $row['BNK_STMT_NBR'];?></td>
			<td style="text-align:left;"><?php echo $row['BNK_STMT_DTE'];?></td>
			<td style="text-align:left;"><?php echo $row['BNK_STMT_TYP'];?></td>
			
			<?php 
				if($security <= 1) {	
					echo '<td style="text-align:left;">'.$row['BNK_STMT_DESC'].'</td>'; 
				}
			?>
			
			<td style="text-align:right;"><?php echo number_format($row['BNK_STMT_AMT'],0);?></td>
			
			<?php 
				if($security <= 0) { ?>
					<td style="text-align:center;"><input disabled name='VRFCTN_F' id='VRFCTN_F'  type='checkbox' class='regular-checkbox' <?php if($row['VRFCTN_F'] == 1){ echo "checked"; } ?> />&nbsp;
					<label for='VRFCTN_F'></label></td>
			<?php }	?>
			
			<td style="text-align:left;"><?php echo $row['NTE'];?></td>
			<td style="text-align:center;<?php echo $displaylock; ?>"><?php echo $row['ACTG_TYP'];?></td>
			</tr>
	<?php } ?>
	</tbody>
</table>