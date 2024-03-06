<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";	

	$OrdDetNbr=$_GET['ORD_DET_NBR'];
	$CountType=$_GET['CNT_TYP']; //PRN or FIN
	$AddQuantity=$_GET['ADD_Q'];
	
	//Get current count	
	$query="SELECT ORD_NBR, ORD_Q,COALESCE(PRN_CMP_Q,0) AS PRN_CMP_Q,COALESCE(FIN_CMP_Q,0) AS FIN_CMP_Q
			  FROM CMP.PRN_DIG_ORD_DET DET
			 WHERE DET.DEL_NBR=0 AND ORD_DET_NBR=".$OrdDetNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$orderQuantity=$row['ORD_Q'];
	$curQuantity=$row[$CountType."_CMP_Q"];
	
	//Add the new quantity
	$newQuantity=$curQuantity+$AddQuantity;
	
	//If too many then assume ORD_Q (idiot-proofing)
	if($orderQuantity<$newQuantity){$newQuantity=$orderQuantity;}
	
	//If negative then zero (another idiot-proofing effort :)
	if($newQuantity<0){$newQuantity=0;}

	//Execute update
	$query="UPDATE CMP.PRN_DIG_ORD_DET
			   SET ".$CountType."_CMP_Q=".$newQuantity."
			 WHERE DEL_NBR=0 AND ORD_DET_NBR=".$OrdDetNbr;
	$result=mysql_query($query);
	
	//Process detail invoice journal
	if ($newQuantity > 0) {
		$query="INSERT INTO CMP.JRN_PRN_DIG (ORD_NBR, JRN_TYP, CRT_NBR)
				VALUES (".$row["ORD_NBR"].",'".$CountType."',".$_SESSION['personNBR'].")";
		//echo $query;
		$resultp=mysql_query($query);
	}
	//Display the new value through select for double-checking purposes
	$query="SELECT ORD_NBR,COALESCE(PRN_CMP_Q,0) AS PRN_CMP_Q,COALESCE(FIN_CMP_Q,0) AS FIN_CMP_Q
			  FROM CMP.PRN_DIG_ORD_DET DET
			 WHERE DET.DEL_NBR=0 AND ORD_DET_NBR=".$OrdDetNbr;
	$result=mysql_query($query);
	//echo $query;
	$row=mysql_fetch_array($result);
	echo $row[$CountType."_CMP_Q"];
	$OrdNbr=$row['ORD_NBR'];
	
	//Status update
	$query="SELECT SUM(ORD_Q) AS ORD_Q,SUM(PRN_CMP_Q) AS PRN_CMP_Q, SUM(FIN_CMP_Q) AS FIN_CMP_Q,ORD_STT_ID
			  FROM CMP.PRN_DIG_ORD_HEAD HED INNER JOIN
			       CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR
			 WHERE DET.DEL_NBR=0 AND HED.ORD_NBR=".$OrdNbr."
			 GROUP BY ORD_STT_ID";
	$result=mysql_query($query);
	//echo $query;
	$row=mysql_fetch_array($result);
	$orderQuantity=$row['ORD_Q'];
	$orderPrinting=$row['PRN_CMP_Q'];
	$orderFinishing=$row['FIN_CMP_Q'];
	$orderStatus=$row['ORD_STT_ID'];
	//echo $orderQuantity." ".$orderPrinting." ".$orderFinishing." ".$orderStatus;
			 
	//If printing but not all printed then change status to printing/process
	if(($orderPrinting>0)&&($row['ORD_STT_ID']=='QU')){
		$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET ORD_STT_ID='PR' WHERE ORD_NBR=".$OrdNbr;
		//echo $query;
		$result=mysql_query($query);
		$query="INSERT INTO CMP.JRN_PRN_DIG (ORD_NBR,ORD_STT_ID,CRT_TS,CRT_NBR)
				VALUES (".$OrdNbr.",'PR',CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
		$result=mysql_query($query);
		
	}
	
	//If all printed then change status to finishing
	if($orderPrinting==$orderQuantity){
		$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET ORD_STT_ID='FN' WHERE ORD_NBR=".$OrdNbr;
		$result=mysql_query($query);
		$query="INSERT INTO CMP.JRN_PRN_DIG (ORD_NBR,ORD_STT_ID,CRT_TS,CRT_NBR)
				VALUES (".$OrdNbr.",'FN',CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
		$result=mysql_query($query);
	}
	
	//If all printed and finished then change status to done
	if(($orderPrinting==$orderQuantity)&&($orderFinishing==$orderQuantity)){
		//Update to delivery if required
		$query="SELECT SUM(CASE WHEN HND_OFF_TYP='DL' THEN 1 ELSE 0 END) AS DL FROM CMP.PRN_DIG_ORD_DET WHERE DEL_NBR=0 AND ORD_NBR=".$OrdNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		if($row['DL']>0){
			$stt='DL';
		}
		$query="SELECT SUM(CASE WHEN HND_OFF_TYP='NS' THEN 1 ELSE 0 END) AS NS FROM CMP.PRN_DIG_ORD_DET WHERE DEL_NBR=0 AND ORD_NBR=".$OrdNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		if($row['NS']>0){
			$stt='NS';
		}else{		
			$stt='RD';
		}
		$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET ORD_STT_ID='".$stt."',CMP_TS=CURRENT_TIMESTAMP WHERE ORD_NBR=".$OrdNbr;
		$result=mysql_query($query);
		$query="INSERT INTO CMP.JRN_PRN_DIG (ORD_NBR,ORD_STT_ID,CRT_TS,CRT_NBR)
				VALUES (".$OrdNbr.",'".$stt."',CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
		$result=mysql_query($query);
	}
?>
