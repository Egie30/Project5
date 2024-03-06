<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";

	$Security=getSecurity($_SESSION['userID'],"Finance");

	
	//Process delete entry
	if($_GET['DEL_A']!="")
	{
		$query="DELETE FROM CMP.EXPENSE WHERE EXP_NBR=".$_GET['DEL_A'];
		$result=mysql_query($query);
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
	<?php
		if($Security<=1){
	?>
	<p class="toolbar-left"><a href="cap-voucher-edit.php?VCHR_NBR=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a></p>
	<?php
		}
	?>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th class="sortable">No. Seri</th>
				<th class="sortable">Nominal</th>
				<th class="sortable-date-dmy">Buat</th>
				<th class="sortable-date-dmy">Print</th>
				<th class="sortable-date-dmy">Validasi</th>
				<th class="sortable">Customer</th>
				<th class="sortable-date-dmy">Issue</th>
				<th class="sortable-date-dmy">Exp</th>
				<th class="sortable-currency" style="border-right:0px;">Guna</th>
			</tr>
		</thead>
		<tbody>
		<?php
			//Issuing company will need to be adjustable in the future
			$query="SELECT VCHR_NBR,VCHR_SER_NBR,AMT,PRN_TS,VLD_TS,RCV_CO_NBR,COM.NAME AS CO_NAME,PPL.NAME AS PPL_NAME,RCV_PRSN_NBR,ISU_TS,EXP_DT,USE_TS,CRT_CO_NBR,CRT_NBR,CRT_TS
					  FROM CMP.CAP_VCHR CAP LEFT OUTER JOIN
					       CMP.PEOPLE PPL ON CAP.RCV_PRSN_NBR=PPL.PRSN_NBR LEFT OUTER JOIN
					       CMP.COMPANY COM ON CAP.RCV_CO_NBR=COM.CO_NBR
					 WHERE CAP.CRT_CO_NBR=".$CoNbrDef."
					ORDER BY 1 ASC";
			//echo $query;
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='cap-voucher-edit.php?VCHR_NBR=".$row['VCHR_NBR']."';".chr(34).">";
				echo "<td style='text-align:center'>".$row['VCHR_SER_NBR']."</td>";
				echo "<td style='text-align:right'>".number_format($row['AMT'],0,",",".")."</td>";
				echo "<td style='text-align:center'>".parseDateShort($row['CRT_TS'])."</td>";
				echo "<td style='text-align:center'>".parseDateShort($row['PRN_TS'])."</td>";
				echo "<td style='text-align:center'>".parseDateShort($row['VLD_TS'])."</td>";
				echo "<td>".trim($row['CO_NAME']." ".$row['PPL_NAME'])."</td>";
				echo "<td style='text-align:center'>".parseDateShort($row['ISU_TS'])."</td>";
				echo "<td style='text-align:center'>".parseDateShort($row['EXP_DT'])."</td>";
				echo "<td style='text-align:center'>".parseDateShort($row['USE_TS'])."</td>";
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
<script>liveReqInit('livesearch','liveRequestResults','cap-voucher-ls.php','','mainResult');</script>
</body>
</html>


