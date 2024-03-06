<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$Typ 	= $_GET['TYP'];
	if($Typ=="APV"){
		$whereClause = "AND PPL.APV_F=0";
	}

	if(($locked==1)||($_COOKIE["LOCK"] == "LOCK")){ 
		$whereClause.= "AND PPL.CO_NBR NOT IN (1002, 271)"; 
	}

	$query="SELECT PRSN_NBR,PPL.NAME,CONCAT(PPL.ADDRESS,', ',CITY_NM) AS ADDR,PPL.PHONE,COM.NAME AS COMPANY
			FROM CMP.PEOPLE PPL
			INNER JOIN CMP.CITY CTY ON PPL.CITY_ID=CTY.CITY_ID
			LEFT OUTER JOIN CMP.COMPANY COM ON PPL.CO_NBR=COM.CO_NBR
			WHERE TERM_DTE IS NULL AND PPL.DEL_NBR=0 ".$whereClause." AND (PPL.NAME LIKE '%".$searchQuery."%' OR PRSN_ID LIKE '%".$searchQuery."%' OR PRSN_NBR LIKE '%".$searchQuery."%' OR COM.NAME LIKE '%".$searchQuery."%' OR PPL.KEYWORDS LIKE '%".$searchQuery."%' OR COM.KEYWORDS LIKE '%".$searchQuery."%')
			ORDER BY 2";
	//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>

<table id="searchTable" class="tablesorter searchTable" style="width: 100%">
	<thead>
		<tr>
			<th class="sortable" style="text-align:right;width:5%;">No.</th>
			<th class="sortable">Nama</th>
			<th class="sortable">Alamat</th>
			<th class="sortable">Perusahaan</th>
			<th class="sortable" style="border-right:0px;">Telpon</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='address-person-edit.php?PRSN_NBR=".$row['PRSN_NBR']."';".chr(34).">";
			echo "<td style='text-align:right'>".$row['PRSN_NBR']."</td>";
			echo "<td>".$row['NAME']."</td>";
			echo "<td>".$row['ADDR']."</td>";
			echo "<td>".$row['COMPANY']."</td>";
			echo "<td>".$row['PHONE']."</td>";
			echo "</tr>";
		}
	?>
	</tbody>
</table>
