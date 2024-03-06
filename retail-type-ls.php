<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	include "framework/security/default.php";
	
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");

	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$query="SELECT RTL_TYP_NBR,RTL_BRC,RTL_NBR,CONCAT(STA.NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE) AS NAME_DESC,COM.NAME,RTL_PRC
					FROM RTL_TYP RTL INNER JOIN STATIONERY STA ON RTL.RTL_NBR=STA.STA_NBR INNER JOIN CMP.COMPANY COM ON STA.CO_NBR=COM.CO_NBR INNER JOIN CMP.STA_COLR CLR ON STA.COLR_NBR=CLR.COLR_NBR
			WHERE CONCAT(STA.NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE) LIKE '%".$searchQuery."%' OR RTL_TYP_NBR LIKE '%".$searchQuery."%' OR RTL_BRC LIKE '%".$searchQuery."%' OR RTL_NBR LIKE '%".$searchQuery."%'";
	//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>

<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
	<thead>
		<tr>
			<th class="sortable">No.</th>
			<th class="sortable">Barcode</th>
			<th class="sortable">No. Stock</th>
			<th class="sortable">Deskripsi</th>
			<th class="sortable">Supplier</th>
			<th class="sortable-currency" style="border-right:0px;">Harga</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$result=mysql_query($query);
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt ";
			if($UpperSec<5){ 
				echo "style='cursor:pointer;' onclick=".chr(34)."location.href='retail-type-edit.php?RTL_TYP_NBR=".$row['RTL_TYP_NBR']."';".chr(34);
			}
			echo ">";
			echo "<td style='text-align:right'>".$row['RTL_TYP_NBR']."</td>";
			echo "<td style='text-align:center'>".$row['RTL_BRC']."</td>";
			echo "<td style='text-align:right'>".$row['RTL_NBR']."</td>";
			echo "<td>".$row['NAME_DESC']."</td>";
			echo "<td>".$row['NAME']."</td>";
			echo "<td style='text-align:right'>".number_format($row['RTL_PRC'],0,'.',',');
			echo "</tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
	?>
	</tbody>
</table>
