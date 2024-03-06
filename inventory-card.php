<?php
	include "framework/database/connect.php";

?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />

	<script src="framework/database/jquery.min.js"></script>
	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
</head>
<body>

<?php
if($_GET['COM']=='EDT'){
$query="SELECT INV_NBR, INV_BCD, NAME FROM RTL.INVENTORY WHERE INV_NBR='".$_GET['INV_NBR']."'";
//echo $query;
$result=mysql_query($query);
$row=mysql_fetch_array($result);

?>
<div class="toolbar">
	<p class="toolbar-left"><h3>Rincian Stock <?php echo "No. ".$row['INV_NBR']." | Barcode : ".$row['INV_BCD']." | Nama : ".$row['NAME'];?></h3></p>
</div>

<table>
		<tr>
			<td valign='top' align='center' width='504px'>
			<font style="color:#3464BC;align:center;font-weight:bold;">Stock Masuk & Mutasi<font>
			
		<table style="border-top: 1px solid #CACBCF;" id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:center;" width='75px'>Tanggal</th>
				<th style="text-align:center;" width='65px'>Nomor</th>
				<th style="text-align:center;" width='65px'>Ref</th>
				<th style="text-align:center;" width='205px'>Perusahaan</th>
				<th style="text-align:right;" width='45px'>Masuk</th>
				<th style="text-align:right;" width='45px'>Mutasi</th>
			</tr>
		</thead>
				<?php
				$query="SELECT DET.ORD_NBR,REF_NBR,
						SUM(CASE WHEN HED.IVC_TYP='RC' THEN ORD_Q ELSE 0 END) AS SORD,
						SUM(CASE WHEN HED.IVC_TYP='XF' THEN ORD_Q ELSE 0 END) AS SMUT,
						DET.INV_NBR,DATE(DET.CRT_TS) DTD,COM.NAME NAME  
						FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR=DET.ORD_NBR 
						LEFT OUTER JOIN CMP.COMPANY COM ON COM.CO_NBR=HED.SHP_CO_NBR 
						WHERE DET.INV_NBR='".$_GET['INV_NBR']."'";
	
				$result=mysql_query($query);
				while($row1=mysql_fetch_array($result))
				{					
				echo "<tr >";
				echo "<td style='text-align:center;'>".DATE('d-m-Y',strtotime($row1['DTD']))."</td>";
				echo "<td style='text-align:center;'>".$row1['ORD_NBR']."</td>";
				echo "<td style='text-align:center;'>".$row1['REF_NBR']."</td>";
				echo "<td style='text-align:center;'>".$row1['NAME']."</td>";
				echo "<td style='text-align:center;'>".$row1['SORD']."</td>";
				echo "<td style='text-align:center;'>".$row1['SMUT']."</td>";
				$tmstock+=$row1['SORD'];
				$tmut+=$row1['SMUT'];
				echo "</tr>";
				}
			?>
			</table>
			</td>
			<td valign='top' align='center' >
			<font style="color:#3464BC;align:center;font-weight:bold;">Stock Keluar<font>
			
			<table style="border-top: 1px solid #CACBCF;">
		<thead>
			<tr>
			<th style="text-align:center;" width='10%'>Tanggal</th>
				<th style="text-align:center;" width='10%'>Nomor</th>
				<th style="text-align:right;" width='10%'>Keluar</th>
			</tr>
		</thead>
		<?php
				$query="SELECT TRSC_NBR,RTL_Q SRTL,DATE(CRT_TS) DTR FROM RTL.CSH_REG WHERE INV_NBR='".$_GET['INV_NBR']."'";
		//echo $query;
				$result=mysql_query($query);
				$alt="";
				$OrdNbr='';
				while($row=mysql_fetch_array($result))
				{					
				echo "<tr >";
				echo "<td style='text-align:center;'>".DATE('d-m-Y',strtotime($row['DTR']))."</td>";
				echo "<td style='text-align:center;'>".$row['TRSC_NBR']."</td>";
				echo "<td style='text-align:center;'>".$row['SRTL']."</td>";
				$tkstock+=$row['SRTL'];
				echo "</tr>";
				}
			?>
			</table>
			</td>
			<td valign='top' align='center' >
			<font style="color:#3464BC;align:center;font-weight:bold;">Opname<font>
			
			<table style="border-top: 1px solid #CACBCF;">
		<thead>
			<tr>
			<th style="text-align:center;" width='10%'>Tanggal</th>
				<th style="text-align:center;" width='10%'>Nomor</th>
				<th style="text-align:right;" width='10%'>Selisih</th>
			</tr>
		</thead>
		<?php
				$query=" SELECT INV.INV_NBR,AUD.INV_BCD,DATE(AUD_TS) DTR FROM RTL.INV_AUD AUD
						 LEFT OUTER JOIN RTL.INVENTORY INV ON AUD.INV_BCD=INV.INV_BCD
						 WHERE INV.INV_NBR='".$_GET['INV_NBR']."'";
					
				$result=mysql_query($query);
				$alt="";
				$OrdNbr='';
				while($row=mysql_fetch_array($result))
				{					
				echo "<tr >";
				echo "<td style='text-align:center;'>".DATE('d-m-Y',strtotime($row['DTR']))."</td>";
				echo "<td style='text-align:center;'>".$row['AUD_NBR']."</td>";
				echo "<td style='text-align:center;'>".$row['INV_DIFF']."</td>";
				$tdiff+=$row['INV_DIFF'];
				echo "</tr>";
				}
			?>
			</table>
			</td>
			</tr>
	</table>
		<table class='rowstyle-alt colstyle-alt no-arrow searchTable'>
			<tr>
				<td style='text-align:left;font-weight:bold;width:100%; border-top: 1px solid #CACBCF; color:#3464BC;'>
					Total Stock Masuk : <?php echo number_format($tmstock,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
					Total Mutasi : <?php echo number_format($tmut,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
					Total Stock Keluar : <?php echo number_format($tkstock,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;						
					Total Selisih : <?php echo number_format($tdiff,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;						
					Sisa Stock : <?php $sisa=$tmstock-$tkstock+$tdiff; echo number_format($sisa,0,',','.'); ?>

				</td>
			</tr>
		</table>
</div>


<?php
}else if($_GET['COM']=='SRC'){
$Where=$_GET['KEY'];
echo $Where;
$searchQuery = mysql_real_escape_string(trim(strtoupper(urldecode($_REQUEST[s]))));
 ?>
 
 <table id="searchTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;">No.</th>
				<th style="text-align:left;">Sub Kategori</th>
				<th style="text-align:left;">Nama</th>
				<th style="text-align:left;">Barcode</th>
				<th style="text-align:right;">Masuk</th>
				<th style="text-align:right;">Mutasi</th>
				<th style="text-align:right;">Keluar</th>
				<th style="text-align:right;">Retur</th>
				<th style="text-align:right;">Opname</th>
				<th style="text-align:right;">Stock</th>
			</tr>
		</thead>
		<tbody>
			<?php
						$query="SELECT INV.INV_NBR INV_NBR, CAT.CAT_SUB_DESC CSUB, NAME, INV_BCD, 
						IFNULL(DET.ORD_Q,0) SORD, 
						IFNULL(XDET.SMUT,0) SMUT, 
						IFNULL(RDET.RET,0) RET, 
						IFNULL(SUM(RTL_Q),0) SRTL
						FROM RTL.INVENTORY INV 
						LEFT OUTER JOIN RTL.CAT_SUB CAT ON INV.CAT_SUB_NBR=CAT.CAT_SUB_NBR 
						LEFT OUTER JOIN (SELECT SUM(ORD_Q) ORD_Q,INV_NBR FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR=DET.ORD_NBR 
						WHERE HED.DEL_F=0 AND HED.IVC_TYP='RC' GROUP BY INV_NBR) DET ON DET.INV_NBR=INV.INV_NBR  
						LEFT OUTER JOIN 
						(SELECT INV_NBR, SUM(ORD_Q) SMUT FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR=DET.ORD_NBR 
						WHERE HED.DEL_F=0 AND HED.IVC_TYP='XF' GROUP BY INV_NBR) XDET ON XDET.INV_NBR=INV.INV_NBR  
						LEFT OUTER JOIN 
						(SELECT INV_NBR, SUM(ORD_Q) RET FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR=DET.ORD_NBR 
						WHERE HED.DEL_F=0 AND HED.IVC_TYP='RT' GROUP BY INV_NBR) RDET ON RDET.INV_NBR=INV.INV_NBR 
						LEFT OUTER JOIN (SELECT SUM(RTL_Q) RTL_Q,INV_NBR FROM RTL.CSH_REG GROUP BY INV_NBR) REG ON REG.INV_NBR=INV.INV_NBR  
						WHERE INV.DEL_NBR=0 AND (INV.INV_NBR LIKE '%$searchQuery%' OR INV_BCD LIKE '%$searchQuery%' OR NAME LIKE '%$searchQuery%' OR CAT.CAT_SUB_DESC LIKE '%$searchQuery%')  
						GROUP BY INV.INV_NBR LIMIT 100";
	
		//echo $query;
				$result=mysql_query($query);
				$alt="";
				while($row=mysql_fetch_array($result))
				{					
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-card.php?COM=EDT&INV_NBR=".$row['INV_NBR']."';".chr(34).">";
				echo "<td style='text-align:left'>".$row['INV_NBR']."</td>";
				echo "<td style='text-align:left'>".$row['CSUB']."</td>";
				echo "<td style='text-align:left'>".$row['NAME']."</td>";
				echo "<td style='text-align:left'>".$row['INV_BCD']."</td>";
				echo "<td style='text-align:right' width='75px'>".$row['SORD']."</td>";
				echo "<td style='text-align:right' width='75px'>".$row['SMUT']."</td>";
				echo "<td style='text-align:right' width='75px'>".$row['SRTL']."</td>";
				echo "<td style='text-align:right' width='75px'>".$row['RET']."</td>";
				echo "<td style='text-align:right' width='75px'>".$row['OPNM']."</td>";
				$tmsk+=$row['SORD'];$tmut+=$row['SMUT'];$tklr+=$row['SRTL'];$tret+=$row['RET'];$topn+=$row['OPNM'];
				$stok=$row['SORD']-$row['SMUT']-$row['SRTL']+$row['OPNM']-$row['RET'];
				echo "<td style='text-align:right' width='100px'>".$stok."</td>";
				echo "</tr>";
				$tstok+=$stok;
				}
			?>
		</tbody>
	</table>
		<table class='rowstyle-alt colstyle-alt no-arrow searchTable'>
			<tr>
				<td style='text-align:left;font-weight:bold;width:100%'>
					Total Masuk <?php echo number_format($tmsk,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
					Total Mutasi <?php echo number_format($tmut,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
					Total Keluar <?php echo number_format($tklr,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
					Total Opname <?php echo number_format($topn,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
					Total Sisa Stock <?php $tstok=$tmsk-$tmut-$tklr+$opn-$tret; echo number_format($tstok,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

				</td>
			</tr>
		</table>
<?php 
}else{
 ?>
 <div class="toolbar">
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>
<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;">No.</th>
				<th style="text-align:left;">Sub_Kategori</th>
				<th style="text-align:left;">Nama</th>
				<th style="text-align:left;">Barcode</th>
				<th style="text-align:right;">Masuk</th>
				<th style="text-align:right;">Mutasi</th>
				<th style="text-align:right;">Keluar</th>
				<th style="text-align:right;">Retur</th>
				<th style="text-align:right;">Opname</th>
				<th style="text-align:right;">Stock</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$query="SELECT INV.INV_NBR INV_NBR, CAT.CAT_SUB_DESC CSUB, NAME, INV_BCD, 
						IFNULL(DET.ORD_Q,0) SORD, 
						IFNULL(XDET.SMUT,0) SMUT, 
						IFNULL(RDET.RET,0) RET, 
						IFNULL(SUM(RTL_Q),0) SRTL
						FROM RTL.INVENTORY INV 
						LEFT OUTER JOIN RTL.CAT_SUB CAT ON INV.CAT_SUB_NBR=CAT.CAT_SUB_NBR 
						LEFT OUTER JOIN (SELECT SUM(ORD_Q) ORD_Q,INV_NBR FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR=DET.ORD_NBR 
						WHERE HED.DEL_F=0 AND HED.IVC_TYP='RC' GROUP BY INV_NBR) DET ON DET.INV_NBR=INV.INV_NBR  
						LEFT OUTER JOIN 
						(SELECT INV_NBR, SUM(ORD_Q) SMUT FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR=DET.ORD_NBR 
						WHERE HED.DEL_F=0 AND HED.IVC_TYP='XF' GROUP BY INV_NBR) XDET ON XDET.INV_NBR=INV.INV_NBR  
						LEFT OUTER JOIN 
						(SELECT INV_NBR, SUM(ORD_Q) RET FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR=DET.ORD_NBR 
						WHERE HED.DEL_F=0 AND HED.IVC_TYP='RT' GROUP BY INV_NBR) RDET ON RDET.INV_NBR=INV.INV_NBR 
						LEFT OUTER JOIN (SELECT SUM(RTL_Q) RTL_Q,INV_NBR FROM RTL.CSH_REG GROUP BY INV_NBR) REG ON REG.INV_NBR=INV.INV_NBR  
						WHERE INV.DEL_NBR=0 GROUP BY INV.INV_NBR ";
						$result=mysql_query($query);
				$alt="";
				while($row=mysql_fetch_array($result))
				{					
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-card.php?COM=EDT&INV_NBR=".$row['INV_NBR']."';".chr(34).">";
				echo "<td style='text-align:left'>".$row['INV_NBR']."</td>";
				echo "<td style='text-align:left'>".$row['CSUB']."</td>";
				echo "<td style='text-align:left'>".$row['NAME']."</td>";
				echo "<td style='text-align:left'>".$row['INV_BCD']."</td>";
				echo "<td style='text-align:right' width='75px'>".$row['SORD']."</td>";
				echo "<td style='text-align:right' width='75px'>".$row['SMUT']."</td>";
				echo "<td style='text-align:right' width='75px'>".$row['SRTL']."</td>";
				echo "<td style='text-align:right' width='75px'>".$row['RET']."</td>";
				echo "<td style='text-align:right' width='75px'>".$row['OPNM']."</td>";
				$tmsk+=$row['SORD'];$tmut+=$row['SMUT'];$tklr+=$row['SRTL'];$tret+=$row['RET'];$topn+=$row['OPNM'];
				$stok=$row['SORD']-$row['SMUT']-$row['SRTL']+$row['OPNM']-$row['RET'];
				echo "<td style='text-align:right' width='100px'>".$stok."</td>";
				echo "</tr>";
				$tstok+=$stok;
				}	
			?>
		</tbody>
	</table>
		<table class='rowstyle-alt colstyle-alt no-arrow searchTable'>
			<tr>
				<td style='text-align:left;font-weight:bold;width:100%'>
					Total Masuk <?php echo number_format($tmsk,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
					Total Mutasi <?php echo number_format($tmut,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
					Total Keluar <?php echo number_format($tklr,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
					Total Opname <?php echo number_format($topn,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
					Total Sisa Stock <?php $tstok=$tmsk-$tmut-$tklr+$opn; echo number_format($tstok,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		

				</td>
			</tr>
		</table>
</div>
<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>
<script>liveReqInit('livesearch','liveRequestResults','inventory-card.php?COM=SRC&KEY=<?php echo $Key;?>','','mainResult');</script>
<?php 
}
?>
</body>
</html>

