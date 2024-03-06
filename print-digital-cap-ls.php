<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);
	$whereClause="CRD.DEL_NBR = 0 AND ";
	foreach($searchQ as $searchQuery)
	{
		$whereClause.="(CAP_NBR LIKE '%".$searchQuery."%' 
		OR PYMT_DTE LIKE '%".$searchQuery."%' 
		OR SHP_CO_NBR LIKE '%".$searchQuery."%' 
		OR SHP.NAME LIKE '%".$searchQuery."%' 
		OR RCV_CO_NBR LIKE '%".$searchQuery."%' 
		OR CRCV.NAME LIKE '%".$searchQuery."%' 
		OR RCV_PRSN_NBR LIKE '%".$searchQuery."%' 
		OR PRCV.NAME LIKE '%".$searchQuery."%') AND ";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	$query="SELECT 
		CAP_NBR,
		PYMT_DTE,
		SHP_CO_NBR,
		SHP.NAME AS SHP_NAME,
		RCV_CO_NBR,
		CRCV.NAME AS CRCV_NAME,
		RCV_PRSN_NBR,
		PRCV.NAME AS PRCV_NAME,
		TOT_AMT,
		PYMT_DOWN,
		PYMT_REM,
		TOT_REM
	FROM CMP.PRN_DIG_CAP CRD
		LEFT OUTER JOIN CMP.COMPANY SHP ON CRD.SHP_CO_NBR=SHP.CO_NBR
		LEFT OUTER JOIN CMP.COMPANY CRCV ON CRD.RCV_CO_NBR=CRCV.CO_NBR
		LEFT OUTER JOIN CMP.PEOPLE PRCV ON CRD.RCV_PRSN_NBR=PRCV.CO_NBR
	WHERE ".$whereClause."
	ORDER BY CRD.UPD_TS DESC";
	//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>". str_replace("{keyword}", "<b>".$searchQuery."</b>", getParam('config', 'errorNotFound')) . "</div>";
		exit;
	}
?>

<div id="mainResult">
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class="sortable">Nbr</th>
				<th class="sortable">Tanggal Terima</th>
				<th class="sortable">Penerima</th>
				<th class="sortable">Total</th>
		</thead>
		<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='print-digital-cap-edit.php?CAP_NBR=".$row['CAP_NBR']."';".chr(34).">";
				echo "<td style='text-align:center'>".$row['CAP_NBR']."</td>";
				echo "<td style='text-align:center'>".$row['PYMT_DTE']."</td>";
				echo "<td>".$row['CRCV_NAME']." ".$row['PRCV_NAME']."</td>";
				echo "<td style='text-align:right'>".number_format($row['TOT_AMT'],1,'.',',')."</td>";
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
	?>
</tbody>
	</table>
</div>