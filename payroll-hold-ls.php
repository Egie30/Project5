<?php
	include "framework/database/connect-cloud.php";
	//Process delete entry
	include "framework/security/default.php";
	$Security=getSecurity($_SESSION['userID'],"Payroll");
	
	$_GET['CO_NBR'] == "ALL";
	$CoNbr = "271, 889, 997, 1002, 1099";
	$key = $_GET['s'];
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
<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:center;">Id Karyawan</th>
				<th>Nama</th>
				<th>Jabatan</th>
				<th>Lokasi</th>
				<th>Gaji Ditahan</th>
				<th>Gaji Diberikan</th>
				<th>Gaji Dihanguskan</th>
				<th>Sisa</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$i = 1;
			$query="SELECT LST.PRSN_NBR
						,SUM(LST.PAY_HLD_AMT) AS PAY_HLD_AMT
						,SUM(CASE 
							WHEN LST.PAY_HLD_TYP = '2'
								THEN LST.PAY_HLD_AMT
							ELSE 0
							END) AS PAY_HLD_PD
						,SUM(CASE 
							WHEN LST.PAY_HLD_TYP = '3'
								THEN LST.PAY_HLD_AMT
							ELSE 0
							END) AS PAY_HLD_COM
						,SUM(LST.PAY_HLD_AMT) - SUM(CASE 
							WHEN LST.PAY_HLD_TYP = '2'
								THEN LST.PAY_HLD_AMT
							ELSE 0
							END) - SUM(CASE 
							WHEN LST.PAY_HLD_TYP = '3'
								THEN LST.PAY_HLD_AMT
							ELSE 0
							END) AS LEAD
						,LST.PYMT_DTE
						,PPL.NAME
						,COM.NAME AS CO_NAME
						,POS.POS_DESC
					FROM PAY.PAY_HLD_LST LST
					INNER JOIN CMP.PEOPLE PPL ON LST.PRSN_NBR = PPL.PRSN_NBR
					INNER JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR 
					INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP = POS.POS_TYP
					INNER JOIN CMP.COMPANY COM ON PPL.CO_NBR = COM.CO_NBR
					WHERE PPL.TERM_DTE IS NULL
						AND PPAY.PAY_TYP = 'MON'
						AND PPL.CO_NBR IN (".$CoNbr.")
						AND (LST.PRSN_NBR LIKE '%".$key."%' OR PPL.NAME LIKE '%".$key."%' OR COM.NAME LIKE '%".$key."%')
					GROUP BY LST.PRSN_NBR";
			//echo "<pre>".$query."</pre>";
			$result=mysql_query($query,$local);
			
			$alt="";
			$Tot=0;
			while($row=mysql_fetch_array($result))
			{
				$Tot=$Tot+$row['LEAD'];
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='payroll-hold-list.php?PRSN_NBR=".$row['PRSN_NBR']."&CO_NBR=".$CoNbr."';".chr(34).">";
				echo "<td style='text-align:center'>".$row['PRSN_NBR']."</td>";
				echo "<td>".$row['NAME']."</td>";
				echo "<td>".$row['POS_DESC']."</td>";
				echo "<td>".$row['CO_NAME']."</td>";
				echo "<td style='text-align:right'>".number_format($row['PAY_HLD_AMT'],0,",",".")."</td>";
				echo "<td style='text-align:right'>".number_format($row['PAY_HLD_PD'],0,",",".")."</td>";
				echo "<td style='text-align:right'>".number_format($row['PAY_HLD_COM'],0,",",".")."</td>";
				echo "<td style='text-align:right'>".number_format($row['LEAD'],0,",",".")."</td>";
				echo "</tr>";
				
				$i++;
			}
			echo "<tr><td colspan='7' style='text-align:right;font-weight:bold;'>Total</td>
						<td style='text-align:right;font-weight:bold'>".number_format($Tot,0,",",".")."</td>
				</tr>";
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
</body>
</html>


