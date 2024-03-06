<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	$Security=getSecurity($_SESSION['userID'],"Inventory");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script type="text/javascript" src="framework/functions/default.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

<link rel="stylesheet" href="framework/combobox/chosen.css">
</head>

<body>
<div class="toolbar">
	<p class="toolbar-left"></p>
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult">
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class='sortable'>No.</th>
				<th class='sortable'>Tgl.</th>
				<th class='sortable'>No. Ref.</th>
				<th class='sortable'>Judul</th>
				<th class='sortable'>Pembeli</th>
				<th class='sortable'>Total</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$query="SELECT 
					REQ.ORD_NBR,
					REQ.ORD_DTE AS SORT_DTE,
					DATE_FORMAT(REQ.ORD_DTE,'%d-%m-%Y') AS ORD_DTE,
					REQ.REF_NBR,REQ.ORD_TTL,
					CASE WHEN NAME IS NULL THEN 'Tunai' ELSE NAME END AS NAME,
					REQ.TOT_AMT
				FROM CMP.CAL_ORD_HEAD REQ 
					LEFT OUTER JOIN CMP.COMPANY COM ON REQ.BUY_CO_NBR=COM.CO_NBR 
					LEFT OUTER JOIN CMP.CAL_ORD_HEAD INV ON INV.REQ_NBR LIKE CONCAT('%',REQ.REF_NBR,'%') AND REQ.BUY_CO_NBR=INV.BUY_CO_NBR
				WHERE REQ.ORD_TYP='REQ' AND INV.ORD_NBR IS NULL AND REQ.ORD_DTE BETWEEN ".getFiscalYear()." 
				GROUP BY REQ.ORD_NBR,REQ.ORD_DTE,REQ.REF_NBR,DATE_FORMAT(REQ.ORD_DTE,'%d-%m-%Y'),REQ.ORD_TTL,CASE WHEN NAME IS NULL THEN 'Tunai' ELSE NAME END,REQ.TOT_AMT
				ORDER BY 2";
			//echo $query;
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result)){
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='calendar-edit.php?ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
				echo "<td class='listable-first'>".$row['ORD_NBR']."</td>";
				echo "<td class='listable' style='text-align:center'>".$row['ORD_DTE']."</a></td>";
				echo "<td class='listable'>".$row['REF_NBR']."</a></td>";
				echo "<td class='listable'>".$row['ORD_TTL']."</a></td>";
				echo "<td class='listable'>".$row['NAME']."</a></td>";
				echo "<td class='listable' align='right'>".number_format($row['TOT_AMT'],0,",",".")."</td>";
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}
				if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
			}
		?>
		</tbody>
	</table>
</div>
<script>liveReqInit('livesearch','liveRequestResults','calendar-report-ordr-ls.php','','mainResult');</script>
<script>fdTableSort.init();</script>
</body>
</html>	
