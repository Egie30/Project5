<?php
	header('Content-Type:text/javascript');
	include "framework/database/connect.php";
	
	$PrsnNbr=$_GET['PRSN_NBR'];
	$CoNbr=$_GET['CO_NBR'];
	$CshFloTyp=$_GET['CSH_FLO_TYP'];
	$OrdNbr=$_GET['ORD_NBR'];
	$TndAmt=$_GET['TND_AMT'];

	//Get new register number
	$query="SELECT COALESCE(MAX(REG_NBR),0)+1 AS NEW_NBR FROM CMP.CSH_REG";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$RegNbr=$row['NEW_NBR'];
	
	//If active transaction exist, use the current transaction number for the corresponding person number
	$query="SELECT TRSC_NBR FROM CMP.CSH_REG WHERE ACT_F=1 AND CRT_NBR=".$PrsnNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$TrscNbr=$row['TRSC_NBR'];
	//Otherwise create a new one
	if($TrscNbr==""){
		$query="SELECT COALESCE(MAX(TRSC_NBR),0)+1 AS NEW_NBR FROM CMP.CSH_REG";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$TrscNbr=$row['NEW_NBR'];
	}
	$query="INSERT INTO CMP.CSH_REG (REG_NBR,TRSC_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,ORD_NBR,TND_AMT) VALUES (".$RegNbr.",".$TrscNbr.",".$PrsnNbr.",".$CoNbr.",'".$CshFloTyp."',".$OrdNbr.",".$TndAmt.")";
	$result=mysql_query($query);
	//echo $query;
	
	if($CshFloTyp=="DP"){
		$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET VAL_PYMT_DOWN=".$RegNbr." WHERE ORD_NBR=".$OrdNbr;
	}
	if($CshFloTyp=="FL"){
		$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET VAL_PYMT_REM=".$RegNbr." WHERE ORD_NBR=".$OrdNbr;
	}
	$result=mysql_query($query);
?>
