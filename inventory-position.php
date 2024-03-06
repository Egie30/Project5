<?php
	include "framework/database/connect.php";
	$CoNbr=$_GET['CO_NBR'];
	$SplNbr=$_GET['SPLNBR'];
	$SCatNbr=$_GET['SCATNBR'];
	if ($SplNbr!='') {$SplNbr=' where COM.CO_NBR='.$SplNbr;}
	if ($SCatNbr!='') {$SCatNbr=' where SUB.CAT_SUB_NBR='.$SCatNbr;}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<script src="framework/database/jquery.min.js"></script>

	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
</head>
<body>
<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult">

<?php if($_GET['RPT_TP']=='SPL')
{
mysql_select_db("rtl");
?>
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;">No.</th>
				<th style="text-align:left;">Nama Supplier</th>
				<th style="text-align:left;">Alamat</th>
				<th style="text-align:right;">Stock</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$query="SELECT 
					COM.CO_NBR CONBR,
					COM.NAME CONAME,
					CONCAT (ADDRESS,' ',CT.CITY_NM) ADDR, 
					INV_PRC,
					RCV.NAME AS RCV_NAME,
					SUM(COALESCE(RCV.ORD_Q,0)) AS RCV_ORD,
					SUM(COALESCE(SLS.SLS_Q,0)) AS SLS_Q,
					SUM(COALESCE(SHP.ORD_Q,0)) AS SHP_ORD,
					SUM(COALESCE(CSH.RTL_Q,0)) AS RTL_Q,
					SUM(COALESCE(RCV.ORD_Q,0) - COALESCE(SHP.ORD_Q,0) - COALESCE(CSH.RTL_Q,0) - COALESCE(SLS.SLS_Q,0)) AS BALANCE
				FROM INVENTORY INV 
					LEFT OUTER JOIN RTL.CAT CAT ON INV.CAT_NBR=CAT.CAT_NBR 
					LEFT OUTER JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR 
					LEFT OUTER JOIN RTL.CAT_SHLF SLF ON INV.CAT_DISC_NBR=SLF.CAT_SHLF_NBR 
					LEFT OUTER JOIN CMP.COMPANY COM ON INV.CO_NBR=COM.CO_NBR 
					LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID
					INNER JOIN(
						SELECT 
							SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,RCV_CO_NBR
						FROM RTL.RTL_STK_DET DET 
							LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
							LEFT OUTER JOIN CMP.COMPANY COM ON HED.RCV_CO_NBR=COM.CO_NBR
						WHERE HED.RCV_CO_NBR=".$CoNbr."
						GROUP BY INV_NBR,NAME
					) RCV ON RCV.INV_NBR=INV.INV_NBR 
					LEFT OUTER JOIN(
						SELECT 
							SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,SHP_CO_NBR
						FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
						LEFT OUTER JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR 
						WHERE SHP_CO_NBR=".$CoNbr."
						GROUP BY INV_NBR,NAME
					) SHP ON SHP.INV_NBR=INV.INV_NBR AND SHP.SHP_CO_NBR=RCV.RCV_CO_NBR 
					LEFT OUTER JOIN(
						SELECT 
							SUM(ORD_Q) AS SLS_Q,INV_NBR,NAME,SHP_CO_NBR
						FROM RTL.RTL_STK_DET DET 
							LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
							LEFT OUTER JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
						WHERE SHP_CO_NBR=".$CoNbr." OR SHP_CO_NBR=".$CoNbrDef." AND IVC_TYP='SL'
						GROUP BY INV_NBR,NAME
					) SLS ON SLS.INV_NBR=INV.INV_NBR 
					LEFT OUTER JOIN(
						SELECT 
							RTL_BRC,SUM(RTL_Q) AS RTL_Q,CO_NBR 
						FROM RTL.CSH_REG 
						WHERE RTL_BRC!='' AND CSH_FLO_TYP='RT' AND CO_NBR=".$CoNbr."
						GROUP BY RTL_BRC,CO_NBR
					) CSH ON INV.INV_BCD=CSH.RTL_BRC AND CSH.CO_NBR=RCV.RCV_CO_NBR 
					GROUP BY COM.CO_NBR ";

				$result=mysql_query($query);
				$alt="";
				$i=1;
				while($row=mysql_fetch_array($result))
				{					
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-position.php?CO_NBR=".$CoNbr."&SPLNBR=".$row['CONBR']."';".chr(34).">";
				echo "<td style='text-align:right'>".$i."</td>";
				echo "<td style='text-align:left'>".$row['CONAME']."</td>";
				echo "<td style='text-align:left'>".$row['ADDR']."</td>";
				$stock=$row['RCV_ORD']-$row['SHP_ORD']-$row['RTLQ'];
				echo "<td style='text-align:right'>".$stock."</td>";
				echo "</tr>";
				$i++;
				$tstock+=$row['RCV_ORD']-$row['SHP_ORD']-$row['RTLQ'];
				$nilai+=$stock*$row['INV_PRC'];
				}	
			?>
		</tbody>
	</table>
		<table class='rowstyle-alt colstyle-alt no-arrow searchTable'>
			<tr>
				<td style='text-align:left;font-weight:bold;width:100%'>
					Total Stock <?php echo number_format($tstock,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	

				</td>
			</tr>
		</table>
<?php
}else if($_GET['RPT_TP']=='SCT'){
mysql_select_db("rtl");
?>
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;" width="50px">No.</th>
				<th style="text-align:left;">Sub Category</th>
				<th style="text-align:left;">Category</th>
				<th style="text-align:right;">Stock</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$query="SELECT 
					SUB.CAT_SUB_NBR CB_NBR,
					SUB.CAT_SUB_DESC CB_DESC, 
					CAT.CAT_DESC C_DESC, 
					INV_PRC,
					RCV.NAME AS RCV_NAME,
					SUM(COALESCE(RCV.ORD_Q,0)) AS RCV_ORD,
					SUM(COALESCE(SLS.SLS_Q,0)) AS SLS_Q,
					SUM(COALESCE(SHP.ORD_Q,0)) AS SHP_ORD,
					SUM(COALESCE(CSH.RTL_Q,0)) AS RTL_Q,
					SUM(COALESCE(RCV.ORD_Q,0) - COALESCE(SHP.ORD_Q,0) - COALESCE(CSH.RTL_Q,0) - COALESCE(SLS.SLS_Q,0)) AS BALANCE
				FROM INVENTORY INV 
				LEFT OUTER JOIN RTL.CAT CAT ON INV.CAT_NBR=CAT.CAT_NBR 
				LEFT OUTER JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR 
				LEFT OUTER JOIN RTL.CAT_SHLF SLF ON INV.CAT_DISC_NBR=SLF.CAT_SHLF_NBR 
				LEFT OUTER JOIN CMP.COMPANY COM ON INV.CO_NBR=COM.CO_NBR 
				LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID 
				INNER JOIN(
					SELECT 
						SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,RCV_CO_NBR
					FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
						LEFT OUTER JOIN CMP.COMPANY COM ON HED.RCV_CO_NBR=COM.CO_NBR
					WHERE HED.RCV_CO_NBR=".$CoNbr."
					GROUP BY INV_NBR,NAME
				) RCV ON RCV.INV_NBR=INV.INV_NBR 
				LEFT OUTER JOIN(
					SELECT 
						SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,SHP_CO_NBR
					FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
						LEFT OUTER JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
					WHERE SHP_CO_NBR=".$CoNbr."
					GROUP BY INV_NBR,NAME
				) SHP ON SHP.INV_NBR=INV.INV_NBR AND SHP.SHP_CO_NBR=RCV.RCV_CO_NBR 
				LEFT OUTER JOIN(
					SELECT 
						SUM(ORD_Q) AS SLS_Q,INV_NBR,NAME,SHP_CO_NBR
					FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
						LEFT OUTER JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
					WHERE SHP_CO_NBR=".$CoNbr." OR SHP_CO_NBR=".$CoNbrDef." AND IVC_TYP='SL'
					GROUP BY INV_NBR,NAME
				) SLS ON SLS.INV_NBR=INV.INV_NBR  
				LEFT OUTER JOIN (
					SELECT 
						RTL_BRC,SUM(RTL_Q) AS RTL_Q,CO_NBR 
					FROM RTL.CSH_REG 
					WHERE RTL_BRC!='' AND CSH_FLO_TYP='RT' AND CO_NBR=".$CoNbr."
					GROUP BY RTL_BRC,CO_NBR
				) CSH ON INV.INV_BCD=CSH.RTL_BRC AND CSH.CO_NBR=RCV.RCV_CO_NBR 
				GROUP BY SUB.CAT_SUB_NBR 
				ORDER BY SUB.CAT_SUB_DESC";
						
				//echo $query;
				$result=mysql_query($query);
				$alt="";
				$i=1;
				while($row=mysql_fetch_array($result))
				{					
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-position.php?CO_NBR=".$CoNbr."&SCATNBR=".$row['CB_NBR']."';".chr(34).">";
				echo "<td style='text-align:right'>".$i."</td>";
				echo "<td style='text-align:left'>".$row['CB_DESC']."</td>";
				echo "<td style='text-align:left'>".$row['C_DESC']."</td>";
				$stock=$row['RCV_ORD']-$row['SHP_ORD']-$row['RTLQ'];
				echo "<td style='text-align:right'>".$stock."</td>";
				echo "</tr>";
				$i++;
				$tstock+=$row['RCV_ORD']-$row['SHP_ORD']-$row['RTLQ'];
				$nilai+=$stock*$row['INV_PRC'];
				}	
			?>
		</tbody>
	</table>
		<table class='rowstyle-alt colstyle-alt no-arrow searchTable'>
			<tr>
				<td style='text-align:left;font-weight:bold;width:100%'>
					Total Stock <?php echo number_format($tstock,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

				</td>
			</tr>
		</table>
<?php 
}else{
?>	
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;">No.</th>
				<th>Kategori</th>
				<th>Sub Kategori</th>
				<th>Nama</th>
				<th>Supplier</th>
				<th>Barcode</th>
				<th>Harga Faktur</th>
				<th>Lokasi</th>
				<th>Terima</th>
				<th>Mutasi</th>
				<th>Jual</th>
				<th>Stock</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$query="SELECT 
					INV.INV_NBR,
					INV.NAME,
					CAT_DESC,
					INV_PRC,
					COM.NAME AS CO_NAME,
					CAT_SUB_DESC,
					INV_BCD,
					CAT_SHLF_DESC,
					RCV.NAME AS RCV_NAME,
					SUM(COALESCE(RCV.ORD_Q,0)) AS RCV_ORD,
					SUM(COALESCE(SLS.ORD_Q,0)) AS SLS_Q,
					SUM(COALESCE(SHP.ORD_Q,0)) AS SHP_ORD,
					SUM(COALESCE(CSH.RTL_Q,0)) AS RTL_Q,
					SUM(COALESCE(RCV.ORD_Q,0) - COALESCE(SHP.ORD_Q,0) - COALESCE(CSH.RTL_Q,0)  - COALESCE(RETSPL.ORD_Q,0) - COALESCE(SLS.ORD_Q,0) + COALESCE(COR.ORD_Q,0)) AS BALANCE
				FROM RTL.INVENTORY INV 
					LEFT OUTER JOIN RTL.CAT CAT ON INV.CAT_NBR=CAT.CAT_NBR 
					LEFT OUTER JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR 
					LEFT OUTER JOIN RTL.CAT_SHLF SLF ON INV.CAT_DISC_NBR=SLF.CAT_SHLF_NBR 
					LEFT OUTER JOIN CMP.COMPANY COM ON INV.CO_NBR=COM.CO_NBR 
					INNER JOIN(
						SELECT 
							SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,RCV_CO_NBR
						FROM RTL.RTL_STK_DET DET 
							LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
							LEFT OUTER JOIN CMP.COMPANY COM ON HED.RCV_CO_NBR=COM.CO_NBR
						WHERE HED.RCV_CO_NBR=$CoNbr
						GROUP BY INV_NBR,NAME
					) RCV ON RCV.INV_NBR=INV.INV_NBR 
					LEFT OUTER JOIN(
						SELECT 
							SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,SHP_CO_NBR
						FROM RTL.RTL_STK_DET DET 
							LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
							LEFT OUTER JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
						WHERE SHP_CO_NBR=$CoNbr
						GROUP BY INV_NBR,NAME
					) SHP ON SHP.INV_NBR=INV.INV_NBR AND SHP.SHP_CO_NBR=RCV.RCV_CO_NBR 
					LEFT OUTER JOIN(
						SELECT 
							SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,SHP_CO_NBR
						FROM RTL.RTL_STK_DET DET 
							LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
							LEFT OUTER JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
						WHERE SHP_CO_NBR=$CoNbr OR SHP_CO_NBR=$CoNbrDef AND IVC_TYP='SL'
						GROUP BY INV_NBR,NAME
					) SLS ON SLS.INV_NBR=INV.INV_NBR 
					LEFT OUTER JOIN(
						SELECT 
							SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,SHP_CO_NBR
						FROM RTL.RTL_STK_DET DET 
							LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
							LEFT OUTER JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
						WHERE DEL_F=0 AND SHP_CO_NBR=$CoNbr AND IVC_TYP='CR'
						GROUP BY INV_NBR,NAME
					) COR ON COR.INV_NBR=INV.INV_NBR AND COR.SHP_CO_NBR=RCV.RCV_CO_NBR 
					LEFT OUTER JOIN(
						SELECT 
							SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,SHP_CO_NBR,RCV_CO_NBR
						FROM RTL.RTL_STK_DET DET 
							LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
							LEFT OUTER JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
						WHERE DEL_F=0 AND IVC_TYP='RT' AND RCV_CO_NBR NOT IN ('".$WhseNbrDef."')
						GROUP BY INV_NBR,NAME
					) RETSPL ON RETSPL.INV_NBR=INV.INV_NBR AND RETSPL.SHP_CO_NBR=SHP.SHP_CO_NBR  
					LEFT OUTER JOIN(
						SELECT 
							RTL_BRC,SUM(RTL_Q) AS RTL_Q,CO_NBR 
						FROM RTL.CSH_REG 
						WHERE RTL_BRC!='' AND CSH_FLO_TYP='RT' AND CO_NBR=$CoNbr
						GROUP BY RTL_BRC,CO_NBR
					) CSH ON INV.INV_BCD=CSH.RTL_BRC AND CSH.CO_NBR=RCV.RCV_CO_NBR
					$SplNbr $SCatNbr
					GROUP BY INV.INV_NBR";
				//echo "<pre>".$query;
				$result=mysql_query($query);
				$rowcol="a";
				$alt="";
				while($row=mysql_fetch_array($result))
				{
					echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-list-edit.php?INV_NBR=".$row['INV_NBR']."';".chr(34).">";
					echo "<td class='std-first' align=right>".$row['INV_NBR']."</td>";
					echo "<td class='std'>".$row['CAT_DESC']."</td>";
					echo "<td class='std'>".$row['CAT_SUB_DESC']."</td>";
					echo "<td class='std'>".$row['NAME']."</td>";
					echo "<td class='std'>".$row['CO_NAME']."</td>";
					echo "<td class='std'>".$row['INV_BCD']."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['INV_PRC'],0,',','.')."</td>";
					echo "<td class='std'>".$row['RCV_NAME']."</td>";
					$balance=$row['RCV_ORD']-$row['SHP_ORD']-$row['RTL_Q']-$row['SLS_Q'];
					$sell=$row['RTL_Q']+$row['SLS_Q'];
					echo "<td class='std' style='text-align:right;'>".number_format($row['RCV_ORD'],0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['SHP_ORD'],0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($sell,0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['BALANCE'],0,',','.')."</td>";
					$harga=$balance*$row['INV_PRC'];
					echo "<td class='std' style='text-align:right;'>".number_format($harga,0,',','.')."</td>";						
					echo "</tr>";
					if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
					$stock+=$row['RCV_ORD']-$row['SHP_ORD']-$row['RTL_Q']-$row['SLS_Q'];
					$all+=$harga;
				}
			?>
		</tbody>
	</table>
		<table class='rowstyle-alt colstyle-alt no-arrow searchTable'>
			<tr>
				<td style='text-align:left;font-weight:bold;width:100%'>
					Total Stock <?php echo number_format($stock,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
					Total Rp. <?php echo number_format($all,0,',','.'); ?>

				</td>
			</tr>
		</table>
	<?php }?>
</div>

<?php
	if($_GET['WHSE']!=""){$whse="?WHSE=".$_GET['WHSE'];}else{$whse="";}
?>
<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>
<script>liveReqInit('livesearch','liveRequestResults','inventory-list-ls.php<?php echo $whse; ?>','','mainResult');</script>
</body>
</html>
