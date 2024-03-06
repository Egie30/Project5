<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";



$searchQuery    	= strtoupper($_REQUEST['s']);
$whereClauses   	= array();
$BankStatementType	= $_GET['BNK_STMT_TYP'];
$Actg				= $_GET['ACTG'];


if (empty($_GET['BEG_DT'])) {
	$BeginDate	= date('Y-m-01');
}
else {
	$BeginDate			= $_GET['BEG_DT'];
}


if (empty($_GET['END_DT'])) {
	$EndDate	= date('Y-m-d');
}
else {
	$EndDate			= $_GET['END_DT'];
}

$whereClauses[] = "BS.DEL_NBR = 0";

if ($Actg != 0) {
	$whereClauses[] = "BS.ACTG_TYP = ".$Actg." ";
}

if ($BankStatementType != "") {
	$whereClauses[] = "BS.BNK_STMT_TYP = '".$BankStatementType."' ";
}

if ($BeginDate != "") {
	$whereClauses[] = "BS.BNK_STMT_DTE >= '".$BeginDate."' ";
}

if ($EndDate != "") {
	$whereClauses[] = "BS.BNK_STMT_DTE <= '".$EndDate."' ";
}

$whereClauses = implode(" AND ", $whereClauses);

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
	GROUP BY BS.BNK_STMT_NBR ORDER BY 2 ASC";

//echo "<pre>".$query;

$result = mysql_query($query);


if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
    exit;
}

?>

<table style="width:150px !important;" class="table-accounting tablesorter std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="nosort" width="15%">No.</th>
			<th class="nosort" width="15%">Tanggal</th>
			<th class="nosort" width="15%">Tipe</th>
			<th class="nosort" width="40%">Deskripsi</th>
			<th class="nosort" width="5%">Jumlah</th>
			<th class="nosort" width="5%">Verifikasi</th>
			<th class="nosort" width="30%">Catatan</th>
			<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
			<th class="nosort" width="5%">Rekening</th>
			<?php } ?>	
		</tr>
	</thead>
	<tbody>

	<?php 
	
	while ($row = mysql_fetch_array($result)) {
		$total += $row['BNK_STMT_AMT']; ?>
		<tr style="cursor:pointer;" onclick="location.href='bank-statement-edit.php?BNK_STMT_NBR=<?php echo $row['BNK_STMT_NBR'];?>';">
			<td class="std" width="15%" style="text-align:left;" ><?php echo $row['BNK_STMT_NBR'];?></td>
			<td class="std" width="15%" style="text-align:left;" ><?php echo $row['BNK_STMT_DTE'];?></td>
			<td class="std" width="15%" style="text-align:left;" ><?php echo $row['BNK_STMT_TYP'];?></td>
			<td class="std" width="40%" style="text-align:left;" ><?php echo $row['BNK_STMT_DESC'];?></td>
			<td class="std" width="5%" style="text-align:right;" ><?php echo number_format($row['BNK_STMT_AMT'],0);?></td>
			<td class="std" width="5%" style="text-align:center;" ><input disabled name='VRFCTN_F' id='VRFCTN_F'  type='checkbox' class='regular-checkbox' <?php if($row['VRFCTN_F'] == 1){ echo "checked"; } ?> />&nbsp;
			<label for='VRFCTN_F'></label></td>
			<td class="std" width="30%" style="text-align:left;" ><?php echo $row['NTE'];?></td>
			<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
			<td class="std" width="5%" style="text-align:center;" ><?php echo $row['ACTG_TYP'];?></td>
			<?php } ?>	
			</tr>
	<?php } 
	
	?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">
			<td class="std" style="text-align:right;font-weight:bold;" colspan=4>Total:</td>
			<?php 
			echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($total, 0, ',', '.').'</td>';
			?>
		</tr>
	</tfoot>
	</table>

