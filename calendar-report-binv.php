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
				<th class='sortable'>Tipe</th>
				<th class='sortable'>No. Ref.</th>
				<th class='sortable'>Judul</th>
				<th class='sortable'>Penjual</th>
				<th class='sortable'>Pembeli</th>
				<th class='sortable'>Total</th>
				<th class='sortable'>Alasan</th>
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
						CASE WHEN CMB.NAME IS NULL THEN 'Tunai' ELSE CMB.NAME END AS BUY_NAME,SUM(TOT_SUB) AS TOT_SUB,
						TOT_AMT,
						FEE_FLM,
						HED.FEE_MISC
					FROM CMP.CAL_ORD_HEAD HED 
						INNER JOIN CMP.ORD_TYP TYP ON HED.ORD_TYP=TYP.ORD_TYP 
						INNER JOIN CMP.CAL_ORD_DET AS DET ON HED.ORD_NBR=DET.ORD_NBR 
						LEFT OUTER JOIN CMP.CAL_LST LST ON DET.CAL_NBR=LST.CAL_NBR 
						LEFT OUTER JOIN CMP.COMPANY CMS ON HED.SEL_CO_NBR=CMS.CO_NBR 
						LEFT OUTER JOIN CMP.COMPANY CMB ON HED.BUY_CO_NBR=CMB.CO_NBR
					WHERE HED.ORD_DTE BETWEEN ".getFiscalYear()."
					GROUP BY HED.ORD_NBR,ORD_DTE,ORD_TTL,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END,TOT_AMT
					HAVING TOT_AMT=0 OR TOT_SUB+FEE_FLM+HED.FEE_MISC<>TOT_AMT
					ORDER BY 2";
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result)){
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='calendar-edit.php?ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
				echo "<td class='listable-first'>".$row['ORD_NBR']."</td>";
				echo "<td class='listable' style='text-align:center'>".$row['ORD_DTE']."</a></td>";
				echo "<td class='listable'>".$row['ORD_DESC']."</a></td>";
				echo "<td class='listable'>".$row['REF_NBR']."</a></td>";
				echo "<td class='listable'>".$row['ORD_TTL']."</a></td>";
				echo "<td class='listable'>".$row['SEL_NAME']."</a></td>";
				echo "<td class='listable'>".$row['BUY_NAME']."</a></td>";
				echo "<td class='listable' align='right'>".number_format($row['TOT_AMT'],0,",",".")."</td>";
				echo "<td class='listable' align='left'>Salah Angka Total</td>";
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}
				if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
			}
			
		$query="SELECT 
					HED.ORD_NBR,
					ORD_DTE AS SORT_DTE,
					DATE_FORMAT(ORD_DTE,'%d-%m-%Y') AS ORD_DTE,
					ORD_TTL,ORD_DESC,
					TYP.ORD_TYP,
					REF_NBR,
					CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END AS SEL_NAME,
					CASE WHEN CMB.NAME IS NULL THEN 'Tunai' ELSE CMB.NAME END AS BUY_NAME,
					TOT_AMT,
					FEE_FLM,
					HED.FEE_MISC
				FROM CMP.CAL_ORD_HEAD HED 
					INNER JOIN CMP.ORD_TYP TYP ON HED.ORD_TYP=TYP.ORD_TYP 
					LEFT OUTER JOIN CMP.COMPANY CMS ON HED.SEL_CO_NBR=CMS.CO_NBR 
					LEFT OUTER JOIN CMP.COMPANY CMB ON HED.BUY_CO_NBR=CMB.CO_NBR
				WHERE TOT_AMT=0 AND (PYMT_REM=0 AND PYMT_DOWN=0) AND HED.ORD_DTE BETWEEN ".getFiscalYear()."
				GROUP BY HED.ORD_NBR,ORD_DTE,ORD_TTL,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END,TOT_AMT
				ORDER BY 2";

		$result=mysql_query($query);
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='calendar-edit.php?ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
			echo "<td class='listable-first'>".$row['ORD_NBR']."</td>";
			echo "<td class='listable' style='text-align:center'>".$row['ORD_DTE']."</a></td>";
			echo "<td class='listable'>".$row['ORD_DESC']."</a></td>";
			echo "<td class='listable'>".$row['REF_NBR']."</a></td>";
			echo "<td class='listable'>".$row['ORD_TTL']."</a></td>";
			echo "<td class='listable'>".$row['SEL_NAME']."</a></td>";
			echo "<td class='listable'>".$row['BUY_NAME']."</a></td>";
			echo "<td class='listable' align='right'>".number_format($row['TOT_AMT'],0,",",".")."</td>";
			echo "<td class='listable' align='left'>Nota Kosong</td>";
			echo "</tr>";
			if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
		
		$query="SELECT 
					HED.ORD_NBR,
					ORD_DTE AS SORT_DTE,
					DATE_FORMAT(ORD_DTE,'%d-%m-%Y') AS ORD_DTE,
					ORD_TTL,
					ORD_DESC,
					TYP.ORD_TYP,
					REF_NBR,
					CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END AS SEL_NAME,
					CASE WHEN CMB.NAME IS NULL THEN 'Tunai' ELSE CMB.NAME END AS BUY_NAME,
					TOT_AMT,
					FEE_FLM,
					HED.FEE_MISC
				FROM CMP.CAL_ORD_HEAD HED 
					INNER JOIN CMP.ORD_TYP TYP ON HED.ORD_TYP=TYP.ORD_TYP 
					LEFT OUTER JOIN CMP.COMPANY CMS ON HED.SEL_CO_NBR=CMS.CO_NBR 
					LEFT OUTER JOIN CMP.COMPANY CMB ON HED.BUY_CO_NBR=CMB.CO_NBR
				WHERE ((SEL_CO_NBR<>1 AND HED.ORD_TYP IN ('REQ','INV')) OR (SEL_CO_NBR=1 AND BUY_CO_NBR<>1 AND HED.ORD_TYP IN ('ORD','RCV'))) AND HED.ORD_DTE BETWEEN ".getFiscalYear()."
				GROUP BY HED.ORD_NBR,ORD_DTE,ORD_TTL,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END,TOT_AMT
				ORDER BY 2";
		$result=mysql_query($query);
		while($row=mysql_fetch_array($result))	while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='calendar-edit.php?ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
			echo "<td class='listable-first'>".$row['ORD_NBR']."</td>";
			echo "<td class='listable' style='text-align:center'>".$row['ORD_DTE']."</a></td>";
			echo "<td class='listable'>".$row['ORD_DESC']."</a></td>";
			echo "<td class='listable'>".$row['REF_NBR']."</a></td>";
			echo "<td class='listable'>".$row['ORD_TTL']."</a></td>";
			echo "<td class='listable'>".$row['SEL_NAME']."</a></td>";
			echo "<td class='listable'>".$row['BUY_NAME']."</a></td>";
			echo "<td class='listable' align='right'>".number_format($row['TOT_AMT'],0,",",".")."</td>";
			echo "<td class='listable' align='left'>Penjual Salah</td>";
			echo "</tr>";
			if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
		?>
		</tbody>
	</table>
</div>
<script>fdTableSort.init();</script>
</body>
</html>	
