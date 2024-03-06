<?php

	include "framework/database/connect-cloud.php";
	//Process delete entry
	include "framework/security/default.php";
	$Security=getSecurity($_SESSION['userID'],"Payroll");
	
	$query	= "SELECT CO_NBR_CMPST FROM NST.PARAM_PAYROLL WHERE CO_NBR = ".$_GET['CO_NBR']." ";
	$result	= mysql_query($query);
	$row 	= mysql_fetch_array($result);
		
	$CoNbr	= $row['CO_NBR_CMPST'];
	
	
	if($cloud!=false){
	
		if(($_GET['DEL']!="")&&($_GET['DATE']!=""))
		{
			$query="UPDATE $PAY.PAYROLL SET DEL_NBR=".$_SESSION['personNBR'].", UPD_TS=CURRENT_TIMESTAMP WHERE PRSN_NBR=".$_GET['DEL']." AND PYMT_DTE='".$_GET['DATE']."'";
			$result=mysql_query($query,$cloud);
			$query=str_replace($PAY,"PAY",$query);
			//echo $query;
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
	<p class="toolbar-left"></p>
	<p class="toolbar-right">
		<?php if($Security<2){ ?> 
		<!---<a href="payroll-prn-dig-bank.php?CO_NBR=<?php echo $_GET['CO_NBR']; ?>"><span class='fa fa-bank toolbar' style="cursor:pointer" onclick="location.href="></span></a> -->
		<a onclick="parent.parent.document.getElementById('datePayrollPopupEditContent').src='payroll-prn-dig-bank-date.php?CO_NBR=<?php echo $_GET['CO_NBR']; ?>';parent.parent.document.getElementById('datePayrollPopupEdit').style.display='block';parent.parent.document.getElementById('fade').style.display='block';">
				<span class='fa fa-bank toolbar' style="cursor:pointer"></span>
			</a>
		<?php } ?>
		<a href="payroll-prn-dig-edit-print.php?CONBR=<?php echo $CoNbr; ?>&EMAIL=1&AUTO=1"><span class='fa fa-paper-plane-o toolbar' style="cursor:pointer" onclick="location.href="></span></a>
		<?php if($Security<2){ ?>
		<a href="payroll-prn-dig-edit-print.php?CONBR=<?php echo $CoNbr; ?>&AUTO=1"><span class='fa fa-print toolbar' style="cursor:pointer" onclick="location.href="></span></a>
		<?php } ?>
		<span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" /></p>
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
			$query="SELECT PPL.PRSN_NBR,NAME,POS_DESC,MAX(PYMT_DTE) AS PYMT_DTE, PPL.CO_NBR
					FROM CMP.PEOPLE PPL
					LEFT JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
					INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP
					LEFT OUTER JOIN PAY.PAYROLL PAY ON PPL.PRSN_NBR=PAY.PRSN_NBR
					WHERE TERM_DTE IS NULL 
					AND PPAY.PAY_TYP='MON'
					AND CO_NBR IN (".$CoNbr.") 
					AND PPL.DEL_NBR=0
					AND (PAY.DEL_NBR=0 OR PAY.DEL_NBR IS NULL) 
					GROUP BY PPL.PRSN_NBR,NAME,POS_DESC ORDER BY 2";
			
			$result=mysql_query($query,$local);
			
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='payroll-edit.php?PRSN_NBR=".$row['PRSN_NBR']."&CO_NBR=".$CoNbr."';".chr(34).">";
				echo "<td style='text-align:right'>".$row['PRSN_NBR']."</td>";
				echo "<td>".$row['NAME']."</td>";
				echo "<td>".$row['POS_DESC']."</td>";
				if ($row['PYMT_DTE'] != "") { 			
				echo "<td>".date('d-m-Y', strtotime($row['PYMT_DTE']))."</td>";
				}
				else { echo "<td></td>";	}
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

<script>liveReqInit('livesearch','liveRequestResults','payroll-ls.php?CO_NBR=<?php echo $_GET['CO_NBR'];?>','','mainResult');</script>

</body>
</html>

