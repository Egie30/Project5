<?php
	include "framework/database/connect-cloud.php";
	include "framework/security/default.php";
	$Security=getSecurity($_SESSION['userID'],"Payroll");

	if($_GET['CO_NBR'] == "ALL") {	$CoNbr = "SELECT CO_NBR FROM NST.PARAM_PAYROLL"; }
		else { $CoNbr = "SELECT CO_NBR_CMPST FROM NST.PARAM_PAYROLL WHERE CO_NBR = ".$_GET['CO_NBR']." "; }
	
	$searchQuery = trim(strtoupper(mysql_real_escape_string($_REQUEST[s])));
	
	if (($Security < 2) && ($_GET['CO_NBR'] == "ALL")){ 
			$query="SELECT PPL.PRSN_NBR
						,PPL.NAME
						,COM.NAME AS CO_NAME
						,POS_DESC
						,PAY.PYMT_DTE AS PYMT_DTE
						,PPAY.PAY_BASE
						,PPAY.PAY_ADD
						,PPAY.PAY_CONTRB
						,PAY.BASE_CNT
						,PAY.OT_CNT
						,PAY.BON_PCT
						,PPL.BNK_ACCT_NBR
						,PAY.PAY_AMT
						,PAY.BON_MO_AMT
						,PAY.DEBT_MO
						,PAY.BON_SNG_AMT
						,PAY.DED_SNG_AMT
					FROM CMP.PEOPLE PPL
					LEFT OUTER JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
					INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP = POS.POS_TYP
					LEFT OUTER JOIN (
						SELECT PRSN_NBR
							,PYMT_DTE
							,PAY_AMT
							,BASE_CNT
							,OT_CNT
							,BON_PCT
							,BON_MO_AMT
							,DEBT_MO
							,BON_SNG_AMT
							,DED_SNG_AMT
							,DEL_NBR
						FROM PAY.PAYROLL AMT
						WHERE AMT.PYMT_DTE = (
								SELECT MAX(PYMT_DTE)
								FROM PAY.PAYROLL SLP
								WHERE SLP.PRSN_NBR = AMT.PRSN_NBR
								)
						) PAY ON PAY.PRSN_NBR = PPL.PRSN_NBR
					INNER JOIN CMP.COMPANY COM ON PPL.CO_NBR = COM.CO_NBR
					WHERE (
							PPL.NAME LIKE '%".$searchQuery."%'
							OR PPL.PRSN_NBR LIKE '%".$searchQuery."%'
							)
						AND TERM_DTE IS NULL
						AND PPAY.PAY_TYP = 'MON'
						AND PPL.CO_NBR IN (".$CoNbr.")
						AND (
							PAY.DEL_NBR = 0
							OR PAY.DEL_NBR IS NULL
							)
						AND PPL.DEL_NBR = 0
					GROUP BY PPL.PRSN_NBR
						,NAME
						,POS_DESC
					ORDER BY 2";
			
			}
			else
			{
			$query = "SELECT PPL.PRSN_NBR
							,PPL.NAME
							,POS.POS_DESC
							,MAX(PYMT_DTE) AS PYMT_DTE
						FROM CMP.PEOPLE PPL
						LEFT OUTER JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
						INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP = POS.POS_TYP
						LEFT OUTER JOIN PAY.PAYROLL PAY ON PPL.PRSN_NBR = PAY.PRSN_NBR
						WHERE (
								PPL.NAME LIKE '%".$searchQuery."%'
								OR PPL.PRSN_NBR LIKE '%".$searchQuery."%'
								)
							AND TERM_DTE IS NULL
							AND PPAY.PAY_TYP = 'MON'
							AND PPL.CO_NBR IN (".$CoNbr.")
							AND PPL.DEL_NBR = 0
							AND (
								PAY.DEL_NBR = 0
								OR PAY.DEL_NBR IS NULL
								)
						GROUP BY PPL.PRSN_NBR";
			}
	
	$result=mysql_query($query,$local);

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
				
				<?php if (($Security<2) && ($_GET['CO_NBR'] == "ALL")) { ?>
					<th>Lokasi</th>
					<th>Pokok</th>
					<th>Tambah</th>
					<th>Kontribusi</th>
					<th>Masuk</th>
					<th>Lembur</th>
					<th>%</th>
					<th>Rek</th>
					<th>Total</th>
					<th>Bonus</th>
					<th>Cicilan</th>
					<th>Extra</th>
					<th>Potong</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
		<?php
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='payroll-edit.php?PRSN_NBR=".$row['PRSN_NBR']."&CO_NBR=".$_GET['CO_NBR']."';".chr(34).">";
				echo "<td style='text-align:right'>".$row['PRSN_NBR']."</td>";
				echo "<td>".$row['NAME']."</td>";
				echo "<td>".$row['POS_DESC']."</td>";
				if ($row['PYMT_DTE'] != "") { 			
				echo "<td>".date('d-m-Y', strtotime($row['PYMT_DTE']))."</td>";
				}
				else { echo "<td></td>";	}

				if(($Security<2) && ($_GET['CO_NBR'] == "ALL")){
					echo "<td>".$row['CO_NAME']."</td>";
					echo "<td style='text-align:right'>".number_format($row['PAY_BASE'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['PAY_ADD'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['PAY_CONTRB'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['BASE_CNT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['OT_CNT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['BON_PCT'],1,",",".")."%</td>";
					echo "<td style='text-align:right'>".$row['BNK_ACCT_NBR']."</td>";
					echo "<td style='text-align:right'>".number_format($row['PAY_AMT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['BON_MO_AMT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['DEBT_MO'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['BON_SNG_AMT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['DED_SNG_AMT'],0,",",".")."</td>";
				}
				echo "</tr>";
			}
		?>
		</tbody>
</table>