<?php
require_once "framework/database/connect.php";
require_once "framework/database/connect-cloud.php";

$searchQuery    = strtoupper($_REQUEST['s']);
$T				= $_GET['T'];
$D				= $_GET['D'];
$whereClauses   = array();

if ($searchQuery != "") {
	$searchQuery	= explode(" ", $searchQuery);
	$T				= $_GET['T'];
	$D				= $_GET['D'];
	$queryColl 		= "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
							  WHERE TABLE_SCHEMA = '".$D."' AND TABLE_NAME = '".$T."'";
	$resultColl		= mysql_query($queryColl);
	$i				= 0;
	
	foreach ($searchQuery as $query) {
		$query = trim($query);
		
		if (empty($query)) {
			continue;
		}
		if (strrpos($query, '%') === false) {
			$query = '%' . $query . '%';
		}
		while($coll=mysql_fetch_array($resultColl)){
			$whereClauses[] = $coll[0]." LIKE '" . $query . "'";
		}$i++;
		
	}
}
$whereClauses = implode(" OR ", $whereClauses);

		
$queryColl 	= "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$D."' AND TABLE_NAME = '".$T."'";
$resultColl	= mysql_query($queryColl);
?>
<table id ='mainTable' class='tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show'>
<thead>
	<tr>
<?php		
while($coll=mysql_fetch_array($resultColl)){
?>
	<th><?php echo $coll[0];?></th>
<?php
	}	
?>
</tr>
</thead>
<tbody>
<?php
	if(empty($searchQuery)){
		$query	= "SELECT * FROM ".$D.".".$T;
	}else {
		$query	= "SELECT * FROM ".$D.".".$T." WHERE (".$whereClauses.")";
		//print_r ($query);
	}
	$result	= mysql_query($query);
	$jum	= mysql_num_fields($result);
	
	while($row=mysql_fetch_array($result)){
?>
		<tr style="cursor:pointer;" onclick="location.href='cloud-list-all-edit.php?K=<?php echo $row[0];?>&&T=<?php echo $T;?>&&D=<?php echo $D;?>';">
		<?php
			for($i=0; $i<$jum; $i++){
		?>
		<td style="text-align:left;">
		<?php echo $row[$i]?></td>
		<?php
			}
		?>
		</tr>
<?php
	}
?>
</tbody>
</table>