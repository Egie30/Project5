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
					HED.ORD_NBR,
					ORD_DTE AS SORT_DTE,
					DATE_FORMAT(ORD_DTE,'%d-%m-%Y') AS ORD_DTE,
					ORD_TTL,
					ORD_DESC,
					TYP.ORD_TYP,
					SUM(ORD_Q) AS ORD_Q,
					REF_NBR,
					CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END AS SEL_NAME,
					CASE WHEN CMB.NAME IS NULL THEN 'Tunai' ELSE CMB.NAME END AS BUY_NAME,
					SUM(CASE WHEN CAL_TYP='ST' THEN ORD_Q ELSE 0 END) AS ST,
					SUM(CASE WHEN CAL_TYP='TR' THEN ORD_Q ELSE 0 END) AS TR,
					SUM(CASE WHEN CAL_TYP='CW' THEN ORD_Q ELSE 0 END) AS CW,
					SUM(CASE WHEN CAL_TYP='TH' THEN ORD_Q ELSE 0 END) AS TH,
					SUM(CASE WHEN CAL_TYP='KK' THEN ORD_Q ELSE 0 END) AS KK,
					SUM(CASE WHEN CAL_TYP NOT IN ('ST','TR','CQ','TH','KK') THEN ORD_Q ELSE 0 END) AS LL,
					TOT_AMT
				FROM CMP.CAL_ORD_HEAD HED 
					INNER JOIN CMP.ORD_TYP TYP ON HED.ORD_TYP=TYP.ORD_TYP 
					INNER JOIN CMP.CAL_ORD_DET AS DET ON HED.ORD_NBR=DET.ORD_NBR 
					LEFT OUTER JOIN CMP.CAL_LST LST ON DET.CAL_NBR=LST.CAL_NBR 
					LEFT OUTER JOIN CMP.COMPANY CMS ON HED.SEL_CO_NBR=CMS.CO_NBR 
					LEFT OUTER JOIN CMP.COMPANY CMB ON HED.BUY_CO_NBR=CMB.CO_NBR
				WHERE HED.ORD_TYP='INV' AND HED.CMP_DTE IS NOT NULL AND HED.PU_DTE IS NULL AND HED.ORD_DTE BETWEEN ".getFiscalYear()."
				GROUP BY HED.ORD_NBR,ORD_DTE,ORD_TTL,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END,TOT_AMT
				ORDER BY 2";
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result)){
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='calendar-edit.php?ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
				echo "<td class='listable-first'>".$row['ORD_NBR']."</td>";
				echo "<td class='listable' style='text-align:center'>".$row['ORD_DTE']."</a></td>";
				echo "<td class='listable'>".$row['REF_NBR']."</a></td>";
				echo "<td class='listable'>".$row['ORD_TTL']."</a></td>";
				echo "<td class='listable'>".$row['BUY_NAME']."</a></td>";
				echo "<td class='listable' align='right'>".number_format($row['TOT_AMT'],0,",",".")."</td>";
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}
				if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
			}
		?>
		</tbody>
	</table>
</div>
<script>fdTableSort.init();</script>
</body>
</html>	
</body>
</html>
