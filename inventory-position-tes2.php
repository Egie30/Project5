<?php
	include "framework/database/connect.php";
	$CoNbr=$_GET['CO_NBR'];
	$SplNbr=$_GET['SPLNBR'];
	$SCatNbr=$_GET['SCATNBR'];
	$InvNbr=$_GET['INV_NBR'];
	$FileNm=basename(__FILE__, '');
	$Where='WHERE RCV.ORD_Q>0 ';
	if ($SplNbr!='') {$SplNbr=' AND COM.CO_NBR='.$SplNbr;}
	if ($SCatNbr!='') {$SCatNbr=' AND SUB.CAT_SUB_NBR='.$SCatNbr;}
	if($CoNbr==$CoNbrDef){
	$RcvOrd="ORD_Q";
	$RcXf="IVC_TYP='RC' OR IVC_TYP='XF'";
	$RtlMts="<th class='sortable'>Mts.Masuk</th><th class='sortable'>Mts.Keluar</th><th class='sortable'>Retur</th><th class='sortable'>Check Out</th><th class='sortable'>Jual</th>";
	$Retur=" AND RETMTS.SHP_CO_NBR=$CoNbr ";
	}
	if($CoNbr==$WhseNbrDef){
	$RcvOrd="IF(IVC_TYP IN ('XF','CR'),'0',ORD_Q)";
	$RcXf="IVC_TYP='RC' OR IVC_TYP='RT' OR IVC_TYP='XF' OR IVC_TYP='CR'";
	$Retur=" AND RETMTS.RCV_CO_NBR=$CoNbr ";
	$RtlMts="<th class='sortable'>Pembelian</th><th class='sortable'>Mts.Masuk</th><th class='sortable'>Retur</th><th class='sortable'>Mts.Keluar</th>";
	$RtlQ="COALESCE(SHP.ORD_Q,0) AS ";
	}
				
	$query="SELECT INV.INV_NBR,INV.NAME,CAT_DESC,INV_PRC,PRC,COM.NAME AS CO_NAME,CAT_SUB_DESC,INV_BCD,
			CAT_SHLF_DESC,RCV.ORD_Q AS RCV_ORD,RET.ORD_Q AS RET,RETMTS.ORD_Q AS RETMTS,
			RETSPL.ORD_Q AS RETSPL,COR.ORD_Q AS COR,COALESCE(SHP.ORD_Q,0) AS SHP_ORD,$RtlQ RTL_Q,RCV.NAME AS RCV_NAME,MOV_Q
			FROM RTL.INVENTORY INV LEFT OUTER JOIN
			RTL.CAT CAT ON INV.CAT_NBR=CAT.CAT_NBR LEFT OUTER JOIN
			RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR LEFT OUTER JOIN
			CMP.COMPANY COM ON INV.CO_NBR=COM.CO_NBR INNER JOIN
			RTL.CAT_SHLF SLF ON INV.CAT_DISC_NBR=SLF.CAT_SHLF_NBR LEFT OUTER JOIN
			(
			SELECT SUM($RcvOrd) AS ORD_Q,INV_NBR,NAME,RCV_CO_NBR
			FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN
			RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR LEFT OUTER JOIN
			CMP.COMPANY COM ON HED.RCV_CO_NBR=COM.CO_NBR
			WHERE DEL_F=0 AND HED.RCV_CO_NBR=$CoNbr AND ($RcXf)  
			GROUP BY INV_NBR,NAME
			) RCV ON RCV.INV_NBR=INV.INV_NBR 
			LEFT OUTER JOIN
			(
			SELECT SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,SHP_CO_NBR
			FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN
			RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR LEFT OUTER JOIN
			CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
			WHERE DEL_F=0 AND SHP_CO_NBR=$CoNbr AND IVC_TYP='XF' 
			GROUP BY INV_NBR,NAME
			) SHP ON SHP.INV_NBR=INV.INV_NBR AND SHP.SHP_CO_NBR=RCV.RCV_CO_NBR 
			LEFT OUTER JOIN
			(
			SELECT SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,SHP_CO_NBR
			FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN
			RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR LEFT OUTER JOIN
			CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
			WHERE DEL_F=0 AND SHP_CO_NBR=$CoNbr AND IVC_TYP='RT'
			GROUP BY INV_NBR,NAME
			) RET ON RET.INV_NBR=INV.INV_NBR AND RET.SHP_CO_NBR=RCV.RCV_CO_NBR 
			LEFT OUTER JOIN
			(
			SELECT SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,SHP_CO_NBR,RCV_CO_NBR
			FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN
			RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR LEFT OUTER JOIN
			CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
			WHERE DEL_F=0 AND IVC_TYP='XF' AND (SHP_CO_NBR=$CoNbr OR RCV_CO_NBR=$CoNbr) 
			GROUP BY INV_NBR,RCV_CO_NBR
			) RETMTS ON RETMTS.INV_NBR=INV.INV_NBR $Retur    
			LEFT OUTER JOIN
			(
			SELECT SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,SHP_CO_NBR,RCV_CO_NBR
			FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN
			RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR LEFT OUTER JOIN
			CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
			WHERE DEL_F=0 AND IVC_TYP='RT' AND RCV_CO_NBR NOT IN ('".$WhseNbrDef."')
			GROUP BY INV_NBR,NAME
			) RETSPL ON RETSPL.INV_NBR=INV.INV_NBR AND RETSPL.SHP_CO_NBR=SHP.SHP_CO_NBR  
			LEFT OUTER JOIN
			(
			SELECT SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,SHP_CO_NBR
			FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN
			RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR LEFT OUTER JOIN
			CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
			WHERE DEL_F=0 AND SHP_CO_NBR=$CoNbr AND IVC_TYP='CR'
			GROUP BY INV_NBR,NAME
			) COR ON COR.INV_NBR=INV.INV_NBR AND COR.SHP_CO_NBR=RCV.RCV_CO_NBR 
			LEFT OUTER JOIN
			(
			SELECT RTL_BRC,SUM(RTL_Q) AS RTL_Q,CO_NBR FROM RTL.CSH_REG WHERE RTL_BRC!='' AND CSH_FLO_TYP='RT'
			AND CO_NBR=$CoNbr
			GROUP BY RTL_BRC,CO_NBR
			) CSH ON INV.INV_BCD=CSH.RTL_BRC AND CSH.CO_NBR=RCV.RCV_CO_NBR
			LEFT OUTER JOIN
			(
			SELECT INV.INV_BCD IMV_BCD,SUM(MOV_Q) AS MOV_Q,RCV_CO_NBR FROM RTL.INV_MOV MOV 
			LEFT OUTER JOIN RTL.RTL_STK_DET DET ON DET.ORD_DET_NBR=MOV.ORD_DET_NBR 
			LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
			LEFT OUTER JOIN RTL.INVENTORY INV ON INV.INV_NBR=DET.INV_NBR  
			WHERE MOV.ORD_DET_NBR!='' AND MOV.DEL_NBR=0 
			AND HED.RCV_CO_NBR=$CoNbr
			GROUP BY INV.INV_BCD,HED.RCV_CO_NBR
			) IMV ON INV.INV_BCD=IMV.IMV_BCD AND IMV.RCV_CO_NBR=RCV.RCV_CO_NBR
			 $Where $SCatNbr ";
		
if($_GET['COM']=='DTL'){
	$query="SELECT CAT_DESC,DATE(HED.CRT_TS) CRT_TS,CAT_SUB_DESC,INV.NAME, SHP_CO_NBR,RCV_CO_NBR,
			COM1.NAME CO_NAME1,COM2.NAME CO_NAME2,INV.INV_BCD,INV.INV_PRC,PRC, DET.INV_NBR,ORD_Q,DET.CRT_TS,IVC_DESC,HED.ORD_NBR ORD_NBR 
			FROM RTL.RTL_STK_DET DET 
			LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR=DET.ORD_NBR 
			LEFT OUTER JOIN RTL.IVC_TYP TYP ON TYP.IVC_TYP=HED.IVC_TYP 
			LEFT OUTER JOIN RTL.INVENTORY INV ON INV.INV_NBR=DET.INV_NBR 
			LEFT OUTER JOIN RTL.CAT  ON CAT.CAT_NBR=INV.CAT_NBR 
			LEFT OUTER JOIN RTL.CAT_SUB CTS  ON CTS.CAT_SUB_NBR=INV.CAT_SUB_NBR 
			LEFT OUTER JOIN CMP.COMPANY COM1 ON COM1.CO_NBR=HED.SHP_CO_NBR 
			LEFT OUTER JOIN CMP.COMPANY COM2 ON COM2.CO_NBR=HED.RCV_CO_NBR 
			WHERE HED.DEL_F=0 AND DET.INV_NBR=$InvNbr   AND (HED.SHP_CO_NBR=$CoNbr OR HED.RCV_CO_NBR=$CoNbr) AND HED.IVC_TYP!='PO' "; //echo $query;
	if($CoNbr==$CoNbrDef){
	$query.=" UNION
			SELECT CAT_DESC,DATE(REG.CRT_TS) CRT_TS,CAT_SUB_DESC,INV.NAME,REG.CO_NBR SHP_CO_NBR,REG.CO_NBR RCV_CO_NBR,
			COM1.NAME CO_NAME1,COM2.NAME CO_NAME2,INV.INV_BCD,INV.INV_PRC,PRC, REG.INV_NBR,RTL_Q,
			REG.CRT_TS,CSH_FLO_DESC,TRSC_NBR ORD_NBR 
			FROM RTL.CSH_REG REG  
			LEFT OUTER JOIN CSH_FLO_TYP TYP ON TYP.CSH_FLO_TYP=REG.CSH_FLO_TYP 
			LEFT OUTER JOIN RTL.INVENTORY INV ON INV.INV_NBR=REG.INV_NBR 
			LEFT OUTER JOIN RTL.CAT ON CAT.CAT_NBR=INV.CAT_NBR 
			LEFT OUTER JOIN RTL.CAT_SUB CTS ON CTS.CAT_SUB_NBR=INV.CAT_SUB_NBR 
			LEFT OUTER JOIN CMP.COMPANY COM1 ON COM1.CO_NBR=REG.CO_NBR 
			LEFT OUTER JOIN CMP.COMPANY COM2 ON COM2.CO_NBR=REG.CO_NBR 
			WHERE REG.CO_NBR=$CoNbr AND REG.INV_NBR=$InvNbr "; //echo $query;
	$query.=" UNION
			SELECT CAT_DESC,DATE(MOV.CRT_TS) CRT_TS,CAT_SUB_DESC,INV.NAME,INV.CO_NBR SHP_CO_NBR,RCV_CO_NBR,
			COM1.NAME CO_NAME1,COM2.NAME CO_NAME2,INV.INV_BCD,INV.INV_PRC,PRC, DET.INV_NBR,-MOV_Q,
			MOV.CRT_TS,'Check Out' CSH_FLO_DESC,'-' ORD_NBR 
			FROM RTL.INV_MOV MOV   
			LEFT OUTER JOIN RTL.RTL_STK_DET DET ON DET.ORD_DET_NBR=MOV.ORD_DET_NBR 
			LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
			LEFT OUTER JOIN RTL.INVENTORY INV ON INV.INV_NBR=DET.INV_NBR  
			LEFT OUTER JOIN RTL.CAT ON CAT.CAT_NBR=INV.CAT_NBR 
			LEFT OUTER JOIN RTL.CAT_SUB CTS ON CTS.CAT_SUB_NBR=INV.CAT_SUB_NBR 
			LEFT OUTER JOIN CMP.COMPANY COM1 ON COM1.CO_NBR=INV.CO_NBR 
			LEFT OUTER JOIN CMP.COMPANY COM2 ON COM2.CO_NBR=INV.CO_NBR 
			WHERE RCV_CO_NBR=$CoNbr AND INV.INV_NBR=$InvNbr "; //echo $query;
	}
//echo $query;	
?>
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<script src="framework/database/jquery.min.js"></script>

<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead style="font-size:10pt;">
			<tr>
				<th style="text-align:right;">No.</th>
				<th>Barcode</th>
				<th>Nama</th>
				<th>Perusahaan</th>
				<th>Tanggal</th>
				<th>Keterangan</th>
				<th>No. Nota</th>
				<th>Faktur</th>
				<th>Harga</th>
				<th>Jumlah</th>
				<th>Balance</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$result=mysql_query($query);
				$rowcol="a";
				$alt="";
				while($row=mysql_fetch_array($result))
				{
					echo "<tr $alt style='cursor:pointer;'".chr(34).">";
					echo "<td class='std-first' align=right>".$row['INV_NBR']."</td>";
					echo "<td class='barcode'>".$row['INV_BCD']."</td>";
					echo "<td class='std'>".$row['NAME']."</td>";
					if($CoNbr==$row['SHP_CO_NBR']){
					echo "<td class='std'>".$row['CO_NAME2']."</td>";
					}else{ echo "<td class='std'>".$row['CO_NAME1']."</td>";
					}
					echo "<td class='std-first' align=right>".$row['CRT_TS']."</td>";
					if($CoNbr==$CoNbrDef && $row['IVC_DESC']=='Retur'){
					echo "<td class='std'>".$row['IVC_DESC']." Mutasi</td>";
					$stock-=$row['ORD_Q'];
					$Min="-";
					}else if($CoNbr==$CoNbrDef && $row['IVC_DESC']=='Retail'){
					echo "<td class='std'>Penjualan</td>";
					$stock-=$row['ORD_Q'];
					$Min="-";
					}else if($CoNbr==$WhseNbrDef && $row['IVC_DESC']=='Retur'){
					echo "<td class='std'>".$row['IVC_DESC']."</td>";
					$stock-=$row['ORD_Q'];
					$Min="-";
					}else if(($CoNbr==$WhseNbrDef || $CoNbr==$CoNbrDef) && $row['IVC_DESC']=='Mutasi'){
					if($row['SHP_CO_NBR']==$CoNbr){$Mts='Keluar';$Min="-";$stock-=$row['ORD_Q'];}else{$Mts='Masuk';$Min="";$stock+=$row['ORD_Q'];}
						echo "<td class='std'>".$row['IVC_DESC']." $Mts</td>";					
					}else{
						echo "<td class='std'>".$row['IVC_DESC']."</td>";
						$stock+=$row['ORD_Q'];
						$Min="";
					}
					echo "<td class='std'>".$row['ORD_NBR']."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['INV_PRC'],0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['PRC'],0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".$Min.number_format($row['ORD_Q'],0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($stock,0,',','.')."</td>";						
					echo "</tr>";
					if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
					
					$all+=$harga;
				}
			?>
		</tbody>
	</table>
</div>
<?php
}else if($_GET['COM']=='LS'){
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	$CoNbr=$_GET['CO_NBR'];
	$query=$query." AND (CAT_DESC LIKE '%".$searchQuery."%' OR CAT_SUB_DESC LIKE '%".$searchQuery."%' OR INV.NAME LIKE '%".$searchQuery."%' OR INV_BCD LIKE '%".$searchQuery."%')";
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
				<th>Kategori</th>
				<th>Sub Kategori</th>
				<th>Nama</th>
				<th>Supplier</th>
				<th>Barcode</th>
				<th>Faktur</th>
				<th>Harga</th>
				<th>Lokasi</th>
				<?php echo $RtlMts;?>
				<th>Koreksi</th>
				<th>Stock</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$result=mysql_query($query);
				$rowcol="a";
				$alt="";
				while($row=mysql_fetch_array($result))
				{
					echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='".$FileNm."?COM=DTL&INV_NBR=".$row['INV_NBR']."&CO_NBR=".$CoNbr."';".chr(34).">";
					echo "<td class='std-first' align=right>".$row['INV_NBR']."</td>";
					echo "<td class='std'>".$row['CAT_DESC']."</td>";
					echo "<td class='std'>".$row['CAT_SUB_DESC']."</td>";
					echo "<td class='std'>".$row['NAME']."</td>";
					echo "<td class='std'>".$row['CO_NAME']."</td>";
					echo "<td class='barcode'>".$row['INV_BCD']."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['INV_PRC'],0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['PRC'],0,',','.')."</td>";
					echo "<td class='std'>".$row['RCV_NAME']."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['RCV_ORD'],0,',','.')."</td>";
					if($row['RETMTS']!=0){if($CoNbr==$WhseNbrDef){$MinQ="";}else{$MinQ="-";}}else{$MinQ="";}
					echo "<td class='std' style='text-align:right;'>".$MinQ.number_format($row['RETMTS'],0,',','.')."</td>";
					if($row['RETSPL']!=0){$MinQ="-";}else{$MinQ="";}
					echo "<td class='std' style='text-align:right;'>".$MinQ.number_format($row['RETSPL'],0,',','.')."</td>";
					if($CoNbr==$CoNbrDef){
					if($row['MOV_Q']!=0){$MinQ="-";}else{$MinQ="";}
					echo "<td class='std' style='text-align:right;'>".$MinQ.number_format($row['MOV_Q'],0,',','.')."</td>";
					}
					if($row['RTL_Q']!=0){$MinQ="-";}else{$MinQ="";}
					echo "<td class='std' style='text-align:right;'>".$MinQ.number_format($row['RTL_Q'],0,',','.')."</td>";
					$balance=$row['RCV_ORD']-$row['RTL_Q']-$row['RETMTS']-$row['RETSPL']+$row['COR']-$row['MOV_Q'];
					echo "<td class='std' style='text-align:right;'>".number_format($row['COR'],0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($balance,0,',','.')."</td>";
					$harga=$balance*$row['INV_PRC'];
					echo "<td class='std' style='text-align:right;'>".number_format($harga,0,',','.')."</td>";						
					echo "</tr>";
					if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
					$stock+=$row['RCV_ORD']-$row['SHP_ORD']-$row['RTL_Q']-$row['RET']+$row['COR'];
					$all+=$harga;
				}	
			?>
		</tbody>
	</table>

<?php
}else{	
if($_GET['EXPORT']=='XLS'){	
header("Cache-Control: no-cache, no-store, must-revalidate");  
header("Content-Type: application/vnd.ms-excel");  
header("Content-Disposition: attachment; filename=Inventaris_toko.xls");  
echo "<style> .barcode{ mso-number-format:\@; } </style>";
}else{
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

<div class="toolbar">
	<p class="toolbar-right">
	<span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" />
	</p>
</div>


<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
<?php
}
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
				<th>Faktur</th>
				<th>Harga</th>
				<th>Lokasi</th>
				<?php echo $RtlMts;?>
				<th>Koreksi</th>
				<th>Stock</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
			<?php 
				$result=mysql_query($query);
				$rowcol="a";
				$alt="";
				while($row=mysql_fetch_array($result))
				{
					echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='".$FileNm."?COM=DTL&INV_NBR=".$row['INV_NBR']."&CO_NBR=".$CoNbr."';".chr(34).">";
					echo "<td class='std-first' align=right>".$row['INV_NBR']."</td>";
					echo "<td class='std'>".$row['CAT_DESC']."</td>";
					echo "<td class='std'>".$row['CAT_SUB_DESC']."</td>";
					echo "<td class='std'>".$row['NAME']."</td>";
					echo "<td class='std'>".$row['CO_NAME']."</td>";
					echo "<td class='barcode'>".$row['INV_BCD']."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['INV_PRC'],0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['PRC'],0,',','.')."</td>";
					echo "<td class='std'>".$row['RCV_NAME']."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['RCV_ORD'],0,',','.')."</td>";
					if($row['RETMTS']!=0){if($CoNbr==$WhseNbrDef){$MinQ="";}else{$MinQ="-";}}else{$MinQ="";}
					echo "<td class='std' style='text-align:right;'>".$MinQ.number_format($row['RETMTS'],0,',','.')."</td>";
					if($row['RETSPL']!=0){$MinQ="-";}else{$MinQ="";}
					echo "<td class='std' style='text-align:right;'>".$MinQ.number_format($row['RETSPL'],0,',','.')."</td>";
					if($CoNbr==$CoNbrDef){
					if($row['MOV_Q']!=0){$MinQ="-";}else{$MinQ="";}
					echo "<td class='std' style='text-align:right;'>".$MinQ.number_format($row['MOV_Q'],0,',','.')."</td>";
					}
					if($row['RTL_Q']!=0){$MinQ="-";}else{$MinQ="";}
					echo "<td class='std' style='text-align:right;'>".$MinQ.number_format($row['RTL_Q'],0,',','.')."</td>";
					$balance=$row['RCV_ORD']-$row['RTL_Q']-$row['RETMTS']-$row['RETSPL']+$row['COR']-$row['MOV_Q'];
					echo "<td class='std' style='text-align:right;'>".number_format($row['COR'],0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($balance,0,',','.')."</td>";
					$harga=$balance*$row['INV_PRC'];
					echo "<td class='std' style='text-align:right;'>".number_format($harga,0,',','.')."</td>";						
					echo "</tr>";
					if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
					$stock+=$row['RCV_ORD']-$row['SHP_ORD']-$row['RTL_Q']-$row['RET']+$row['COR'];
					$all+=$harga;
				}	
			?>
		</tbody>
	</table>
<?php
if($_GET['EXPORT']=='XLS'){	
exit();
}else{
?>	
		<table class='rowstyle-alt colstyle-alt no-arrow searchTable'>
			<tr>
				<td style='text-align:left;font-weight:bold;width:100%'>
					Total Stock <?php echo number_format($stock,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
					Total Rp. <?php echo number_format($all,0,',','.'); ?>

				</td>
			</tr>
		</table>
	
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
<script>liveReqInit('livesearch','liveRequestResults','<?php echo $FileName."?COM=LS&CO_NBR=".$CoNbr; ?>','','mainResult');</script>
</body>
</html>
<?php
}
}
?>