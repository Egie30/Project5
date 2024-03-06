<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$CoNbr=$_GET['CO_NBR'];
	$Typ=$_GET['TYP'];
	$query="SELECT REQ.ORD_NBR,REQ.ORD_DTE AS SORT_DTE,DATE_FORMAT(REQ.ORD_DTE,'%d-%m-%Y') AS ORD_DTE,REQ.REF_NBR,REQ.ORD_TTL,CASE WHEN NAME IS NULL THEN 'Tunai' ELSE NAME END AS NAME,REQ.TOT_AMT
			FROM CMP.CAL_ORD_HEAD REQ LEFT OUTER JOIN CMP.COMPANY COM ON REQ.BUY_CO_NBR=COM.CO_NBR LEFT OUTER JOIN CMP.CAL_ORD_HEAD INV ON INV.REQ_NBR LIKE CONCAT('%',REQ.REF_NBR,'%') AND REQ.BUY_CO_NBR=INV.BUY_CO_NBR
			WHERE REQ.ORD_TYP='REQ' AND INV.ORD_NBR IS NULL AND REQ.ORD_DTE BETWEEN ".getFiscalYear()." 
			GROUP BY REQ.ORD_NBR,REQ.ORD_DTE,REQ.REF_NBR,DATE_FORMAT(REQ.ORD_DTE,'%d-%m-%Y'),REQ.ORD_TTL,CASE WHEN NAME IS NULL THEN 'Tunai' ELSE NAME END,REQ.TOT_AMT
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
		echo "<td class='listable' style='text-align:center'>".$row['ORD_DTE']."</a></td>";
		echo "<td class='listable'>".$row['REF_NBR']."</a></td>";
		echo "<td class='listable'>".$row['ORD_TTL']."</a></td>";
		echo "<td class='listable'>".$row['NAME']."</a></td>";
		echo "<td class='listable' align='right'>".number_format($row['TOT_AMT'],0,",",".")."</td>";
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
