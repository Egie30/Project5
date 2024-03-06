<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$searchQuery = trim(strtoupper(urldecode($_REQUEST['s'])));
	$query="SELECT 
				ORD_TYP,
				HED.ORD_NBR,
				REF_NBR,
				ORD_TTL,
				DATE_FORMAT(ORD_DTE,'%d-%m-%Y') AS ORD_DTE,
				CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END AS SEL_NAME,
				CASE WHEN CMB.NAME IS NULL THEN 'Tunai' ELSE CMB.NAME END AS BUY_NAME,
				SUM(CASE WHEN CAL_TYP='ST' THEN ORD_Q ELSE 0 END) AS ST,
				SUM(CASE WHEN CAL_TYP='TR' THEN ORD_Q ELSE 0 END) AS TR,
				SUM(CASE WHEN CAL_TYP='CW' THEN ORD_Q ELSE 0 END) AS CW,
				SUM(CASE WHEN CAL_TYP='TH' THEN ORD_Q ELSE 0 END) AS TH,
				SUM(CASE WHEN CAL_TYP='KK' THEN ORD_Q ELSE 0 END) AS KK,
				SUM(CASE WHEN CAL_TYP NOT IN ('ST','TR','CQ','TH','KK') THEN ORD_Q ELSE 0 END) AS LL,
				TOT_AMT
			FROM CMP.CAL_ORD_HEAD HED 
				LEFT OUTER JOIN CMP.CAL_ORD_DET AS DET ON HED.ORD_NBR=DET.ORD_NBR 
				LEFT OUTER JOIN CMP.CAL_LST LST ON DET.CAL_NBR=LST.CAL_NBR 
				LEFT OUTER JOIN CMP.COMPANY CMS ON HED.SEL_CO_NBR=CMS.CO_NBR 
				LEFT OUTER JOIN CMP.COMPANY CMB ON HED.BUY_CO_NBR=CMB.CO_NBR
			WHERE (HED.ORD_NBR LIKE '%".$searchQuery."%' OR DATE_FORMAT(ORD_DTE,'%d-%m-%Y') LIKE '%".$searchQuery."%' OR CMS.NAME LIKE '%".$searchQuery."%' OR CMB.NAME LIKE '%".$searchQuery."%' OR ORD_TTL LIKE '%".$searchQuery."%' OR REF_NBR LIKE '%".$searchQuery."%' OR CAL_DESC LIKE '%".$searchQuery."%') AND ORD_DTE BETWEEN ".getFiscalYear()."
			GROUP BY HED.ORD_NBR,ORD_DTE,ORD_TTL,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END,TOT_AMT
			ORDER BY HED.UPD_DTE DESC";
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan datas</div>";
		exit;
	}
?>

<table id="searchTable" class="sortable-onload-5-6r rowstyle-alt colstyle-alt no-arrow searchTable">
	<thead>
		<tr>
			<th class="sortable">No</th>
				<th class="sortable">Tanggal</th>
				<th class="sortable">No. Referensi</th>
				<th class="sortable">Judul</th>
				<th class="sortable">Penjual</th>
				<th class="sortable">Pembeli</th>
				<th class="sortable" style="border-right:0px;">Total</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='calendar-edit.php?ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
				echo "<td style='text-align:center'>".$row['ORD_NBR']."</td>";
				echo "<td style='text-align:center'>".$row['ORD_DTE']."</td>";
				echo "<td style='text-align:center'>".$row['REF_NBR']."</td>";
				echo "<td style='text-align:center'>".$row['ORD_TTL']."</td>";
				echo "<td>".$row['SEL_NAME']."</td>";
				echo "<td>".$row['BUY_NAME']."</td>";
				echo "<td style='text-align:right'>".number_format($row['TOT_AMT'],0,",",".")."</td>";
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
	?>
	</tbody>
</table>
