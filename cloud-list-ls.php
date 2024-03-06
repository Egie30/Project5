<?php
require_once "framework/database/connect.php";
require_once "framework/database/connect-cloud.php";

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
			TBL_NM LIKE '" . $query . "'
			OR DB_NM LIKE '" . $query . "'
			OR COL_NM LIKE '" . $query . "'
			OR UPD_TS LIKE '" . $query . "'
		)";
	}
}
$whereClauses = implode("", $whereClauses);
?>
	<table id ='mainTable' class='tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show'>
	<thead>
		<tr>
		<th>Nama Tabel</th>
		<th>Nama Database</th>
		<th>Nama Kolom</th>
		<th>Jum Kolom</th>
		<th>Tanggal Update</th>
		</tr>
	</thead>
	
<?php
	$query	= "SELECT SYNC.TBL_NM, SYNC.DB_NM, SYNC.COL_NM, SYNC.UPD_TS FROM ".$NST.".SYNC_LIST SYNC WHERE ";
	if (empty($searchQuery)){
		 $query.= "SYNC.SYNC_DIR_TYP='D' ORDER BY 2";
	}else {
		$query.= $whereClauses." AND SYNC.SYNC_DIR_TYP='D' ORDER BY 2";
	}
	$result	= mysql_query($query,$cloud);
	
	while ($row	= mysql_fetch_array($result)){
		$TBL_NM[]	= $row['TBL_NM'];
		$DB_NM[]	= $row['DB_NM'];
		$COL_NM[] 	= $row['COL_NM'];
		$UPD_TS[] 	= $row['UPD_TS'];
		$countRow	= count($row);
		
		$queryFields  = "SELECT COUNT(*) AS JUM FROM ".$row['DB_NM'].".".$row['TBL_NM'];
		$resultFields = mysql_query($queryFields);
		
		$fields		  = mysql_fetch_array ($resultFields);
		$JUM[]		  = $fields['JUM'];
	}
?>
	<tbody> 	
<?php
		
		for($i=0;$i<$countRow; $i++){	
?>
		<tr style="cursor:pointer;" onclick="location.href='cloud-list-all.php?T=<?php echo $TBL_NM[$i];?>&&D=<?php echo $DB_NM[$i];?>';">
		<td style="text-align:left;" ><?php echo $TBL_NM[$i];?></td>
		<td style="text-align:left;"><?php echo $DB_NM[$i];?></td>
		<td style="text-align:left;"><?php echo $COL_NM[$i];?></td>
		<td style="text-align:center;"><?php echo $JUM[$i];?></td>		
		<td style="text-align:left;"><?php echo $UPD_TS[$i];?></td>
		</tr>
<?php	
		}
?>
	</tbody>
	</table>