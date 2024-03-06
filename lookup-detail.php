<?php
	include "../framework/database/connect.php";
	include "../framework/functions/default.php";
	$OrdNbr=$_GET['ORD_NBR'];
	$ProdList=$_GET['PROD_LIST'];
	$HndOffTyp=$_GET['HND_OFF_TYP'];
	$PrsnNbr=substr($_GET['PRSN_NBR'],0,-1);
	//echo $OrdNbr;

	//Process pickup and delivery
	if($ProdList!=''){
		$Prod=explode(',',$ProdList);
		foreach($Prod as $OrdDetNbr){
			$query="UPDATE CMP.PRN_DIG_ORD_DET SET HND_OFF_TYP='".$HndOffTyp."',HND_OFF_NBR=".$PrsnNbr.",HND_OFF_TS=CURRENT_TIMESTAMP WHERE ORD_DET_NBR=".substr($OrdDetNbr,1);
			$result=mysql_query($query);
			//echo $query;
		}
		//Move status to finished when all items are picked up.
		$query="SELECT COUNT(*) AS UNF FROM CMP.PRN_DIG_ORD_DET WHERE HND_OFF_TS IS NULL AND ORD_NBR=".$OrdNbr;
		//echo $query;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		if($row['UNF']==0){
			$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET ORD_STT_ID='CP',PU_TS=CURRENT_TIMESTAMP WHERE ORD_NBR=".$OrdNbr;
			$result=mysql_query($query);	
			//echo $query;
		}
	}

	//If first letter is 'P'
	if(substr($OrdNbr,0,1)=='P'){
		$OrdDetNbr=substr($OrdNbr,1);
		$query="SELECT ORD_NBR FROM CMP.PRN_DIG_ORD_DET WHERE ORD_DET_NBR=".$OrdDetNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$OrdNbr=$row['ORD_NBR'];
	}
	//echo $OrdNbr;
		
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
		echo "<tr ";
		if($row['HND_OFF_TS']==""){
			echo "id='P".str_pad($row['ORD_DET_NBR'],6,0,STR_PAD_LEFT);
		}else{
			if($ProdList!=''){
				if(in_array('P'.str_pad($row['ORD_DET_NBR'],6,0,STR_PAD_LEFT),$Prod)){
					echo "style='background-color:#f8e6d0'";
				}else{
					echo "style='background-color:#dddddd'";
				}
			}else{
				echo "style='background-color:#dddddd'";
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
	echo "<div style='margin-top:10px;'>";
	echo "<input class='process' type='button' value='Ambil' onClick='syncGetContent(".chr(34)."detail".chr(34).",".chr(34)."lookup-detail.php?PRSN_NBR=".chr(34)."+prsnNbr+".chr(34)."&HND_OFF_TYP=PU&ORD_NBR=".$OrdNbr."&PROD_LIST=".chr(34)."+prodList);prodList=".chr(34).chr(34)."'/>&nbsp;";
	echo "<input class='process' type='button' value='Kirim' onClick='syncGetContent(".chr(34)."detail".chr(34).",".chr(34)."lookup-detail.php?PRSN_NBR=".chr(34)."+prsnNbr+".chr(34)."&HND_OFF_TYP=DL&ORD_NBR=".$OrdNbr."&PROD_LIST=".chr(34)."+prodList);prodList=".chr(34).chr(34)."'/>&nbsp;";
	echo "</div>";
?>