<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$searchQuery	= strtoupper($_REQUEST['s']);
$whereClauses	= array("COM.DEL_NBR=0");


if ($_GET['TYPE'] != "") {
	if($_GET['TYPE'] == 1){
		$whereClauses[] = "MAX_DTE <= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
	}else{
		$whereClauses[] = "MAX_DTE <= DATE_SUB(NOW(), INTERVAL 2 MONTH)";
	}
}

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
			COM.CO_NBR LIKE '" . $query . "'
			OR COM.NAME LIKE '" . $query . "'
			OR COM.ACCT_EXEC_NBR LIKE '" . $query . "'
			OR PPL.NAME LIKE '" . $query . "'
		)";
	}
}

$whereClauses = implode(" AND ", $whereClauses);

$query = "SELECT 
	COM.CO_NBR, 
	COM.NAME,
	CONCAT(COM.ADDRESS,', ',CITY_NM) AS ADDRESS,
	COM.PHONE,
	COM.ACCT_EXEC_NBR,
	PPL.NAME AS ACCT_EXEC_NAME,
	MAX_DTE
FROM CMP.COMPANY COM
	INNER JOIN CMP.CITY CTY ON COM.CITY_ID=CTY.CITY_ID
	LEFT OUTER JOIN CMP.PEOPLE PPL ON COM.ACCT_EXEC_NBR = PPL.PRSN_NBR
	LEFT OUTER JOIN (
		SELECT 
			BUY_CO_NBR, MAX(DATE(ORD_TS)) AS MAX_DTE
		FROM CMP.PRN_DIG_ORD_HEAD
		WHERE DEL_NBR = 0
		GROUP BY BUY_CO_NBR
	) ORD ON ORD.BUY_CO_NBR = COM.CO_NBR
WHERE " . $whereClauses . "
GROUP BY COM.CO_NBR";
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
			<th class="sortable" style="text-align:center;">No.</th>
			<th class="sortable" style="text-align:center;">Perusahaan</th>
			<th class="sortable" style="text-align:center;">Alamat</th>
			<th class="sortable">Telpon</th>
			<th class="sortable">Account Executive</th>
			<th class="sortable">Transaksi Terakhir</th>
		</tr>
	</thead>
	<tbody>
	<?php 
		$alt="";
		while ($row = mysql_fetch_array($result)) {
	?>
	<tr <?php echo $alt; ?>>
		<td><?php echo $row['CO_NBR'];?></td>
		<td style='white-space:nowrap;'><?php echo $row['NAME'];?></td>
		<td><?php echo $row['ADDRESS'];?></td>
		<td><?php echo $row['PHONE'];?></td>
		<td style='white-space:nowrap;'><?php echo shortName($row['ACCT_EXEC_NAME']);?></td>
		<td><?php echo $row['MAX_DTE'];?></td>
	</tr>
	<?php 
		if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
	?>
	</tbody>
</table>