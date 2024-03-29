<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";



	//security
	$Security 	= getSecurity($_SESSION['userID'],"Finance");
	$Securitys  = getSecurity($_SESSION['userID'],"AddressBook");

	$rcvCoNbr 	= $_GET['RCV_CO_NBR'];

	if($rcvCoNbr!="")
	{
		$whereString = "RCV_CO_NBR=".$rcvCoNbr;
		$queryString = "RCV_CO_NBR=".$rcvCoNbr;
	}

	if($rcvCoNbr=="0")
	{
		$whereString="(RCV_CO_NBR IS NULL)";
	}
	//echo $whereString;

	$filter_date = str_replace("+"," ",$_GET['FLTR_DATE']);

	if ($filter_date!="") 
	{
		$data		= explode(" ",$filter_date);
		$data_month	= $data[0];
		$data_year	= $data[1];
		$whereDte	= " AND MONTH(ORD_DTE)='".$data_month."' AND YEAR(ORD_DTE)='".$data_year."' ";
	}

	$actual_link	= "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$data_link		= explode("?",$actual_link);
	$link 			= "retail-order-receivables-ls.php?".$data_link[1];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	
</head>

<body>

<div class="toolbar">
	<div class="combobox"></div>
	<div class="toolbar-text">
		<p class="toolbar-left" style="float:none;margin-top: 6px;">
		<select id="RCV_DATE" name="RCV_DATE" style="width:150px" class="chosen-select">
		<?php				
				$query_dte	= " SELECT ORD_DTE,
									   CONCAT(MONTH(ORD_DTE),' ',YEAR(ORD_DTE)) AS DTE,
									   CONCAT(CASE WHEN MONTH(ORD_DTE)='1' THEN 'Januari'
												   WHEN MONTH(ORD_DTE)='2' THEN 'Februari'
												   WHEN MONTH(ORD_DTE)='3' THEN 'Maret'
												   WHEN MONTH(ORD_DTE)='4' THEN 'April'
												   WHEN MONTH(ORD_DTE)='5' THEN 'Mei'
												   WHEN MONTH(ORD_DTE)='6' THEN 'Juni'
												   WHEN MONTH(ORD_DTE)='7' THEN 'Juli'
												   WHEN MONTH(ORD_DTE)='8' THEN 'Agustus'
												   WHEN MONTH(ORD_DTE)='9' THEN 'September'
												   WHEN MONTH(ORD_DTE)='10' THEN 'Oktober'
												   WHEN MONTH(ORD_DTE)='11' THEN 'November'
												   WHEN MONTH(ORD_DTE)='12' THEN 'Desember'
												   END,' ',YEAR(ORD_DTE)) AS DTE_DESC,
												   SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0) AS TOT_REM
								FROM RTL.RTL_ORD_HEAD HED 
								INNER JOIN RTL.ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
								LEFT JOIN (SELECT PYMT.ORD_NBR,
												  COALESCE(SUM(PYMT.TND_AMT),0) AS TND_AMT
										   FROM RTL.RTL_ORD_PYMT PYMT
										   WHERE PYMT.DEL_NBR = 0
										   GROUP BY PYMT.ORD_NBR
										   ) PAY ON PAY.ORD_NBR = HED.ORD_NBR
								WHERE HED.DEL_F=0
								GROUP BY YEAR(ORD_DTE),MONTH(ORD_DTE) HAVING (SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0))>0";
				// echo $query_dte;
				genCombo($query_dte, "DTE", "DTE_DESC", $filter_date, "Semua Data");
		?>
		</select>
		</p>
		<p class="toolbar-left" style="float:none;margin-left: 160px;">
			<span class="fa fa-calendar toolbar fa-lg" id="filter-by-date" 
				style="padding-left:0px;margin-bottom:12px;cursor:pointer"
				onclick="location.href='retail-order-receivables.php?FLTR_DATE='+document.getElementById('RCV_DATE').value">	
			</span>
		</p>
		
		<p class="toolbar-right">
			<?php if($Security<=1 || $Securitys == 1){?>
				<a title="Export to Excel" href="report-excel.php?RPT_TYP=retail-order-receivables-excel&STT=ACT&YEAR=<?php echo $data_year; ?>&MONTH=<?php echo $data_month; ?>&RCV_CO_NBR=<?php echo $_GET['RCV_CO_NBR']; ?>" target="_blank"><span class='fa fa-file-excel-o toolbar' style="cursor:pointer" onclick="location.href="></span></a>
			<?php }?>
			<span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" />
		</p>

	</div>
</div>
<br />
<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;">Nota</th>
				<?php
					if($rcvCoNbr!="")
					{
						echo '<th>Periode</th>';
					}
					/*else{
						echo '<th>Awal</th>';
						echo '<th>Akhir</th>';
					}*/
				?>
				<th>Nama</th>
				<th>Total</th>
				<th>Pembayaran</th>
				<th style="text-align:right;">Sisa</th>
			</tr>
		</thead>
		<tbody>
		<?php
			if($rcvCoNbr!="")
			{
				$query = "SELECT COUNT(HED.ORD_NBR) AS NBR_ORD, 
							     YEAR(ORD_DTE) AS ORD_YEAR,
							     MONTH(ORD_DTE) AS ORD_MONTH,
							     COM.NAME AS NAME_CO,
							     COM.CO_NBR AS RCV_CO_NBR,
							     SUM(TOT_AMT) AS TOT_AMT,
							     COALESCE(SUM(PAY.TND_AMT),0) AS PYMT_DOWN,
							     SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0) AS TOT_REM 
						  FROM RTL.RTL_ORD_HEAD HED 
						  INNER JOIN RTL.ORD_STT STT ON HED.ORD_STT_ID = STT.ORD_STT_ID 
						  LEFT OUTER JOIN CMP.COMPANY COM ON HED.RCV_CO_NBR = COM.CO_NBR
						  LEFT JOIN (
							  SELECT PYMT.ORD_NBR,
							  		 COALESCE(SUM(PYMT.TND_AMT),0) AS TND_AMT
							  FROM RTL.RTL_ORD_PYMT PYMT
							  WHERE PYMT.DEL_NBR = 0
							  GROUP BY PYMT.ORD_NBR
						  ) PAY ON PAY.ORD_NBR = HED.ORD_NBR
						  WHERE TOT_REM>0 
						  AND $whereString 
						  AND HED.DEL_F=0
						  $whereDte
					      GROUP BY YEAR(ORD_DTE),MONTH(ORD_DTE),COM.NAME,COM.CO_NBR HAVING (SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0))>0 ORDER BY 2";
			}
			else
			{
				$query="SELECT COUNT(HED.ORD_NBR) AS NBR_ORD, 
							   MIN(DATE(ORD_DTE)) AS ORD_TS_MIN,
							   MAX(DATE(ORD_DTE)) AS ORD_TS_MAX,
							   COM.NAME AS NAME_CO,
							   COM.CO_NBR AS RCV_CO_NBR,
							   SUM(TOT_AMT) AS TOT_AMT,
							   COALESCE(SUM(PAY.TND_AMT),0) AS PYMT_DOWN,
							   SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0) AS TOT_REM 
						FROM RTL.RTL_ORD_HEAD HED 
						INNER JOIN RTL.ORD_STT STT ON HED.ORD_STT_ID = STT.ORD_STT_ID 
						LEFT OUTER JOIN CMP.COMPANY COM ON HED.RCV_CO_NBR = COM.CO_NBR 
						LEFT JOIN (
						SELECT PYMT.ORD_NBR,
							   COALESCE(SUM(PYMT.TND_AMT),0) AS TND_AMT
						FROM RTL.RTL_ORD_PYMT PYMT
						WHERE PYMT.DEL_NBR = 0
						GROUP BY PYMT.ORD_NBR
						) PAY ON PAY.ORD_NBR = HED.ORD_NBR
						WHERE TOT_REM>0 
						AND HED.DEL_F=0 
						$whereDte
						GROUP BY COM.NAME,COM.CO_NBR HAVING (SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0))>0 ORDER BY 8 DESC";
			}
			// echo $query;

			$result = mysql_query($query);

			$alt="";
			$SumTotAmt=0; $SumPymtDown=0; $SumTotRem=0; $col=2;

			while($row = mysql_fetch_array($result))
			{
				$queryString = "RCV_CO_NBR=0";
			
				if($row['RCV_CO_NBR']!="")
				{
					$queryString = "RCV_CO_NBR=".$row['RCV_CO_NBR'];
				}
				
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='";

				if($rcvCoNbr!="")
				{
					echo "retail-order-receivables-list.php?STT=COL&YEAR=".$row['ORD_YEAR']."&MONTH=".$row['ORD_MONTH']."&FLTR_DATE=".$_GET['FLTR_DATE']."&".$queryString;
				}
				else
				{
					echo "retail-order-receivables.php?".$queryString."&FLTR_DATE=".$_GET['FLTR_DATE'];
				}

				echo "';".chr(34).">";
				echo "<td style='text-align:right'>".$row['NBR_ORD']."</td>";

				if($rcvCoNbr!="")
				{
					echo "<td style='text-align:center'>".$row['ORD_YEAR']."-".sprintf('%02d',$row['ORD_MONTH'])."</td>";
					$col=3;
				}
				/*else{
					echo "<td style='text-align:center'>".$row['ORD_TS_MIN']."</td>";
					echo "<td style='text-align:center'>".$row['ORD_TS_MAX']."</td>";
				}*/
				echo "<td>";
				
				if($row['RCV_CO_NBR']=="")
				{
					echo "Tunai";
				}
				else
				{
					echo $row['NAME_CO'];
				} 
				
				echo "</td>";
				echo "<td style='text-align:right'>".number_format($row['TOT_AMT'],0,'.','.')."</td>";
				$SumTotAmt   = $SumTotAmt + $row['TOT_AMT'];
				echo "<td style='text-align:right'>".number_format($row['PYMT_DOWN'],0,'.','.')."</td>";
				$SumPymtDown = $SumPymtDown + $row['PYMT_DOWN'];
				echo "<td style='text-align:right'>".number_format($row['TOT_REM'],0,'.','.')."</td>";
				$SumTotRem 	 = $SumTotRem + $row['TOT_REM'];
				echo "</tr>";
			}

			echo "<tr>
				<td colspan='".$col."' style='text-align:right;font-weight:bold;'> Total</td>";
			echo "<td style='text-align:right;font-weight:bold;'>".number_format($SumTotAmt,0,'.','.')."</td>";
			echo "<td style='text-align:right;font-weight:bold;'>".number_format($SumPymtDown,0,'.','.')."</td>";
			echo "<td style='text-align:right;font-weight:bold;'>".number_format($SumTotRem,0,'.','.')."</td>";
			echo "</tr>";
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

<script>liveReqInit('livesearch','liveRequestResults','<?php echo $link; ?>','','mainResult');</script>
</body>
</html>


