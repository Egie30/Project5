<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	$CoNbr=$_GET['CO_NBR'];
	$Typ=$_GET['TYP'];
	$CalNbr=$_GET['CAL_NBR'];

	$query="SELECT HED.ORD_NBR,ORD_DTE AS SORT_DTE,DATE_FORMAT(ORD_DTE,'%d-%m-%Y') AS ORD_DTE,ORD_TTL,ORD_DESC,TYP.ORD_TYP,ORD_Q,REF_NBR,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END AS SEL_NAME,CASE WHEN CMB.NAME IS NULL THEN 'Tunai' ELSE CMB.NAME END AS BUY_NAME,TOT_AMT,SEL_CO_NBR,BUY_CO_NBR
			FROM CMP.CAL_ORD_HEAD HED INNER JOIN CMP.ORD_TYP TYP ON HED.ORD_TYP=TYP.ORD_TYP INNER JOIN CMP.CAL_ORD_DET AS DET ON HED.ORD_NBR=DET.ORD_NBR LEFT OUTER JOIN CMP.CAL_LST LST ON DET.CAL_NBR=LST.CAL_NBR LEFT OUTER JOIN CMP.COMPANY CMS ON HED.SEL_CO_NBR=CMS.CO_NBR LEFT OUTER JOIN CMP.COMPANY CMB ON HED.BUY_CO_NBR=CMB.CO_NBR
			WHERE DET.CAL_NBR=".$CalNbr."
			ORDER BY 2 ASC,5 DESC";
	//echo $query;
	$result=mysql_query($query);
		
	echo "<br/><table style='width:100%'>";
			$rowcol="a";
		
			echo "<tr class='listable'>";
			echo "<th class='listable'>No.</th>";
			echo "<th class='listable'>Tgl.</th>";
			echo "<th class='listable'>Tipe</th>";
			echo "<th class='listable'>No. Ref.</th>";
			echo "<th class='listable'>Judul</th>";
			echo "<th class='listable'>Penjual</th>";
			echo "<th class='listable'>Pembeli</th>";
			echo "<th class='listable'>Jumlah</th>";
			echo "<th class='listable'>Order</th>";
			echo "<th class='listable'>Stock</th>";
			echo "</tr>";
		$order=0;
		$inventory=0;
		while($row=mysql_fetch_array($result))
		
		{
		echo "<tr ".$alt." >";
		echo "<td class='listable-first' align='center'><a href='calendar-edit.php?ORD_NBR=".$row['ORD_NBR']."'>".$row['ORD_NBR']."</td>";
		echo "<td class='listable' style='text-align:center'>".$row['ORD_DTE']."</a></td>";
		echo "<td class='listable'>".$row['ORD_DESC']."</a></td>";
		echo "<td class='listable' align='right'>".$row['REF_NBR']."</a></td>";
		echo "<td class='listable'>".$row['ORD_TTL']."</a></td>";
		echo "<td class='listable'>".$row['SEL_NAME']."</a></td>";
		echo "<td class='listable'>".$row['BUY_NAME']."</a></td>";
		echo "<td class='listable' align='right'>".number_format($row['ORD_Q'],0,",",".")."</td>";
		if($row['ORD_TYP']=="ORD")
		{
			$order+=$row['ORD_Q'];
		}
		elseif($row['ORD_TYP']=="RCV")
		{
			$order-=$row['ORD_Q'];
		}
		elseif($row['ORD_TYP']=="RET")
		{
			$order-=$row['ORD_Q'];
		}
		echo "<td class='listable' align='right'>".number_format($order,0,",",".")."</td>";		
		if(($row['ORD_TYP']=="RCV")&&($row['BUY_CO_NBR']==1))
		{
			$inventory+=$row['ORD_Q'];
		}
		elseif(($row['ORD_TYP']=="REQ")&&($row['SEL_CO_NBR']==1))
		{
			$inventory-=$row['ORD_Q'];
		}
		elseif(($row['ORD_TYP']=="RET")&&($row['BUY_CO_NBR']==1))
		{
			$inventory-=$row['ORD_Q'];
		}
		echo "<td class='listable' align='right'>".number_format($inventory,0,",",".")."</td>";
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
