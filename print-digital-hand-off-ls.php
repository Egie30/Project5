<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	include "framework/security/default.php";
	
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");

	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$query="SELECT HND_OFF_TYP, HND_OFF_DESC FROM CMP.HND_OFF_TYP
			WHERE (HND_OFF_TYP LIKE '%".$searchQuery."%' OR HND_OFF_DESC LIKE '%".$searchQuery."%')
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
			<th>Kode</th>
			<th>Serah Terima</th>
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
				echo "style='cursor:pointer;' onclick=".chr(34)."location.href='print-digital-discount-edit.php?HND_OFF_TYP=".$row['HND_OFF_TYP']."';".chr(34);
			}
			echo ">";
			echo "<td style='text-align:center;'>".$i."</td>";
			echo "<td>".$row['HND_OFF_TYP']."</td>";
			echo "<td>".$row['HND_OFF_DESC']."</td>";
			echo "</tr>";
			
			$i++;
		}
	?>
	</tbody>
</table>
