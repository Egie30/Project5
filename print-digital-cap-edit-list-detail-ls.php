<?php
	include "framework/database/connect.php";

	$searchQuery 	= trim(strtoupper(urldecode($_REQUEST[s])));
	$orderNumber	= $_GET['ORD_NBR'];
	
	if($orderNumber!=""){
		$whereClause="ORD_NBR=".$orderNumber;
	}else{
		$searchQ=explode(" ",$searchQuery);
		$whereClause="";
		foreach($searchQ as $searchQuery){
			$whereClause.="((ORD_NBR LIKE '%".$searchQuery."%' OR 
			ORD_TTL LIKE '%".$searchQuery."%' OR 
			BUY_CO_NBR LIKE '%".$searchQuery."%' OR 
			COM.NAME LIKE '%".$searchQuery."%' OR 
			BUY_PRSN_NBR LIKE '%".$searchQuery."%' OR 
			PPL.NAME LIKE '%".$searchQuery."%')) AND";
		}
		$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	}

	//Search for inventory number
	$query="SELECT 
		ORD_NBR,
		ORD_TTL,
		BUY_CO_NBR,
		COM.NAME AS CO_NAME,
		BUY_PRSN_NBR,
		PPL.NAME AS PPL_NAME,
		TOT_AMT
	FROM CMP.PRN_DIG_ORD_HEAD HED 
		LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR 
		LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
	WHERE $whereClause AND HED.DEL_NBR=0
	GROUP BY ORD_NBR
	ORDER BY HED.UPD_TS DESC";
	//echo "<PRE>".$query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)>0){
		echo "<table style='padding:0px;margin:0px'>";
		while($row=mysql_fetch_array($result)){
			if($row['TOT_AMT']==''){$totalAmount='0';}else{$totalAmount=$row['TOT_AMT'];}
			$OnClick="<tr $alt style='cursor:pointer;' onclick=".chr(34)."
				document.getElementById('livesearch').value='".$row['ORD_NBR']."';
				document.getElementById('ORD_TTL').value='".$row['ORD_TTL']."';
				document.getElementById('ORD_NBR').value=".$row['ORD_NBR'].";
				document.getElementById('AMT').value=".$totalAmount.";
				calcPay();
			".chr(34).">";
			echo $OnClick;
			echo "<td>";
			echo "<span style='color:#999999'>".$row['ORD_TTL']."</span> ". $code ." <br/>";
			echo "<span style='color:#999999'>".$row['ORD_NBR']."</span> ".$row['CO_NAME']." ".$row['PPL_NAME']."</div>";
			echo "</td>";
			echo "<td style='vertical-align:top;text-align:right'><b>".number_format($row['TOT_AMT'],0,",",".")."</b></td>";
			echo "</tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
		echo "</table>";
	}else{
		echo "Nama tidak ada didalam kumpulan data.";
	}
?>
