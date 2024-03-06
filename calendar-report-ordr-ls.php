<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$searchQuery = trim(strtoupper(urldecode($_REQUEST['s'])));
	$searchQ=explode(" ",$searchQuery);
	$whereClause="";
	foreach($searchQ as $searchQuery)
	{
		$whereClause.="(REQ.ORD_NBR LIKE '%".$searchQuery."%' OR REQ.REF_NBR LIKE '%".$searchQuery."%' OR REQ.REF_NBR LIKE '%".$searchQuery."%' OR REQ.ORD_TTL LIKE '%".$searchQuery."%') AND ";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	//Search for kalender
	$query="SELECT 
				REQ.ORD_NBR,
				REQ.ORD_DTE AS SORT_DTE,
				DATE_FORMAT(REQ.ORD_DTE,'%d-%m-%Y') AS ORD_DTE,
				REQ.REF_NBR,
				REQ.ORD_TTL,
				CASE WHEN NAME IS NULL THEN 'Tunai' ELSE NAME END AS NAME,
				REQ.TOT_AMT
			FROM CMP.CAL_ORD_HEAD REQ 
				LEFT OUTER JOIN CMP.COMPANY COM ON REQ.BUY_CO_NBR=COM.CO_NBR 
				LEFT OUTER JOIN CMP.CAL_ORD_HEAD INV ON INV.REQ_NBR LIKE CONCAT('%',REQ.REF_NBR,'%') AND REQ.BUY_CO_NBR=INV.BUY_CO_NBR
			WHERE REQ.ORD_TYP='REQ' AND INV.ORD_NBR IS NULL AND ".$whereClause." AND REQ.ORD_DTE BETWEEN ".getFiscalYear()." 
			GROUP BY REQ.ORD_NBR,REQ.ORD_DTE,REQ.REF_NBR,DATE_FORMAT(REQ.ORD_DTE,'%d-%m-%Y'),REQ.ORD_TTL,CASE WHEN NAME IS NULL THEN 'Tunai' ELSE NAME END,REQ.TOT_AMT
			ORDER BY 2";
	//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>

<table id="searchTable" class="sortable-onload-5-6r rowstyle-alt colstyle-alt no-arrow searchTable">
	<thead>
			<tr>
				<th class='sortable'>No.</th>
				<th class='sortable'>Tgl.</th>
				<th class='sortable'>No. Ref.</th>
				<th class='sortable'>Judul</th>
				<th class='sortable'>Pembeli</th>
				<th class='sortable'>Total</th>
			</tr>
		</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result)){
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='calendar-edit.php?ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
				echo "<td class='listable-first'>".$row['ORD_NBR']."</td>";
				echo "<td class='listable' style='text-align:center'>".$row['ORD_DTE']."</a></td>";
				echo "<td class='listable'>".$row['REF_NBR']."</a></td>";
				echo "<td class='listable'>".$row['ORD_TTL']."</a></td>";
				echo "<td class='listable'>".$row['NAME']."</a></td>";
				echo "<td class='listable' align='right'>".number_format($row['TOT_AMT'],0,",",".")."</td>";
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}
				if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
			}
	?>
	</tbody>
</table>
