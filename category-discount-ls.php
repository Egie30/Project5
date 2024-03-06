<?php
	include "framework/database/connect.php";
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);
	$whereClause="";
	foreach($searchQ as $searchQuery)
	{
		$whereClause.="(CAT_DISC_NBR LIKE '%".$searchQuery."%' OR CAT_DISC_DESC LIKE '%".$searchQuery."%') AND ";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	
	$query=mysql_query("SELECT CAT_DISC_NBR,CAT_DISC_DESC,CAT_DISC_PCT,CAT_DISC_AMT
							FROM RTL.CAT_DISC
							WHERE ".$whereClause."
							ORDER BY 2 DESC");
	if(mysql_num_rows($query)==0){
		echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}		
	
?>
<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th>No.</th>
			<th>Deskripsi</th>
			<th>Persen</th>
			<th>Jumlah</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$alt="";
	while($row=mysql_fetch_array($query)){
		echo "<tr $alt onclick=".chr(34)."location.href='category-edit.php?CAT_DISC_NBR=".$row['CAT_DISC_NBR']."';".chr(34).">";
		echo "<td style='text-align:center;'>".$row['CAT_DISC_NBR']."</td>";
		echo "<td style='text-align:left;'>".$row['CAT_DISC_DESC']."</td>";
		if($row['CAT_DISC_PCT']==""){$CatDiscPct="";}else{$CatDiscPct=number_format($row['CAT_DISC_PCT'],1,'.',',');}
		if($row['CAT_DISC_AMT']==""){$CatDiscAmt="";}else{$CatDiscAmt=number_format($row['CAT_DISC_AMT'],0,'.',',');}
		echo "<td class='std' style='text-align:right;'>".$CatDiscPct."</td>";
		echo "<td class='std' style='text-align:right;'>".$CatDiscAmt."</td>";
		echo "</tr>";
		}
	?>
	</tbody>
	</table>
	