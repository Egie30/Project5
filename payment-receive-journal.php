<?php
include "framework/database/connect.php";
include "framework/security/default.php";

$UpperSec		= getSecurity($_SESSION['userID'],"Stationery");
$shippingNbr	= $_GET['SHP_CO_NBR'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>
</head>
<body>

<div class="toolbar">
	<p class="toolbar-left">&nbsp;</p>
	<p class="toolbar-right">&nbsp;</p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class="sortable">No.</th>
				<th class="sortable">Tanggal</th>
				<th class="sortable">Deskripsi</th>				
				<th class="sortable">Jumlah</th>
				<th class="sortable">Balance</th>
			</tr>
		</thead>
		<tbody>
	<?php 
	$alt;
	$query="SELECT 
		'PYMT' AS ORIGIN,
		PYMT_RCV_NBR AS IVC_NBR,
		PYMT_RCV_DTE AS DTE,
		CONCAT('Pembayaran',' - ',TYP.PYMT_DESC,' (',REF_NBR,') dari ',SHP.NAME) AS IVC_DESC,
		TND_AMT AS AMT
	FROM RTL.PYMT_RCV RCV
		INNER JOIN CMP.COMPANY SHP ON RCV.SHP_CO_NBR = SHP.CO_NBR
		LEFT JOIN RTL.PYMT_TYP TYP ON RCV.PYMT_TYP = TYP.PYMT_TYP
	WHERE RCV.DEL_NBR=0 AND RCV.SHP_CO_NBR = ". $shippingNbr ."

	UNION ALL

	SELECT 
		'ORD' AS ORIGIN,
		HED.ORD_NBR AS IVC_NBR ,
		DATE(ORD_TS) AS DTE,
		CONCAT(COALESCE(HED.ORD_TTL,''),' ',COALESCE(COM.NAME,''),' ',COALESCE(PPL.NAME,'')) AS IVC_DESC,
		SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0) AS AMT
	FROM CMP.PRN_DIG_ORD_HEAD HED 
		INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
		LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
		LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
		LEFT JOIN (
			SELECT 
				PYMT.ORD_NBR,
				COALESCE(SUM(PYMT.TND_AMT),0) AS TND_AMT
			FROM CMP.PRN_DIG_ORD_PYMT PYMT
			WHERE PYMT.DEL_NBR = 0
			GROUP BY PYMT.ORD_NBR
		) PAY ON PAY.ORD_NBR = HED.ORD_NBR
		WHERE TOT_REM > 0 AND HED.DEL_NBR=0 AND HED.BUY_CO_NBR = ". $shippingNbr ."
	GROUP BY HED.ORD_NBR";
	//echo "<pre>".$query;
	$result = mysql_query($query);
	while ($row = mysql_fetch_array($result)) {
		if($row['ORIGIN'] == 'PYMT'){
			$bold = "style='font-weight:bold;'";
			$bolds = "font-weight:bold;";
			$balance 	+= $row['AMT'];
		}else{
			$bold = "";
			$bolds = "";
			$balance 	-= $row['AMT'];
		}
	?>
	<tr <?php echo $alt; ?> style ="cursor: pointer;" onclick="location.href='payment-receive-edit.php?PYMT_RCV_NBR=<?php echo $row['PYMT_RCV_NBR'];?>';">
		<td  style="text-align:center;<?php echo $bolds; ?>"><?php echo $row['IVC_NBR'];?></td>
		<td <?php echo $bold; ?>><?php echo $row['DTE'];?></td>
		<td <?php echo $bold; ?>><?php echo $row['IVC_DESC'];?></td>
		<td  <?php echo $bold; ?> align="right"><?php echo number_format($row['AMT'],0,'.',',');?></td>
		<td  <?php echo $bold; ?> align="right"><?php echo number_format($balance,0,'.',',');?></td>
	</tr>
	<?php 
	}
	?>
	</tbody>
	</table>
</div>

<script>liveReqInit('livesearch','liveRequestResults','retail-type-ls.php','','mainResult');</script>
<script>fdTableSort.init();</script>
</body>
</html>