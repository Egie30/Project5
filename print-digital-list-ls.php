<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	
	//Take care of leading zeros on the order number
	if(is_numeric($searchQuery)){
		$searchQuery=$searchQuery+0;
	}
	
	$query="SELECT NBR FROM CDW.PRN_DIG_TOP_CUST";
	$result=mysql_query($query);
	while($row=mysql_fetch_array($result)){
		$TopCusts[]=strval($row['NBR']);
	}
	
	$query="SELECT ORD_NBR,DL_CNT,PU_CNT,NS_CNT,IVC_PRN_CNT,ORD_TS,ORD_STT_DESC,BUY_PRSN_NBR,PPL.NAME AS NAME_PPL,COM.NAME AS NAME_CO,BUY_CO_NBR,REF_NBR,ORD_TTL,DUE_TS,PRN_CO_NBR,FEE_MISC,TOT_AMT,PYMT_DOWN,PYMT_REM,TOT_REM,CMP_TS,PU_TS,SPC_NTE,HED.CRT_TS,HED.CRT_NBR,HED.UPD_TS,HED.UPD_NBR
			FROM CMP.PRN_DIG_ORD_HEAD HED
			INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
			LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
			LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
			WHERE (ORD_TTL LIKE '%".$searchQuery."%' OR PPL.NAME LIKE '%".$searchQuery."%' OR COM.NAME LIKE '%".$searchQuery."%' OR ORD_NBR LIKE '%".$searchQuery."%')
			AND HED.DEL_NBR=0
			ORDER BY ORD_NBR DESC";
			//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>

<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th style="text-align:right;">No.</th>
			<th>&nbsp;</th>
			<th>Judul</th>
			<th>Pemesan</th>
			<th style="width:7%;">Tgl. Pesan</th>
			<th>Status</th>
			<th style="width:7%;">Tgl. Janji </th>
			<th style="width:7%;">Tgl. Jadi </th>
			<th style="text-align:right;">Jumlah</th>
			<th style="text-align:right;">Sisa</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='print-digital-edit.php?ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
			echo "<td style='text-align:right'>".$row['ORD_NBR']."</td>";
			echo "<td style='text-align:left;white-space:nowrap'>";
					if(in_array($row['BUY_CO_NBR'],$TopCusts)){
						echo "<div class='listable'><span class='fa fa-star listable'></span></div>";
					}				
					if($row['SPC_NTE']!=""){
						echo "<div class='listable'><span class='fa fa-comment listable'></span></div>";
					}
					if($row['DL_CNT']>0){
						echo "<div class='listable'><span class='fa fa-truck listable' style='margin-left:-1px'></span></div>";
					}
					if($row['PU_CNT']>0){
						echo "<div class='listable'><span class='fa fa-shopping-cart listable'></span></div>";
					}
					if($row['NS_CNT']>0){
						echo "<div class='listable'><span class='fa fa-flag listable'></span></div>";
					}
					if($row['IVC_PRN_CNT']>0){
						echo "<div class='listable'><span class='fa fa-print listable'></span></div>";
					}
			echo "</td>";
			echo "<td>".$row['ORD_TTL']."</td>";
			echo "<td>".$row['NAME_PPL']." ".$row['NAME_CO']."</td>";
			echo "<td style='text-align:center'>".parseDateShort($row['ORD_TS'])."</td>";
			echo "<td style='text-align:center'>".$row['ORD_STT_DESC']."</td>";
			echo "<td style='text-align:center;white-space:nowrap'>".parseDateShort($row['DUE_TS'])." ".parseHour($row['DUE_TS']).":".parseMinute($row['DUE_TS'])."</td>";
			echo "<td style='text-align:center'>".parseDateShort($row['CMP_TS'])."</td>";
			echo "<td style='text-align:right;'>".number_format($row['TOT_AMT'],0,'.','.')."</td>";
			echo "<td style='text-align:right;'>".number_format($row['TOT_REM'],0,'.','.')."</td>";
			echo "</tr>";
		}
	?>
	</tbody>
</table>
