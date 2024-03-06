<?php
	include "framework/database/connect.php";
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	$CoNbr=$CoNbrDef;
	$AudDt=$_GET['AUD_DT'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />

<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
</head>
<body>

<div class="toolbar">
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult">

	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;">No.</th>
				<th>Kategory</th>
				<th>Sub Kategory</th>
				<th>Nama</th>
				<th>Supplier</th>
				<th>Barcode</th>
				<th>Stock</th>
				<th>Audit</th>
				<th>Jual</th>
			</tr>
		</thead>
		<tbody>
			<?php
				//Get barcode listing
				$query="SELECT AUD.INV_BCD,INV.INV_NBR FROM RTL.INV_AUD AUD 
						LEFT OUTER JOIN RTL.INVENTORY INV ON AUD.INV_BCD=INV.INV_BCD WHERE DATE(AUD_TS)='$AudDt'"; //echo $query;
				$result=mysql_query($query);
				while($row=mysql_fetch_array($result)){
					$InvBcd.="'".$row['INV_BCD']."',";
					if($row['INV_NBR']!=""){$InvNbr.=$row['INV_NBR'].",";}
				}

				$InvBcd=substr($InvBcd,0,-1);
				$InvNbr=substr($InvNbr,0,-1);

				//The short query goes here
				$query="SELECT INV.INV_NBR,INV.NAME,CAT_DESC,COM.NAME AS CO_NAME,CAT_SUB_DESC,INV.INV_BCD,CAT_SHLF_DESC,AUD.DEL_NBR,
				RCV.ORD_Q AS RCV_ORD,COALESCE(SHP.ORD_Q,0) AS SHP_ORD,CSH.RTL_Q,AUD_Q,COALESCE(PRV.RTL_Q,0) AS PRV_Q,RCV.NAME AS RCV_NAME,INV_PRC,PRC
				FROM RTL.INVENTORY INV LEFT OUTER JOIN
				RTL.CAT CAT ON INV.CAT_NBR=CAT.CAT_NBR LEFT OUTER JOIN
				RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR LEFT OUTER JOIN
				RTL.CAT_SHLF SLF ON INV.CAT_DISC_NBR=SLF.CAT_SHLF_NBR LEFT OUTER JOIN
				CMP.COMPANY COM ON INV.CO_NBR=COM.CO_NBR LEFT OUTER JOIN
				(
				SELECT SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,RCV_CO_NBR
				FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN
				RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR LEFT OUTER JOIN
				CMP.COMPANY COM ON HED.RCV_CO_NBR=COM.CO_NBR
				WHERE HED.RCV_CO_NBR=$CoNbr
				AND DET.INV_NBR IN ('$InvNbr')
				GROUP BY INV_NBR,NAME
				) RCV ON RCV.INV_NBR=INV.INV_NBR LEFT OUTER JOIN
				(
				SELECT SUM(ORD_Q) AS ORD_Q,INV_NBR,NAME,SHP_CO_NBR
				FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN
				RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR LEFT OUTER JOIN
				CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
				WHERE SHP_CO_NBR=$CoNbr
				AND DET.INV_NBR IN ('$InvNbr')
				GROUP BY INV_NBR,NAME
				) SHP ON SHP.INV_NBR=INV.INV_NBR AND SHP.SHP_CO_NBR=RCV.RCV_CO_NBR LEFT OUTER JOIN
				(
				SELECT RTL_BRC,SUM(RTL_Q) AS RTL_Q,CO_NBR FROM RTL.CSH_REG REG WHERE RTL_BRC!='' AND CSH_FLO_TYP='RT'
				AND CO_NBR=$CoNbr
				AND CRT_TS>=(SELECT MIN(AUD_TS) FROM RTL.INV_AUD AUD WHERE DATE(AUD_TS)='$AudDt' AND AUD.INV_BCD=REG.RTL_BRC)
				AND RTL_BRC IN($InvBcd)
				GROUP BY RTL_BRC,CO_NBR
				) CSH ON INV.INV_BCD=CSH.RTL_BRC AND CSH.CO_NBR=RCV.RCV_CO_NBR LEFT OUTER JOIN
				(
				SELECT RTL_BRC,SUM(RTL_Q) AS RTL_Q,CO_NBR FROM RTL.CSH_REG REG WHERE RTL_BRC!='' AND CSH_FLO_TYP='RT'
				AND CO_NBR=$CoNbr
				AND CRT_TS>=(SELECT MIN(AUD_TS) FROM RTL.INV_AUD AUD WHERE DATE(AUD_TS)='$AudDt' AND AUD.INV_BCD=REG.RTL_BRC)
				AND RTL_BRC IN($InvBcd)
				GROUP BY RTL_BRC,CO_NBR
				) PRV ON INV.INV_BCD=PRV.RTL_BRC AND PRV.CO_NBR=RCV.RCV_CO_NBR INNER JOIN
				(
				SELECT INV_BCD,DEL_NBR,SUM(AUD_Q) AS AUD_Q FROM RTL.INV_AUD WHERE DEL_NBR=0 AND DATE(AUD_TS)='$AudDt'
				GROUP BY INV_BCD
				) AUD ON INV.INV_BCD=AUD.INV_BCD
				WHERE INV.INV_BCD IN ($InvBcd) AND (INV.NAME LIKE '%".$searchQuery."%' OR INV.INV_BCD LIKE '%".$searchQuery."%')";
				$result=mysql_query($query);
				$rowcol="a";
				$alt="";
				while($row=mysql_fetch_array($result))
				{
					echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-audit-journal.php?AUD_DT=$AudDt&INV_BCD=".$row['INV_BCD']."';".chr(34).">";
					echo "<td class='std-first' align=right>".$row['INV_NBR']."</td>";
					echo "<td class='std'>".$row['CAT_DESC']."</td>";
					echo "<td class='std'>".$row['CAT_SUB_DESC']."</td>";
					echo "<td class='std'>".$row['NAME']."</td>";
					echo "<td class='std'>".$row['CO_NAME']."</td>";
					echo "<td class='std'>".$row['INV_BCD']."</td>";
					$balance=$row['RCV_ORD']-$row['SHP_ORD']-$row['RTL_Q']-$row['PRV_Q'];
					echo "<td class='std' style='text-align:right;'>".number_format($balance,0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['AUD_Q'],0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['RTL_Q'],0,',','.')."</td>";
					echo "</tr>";
					if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
					$subTotal=$row['PRC']*$row['RTL_Q'];
					$sub+=$subTotal;
					$item+=$row['RTL_Q'];
					$hb+=$row['INV_PRC']*$row['RTL_Q'];
					$hj+=$row['PRC']*$row['RTL_Q'];					
				}		
			?>
		</tbody>
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

<script>liveReqInit('livesearch','liveRequestResults','inventory-list-ls.php<?php echo $whse; ?>','','mainResult');</script>
</body>
</html>