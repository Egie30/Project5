<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";

	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	$executive 	 = getSecurity($_SESSION['userID'],"Executive");
	$finance 	 = getSecurity($_SESSION['userID'],"Finance");
	$month 		 = $_GET['PYMT_DTE_M'];
	$year 		 = $_GET['PYMT_DTE_Y'];
	$filter_date = $month." ".$year;
	$PPlFinance  = array(706,368);

	if (!in_array($_SESSION['personNBR'], $PPlFinance)){
		$whereClause = " AND EPC.PRSN_NBR=".$_SESSION['personNBR'];
		$groupBy 	 = " GROUP BY EPC.PRSN_NBR,PYMT_DTE";
	}
	if (in_array($_SESSION['personNBR'], $PPlFinance) || $executive<1){
		$groupBy     = " GROUP BY EPC.PRSN_NBR";
		$whereClause = "";
	}
	
	$query="SELECT 
				EPC.PRSN_NBR,
				EPC.PYMT_DTE, 
				PPL.NAME AS PPL_NAME, 
				COM.NAME AS CO_NAME,
				POS.POS_DESC,
				EPC.CRDT_AMT,
				EPC.PYMT_NBR,
				EPC.CRDT_PRNC,
				EPC.CRDT_RSN,
				APV.NAME AS APV_NAME,
				CASE WHEN EPC.CRDT_APV =0 THEN 'Belum Disetujui' ELSE 'Disetujui' END AS CRDT_APV,
				COALESCE(SUM(CASE WHEN EPC.CRDT_APV =0 THEN 1 ELSE 0 END),0) AS CNT_DIS,
				COALESCE(SUM(CASE WHEN EPC.CRDT_APV =1 THEN 1 ELSE 0 END),0) AS CNT_APV,
				MAX(PYMT_DTE) AS MAX_PYMT_DTE,
				CASE WHEN EPC.DSBRS_TYP ='TRF' OR EPC.DSBRS_TYP IS NULL THEN 'Transfer' ELSE 'Payroll' END AS DSBRS_TYP
			FROM PAY.EMPL_CRDT EPC
			LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=EPC.PRSN_NBR
			LEFT OUTER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP 
			LEFT OUTER JOIN CMP.COMPANY COM ON PPL.CO_NBR=COM.CO_NBR
			LEFT OUTER JOIN CMP.PEOPLE APV ON APV.PRSN_NBR=EPC.CRDT_APV_NBR 
			WHERE PPL.TERM_DTE IS NULL  
				AND EPC.DEL_NBR=0 
				AND MONTH(PYMT_DTE)=".$month."
				AND YEAR(PYMT_DTE)=".$year."
				AND (
					EPC.PRSN_NBR LIKE '%".$searchQuery."%' OR 
					PPL.NAME LIKE '%".$searchQuery."%' OR 
					COM.NAME LIKE '%".$searchQuery."%' 
				)
				".$whereClause." ".$groupBy." ";
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>

<table id="searchTable" class="tablesorter searchTable">
	<thead>
		<tr>
			<th style="text-align:right;width:5%;">No.</th>
			<th style="text-align:right;width:5%;">NIK</th>
			<th style="width: 20%;">Nama</th>
			<th>Perusahaan</th>
			<th>Tanggal</th>
			<?php if (!in_array($_SESSION['personNBR'], $PPlFinance) && $executive!=0) {?>
			<th>Bon</th>
			<th>Periode Cicilan</th>
			<th style="width: 25%;">Alasan</th>
			<?php }else{?>
				<th>Tipe Pencairan</th>
				<?php } ?>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt="";
		$i=1;
		while($row=mysql_fetch_array($result))
		{
			
			$link = "kas-bon-detail.php?PRSN_NBR=".$row['PRSN_NBR']."&PYMT_DTE=".$row['PYMT_DTE']."&FLTR_DATE=".$filter_date;
			if (!in_array($_SESSION['personNBR'], $PPlFinance)){$pymtDte = $row['PYMT_DTE'];}
			if ($executive<1 || in_array($_SESSION['personNBR'], $PPlFinance)){$pymtDte = $row['MAX_PYMT_DTE'];}
			
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='".$link."';".chr(34).">";
			echo "<td style='text-align:right'>".$i."</td>";
			echo "<td style='text-align:right'>".$row['PRSN_NBR']."</td>";
			echo "<td>".$row['PPL_NAME']."</td>";
			echo "<td>".$row['CO_NAME']."</td>";
			echo "<td style='text-align:center'>".$pymtDte."</td>";
			if (!in_array($_SESSION['personNBR'], $PPlFinance) && $executive!=0) {
			echo "<td style='text-align:right'>".number_format($row['CRDT_AMT'], 0, ",", ".")."</td>";
			echo "<td style='text-align:right'>".$row['PYMT_NBR']."</td>";
			echo "<td>".$row['CRDT_RSN']."</td>";
			}else {
				echo "<td>".$row['DSBRS_TYP']."</td>";
			}
			echo "<td>".$row['CRDT_APV']."</td>";
			echo "</tr>";
		$i++;}
	?>
	</tbody>
</table>
