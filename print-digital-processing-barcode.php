<?php
	include "framework/functions/dotmatrix.php";
	include "framework/database/connect.php";
	
	$OrdDetNbr=$_GET['ORD_DET_NBR'];
	
	$query="SELECT ORD_DET_NBR,DET.ORD_NBR,COM.NAME AS NAME_CO,ORD_TTL,PPL.NAME AS NAME_PPL,DET_TTL,PRN_DIG_EQP,PRN_DIG_DESC,DET.PRN_DIG_PRC,ORD_Q,FIL_LOC,PRN_LEN,PRN_WID,FIN_BDR_DESC,FAIL_CNT,DISC_PCT,DISC_AMT,VAL_ADD_AMT,TOT_SUB,COALESCE(FIN_BDR_WID,0) AS FIN_BDR_WID,FIN_BDR_DESC,COALESCE(FIN_LOP_WID,0) AS FIN_LOP_WID,GRM_CNT_TOP,GRM_CNT_BTM,GRM_CNT_LFT,GRM_CNT_RGT,COALESCE(PRN_CMP_Q,0) AS PRN_CMP_Q,COALESCE(FIN_CMP_Q,0) AS FIN_CMP_Q,PRFO_F
			FROM CMP.PRN_DIG_ORD_DET DET INNER JOIN
			     CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR LEFT OUTER JOIN
			     CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP LEFT OUTER JOIN
			     CMP.PEOPLE PPL ON PRSN_NBR=BUY_PRSN_NBR LEFT OUTER JOIN
			     CMP.COMPANY COM ON COM.CO_NBR=BUY_CO_NBR LEFT OUTER JOIN
			     CMP.PRN_DIG_FIN_BDR_TYP BDR ON DET.FIN_BDR_TYP=BDR.FIN_BDR_TYP			     
			WHERE DET.DEL_NBR=0 AND ORD_DET_NBR=".$OrdDetNbr;
		    //echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	//Only print one label for each type
	//if($row['PRN_DIG_EQP']=='FLJ320P'){$cnt=$row['ORD_Q'];}else{$cnt=1;}
	if($row['ORD_Q']>1){$pieces="pcs";}else{$pieces="pc";}
	//for ($item=1;$item<=$cnt;$item++){
		//$string=leadZero($row['ORD_DET_NBR'],6)." ".followSpace(trim($row['NAME_CO']." ".$row['NAME_PPL']),25).leadSpace($item." of ".$cnt,12);
		$string=leadZero($row['ORD_DET_NBR'],6)." ".followSpace(trim($row['NAME_CO']." ".$row['NAME_PPL']),29).leadSpace($row['ORD_Q']." ".$pieces,8);
		$string.=chr(13).chr(10);
		if(($row['PRN_LEN']!="")&&($row['PRN_WID']!="")){$prnDim=$row['PRN_LEN']."x".$row['PRN_WID'];}else{$prnDim="";}
		$string.=substr(trim($row['ORD_TTL']),0,44).chr(13).chr(10);
		$string.=substr(trim($row['DET_TTL']." ".$row['PRN_DIG_DESC']." ".$prnDim),0,44).chr(13).chr(10);
		//Deactivate this line until there is an available label space
		//$string.=$row['FIN_BDR_DESC']." ";
		//$string.="P".$row['FIN_BDR_WID']." ";
		//$string.="K".$row['FIN_LOP_WID']." ";
		//if(($row['GRM_CNT_TOP']!="")||($row['GRM_CNT_BTM']!="")||($row['GRM_CNT_LFT']!="")||($row['GRM_CNT_RGT']!="")){
		//	if($row['GRM_CNT_TOP']!=""){
		//		$string.="A".$row['GRM_CNT_TOP']." ";					
		//	}
		//	if($row['GRM_CNT_BTM']!=""){
		//		$string.="B".$row['GRM_CNT_BTM']." ";					
		//	}
		//	if($row['GRM_CNT_LFT']!=""){
		//		$string.="KA".$row['GRM_CNT_LFT']." ";					
		//	}
		//	if($row['GRM_CNT_RGT']!=""){
		//		$string.="KI".$row['GRM_CNT_RGT']." ";					
		//	}
		//}
		//if($row['PRFO_F']==1){$string.="Perf";}
		//$string.=chr(13).chr(10);
		$prnString.=$string;
		$dspString.=$string;
		$prnString.=chr(27)."(B".chr(13).chr(0).chr(5).chr(2).chr(-3).chr(11).chr(0).chr(2);
		$prnString.="P".leadZero($OrdDetNbr,6).chr(13).chr(10).chr(13).chr(10).chr(13).chr(10);
		$dspString.="||||||||||||||||||||||".chr(13).chr(10);
		$dspString.="||||||||||||||||||||||".chr(13).chr(10);
	//}

	echo "<pre style='font-size:9pt;letter-spacing:-1.25px;'>";
	echo $dspString;
	echo "</pre>";
	
	$fh=fopen("print-digital/label/".$OrdDetNbr.".txt", "w");
	fwrite($fh, chr(15).chr(27).chr(67).chr(0).chr(1).$prnString.chr(18));
	fclose($fh);
	
	//Remember the order number
	$OrdNbr=$row['ORD_NBR'];
	
	//Update printing number
	$query="UPDATE CMP.PRN_DIG_ORD_DET SET PRN_CMP_Q=ORD_Q WHERE DEL_NBR=0 AND ORD_DET_NBR=".$OrdDetNbr;
	$result=mysql_query($query);
	
	//Update status to complete if all ORD_Q=PRN_CMP_Q in one order
	$query="SELECT SUM(ORD_Q) AS ORD_Q,SUM(PRN_CMP_Q) AS PRN_CMP_Q FROM CMP.PRN_DIG_ORD_DET WHERE DEL_NBR=0 AND ORD_NBR=".$OrdNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	if($row['ORD_Q']==$row['PRN_CMP_Q']){
		$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET ORD_STT_ID='FN' WHERE ORD_NBR=".$OrdNbr;
		$result=mysql_query($query);
	}
?>