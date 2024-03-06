<?php
	if($_GET['DEL_L']!=""){
		include "framework/database/connect-cloud.php";
	}else{
		include "framework/database/connect.php";
	}
	include "framework/functions/default.php";
	include "framework/security/default.php";
	$Security=getSecurity($_SESSION['userID'],"Payroll");
	$PrsnNbr=$_GET['PRSN_NBR'];
	
	if($cloud!=false){
		if($_GET['DEL_L']!=""){
			$query="UPDATE $PAY.EMPL_CRDT SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP WHERE PRSN_NBR=".$_GET['DEL_L']." AND PYMT_DTE='".$_GET['PYMT_DTE']."'";
	   		$result=mysql_query($query,$cloud);
			$query=str_replace($PAY,"PAY",$query);
			$result=mysql_query($query,$local);
		}
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
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th>No.</th>
				<th>Nama</th>
				<th>Jabatan</th>
				<th>Tanggal</th>
				<th>Bon</th>
				<th>Cicilan</th>
				<th>Sisa</th>

			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT PPL.PRSN_NBR
						,NAME
						,POS_DESC
						,MAX(PYMT_DTE) AS PYMT_DTE
						,DEBT_MO_TOT
						,SUM(CRDT_AMT) AS CRDT_AMT_TOT
					FROM CMP.PEOPLE PPL
					LEFT OUTER JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
					INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP = POS.POS_TYP
					LEFT OUTER JOIN PAY.EMPL_CRDT BON ON PPL.PRSN_NBR = BON.PRSN_NBR
					LEFT OUTER JOIN (
						SELECT PPL.PRSN_NBR
							,SUM(DEBT_MO) AS DEBT_MO_TOT
						FROM CMP.PEOPLE PPL
						LEFT OUTER JOIN PAY.PAYROLL PAY ON PPL.PRSN_NBR = PAY.PRSN_NBR
						WHERE PPL.DEL_NBR = 0
							AND (
								PAY.DEL_NBR = 0
								OR PAY.DEL_NBR IS NULL
								)
						GROUP BY PAY.PRSN_NBR ASC
						) AS PAY ON PPL.PRSN_NBR = PAY.PRSN_NBR
					WHERE TERM_DTE IS NULL
						AND PPAY.PAY_TYP = 'MON'
						AND CO_NBR IN (
							SELECT CO_NBR
							FROM NST.PARAM_PAYROLL
							)
						AND (
							BON.DEL_NBR = 0
							OR BON.DEL_NBR IS NULL
							)
						-- AND BON.CRDT_APV=1
					GROUP BY PPL.PRSN_NBR
					ORDER BY 2";	
			//echo $query;
			$result=mysql_query($query);		
			$alt="";
			while($row=mysql_fetch_array($result))
				{
					echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='employee-credit-edit.php?PRSN_NBR=".$row['PRSN_NBR']."';".chr(34).">";
					echo "<td class='listable' align=center>".$row['PRSN_NBR']."</a></td>";
					echo "<td class='listable' align='left'>".$row['NAME']."</td>";
					echo "<td class='listable' align='left'>".$row['POS_DESC']."</td>";
					echo "<td class='listable' style='text-align:center'>".$row['PYMT_DTE']."</td>";
					echo"<td class='listable' align='right'>".number_format($row['CRDT_AMT_TOT'],0,",",".")."</td>";
					echo"<td class='listable' align='right'>".number_format($row['DEBT_MO_TOT'],0,",",".")."</td>";
					$TotBon=$row['CRDT_AMT_TOT']-$row['DEBT_MO_TOT'];
					echo"<td class='listable' align='right'>".number_format($TotBon,0,",",".")."</td>";
					
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

<script>liveReqInit('livesearch','liveRequestResults','employee-credit-ls.php','','mainResult');</script>

</body>
</html>


