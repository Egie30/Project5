<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	include "framework/security/default.php";
	
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");

	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$query="SELECT PROD_NBR, PROD_DESC, PROD_PRC FROM CMP.PROD_LST
			WHERE DEL_NBR=0 AND (PROD_DESC LIKE '%".$searchQuery."%' OR PROD_PRC LIKE '%".$searchQuery."%')
			ORDER BY 2";
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
			<th style="text-align:center;">No</th>
			<th>Nama Produk</th>
			<th>Harga Produk</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$i = 1;
		
		$result=mysql_query($query);
		$alt="";
		while($row=mysql_fetch_array($result))
		{

			echo "<tr $alt ";
			if($UpperSec<5){ 
				echo "style='cursor:pointer;' onclick=".chr(34)."location.href='print-digital-discount-edit.php?PROD_NBR=".$row['PROD_NBR']."';".chr(34);
			}
			echo ">";
			echo "<td style='text-align:center;'>".$i."</td>";
			echo "<td>".$row['PROD_DESC']."</td>";
			echo "<td style='text-align:right'>".$row['PROD_PRC']."</td>";
			echo "</tr>";
			$i++;
		}
	?>
	</tbody>
</table>
