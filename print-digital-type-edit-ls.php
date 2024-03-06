<?php
	include "framework/database/connect.php";
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);
	$whereClause="";
	foreach($searchQ as $searchQuery)
	{
		$whereClause.="(INV.INV_NBR LIKE '%".$searchQuery."%' OR INV.NAME LIKE '%".$searchQuery."%' OR COLR_DESC LIKE '%".$searchQuery."%' OR THIC LIKE '%".$searchQuery."%' OR SIZE LIKE '%".$searchQuery."%' OR WEIGHT LIKE '%".$searchQuery."%') AND ";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	//Search for kalender
	$query="SELECT INV.INV_NBR,PRC,CONCAT(INV.NAME,' ',COLR_DESC,' ',THIC,' ',SIZE,' ',WEIGHT) AS NAME_DESC,CMP.NAME,SUM(CASE WHEN MOV_TYP='IN' THEN MOV_CNT ELSE -1*MOV_CNT END) AS QTY
			FROM CMP.INVENTORY INV LEFT OUTER JOIN CMP.INV_LOG LOG ON LOG.INV_NBR=INV.INV_NBR INNER JOIN CMP.COMPANY CMP ON INV.CO_NBR=CMP.CO_NBR INNER JOIN CMP.INV_TYP TYP ON INV.INV_TYP=TYP.INV_TYP INNER JOIN CMP.INV_COLR CLR ON INV.COLR_NBR=CLR.COLR_NBR
			WHERE ".$whereClause."
			GROUP BY INV.INV_NBR,CONCAT(INV.NAME,' ',COLR_DESC,' ',THIC,' ',SIZE,' ',WEIGHT),CMP.NAME
			ORDER BY 2 ASC";
	//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)>0)
	{
		echo "<table>";
    	echo "<tr>";
        echo "<th class='listable'>No.</th>";
        echo "<th class='listable'>Nama</th>";
        echo "<th class='listable'>Perusahaan</th>";
        echo "<th class='listable'>Harga</th>";
    	echo "</tr>";

		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt onclick=".chr(34)."document.getElementById('INV_NBR').value=".$row['INV_NBR'].chr(34).">";
			echo "<td style='text-align:right'>".$row['INV_NBR']."</td>";
			echo "<td>".$row['NAME_DESC']."</td>";
			echo "<td>".$row['NAME']."</td>";
			echo "<td style='text-align:right'>Rp. ".number_format($row['PRC'],0,",",".")."</td>";
			echo "</tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}

		echo "</tbody>";
		echo "</table>";
	}else{
		echo "Nama tidak ada didalam kumpulan data.";
	}
?>
