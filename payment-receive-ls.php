<?php
require_once "framework/database/connect.php";

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
			PYMT_RCV_NBR LIKE '" . $query . "'
			OR REF_NBR LIKE '" . $query . "'
			OR SHP_CO_NBR LIKE '" . $query . "'
			OR SHP.NAME LIKE '" . $query . "'
			OR PYMT_DESC LIKE '" . $query . "'
		)";
	}
}

$whereClauses[] = "RCV.DEL_NBR=0";
$whereClauses 	= implode(" AND ", $whereClauses);

$query="SELECT 
		PYMT_RCV_NBR,
		PYMT_RCV_DTE,
		SHP_CO_NBR,
		SHP.NAME AS SHP_NAME,
		REF_NBR,
		TND_AMT,
		RCV.PYMT_TYP,
		PYMT_DESC
	FROM RTL.PYMT_RCV RCV
	INNER JOIN CMP.COMPANY SHP ON RCV.SHP_CO_NBR = SHP.CO_NBR
	LEFT JOIN RTL.PYMT_TYP TYP ON RCV.PYMT_TYP = TYP.PYMT_TYP
WHERE ".$whereClauses." 
GROUP BY PYMT_RCV_NBR
ORDER BY PYMT_RCV_NBR DESC";

$result = mysql_query($query);
//echo "<pre>".$query."<br><br>";
if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Data or Number Not Found</div>";
    exit;
}
?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="sortable" style="text-align:center;">No</th>
			<th class="sortable">Referensi</th>
			<th class="sortable">Tanggal</th>
			<th class="sortable">Pengirim</th>
			<th class="sortable">Jumlah</th>
		</tr>
	</thead>
	<tbody>
	<?php 
	$alt;
	while ($row = mysql_fetch_array($result)) { 
	?>
	<tr <?php echo $alt; ?> style ="cursor: pointer;" onclick="location.href='payment-receive-edit.php?PYMT_RCV_NBR=<?php echo $row['PYMT_RCV_NBR'];?>';">
		<td style="text-align:center;"><?php echo $row['PYMT_RCV_NBR'];?></td>
		<td><?php echo $row['REF_NBR'];?></td>
		<td style="text-align:center;"><?php echo $row['PYMT_RCV_DTE'];?></td>
		<td><?php echo $row['SHP_NAME'];?></td>
		<td align="right"><?php echo number_format($row['TND_AMT'],0,'.',',');?></td>
	</tr>
	<?php 
	}
	?>
	</tbody>
</table>