<?php
	include "framework/database/connect.php";
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);
	$whereClause="";
	foreach($searchQ as $searchQuery)
	{
		$whereClause.="(CAT_NBR LIKE '%".$searchQuery."%' OR CAT_DESC LIKE '%".$searchQuery."%') AND ";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	
	$query=mysql_query("SELECT CAT_NBR, CAT_DESC
							FROM RTL.CAT
							WHERE DEL_NBR=0 AND (".$whereClause.")
							ORDER BY 2 DESC");
	if(mysql_num_rows($query)==0){
		echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}		
	
?>
<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th style="text-align:center;">No</th>
			<th>Deskripsi</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$alt="";
	while($row=mysql_fetch_array($query)){
		echo "<tr $alt onclick=".chr(34)."location.href='category-edit.php?CAT_NBR=".$row['CAT_NBR']."';".chr(34).">";
		echo "<td style='text-align:center;'>".$row['CAT_NBR']."</td>";
		echo "<td style='text-align:left;'>".$row['CAT_DESC']."</td>";
		echo "</tr>";
		}
	?>
	</tbody>
	</table>
	
