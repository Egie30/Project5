<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";	

	$OrdNbr=$_GET['ORD_NBR'];
	$CountType=$_GET['CNT_TYP']; //PRN or FIN
	
	//Get current count	
	$query="SELECT SUM(ORD_Q) AS ORD_Q,SUM(PRN_CMP_Q) AS PRN_CMP_Q,SUM(FIN_CMP_Q) AS FIN_CMP_Q
			  FROM CMP.PRN_DIG_ORD_DET DET
			 WHERE DET.DEL_NBR=0 AND ORD_NBR=".$OrdNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$orderQuantity=$row['ORD_Q'];
	$curQuantity=$row[$CountType."_CMP_Q"];
	
	//Making sure the denominator is not zero
	if($orderQuantity==0){$orderQuantity=1;}

	//Set progress bar
	$pctValue=number_format($curQuantity/$orderQuantity*100,0);
	if($pctValue<50){
		$barColor="red";
	}elseif($pctValue<100){
		$barColor="orange";
	}else{
		$barColor="";
	}
	echo "<div class='meter $barColor' style='width:130px'><span style='width: $pctValue%'></span></div>";
?>
