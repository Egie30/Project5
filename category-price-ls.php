<?php
	include "framework/database/connect.php";
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);
	$whereClause="";
	foreach($searchQ as $searchQuery)
	{
		$whereClause.="(CAT_PRC_NBR LIKE '%".$searchQuery."%' OR CAT_PRC_DESC LIKE '%".$searchQuery."%') AND ";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	
	$query=mysql_query("SELECT CAT_PRC_NBR,CAT_PRC_DESC,CAT_PRC_PCT,CAT_PRC_AMT,CAT_PRC_RND,CAT_PRC_LES
							FROM RTL.CAT_PRC
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
			<th>No.</th>
			<th>Deskripsi</th>
			<th>Persen</th>
			<th>Jumlah</th>
			<th>Pembulatan</th>
			<th>Pengurangan</th>			
		</tr>
	</thead>
	<tbody>
	<?php
	$alt="";
	while($row=mysql_fetch_array($query)){
		echo "<tr $alt onclick=".chr(34)."location.href='category-price-edit.php?CAT_PRC_NBR=".$row['CAT_PRC_NBR']."';".chr(34).">";
		echo "<td style='text-align:center;'>".$row['CAT_PRC_NBR']."</td>";
		echo "<td style='text-align:left;'>".$row['CAT_PRC_DESC']."</td>";
		if($row['CAT_PRC_PCT']==""){$CatDiscPct="";}else{$CatDiscPct=number_format($row['CAT_PRC_PCT'],1,'.',',');}
		if($row['CAT_PRC_AMT']==""){$CatDiscAmt="";}else{$CatDiscAmt=number_format($row['CAT_PRC_AMT'],0,'.',',');}
			if($row['CAT_PRC_RND']==""){$CatPrcRnd="";}else{$CatPrcRnd=number_format($row['CAT_PRC_RND'],0,'.',',');}
			if($row['CAT_PRC_LES']==""){$CatPrcLes="";}else{$CatPrcLes=number_format($row['CAT_PRC_LES'],0,'.',',');}		
		echo "<td class='std' style='text-align:right;'>".$CatDiscPct."</td>";
		echo "<td class='std' style='text-align:right;'>".$CatDiscAmt."</td>";
		echo "<td class='std' style='text-align:right;'>".$CatPrcRnd."</td>";
		echo "<td class='std' style='text-align:right;'>".$CatPrcLes."</td>";		
		echo "</tr>";
		}
	?>
	</tbody>
	</table>
	