<?php
	include "framework/database/connect.php";
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	
	$sql = "SELECT PPL.PRSN_NBR
				,NAME
				,POS_DESC
				,MAX(PYMT_DTE) AS PYMT_DTE
				,DEBT_MO_TOT
				,SUM(CRDT_AMT) AS CRDT_AMT_TOT
			FROM CMP.PEOPLE PPL
			LEFT OUTER JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
			INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP = POS.POS_TYP
			LEFT OUTER JOIN (
						SELECT PRSN_NBR,
								PYMT_DTE,
								CRDT_AMT
						FROM PAY.EMPL_CRDT 
						WHERE (DEL_NBR = 0
							OR DEL_NBR IS NULL)
							AND CRDT_APV=1
					) BON ON PPL.PRSN_NBR = BON.PRSN_NBR
			LEFT OUTER JOIN (
				SELECT PPL.PRSN_NBR
					,SUM(DEBT_MO) AS DEBT_MO_TOT
				FROM CMP.PEOPLE PPL
				LEFT OUTER JOIN PAY.PAYROLL PAY ON PPL.PRSN_NBR = PAY.PRSN_NBR
				WHERE PPL.DEL_NBR = 0
					AND (
						PAY.DEL_NBR = 0
						OR PAY.DEL_NBR IS NULL
						)
				GROUP BY PAY.PRSN_NBR ASC
				) AS PAY ON PPL.PRSN_NBR = PAY.PRSN_NBR
			WHERE TERM_DTE IS NULL
				AND PPAY.PAY_TYP = 'MON'
				AND (
					NAME LIKE '%".$searchQuery."%'
					OR PPL.PRSN_NBR LIKE '%".$searchQuery."%'
					)
				AND PPL.DEL_NBR = 0
				AND CO_NBR IN (
					SELECT CO_NBR
					FROM NST.PARAM_PAYROLL
					)
			GROUP BY PPL.PRSN_NBR
				,NAME
				,POS_DESC
			ORDER BY 2";

	$query=mysql_query($sql);
	if(mysql_num_rows($query)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>
<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th style="text-align:center;">No.</th>
			<th>Nama</th>
			<th>Jabatan</th>
			<th>Tanggal</th>
			<th>Bon</th>
			<th>Cicilan</th>
			<th>Sisa</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		while($row=mysql_fetch_array($query))
		{
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='employee-credit-edit.php?PRSN_NBR=".$row['PRSN_NBR']."';".chr(34).">";
			echo "<td style='text-align:center'>".$row['PRSN_NBR']."</td>";
					echo "<td class='listable' align='left'>".$row['NAME']."</td>";
					echo "<td class='listable' align='left'>".$row['POS_DESC']."</td>";
					echo "<td class='listable' style='text-align:center'>".$row['PYMT_DTE']."</td>";	
					echo "<td class='listable' align='right'>".number_format($row['CRDT_AMT_TOT'],0,",",".")."</td>";
					echo "<td class='listable' align='right'>".number_format($row['DEBT_MO_TOT'],0,",",".")."</td>";
					$TotBon=$row['CRDT_AMT_TOT']-$row['DEBT_MO_TOT'];
					echo"<td class='listable' align='right'>".number_format($TotBon,0,",",".")."</td>";
			echo "</tr>";
		}
	?>
	</tbody>
</table>