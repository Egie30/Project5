<?php 
	include '../framework/database/connect.php';
	include '../framework/functions/crypt.php';
	include '../framework/functions/default.php';

	$PrsnNbr  = $_GET['PRSN_NBR'];
	$LastPymt = $_GET['PAY_BEG_DTE'];
	$CoNbr    = $_GET['CO_NBR'];

	if (($CoNbr == 1002) || ($CoNbr == 271) || ($CoNbr == 2996) || ($CoNbr == 2997) || ($CoNbr == 3110)) {
		
		$query 		= "SELECT BON_MULT FROM PAY.PEOPLE WHERE PRSN_NBR=".$PrsnNbr;
		$result 	= mysql_query($query);	
		$row 		= mysql_fetch_array($result);	
		
		if($row['BON_MULT']==''){$BonMult=1;}else{$BonMult=$row['BON_MULT'];}
		
		//Mencari batas nominal mendapatkan bonus 
		$query 		= "SELECT FLEX_BASE_Q,
							  BASE_PCT,
							  DOC_BASE_Q,
							  FLEX_INC_Q,
							  DOC_INC_Q 
						FROM PAY.PRN_DIG_BON_PLAN 
						WHERE CURRENT_DATE BETWEEN BEG_DT AND END_DT";
		$result 	= mysql_query($query);	
		$row 		= mysql_fetch_array($result);
		$BasePct 	= $row['BASE_PCT'];
		$FlexBaseQ 	= $row['FLEX_BASE_Q'];
		$FlexIncQ 	= $row['FLEX_INC_Q'];
		$DocBaseQ 	= $row['DOC_BASE_Q'];
		$DocIncQ 	= $row['DOC_INC_Q'];
			
		$QueryParam = "SELECT VAL_R2S, VAL_R2P, BON_DIV FROM NST.PARAM_LOC PLC";
		$ResultParam= mysql_query($QueryParam);
		$rowParam   = mysql_fetch_array($ResultParam);
		$R2S		= $rowParam['VAL_R2S'];
		$R2P		= $rowParam['VAL_R2P'];
		$div 		= $rowParam['BON_DIV'];

		//Mencari lunas + order
		$query 		= "SELECT SUM(FLJ320P_BON)+SUM(FLJ320P_ALL) AS FLJ320P, 
								(COALESCE(SUM(KMC6501_BON),0) + COALESCE(SUM(KMC8000_BON * ".$R2S."),0) + COALESCE(SUM(KMC1085_BON * ".$R2P."),0))+ (COALESCE(SUM(KMC6501_ALL),0) + COALESCE(SUM(KMC8000_ALL * ".$R2S."),0) + COALESCE(SUM(KMC1085_ALL * ".$R2P."),0)) AS KMC6501,
								SUM(RVS640_BON)+SUM(RVS640_ALL) AS RVS640,
								SUM(AJ1800F_BON)+SUM(AJ1800F_ALL) AS AJ1800F, 
								SUM(HPL375_BON)+SUM(HPL375_ALL) AS HPL375, 
								SUM(ATX67_BON)+SUM(ATX67_ALL) AS ATX67 
						FROM CDW.PRN_DIG_DSH_BRD 
						WHERE DTE BETWEEN '".$LastPymt."' AND CURRENT_DATE ";
		$result 	= mysql_query($query);
		
		$row 		= mysql_fetch_array($result);
		$FLJ320P 	= $row['FLJ320P']/$div;
		$KMC6501 	= $row['KMC6501']/$div;	
		$RVS640 	= $row['RVS640']/$div;	
		$AJ1800F 	= $row['AJ1800F']/$div;	
		$HPL375 	= $row['HPL375']/$div;	
		$ATX67 		= $row['ATX67']/$div;
		

		$Bonus=0;
		if((($FLJ320P)>=$FlexBaseQ)&&($KMC6501>=$DocBaseQ)){
			$Bonus=$BasePct;
			if($FLJ320P>=$FlexIncQ){
				$FLJ320P=$FLJ320P-$FlexBaseQ;
				$Bonus=$Bonus+floor(($FLJ320P+$RVS640+$AJ1800F+$HPL375+$ATX67)/$FlexIncQ);
			}
			
			if($KMC6501>=$DocIncQ){
				$KMC6501=$KMC6501-$DocBaseQ;
				$Bonus=$Bonus+floor($KMC6501/$DocIncQ);
				
			}
		}else{
			$Bonus=0;
		};

		$Bonus=$Bonus*$BonMult;
		if(!bonusTenure($PrsnNbr,90)){$Bonus=0;}
		//echo $Bonus;
		$Reward = array('REWARD'=>$Bonus);

		echo simple_crypt(json_encode($Reward),'e');
	}
?>