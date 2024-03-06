<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	include "framework/security/default.php";
	
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");

	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));

	$query="SELECT TYP.PRN_DIG_TYP,PRN_DIG_TYP_PAR,PRN_DIG_DESC,CAT_SUB_DESC,PRN_DIG_EQP_DESC,TYP.INV_NBR,TYP.PRN_DIG_PRC,PRN_DIG_PRC_PRSN,PRN_DIG_PRC_MBR,INV_PRC,AVG_INV_PRC,(CASE WHEN AVG_INV_PRC <>0 THEN  TYP.PRN_DIG_PRC/AVG_INV_PRC ELSE TYP.PRN_DIG_PRC/INV_PRC END) AS FACTOR,PRN_DIG_PRC,PROMO_DISC_AMT,PLAN_DESC,PRN_DIG_CD,ACT_F
					FROM CMP.PRN_DIG_TYP TYP INNER JOIN 
					CMP.PRN_DIG_VOL_PLAN_TYP PLN ON TYP.PLAN_TYP=PLN.PLAN_TYP INNER JOIN
					CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP LEFT OUTER JOIN
					(SELECT PRN_DIG_TYP,SUM(PROMO_DISC_AMT) AS PROMO_DISC_AMT FROM CMP.PRN_DIG_PROMO WHERE BEG_DT<=CURRENT_DATE AND END_DT>=CURRENT_DATE GROUP BY PRN_DIG_TYP) PRO ON TYP.PRN_DIG_TYP=PRO.PRN_DIG_TYP
					LEFT OUTER JOIN 
						(
							SELECT INV_NBR, CAT_SUB_NBR, PRD_PRC_TYP
							FROM RTL.INVENTORY
							GROUP BY PRD_PRC_TYP
						) INV ON INV.PRD_PRC_TYP = TYP.PRN_DIG_TYP
					LEFT OUTER  JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR AND SUB.DEL_NBR=0
					LEFT OUTER JOIN (
						SELECT 
							DET.ORD_DET_NBR, DET.ORD_NBR, HED.ORD_DTE, DET.INV_NBR, DET.INV_PRC, INV.PRD_PRC_TYP
						FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR = DET.ORD_NBR
						LEFT JOIN RTL.INVENTORY INV ON INV.INV_NBR = DET.INV_NBR
						INNER JOIN (
							SELECT ORD_DET_NBR, DET.INV_NBR, MAX(ORD_DTE) AS MAX_ORD_DATE, INV.PRD_PRC_TYP
							FROM RTL.RTL_STK_DET DET 
							LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR = DET.ORD_NBR
							LEFT JOIN RTL.INVENTORY INV ON INV.INV_NBR = DET.INV_NBR
							WHERE IVC_TYP='RC'
							GROUP BY PRD_PRC_TYP
						)MAX ON MAX.PRD_PRC_TYP = INV.PRD_PRC_TYP AND MAX.MAX_ORD_DATE = HED.ORD_DTE
						WHERE IVC_TYP='RC'
						GROUP BY INV.PRD_PRC_TYP
					) STK ON STK.PRD_PRC_TYP = TYP.PRN_DIG_TYP 
					LEFT OUTER JOIN (
						SELECT 
								DET.ORD_DET_NBR, DET.INV_NBR, AVG(DET.INV_PRC) AS AVG_INV_PRC,PRD_PRC_TYP
						FROM 
								RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR =  DET.ORD_NBR
						LEFT OUTER JOIN RTL.INVENTORY INV ON INV.INV_NBR = DET.INV_NBR
						INNER JOIN (
								SELECT ORD_DET_NBR, INV_NBR, MAX(ORD_DTE) AS MAX_ORD_DATE
								FROM RTL.RTL_STK_DET DET 
								LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR = DET.ORD_NBR 
								WHERE IVC_TYP='RC'
								GROUP BY INV_NBR
						) MAX ON MAX.INV_NBR = DET.INV_NBR
						WHERE 
							IVC_TYP='RC' AND
							(HED.ORD_DTE BETWEEN  (MAX.MAX_ORD_DATE - INTERVAL 6 MONTH) AND MAX.MAX_ORD_DATE)
						GROUP BY INV.PRD_PRC_TYP
					)AVG ON AVG.PRD_PRC_TYP =  TYP.PRN_DIG_TYP
			WHERE TYP.DEL_NBR=0 AND (PRN_DIG_DESC LIKE '%".$searchQuery."%' OR TYP.PRN_DIG_TYP LIKE '%".$searchQuery."%' OR TYP.INV_NBR LIKE '%".$searchQuery."%')
			ORDER BY 2";
	// echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>

<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th style="text-align:right;">Kode</th>
			<th>Deskripsi</th>
			<th>Kategori</th>
			<th>No. Stock</th>
			<th>Harga</th>
			<th>Harga Tunai</th>
			<th>Harga Member</th>
			<?php if ($UpperSec == 0){ ?>
			<th>Harga Beli Terakhir</th>
			<th>Harga Rata-Rata</th>
			<th>Faktor</th>
			<?php } ?>
			<th>Promo</th>
			<th style="border-right:0px;">Vol Disc</th>
			<th style="width: 6%">Alias</th>	
			<?php if ($UpperSec == 0){ ?>
			<th style="width: 6%">Status</th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
	<?php
		$result=mysql_query($query);
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			if ($row['ACT_F']=='1')
			{ 
				$status = "checked"; 
			}
			else
			{
				$status = "";
			}
			echo "<tr $alt ";
			if($UpperSec<5){ 
				echo "style='cursor:pointer;' onclick=".chr(34)."location.href='print-digital-type-edit.php?PRN_DIG_TYP=".$row['PRN_DIG_TYP']."';".chr(34);
			}
			echo ">";
			echo "<td>".$row['PRN_DIG_TYP']."</td>";
			echo "<td>".$row['PRN_DIG_DESC']."</td>";
			echo "<td>".$row['CAT_SUB_DESC']."</td>";//Kategory
			echo "<td style='text-align:center'>".$row['INV_NBR']."</td>";
			echo "<td style='text-align:right'>".number_format($row['PRN_DIG_PRC'],0,'.',',');
			echo "<td style='text-align:right'>".number_format($row['PRN_DIG_PRC_PRSN'],0,'.','.');
				echo "<td style='text-align:right'>".number_format($row['PRN_DIG_PRC_MBR'],0,'.','.');
			if ($UpperSec==0){
				echo "<td style='text-align:right'>".number_format($row['INV_PRC'],0,'.',',');//harga beli terakhir
				echo "<td style='text-align:right'>".number_format($row['AVG_INV_PRC'],0,'.',',');//rata-rata /6bulan
				echo "<td style='text-align:right'>".number_format($row['FACTOR'],2,'.',',');//faktor (harga jual/harga rata-rata 6 bulan)
			}
			echo "<td style='text-align:right'>";
			if($row['PROMO_DISC_AMT']!=""){
				echo number_format($row['PRN_DIG_PRC']-$row['PROMO_DISC_AMT'],0,'.',',');
			}
			echo "</td>";
			echo "<td>".$row['PLAN_DESC']."</td>";
			echo "<td>".$row['PRN_DIG_CD']."</td>";
			if ($UpperSec == 0){
			echo "<td><input name='ACT_F' id='ACT_F' type='checkbox' class='regular-checkbox' ".$status."/><label for='ACT_F'></label></td>";
			}
			echo "</tr>";
		}
	?>
	</tbody>
</table>
