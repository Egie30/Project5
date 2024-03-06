<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";

	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	$CalCode=$_GET['CAL_CODE'];
	$ordTyp=$_GET['ORD_TYP'];
	
	if($CalCode!=""){
		$whereClause="CONCAT (CO_ID,CAL_ID,CAL_TYP)='".$CalCode."'";
	}else{
		$searchQ=explode(" ",$searchQuery);
		$whereClause="";
		if($ordTyp=='INV_TBL'){$whereClause=" CAL_CODE<>'".$_GET['INVNBR']."' AND ";}
		foreach($searchQ as $searchQuery)
		{
				$whereClause.="(CONCAT(CO_ID,CAL_ID,CAL_TYP) LIKE '%".$searchQuery."%' OR CAL_DESC LIKE '%".$searchQuery."%') AND ";
			}
		$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	}
	//Search for inventory number
	$query="SELECT CAL_NBR,CO_ID,CAL.CO_NBR,CAL_ID,CAL_TYP,CONCAT(CO_ID,CAL_ID,CAL_TYP) AS CAL_CODE,CAL_DESC,CAL_PRC_BLK,CAL_PRC_PRN
	        FROM CMP.CAL_LST CAL INNER JOIN CMP.COMPANY COM ON CAL.CO_NBR=COM.CO_NBR
			WHERE ACTIVE_F IS TRUE AND CAL.UPD_DTE BETWEEN ".getFiscalYear()." AND ".$whereClause."
			ORDER BY 1 ASC ";
	//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)>0)
	{
		echo "<table style='width:343px;padding:0px;margin:0px'>";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt><td>";
			echo "".$row['CAL_CODE']."";
			echo " <span style='color:#999999'>".$row['CAL_DESC']."</span>"." ";
			echo "<a style='cursor:pointer' onclick=".chr(34)."document.getElementById('label-print').innerText='Harga Blanko';document.getElementById('CAL_DESC').value='".addslashes($row['CAL_DESC'])."';document.getElementById('CAL_PRC').value=".$row['CAL_PRC_BLK'].";document.getElementById('PRN_F').value=0;document.getElementById('CAL_NBR').value=".$row['CAL_NBR'].";calcPay();".chr(34).">".number_format($row['CAL_PRC_BLK'],0,",",".")."</a>";
			echo "<td style='vertical-align:top;text-align:right'>";
			echo "<a style='cursor:pointer' onclick=".chr(34)."document.getElementById('label-print').innerText='Harga Cetak';document.getElementById('CAL_DESC').value='".addslashes($row['CAL_DESC'])."';document.getElementById('CAL_PRC').value=".$row['CAL_PRC_PRN'].";document.getElementById('PRN_F').value=1;document.getElementById('CAL_NBR').value=".$row['CAL_NBR'].";calcPay();".chr(34).">".number_format($row['CAL_PRC_PRN'],0,",",".")."</a>";
			echo "</td></tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
	}else{
		echo "Nama tidak ada didalam kumpulan data.";
	}
?>
