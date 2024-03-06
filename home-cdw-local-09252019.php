<?php
include_once("framework/database/connect-local.php");
include_once("framework/functions/default.php");
include_once("framework/security/default.php");
ini_set('max_execution_time',7500);

$CoEx	= "SELECT CO_NBR FROM NST.PARAM_COMPANY";

//Datawarehousing processes
	$query="SELECT CURRENT_DATE,DATE(PRN_DIG_DSH_BRD) AS PRN_DIG_DSH_BRD FROM CDW.UPD_LAST";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$LastUpdate=$row['PRN_DIG_DSH_BRD'];
		
	if($row['CURRENT_DATE']!=$row['PRN_DIG_DSH_BRD']){
		//Immediately update the date so that no other process will run
		$query="UPDATE CDW.UPD_LAST SET PRN_DIG_DSH_BRD=CURRENT_TIMESTAMP";
		$result=mysql_query($query);
	
		$query_date	= "SELECT DATE(MIN(ORD_TS)) AS DEL_DTE FROM (
							SELECT 
								ORD_NBR, 
								ORD_TS, 
								UPD_TS 
							FROM CMP.PRN_DIG_ORD_HEAD 
							WHERE UPD_TS >= (NOW() - INTERVAL 1 MONTH) 
							ORDER BY ORD_TS
						) T1";
		$result_date= mysql_query($query_date);
		$row_date	= mysql_fetch_array($result_date);
		$DelDte		= $row_date['DEL_DTE'];
		
		$query		= "DELETE FROM CDW.PRN_DIG_DSH_BRD WHERE DTE >='".$DelDte."'";
		$result		= mysql_query($query);
		
		$query="SELECT COALESCE(MAX(DTE),'0000-00-00') AS DTE FROM CDW.PRN_DIG_DSH_BRD";	
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$begDate=$row['DTE'];

		//If no data found, add first date
		$query="INSERT INTO CDW.PRN_DIG_DSH_BRD (DTE,REV_ALL,TOT_REM_ALL,FLJ320P_ALL,KMC6501_ALL,RVS640_ALL,KMC8000_ALL,KMC1085_ALL,HPL375_ALL,ATX67_ALL,LABSVCS_ALL,REV_FLJ320P_ALL,REV_KMC6501_ALL,REV_RVS640_ALL,AJ1800F_ALL,REV_AJ1800F_ALL,MVJ1624_ALL,REV_MVJ1624_ALL,REV_KMC8000_ALL,REV_KMC1085_ALL,REV_HPL375_ALL,REV_ATX67_ALL,REV_LABSVCS_ALL) 
		SELECT DATE(ORD_TS),SUM(TOT_SUB)+COALESCE(SUM(TOT_SUB_ADD),0),0,
		SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS FLJ320P,
		SUM(CASE WHEN PRN_DIG_EQP='KMC6501' THEN ORD_Q ELSE 0 END) AS KMC6501,
		SUM(CASE WHEN PRN_DIG_EQP='RVS640' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS RVS640,
		SUM(CASE WHEN PRN_DIG_EQP='KMC8000' THEN ORD_Q ELSE 0 END) AS KMC8000,
		SUM(CASE WHEN PRN_DIG_EQP='KMC1085' THEN ORD_Q ELSE 0  END) AS KMC1085,
		SUM(CASE WHEN PRN_DIG_EQP='HPL375' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS HPL375,
		SUM(CASE WHEN PRN_DIG_EQP='ATX67' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS ATX67,
		SUM(CASE WHEN PRN_DIG_EQP='LABSVCS' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS LABSVCS,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' THEN TOT_SUB ELSE 0 END),0) AS REV_FLJ320P,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='KMC6501' THEN TOT_SUB ELSE 0 END),0) AS REV_KMC6501,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='RVS640' THEN TOT_SUB ELSE 0 END),0) AS REV_RVS640,
		SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' THEN ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1)) ELSE 0 END) AS AJ1800F,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' THEN TOT_SUB ELSE 0 END),0) AS REV_AJ1800F,
		SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' THEN ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1)) ELSE 0 END) AS MVJ1624,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' THEN TOT_SUB ELSE 0 END),0) AS REV_MVJ1624,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='KMC8000' THEN  TOT_SUB ELSE 0 END),0) AS REV_KMC8000,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='KMC1085' THEN  TOT_SUB ELSE 0 END),0) AS REV_KMC1085,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='HPL375' THEN TOT_SUB ELSE 0 END),0) AS REV_HPL375,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='ATX67' THEN TOT_SUB ELSE 0 END),0) AS REV_ATX67,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='LABSVCS' THEN TOT_SUB ELSE 0 END),0) AS REV_LABSVCS
		FROM CMP.PRN_DIG_ORD_HEAD HED 
		LEFT OUTER JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR 
		LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
		LEFT OUTER JOIN (SELECT ORD_DET_NBR,SUM(TOT_SUB) AS TOT_SUB_ADD FROM CMP.PRN_DIG_ORD_VAL_ADD
		GROUP BY ORD_NBR) VAL ON DET.ORD_DET_NBR=VAL.ORD_DET_NBR
		WHERE DATE(ORD_TS)='$begDate' AND HED.DEL_NBR=0 AND DET.DEL_NBR=0
		GROUP BY 1"; 
		//echo $query;
		$result=mysql_query($query);
		$query="UPDATE CDW.PRN_DIG_DSH_BRD SET TOT_REM_ALL=(SELECT SUM(TOT_REM) FROM CMP.PRN_DIG_ORD_HEAD WHERE DATE(ORD_TS)='$begDate' AND DEL_NBR=0) WHERE DTE='$begDate'";
		$result=mysql_query($query);
		//Local
		$query="UPDATE CDW.PRN_DIG_DSH_BRD DSB 
		LEFT OUTER JOIN (SELECT ORD_TS,SUM(TOT_SUB)+COALESCE(SUM(TOT_SUB_ADD),0) REV,
		SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS FLJ320P,
		SUM(CASE WHEN PRN_DIG_EQP='KMC6501' THEN ORD_Q ELSE 0 END) AS KMC6501,
		SUM(CASE WHEN PRN_DIG_EQP='RVS640' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS RVS640,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' THEN TOT_SUB ELSE 0 END),0) AS REV_FLJ320P,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='KMC6501' THEN TOT_SUB ELSE 0 END),0) AS REV_KMC6501,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='RVS640' THEN TOT_SUB ELSE 0 END),0) AS REV_RVS640,
		SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' THEN ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1)) ELSE 0 END) AS AJ1800F,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' THEN TOT_SUB ELSE 0 END),0) AS REV_AJ1800F,
		SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' THEN ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1)) ELSE 0 END) AS MVJ1624,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' THEN TOT_SUB ELSE 0 END),0) AS REV_MVJ1624
		FROM CMP.PRN_DIG_ORD_HEAD HED 
		LEFT OUTER JOIN	CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR 
		LEFT OUTER JOIN	CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
		LEFT OUTER JOIN (SELECT ORD_DET_NBR,SUM(TOT_SUB) AS TOT_SUB_ADD FROM CMP.PRN_DIG_ORD_VAL_ADD GROUP BY ORD_NBR) VAL ON DET.ORD_DET_NBR=VAL.ORD_DET_NBR
		WHERE (BUY_CO_NBR IS NULL OR BUY_CO_NBR NOT IN ($CoEx)) AND DATE(ORD_TS)='$begDate' AND HED.DEL_NBR=0 AND DET.DEL_NBR=0) ORD ON DATE(ORD.ORD_TS)='$begDate'
		SET DSB.REV=ORD.REV,DSB.FLJ320P=ORD.FLJ320P,DSB.KMC6501=ORD.KMC6501,DSB.RVS640=ORD.RVS640,
		DSB.REV_FLJ320P=ORD.REV_FLJ320P,DSB.REV_KMC6501=ORD.REV_KMC6501,DSB.REV_RVS640=ORD.REV_RVS640,DSB.AJ1800F=ORD.AJ1800F,
		DSB.REV_AJ1800F=ORD.REV_AJ1800F,DSB.MVJ1624=ORD.MVJ1624,
		DSB.REV_MVJ1624=ORD.REV_MVJ1624 WHERE DSB.DTE='$begDate' ";		
		
		//echo $query."<br /><br /><br />";
		
		mysql_query($query);
		
		$query="UPDATE CDW.PRN_DIG_DSH_BRD DSB 
		LEFT OUTER JOIN (SELECT ORD_TS,
		SUM(CASE WHEN PRN_DIG_EQP='KMC8000' THEN ORD_Q ELSE 0 END) AS KMC8000,
		SUM(CASE WHEN PRN_DIG_EQP='KMC1085' THEN ORD_Q ELSE 0 END) AS KMC1085,
		SUM(CASE WHEN PRN_DIG_EQP='HPL375' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS HPL375,
		SUM(CASE WHEN PRN_DIG_EQP='ATX67' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS ATX67,
		SUM(CASE WHEN PRN_DIG_EQP='LABSVCS' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS LABSVCS,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='KMC8000' THEN TOT_SUB  ELSE 0 END),0) AS REV_KMC8000,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='KMC1085' THEN TOT_SUB  ELSE 0 END),0) AS REV_KMC1085,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='HPL375' THEN TOT_SUB ELSE 0 END),0) AS REV_HPL375,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='ATX67' THEN TOT_SUB ELSE 0 END),0) AS REV_ATX67,
		COALESCE(SUM(CASE WHEN PRN_DIG_EQP='LABSVCS' THEN TOT_SUB ELSE 0 END),0) AS REV_LABSVCS
		FROM CMP.PRN_DIG_ORD_HEAD HED 
		LEFT OUTER JOIN	CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR 
		LEFT OUTER JOIN	CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
		LEFT OUTER JOIN (SELECT ORD_DET_NBR,SUM(TOT_SUB) AS TOT_SUB_ADD FROM CMP.PRN_DIG_ORD_VAL_ADD GROUP BY ORD_NBR) VAL ON DET.ORD_DET_NBR=VAL.ORD_DET_NBR
		WHERE (BUY_CO_NBR IS NULL OR BUY_CO_NBR NOT IN ($CoEx)) AND DATE(ORD_TS)='$begDate' AND HED.DEL_NBR=0 AND DET.DEL_NBR=0) 
		ORD ON DATE(ORD.ORD_TS)='$begDate'
		SET 
		DSB.KMC8000=ORD.KMC8000,
		DSB.KMC1085=ORD.KMC1085,
		DSB.HPL375=ORD.HPL375,
		DSB.ATX67=ORD.ATX67,
		DSB.LABSVCS=ORD.LABSVCS,
		DSB.REV_KMC8000=ORD.REV_KMC8000,
		DSB.REV_KMC1085=ORD.REV_KMC1085,
		DSB.REV_HPL375=ORD.REV_HPL375,
		DSB.REV_ATX67=ORD.REV_ATX67,
		DSB.REV_LABSVCS=ORD.REV_LABSVCS
		WHERE DSB.DTE='$begDate' ";		
		//	echo $query;
		mysql_query($query);
		
		$query="UPDATE CDW.PRN_DIG_DSH_BRD SET TOT_REM=(SELECT SUM(TOT_REM) FROM CMP.PRN_DIG_ORD_HEAD WHERE (BUY_CO_NBR IS NULL OR BUY_CO_NBR NOT IN ($CoEx)) AND DATE(ORD_TS)='$begDate' AND DEL_NBR=0) WHERE DTE='$begDate'";
		mysql_query($query);

		//retail by category
		$query="SELECT INV.CAT_NBR, SUM(CSH.TND_AMT) AS TND_AMT
			FROM RTL.CSH_REG CSH
				LEFT OUTER JOIN RTL.INVENTORY INV ON CSH.RTL_BRC=INV.INV_BCD
				LEFT OUTER JOIN RTL.RTL_ORD_PYMT ORD ON CSH.REG_NBR = ORD.VAL_NBR
			WHERE DATE(CSH.CRT_TS)='$begDate' AND RTL_BRC<>'' AND (`CSH_FLO_TYP` ='RT' OR (`CSH_FLO_TYP` ='FL' AND ORD.VAL_NBR<>''))
			GROUP BY INV.CAT_NBR";
		$result=mysql_query($query);
		$retail = 0;
		$retailCafe = 0;

		while ($row = mysql_fetch_array($result)) {
			if ($row['CAT_NBR'] == 9) {
				$retailCafe += $row['TND_AMT'];
			} else {
				$retail += $row['TND_AMT'];
			}
		}

		//retail
		$query="UPDATE CDW.PRN_DIG_DSH_BRD SET REV_RTL=$retail WHERE DTE='$begDate'";
		$result=mysql_query($query);

		//retail cafe
		$query="UPDATE CDW.PRN_DIG_DSH_BRD SET REV_CAFE=$retailCafe WHERE DTE='$begDate'";
		$result=mysql_query($query);

		//echo $query;
		//Iterate add data until yesterday's order
		$query="SELECT DATE_ADD(CURRENT_DATE,INTERVAL -1 DAY) AS DTE";
		//$query="SELECT MAX(DATE(ORD_TS)) AS DTE FROM CMP.PRN_DIG_ORD_HEAD";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$endDate=$row['DTE'];
		$days=round((strtotime($endDate)-strtotime($begDate))/86400);
		for($day=1; $day<=$days; $day++){
			$query="SELECT ORD_TS FROM CMP.PRN_DIG_ORD_HEAD WHERE DATE(ORD_TS)=DATE_ADD('$begDate',INTERVAL $day DAY)";
			$result=mysql_query($query);
			if(mysql_num_rows($result)==0){
				$query="INSERT INTO CDW.PRN_DIG_DSH_BRD (DTE,REV_ALL,TOT_REM_ALL,FLJ320P_ALL,KMC6501_ALL,RVS640_ALL,KMC8000_ALL,KMC1085_ALL,HPL375_ALL,ATX67_ALL,LABSVCS_ALL, REV_FLJ320P_ALL,REV_KMC6501_ALL,REV_RVS640_ALL,AJ1800F_ALL,REV_AJ1800F_ALL,MVJ1624_ALL,REV_MVJ1624_ALL,REV_KMC8000_ALL,REV_KMC1085_ALL,REV_HPL375_ALL,REV_ATX67_ALL,REV_LABSVCS_ALL) VALUES (DATE_ADD('$begDate',INTERVAL $day DAY),0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0)";
			}else{
				$query="INSERT INTO CDW.PRN_DIG_DSH_BRD (DTE,REV_ALL,TOT_REM_ALL,FLJ320P_ALL,KMC6501_ALL,RVS640_ALL,KMC8000_ALL,KMC1085_ALL,HPL375_ALL,ATX67_ALL,LABSVCS_ALL,REV_FLJ320P_ALL,REV_KMC6501_ALL,REV_RVS640_ALL,AJ1800F_ALL,REV_AJ1800F_ALL,MVJ1624_ALL,REV_MVJ1624_ALL,REV_KMC8000_ALL,REV_KMC1085_ALL,REV_HPL375_ALL,REV_ATX67_ALL,REV_LABSVCS_ALL) 
					SELECT DATE(ORD_TS),SUM(TOT_SUB)+COALESCE(SUM(TOT_SUB_ADD),0),0,
					SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS FLJ320P,
					SUM(CASE WHEN PRN_DIG_EQP='KMC6501' THEN ORD_Q ELSE 0 END) AS KMC6501,
					SUM(CASE WHEN PRN_DIG_EQP='RVS640' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS RVS640,
					SUM(CASE WHEN PRN_DIG_EQP='KMC8000' THEN ORD_Q ELSE 0 END) AS KMC8000,
					SUM(CASE WHEN PRN_DIG_EQP='KMC1085' THEN ORD_Q ELSE 0 END) AS KMC1085,
					SUM(CASE WHEN PRN_DIG_EQP='HPL375' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS HPL375,
					SUM(CASE WHEN PRN_DIG_EQP='ATX67' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS ATX67,
					SUM(CASE WHEN PRN_DIG_EQP='LABSVCS' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS LABSVCS,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' THEN TOT_SUB ELSE 0 END),0) AS REV_FLJ320P,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='KMC6501' THEN TOT_SUB ELSE 0 END),0) AS REV_KMC6501,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='RVS640' THEN TOT_SUB ELSE 0 END),0) AS REV_RVS640,
					SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS AJ1800F,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' THEN TOT_SUB ELSE 0 END),0) AS REV_AJ1800F,
					SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS MVJ1624,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' THEN TOT_SUB ELSE 0 END),0) AS REV_MVJ1624,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='KMC8000' THEN TOT_SUB ELSE 0 END),0) AS REV_KMC8000,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='KMC1085' THEN TOT_SUB ELSE 0 END),0) AS REV_KMC1085,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='HPL375' THEN TOT_SUB ELSE 0 END),0) AS REV_HPL375,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='ATX67' THEN TOT_SUB ELSE 0 END),0) AS REV_ATX67,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='LABSVCS' THEN TOT_SUB ELSE 0 END),0) AS REV_LABSVCS
					FROM CMP.PRN_DIG_ORD_HEAD HED LEFT OUTER JOIN
					CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR LEFT OUTER JOIN
					CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP LEFT OUTER JOIN
					(SELECT ORD_DET_NBR,SUM(TOT_SUB) AS TOT_SUB_ADD FROM CMP.PRN_DIG_ORD_VAL_ADD
					GROUP BY ORD_NBR) VAL ON DET.ORD_DET_NBR=VAL.ORD_DET_NBR
					WHERE DATE(ORD_TS)=DATE_ADD('$begDate',INTERVAL $day DAY) AND HED.DEL_NBR=0 AND DET.DEL_NBR=0
					GROUP BY 1"	;	
			}
			//echo $query."<br/>";
			$result=mysql_query($query);
			$query="UPDATE CDW.PRN_DIG_DSH_BRD SET TOT_REM_ALL=(SELECT SUM(TOT_REM) FROM CMP.PRN_DIG_ORD_HEAD WHERE DATE(ORD_TS)=DATE_ADD('$begDate',INTERVAL $day DAY) AND DEL_NBR=0) WHERE DTE=DATE_ADD('$begDate',INTERVAL $day DAY)";
			//echo $query."<br/>";
			$result=mysql_query($query);
			
			//Local
			$query="UPDATE CDW.PRN_DIG_DSH_BRD DSB 
					LEFT OUTER JOIN (SELECT ORD_TS,SUM(TOT_SUB)+COALESCE(SUM(TOT_SUB_ADD),0) REV,
					SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS FLJ320P,
					SUM(CASE WHEN PRN_DIG_EQP='KMC6501' THEN ORD_Q ELSE 0 END) AS KMC6501,
					SUM(CASE WHEN PRN_DIG_EQP='RVS640'  THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS RVS640,
					SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' THEN ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1)) ELSE 0 END) AS AJ1800F,
					SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' THEN ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1)) ELSE 0 END) AS MVJ1624,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' THEN TOT_SUB ELSE 0 END),0) AS REV_FLJ320P,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='KMC6501' THEN TOT_SUB ELSE 0 END),0) AS REV_KMC6501,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='RVS640'  THEN TOT_SUB ELSE 0 END),0) AS REV_RVS640,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' THEN TOT_SUB ELSE 0 END),0) AS REV_AJ1800F,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' THEN TOT_SUB ELSE 0 END),0) AS REV_MVJ1624
					FROM CMP.PRN_DIG_ORD_HEAD HED 
					LEFT OUTER JOIN	CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR 
					LEFT OUTER JOIN	CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
					LEFT OUTER JOIN (SELECT ORD_DET_NBR,SUM(TOT_SUB) AS TOT_SUB_ADD FROM CMP.PRN_DIG_ORD_VAL_ADD GROUP BY ORD_NBR) VAL ON DET.ORD_DET_NBR=VAL.ORD_DET_NBR
					WHERE (BUY_CO_NBR IS NULL OR BUY_CO_NBR NOT IN ($CoEx)) AND DATE(ORD_TS)=DATE_ADD('$begDate',INTERVAL $day DAY) AND HED.DEL_NBR=0 AND DET.DEL_NBR=0) ORD ON DATE(ORD.ORD_TS)=DATE_ADD('$begDate',INTERVAL $day DAY)
					SET DSB.REV=ORD.REV,
                    DSB.FLJ320P=ORD.FLJ320P,
                    DSB.KMC6501=ORD.KMC6501,
                    DSB.RVS640 =ORD.RVS640,
                    DSB.AJ1800F=ORD.AJ1800F,
                    DSB.MVJ1624=ORD.MVJ1624,
					DSB.REV_FLJ320P=ORD.REV_FLJ320P,
                    DSB.REV_KMC6501=ORD.REV_KMC6501,
                    DSB.REV_RVS640 =ORD.REV_RVS640,
					DSB.REV_AJ1800F=ORD.REV_AJ1800F,
					DSB.REV_MVJ1624=ORD.REV_MVJ1624 WHERE DSB.DTE=DATE_ADD('$begDate',INTERVAL $day DAY) "; 
			//echo $query."<br/>";
			mysql_query($query);
		
		$query="UPDATE CDW.PRN_DIG_DSH_BRD DSB 
					LEFT OUTER JOIN (SELECT ORD_TS,
					SUM(CASE WHEN PRN_DIG_EQP='KMC8000' THEN ORD_Q ELSE 0 END) AS KMC8000,
					SUM(CASE WHEN PRN_DIG_EQP='KMC1085' THEN ORD_Q ELSE 0 END) AS KMC1085,
					SUM(CASE WHEN PRN_DIG_EQP='HPL375' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS HPL375,
					SUM(CASE WHEN PRN_DIG_EQP='ATX67' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS ATX67,
					SUM(CASE WHEN PRN_DIG_EQP='LABSVCS' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS LABSVCS,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='KMC8000' THEN TOT_SUB ELSE 0 END),0) AS REV_KMC8000,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='KMC1085' THEN TOT_SUB ELSE 0 END),0) AS REV_KMC1085,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='HPL375' THEN TOT_SUB ELSE 0 END),0) AS REV_HPL375,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='ATX67' THEN TOT_SUB ELSE 0 END),0) AS REV_ATX67,
					COALESCE(SUM(CASE WHEN PRN_DIG_EQP='LABSVCS' THEN TOT_SUB ELSE 0 END),0) AS REV_LABSVCS
					FROM CMP.PRN_DIG_ORD_HEAD HED 
					LEFT OUTER JOIN	CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR 
					LEFT OUTER JOIN	CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
					LEFT OUTER JOIN (SELECT ORD_DET_NBR,SUM(TOT_SUB) AS TOT_SUB_ADD FROM CMP.PRN_DIG_ORD_VAL_ADD GROUP BY ORD_NBR) VAL ON DET.ORD_DET_NBR=VAL.ORD_DET_NBR
					WHERE (BUY_CO_NBR IS NULL OR BUY_CO_NBR NOT IN ($CoEx)) AND DATE(ORD_TS)=DATE_ADD('$begDate',INTERVAL $day DAY) AND HED.DEL_NBR=0 AND DET.DEL_NBR=0) ORD ON DATE(ORD.ORD_TS)=DATE_ADD('$begDate',INTERVAL $day DAY)
					SET 
                    DSB.KMC8000=ORD.KMC8000,
					DSB.KMC1085=ORD.KMC1085,
					DSB.HPL375=ORD.HPL375,
					DSB.ATX67=ORD.ATX67,
					DSB.LABSVCS=ORD.LABSVCS,
                    DSB.REV_KMC8000=ORD.REV_KMC8000,
					DSB.REV_KMC1085=ORD.REV_KMC1085,
					DSB.REV_HPL375=ORD.REV_HPL375,
					DSB.REV_ATX67=ORD.REV_ATX67,
					DSB.REV_LABSVCS=ORD.REV_LABSVCS
					WHERE DSB.DTE=DATE_ADD('$begDate',INTERVAL $day DAY) "; 
			//echo $query."<br/>";
			mysql_query($query);
			
        #UPDATE BONUS
        $query = "UPDATE CDW.PRN_DIG_DSH_BRD DSB
                  LEFT OUTER JOIN
                  (
                    SELECT
                      DATE(HED.ORD_TS)     AS ORD_TS,
                      DATE(PAY.MAX_CRT_TS) AS CSH_DTE,

                      SUM(
                          CASE
                          WHEN
                            PRN_DIG_EQP = 'FLJ320P'
                            THEN
                              CASE
                              WHEN
                                DATE(PAY.MAX_CRT_TS) >= DATE(HED.ORD_TS)
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (100 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (80 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (60 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (40 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (20 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (0 / 100)
                              END
                          END
                      )                    AS FLJ320P_BON,
                      SUM(
                          CASE
                          WHEN
                            PRN_DIG_EQP = 'KMC6501'
                            THEN
                              CASE
                              WHEN
                                DATE(PAY.MAX_CRT_TS) >= DATE(HED.ORD_TS)
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                THEN ORD_Q * (100 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                THEN ORD_Q * (80 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                THEN ORD_Q * (60 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                THEN ORD_Q * (40 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN ORD_Q * (20 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN ORD_Q * (0 / 100)
                              END
                          END
                      )                    AS KMC6501_BON,
                      SUM(
                          CASE
                          WHEN
                            PRN_DIG_EQP = 'RVS640'
                            THEN
                              CASE
                              WHEN
                                DATE(PAY.MAX_CRT_TS) >= DATE(HED.ORD_TS)
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (100 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (80 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (60 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (40 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (20 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (0 / 100)
                              END
                          END
                      )                    AS RVS640_BON,
                      SUM(
                          CASE
                          WHEN
                            PRN_DIG_EQP = 'AJ1800F'
                            THEN
                              CASE
                              WHEN
                                DATE(PAY.MAX_CRT_TS) >= DATE(HED.ORD_TS)
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                THEN (ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1))) * (100 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                THEN (ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1))) * (80 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                THEN (ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1))) * (60 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                THEN (ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1))) * (40 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1))) * (20 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1))) * (0 / 100)
                              END
                          END
                      )                    AS AJ1800F_BON,
                      SUM(
                          CASE
                          WHEN
                            PRN_DIG_EQP = 'MVJ1624'
                            THEN
                              CASE
                              WHEN
                                DATE(PAY.MAX_CRT_TS) >= DATE(HED.ORD_TS)
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                THEN (ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1))) * (100 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                THEN (ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1))) * (80 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                THEN (ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1))) * (60 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                THEN (ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1))) * (40 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1))) * (20 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*(COALESCE(PRN_LEN,1))*(COALESCE(PRN_WID,1))) * (0 / 100)
                              END
                          END
                      )                    AS MVJ1624_BON
                    FROM
                      CMP.PRN_DIG_ORD_HEAD HED
                      LEFT JOIN
                      (
                        SELECT
                          PYMT.PYMT_NBR,
                          PYMT.ORD_NBR,
                          SUM(PYMT.TND_AMT) AS TND_AMT,
                          PYMT.CRT_TS,
                          MAX(PYMT.CRT_TS)  AS MAX_CRT_TS
                        FROM CMP.PRN_DIG_ORD_PYMT PYMT
                        WHERE PYMT.DEL_NBR = 0
                        GROUP BY PYMT.ORD_NBR
                      ) PAY ON PAY.ORD_NBR = HED.ORD_NBR
                      LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
                      LEFT JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR = DET.ORD_NBR
                      LEFT JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP = TYP.PRN_DIG_TYP
                    WHERE PAY.TND_AMT >= HED.TOT_AMT
                          AND DATE(PAY.MAX_CRT_TS) = DATE_ADD('$begDate',INTERVAL $day DAY)
                          AND DATE(ORD_TS) >= '2016-05-01' AND HED.DEL_NBR= 0 AND DET.DEL_NBR = 0 
                    GROUP BY DATE(PAY.MAX_CRT_TS)
                    ) BON ON DATE(BON.CSH_DTE)=DATE_ADD('$begDate',INTERVAL $day DAY)
                SET
                DSB.FLJ320P_BON = BON.FLJ320P_BON,
                DSB.KMC6501_BON = BON.KMC6501_BON,
                DSB.RVS640_BON  = BON.RVS640_BON,
                DSB.AJ1800F_BON = BON.AJ1800F_BON,
                DSB.MVJ1624_BON = BON.MVJ1624_BON

                WHERE DSB.DTE=DATE_ADD('$begDate',INTERVAL $day DAY)";
        mysql_query($query);
		
		        $query = "UPDATE CDW.PRN_DIG_DSH_BRD DSB
                  LEFT OUTER JOIN
                  (
                    SELECT
                      DATE(HED.ORD_TS)     AS ORD_TS,
                      DATE(PAY.MAX_CRT_TS) AS CSH_DTE,
                      SUM(
                          CASE
                          WHEN
                            PRN_DIG_EQP = 'KMC8000'
                            THEN
                              CASE
                              WHEN
                                DATE(PAY.MAX_CRT_TS) >= DATE(HED.ORD_TS)
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                THEN (ORD_Q)* (100 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                THEN (ORD_Q) * (80 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                THEN (ORD_Q)* (60 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                THEN (ORD_Q) * (40 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q) * (20 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q) * (0 / 100)
                              END
                          END
                      ) AS KMC8000_BON,
					SUM(
                          CASE
                          WHEN
                            PRN_DIG_EQP = 'KMC1085'
                            THEN
                              CASE
                              WHEN
                                DATE(PAY.MAX_CRT_TS) >= DATE(HED.ORD_TS)
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                THEN (ORD_Q) * (100 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                THEN (ORD_Q) * (80 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                THEN (ORD_Q) * (60 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                THEN (ORD_Q) * (40 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q) * (20 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q) * (0 / 100)
                              END
                          END
                      ) AS KMC1085_BON,
                      SUM(
                          CASE
                          WHEN
                            PRN_DIG_EQP = 'HPL375'
                            THEN
                              CASE
                              WHEN
                                DATE(PAY.MAX_CRT_TS) >= DATE(HED.ORD_TS)
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (100 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (80 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (60 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (40 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (20 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (0 / 100)
                              END
                          END
                      ) AS HPL375_BON,
                      SUM(
                          CASE
                          WHEN
                            PRN_DIG_EQP = 'ATX67'
                            THEN
                              CASE
                              WHEN
                                DATE(PAY.MAX_CRT_TS) >= DATE(HED.ORD_TS)
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (100 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (80 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (60 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (40 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (20 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (0 / 100)
                              END
                          END
                      ) AS ATX67_BON,
						SUM(
                          CASE
                          WHEN
                            PRN_DIG_EQP = 'LABSVCS'
                            THEN
                              CASE
                              WHEN
                                DATE(PAY.MAX_CRT_TS) >= DATE(HED.ORD_TS)
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (100 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (80 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (60 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (40 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                                AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (20 / 100)
                              WHEN
                                DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                                THEN (ORD_Q*PRN_LEN*PRN_WID) * (0 / 100)
                              END
                          END
                      ) AS LABSVCS_BON
					  FROM
                      CMP.PRN_DIG_ORD_HEAD HED
                      LEFT JOIN
                      (
                        SELECT
                          PYMT.PYMT_NBR,
                          PYMT.ORD_NBR,
                          SUM(PYMT.TND_AMT) AS TND_AMT,
                          PYMT.CRT_TS,
                          MAX(PYMT.CRT_TS)  AS MAX_CRT_TS
                        FROM CMP.PRN_DIG_ORD_PYMT PYMT
                        WHERE PYMT.DEL_NBR = 0
                        GROUP BY PYMT.ORD_NBR
                      ) PAY ON PAY.ORD_NBR = HED.ORD_NBR
                      LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
                      LEFT JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR = DET.ORD_NBR
                      LEFT JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP = TYP.PRN_DIG_TYP
                    WHERE PAY.TND_AMT >= HED.TOT_AMT
                          AND DATE(PAY.MAX_CRT_TS) = DATE_ADD('$begDate',INTERVAL $day DAY)
                          AND DATE(ORD_TS) >= '2016-05-01' AND HED.DEL_NBR= 0 AND DET.DEL_NBR = 0 
                    GROUP BY DATE(PAY.MAX_CRT_TS)
                    ) BON ON DATE(BON.CSH_DTE)=DATE_ADD('$begDate',INTERVAL $day DAY)
                SET
                DSB.KMC8000_BON = BON.KMC8000_BON,
                DSB.KMC1085_BON = BON.KMC1085_BON,
				DSB.HPL375_BON = BON.HPL375_BON,
				DSB.ATX67_BON = BON.ATX67_BON,
				DSB.LABSVCS_BON = BON.LABSVCS_BON
                WHERE DSB.DTE=DATE_ADD('$begDate',INTERVAL $day DAY)";
        mysql_query($query);
		
		
#END BONUS
			$query="UPDATE CDW.PRN_DIG_DSH_BRD SET TOT_REM=(SELECT SUM(TOT_REM) FROM CMP.PRN_DIG_ORD_HEAD WHERE (BUY_CO_NBR IS NULL OR BUY_CO_NBR NOT IN ($CoEx)) AND  DATE(ORD_TS)=DATE_ADD('$begDate',INTERVAL $day DAY) AND DEL_NBR=0) WHERE DTE=DATE_ADD('$begDate',INTERVAL $day DAY)";
			//echo $query."<br/>";
			mysql_query($query);
		
			//retail by category
			$query="SELECT INV.CAT_NBR, SUM(CSH.TND_AMT) AS TND_AMT
				FROM RTL.CSH_REG CSH
					LEFT OUTER JOIN RTL.INVENTORY INV ON CSH.RTL_BRC=INV.INV_BCD
					LEFT OUTER JOIN RTL.RTL_ORD_PYMT ORD ON CSH.REG_NBR = ORD.VAL_NBR
				WHERE DATE(CSH.CRT_TS)=DATE_ADD('$begDate',INTERVAL $day DAY) AND RTL_BRC<>'' AND (`CSH_FLO_TYP` ='RT' OR (`CSH_FLO_TYP` ='FL' AND ORD.VAL_NBR<>''))
				GROUP BY INV.CAT_NBR";
			$result=mysql_query($query);
			$retail = 0;
			$retailCafe = 0;

			while ($row = mysql_fetch_array($result)) {
				if ($row['CAT_NBR'] == 9) {
					$retailCafe += $row['TND_AMT'];
				} else {
					$retail += $row['TND_AMT'];
				}
			}

			//retail
			$query="UPDATE CDW.PRN_DIG_DSH_BRD SET REV_RTL=$retail WHERE DTE=DATE_ADD('$begDate',INTERVAL $day DAY)";
			//echo $query."<br/>";
			mysql_query($query);

			//retail cafe
			$query="UPDATE CDW.PRN_DIG_DSH_BRD SET REV_CAFE=$retailCafe WHERE DTE=DATE_ADD('$begDate',INTERVAL $day DAY)";
			//echo $query."<br/>";
			mysql_query($query);
		}
		
		//Top digital print type
		$query="DELETE FROM CDW.PRN_DIG_DSH_BRD_EQP";
		$result=mysql_query($query);
		$query="INSERT INTO CDW.PRN_DIG_DSH_BRD_EQP
				SELECT PRN_DIG_DESC,'FLJ320P' AS PRN_DIG_EQP,COALESCE(SUM(ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1)),0) AS VOL_ALL
					  ,COALESCE(SUM(CASE WHEN ORD_TS>=(CURRENT_TIMESTAMP - INTERVAL 30 DAY) THEN ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1) ELSE 0 END),0) AS VOL_LM
				  FROM CMP.PRN_DIG_TYP TYP LEFT OUTER JOIN
					   CMP.PRN_DIG_ORD_DET DET ON TYP.PRN_DIG_TYP=DET.PRN_DIG_TYP LEFT OUTER JOIN
					   CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR
				 WHERE PRN_DIG_EQP='FLJ320P' AND HED.DEL_NBR=0 AND DET.DEL_NBR=0
				 GROUP BY PRN_DIG_DESC
				 ORDER BY PRN_DIG_EQP,VOL_LM DESC";
		$result=mysql_query($query);
		$query="INSERT INTO CDW.PRN_DIG_DSH_BRD_EQP
				SELECT PRN_DIG_DESC,'KMC6501' AS PRN_DIG_EQP,COALESCE(SUM(ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1)),0) AS VOL_ALL
					  ,COALESCE(SUM(CASE WHEN ORD_TS>=(CURRENT_TIMESTAMP - INTERVAL 30 DAY) THEN ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1) ELSE 0 END),0) AS VOL_LM
				  FROM CMP.PRN_DIG_TYP TYP LEFT OUTER JOIN
					   CMP.PRN_DIG_ORD_DET DET ON TYP.PRN_DIG_TYP=DET.PRN_DIG_TYP LEFT OUTER JOIN
					   CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR
				 WHERE PRN_DIG_EQP='KMC6501' AND HED.DEL_NBR=0 AND DET.DEL_NBR=0
				 GROUP BY PRN_DIG_DESC
				 ORDER BY PRN_DIG_EQP,VOL_LM DESC";
		$result=mysql_query($query);
		$query="INSERT INTO CDW.PRN_DIG_DSH_BRD_EQP
				SELECT PRN_DIG_DESC,'AJ1800F' AS PRN_DIG_EQP,COALESCE(SUM(ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1)),0) AS VOL_ALL
					  ,COALESCE(SUM(CASE WHEN ORD_TS>=(CURRENT_TIMESTAMP - INTERVAL 30 DAY) THEN ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1) ELSE 0 END),0) AS VOL_LM
				  FROM CMP.PRN_DIG_TYP TYP LEFT OUTER JOIN
					   CMP.PRN_DIG_ORD_DET DET ON TYP.PRN_DIG_TYP=DET.PRN_DIG_TYP LEFT OUTER JOIN
					   CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR
				 WHERE PRN_DIG_EQP='AJ1800F' AND HED.DEL_NBR=0 AND DET.DEL_NBR=0
				 GROUP BY PRN_DIG_DESC
				 ORDER BY PRN_DIG_EQP,VOL_LM DESC";
		$result=mysql_query($query);
		//Top customers
		$query="DELETE FROM CDW.PRN_DIG_TOP_CUST";
		//echo "here";
		$result=mysql_query($query);
		$query="INSERT INTO CDW.PRN_DIG_TOP_CUST
				SELECT COALESCE(BUY_CO_NBR,BUY_PRSN_NBR) AS NBR,
				COALESCE(COALESCE(COM.NAME,PPL.NAME),'Tunai') AS NAME,CASE WHEN BUY_CO_NBR IS NOT NULL THEN 'CO_NBR' ELSE 'PRSN_NBR' END AS TYP,SUM(TOT_AMT) AS REV_TOT
				FROM CMP.PRN_DIG_ORD_HEAD HED
				LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
				LEFT OUTER JOIN CMP.PEOPLE  PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
				WHERE CRT_TS>=CURRENT_TIMESTAMP - INTERVAL 6 MONTH
				GROUP BY COALESCE(BUY_CO_NBR,BUY_PRSN_NBR),
				COALESCE(COM.NAME,PPL.NAME)
				ORDER BY 4 DESC
				LIMIT 0,100";
		$result=mysql_query($query);
		
		//Truncate Table
		$queryt = "TRUNCATE TABLE CDW.PRN_DIG_PROD";
		$result = mysql_query($queryt);
	}
// insert data ke PRN_DIG_PROD
$query = " INSERT INTO CDW.PRN_DIG_PROD
	SELECT 
	    NOTA.ORD_TS,
	    NOTA.PRN_FLJ320P,
	    NOTA.FIN_FLJ320P,
	    NOTA.RDY_FLJ320P,

	    NOTA.PRN_KMC6501,
	    NOTA.FIN_KMC6501,
	    NOTA.RDY_KMC6501,

	    NOTA.PRN_KMC8000,
	    NOTA.FIN_KMC8000,
	    NOTA.RDY_KMC8000,

	    NOTA.PRN_KMC1085,
	    NOTA.FIN_KMC1085,
	    NOTA.RDY_KMC1085,

	    NOTA.PRN_RVS640,
	    NOTA.FIN_RVS640,
	    NOTA.RDY_RVS640,

	    NOTA.PRN_AJ1800F,
	    NOTA.FIN_AJ1800F,
	    NOTA.RDY_AJ1800F,

	    NOTA.PRN_MVJ1624,
	    NOTA.FIN_MVJ1624,
	    NOTA.RDY_MVJ1624,

	    NOTA.PRN_HPL375,
	    NOTA.FIN_HPL375,
	    NOTA.RDY_HPL375,

	    NOTA.PRN_ATX67,
	    NOTA.FIN_ATX67,
	    NOTA.RDY_ATX67

	FROM 
	(
	SELECT ORD_TS, SUM(CASE WHEN PRN_DIG_EQP='FLJ320P'  AND ORD_STT_ID IN ('QU','PR','FN') 
                        THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS FLJ320P,
                    SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(PRN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS PRN_FLJ320P,
                    SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(FIN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS FIN_FLJ320P,
                    SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP') AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) 
                        THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RDY_FLJ320P,
                                            
                    SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501') AND ORD_STT_ID IN ('QU','PR','FN') 
                        THEN ORD_Q ELSE 0 END) AS KMC6501,
                    SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501') AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ORD_Q - PRN_CMP_Q) ELSE 0 END) AS PRN_KMC6501,
                    SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501') AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ORD_Q - FIN_CMP_Q) ELSE 0 END) AS FIN_KMC6501,
                    SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501') AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP')   AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) THEN ORD_Q ELSE 0 END) AS RDY_KMC6501,

                    SUM(CASE WHEN PRN_DIG_EQP IN ('KMC8000') AND ORD_STT_ID IN ('QU','PR','FN') 
                        THEN ORD_Q ELSE 0 END) AS KMC8000,
                    SUM(CASE WHEN PRN_DIG_EQP IN ('KMC8000') AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ORD_Q - PRN_CMP_Q) ELSE 0 END) AS PRN_KMC8000,
                    SUM(CASE WHEN PRN_DIG_EQP IN ('KMC8000') AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ORD_Q - FIN_CMP_Q) ELSE 0 END) AS FIN_KMC8000,
                    SUM(CASE WHEN PRN_DIG_EQP IN ('KMC8000') AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP')   AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) THEN ORD_Q ELSE 0 END) AS RDY_KMC8000,

                    SUM(CASE WHEN PRN_DIG_EQP IN ('KMC1085') AND ORD_STT_ID IN ('QU','PR','FN') 
                        THEN ORD_Q ELSE 0 END) AS KMC1085,
                    SUM(CASE WHEN PRN_DIG_EQP IN ('KMC1085') AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ORD_Q - PRN_CMP_Q) ELSE 0 END) AS PRN_KMC1085,
                    SUM(CASE WHEN PRN_DIG_EQP IN ('KMC1085') AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ORD_Q - FIN_CMP_Q) ELSE 0 END) AS FIN_KMC1085,
                    SUM(CASE WHEN PRN_DIG_EQP IN ('KMC1085') AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP')   AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) THEN ORD_Q ELSE 0 END) AS RDY_KMC1085,
                    
                    SUM(CASE WHEN PRN_DIG_EQP='RVS640'  AND ORD_STT_ID IN ('QU','PR','FN') 
                        THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RVS640,
                    SUM(CASE WHEN PRN_DIG_EQP='RVS640' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(PRN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS PRN_RVS640,
                    SUM(CASE WHEN PRN_DIG_EQP='RVS640' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(FIN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS FIN_RVS640,
                    SUM(CASE WHEN PRN_DIG_EQP='RVS640' AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP') AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) 
                        THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RDY_RVS640,
                        
                    SUM(CASE WHEN PRN_DIG_EQP='AJ1800F'  AND ORD_STT_ID IN ('QU','PR','FN') 
                        THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS AJ1800F,
                    SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(PRN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS PRN_AJ1800F,
                    SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(FIN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS FIN_AJ1800F,
                    SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP') AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) 
                        THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RDY_AJ1800F,    
                        
                        
                    SUM(CASE WHEN PRN_DIG_EQP='MVJ1624'  AND ORD_STT_ID IN ('QU','PR','FN') 
                        THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS MVJ1624,
                    SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(PRN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS PRN_MVJ1624,
                    SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(FIN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS FIN_MVJ1624,
                    SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP') AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) 
                        THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RDY_MVJ1624,

	                SUM(CASE WHEN PRN_DIG_EQP='HPL375'  AND ORD_STT_ID IN ('QU','PR','FN') 
                        THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS HPL375,
                    SUM(CASE WHEN PRN_DIG_EQP='HPL375' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(PRN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS PRN_HPL375,
                    SUM(CASE WHEN PRN_DIG_EQP='HPL375' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(FIN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS FIN_HPL375,
                    SUM(CASE WHEN PRN_DIG_EQP='HPL375' AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP') AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) 
                        THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RDY_HPL375,

                    SUM(CASE WHEN PRN_DIG_EQP='ATX67'  AND ORD_STT_ID IN ('QU','PR','FN') 
                        THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS ATX67,
                    SUM(CASE WHEN PRN_DIG_EQP='ATX67' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(PRN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS PRN_ATX67,
                    SUM(CASE WHEN PRN_DIG_EQP='ATX67' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
                        THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(FIN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS FIN_ATX67,
                    SUM(CASE WHEN PRN_DIG_EQP='ATX67' AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP') AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) 
                        THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RDY_ATX67

	                    FROM CMP.PRN_DIG_ORD_DET DET 
	                        LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
	                        LEFT JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
	                    WHERE HED.DEL_NBR=0 
	                        AND DET.DEL_NBR=0 
	                        AND DATE(HED.ORD_TS) < CURRENT_DATE
	                        AND DET.PRN_DIG_TYP !='PROD'
	                    GROUP BY DATE(HED.ORD_TS)
	                    ORDER BY ORD_TS DESC
	) NOTA
	    WHERE NOTA.PRN_FLJ320P > 0
	        OR NOTA.FIN_FLJ320P > 0
	        OR NOTA.RDY_FLJ320P > 0
	        OR NOTA.PRN_KMC6501 > 0
	        OR NOTA.FIN_KMC6501 > 0
	        OR NOTA.RDY_KMC6501 > 0
	        OR NOTA.PRN_KMC8000 > 0
		    OR NOTA.FIN_KMC8000 > 0
		    OR NOTA.RDY_KMC8000 > 0
		    OR NOTA.PRN_KMC1085 > 0
		    OR NOTA.FIN_KMC1085 > 0
		    OR NOTA.RDY_KMC1085 > 0
		    OR NOTA.PRN_RVS640 > 0
		    OR NOTA.FIN_RVS640 > 0
		    OR NOTA.RDY_RVS640 > 0
		    OR NOTA.PRN_AJ1800F > 0
		    OR NOTA.FIN_AJ1800F > 0
		    OR NOTA.RDY_AJ1800F > 0
		    OR NOTA.PRN_MVJ1624 > 0
		    OR NOTA.FIN_MVJ1624 > 0
		    OR NOTA.RDY_MVJ1624 > 0
		    OR NOTA.PRN_HPL375 > 0
		    OR NOTA.FIN_HPL375 > 0
		    OR NOTA.RDY_HPL375 > 0
		    OR NOTA.PRN_ATX67 > 0
		    OR NOTA.FIN_ATX67 > 0
		    OR NOTA.RDY_ATX67 > 0
			";

$result = mysql_query($query);
?>