<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);
	$whereClause="";
	foreach($searchQ as $searchQuery)
	{
		$whereClause.="(CAP.VCHR_NBR LIKE '%".$searchQuery."%' OR PPL.NAME LIKE '%".$searchQuery."%' OR COM.NAME LIKE '%".$searchQuery."%' OR CAP.VCHR_SER_NBR LIKE '%".$searchQuery."%' OR AMT LIKE '%".$searchQuery."%') AND ";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	
	$query="SELECT VCHR_NBR,VCHR_SER_NBR,AMT,PRN_TS,VLD_TS,RCV_CO_NBR,COM.NAME AS CO_NAME,PPL.NAME AS PPL_NAME,RCV_PRSN_NBR,ISU_TS,EXP_DT,USE_TS,CRT_CO_NBR,CRT_NBR,CRT_TS
					  FROM CMP.CAP_VCHR CAP LEFT OUTER JOIN
					       CMP.PEOPLE PPL ON CAP.RCV_PRSN_NBR=PPL.PRSN_NBR LEFT OUTER JOIN
					       CMP.COMPANY COM ON CAP.RCV_CO_NBR=COM.CO_NBR
					 WHERE CAP.CRT_CO_NBR=271 AND ".$whereClause."
					 ORDER BY 2";
	//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>
<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th>No. Seri</th>
			<th>Nominal</th>
			<th>Buat</th>
			<th>Print</th>
			<th>Validasi</th>
			<th>Customer</th>
			<th>Issue</th>
			<th>Exp</th>
			<th style="border-right:0px;">Guna</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='cap-voucher-edit.php?VCHR_NBR=".$row['VCHR_NBR']."';".chr(34).">";
			echo "<td style='text-align:center'>".$row['VCHR_SER_NBR']."</td>";
			echo "<td style='text-align:right'>".number_format($row['AMT'],0,",",".")."</td>";
			echo "<td style='text-align:center'>".parseDateShort($row['CRT_TS'])."</td>";
			echo "<td style='text-align:center'>".parseDateShort($row['PRN_TS'])."</td>";
			echo "<td style='text-align:center'>".parseDateShort($row['VLD_TS'])."</td>";
			echo "<td>".trim($row['CO_NAME']." ".$row['PPL_NAME'])."</td>";
			echo "<td style='text-align:center'>".parseDateShort($row['ISU_TS'])."</td>";
			echo "<td style='text-align:center'>".parseDateShort($row['EXP_DT'])."</td>";
			echo "<td style='text-align:center'>".parseDateShort($row['USE_TS'])."</td>";
			echo "</tr>";
		}
	?>
	</tbody>
</table>
