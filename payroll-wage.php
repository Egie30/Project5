<?php
	include "framework/database/connect.php";
	//Process delete entry
	if(($_GET['DEL']!="")&&($_GET['DATE']!=""))
	{
		$query="UPDATE PAY.PAYROLL_LOC SET DEL_NBR=".$_SESSION['personNBR']." WHERE PRSN_NBR=".$_GET['DEL']." AND PYMT_DTE='".$_GET['DATE']."'";
		$result=mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	
</head>

<body>

<div class="toolbar">
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;">No.</th>
				<th>Nama</th>
				<th>Jabatan</th>
				<th style="border-right:0px;">Gajian Terakhir</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT PPL.PRSN_NBR
						,NAME
						,POS_DESC
						,MAX(PYMT_DTE) AS PYMT_DTE
					FROM CMP.PEOPLE PPL
					LEFT JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
					INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP = POS.POS_TYP
					LEFT OUTER JOIN PAY.PAYROLL_LOC PAY ON PPL.PRSN_NBR = PAY.PRSN_NBR
					WHERE TERM_DTE IS NULL
						AND (
							PAY.DEL_NBR = 0
							OR PAY.DEL_NBR IS NULL
							)
						AND PPAY.PAY_TYP = 'DAY'
					GROUP BY PPL.PRSN_NBR
						,NAME
						,POS_DESC
					ORDER BY 2";
			//echo $query;
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='payroll-wage-edit.php?PRSN_NBR=".$row['PRSN_NBR']."';".chr(34).">";
				echo "<td style='text-align:right'>".$row['PRSN_NBR']."</td>";
				echo "<td>".$row['NAME']."</td>";
				echo "<td>".$row['POS_DESC']."</td>";
				echo "<td>".$row['PYMT_DTE']."</td>";
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

<script>liveReqInit('livesearch','liveRequestResults','payroll-wage-ls.php','','mainResult');</script>

</body>
</html>


