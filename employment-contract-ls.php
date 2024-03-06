<?php
	// include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/database/connect-cloud.php";

	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$query 		 = "SELECT PPL.PRSN_NBR,
						   PPL.NAME,
						   CNTRCT_TYP.EMPL_CNTRCT_DESC AS EMPL_CNTRCT_DESC,
						   COM.NAME AS COMPANY,
						   CNTRCT.CONTRACTBEGDATE AS CONTRACTBEGDATE,
						   CNTRCT.CONTRACTENDDATE AS CONTRACTENDDATE
			  FROM  $CMP.PEOPLE PPL
			  INNER JOIN $CMP.POS_TYP POS ON PPL.POS_TYP = POS.POS_TYP
			  LEFT  JOIN $CMP.COMPANY COM ON PPL.CO_NBR = COM.CO_NBR
			  LEFT  JOIN $CMP.EMPL_CNTRCT_TYP CNTRCT_TYP ON CNTRCT_TYP.EMPL_CNTRCT_TYP = PPL.EMPL_CNTRCT
			  LEFT  JOIN (SELECT PRSN_NBR,MAX(BEG_DTE) AS CONTRACTBEGDATE, MAX(END_DTE) AS CONTRACTENDDATE FROM $CMP.EMPL_CNTRCT GROUP BY PRSN_NBR ORDER BY BEG_DTE ASC) CNTRCT ON
					   CNTRCT.PRSN_NBR =  PPL.PRSN_NBR
			  WHERE TERM_DTE IS NULL AND PPL.DEL_NBR = 0 AND PPL.EMPL_CNTRCT > 0  AND (PPL.NAME LIKE '%".$searchQuery."%' OR PPL.NAME LIKE '%".$searchQuery."%' OR CNTRCT_TYP.EMPL_CNTRCT_DESC LIKE '%".$searchQuery."%' OR COM.NAME LIKE '%".$searchQuery."%' OR CNTRCT.CONTRACTBEGDATE LIKE '%".$searchQuery."%') ORDER BY 2";
	// echo $query;
	$result = mysql_query($query,$cloud);
	
	if(mysql_num_rows($result)==0)
	{
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}

?>

<table id="searchTable" class="tablesorter searchTable" style="width:100%;">
	<thead>
		<tr>
			<th class="sortable" style="text-align:right;width:8%;">ID Karyawan</th>
			<th class="sortable">Nama</th>
			<th class="sortable">Perusahaan</th>
			<th>Jenis Kontrak Terakhir</th>
			<th>Tanggal Kontrak Terakhir</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			$now = date("Y-m-d");
				// echo $now
				$endday = $row['CONTRACTENDDATE'];

				if($now >= date('Y-m-d',(strtotime ( '-30 day' , strtotime ($endday)))) && $now <= date('Y-m-d',(strtotime ('-15 day' , strtotime ($endday))))) 
				{
					 $color = "<div class='print-digital-green'>";
				} 
				else if ($now >= date('Y-m-d',(strtotime ( '-14 day' , strtotime ($endday)))) && $now <= date('Y-m-d',(strtotime ( '-1 day' , strtotime ($endday))))) 
				{
					 $color = "<div class='print-digital-yellow'>";
				}

				if ($now 	<=  date('Y-m-d',(strtotime ( '-30 day' , strtotime ($endday))))) 
				{
					 $color = "";
				}
				else if ($now >=  date('Y-m-d',(strtotime ( '-1 day' , strtotime ($endday))))) 
				{
					 $color = "<div class='print-digital-red'>";
				}

				if ($row['CONTRACTENDDATE'] == '') 
				{
					$color   = "";
					$contractdesc = "";
				}
				else
				{
					$contractdesc = $row['EMPL_CNTRCT_DESC'];;
				}
				

			echo "<tr $alt style = 'cursor:pointer;' onclick = ".chr(34)."location.href = 'employment-contract-edit.php?PRSN_NBR=".$row['PRSN_NBR']."';".chr(34).">";

			echo "<td style='text-align:right'>".$row['PRSN_NBR']."</td>";
			echo "<td>".$row['NAME']."</td>";
			echo "<td>".$row['COMPANY']."</td>";
			echo "<td>".$contractdesc."</td>";
			echo "<td style='text-align:center;'>$color".$row['CONTRACTENDDATE']."</td>";
			echo "</tr>";
		}
	?>
	</tbody>
</table>
