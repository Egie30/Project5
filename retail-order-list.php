<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	$CoNbr	= $_GET['CO_NBR'];
	$IvcTyp	= $_GET['IVC_TYP'];
	$type	= $_GET['TYP'];
	$ShpNbr	= $_GET['SHP_NBR'];
	
	if($type == "EST"){
		$headtable 	= "RTL.RTL_ORD_HEAD_EST";
		$detailtable= "RTL.RTL_ORD_DET_EST";
	}else{
		$headtable 	= "RTL.RTL_ORD_HEAD";
		$detailtable= "RTL.RTL_ORD_DET";
	}
	
	if ($ShpNbr!='') {$ShpNbr='AND SHP_CO_NBR='.$ShpNbr;}
	
	//if($IvcTyp!=''){$where="WHERE HED.IVC_TYP='".$IvcTyp."'";}	
	
	if($CoNbr=='1' || $CoNbr=='4'){$where="WHERE SHP_CO_NBR='".$CoNbr."' AND HED.IVC_TYP='".$IvcTyp."'";}
	//elseif($CoNbr=='4'){$where="WHERE SHP_CO_NBR='".$CoNbr."' AND HED.IVC_TYP='".$IvcTyp."'";}
	else{$where="WHERE HED.IVC_TYP='".$IvcTyp."'";}
	
	//Process delete entry
	if($_GET['DEL']!="")
	{
		$query="UPDATE ". $headtable ." SET DEL_F=1 WHERE ORD_NBR=".$_GET['DEL'];
		$result=mysql_query($query);
	}
	if($_GET['SEL']=="DEB")
	{
		$where="WHERE TOT_REM>0";
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>
	
</head>

<body>
<?php
if($_GET['COMTP']==1){
?>
<img class="toolbar-left" style="cursor:pointer" src="img/close.png" onclick="parent.document.getElementById('printDigitalPopupEdit').style.display='none';parent.document.getElementById('fade').style.display='none'"></a></p>
<!--location.href='retail-stock-edit.php?IVC_TYP=RC&ORD_NBR=43'
<input class="process" type="submit" value="Simpan" onclick="parent.document.getElementById('content').src=
	'retail-stock-edit.php?IVC_TYP=RC&ORD_NBR=43';
	parent.document.getElementById('printDigitalPopupEdit').style.display='none';
	parent.document.getElementById('fade').style.display='none';"
/>	-->
</form>
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class="sortable" style="text-align:right;">No.</th>
				<th class="sortable" style="text-align:right;">Item</th>
				<th class="sortable">Pengirim</th>
				<th class="sortable">Tgl Order</th>
				<th class="sortable" style="text-align:right;">Jumlah</th>
				<?php
					if($_GET['SEL']=="DEB"){
						echo "<th class='sortable'>Jatuh Tempo</th>";
					}
				?>
				<th class="sortable" style="text-align:right;">Pembuat</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT 
				HED.ORD_NBR,
				ORD_DTE,
				IVC_DESC,
				ORD_Q_TOT,
				REF_NBR,
				SHP_CO_NBR,
				RCV_CO_NBR,
				SHP.NAME AS SHP_NAME,
				COALESCE(RCV.NAME,'Tunai') AS RCV_NAME,
				HED.FEE_MISC,
				TOT_AMT,
				PYMT_DOWN,
				PYMT_REM,
				TOT_REM,
				DL_TS,
				SPC_NTE,
				HED.CRT_TS,
				HED.CRT_NBR,
				CRT.NAME AS CRT_NAME,
				HED.UPD_TS,
				HED.UPD_NBR,DATEDIFF(DATE_ADD(ORD_DTE,INTERVAL COALESCE(SHP.PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS SHP_PAST_DUE
			FROM ". $headtable ." HED 
				LEFT OUTER JOIN (
					SELECT 
						HED.ORD_NBR, 
						SUM(ORD_Q) AS ORD_Q_TOT
					FROM ". $headtable ." HED 
					INNER JOIN RTL.RTL_STK_DET DET ON HED.ORD_NBR=DET.ORD_NBR
					GROUP BY DET.ORD_NBR ASC
				) AS DET ON HED.ORD_NBR=DET.ORD_NBR
				LEFT OUTER JOIN RTL.IVC_TYP IVC ON HED.IVC_TYP=IVC.IVC_TYP
				LEFT OUTER JOIN CMP.COMPANY SHP ON HED.SHP_CO_NBR=SHP.CO_NBR
				LEFT OUTER JOIN CMP.COMPANY RCV ON HED.RCV_CO_NBR=RCV.CO_NBR
				LEFT OUTER JOIN CMP.PEOPLE CRT ON HED.CRT_NBR=CRT.PRSN_NBR
			$where AND DEL_F=0 $ShpNbr AND REF_NBR<>'100' 
			ORDER BY DL_TS ASC";
			//echo $query;
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."parent.document.getElementById('content').src=
	'retail-stock-edit.php?IVC_TYP=RC&ORD_NBR=".$_GET['ORD']."&PO_NBR=".$row['ORD_NBR']."';".chr(34).">";
				echo "<td style='text-align:right'>".$row['ORD_NBR']."</td>";
				if($IvcTyp=="RT"){
					echo "<td style='text-align:right'>- ".$row['ORD_Q_TOT']."</td>";
				}
				else{
					echo "<td style='text-align:right'> ".$row['ORD_Q_TOT']."</td>";
				}	
				echo "<td>".$row['SHP_NAME']."</td>";
				//echo "<td>".$row['REF_NBR']." ".$row['NAME_CO']."</td>";
				echo "<td style='text-align:center'>".parseDate($row['DL_TS'])."</td>";
				if($IvcTyp=="RT"){
					echo "<td style='text-align:right;'> - ".number_format($row['TOT_AMT'],0,',','.')."</td>";
				}
				else{
					echo "<td style='text-align:right;'>".number_format($row['TOT_AMT'],0,',','.')."</td>";
				}
				if($_GET['SEL']=="DEB"){
					echo "<td style='text-align:right'>".parseDate($row['PAST_DUE'])."</td>";
				}
				echo "<td>".$row['CRT_NAME']."</td>";
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}
				//$sub+=$row['TOT_AMT'];
				//$tot+=$row['ORD_Q_TOT'];
			}

		?>
		</tbody>
	</table>
<?php
}else{
?>
<div class="toolbar">
	<p class="toolbar-left"><a href="retail-order-edit.php?IVC_TYP=<?php echo $IvcTyp; ?>&TYP=<?php echo $type; ?>&ORD_NBR=0"><img class="toolbar-left" src="img/add.png" onclick="location.href="></a></p>
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class="sortable" style="text-align:right;">No.</th>
				<th class="sortable" style="text-align:right;">Item</th>
				<th class="sortable">Pengirim</th>
				<th class="sortable">Penerima</th>
				<th class="sortable">Tgl Order</th>
				<th class="sortable">Nota</th>
				<th class="sortable" style="text-align:right;">Jumlah</th>
				<th class="sortable">Pembuat</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT 
				HED.ORD_NBR,
				ORD_DTE,
				IVC_DESC,
				ORD_Q_TOT,
				REF_NBR,
				SHP_CO_NBR,
				RCV_CO_NBR,
				SHP.NAME AS SHP_NAME,
				COALESCE(RCV.NAME,'Tunai') AS RCV_NAME,
				HED.FEE_MISC,
				TOT_AMT,
				PYMT_DOWN,
				PYMT_REM,
				TOT_REM,
				DL_TS,
				SPC_NTE,
				HED.CRT_TS,
				HED.CRT_NBR,
				CRT.NAME AS CRT_NAME,
				HED.UPD_TS,
				HED.UPD_NBR,
				DATEDIFF(DATE_ADD(ORD_DTE,INTERVAL COALESCE(SHP.PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS SHP_PAST_DUE
			FROM ". $headtable ." HED 
				LEFT OUTER JOIN (
					SELECT 
						HED.ORD_NBR, 
						SUM(ORD_Q) AS ORD_Q_TOT
					FROM ". $headtable ." HED 
						INNER JOIN RTL.RTL_STK_DET DET ON HED.ORD_NBR=DET.ORD_NBR
					GROUP BY DET.ORD_NBR ASC
				) AS DET ON HED.ORD_NBR=DET.ORD_NBR
				LEFT OUTER JOIN RTL.IVC_TYP IVC ON HED.IVC_TYP=IVC.IVC_TYP
				LEFT OUTER JOIN CMP.COMPANY SHP ON HED.SHP_CO_NBR=SHP.CO_NBR
				LEFT OUTER JOIN CMP.COMPANY RCV ON HED.RCV_CO_NBR=RCV.CO_NBR
				LEFT OUTER JOIN CMP.PEOPLE CRT ON HED.CRT_NBR=CRT.PRSN_NBR
			$where AND DEL_F=0 $ShpNbr
			ORDER BY HED.UPD_TS DESC";
			//echo $query;
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='retail-order-edit.php?IVC_TYP=".$IvcTyp."&TYP=".$type."&ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
				echo "<td style='text-align:right'>".$row['ORD_NBR']."</td>";
				if($IvcTyp=="RT"){
					echo "<td style='text-align:right'>- ".$row['ORD_Q_TOT']."</td>";
				}
				else{
					echo "<td style='text-align:right'> ".$row['ORD_Q_TOT']."</td>";
				}	
				echo "<td>".$row['SHP_NAME']."</td>";
				echo "<td>".$row['RCV_NAME']."</td>";
				//echo "<td>".$row['REF_NBR']." ".$row['NAME_CO']."</td>";
				echo "<td style='text-align:center'>".parseDate($row['DL_TS'])."</td>";
				echo "<td style='text-align:center'>".parseDate($row['ORD_DTE'])."</td>";
				if($IvcTyp=="RT"){
					echo "<td style='text-align:right;'> - ".number_format($row['TOT_AMT'],0,',','.')."</td>";
				}
				else{
					echo "<td style='text-align:right;'>".number_format($row['TOT_AMT'],0,',','.')."</td>";
				}
				if($_GET['SEL']=="DEB"){
					echo "<td style='text-align:right'>".parseDate($row['PAST_DUE'])."</td>";
				}
				echo "<td>".shortName($row['CRT_NAME'])."</td>";
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}
				//$sub+=$row['TOT_AMT'];
				//$tot+=$row['ORD_Q_TOT'];
			}
		//echo "<tr><td style='text-align:right;font-weight:bold' colspan=1>Total Item</td><td style='text-align:right'>".number_format($tot,0,'.',',')."</td><td colspan=4>";			
		//echo "<td style='text-align:right;font-weight:bold' colspan=7>Total</td><td style='text-align:right'>".number_format($sub,0,',','.')."</td></tr>";			
		
		?>
		</tbody>
	</table>
	
</div>

<script>liveReqInit('livesearch','liveRequestResults','retail-order-ls.php?IVC_TYP=<?php echo $IvcTyp; ?>&TYP=<?php echo $type; ?>','','mainResult');</script>

<script>fdTableSort.init();</script>
<?php
}
?>
</body>
</html>


