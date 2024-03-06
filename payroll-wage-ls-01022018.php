<?php
	include "framework/database/connect.php";
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$query="SELECT PPL.PRSN_NBR,NAME,POS_DESC,MAX(PYMT_DTE) AS PYMT_DTE
			FROM CMP.PEOPLE PPL
			INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP
			INNER JOIN CMP.PAYROLL_LOC PAY ON PPL.PRSN_NBR=PAY.PRSN_NBR
			WHERE TERM_DTE IS NULL AND (NAME LIKE '%".$searchQuery."%' OR PPL.PRSN_NBR LIKE '%".$searchQuery."%')
			AND PPL.PAY_TYP='DAY'
			GROUP BY PPL.PRSN_NBR,NAME,POS_DESC ORDER BY 2";
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
			<th>Nama</th>
			<th>Jabatan</th>
			<th style="border-right:0px;">Gajian Terakhir</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt onclick=".chr(34)."location.href='payroll-wage-edit.php?PRSN_NBR=".$row['PRSN_NBR']."';".chr(34).">";
			echo "<td style='text-align:right'>".$row['PRSN_NBR']."</td>";
			echo "<td>".$row['NAME']."</td>";
			echo "<td>".$row['POS_DESC']."</td>";
			echo "<td>".$row['PYMT_DTE']."</td>";
			echo "</tr>";
		}
	?>
	</tbody>
</table>
