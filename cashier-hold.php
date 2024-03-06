<?php
require_once "framework/database/connect-cashier.php";
require_once "framework/functions/default.php";
require_once "framework/functions/dotmatrix.php";



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>
	<script type="text/javascript" src='framework/tablesort/customsort.js'></script>
	<script type="text/javascript">parent.Pace.restart();</script>
</head>
<body>
<!--
<div class="toolbar">
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>
-->

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">

		<?php
			$activeType = 2;
			
			$query  = "SELECT TRSC_NBR, Q_NBR, DATE(CRT_TS) AS CRT_TS, TIME(CRT_TS) AS CRT_TIME, ACT_F FROM RTL.CSH_REG WHERE (ACT_F=1 OR ACT_F = 2) AND POS_ID = " . $POSID . " AND DATE(CRT_TS)=CURRENT_DATE GROUP BY TRSC_NBR ORDER BY CRT_TS DESC";
			$result = mysql_query($query, $rtl);
    
			if (mysql_num_rows($result) > 0) {
				echo "<b>Pilih dari daftar transaksi dibawah ini:</b>";
				echo '<table id="mainTable" class="std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable">
				<thead>
					<tr>
						<th class="sortable" style="text-align:right;">No.</th>
						<th class="sortable sortable-date-dmy">Tanggal</th>
						<th class="sortable">Waktu</th>
						<th class="sortable" style="align:right">Kasir</th>
						<th class="sortable sortable-currency">Jumlah</th>
					</tr>
				</thead>
				<tbody>';
				echo "<table class='std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable' style='width:520px;padding:0px;margin:0px;margin-top:10px'>";
				
				while ($row = mysql_fetch_array($result)) {
					$activeType = $row['ACT_F'];
					
					if ($activeType == 2) {
						$onclick = "doListingRequest('" . $row['Q_NBR'] . "', 'OHL');";
					} else {
						$onclick .= "parent.document.getElementById('listing').src='cashier-listing.php?POS_ID=" . $POSID . "';showListingView();";
					}

					echo "<tr style='cursor:pointer' onclick=\"" . $onclick . "\">";

					echo "<td style='font-size:10pt;vertical-align:top'>";
					echo "<b>" . $row['Q_NBR'] . "</b>";
					echo "</td>";
					echo "<td style='color:#999999;vertical-align:top'>";
					echo $row['CRT_TS'];
					echo "<hr/>";
					echo $row['CRT_TIME'];
					echo "<hr/>";
					if($row['ACT_F'] == 1) { echo "Active"; } 
						else if ($row['ACT_F'] == 2) { echo "Hold"; }
					echo "</td>";
					echo "<td style='vertical-align:top;text-align:right;padding-right:0px;'>";

					//Retail entries
					$queryRetail  = "SELECT REG_NBR,TRSC_NBR,REG.CO_NBR,REG.RTL_BRC,RTL_Q,REG.RTL_PRC,INV.NAME AS NAME_DESC,COALESCE(DISC_AMT,0) AS DISC_AMT,COALESCE(DISC_PCT,0) AS DISC_PCT,TND_AMT,ORD_NBR,CSH_FLO_DESC,REG.CSH_FLO_TYP,CSH_FLO_MULT,PYMT_DESC,REG.PYMT_TYP,ACT_F
										FROM RTL.CSH_REG REG LEFT OUTER JOIN
											 RTL.COMPANY COM ON REG.CO_NBR=COM.CO_NBR LEFT OUTER JOIN
											 RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP LEFT OUTER JOIN
											 RTL.PYMT_TYP PAY ON REG.PYMT_TYP=PAY.PYMT_TYP LEFT OUTER JOIN RTL.INVENTORY INV ON REG.RTL_BRC=INV.INV_BCD
										WHERE (INV.DEL_NBR=0 OR REG.CSH_FLO_TYP='FL' OR REG.CSH_FLO_TYP='DP' OR REG.CSH_FLO_TYP='GP' ) AND TRSC_NBR=".$row['TRSC_NBR']."
										AND REG.RTL_BRC <> ''
										ORDER BY CRT_TS DESC";
					$resultRetail = mysql_query($queryRetail, $rtl);
					$altRetail    = "";

					echo "<table class='std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable' style='border:1px solid #eeeeee;overflow:scroll;width:100%'>";

					while ($rowRetail = mysql_fetch_array($resultRetail)) {
						if ($rowRetail['DISC_AMT'] != 0) {
							$disc = " <span style='color:#999999'>(Disc Rp. " . number_format($rowRetail['DISC_AMT'], 0, ",", ".") . ")</span>";
						} else if ($rowRetail['DISC_PCT'] != 0) {
							$disc = " <span style='color:#999999'>(Disc " . number_format($rowRetail['DISC_PCT'], 0, ",", ".") . "%)</span>";
						} else {
							$disc = "";
						}
						
						echo "<tr>";
						
						if ($rowRetail['CSH_FLO_TYP'] == 'PN') {
							echo "<td style='text-align:left'>" . $rowRetail['NAME_DESC'] . " (PPN) <br/><span style='color:#999999'>" . $rowRetail['RTL_BRC'] . "</span> " . $rowRetail['RTL_Q'] . " x @ Rp. " . number_format($rowRetail['RTL_PRC'], 0, ",", ".") . $disc . "</td>";
						} else {
							echo "<td style='text-align:left'>" . $rowRetail['NAME_DESC'] . "<br/><span style='color:#999999'>" . $rowRetail['RTL_BRC'] . "</span> " . $rowRetail['RTL_Q'] . " x @ Rp. " . number_format($rowRetail['RTL_PRC'], 0, ",", ".") . $disc . "</td>";
						}
						
						if ($rowRetail['DISC_PCT'] != 0) {
							$DiscVal = ($rowRetail['CSH_FLO_MULT'] * $rowRetail['TND_AMT']) * ($rowRetail['DISC_PCT'] / 100);
						} else {
							$DiscVal = $rowRetail['DISC_AMT'];
						}
						
						echo "<td style='text-align:right;vertical-align:top'><b>Rp. " . number_format(($rowRetail['CSH_FLO_MULT'] * $rowRetail['TND_AMT']) - $DiscVal, 0, ",", ".") . "</b></td>";
						echo "</tr>";
					}

					echo "</table>";
					echo "</td>";
					echo "</tr>";
				}
				echo "</table>";
				echo "</tbody></table>";
    } else {
        ?>
		<script type="text/javascript">
			parent.document.getElementById('search').src = 'cashier-search.php?POS_ID=<?php echo $POSID; ?>';
			parent.document.getElementById('bottom').src = 'http://<?php echo $POSIP; ?>/cashier-bottom-reset.php?POS_ID=<?php echo $POSID;?>&DEFCO=<?php echo $CoNbrDef;?>"&CSH=<?php echo $_SESSION['userID'];?>';
		</script>
		<?php
    }
		
	?>
		
</div>
<script type="text/javascript">
		function doListingRequest(value, action) {
			parent.document.getElementById('listing').src = 'cashier-listing.php?POS_ID=<?php echo $POSID; ?>&VALUE=' + value + '&ACTION=' + action;
			showListingView();
		}

		function showListingView() {
			parent.document.getElementById('search').src = 'cashier-search.php?POS_ID=<?php echo $POSID; ?>';
			parent.document.getElementById('data').style.display = '';
			parent.document.getElementById('slides').style.display = 'none';
			document.getElementById('livesearch').value = '';
			document.getElementById('liveRequestResults').style.display = 'none';
			document.getElementById('livesearch').focus();
		}
</script>
<script type="text/javascript">liveReqInit('livesearch','liveRequestResults','address-member-ls.php','','mainResult');</script>
<script type="text/javascript">fdTableSort.init();</script>
</body>
</html>
<?php
/*
}
else {
} ?>
<script type='text/javascript'>
changeUrl('bottom','http://<?php echo $POSIP; ?>/cashier-bottom-reset.php?POS_ID=<?php echo $POSID;?>&DEFCO=<?php echo $CoNbrDef;?>"&CSH=<?php echo $_SESSION['userID'];?>')
</script>

<?php
}
*/
?>