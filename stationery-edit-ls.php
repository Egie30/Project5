<?php
	include "framework/database/connect.php";
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);
	$whereClause="";
	foreach($searchQ as $searchQuery)
	{
		$whereClause.="(STA.STA_NBR LIKE '%".$searchQuery."%' OR STA.NAME LIKE '%".$searchQuery."%' OR COLR_DESC LIKE '%".$searchQuery."%' OR MATR LIKE '%".$searchQuery."%' OR SIZE LIKE '%".$searchQuery."%' OR TYPE LIKE '%".$searchQuery."%') AND ";
	}
	$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	//Search for kalender
	$query="SELECT STA.STA_NBR,CONCAT(STA.NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE) AS NAME_DESC,
			CMP.NAME,SUM(CASE WHEN MOV_TYP='IN' THEN MOV_CNT ELSE -1*MOV_CNT END) AS QTY
			FROM CMP.STATIONERY STA LEFT OUTER JOIN 
				 CMP.STA_LOG LOG ON LOG.STA_NBR=STA.STA_NBR INNER JOIN 
				 CMP.COMPANY CMP ON STA.CO_NBR=CMP.CO_NBR INNER JOIN 
				 CMP.STA_TYP TYP ON STA.STA_TYP=TYP.STA_TYP INNER JOIN 
				 CMP.STA_COLR CLR ON STA.COLR_NBR=CLR.COLR_NBR
			WHERE ".$whereClause."
			GROUP BY STA.STA_NBR,CONCAT(STA.NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE),CMP.NAME
			ORDER BY 2 ASC";
	//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)>0)
	{
		$rowcol="a";

		echo "<table>";
    	echo "<tr>";
        echo "<th class='listable'>No.</th>";
        echo "<th class='listable'>Nama</th>";
        echo "<th class='listable'>Perusahaan</th>";
        echo "<th class='listable'>Sisa</th>";
    	echo "</tr>";

		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt onclick=".chr(34)."document.getElementById('STA_NBR').value=".$row['STA_NBR'].chr(34).">";
			echo "<td style='text-align:right'>".$row['STA_NBR']."</td>";
			echo "<td>".$row['NAME_DESC']."</td>";
			echo "<td>".$row['NAME']."</td>";
			echo "<td style='text-align:right'>".number_format($row['QTY'],0,",",".")."</td>";
			echo "</tr>";
			if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}

		echo "</tbody>";
		echo "</table>";
	}else{
		echo "Nama tidak ada didalam kumpulan data.";
	}
?>
