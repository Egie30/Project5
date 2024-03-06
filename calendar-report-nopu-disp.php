<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$CoNbr=$_GET['CO_NBR'];
	$Typ=$_GET['TYP'];
	$query="SELECT HED.ORD_NBR,ORD_DTE AS SORT_DTE,DATE_FORMAT(ORD_DTE,'%d-%m-%Y') AS ORD_DTE,ORD_TTL,ORD_DESC,TYP.ORD_TYP,SUM(ORD_Q) AS ORD_Q,REF_NBR,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END AS SEL_NAME,CASE WHEN CMB.NAME IS NULL THEN 'Tunai' ELSE CMB.NAME END AS BUY_NAME,SUM(CASE WHEN CAL_TYP='ST' THEN ORD_Q ELSE 0 END) AS ST,SUM(CASE WHEN CAL_TYP='TR' THEN ORD_Q ELSE 0 END) AS TR,SUM(CASE WHEN CAL_TYP='CW' THEN ORD_Q ELSE 0 END) AS CW,SUM(CASE WHEN CAL_TYP='TH' THEN ORD_Q ELSE 0 END) AS TH,SUM(CASE WHEN CAL_TYP='KK' THEN ORD_Q ELSE 0 END) AS KK,SUM(CASE WHEN CAL_TYP NOT IN ('ST','TR','CQ','TH','KK') THEN ORD_Q ELSE 0 END) AS LL,TOT_AMT
			FROM CMP.CAL_ORD_HEAD HED INNER JOIN CMP.ORD_TYP TYP ON HED.ORD_TYP=TYP.ORD_TYP INNER JOIN CMP.CAL_ORD_DET AS DET ON HED.ORD_NBR=DET.ORD_NBR LEFT OUTER JOIN CMP.CAL_LST LST ON DET.CAL_NBR=LST.CAL_NBR LEFT OUTER JOIN CMP.COMPANY CMS ON HED.SEL_CO_NBR=CMS.CO_NBR LEFT OUTER JOIN CMP.COMPANY CMB ON HED.BUY_CO_NBR=CMB.CO_NBR
			WHERE HED.ORD_TYP='INV' AND HED.CMP_DTE IS NOT NULL AND HED.PU_DTE IS NULL AND HED.ORD_DTE BETWEEN ".getFiscalYear()."
			GROUP BY HED.ORD_NBR,ORD_DTE,ORD_TTL,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END,TOT_AMT
			ORDER BY 2";
	//echo $query;
	$result=mysql_query($query);
	
	echo "<br/><table style='width:100%'>";
			$rowcol="a";
		
			echo "<tr class='listable'>";
			echo "<th class='listable'>No.</th>";
			echo "<th class='listable'>Tgl.</th>";
			echo "<th class='listable'>No. Ref.</th>";
			echo "<th class='listable'>Judul</th>";
			echo "<th class='listable'>Pembeli</th>";
			echo "<th class='listable'>Total</th>";
			echo "</tr>";
		$order=0;
		$inventory=0;
		while($row=mysql_fetch_array($result))
		
		{
		echo "<tr ".$alt." >";
		echo "<td class='listable-first' align='center'><a href='calendar-edit.php?ORD_NBR=".$row['ORD_NBR']."'>".$row['ORD_NBR']."</td>";
		echo "<td class='listable' nowrap>".$row['ORD_DTE']."</a></td>";
		echo "<td class='listable'>".$row['REF_NBR']."</a></td>";
		echo "<td class='listable'>".$row['ORD_TTL']."</a></td>";
		echo "<td class='listable'>".$row['BUY_NAME']."</a></td>";
		echo "<td class='listable	' align='right'>".number_format($row['TOT_AMT'],0,",",".")."</td>";
		echo "</tr>";
		if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
		if($alt==""){$alt="class='alt'";}else{$alt="";}
	
}
	

	echo "</table>";
/*
	function dispInv($ref,$val)
	{
		if($val==0){
			echo "<td class='sortable'></td>";
		}else{
			echo "<td class='sortable' align='right'><a href='calendar-ls-edit.php?LOG_NBR=".$ref."'>".number_format($val,0,",",".")."</a></td>";
		}
	}*/
?>
