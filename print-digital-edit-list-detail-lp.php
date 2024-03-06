<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$searchQuery=trim(urldecode($_REQUEST[q]));
	$searchQuery=str_replace("\'","'",$searchQuery);
	//echo $searchQuery;

	//Parse and remove previous quantity
	$prevQuantity=substr(strrchr($searchQuery,"@"),1);
	$searchQuery=substr($searchQuery,0,strrpos($searchQuery,"@"));
	//echo $prevQuantity." ";

	//Parse and remove quantity
	$orderQuantity=substr(strrchr($searchQuery,"@"),1);
	$searchQuery=substr($searchQuery,0,strrpos($searchQuery,"@"));
	//echo $orderQuantity." ";

	//Extract person number
	$personNbr=substr(strrchr($searchQuery,"="),1);
	//echo $searchQuery;
	
	//Get plan type
	$query="SELECT BRKR_PLAN_TYP FROM CMP.PEOPLE WHERE PRSN_NBR=$personNbr";
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$planType=$row['BRKR_PLAN_TYP'];
	if(($row['BRKR_PLAN_TYP']!="")&&($orderQuantity>0)){
		//Get digital print type
		$begPos=strpos($searchQuery,"'");
		$printType=substr($searchQuery,$begPos,strpos($searchQuery,"'",$begPos+1)-$begPos+1);
		//echo $printType;
	
		//Iterate order quantity and price
		$query="SELECT SUM(COALESCE(ORD_Q,0)) AS CUR_Q
				  FROM CMP.PEOPLE PPL LEFT OUTER JOIN
				       CMP.PRN_DIG_ORD_HEAD HED ON PPL.PRSN_NBR=HED.BUY_PRSN_NBR
					   LEFT OUTER JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR
				 WHERE BUY_PRSN_NBR=$personNbr AND MONTH(ORD_TS)=MONTH(CURRENT_DATE) AND YEAR(ORD_TS)=YEAR(CURRENT_DATE)";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		if($row['CUR_Q']==""){
			$curQuantity=0;
		}else{
			$curQuantity=$row['CUR_Q']-$prevQuantity;
		}
		$remQuantity=$orderQuantity;
		//echo "$remQuantity $curPrice $curQuantity";
		while($remQuantity>0){
			$query="SELECT PRC,MAX_Q FROM CMP.PRN_DIG_BRKR_SCHED
					 WHERE PLAN_TYP='$planType' AND PRN_DIG_TYP=$printType AND MIN_Q<=$curQuantity+1 AND MAX_Q>=$curQuantity+1";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			if(mysql_num_rows($result)==0){
				exit;
			}else{
				$curPrice=$row['PRC'];
				if($row['MAX_Q']>=$curQuantity+$remQuantity){
					//echo "Here $remQuantity $curPrice $curQuantity ";
					$totRev+=$remQuantity*$curPrice;
					//echo "$totRev ";
					$remQuantity=0;
				}else{
					//echo "There $remQuantity $curPrice $curQuantity ";
					$totRev+=($row['MAX_Q']-$curQuantity)*$curPrice;
					//echo "$totRev ";
					$remQuantity=$remQuantity-($row['MAX_Q']-$curQuantity);
					$curQuantity=$row['MAX_Q'];
				}
			}
			//$count++;if($count>10){exit;}
		}

		//Fetch the original price
		$query="SELECT PRN_DIG_PRC
				  FROM CMP.PRN_DIG_TYP
				 WHERE PRN_DIG_TYP=$printType";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$origPrice=$row['PRN_DIG_PRC'];

		//Apply discount
		echo $origPrice-$totRev/$orderQuantity;
	}else{
		//Get historical discount
		$query="SELECT DISC_AMT
				  FROM CMP.RAT_ENG
				 WHERE $searchQuery AND DIV_ID='PRN'
				   AND UPD_TS=(SELECT MAX(UPD_TS) FROM CMP.RAT_ENG WHERE $searchQuery AND DIV_ID='PRN')";
		//echo $query;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		if(mysql_num_rows($result)==0){
			$histDisc=0;
		}else{
			$histDisc=$row['DISC_AMT'];
		}
		
		//Get PRN_DIG_TYP value
		$prnDigTyp=substr($searchQuery,strpos($searchQuery,"'")+1,strpos($searchQuery,"'",strpos($searchQuery,"'")+1)-strpos($searchQuery,"'")-1);
		
		//Get current promo
		$query="SELECT PROMO_DISC_AMT FROM CMP.PRN_DIG_PROMO WHERE BEG_DT<=CURRENT_DATE AND END_DT>=CURRENT_DATE AND PRN_DIG_TYP='$prnDigTyp'";
		//echo $query;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		if(mysql_num_rows($result)==0){
			$curPromo=0;
		}else{
			$curPromo=$row['PROMO_DISC_AMT'];
		}
		
		//Determine promo or historical discount to be applied
		if($histDisc>=$curPromo){
			$finalDisc=$histDisc;
		}elseif($histDisc<$curPromo){
			$finalDisc=$curPromo;
		}
		if($finalDisc!=0){echo $finalDisc;}
	}
	//PRN_DIG_TYP='ADXTRA' AND PRSN_NBR=274
?>