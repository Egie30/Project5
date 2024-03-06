<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
include "framework/security/default.php";

$Payroll = getSecurity($_SESSION['userID'],"Payroll");

if ($_GET['FLTR_DTE']==''){
	$_GET['FLTR_DTE']=date('n Y');
}

$filter_date=str_replace("+"," ",$_GET['FLTR_DTE']);
if ($filter_date!="") {
	$data		= explode(" ",$filter_date);
	$month	= $data[0];
	$year	= $data[1];
}

$searchQuery    = strtoupper($_REQUEST['s']);
$whereClauses   = array();
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
			ENT.PRSN_NBR LIKE '" . $query . "'
			OR NAME LIKE '" . $query . "'
			OR DATE(EXCPTN_ETRY_TS) LIKE '" . $query . "'
		)";
	}
}

$whereClauses[] = "MONTH(EXCPTN_ETRY_TS)=".$month." AND YEAR(EXCPTN_ETRY_TS)=".$year."";

$whereClauses[] = "ENT.DEL_NBR=0";

$whereClauses = implode(" AND ", $whereClauses);

$query = "SELECT 
		EXCPTN_ETRY_NBR,
		ENT.PRSN_NBR,
		NAME,
		EXCPTN_ETRY_TS,
		DATE(EXCPTN_ETRY_TS) AS EXCPTN_ETRY_DTE,
		TIME(EXCPTN_ETRY_TS) AS EXCPTN_ETRY_TM,
		EXCPTN_ETRY_ACT,
		EXCPTN_ETRY_RSN,
		CLOK_NBR
	FROM PAY.EXCPTN_ETRY ENT
		INNER JOIN CMP.PEOPLE PPL ON ENT.PRSN_NBR = PPL.PRSN_NBR
	WHERE " . $whereClauses . " GROUP BY ENT.EXCPTN_ETRY_NBR ORDER BY 1 ASC";

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
			<th class="sortable" style="text-align:center;width: 5%;">No.</th>
			<th class="sortable" style="text-align:center;width: 8%;">No.Induk</th>
			<th class="sortable" style="text-align:center;">Nama</th>
			<th class="sortable">Tanggal</th>
			<th class="sortable">Jam</th>
			<th class="sortable">Aksi</th>
			<th class="sortable">Alasan</th>
		</tr>
	</thead>
	<tbody>
	<?php while ($row = mysql_fetch_array($result)) { ?>
	
		<tr <?php if ($Payroll<=2){ ?>style="cursor:pointer;" onclick="location.href='exceptional-entry-edit.php?EXCPTN_ETRY_NBR=<?php echo $row['EXCPTN_ETRY_NBR'];?>&CLOK_NBR=<?php echo $row['CLOK_NBR'];?>';" <?php } ?>>
			<td style="text-align:center;"><?php echo $row['EXCPTN_ETRY_NBR'];?></td>
			<td style="text-align:right;"><?php echo $row['PRSN_NBR'];?></td>
			<td style="text-align:left;"><?php echo $row['NAME'];?></td>
			<td style="text-align:left;"><?php echo $row['EXCPTN_ETRY_DTE'];?></td>
			<td style="text-align:left;"><?php echo $row['EXCPTN_ETRY_TM'];?></td>
			<td style="text-align:left;"><?php echo $row['EXCPTN_ETRY_ACT'];?></td>
			<td style="text-align:left;"><?php echo $row['EXCPTN_ETRY_RSN'];?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>