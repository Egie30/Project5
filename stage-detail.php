<?php
	include "../framework/database/connect.php";
	include "../framework/functions/default.php";
	$ProdNbr=$_GET['PROD_NBR'];
	$SortBay=$_GET['SORT_BAY'];
	$PrsnNbr=substr($_GET['PRSN_NBR'],0,-1);

	//If first letter is 'P' then find the product
	if(substr($ProdNbr,0,1)=='P'){
		$OrdDetNbr=substr($ProdNbr,1);
		$query="SELECT ORD_NBR FROM CMP.PRN_DIG_ORD_DET WHERE ORD_DET_NBR=".$OrdDetNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$OrdNbr=$row['ORD_NBR'];
		
		//Process staging
		if(substr($SortBay,0,1)=='S'){
			//Find valid sort bay
			$SortBay=substr($SortBay,1);
			
			//If valid sort bay found then process staging
			$query="SELECT SORT_BAY_ID FROM CMP.SORT_BAY WHERE CO_NBR=271 AND SORT_BAY_ID='".$SortBay."'";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			//If not found then do nothing, the parent javascript will automatically flip the display to invalid
			if($row['SORT_BAY_ID']!=''){
				$Sorted=$OrdDetNbr;
				$query="UPDATE CMP.PRN_DIG_ORD_DET SET SORT_BAY_ID='".$SortBay."',SORT_BAY_TS=CURRENT_TIMESTAMP,SORT_BAY_NBR=".$PrsnNbr." WHERE ORD_DET_NBR=".$OrdDetNbr;
				$result=mysql_query($query);
				//echo $query;
				
				//Update finishing number
				$query="UPDATE CMP.PRN_DIG_ORD_DET SET FIN_CMP_Q=ORD_Q WHERE ORD_DET_NBR=".$OrdDetNbr;
				$result=mysql_query($query);
	
				//Update status to complete if all ORD_Q=FIN_CMP_Q in one order
				$query="SELECT SUM(ORD_Q) AS ORD_Q,SUM(FIN_CMP_Q) AS FIN_CMP_Q FROM CMP.PRN_DIG_ORD_DET WHERE ORD_NBR=".$OrdNbr;
				$result=mysql_query($query);
				$row=mysql_fetch_array($result);
				if($row['ORD_Q']==$row['FIN_CMP_Q']){
					$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET ORD_STT_ID='RD',CMP_TS=CURRENT_TIMESTAMP WHERE ORD_NBR=".$OrdNbr;
					$result=mysql_query($query);
				}
			}
		}
	}	
	
	//Need more error checking
	$query="SELECT ORD_DET_NBR,DET.ORD_NBR,DET_TTL,PRN_DIG_DESC,DET.PRN_DIG_PRC,ORD_Q,FIL_LOC,PRN_LEN,PRN_WID,FIN_BDR_WID,FIN_LOP_WID,FIN_BDR_DESC,FEE_MISC,FAIL_CNT,DISC_PCT,DISC_AMT,VAL_ADD_AMT,TOT_SUB,ROLL_F,HND_OFF_TYP,HND_OFF_TS,NAME,SORT_BAY_ID,GRM_CNT_TOP,GRM_CNT_BTM,GRM_CNT_LFT,GRM_CNT_RGT,PRFO_F
			FROM CMP.PRN_DIG_ORD_DET DET LEFT OUTER JOIN
			     CMP.PEOPLE PPL ON DET.HND_OFF_NBR=PPL.PRSN_NBR LEFT OUTER JOIN
			     CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP LEFT OUTER JOIN
				 CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP LEFT OUTER JOIN
			     CMP.PRN_DIG_FIN_BDR_TYP BDR ON DET.FIN_BDR_TYP=BDR.FIN_BDR_TYP
			WHERE ORD_NBR=".$OrdNbr;
	$result=mysql_query($query);
	//echo $query;
	echo "<table style='width:100%'>";
	echo "<tr>";
	echo "<th class='header'>Prod ID</th>";
	echo "<th class='header'>Item</td>";
	echo "<th class='header'>Qty</td>";
	echo "<th class='header'></td>";
	echo "<th class='header'></td>";
	echo "<th class='header'>Waktu</td>";
	while($row=mysql_fetch_array($result))
	{
		//Show new color if sort bay is found
		echo "<tr ";
		if($row['HND_OFF_TS']!=""){
			if(str_pad($row['ORD_DET_NBR'],6,0,STR_PAD_LEFT)==$OrdDetNbr){
				echo "style='background-color:#ffb1ac'";
			}else{
				echo "style='background-color:#dddddd'";
			}
		}else{
			if($Sorted==str_pad($row['ORD_DET_NBR'],6,0,STR_PAD_LEFT)){
				echo "id=S".$SortBay." style='background-color:#e3f7db'";	
			}else{
				if(str_pad($row['ORD_DET_NBR'],6,0,STR_PAD_LEFT)==$OrdDetNbr){
					echo "style='background-color:#eaccec'";
				}
			}
		}
		echo "'>";
		echo "<td class='detail-bold'>".str_pad($row['ORD_DET_NBR'],6,0,STR_PAD_LEFT)."</td>";
		echo "<td class='detail-left'>".$row['DET_TTL']." ".$row['PRN_DIG_DESC'];
		if(($row['PRN_LEN']!="")&&($row['PRN_WID']!="")){$prnDim=" ".$row['PRN_LEN']."x".$row['PRN_WID'];}else{$prnDim="";}
		echo " ".$prnDim."<br>";
		echo "<span style='color:#999999;'>Finishing: </span>".$row['FIN_BDR_DESC']."&nbsp;&nbsp;";
		echo "<span style='color:#999999;'>P: </span>".$row['FIN_BDR_WID']."&nbsp;&nbsp;";					
		echo "<span style='color:#999999;'>K: </span>".$row['FIN_LOP_WID']."&nbsp;&nbsp;";
		if(($row['GRM_CNT_TOP']!="")||($row['GRM_CNT_BTM']!="")||($row['GRM_CNT_LFT']!="")||($row['GRM_CNT_RGT']!="")){
			echo "<span style='color:#999999;'>Keling </span>";
			if($row['GRM_CNT_TOP']!=""){
				echo "<span style='color:#999999;'>A: </span>".$row['GRM_CNT_TOP']."&nbsp;&nbsp;";					
			}
			if($row['GRM_CNT_BTM']!=""){
				echo "<span style='color:#999999;'>B: </span>".$row['GRM_CNT_BTM']."&nbsp;&nbsp;";					
			}
			if($row['GRM_CNT_LFT']!=""){
				echo "<span style='color:#999999;'>KA: </span>".$row['GRM_CNT_LFT']."&nbsp;&nbsp;";					
			}
			if($row['GRM_CNT_RGT']!=""){
				echo "<span style='color:#999999;'>KI: </span>".$row['GRM_CNT_RGT']."&nbsp;&nbsp;";					
			}
		}
		if($row['PRFO_F']==1){echo "Perforasi";}
		echo "</td>";
		echo "<td class='detail-bold'>".$row['ORD_Q']."</td>";
		echo "<td class='detail-center'>";
		if($row['HND_OFF_TYP']=='PU'){echo "<img class='listable' src='img/container.png'>";}
		if($row['HND_OFF_TYP']=='DL'){echo "<img class='listable' src='img/van.png'>";}		
		echo "</td>";
		echo "<td class='detail-center'><div class='sort-bay'>";
		echo $row['SORT_BAY_ID'];
		echo "</div></td>";
		echo "<td class='detail-center'>";
		if($row['HND_OFF_TS']!=""){
			echo parseDateShort($row['HND_OFF_TS'])." ".parseHour($row['HND_OFF_TS']).":".parseMinute($row['HND_OFF_TS']);
			echo "<br/>";
			$Names=explode(' ',$row['NAME']);
			$count=1;
			foreach($Names as $Name){
				if($count==1){
					$nickName=$Name." ";
				}else{
					$nickName.=substr($Name,0,1);
				}
				$count++;
			}
			echo $nickName;
		}
		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
?>