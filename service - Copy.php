<?php

	// include "framework/database/connect.php";
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";

	$query 		= "SELECT CO_NBR_DEF FROM NST.PARAM_LOC";
	$result 	= mysql_query($query,$local);
	while ($row = mysql_fetch_array($result)) 
	{
		$conbrdef = $row['CO_NBR_DEF'];
	}

	$query 	= " SELECT 	 GROUP_CONCAT(HED.ORD_NBR) 	       AS ORD_NBR,  
						 GROUP_CONCAT(HED.ORD_DTE) 	       AS ORD_DTE,
						 GROUP_CONCAT(HED.RCV_CO_NBR)      AS RCV_CO_NBR,
						 GROUP_CONCAT(HED.REF_NBR) 	       AS REF_NBR, 
						 GROUP_CONCAT(HED.SHP_CO_NBR)      AS SHP_CO_NBR, 
						 GROUP_CONCAT(HED.IVC_TYP) 	       AS IVC_TYP,
						 GROUP_CONCAT(HED.FEE_MISC) 	   AS FEE_MISC,  
						 GROUP_CONCAT(HED.DISC_PCT) 	   AS DISC_PCT,  
						 GROUP_CONCAT(HED.DISC_AMT) 	   AS DISC_AMT,  
						 GROUP_CONCAT(HED.TOT_AMT) 	       AS TOT_AMT,  
						 GROUP_CONCAT(HED.PYMT_DOWN) 	   AS PYMT_DOWN, 
						 GROUP_CONCAT(HED.PYMT_REM) 	   AS PYMT_REM,   
						 GROUP_CONCAT(HED.TOT_REM) 	       AS TOT_REM,   
						 GROUP_CONCAT(HED.DL_TS) 		   AS DL_TS,   
						 GROUP_CONCAT(HED.SPC_NTE) 	       AS SPC_NTE,   
						 GROUP_CONCAT(HED.IVC_PRN_CNT)     AS IVC_PRN_CNT,   
						 GROUP_CONCAT(HED.DEL_F) 		   AS DEL_F,   
						 GROUP_CONCAT(HED.CRT_TS) 	       AS CRT_TS,   
						 GROUP_CONCAT(HED.CRT_NBR) 	       AS CRT_NBR,
						 GROUP_CONCAT(HED.UPD_TS) 	       AS UPD_TS,    
						 GROUP_CONCAT(HED.UPD_NBR) 	       AS UPD_NBR,   
						 GROUP_CONCAT(HED.TAX_APL_ID)      AS TAX_APL_ID,
						 GROUP_CONCAT(HED.TAX_AMT) 	       AS TAX_AMT,    
						 GROUP_CONCAT(HED.SLS_PRSN_NBR)    AS SLS_PRSN_NBR,    
						 GROUP_CONCAT(HED.SLS_TYP_ID)      AS SLS_TYP_ID,    
						 GROUP_CONCAT(HED.VAL_PYMT_DOWN)   AS VAL_PYMT_DOWN,
						 GROUP_CONCAT(HED.VAL_PYMT_REM)    AS VAL_PYMT_REM,  
						 GROUP_CONCAT(HED.PYMT_DOWN_TS)    AS PYMT_DOWN_TS,   
						 GROUP_CONCAT(HED.PYMT_REM_TS)     AS PYMT_REM_TS,     
						 GROUP_CONCAT(HED.PYMT_TYP) 	   AS PYMT_TYP,   
						 GROUP_CONCAT(HED.CAT_SUB_NBR)     AS CAT_SUB_NBR,   
						 GROUP_CONCAT(HED.ACTG_TYP) 	   AS ACTG_TYP 
						 FROM RTL.RTL_STK_HEAD HED JOIN CMP.COMPANY COM ON HED.RCV_CO_NBR = COM.CO_NBR  WHERE COM.ACCT_EXEC_NBR != 'NULL' AND HED.CAT_SUB_NBR = 273 AND HED.IVC_TYP = 'PO' AND HED.DEL_F = 0 AND HED.TOT_AMT > 0 ";
	// echo $query;
	$result 	= mysql_query($query,$local);

	while($row  = mysql_fetch_array($result))
	{
		$ORD_NBR 		= $row['ORD_NBR'];
		$ORD_DTE 			= $row['ORD_DTE'];
		$RCV_CO_NBR		 	= $row['RCV_CO_NBR'];
		$REF_NBR 			= $row['REF_NBR'];
		$SHP_CO_NBR 		= $row['SHP_CO_NBR'];
		$IVC_TYP 			= $row['IVC_TYP'];
		$FEE_MISC 			= $row['FEE_MISC'];
		$DISC_PCT 			= $row['DISC_PCT'];
		$DISC_AMT 			= $row['DISC_AMT'];
		$TOT_AMT 			= $row['TOT_AMT'];
		$PYMT_DOWN 			= $row['PYMT_DOWN'];
		$PYMT_REM 			= $row['PYMT_REM'];
		$TOT_REM 			= $row['TOT_REM'];
		$DL_TS 				= $row['DL_TS'];
		$SPC_NTE 			= $row['SPC_NTE'];
		$IVC_PRN_CNT 		= $row['IVC_PRN_CNT'];
		$DEL_F 				= $row['DEL_F'];
		$CRT_TS 			= $row['CRT_TS'];
		$CRT_NBR 			= $row['CRT_NBR'];
		$UPD_TS 			= $row['UPD_TS'];
		$UPD_NBR 			= $row['UPD_NBR'];
		$TAX_APL_ID 		= $row['TAX_APL_ID'];
		$TAX_AMT 			= $row['TAX_AMT'];
		$SLS_PRSN_NBR 		= $row['SLS_PRSN_NBR'];
		$SLS_TYP_ID 		= $row['SLS_TYP_ID'];
		$VAL_PYMT_DOWN 		= $row['VAL_PYMT_DOWN'];
		$VAL_PYMT_REM 		= $row['VAL_PYMT_REM'];
		$PYMT_DOWN_TS 		= $row['PYMT_DOWN_TS'];
		$PYMT_REM_TS 		= $row['PYMT_REM_TS'];
		$PYMT_TYP 			= $row['PYMT_TYP'];
		$CAT_SUB_NBR 		= $row['CAT_SUB_NBR'];
		$ACTG_TYP 			= $row['ACTG_TYP'];

		$ordnbrz        =  explode(',',$ORD_NBR);
		$ord_dtez       =  explode(',',$ORD_DTE);
		$rcv_conbrz     =  explode(',',$RCV_CO_NBR);
		$ref_nbrz 	    =  explode(',',$REF_NBR);
		$shp_co_nbrz    =  explode(',',$SHP_CO_NBR);
		$ivc_typz 	    =  explode(',',$IVC_TYP);
		$fee_miscz 	    =  explode(',',$FEE_MISC);
		$disc_pctz 	    =  explode(',',$DISC_PCT);
		$disc_amtz 	    =  explode(',',$DISC_AMT);
		$tot_amtz  	    =  explode(',',$TOT_AMT);
		$pymt_downz	    =  explode(',',$PYMT_DOWN);
		$pymt_remz 	    =  explode(',',$PYMT_REM);
		$tot_remz  	    =  explode(',',$TOT_REM);
		$del_tsz  	    =  explode(',',$DL_TS);
		$spc_ntez  	    =  explode(',',$SPC_NTE);
		$ivc_prn_cntz   =  explode(',',$IVC_PRN_CNT);
		$del_fz  	    =  explode(',',$DEL_F);
		$crt_tsz  	    =  explode(',',$CRT_TS);
		$crt_nbrz  	    =  explode(',',$CRT_NBR);
		$upd_tsz  	    =  explode(',',$UPD_TS);
		$upd_nbrz  	    =  explode(',',$UPD_NBR);
		$tax_apl_idz    =  explode(',',$TAX_APL_ID);
		$tax_amtz  	    =  explode(',',$TAX_AMT);
		$sls_prsn_nbrz  =  explode(',',$SLS_PRSN_NBR);
		$sls_typ_idz    =  explode(',',$SLS_TYP_ID);
		$val_pymt_downz =  explode(',',$VAL_PYMT_DOWN);
		$val_pymt_remz  =  explode(',',$VAL_PYMT_REM);
		$pymt_down_tsz  =  explode(',',$PYMT_DOWN_TS);
		$pymt_rem_tsz   =  explode(',',$PYMT_REM_TS);
		$pymt_typz 	    =  explode(',',$PYMT_TYP);
		$cat_sub_nbrz   =  explode(',',$CAT_SUB_NBR);
		$actg_typz 	    =  explode(',',$ACTG_TYP);


		foreach ($ordnbrz as $index  => $ORD_NBRS) 
		{
				
				$query     = "INSERT INTO $RTL.RTL_STK_HEAD
											   (ORD_NBR,
												ORD_DTE,
												RCV_CO_NBR,
												REF_NBR,
												SHP_CO_NBR,
												IVC_TYP,
												FEE_MISC,
												DISC_PCT,
												DISC_AMT,
												TOT_AMT,
												PYMT_DOWN,
												PYMT_REM,
												TOT_REM,
												DL_TS,
												SPC_NTE,
												IVC_PRN_CNT,
												DEL_F,
												CRT_TS,
												CRT_NBR,
												UPD_TS,
												UPD_NBR,
												TAX_APL_ID,
												TAX_AMT,
												SLS_PRSN_NBR,
												SLS_TYP_ID,
												VAL_PYMT_DOWN,
												VAL_PYMT_REM,
												PYMT_DOWN_TS,
												PYMT_REM_TS,
												PYMT_TYP,
												CAT_SUB_NBR,
												ACTG_TYP,
												CO_NBR_DEF) 
							  VALUES ('".$ORD_NBRS."',
							  		  '".$ord_dtez[$index]."',
							  		  '".$rcv_conbrz[$index]."',
							  		  '".$ref_nbrz[$index]."', 
							  		  '".$shp_co_nbrz[$index]."', 
							  		  '".$ivc_typz[$index]."',
							  		  '".$fee_miscz[$index]."',
							  		  '".$disc_pctz[$index]."',
							  		  '".$disc_amtz[$index]."',
							  		  '".$tot_amtz[$index]."',
							  		  '".$pymt_downz[$index]."',
							  		  '".$pymt_remz[$index]."',
							  		  '".$tot_remz[$index]."',
							  		  '".$del_tsz[$index]."',
							  		  '".$spc_ntez[$index]."',
							  		  '".$ivc_prn_cntz[$index]."',
							  		  '".$del_fz[$index]."',
							  		  '".$crt_tsz[$index]."',
							  		  '".$crt_nbrz[$index]."',
							  		  '".$upd_tsz[$index]."',
							  		  '".$upd_nbrz[$index]."',
							  		  '".$tax_apl_idz[$index]."',
							  		  '".$tax_amtz[$index]."',
							  		  '".$sls_prsn_nbrz[$index]."',
							  		  '".$sls_typ_idz[$index]."',
							  		  '".$val_pymt_downz[$index]."',
							  		  '".$val_pymt_remz[$index]."',
							  		  '".$pymt_down_tsz[$index]."',
							  		  '".$pymt_rem_tsz[$index]."',
							  		  '".$pymt_typz[$index]."',
							  		  '".$cat_sub_nbrz[$index]."',
							  		  '".$actg_typz[$index]."',
							  		  '".$conbrdef."')";
				// echo $query.'<br>';
				$result = mysql_query($query,$cloud);
				// $query 	= str_replace($RTL,"RTL",$query);
				// $result = mysql_query($query,$local);
		

	}
	}
	
	

?>