<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	//security
	$Security = getSecurity($_SESSION['userID'],"Finance");
	
	$rcvCoNbr = $_GET['RCV_CO_NBR'];

	if($rcvCoNbr!="")
	{
		$whereString = "RCV_CO_NBR=".$rcvCoNbr;
		$queryString = "RCV_CO_NBR=".$rcvCoNbr;
	}

	if($rcvCoNbr == "0")
	{
		$whereString = "(RCV_CO_NBR IS NULL)";
	}
	
	$filter_date = str_replace("+"," ",$_GET['FLTR_DATE']);

	if ($filter_date!="") 
	{
		$data		= explode(" ",$filter_date);
		$data_month	= $data[0];
		$data_year	= $data[1];
		$whereDte	= " AND MONTH(ORD_DTE)='".$data_month."' AND YEAR(ORD_DTE)='".$data_year."' ";
	}
	
	//------------------Search
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ 	 = explode(" ",$searchQuery);
	$whereClause = "";

	foreach($searchQ as $searchQuery)
	{
		$whereClause.="(COM.NAME LIKE '%".$searchQuery."%') AND ";
	}

	$whereClause = substr($whereClause,0,strlen($whereClause)-4);
	
	if($rcvCoNbr!="")
	{
		$query = "	SELECT COUNT(HED.ORD_NBR) AS NBR_ORD, 
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
						AND ".$whereClause."
						AND HED.DEL_F=0
						".$whereDte."
					GROUP BY YEAR(ORD_DTE),MONTH(ORD_DTE),COM.NAME,COM.CO_NBR HAVING (SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0))>0 ORDER BY 2";
	}
	else
	{
		$query="SELECT  COUNT(HED.ORD_NBR) AS NBR_ORD, 
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
						AND ".$whereClause."
						AND HED.DEL_F=0 
						".$whereDte."
					GROUP BY COM.NAME,COM.CO_NBR HAVING (SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0))>0 ORDER BY 8 DESC";
	}

	// echo $query;
			
	$result=mysql_query($query);

	if(mysql_num_rows($result)==0)
	{
		echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
?>
<table id="searchTable" class="tablesorter searchTable">
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
			$SumTotAmt=0; $SumPymtDown=0; $SumTotRem=0; $col=2;
			while($row=mysql_fetch_array($result))
			{
				$queryString = "RCV_CO_NBR = 0";
				if($row['RCV_CO_NBR']!="")
				{
					$queryString = "RCV_CO_NBR=".$row['RCV_CO_NBR'];
				}
				
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='";
				if($rcvCoNbr!="")
				{
					echo "retail-order-list.php?STT=COL&YEAR=".$row['ORD_YEAR']."&MONTH=".$row['ORD_MONTH']."&FLTR_DATE=".$_GET['FLTR_DATE']."&".$queryString;
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
				$SumTotAmt 		= $SumTotAmt 	+ $row['TOT_AMT'];
				echo "<td style='text-align:right'>".number_format($row['PYMT_DOWN'],0,'.','.')."</td>";
				$SumPymtDown 	= $SumPymtDown  + $row['PYMT_DOWN'];
				echo "<td style='text-align:right'>".number_format($row['TOT_REM'],0,'.','.')."</td>";
				$SumTotRem 		= $SumTotRem 	+ $row['TOT_REM'];
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