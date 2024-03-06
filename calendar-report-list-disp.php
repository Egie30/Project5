<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$CoNbr=$_GET['CO_NBR'];
	$Typ=$_GET['TYP'];
	//echo $Typ;
	$query="SELECT CONCAT(CO_ID,CAL_ID,CAL_TYP) AS CAL_CODE,CAL_TYP,CAL_ID,LST.CAL_NBR,CAL_DESC,CAL_PRC_BLK,CAL_PRC_PRN,
			SUM(CASE WHEN ORD_TYP='ORD' THEN ORD_Q ELSE 0 END)-SUM(CASE WHEN ORD_TYP='RCV' THEN ORD_Q ELSE 0 END)+SUM(CASE WHEN ORD_TYP='RET' THEN ORD_Q ELSE 0 END) AS SHP,
			SUM(CASE WHEN ORD_TYP='RCV' AND BUY_CO_NBR=1 THEN ORD_Q ELSE 0 END)-SUM(CASE WHEN ORD_TYP='REQ' AND SEL_CO_NBR=1 THEN ORD_Q ELSE 0 END)+SUM(CASE WHEN ORD_TYP='RET' AND SEL_CO_NBR=1 THEN ORD_Q ELSE 0 END) AS CMP
			FROM CMP.CAL_LST LST LEFT OUTER JOIN CMP.CAL_ORD_DET DET ON DET.CAL_NBR=LST.CAL_NBR LEFT OUTER JOIN CMP.CAL_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR LEFT OUTER JOIN CMP.COMPANY CMP ON LST.CO_NBR=CMP.CO_NBR
			WHERE ACTIVE_F IS TRUE AND LST.CO_NBR=".$CoNbr." AND LST.UPD_DTE BETWEEN ".getFiscalYear()."
			GROUP BY CONCAT(CO_ID,CAL_ID,CAL_TYP),CAL_NBR,CAL_DESC
			ORDER BY 2,3";
	//echo $query;
	$result=mysql_query($query);
	
	echo "<br/><table style='width:100%'>";
			$rowcol="a";
		
			echo "<tr class='listable'>";
			echo "<th class='listable'>Kode</th>";
			echo "<th class='listable'>Deskripsi</th>";
			echo "<th class='listable'>Blanko</th>";
			echo "<th class='listable'>Cetak</th>";
			echo "<th class='listable'>Pesan</th>";
			echo "<th class='listable'>Stock</th>";
			echo "</tr>";
		
		while($row=mysql_fetch_array($result))
		
		{
		echo "<tr ".$alt." >";
		echo "<td class='listable-first'><a href='calendar-list-edit.php?CAL_NBR=".$row['CAL_NBR']."'>".$row['CAL_CODE']."</a></td>";
		echo "<td class='listable'>".$row['CAL_DESC']."</td>";
		echo "<td class='listable' align='right'>".number_format($row['CAL_PRC_BLK'],0,",",".")."</td>";
		echo "<td class='listable' align='right'>".number_format($row['CAL_PRC_PRN'],0,",",".")."</td>";
		echo "<td class='listable' align='right'><a href='calendar-report-act.php?CAL_NBR=".$row['CAL_NBR']."'>".number_format($row['SHP'],0,",",".")."</a></td>";
		echo "<td class='listable' align='right'><a href='calendar-report-act.php?CAL_NBR=".$row['CAL_NBR']."'>".number_format($row['CMP'],0,",",".")."</a></td>";
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
