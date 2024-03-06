<?php
	include "framework/database/connect.php";
	//Process delete entry
	if(($_GET['DEL']!="")&&($_GET['DATE']!=""))
	{
		$query="DELETE FROM CMP.PAYROLL WHERE PRSN_NBR=".$_GET['DEL']." AND PYMT_DTE='".$_GET['DATE']."'";
		$result=mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>
	
</head>

<body>

<div class="toolbar">
	<p class="toolbar-left"></p>
	<p class="toolbar-right"><a href="payroll-prn-dig-bank.php"><img class="toolbar-left" src="img/bank.png" onclick="location.href="></a><a href="payroll-prn-dig-edit-print.php?AUTO=1"><img class="toolbar-left" src="img/print.png" onclick="location.href="></a><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class="sortable" style="text-align:right;">No.</th>
				<th class="sortable">Nama</th>
				<th class="sortable">Perusahaan</th>
				<th class="sortable">Jabatan</th>
				<th class="sortable" style="border-right:0px;">Gajian Terakhir</th>
				<th class="sortable">Hari</th>
				<th class="sortable">Lembur</th>
				<th class="sortable">Bank</th>
				<th class="sortable">Gaji</th>
				<th class="sortable">Bonus</th>
				<th class="sortable">Cicilan</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT PAY.PRSN_NBR,PPL.NAME,COM.NAME,POS_DESC,DTE.PYMT_DTE,BASE_CNT,OT_CNT,BON_PCT,PPL.BNK_ACCT_NBR,PAY_AMT,BON_MO_AMT,DEBT_MO FROM CMP.PAYROLL PAY INNER JOIN (SELECT MAX(PYMT_DTE) AS PYMT_DTE,PAY.PRSN_NBR FROM CMP.PAYROLL PAY INNER JOIN CMP.PEOPLE PPL ON PAY.PRSN_NBR=PPL.PRSN_NBR WHERE PPL.CO_NBR IN (271,889,997,1002) AND TERM_DTE IS NULL AND PAY.DEL_NBR=0 AND PPL.PAY_TYP='MON' GROUP BY PAY.PRSN_NBR) DTE ON PAY.PYMT_DTE=DTE.PYMT_DTE AND PAY.PRSN_NBR=DTE.PRSN_NBR INNER JOIN CMP.PEOPLE PPL ON PAY.PRSN_NBR=PPL.PRSN_NBR INNER JOIN CMP.COMPANY COM ON PPL.CO_NBR=COM.CO_NBR INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE TERM_DTE IS NULL AND PAY.DEL_NBR=0 AND PPL.CO_NBR IN (271,889,997,1002) AND PPL.PAY_TYP='MON'";
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='payroll-champion-campus-edit.php?PRSN_NBR=".$row['PRSN_NBR']."';".chr(34).">";
				echo "<td style='text-align:right'>".$row['PRSN_NBR']."</td>";
				echo "<td>".$row['NAME']."</td>";
				echo "<td>".$row['POS_DESC']."</td>";
				echo "<td>".$row['PYMT_DTE']."</td>";
				echo "<td>".$row['BASE_CNT']."</td>";
				echo "<td>".$row['OT_CNT']."</td>";
				echo "<td>".$row['BNK_ACCT_NBR']."</td>";
				echo "<td>".$row['PAY_AMT']."</td>";
				echo "<td>".$row['BON_MO_AMT']."</td>";
				echo "<td>".$row['DEBT_MO']."</td>";
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}
			}
		?>
		</tbody>
	</table>
</div>

<script>liveReqInit('livesearch','liveRequestResults','payroll-champion-campus-ls.php','','mainResult');</script>

<script>fdTableSort.init();</script>

</body>
</html>


