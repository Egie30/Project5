<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";	
	include "framework/functions/dotmatrix.php";
	
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));

?>
<table style="background:#ffffff;">
	<tr>
		<th class="listable">Barcode</th>
		<th class="listable">Deskripsi</th>
		<th class="listable">Harga</th>
	</tr>
	<?php
		$query="SELECT RTL_TYP_NBR,RTL_BRC,RTL_NBR,CONCAT(STA.NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE) AS NAME_DESC,COM.NAME,RTL_PRC
				FROM CMP.RTL_TYP RTL INNER JOIN STATIONERY STA ON RTL.RTL_NBR=STA.STA_NBR INNER JOIN CMP.COMPANY COM ON STA.CO_NBR=COM.CO_NBR INNER JOIN CMP.STA_COLR CLR ON STA.COLR_NBR=CLR.COLR_NBR
				WHERE CONCAT(STA.NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE) LIKE '%".$searchQuery."%' OR RTL_TYP_NBR LIKE '%".$searchQuery."%' OR RTL_BRC LIKE '%".$searchQuery."%' OR RTL_NBR LIKE '%".$searchQuery."%'";
		//echo $query;
		$result=mysql_query($query);
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr -$alt onclick=".chr(34)."document.getElementById('livesearch').value='".$row['RTL_BRC']."';document.getElementById('livesearch').focus();".chr(34).">";
			echo "<td style='text-align:left;'>".$row['RTL_BRC']."</td>";
			echo "<td style='text-align:left;'>".$row['NAME_DESC']."</td>";
			echo "<td style='text-align:right;'>".number_format($row['RTL_PRC'],0,'.',',')."</td>";
			echo "</td></tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
	?>
</table>
<input type="hidden" id="TOT_NET" name="TOT_NET" value="<? echo $TotNet; ?>" />