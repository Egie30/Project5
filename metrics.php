<?php
	include_once "home-cdw.php";
	include "framework/functions/print-digital.php";
	
	$QueryParam = "SELECT VAL_R2S, VAL_R2P FROM NST.PARAM_LOC PLC";
	$ResultParam= mysql_query($QueryParam);
	$rowParam   = mysql_fetch_array($ResultParam);
	$R2S		= $rowParam['VAL_R2S'];
	$R2P		= $rowParam['VAL_R2P'];
?>


	    <!--===============================|| Chart #1 [Monthly Price Index] ||===============================-->
<?php
		if($UpperSec<5){
            $query="SELECT 
						DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM,
						DATE_FORMAT(DTE,'%c') AS ORD_MONTH,
						DATE_FORMAT(DTE,'%Y') AS ORD_YEAR,
						SUM(FLJ320P_ALL) AS FLJ320P,
						SUM(KMC6501_ALL) AS KMC6501,
						SUM(RVS640_ALL)  AS RVS640,
						SUM(AJ1800F_ALL)  AS AJ1800F,
						SUM(MVJ1624_ALL)  AS MVJ1624,
						SUM(KMC8000_ALL) AS KMC8000,
						SUM(KMC1085_ALL) AS KMC1085,
						SUM(KMC6501_ALL) + SUM(KMC8000_ALL) + SUM(KMC1085_ALL) AS KMCNETT,
						SUM(HPL375_ALL) AS HPL375,
						SUM(REV_FLJ320P_ALL) AS REV_FLJ320P,
						SUM(REV_KMC6501_ALL) AS REV_KMC6501,
						SUM(REV_RVS640_ALL)  AS REV_RVS640,
						SUM(REV_AJ1800F_ALL) AS REV_AJ1800F,
						SUM(REV_MVJ1624_ALL) AS REV_MVJ1624,
						SUM(REV_KMC6501_ALL) + SUM(REV_KMC8000_ALL) + SUM(REV_KMC1085_ALL) AS REV_KMCNETT,
						SUM(REV_HPL375_ALL) AS REV_HPL375
					FROM CDW.PRN_DIG_DSH_BRD
					WHERE DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) AND CURRENT_DATE 
					GROUP BY DATE_FORMAT(DTE,'%Y'),DATE_FORMAT(DTE,'%c')*1,DATE_FORMAT(DTE,'%b')";
            $starttime=microtime(true);
            $result=mysql_query($query);
            $endtime= microtime(true);
            $duration=$endtime-$starttime;
			
			$monthlyPrcFLJ320P='[';
			$monthlyPrcKMC6501='[';
			$monthlyPrcRVS640='[';
			$monthlyPrcAJ1800F='[';
			$monthlyPrcMVJ1624='[';
			$monthlyPrcKMCNETT='[';
			$monthlyPrcHPL375='[';
				
			$months='[';
			while($row=mysql_fetch_array($result)){
				
				if($row['FLJ320P']==0){$monthlyPrcFLJ320P.="0,";}else{$monthlyPrcFLJ320P.=$row['REV_FLJ320P']/$row['FLJ320P'].",";}
				if($row['KMC6501']==0){$monthlyPrcKMC6501.="0,";}else{$monthlyPrcKMC6501.=$row['REV_KMC6501']/$row['KMC6501'].",";}
				if($row['RVS640']==0){$monthlyPrcRVS640.="0,";}else{$monthlyPrcRVS640.=$row['REV_RVS640']/$row['RVS640'].",";}
				if($row['AJ1800F']==0){$monthlyPrcAJ1800F.="0,";}else{$monthlyPrcAJ1800F.=$row['REV_AJ1800F']/$row['AJ1800F'].",";}
				if($row['MVJ1624']==0){$monthlyPrcMVJ1624.="0,";}else{$monthlyPrcMVJ1624.=$row['REV_MVJ1624']/$row['MVJ1624'].",";}
				if($row['KMCNETT']==0){$monthlyPrcKMCNETT.="0,";}else{$monthlyPrcKMCNETT.=$row['REV_KMCNETT']/$row['KMCNETT'].",";}
				if($row['HPL375']==0){$monthlyPrcHPL375.="0,";}else{$monthlyPrcHPL375.=$row['REV_HPL375']/$row['HPL375'].",";}
				
				$months.="'".$row['ORD_MONTH_NM']." ".$row['ORD_YEAR']."',";

			}
			$monthlyPrcMVJ1624=substr($monthlyPrcMVJ1624,0,strlen($monthlyPrcMVJ1624)-1);
			$monthlyPrcMVJ1624.=']';
			$monthlyPrcAJ1800F=substr($monthlyPrcAJ1800F,0,strlen($monthlyPrcAJ1800F)-1);
			$monthlyPrcAJ1800F.=']';
			$monthlyPrcRVS640=substr($monthlyPrcRVS640,0,strlen($monthlyPrcRVS640)-1);
			$monthlyPrcRVS640.=']';
			$monthlyPrcKMC6501=substr($monthlyPrcKMC6501,0,strlen($monthlyPrcKMC6501)-1);
			$monthlyPrcKMC6501.=']';
			$monthlyPrcFLJ320P=substr($monthlyPrcFLJ320P,0,strlen($monthlyPrcFLJ320P)-1);
			$monthlyPrcFLJ320P.=']';
			$monthlyPrcKMCNETT=substr($monthlyPrcKMCNETT,0,strlen($monthlyPrcKMCNETT)-1);
			$monthlyPrcKMCNETT.=']';
			$monthlyPrcHPL375=substr($monthlyPrcHPL375,0,strlen($monthlyPrcHPL375)-1);
			$monthlyPrcHPL375.=']';

			$months=substr($months,0,strlen($months)-1);
			$months.=']';
		}
?>		
		
<!--===============================|| Chart #1A3 [Monthly Contribution Index] ||===============================-->
<?php
		if($UpperSec<5){
			$query="";
			$query="SELECT 
				ONE.ORD_MONTH_NM, 
				ONE.ORD_MONTH, 
				ONE.ORD_YEAR, 
				(ONE.REV_FLJ320P-TWO.RTL_FLJ320P)/ONE.ORD_Q_FLJ320P AS DATA_FLJ320P,
				(ONE.REV_KMC6501-TWO.RTL_KMC6501)/ONE.ORD_Q_KMC6501 AS DATA_KMC6501,
				COALESCE((ONE.REV_KMC1085-TWO.RTL_KMC1085)/ONE.ORD_Q_KMC1085,0) AS DATA_KMC1085,
				COALESCE((ONE.REV_KMC8000-TWO.RTL_KMC8000)/ONE.ORD_Q_KMC8000,0) AS DATA_KMC8000,
				((ONE.REV_KMC6501+ONE.REV_KMC1085+ONE.REV_KMC8000)-(TWO.RTL_KMC6501+TWO.RTL_KMC1085+TWO.RTL_KMC8000))/(ONE.ORD_Q_KMC6501+ONE.ORD_Q_KMC1085+ONE.ORD_Q_KMC8000) AS DATA_ALL
			FROM (
				SELECT 
					DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM, 
					DATE_FORMAT(DTE,'%c') AS ORD_MONTH, 
					DATE_FORMAT(DTE,'%Y') AS ORD_YEAR, 
					SUM(FLJ320P_ALL) AS ORD_Q_FLJ320P, 
					SUM(KMC6501_ALL) AS ORD_Q_KMC6501, 
					SUM(REV_FLJ320P_ALL) AS REV_FLJ320P, 
					SUM(REV_KMC6501_ALL) AS REV_KMC6501,
					SUM(KMC1085_ALL) AS ORD_Q_KMC1085, 
					SUM(KMC8000_ALL) AS ORD_Q_KMC8000, 
					SUM(REV_KMC1085_ALL) AS REV_KMC1085, 
					SUM(REV_KMC8000_ALL) AS REV_KMC8000
				FROM CDW.PRN_DIG_DSH_BRD 
				WHERE DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) 
				AND CURRENT_DATE GROUP BY DATE_FORMAT(DTE,'%Y'),DATE_FORMAT(DTE,'%c')*1,DATE_FORMAT(DTE,'%b')
			) ONE LEFT JOIN (
				SELECT 
				DATE_FORMAT(T1.CRT_TS,'%b') AS RTL_MONTH_NM, 
				DATE_FORMAT(T1.CRT_TS,'%c') AS RTL_MONTH, 
				DATE_FORMAT(T1.CRT_TS,'%Y') AS RTL_YEAR,
				T1.ORD_DET_NBR,
				T1.PRN_DIG_TYP,
				T1.CRT_TS,
				SUM(T1.A3) AS JUM_A3,
				SUM(T1.FLEX) AS JUM_FLEX,
				T5.INV_PRC,
				SUM(T1.A3*T5.INV_PRC) AS RTL_KMC6501,
				SUM(T1.FLEX*T5.INV_PRC) AS RTL_FLJ320P,
				SUM(T1.JUM_P*T5.INV_PRC) AS RTL_KMC1085,
				SUM(T1.JUM_S*T5.INV_PRC) AS RTL_KMC8000
			FROM (
				SELECT * FROM (
					SELECT
						DET.ORD_DET_NBR,
						EQP.PRN_DIG_EQP,
						DET.PRN_DIG_TYP,
						DET.CRT_TS,
						DET.DEL_NBR,
						CASE WHEN EQP.PRN_DIG_EQP='KMC6501' THEN DET.ORD_Q ELSE 0 END AS A3,
						CASE WHEN EQP.PRN_DIG_EQP='FLJ320P' THEN DET.ORD_Q * (COALESCE(DET.PRN_LEN,1))*(COALESCE(DET.PRN_WID,1))  ELSE 0 END AS FLEX,
						CASE WHEN EQP.PRN_DIG_EQP='KMC1085' THEN DET.ORD_Q ELSE 0 END AS JUM_P,
						CASE WHEN EQP.PRN_DIG_EQP='KMC8000' THEN DET.ORD_Q ELSE 0 END AS JUM_S
					FROM CMP.PRN_DIG_ORD_DET DET LEFT JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
								LEFT JOIN CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
								LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR
					WHERE HED.BUY_CO_NBR NOT IN ($CoEx)
				) GET_ORD WHERE A3<>0 OR FLEX<>0 OR JUM_P<>0 OR JUM_S<>0
			) T1 LEFT JOIN (
				SELECT 
					PRN_DIG_TYP, 
					AVG(INV_PRC) AS INV_PRC
				FROM (
					SELECT 
						T2.INV_NBR,
						T2.PRN_DIG_TYP,
						COALESCE(T3.INV_PRC,0) AS INV_PRC
					FROM (
						SELECT
							INV.INV_NBR,
							TYP.PRN_DIG_TYP,
							TYP.PRN_DIG_TYP_PAR
						FROM CMP.PRN_DIG_TYP TYP LEFT JOIN RTL.INVENTORY INV ON TYP.PRN_DIG_TYP_PAR=INV.PRD_PRC_TYP 
						WHERE TYP.DEL_NBR=0 AND INV.DEL_NBR=0
					) T2 LEFT JOIN (
						SELECT * FROM (
							SELECT 
								DET.INV_NBR, 
								COALESCE(DET.INV_PRC,DET.FEE_MISC) AS INV_PRC, 
								DET.CRT_TS 
							FROM RTL.RTL_STK_DET DET 
							LEFT JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR
							WHERE DET.INV_NBR<>0 
								AND (DET.ORD_X IS NOT NULL OR DET.ORD_Y IS NOT NULL OR DET.ORD_Z IS NOT NULL) 
								AND HED.IVC_TYP = 'RC'
								ORDER BY DET.INV_NBR DESC, DET.ORD_DET_NBR DESC
						) TMP2 GROUP BY INV_NBR 
					) T3 ON T2.INV_NBR=T3.INV_NBR
					WHERE T3.INV_PRC IS NOT NULL AND T3.INV_PRC != 0
				) T4 GROUP BY PRN_DIG_TYP
			) T5 ON T1.PRN_DIG_TYP=T5.PRN_DIG_TYP WHERE T1.CRT_TS BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) AND CURRENT_DATE AND T1.DEL_NBR=0 
				GROUP BY DATE_FORMAT(T1.CRT_TS,'%Y'),DATE_FORMAT(T1.CRT_TS,'%c')*1,DATE_FORMAT(T1.CRT_TS,'%b')
			) TWO ON ONE.ORD_MONTH_NM=TWO.RTL_MONTH_NM AND ONE.ORD_YEAR=TWO.RTL_YEAR";
            $starttime=microtime(true);
            $result=mysql_query($query);
            $endtime= microtime(true);
            $duration=$endtime-$starttime;
            //echo $duration."<br>";
			//echo "BIASA A3+<br/>";
			//echo $query."<br/>";

			$monthlyPrcFLJ320P_T='[';
			$monthlyPrcKMC6501_T='[';
			$monthlyPrcKMC1085_T='[';
			$monthlyPrcKMC8000_T='[';
			$monthlyPrcKMCALL='[';
			$months='[';
			while($row=mysql_fetch_array($result)){
				$monthlyPrcFLJ320P_T.=$row['DATA_FLJ320P'].",";
				$monthlyPrcKMC6501_T.=$row['DATA_KMC6501'].",";
				$monthlyPrcKMC1085_T.=$row['DATA_KMC1085'].",";
				$monthlyPrcKMC8000_T.=$row['DATA_KMC8000'].",";
				$monthlyPrcKMCALL.=$row['DATA_ALL'].",";
				$months.="'".$row['ORD_MONTH_NM']." ".$row['ORD_YEAR']."',";
			}
			$monthlyPrcKMCALL=substr($monthlyPrcKMCALL,0,strlen($monthlyPrcKMCALL)-1);
			$monthlyPrcKMCALL.=']';
			$monthlyPrcKMC8000_T=substr($monthlyPrcKMC8000_T,0,strlen($monthlyPrcKMC8000_T)-1);
			$monthlyPrcKMC8000_T.=']';
			$monthlyPrcKMC1085_T=substr($monthlyPrcKMC1085_T,0,strlen($monthlyPrcKMC1085_T)-1);
			$monthlyPrcKMC1085_T.=']';
			$monthlyPrcKMC6501_T=substr($monthlyPrcKMC6501_T,0,strlen($monthlyPrcKMC6501_T)-1);
			$monthlyPrcKMC6501_T.=']';
			$monthlyPrcFLJ320P_T=substr($monthlyPrcFLJ320P_T,0,strlen($monthlyPrcFLJ320P_T)-1);
			$monthlyPrcFLJ320P_T.=']';

			$months=substr($months,0,strlen($months)-1);
			$months.=']';
		}
	?>
		
		<!--===============================|| Chart #2 [Payroll]             ||===============================-->
<?php		
		if($UpperSec<5){
			$query=" SELECT DATE_FORMAT(PAY_CONFIG_BEG_DTE,'%c') AS ORD_MONTH
						   ,DATE_FORMAT(PAY_CONFIG_BEG_DTE ,'%Y') AS ORD_YEAR
						   ,DATE_FORMAT(PAY_CONFIG_BEG_DTE ,'%b') AS ORD_MONTH_NM
						   ,COUNT(PAY.PRSN_NBR) AS NBR_PRSN
						   ,SUM(BASE_TOT)/1000000 AS BASE_TOT
						   ,SUM(ADD_TOT)/1000000 AS ADD_TOT
						   ,SUM(OT_TOT)/1000000 AS OT_TOT
						   ,SUM(MISC_TOT)/1000000 AS MISC_TOT
						   ,SUM(BON_MO_AMT)/1000000 AS BON_MO_AMT
						   ,SUM(TOT_PAY_ADD_AMT)/1000000 AS TOT_PAY_ADD_AMT
					FROM PAY.PAYROLL PAY LEFT OUTER JOIN
					CMP.PEOPLE PPL ON PAY.PRSN_NBR=PPL.PRSN_NBR
					WHERE PPL.CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_COMPANY WHERE CO_NBR_DEF = ".$CoNbrDef.") AND PAY_CONFIG_BEG_DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) AND CURRENT_DATE
					GROUP BY DATE_FORMAT(PAY_CONFIG_BEG_DTE,'%Y')
					,DATE_FORMAT(PAY_CONFIG_BEG_DTE,'%c')*1
					,DATE_FORMAT(PAY_CONFIG_BEG_DTE,'%b')";

			//echo $query;
			$result=mysql_query($query);
			$nbrEmpl='[';
			$baseTot='[';
			$addTot='[';
			$otTot='[';
			$miscTot='[';
			$bonMoAmt='[';
			$totPayAddAmt='[';
			$moPay='[';
			$extOvBase='[';
			$absMax=0;
			while($row=mysql_fetch_array($result)){
				$nbrEmpl.=$row['NBR_PRSN'].",";
				$baseTot.=$row['BASE_TOT'].",";
				$addTot.=$row['ADD_TOT'].",";
				$otTot.=$row['OT_TOT'].",";
				$miscTot.=$row['MISC_TOT'].",";
				$bonMoAmt.=$row['BON_MO_AMT'].",";
				$totPayAddAmt.=$row['TOT_PAY_ADD_AMT'].",";
				$moPay.="'".$row['ORD_MONTH_NM']." ".$row['ORD_YEAR']."',";
				$pay[]=$row['BASE_TOT']+$row['ADD_TOT']+$row['OT_TOT']+$row['MISC_TOT']+$row['BON_MO_AMT'];
                if(($row['BASE_TOT']+$row['ADD_TOT'])==0){
                    $extOvBase.="0,";
                }else{
                    $extOvBase.=($row['BON_MO_AMT']+$row['OT_TOT']+$row['MISC_TOT'])*100/($row['BASE_TOT']+$row['ADD_TOT']).",";
                }
				$curMax=$row['BON_MO_AMT']+$row['OT_TOT']+$row['MISC_TOT']+$row['BASE_TOT']+$row['ADD_TOT'];
				//echo $curMax." ";
				if($curMax>$absMax){
					$absMax=$curMax;
				}
			}
			$nbrEmpl=substr($nbrEmpl,0,strlen($nbrEmpl)-1);
			$nbrEmpl.=']';
			$baseTot=substr($baseTot,0,strlen($baseTot)-1);
			$baseTot.=']';
			$addTot=substr($addTot,0,strlen($addTot)-1);
			$addTot.=']';
			$otTot=substr($otTot,0,strlen($otTot)-1);
			$otTot.=']';
			$miscTot=substr($miscTot,0,strlen($miscTot)-1);
			$miscTot.=']';
			$bonMoAmt=substr($bonMoAmt,0,strlen($bonMoAmt)-1);
			$bonMoAmt.=']';
			$totPayAddAmt=substr($totPayAddAmt,0,strlen($totPayAddAmt)-1);
			$totPayAddAmt.=']';
			$extOvBase=substr($extOvBase,0,strlen($extOvBase)-1);
			$extOvBase.=']';
			$moPay=substr($moPay,0,strlen($moPay)-1);
			$moPay.=']';
			$absMax=(intval($absMax/10)+1)*10;
		}
?>		

		<!--===============================|| Chart #3 [Cost]                ||===============================-->
<?php		
		if($UpperSec<4){
		if($_GET['ALL']=='1'){
			$query="SELECT ORD_MONTH, ORD_YEAR, ORD_MONTH_NM, AVG(REVENUE) AS REV_AVG, SUM(REVENUE) AS REV_SUM, SUM(TOT_REM) AS TOT_REM,COUNT(ORD_DAY) AS DAY_NBR 
			          FROM (
					        SELECT DATE_FORMAT(DTE,'%e') AS ORD_DAY
						          ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
						          ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
								  ,DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM
			                      ,REV_ALL/1000000 AS REVENUE
			                      ,TOT_REM_ALL/1000000 AS TOT_REM
			                  FROM CDW.PRN_DIG_DSH_BRD
	                                 WHERE DTE BETWEEN (CURRENT_DATE - INTERVAL 13 MONTH) AND CURRENT_DATE) DAT GROUP BY ORD_YEAR, ORD_MONTH*1, ORD_MONTH_NM";
			}else{
			$query="SELECT ORD_MONTH, ORD_YEAR, ORD_MONTH_NM, AVG(REVENUE) AS REV_AVG, SUM(REVENUE) AS REV_SUM, SUM(TOT_REM) AS TOT_REM,COUNT(ORD_DAY) AS DAY_NBR 
			          FROM (
					        SELECT DATE_FORMAT(DTE,'%e') AS ORD_DAY
						          ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
						          ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
								  ,DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM
			                      ,REV/1000000 AS REVENUE
			                      ,TOT_REM/1000000 AS TOT_REM
			                  FROM CDW.PRN_DIG_DSH_BRD
	                                 WHERE DTE BETWEEN (CURRENT_DATE - INTERVAL 14 MONTH) AND CURRENT_DATE) DAT GROUP BY ORD_YEAR, ORD_MONTH*1, ORD_MONTH_NM";
			}
            $starttime=microtime(true);
            $result=mysql_query($query);
            $endtime= microtime(true);
            $duration=$endtime-$starttime;
            
			while($row=mysql_fetch_array($result)){
				$revP[]=$row['REV_SUM'];
			}
		}

		if($UpperSec<5){
			$query="SELECT 
					EXP_MONTH,
					EXP_YEAR,
					SUM(CASE WHEN EXP_TYP='EXP' THEN EXP/1000000 ELSE 0 END) AS EXP,
					SUM(CASE WHEN EXP_TYP='UTL' THEN EXP/1000000 ELSE 0 END) AS UTL,
					SUM(CASE WHEN EXP_TYP='IVC' THEN EXP/1000000 ELSE 0 END) AS IVC,
					SUM(CASE WHEN EXP_TYP='PAY' THEN EXP/1000000 ELSE 0 END) AS PAY,
					SUM(CASE WHEN EXP_TYP='CSH' THEN EXP/1000000 ELSE 0 END) AS CSH,
					(SUM(CASE WHEN EXP_TYP='CSH' THEN EXP/1000000 ELSE 0 END) - (SUM(CASE WHEN EXP_TYP='EXP' THEN EXP/1000000 ELSE 0 END) + SUM(CASE WHEN EXP_TYP='UTL' THEN EXP/1000000 ELSE 0 END) + SUM(CASE WHEN EXP_TYP='IVC' THEN EXP/1000000 ELSE 0 END))) / SUM(CASE WHEN EXP_TYP='PAY' THEN EXP/1000000 ELSE 0 END) AS ALL_ROI
				FROM (
					SELECT 
							MONTH(CRT_TS) AS EXP_MONTH,
							YEAR(CRT_TS) AS EXP_YEAR,
							SUM(COALESCE(TOT_SUB, 0)) AS EXP,
							'EXP' AS EXP_TYP 
					FROM CMP.EXPENSE 
					WHERE DATE(CRT_TS) BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) AND CURRENT_DATE 
					GROUP BY YEAR(CRT_TS), MONTH(CRT_TS)
					
					UNION ALL
					
					SELECT MO.EXP_MONTH,
						MO.EXP_YEAR,
						COALESCE(UTL.EXP,0) AS EXP,
						COALESCE(UTL.EXP_TYP,'UTL') AS EXP_TYP
						
					FROM
					(SELECT 
									MONTH(CRT_TS) AS EXP_MONTH,
									YEAR(CRT_TS) AS EXP_YEAR
								FROM RTL.RTL_STK_HEAD 
								WHERE DATE(CRT_TS) BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) AND CURRENT_DATE AND IVC_TYP='RC' AND RCV_CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_COMPANY WHERE CO_NBR_DEF = ".$CoNbrDef.") 
								GROUP BY YEAR(CRT_TS), MONTH(CRT_TS)
					) MO 
					LEFT JOIN 
					(
					SELECT 
						MONTH(CRT_TS) AS EXP_MONTH,
						YEAR(CRT_TS) AS EXP_YEAR,
						SUM(COALESCE(TOT_SUB, 0)) AS EXP,
						'UTL' AS EXP_TYP 
					FROM CMP.UTILITY 
					WHERE DATE(CRT_TS) BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) AND CURRENT_DATE 
					GROUP BY YEAR(CRT_TS), MONTH(CRT_TS)
					) UTL 
						ON MO.EXP_MONTH = UTL.EXP_MONTH 
							AND MO.EXP_YEAR = UTL.EXP_YEAR
					
					UNION ALL
			
					SELECT 
							MONTH(CRT_TS) AS EXP_MONTH,
							YEAR(CRT_TS) AS EXP_YEAR,
						SUM(COALESCE(TOT_AMT, 0)) AS EXP,
						'IVC' AS EXP_TYP 
					FROM RTL.RTL_STK_HEAD 
					WHERE DATE(CRT_TS) BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) AND CURRENT_DATE AND IVC_TYP='RC' AND RCV_CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_COMPANY WHERE CO_NBR_DEF = ".$CoNbrDef.") 
					GROUP BY YEAR(CRT_TS), MONTH(CRT_TS)
					
					UNION ALL
			
					SELECT 
						MONTH(CRT_TS) AS EXP_MONTH,
						YEAR(CRT_TS) AS EXP_YEAR,
						SUM(COALESCE(TOT_AMT, 0)) AS EXP,
						'XFR' AS EXP_TYP 
					FROM RTL.RTL_STK_HEAD 
					WHERE DATE(CRT_TS) BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) AND CURRENT_DATE AND IVC_TYP='XF' AND SHP_CO_NBR  IN (SELECT CO_NBR FROM NST.PARAM_COMPANY WHERE CO_NBR_DEF = ".$CoNbrDef.")
					GROUP BY YEAR(CRT_TS), MONTH(CRT_TS)
					
					UNION ALL
					
					SELECT 
						MONTH(PYMT_DTE) AS EXP_MONTH,
						YEAR(PYMT_DTE) AS EXP_YEAR,
						SUM(COALESCE(PAY.BASE_TOT,0) + COALESCE(PAY.ADD_TOT,0) + COALESCE(PAY.OT_TOT,0) + COALESCE(PAY.MISC_TOT,0) + COALESCE(PAY.BON_MO_AMT,0)) AS EXP,
						'PAY' AS EXP_TYP
					FROM PAY.PAYROLL PAY 
						LEFT OUTER JOIN CMP.PEOPLE PPL ON PAY.PRSN_NBR=PPL.PRSN_NBR
					WHERE PPL.CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_COMPANY WHERE CO_NBR_DEF = ".$CoNbrDef.")
						AND PYMT_DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) AND CURRENT_DATE
					GROUP BY YEAR(PYMT_DTE), MONTH(PYMT_DTE)
					
					UNION ALL

					SELECT 
						MONTH(DTE) AS EXP_MONTH,
						YEAR(DTE) AS EXP_YEAR,
						SUM(REV) AS EXP,
						'CSH' AS EXP_TYP
					FROM CDW.PRN_DIG_DSH_BRD
					WHERE DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) AND CURRENT_DATE
					GROUP BY YEAR(DTE), MONTH(DTE)
					) EXPENSE
				GROUP BY EXPENSE.EXP_YEAR, EXPENSE.EXP_MONTH
				ORDER BY EXPENSE.EXP_YEAR ASC, EXPENSE.EXP_MONTH ASC";
				//echo "<pre>".$query;
			$result=mysql_query($query);
			$monthlyExp='[';
			$monthlyUtl='[';
			$monthlyIvc='[';
			//$roi='[';
			while($row=mysql_fetch_array($result)){
				$monthlyExp.=$row['EXP'].",";
				$monthlyUtl.=$row['UTL'].",";
				$monthlyIvc.=$row['IVC'].",";
				$totroi = substr($row['ALL_ROI'],0,4);
				
				//$roi.=$totroi.",";
				$cost[]=$row['EXP']+$row['UTL']+$row['IVC'];
				$payr[]=$row['PAY'];
				//$months.="'".$row['ORD_MONTH_NM']." ".$row['ORD_YEAR']."',";
				//echo $i." ".($row['EXP']+$row['UTL']+$row['IVC'])." ";
				//$i++;
			}
			$monthlyExp=substr($monthlyExp,0,strlen($monthlyExp)-1);
			$monthlyExp.=']';
			$monthlyUtl=substr($monthlyUtl,0,strlen($monthlyUtl)-1);
			$monthlyUtl.=']';
			$monthlyIvc=substr($monthlyIvc,0,strlen($monthlyIvc)-1);
			$monthlyIvc.=']';
			//$roi=substr($roi,0,strlen($roi)-1);
			//$roi.=']';
			$OR='[';
			for($mo=0; $mo<=count($revP)-1; $mo++){
				//echo $mo." ".$revP[$mo]." ".$revR[$mo]." ".$pay[$mo]." ".$cost[$mo]." ";
				if(($revP[$mo]+$revR[$mo])==0){$revPR=1;}else{$revPR=$revP[$mo]+$revR[$mo];}
				$OR.=(($pay[$mo]+$cost[$mo])/($revPR)/10000).",";
			}
			$OR=substr($OR,0,strlen($OR)-1);
			$OR.=']';
			
			$roi='[';
			for($mo=0; $mo<=count($revP)-1; $mo++){
				if($revP[$mo]==0){$revPR=1;}else{$revPR=$revP[$mo];}
				$roi.=@(((($revPR - $cost[$mo])/$payr[$mo])/10000)/3).",";
			}
			$roi=substr($roi,0,strlen($roi)-1);
			$roi.=']';			
		}
    ?>

	<!-- Chart #A3+ -->
	<?php
	if($UpperSec<5){
		if($_GET['ALL']=='1'){
            $query="SELECT 
						DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM,
						DATE_FORMAT(DTE,'%c') AS ORD_MONTH,
						DATE_FORMAT(DTE,'%Y') AS ORD_YEAR,
						SUM(KMC6501_ALL) AS KMC6501,
						SUM(KMC8000_ALL) AS KMC8000,
						SUM(KMC1085_ALL) AS KMC1085
					FROM 	CDW.PRN_DIG_DSH_BRD
					WHERE 	DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) 
							AND CURRENT_DATE 
					GROUP BY DATE_FORMAT(DTE,'%Y'),DATE_FORMAT(DTE,'%c')*1,DATE_FORMAT(DTE,'%b')";
		}else{
			$query="SELECT 
						DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM,
						DATE_FORMAT(DTE,'%c') AS ORD_MONTH,
						DATE_FORMAT(DTE,'%Y') AS ORD_YEAR,
						SUM(KMC6501) AS KMC6501,
						SUM(KMC8000) AS KMC8000,
						SUM(KMC1085) AS KMC1085
					FROM 	CDW.PRN_DIG_DSH_BRD
					WHERE 	DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) 
							AND CURRENT_DATE 
					GROUP BY DATE_FORMAT(DTE,'%Y'),DATE_FORMAT(DTE,'%c')*1,DATE_FORMAT(DTE,'%b')";
		}
            $starttime=microtime(true);
            $result=mysql_query($query);
            $endtime= microtime(true);
            $duration=$endtime-$starttime;
			
            //echo $duration."<br>";
            //echo $query;
			$monthlyProdKMC6501_a3='[';
			$monthlyProdKMC8000_a3='[';
			$monthlyProdKMC1085_a3='[';
			
			$months='[';
			while($row=mysql_fetch_array($result)){
				$monthlyProdKMC6501_a3.=$row['KMC6501'].",";
				$monthlyProdKMC8000_a3.=$row['KMC8000'].",";
				$monthlyProdKMC1085_a3.=$row['KMC1085'].",";
				
				$months.="'".$row['ORD_MONTH_NM']." ".$row['ORD_YEAR']."',";

			}
						
			$monthlyProdKMC6501_a3=substr($monthlyProdKMC6501_a3,0,strlen($monthlyProdKMC6501_a3)-1);
			$monthlyProdKMC6501_a3.=']';
			$monthlyProdKMC8000_a3=substr($monthlyProdKMC8000_a3,0,strlen($monthlyProdKMC8000_a3)-1);
			$monthlyProdKMC8000_a3.=']';
			$monthlyProdKMC1085_a3=substr($monthlyProdKMC1085_a3,0,strlen($monthlyProdKMC1085_a3)-1);
			$monthlyProdKMC1085_a3.=']';
					
			$months=substr($months,0,strlen($months)-1);
			$months.=']';
	}
	?>
	
			<!--===============================|| Chart #4 [Digital Printing Year Revenue] ||===============================-->
<?php
		if($UpperSec<4){
		if($_GET['ALL']=='1'){
			$query="SELECT ORD_YEAR, AVG(REVENUE) AS REV_AVG, SUM(REVENUE) AS REV_SUM, SUM(TOT_REM) AS TOT_REM,COUNT(ORD_DAY) AS DAY_NBR 
			          FROM (
					        SELECT DATE_FORMAT(DTE,'%e') AS ORD_DAY
						          ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
						          ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
								  ,DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM
			                      ,REV_ALL/1000000 AS REVENUE
			                      ,TOT_REM_ALL/1000000 AS TOT_REM
			                  FROM CDW.PRN_DIG_DSH_BRD
	                                 WHERE DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 9 YEAR) AND CURRENT_DATE) DAT GROUP BY ORD_YEAR";
			}else{
			$query="SELECT ORD_YEAR, AVG(REVENUE) AS REV_AVG, SUM(REVENUE) AS REV_SUM, SUM(TOT_REM) AS TOT_REM,COUNT(ORD_DAY) AS DAY_NBR 
			          FROM (
					        SELECT DATE_FORMAT(DTE,'%e') AS ORD_DAY
						          ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
						          ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
								  ,DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM
			                      ,REV/1000000 AS REVENUE
			                      ,TOT_REM/1000000 AS TOT_REM
			                  FROM CDW.PRN_DIG_DSH_BRD
	                                 WHERE DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 10 YEAR) AND CURRENT_DATE) DAT GROUP BY ORD_YEAR";
			}
            $starttime=microtime(true);
            $result=mysql_query($query);
            $endtime= microtime(true);
            $duration=$endtime-$starttime;
            //echo $duration."<br>";
            //echo $query;
			$yearAvg='[';
			$yearTot='[';
			$yearRem='[';
			$yearEstTot='[';
			$years='[';
			while($row=mysql_fetch_array($result)){
				$yearAvg.=$row['REV_AVG'].",";
				$yearTot.=$row['REV_SUM'].",";
				$revP[]=$row['REV_SUM'];
				$yearRem.=$row['TOT_REM'].",";
				$years.=$row['ORD_YEAR'].",";
				$yearEstTot.="0,";
				$lastAvg=$row['REV_AVG'];
				$lastYear=$row['ORD_YEAR'];
				$lastTot=$row['REV_SUM'];
			}
			$yearAvg=substr($yearAvg,0,strlen($yearAvg)-1);
			$yearAvg.=']';
			$yearTot=substr($yearTot,0,strlen($yearTot)-1);
			$yearTot.=']';
			$yearRem=substr($yearRem,0,strlen($yearRem)-1);
			$yearRem.=']';
			$years=substr($years,0,strlen($years)-1);
			$years.=']';

            // Calculate number of days in each month for a given year.
			function getDays($year){
			    $num_of_days = array();
			    $total_month = 12;
			/*
			    if($year == date('Y'))
			        $total_month = date('m');
			    else
			        $total_month = 12;
			*/
			    for($m=1; $m<=$total_month; $m++){
			        $num_of_days[$m] = cal_days_in_month(CAL_GREGORIAN, $m, $year);
			    }

			    return $num_of_days;
			}
			$cal_day_in_year = array_sum(getDays($lastYear));
			$estTot=$cal_day_in_year*$lastAvg-$lastTot;
			$yearEstTot=substr($yearEstTot,0,strlen($yearEstTot)-2);
			$yearEstTot.=$estTot.']';
			
			//echo $yearEstTot;
            $query="SELECT YEAR(ORD_DTE) AS ORD_YEAR,SUM(TOT_AMT/1000000) AS TOT_AMT, SUM(TOT_REM/1000000) AS TOT_REM FROM RTL.RTL_STK_HEAD WHERE (ORD_DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) AND CURRENT_DATE) AND DEL_F=0 AND IVC_TYP='RC' GROUP BY YEAR(ORD_DTE)";
			$result=mysql_query($query);
            //echo $query;
			$yearPyb='[';
			while($row=mysql_fetch_array($result)){
				$yearPyb.=$row['TOT_REM'].",";
            }
			$yearPyb=substr($yearPyb,0,strlen($yearPyb)-1);
			$yearPyb.=']';
            //echo $yearPyb;
		} 
?>
 			<!--===============================|| Chart #5 [Goods Year Revenue] ||===============================-->
<?php 
		if($UpperSec<4){
			if($_GET['ALL']=='1'){
				$query="SELECT ORD_YEAR, AVG(REVENUE) AS REV_AVG, SUM(REVENUE) AS REV_SUM, SUM(TOT_REM) AS TOT_REM,COUNT(ORD_DAY) AS DAY_NBR 
				          FROM (
						        SELECT DATE_FORMAT(DTE,'%e') AS ORD_DAY
							          ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
							          ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
									  ,DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM
				                      ,(REV_RETAIL+REV_ORD_ALL)/1000000 AS REVENUE
				                      ,TOT_REM_ORD_ALL/1000000 AS TOT_REM
				                  FROM CDW.PRN_DIG_DSH_BRD
		                                 WHERE DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 9 YEAR) AND CURRENT_DATE) DAT GROUP BY ORD_YEAR";
			}else{
				$query="SELECT ORD_YEAR, AVG(REVENUE) AS REV_AVG, SUM(REVENUE) AS REV_SUM, SUM(TOT_REM) AS TOT_REM,COUNT(ORD_DAY) AS DAY_NBR 
				          FROM (
						        SELECT DATE_FORMAT(DTE,'%e') AS ORD_DAY
							          ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
							          ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
									  ,DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM
				                      ,(REV_RETAIL + REV_ORD)/1000000 AS REVENUE
				                      ,TOT_REM_ORD/1000000 AS TOT_REM
				                  FROM CDW.PRN_DIG_DSH_BRD
		                                 WHERE DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 10 YEAR) AND CURRENT_DATE) DAT GROUP BY ORD_YEAR";
			}
            $starttimeR= microtime(true);
            $result    = mysql_query($query);
            $endtimeR  = microtime(true);
            $durationR = $endtimeR-$starttimeR;
            
			$yearAvgR 	 ='[';
			$yearTotR 	 ='[';
			$yearRemR	 ='[';
			$yearEstTotR ='[';
			$yearsR      ='[';
			while($row=mysql_fetch_array($result)){
				$yearAvgR 	   .= $row['REV_AVG'].",";
				$yearTotR 	   .= $row['REV_SUM'].",";
				$yearRemR 	   .= $row['TOT_REM'].",";
				$yearsR 	   .= $row['ORD_YEAR'].",";
				$yearEstTotR   .= "0,";
				$lastAvgR 	 	= $row['REV_AVG'];
				$lastYearR 		= $row['ORD_YEAR'];
				$lastTotR 		= $row['REV_SUM'];
			}
			$yearAvgR = substr($yearAvgR,0,strlen($yearAvgR)-1);
			$yearAvgR.= ']';
			$yearTotR = substr($yearTotR,0,strlen($yearTotR)-1);
			$yearTotR.= ']';
			$yearRemR = substr($yearRemR,0,strlen($yearRemR)-1);
			$yearRemR.= ']';
			$yearsR   = substr($yearsR,0,strlen($yearsR)-1);
			$yearsR  .= ']';

			$cal_day_in_yearR = array_sum(getDays($lastYearR));
			$estTotR          = $cal_day_in_yearR*$lastAvgR-$lastTotR;
			$yearEstTotR      = substr($yearEstTotR,0,strlen($yearEstTotR)-2);
			$yearEstTotR     .= $estTotR.']';
		}
?>  
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	
	<!-- 1. Add these JavaScript inclusions in the head of your page -->
    <script type="text/javascript" src="framework/slider/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="framework/charts3/js/highcharts.js"></script>
    <script type="text/javascript" src="framework/charts3/js/highcharts-more.js"></script>
	
	<style type="text/css">
		table {
			/* font-family: 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, sans-serif; */
			font-size:10pt;
		}
		.spiffy{
			display:block;
		}
		.spiffy *{
			display:block;
			height:1px;
			overflow:hidden;
			background:#eeeeee;
		}
		.spiffy1{
			border-right:1px solid #f7f7f7;
			padding-right:1px;
			margin-right:3px;
			border-left:1px solid #f7f7f7;
			padding-left:1px;
			margin-left:3px;
			background:#f2f2f2;
		}
		.spiffy2{
			border-right:1px solid #fdfdfd;
			border-left:1px solid #fdfdfd;
			padding:0px 1px;
			background:#f1f1f1;
			margin:0px 1px;
		}
		.spiffy3{
			border-right:1px solid #f1f1f1;
			border-left:1px solid #f1f1f1;
			margin:0px 1px;
		}
		.spiffy4{
			border-right:1px solid #f7f7f7;
			border-left:1px solid #f7f7f7;
		}
		.spiffy5{
			border-right:1px solid #f2f2f2;
			border-left:1px solid #f2f2f2;
		}
		.spiffy_content{
			padding:0px 5px;
			background:#eeeeee;
			text-align:center;
		}
	</style>
	
	<!-- Add the JavaScript to initialize the chart on document ready -->
    <script type="text/javascript">
	var chart3A3;
        var chart1;
        var chart1A3;		
        var chart2;
        var chart3;
		var chart4;
		var chart5;
	
        
		$(document).ready(function() {
            
            Highcharts.setOptions({
                colors: [{linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#54b6ff'],[1, '#1169d8']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#4edd19'],[1, '#009c21']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#FED75C'],[1, '#F9CB1D']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#fd630a'],[1, '#ea1212']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#ab2e96'],[1, '#500a85']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#ed8f1c'],[1, '#a63d00']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#0ace80'],[1, '#008391']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#d2d2d2'],[1, '#b6b6b6']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#747474'],[1, '#242424']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#7d7d7d'],[1, '#303030']]},
                         '#2c83de','#32c028','#F9CB1D','#ea1212','#822694','#cd7115','#08ad90','#b6b6b6','#242424','#575757'],
                chart: {
                    style: {
                        fontFamily: 'San Francisco Display'
                    }
                },
                credits: {
                    enabled: false
                }
            });
			
			<!--===============================|| Chart #3A3 [Monthly A3+ Production Output] ||===============================-->

	chart3A3 = new Highcharts.Chart({
				chart: {
					renderTo: 'monthlyA3+Prod',
					defaultSeriesType: 'column',
				},
				title: {
					text: 'Monthly A3+ Production Output',
				},
				subtitle: {
					text: 'By Equipment',
				},
				xAxis: {
					categories: <?php echo $months; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'A3+ Full Service/R2S/R2P',
						style: {
							color: '#666666'
						}
					}
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y, 0);
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				plotOptions: {
			        series: {
						pointPadding: 0.06,
						borderWidth: 0,
			            groupPadding: 0.12,
						shadow: false
			        }
			    },
				series: [{
					name: 'A3+ Full Service',
					data: <?php echo $monthlyProdKMC6501_a3; ?>,
					color: Highcharts.getOptions().colors[7],
					
				},{
					name: 'R2S',
					data: <?php echo $monthlyProdKMC8000_a3; ?>,
					color: Highcharts.getOptions().colors[5],
				},{
					name: 'R2P',
					data: <?php echo $monthlyProdKMC1085_a3; ?>,
					color: Highcharts.getOptions().colors[6],
				}]
			});

			<!--===============================|| Chart #1 [Monthly Price Index] ||===============================-->
			chart1 = new Highcharts.Chart({
				chart: {
					renderTo: 'monthlyPrc',
					defaultSeriesType: 'spline',
				},
				title: {
					text: 'Monthly Price Index',
				},
				subtitle: {
					text: 'By Type',
				},
				xAxis: {
					categories: <?php echo $months; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Flex / Fabric / Indoor',
						style: {
							color: '#666666'
						}
					},
				},{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'A3+',
						style: {
							color: '#666666'
						}
					},
					opposite: true
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y, 0);
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				plotOptions: {
					series: {
						shadow: false,
						marker: {
                    		enabled: true
                		},
					}
				},
				series: [{
					name: 'Flex',
					data: <?php echo $monthlyPrcFLJ320P; ?>,
					color: Highcharts.getOptions().colors[10],
				},{
					name: 'A3+',
					data: <?php echo $monthlyPrcKMCNETT; ?>,
					yAxis: 1,
					color: Highcharts.getOptions().colors[11],
				},{
					name: 'Indoor',
					data: <?php echo $monthlyPrcRVS640; ?>,
					color: Highcharts.getOptions().colors[12],
				},{
					name: 'Direct Fabric',
					data: <?php echo $monthlyPrcAJ1800F; ?>,
					color: Highcharts.getOptions().colors[13],
				},{
					name: 'Heat Transfer',
					data: <?php echo $monthlyPrcMVJ1624; ?>,
					color: Highcharts.getOptions().colors[14],
				},{
					name: 'Latex',
					data: <?php echo $monthlyPrcHPL375; ?>,
					color: Highcharts.getOptions().colors[19],
				}]
			});
		
<!--===============================|| Chart #1A3 [Monthly Contribution Index] ||===============================-->
			chart1A3 = new Highcharts.Chart({
				chart: {
					renderTo: 'monthlyPrcContribution',
					defaultSeriesType: 'spline',
				},
				title: {
					text: 'Monthly Contribution Index',
				},
				subtitle: {
					text: 'By Type',
				},
				xAxis: {
					categories: <?php echo $months; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Flex',
						style: {
							color: '#666666'
						}
					},
					min:0
				},{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'A3+/A3+ R2S/A3+ R2P/A3+ All',
						style: {
							color: '#666666'
						}
					},
					min:0,
					opposite: true
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y, 0);
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				plotOptions: {
			        series: {
						shadow: false
			        }
			    },
				series: [{
					name: 'Flex',
					data: <?php echo $monthlyPrcFLJ320P_T; ?>,
					color: Highcharts.getOptions().colors[10],
				},{
					name: 'A3+ All',
					data: <?php echo $monthlyPrcKMCALL; ?>,
					yAxis: 1,
					color: Highcharts.getOptions().colors[11],
				},{
					name: 'A3+ R2S',
					data: <?php echo $monthlyPrcKMC8000_T; ?>,
					color: '#9142ab',
					yAxis: 1,
					color: Highcharts.getOptions().colors[15],
				},{
					name: 'A3+ R2P',
					data: <?php echo $monthlyPrcKMC1085_T; ?>,
					color: '#abb2be',
					yAxis: 1,
					color: Highcharts.getOptions().colors[16],
				},{
					name: 'A3+ Full Service',
					data: <?php echo $monthlyPrcKMC6501_T; ?>,
					yAxis: 1,
					color: Highcharts.getOptions().colors[17],
				}]
			});
			
			
			<!--===============================|| Chart #2 [Payroll]             ||===============================-->
			chart2 = new Highcharts.Chart({
				chart: {
					renderTo: 'payroll',
					defaultSeriesType: 'column'
				},
				title: {
					text: 'Payroll',
				},
				subtitle: {
					text: 'Monthly Data',
				},
				xAxis: {
					categories: <?php echo $moPay; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Number of Employee',
						style: {
							color: '#666666'
						}
					},
					min:0,
					max:<?php echo $absMax; ?>
					
				},{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Pay (millions)',
						style: {
							color: '#666666'
						}
					},
					opposite: true,
					min:0,
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ (this.series.name.substr(-3) == 'Pay' ? Highcharts.numberFormat(this.y*1000000, 0):Highcharts.numberFormat(this.y, 0));
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				plotOptions: {
					column: {
						stacking: 'normal',
						borderWidth: 0,
						shadow: false
                	},
			        series: {
						shadow: false
			        }
            	},
				series: [{
					name: 'Base Pay',
					yAxis:1,
					data: <?php echo $baseTot; ?>,
				},{
					name: 'Additional Pay',
					yAxis:1,
					data: <?php echo $addTot; ?>,
				},{
					name: 'Miscellaneous Pay',
					yAxis:1,
					data: <?php echo $miscTot; ?>
				},{
					name: 'Overtime Pay',
					yAxis:1,
					data: <?php echo $otTot; ?>
				},{
					name: 'Number of Employee',
					type: 'line',
					data: <?php echo $nbrEmpl; ?>,
					color: Highcharts.getOptions().colors[15],
				},{
					name: 'Extra over Base',
					type: 'line',
					data: <?php echo $extOvBase; ?>,
					color: Highcharts.getOptions().colors[18],
				},{
					name: 'Bonus Pay',
					yAxis:1,
					data: <?php echo $bonMoAmt; ?>
				},{
					name: 'Extra Pay',
					yAxis:1,
					data: <?php echo $totPayAddAmt; ?>
				}]
			});
		
			<!--===============================|| Chart #3 [Cost]                ||===============================-->
			chart3 = new Highcharts.Chart({
				chart: {
					renderTo: 'cost',
					defaultSeriesType: 'column'
				},
				title: {
					text: 'Cost',
				},
				subtitle: {
					text: 'Monthly Data',
				},
				xAxis: {
					categories: <?php echo $months; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value*1000000, 0);
							console.log(this.value);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Operating Ratio',
						style: {
							color: '#666666'
						}
					},
					min:0
					
				},{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Cost (millions)',
						style: {
							color: '#666666'
						}
					},
					opposite: true,
					min:0,
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y*1000000, 0);
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				plotOptions: {
					column: {
						stacking: 'normal',
						borderWidth: 0,
						shadow: false
                	},
			        series: {
						shadow: false
			        }
            	},
				series: [{
					name: 'Daily Expenses',
					yAxis:1,
					data: <?php echo $monthlyExp; ?>,
				},{
					name: 'Capital Expenses',
					yAxis:1,
					data: <?php echo $monthlyUtl; ?>,
				},{
					name: 'Raw Material and Goods',
					yAxis:1,
					data: <?php echo $monthlyIvc; ?>,
				},{
					name: 'Operating Ratio',	
					type: 'line',
					data: <?php echo $OR; ?>,
					color: Highcharts.getOptions().colors[13],
				}]
			});
			
			<!--===============================|| Chart #4 [Digital Printing Year Revenue] ||===============================-->
			chart4 = new Highcharts.Chart({
				chart: {
					renderTo: 'yearRev',
					defaultSeriesType: 'column',
				},
				title: {
					text: 'Digital Printing Year Revenue',
				},
				subtitle: {
					text: 'Average per Working Day',
				},
				xAxis: {
					categories: <?php echo $years; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Average Revenue (millions)',
						style: {
							color: '#666666'
						}
					},
					min:0,
				},{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Total Revenue (millions)',
						style: {
							color: '#666666'
						}
					},
					opposite: true,
					min:0,
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y*1000000, 0);
					}
				},
				plotOptions: {
					series: {
						pointPadding: 0.075,
						borderWidth: 0,
						groupPadding: 0.15,
						shadow: false
					},
					column: {
						stacking: 'normal'
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				series: [{
					name: 'Average Revenue',
					data: <?php echo $yearAvg; ?>,
					stack:'avg'
				},{
					name: 'Estimated Revenue',
					yAxis:1,
					data: <?php echo $yearEstTot; ?>,
					stack:'tot',
					color:'#656d78'
				},{
					name: 'Total Revenue',
					yAxis:1,
					data: <?php echo $yearTot; ?>,
					stack:'tot'
				}]
			});
		
			<!--===============================|| Chart #5 [Goods Year Revenue] ||===============================-->
			chart5 = new Highcharts.Chart({
				chart: {
					renderTo: 'GoodyearRev',
					defaultSeriesType: 'column',
				},
				title: {
					text: 'Goods Year Revenue',
				},
				subtitle: {
					text: 'Average per Working Day',
				},
				xAxis: {
					categories: <?php echo $years; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Average Revenue (millions)',
						style: {
							color: '#666666'
						}
					},
					min:0,
				},{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Total Revenue (millions)',
						style: {
							color: '#666666'
						}
					},
					opposite: true,
					min:0,
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y*1000000, 0);
					}
				},
				plotOptions: {
					series: {
						pointPadding: 0.075,
						borderWidth: 0,
						groupPadding: 0.15,
						shadow: false
					},
					column: {
						stacking: 'normal'
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				series: [{
					name: 'Average Revenue',
					data: <?php echo $yearAvgR; ?>,
					stack:'avg'
				},{
					name: 'Estimated Revenue',
					yAxis:1,
					data: <?php echo $yearEstTotR; ?>,
					stack:'tot',
					color:'#656d78'
				},{
					name: 'Total Revenue',
					yAxis:1,
					data: <?php echo $yearTotR; ?>,
					stack:'tot'
				}]
			});
		});
	</script>
        	
	<style>
		body {
			color: #666666;
			text-shadow: 0 0px 1px rgba(0,0,0,0.15);
		}
	
		div.title {
			margin-top:10px;
			margin-left:10px;
			font-size:13pt;
			margin-bottom:6px;
			color:#000000;
		}
		
		div.text {
			margin-left:10px;		
		}
	</style>

</head>
<body>
	<?php
		$Security=getSecurity($_SESSION['userID'],"DigitalPrint");
		$UpperSec=getSecurity($_SESSION['userID'],"Executive");
	?>
	
	<div id="monthlyA3+Prod" style="width: 800px; height: 400px; margin: 0 auto; <?php if($UpperSec>=8){echo "display:none;";} ?>"></div>
	<div id="monthlyPrc" style="width: 800px; height: 400px; margin: 0 auto; <?php if($UpperSec>=5){echo "display:none;";} ?>"></div>
	<div id="monthlyPrcContribution" style="width: 800px; height: 400px; margin: 0 auto; <?php if($UpperSec>=5){echo "display:none;";} ?>"></div>
	<div id="payroll" style="width: 800px; height: 400px; margin: 0 auto; <?php if($UpperSec>=5){echo "display:none;";} ?>"></div>
	<div id="cost" style="width: 800px; height: 400px; margin: 0 auto; <?php if($UpperSec>=5){echo "display:none;";} ?>"></div>
	<div id="yearRev" style="width: 800px; height: 400px; margin: 0 auto; <?php if($UpperSec>=5){echo "display:none;";} ?>"></div>
	<div id="GoodyearRev" style="width: 800px; height: 400px; margin: 0 auto; <?php if($UpperSec>=5){echo "display:none;";} ?>"></div>
</body>
</html>
