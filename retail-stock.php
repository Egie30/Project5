<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	$CoNbr=$_GET['CO_NBR'];
	$IvcTyp=$_GET['IVC_TYP'];
	$ShpNbr=$_GET['SHP_NBR'];
	
	if ($ShpNbr!='') {$ShpNbr='AND SHP_CO_NBR='.$ShpNbr;}
	
	//if($IvcTyp!=''){$where="WHERE HED.IVC_TYP='".$IvcTyp."'";}	
	
	if($CoNbr=='1' || $CoNbr=='4'){$where="WHERE SHP_CO_NBR='".$CoNbr."' AND HED.IVC_TYP='".$IvcTyp."'";}
	//elseif($CoNbr=='4'){$where="WHERE SHP_CO_NBR='".$CoNbr."' AND HED.IVC_TYP='".$IvcTyp."'";}
	else{$where="WHERE HED.IVC_TYP='".$IvcTyp."'";}
	
	//Process delete entry
	if($_GET['DEL']!=""){
		$query="UPDATE RTL.RTL_STK_HEAD SET DEL_F=1 WHERE ORD_NBR=".$_GET['DEL'];
		$result=mysql_query($query);
		
		if ($IvcTyp=="RC"){
		$query="SELECT 
			GROUP_CONCAT(INV_NBR) AS INV_NBR,
			GROUP_CONCAT(ORD_DET_NBR) AS ORD_DET_NBR 
		FROM RTL.RTL_STK_DET
		WHERE ORD_NBR = ".$_GET['DEL'];
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		
		$query="UPDATE RTL.INV_MOV SET DEL_NBR = ".$_SESSION['personNBR']." WHERE INV_NBR IN (".$row['INV_NBR'].") AND ORD_DET_NBR IN (".$row['ORD_DET_NBR'].")";
		$result=mysql_query($query);
		}
	}
	
	//Process checkout entry
	/*
	if($_GET['CHECKOUT']!=""){
		$query="SELECT 
			ORD_Q,ORD_DET_NBR,DET.INV_PRC,DET.INV_NBR 
		FROM RTL.RTL_STK_DET DET
		WHERE ORD_NBR=".$_GET['CHECKOUT']." AND ORD_NBR != 0
		ORDER BY DET.ORD_DET_NBR ASC";
		$result=mysql_query($query);
		$i = 0;
		while($row=mysql_fetch_array($result)){
			$dateTime = date("Y-m-d H:i:s", strtotime("+$i sec"));
			$sql="INSERT INTO RTL.INV_MOV (MOV_Q,ORD_DET_NBR,DET_INV_PRC,CRT_NBR,CRT_TS,INV_NBR) VALUES (".$row['ORD_Q'].",".$row['ORD_DET_NBR'].",".$row['INV_PRC'].",".$_SESSION['personNBR'].",'".$dateTime."',".$row['INV_NBR'].")";
			$results=mysql_query($sql);
			$i++;
		}
	}
	*/
	if($_GET['SEL']=="DEB"){
		$where="WHERE TOT_REM>0";
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<script>parent.Pace.restart();</script>
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
	<p class="toolbar-left">
		<?php if (in_array($_GET['IVC_TYP'], array("AS", "DS"))) { ?>
		<a href="retail-stock-edit-asm.php?IVC_TYP=<?php echo $IvcTyp; ?>&ORD_NBR=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a>
		<?php } else { ?>
		<a href="retail-stock-edit.php?IVC_TYP=<?php echo $IvcTyp; ?>&ORD_NBR=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a>
		<?php } ?>
	</p>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;">No.</th>
				<th style="text-align:right;">Kategori</th>
				<th style="text-align:right;">Referensi</th>
				<th style="text-align:right;">Item</th>
				<th>Pengirim</th>
				<th>Penerima</th>
				<th>Terima</th>
				<th>Nota</th>
				<?php if($IvcTyp=="RC"){ 
					echo "<th>Tempo Pembayaran</th>";
					echo "<th>Lunas</th>";
				} ?>
				
				<th style="text-align:right;">Jumlah</th>
				<?php if($IvcTyp=="RC"){ ?>
					<th style="text-align:right;">Sisa</th>
					<th style="text-align:center;">Rekening</th>
				<?php
				}
					if($_GET['SEL']=="DEB"){
						echo "<th>Jatuh Tempo</th>";
					}
					
				?>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT 
				HED.ORD_NBR,
				ORD_DTE,
				DATE_ADD(ORD_DTE,INTERVAL COALESCE(SHP.BUY_TERM,14) DAY) AS BUY_TERM_DTE,
				IVC_DESC,
				ORD_Q_TOT,
				REF_NBR,
				SHP_CO_NBR,
				RCV_CO_NBR,
				SHP.NAME AS SHP_NAME,
				RCV.NAME AS RCV_NAME,
				HED.FEE_MISC,
				TOT_AMT,
				PYMT_DOWN,
				PYMT_REM,
				TOT_REM,
				DL_TS,
				SPC_NTE,
				HED.CRT_TS,
				HED.CRT_NBR,
				HED.UPD_TS,
				HED.UPD_NBR,
				DATEDIFF(DATE_ADD(ORD_DTE,INTERVAL COALESCE(SHP.PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS SHP_PAST_DUE,
				HED.CAT_SUB_NBR,
				SUB.CAT_SUB_DESC,
				(CASE WHEN HED.ACTG_TYP = 0 THEN '' ELSE HED.ACTG_TYP END) AS ACTG_TYP,
				DATE(HED.PYMT_REM_TS) AS PYMT_REM_DTE
			FROM RTL.RTL_STK_HEAD HED 
				LEFT OUTER JOIN(
					SELECT 
						HED.ORD_NBR, 
						SUM(ORD_Q) AS ORD_Q_TOT
					FROM RTL.RTL_STK_HEAD HED 
						INNER JOIN RTL.RTL_STK_DET DET ON HED.ORD_NBR=DET.ORD_NBR
					GROUP BY DET.ORD_NBR ASC
				) AS DET
					ON HED.ORD_NBR=DET.ORD_NBR
				LEFT OUTER JOIN RTL.IVC_TYP IVC 
					ON HED.IVC_TYP=IVC.IVC_TYP
				LEFT OUTER JOIN CMP.COMPANY SHP 
					ON HED.SHP_CO_NBR=SHP.CO_NBR
				LEFT OUTER JOIN CMP.COMPANY RCV 
					ON HED.RCV_CO_NBR=RCV.CO_NBR 
				LEFT JOIN RTL.CAT_SUB SUB
					ON HED.CAT_SUB_NBR = SUB.CAT_SUB_NBR
			".$where." AND DEL_F=0 ".$ShpNbr."
			ORDER BY HED.ORD_NBR DESC";
			//ECHO "<pre>".$query;
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result)){
				$remain		= $row['TOT_AMT']-$row['PYMT_DOWN']-$row['PYMT_REM'];
				$due		= strtotime($row['BUY_TERM_DTE']);
				$due2 		= $row['BUY_TERM_DTE'];
				$dateFirst 	= date("Y-m-d", strtotime("-14 days", $due2));
				$dateFirst2 = date("Y-m-d", strtotime("-3 days", $due2));
				$stopFirst 	= strtotime($due2 . ' - 14 day');
				$stopSecond = strtotime($due2 . ' - 3 day');
				$timeNow 	= strtotime("now");
				$back		= "";
				
				if($remain > 0){
					if ($timeNow < $due) {
						if ($timeNow > $stopFirst && strtotime("now") < $stopSecond) {
							$back="print-digital-green";
						} elseif (strtotime("now") > $stopSecond && strtotime("now") < $due) {
							$back="print-digital-yellow";
						}
					} elseif ($timeNow > $due) {
						$back="print-digital-red";
					}
				}
				
				if (in_array($IvcTyp, array("AS", "DS"))) {
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='retail-stock-edit-asm.php?IVC_TYP=".$IvcTyp."&ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
				}else{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='retail-stock-edit.php?IVC_TYP=".$IvcTyp."&ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
				}
				echo "<td style='text-align:right'>".$row['ORD_NBR']."</td>";
				echo "<td style='text-align:left'>".$row['CAT_SUB_DESC']."</td>";
				echo "<td style='text-align:right'>".$row['REF_NBR']."</td>";
				if($IvcTyp=="RT"){
					echo "<td style='text-align:right'>- ".$row['ORD_Q_TOT']."</td>";
				}
				else{
					echo "<td style='text-align:right'> ".$row['ORD_Q_TOT']."</td>";
				}	
				echo "<td>".$row['SHP_NAME']."</td>";
				echo "<td>".$row['RCV_NAME']."</td>";
				echo "<td style='text-align:center'>".parseDate($row['DL_TS'])."</td>";
				echo "<td style='text-align:center'>".parseDate($row['ORD_DTE'])."</td>";
				echo "<td style='text-align:center'><div class='$back'>".parseDate($row['BUY_TERM_DTE'])."</div></td>";
				
				if($IvcTyp=="RC"){ 
					echo "<td>".parseDate($row['PYMT_REM_DTE'])."</td>";
				}
				
				if($IvcTyp=="RT"){
					echo "<td style='text-align:right;'> - ".number_format($row['TOT_AMT'],0,',','.')."</td>";
				}
				else{
					echo "<td style='text-align:right;'>".number_format($row['TOT_AMT'],0,',','.')."</td>";
				}
				if($_GET['SEL']=="DEB"){
					echo "<td style='text-align:right'>".parseDate($row['PAST_DUE'])."</td>";
				}
				if($IvcTyp=="RC"){
					echo "<td style='text-align:right;'> ".number_format($row['TOT_AMT']-$row['PYMT_DOWN']-$row['PYMT_REM'],0,',','.')."</td>";
					echo "<td style='text-align:center;'> ".$row['ACTG_TYP']."</td>";
				}
				echo "</tr>";
			}			
		
		?>
		</tbody>
	</table>
	
</div>

<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>

<script>liveReqInit('livesearch','liveRequestResults','retail-stock-ls.php?IVC_TYP=<?php echo $IvcTyp; ?>','','mainResult');</script>

</body>
</html>
