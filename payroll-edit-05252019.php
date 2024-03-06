<?php
	include "framework/database/connect.php";
	include "framework/database/connect-cloud.php";
	include "framework/functions/komisi.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	include "framework/functions/crypt.php";

	date_default_timezone_set('Asia/Jakarta');

	$PrsnNbr 		= $_GET['PRSN_NBR'];
	$PymtDte 		= $_GET['PYMT_DTE'];
	$CoNbr 			= $_GET['CO_NBR'];
	$Security 		= getSecurity($_SESSION['userID'],"Payroll");
	$absF       	= $_GET['ABS_F'];
	$upperSecurity 	= getSecurity($_SESSION['userID'],"Executive");
	$Finance 		= getSecurity($_SESSION['userID'],"Finance");
	$Url 			= generateUrl(271,$CoNbrDef);

	//param
	$queryPrm	= "SELECT PERF_INCT_AMT, PERF_INCT_PCT FROM NST.PARAM_LOC";
	$resultPrm	= mysql_query($queryPrm);
	$rowPrm		= mysql_fetch_array($resultPrm);

	//Process changes here
	//Credit status check
	$query 		= "SELECT (SUM(CRDT_AMT)-(SELECT COALESCE(SUM(DEBT_MO),0) 
					FROM PAY.PAYROLL WHERE PRSN_NBR=".$PrsnNbr.")) REM_CRDT   
					FROM PAY.EMPL_CRDT WHERE PRSN_NBR=".$PrsnNbr." AND CRDT_APV=1 AND CRDT_APV_FIN=1 AND DEL_NBR=0";
	$result 	= mysql_query($query, $local); 
	$row 		= mysql_fetch_array($result);	
	$RemCrdt 	= $row['REM_CRDT'];
	
	if($RemCrdt > 0) {
	
	$query_crdt ="SELECT CREDIT.PRSN_NBR, 
					COALESCE(CREDIT.PYMT_NBR,0) AS PYMT_NBR, 
					COALESCE(SUM(PAYR.DEBT_MO),0) AS PAY_DEBT_MO, 
					COALESCE(COUNT(PAYR.PYMT_DTE),0) AS CNT_PYMT
				FROM PAY.PAYROLL PAYR 
					LEFT OUTER JOIN (
						SELECT CRDT.PRSN_NBR,
							COALESCE(CRDT.PYMT_NBR,0) AS PYMT_NBR,
							MAX(CRDT.PYMT_DTE) AS PYMT_DTE 
						FROM PAY.EMPL_CRDT CRDT
							WHERE CRDT.PRSN_NBR= ".$PrsnNbr."
								AND CRDT_F = 1 AND CRDT_APV=1 AND CRDT_APV_FIN=1
					) CREDIT ON CREDIT.PRSN_NBR = PAYR.PRSN_NBR
						WHERE PAYR.PRSN_NBR= ".$PrsnNbr."
							AND PAYR.PYMT_DTE >= CREDIT.PYMT_DTE
							AND PAYR.DEBT_MO != 0
					";
	
	$result_crdt	= mysql_query($query_crdt, $local);
	$row_crdt 		= mysql_fetch_array($result_crdt);
	
	
	$PymtNbr		= $row_crdt['PYMT_NBR'];
	$CntPymt		= $row_crdt['CNT_PYMT'];
	$PayDebtMo		= $row_crdt['PAY_DEBT_MO'];
	$CrdtPymtDte 	= $row_crdt['CRDT_PYMT_DTE'];
	
	}
	$query_date      = "SELECT PAY_CONFIG_NBR,
							  PAY_BEG_DTE,
							  PAY_END_DTE
						FROM PAY.PAY_CONFIG_DTE 
						WHERE PAY_ACT_F =1";
	$result_date	= mysql_query($query_date);
	$row_date		= mysql_fetch_array($result_date);	
	//$MaxPymtDte		= $row_date['MAX_PYMT_DTE'];
	$PayBegDte 		= $row_date['PAY_BEG_DTE'];
	$PayEndDte      = $row_date['PAY_END_DTE'];
	$PayConfigNbr   = $row_date['PAY_CONFIG_NBR'];
	//$MaxPymtDte='2018-06-27';

	#get peer value
	$queryrs 		= "SELECT PEER_RWD, STY_AMT, PEER_PNLTY FROM NST.PARAM_GLBL";
	$resultrs 		= mysql_query($queryrs, $local);
	$rowrs 			= mysql_fetch_array($resultrs);
	$peer_value 	= $rowrs['PEER_RWD'];
	$stayAmount 	= $rowrs['STY_AMT'];
	$pnlty_value 	= $rowrs['PEER_PNLTY'];

	#get hire date
	$queryHire 		= "SELECT HIRE_DTE, PPAY.PAY_BASE, PPAY.PAY_ADD, PPAY.PAY_CONTRB, PPAY.HLD_AMT 
							FROM CMP.PEOPLE PPL
							LEFT JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR 
						WHERE PPL.PRSN_NBR='$PrsnNbr'";
	$resultHire 	= mysql_query($queryHire);
	$rowHire 		= mysql_fetch_array($resultHire);
	$PayBasePar 	= $rowHire['PAY_BASE'];
	$PayAddPar 		= $rowHire['PAY_ADD'];
	$payTotMth 		= $rowHire['HLD_AMT']; //jumlah gaji yang harus ditahan
	$PayContrb 		= $rowHire['PAY_CONTRB'];
	
	#untuk mencari masa Travel Autorize
	$queryTrvl 		= "SELECT 
							GROUP_CONCAT(AUTH_TRVL_NBR) AS TRVL_NBR,
							ROUND(SUM(DIST),1) AS TOT_DIST 
						FROM CMP.AUTH_TRVL 
						WHERE VRFD_F = 1 AND PRSN_NBR=".$PrsnNbr." AND DATE(ORIG_TS) > '".$PayBegDte."' AND DATE(ORIG_TS) <= '".$PayEndDte."'";
	$resultTrvl		= mysql_query($queryTrvl);
	$rowTrvl		= mysql_fetch_array($resultTrvl);
	$TrvlNbr		= $rowTrvl['TRVL_NBR'];
	$TrvlAmt		= $rowTrvl['TOT_DIST'];
	
	#untuk mencari masa kerjanya
	$queryVal 		= "SELECT DATE_ADD('$rowHire[HIRE_DTE]', INTERVAL 3 MONTH) AS GET_DATE";
	$resultVal 		= mysql_query($queryVal);
	$rowVal 		= mysql_fetch_array($resultVal);
	
	$queryJum 		= "SELECT COUNT(*) AS TOT_LIST,COALESCE(SUM(PAY_HLD_AMT),0) AS PAY_HLD_AMT FROM PAY.PAY_HLD_LST WHERE PRSN_NBR='".$PrsnNbr."' AND DEL_NBR='0'";
	$resultJum 		= mysql_query($queryJum);
	$rowJum 		= mysql_fetch_array($resultJum);
	#0 --> masa kerja > 3 bulan  ||  1 --> masa kerja < 3 bulan
	if ((date('Y-m-d')>$rowVal['GET_DATE'])) {$HoldVal = 0;} else {$HoldVal = 1;}

	$HldAmtTot = 0;
	if (mysql_num_rows($resultJum)>0){
		$HldAmtTot = $rowJum['PAY_HLD_AMT']; 
	}
	//echo 'jumlah '.$HldAmtTot.' harusnya '.$payTotMth;
	$queryCekLst 	= "SELECT PRSN_NBR FROM PAY.PAY_HLD_LST WHERE PRSN_NBR='".$PrsnNbr."' AND PYMT_DTE='".$PymtDte."' AND DEL_NBR='0'"; 
	$resultCekLst 	= mysql_query($queryCekLst);
	$jumCekLst 		= mysql_num_rows($resultCekLst);

	// PEMASANGAN 
	$query 		= "SELECT MIN(PAYR.PYMT_DTE) AS PYMT_DTE 
					FROM PAY.PAYROLL PAYR 
					LEFT JOIN CMP.PEOPLE PPL 
						ON PAYR.PRSN_NBR = PPL.PRSN_NBR
					WHERE MONTH(PAYR.PYMT_DTE) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) 
						AND YEAR(PAYR.PYMT_DTE) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
						AND PPL.TERM_DTE IS NULL";
	$result 	= mysql_query($query, $local);	
	$row 		= mysql_fetch_array($result);	
	$LastPays 	= $row['PYMT_DTE'];

	if($PayBegDte != "") 
	{
		$lastpayroll  = $PayBegDte;
	}
	else
	{
		$lastpayroll  = $LastPays;
	}
	
	//$lastpayroll	= "2018-09-26";

	$beginPemasangan	= "2019-03-31";
	$endPemasangan		= "2019-04-29";
	
	if($PymtDte == "")
	{
		$query = "SELECT GROUP_CONCAT(HED.ORD_NBR) AS ORD_NBR, SUM(TOT_AMT) AS TOT_AMT
								 FROM $RTL.RTL_STK_HEAD HED  JOIN $CMP.COMPANY COM ON HED.SHP_CO_NBR = COM.CO_NBR 
								 WHERE ACCT_EXEC_NBR = '".$PrsnNbr."' AND HED.DEL_F = 0 AND COM.DEL_NBR = 0 
								 AND HED.ORD_DTE >= '".$beginPemasangan."' AND HED.ORD_DTE <= '".$endPemasangan."' GROUP BY COM.ACCT_EXEC_NBR"; 
		 //echo $query;
		$result 	= mysql_query($query,$cloud);
		while($row 	= mysql_fetch_array($result))
		{
			$ord_nbr = $row['ORD_NBR'];	
			$totamt  = $row['TOT_AMT'];
			// echo $ord_nbr;		
		}
	}
	else
	{
	  $query = "SELECT PYMT_DTE,PAY_MISC_AMT FROM $PAY.PAYROLL WHERE PRSN_NBR='".$PrsnNbr."' AND PYMT_DTE = '".$PymtDte."'";
	  // echo $query;
	  $result 	= mysql_query($query,$cloud);
	  while($row 	= mysql_fetch_array($result))
	  {
			$totamt  = $row['PAY_MISC_AMT'];
			// echo $ord_nbr;		
	  }
	}

	//Menghitung Pencairan Kas bon bulan ini
	$queryDsb   = "SELECT COALESCE(CRDT_PRNC,0) AS CRDT_PRNC,
						COALESCE(CRDT_AMT,0) AS CRDT_AMT, 
						PYMT_NBR
					FROM PAY.EMPL_CRDT 
					WHERE PRSN_NBR=".$PrsnNbr." 
						AND DSBRS_TYP='PAY'
						AND CRDT_APV=1 
						AND CRDT_APV_FIN=1 
						AND DATE(PYMT_DTE) > '".$PayBegDte."' 
						AND DATE(PYMT_DTE) <= CURRENT_DATE";
	$resultDsb  = mysql_query($queryDsb, $local);//echo $queryDsb;
	$rowDsb     = mysql_fetch_array($resultDsb);
	$crdtPrsnc  = $rowDsb['CRDT_PRNC'];
	
	
	if(($_POST['PRSN_NBR']!="")&&($cloud!=false)){
		$j=syncTable("PAYROLL","PRSN_NBR,PYMT_DTE","PAY",$PAY,$local,$cloud);
		$j+=syncTable("PEOPLE","PRSN_NBR","PAY",$PAY,$local,$cloud);

		$PrsnNbr=$_POST['PRSN_NBR'];
		$PymtDte=$_POST['PYMT_DTE'];
		
		//Take care of nulls
		if($_POST['BASE_AMT']==""){$BaseAmt="0";}else{$BaseAmt=$_POST['BASE_AMT'];}
		if($_POST['BASE_CNT']==""){$BaseCnt="0";}else{$BaseCnt=$_POST['BASE_CNT'];}
		if($_POST['BASE_TOT']==""){$BaseTot="0";}else{$BaseTot=$_POST['BASE_TOT'];}		
		if($_POST['ADD_AMT']==""){$AddAmt="0";}else{$AddAmt=$_POST['ADD_AMT'];}
		if($_POST['ADD_CNT']==""){$AddCnt="0";}else{$AddCnt=$_POST['ADD_CNT'];}
		if($_POST['ADD_TOT']==""){$AddTot="0";}else{$AddTot=$_POST['ADD_TOT'];}
		if($_POST['OT_AMT']==""){$OTAmt="0";}else{$OTAmt=$_POST['OT_AMT'];}
		if($_POST['OT_CNT']==""){$OTCnt="0";}else{$OTCnt=$_POST['OT_CNT'];}
		if($_POST['OT_TOT']==""){$OTTot="0";}else{$OTTot=$_POST['OT_TOT'];}
		if($_POST['CONTRB_AMT']==""){$ContrbAmt="0";}else{$ContrbAmt=$_POST['CONTRB_AMT'];}
		if($_POST['BON_PCT']==""){$BonPct="0";}else{$BonPct=$_POST['BON_PCT'];}
		if($_POST['BON_MO_AMT']==""){$BonMoAmt="0";}else{$BonMoAmt=$_POST['BON_MO_AMT'];}

		if($_POST['LTR_RPRMN_PCT']==""){$LtrRprmnPct="0";}else{$LtrRprmnPct=$_POST['LTR_RPRMN_PCT'];}
		if($_POST['LTR_RPRMN_AMT']==""){$LtrRprmnAmt="0";}else{$LtrRprmnAmt=$_POST['LTR_RPRMN_AMT'];}
		if($_POST['LTR_RPRMN']==""){$RprmnNbr="0";}else{$RprmnNbr=$_POST['LTR_RPRMN'];}
		
		if($_POST['DEBT_MO']==""){$DebtMo="0";}else{$DebtMo=$_POST['DEBT_MO'];}
		
		if($_POST['DED_DEF']==""){$DedDef="0";}else{$DedDef=$_POST['DED_DEF'];}
		if($_POST['CRDT_DTE']==""){$CrdtDte="0";}else{$CrdtDte=$_POST['CRDT_DTE'];}
		if($_POST['INST_NBR']==""){$InstNbr="0";}else{$InstNbr=$_POST['INST_NBR'];}
		if($_POST['CRDT_PRNC']==""){$BonPokok="0";}else{$BonPokok=$_POST['CRDT_PRNC'];}
		if($_POST['CMSN_AMT'] == "") {$Komisi = 0;}else{$Komisi = $_POST['CMSN_AMT'];}
		
		
		$query_pnlty	= "SELECT CRDT_PCT_MO, CRDT_PNLTY_MO FROM NST.PARAM_LOC";
		$result_pnlty	= mysql_query($query_pnlty);
		$row_pnlty		= mysql_fetch_array($result_pnlty);
		
		$Persen			= $row_pnlty['CRDT_PCT_MO'];
		$Penalty		= $row_pnlty['CRDT_PNLTY_MO'];
	
		$DatePnlty 		= date('Y-m-d', strtotime("+1 day", strtotime($PymtDte)));
		$DateBon 		= date('Y-m-d', strtotime("+2 day", strtotime($PymtDte)));
		
		if($crdtPrsnc == 0) {
		if (($RemCrdt > 0) && (($DebtMo == 0) || ($DebtMo == ""))){
			$query_cek  = "SELECT (CASE WHEN '".$DatePnlty."' IN (SELECT CRDT.PYMT_DTE FROM PAY.EMPL_CRDT CRDT WHERE CRDT.PRSN_NBR = ".$PrsnNbr." AND CRDT_APV=1 AND CRDT_APV_FIN=1) THEN 1 ELSE 0 END) AS DED_FLAG";			
			$result_cek	= mysql_query($query_cek);
			$row_cek	= mysql_fetch_array($result_cek);
			
			$DedFlag	= $row_cek['DED_FLAG'];
			
			if($DedFlag != 1) {
				$n 			= $PymtNbr - $CntPymt;
				$KasBon 	= $Persen * $RemCrdt;
				$Credit 	= ( $Persen * $KasBon ) / ( 1 - pow((1+$Persen),- $n) );
				$PenaltyMo	= $Penalty / $n;
				$DatePnlty 	= date('Y-m-d', strtotime("+1 day", strtotime($PymtDte)));
				$DateBon 	= date('Y-m-d', strtotime("+2 day", strtotime($PymtDte)));
				//echo $DatePnlty."--".$DateBon;
							
				$query_ins	= "INSERT INTO $PAY.EMPL_CRDT (PRSN_NBR, PYMT_DTE, CRDT_AMT, PYMT_NBR, CRDT_PRNC, CRDT_APV, CRDT_APV_NBR, CRDT_APV_FIN, CRDT_APV_FIN_NBR, UPD_TS, UPD_NBR)
								VALUES (
								'".$PrsnNbr."',
								'".$DateBon."',
								'".($Credit * $n)."',
								'".$n."',
								'".$KasBon."', 
								1, 
								".$_SESSION['personNBR'].",
								1,
								".$_SESSION['personNBR'].",
								CURRENT_TIMESTAMP,
								".$_SESSION['personNBR']."
								)";
				$result 	= mysql_query($query_ins,$cloud);
				$query_ins 	= str_replace($PAY,"PAY",$query_ins);
				$result 	= mysql_query($query_ins,$local);
								
				//INSERT PENALTY
				$query_pnlty= "INSERT INTO $PAY.EMPL_CRDT (PRSN_NBR, PYMT_DTE, CRDT_AMT, PYMT_NBR, CRDT_PRNC, CRDT_APV, CRDT_APV_NBR CRDT_APV_FIN, CRDT_APV_FIN_NBR, UPD_TS, UPD_NBR)
									VALUES (
									'".$PrsnNbr."',
									'".$DatePnlty."',
									'".$Penalty."',
									'".$n."',
									'".$Penalty."', 
									1,
									".$_SESSION['personNBR'].",
									1,
									".$_SESSION['personNBR'].",
									CURRENT_TIMESTAMP,
									".$_SESSION['personNBR']."
									)";
				$result 	= mysql_query($query_pnlty,$cloud);
				$query_pnlty= str_replace($PAY,"PAY",$query_pnlty);
				$result 	= mysql_query($query_pnlty,$local);
							
				//UPDATE KAS BON
				$query 		= "UPDATE $PAY.PEOPLE
								SET DED_DEF=DED_DEF+".$Credit."+".$PenaltyMo.",
									UPD_TS=CURRENT_TIMESTAMP,
									UPD_NBR=".$_SESSION['personNBR']."
								WHERE 
									PRSN_NBR=".$PrsnNbr."
								";
				$result 	= mysql_query($query,$cloud);
				$query 		= str_replace($PAY,"PAY",$query);
				$result 	= mysql_query($query,$local);
			}
		}
		}		
		
		
		if($_POST['PAY_AMT']==""){$PayAmt="0";}else{$PayAmt=$_POST['PAY_AMT'];}
		if($_POST['BON_SNG_AMT']==""){$BonAmt="0";}else{$BonAmt=$_POST['BON_SNG_AMT'];}
		if($_POST['DED_SNG_AMT']==""){$PotAmt="0";}else{$PotAmt=$_POST['DED_SNG_AMT'];}
		if($_POST['PERF_INCT']==""){$PerfInct="0";}else{$PerfInct=$_POST['PERF_INCT'];}
		if($_POST['PERF_INCT_TOT']==""){$PerfInctAmt="0";}else{$PerfInctAmt=abs($_POST['PERF_INCT_TOT']);}
		if($_POST['PERF_INCT_DESC']==""){$PerfInctDesc="";}else{$PerfInctDesc=$_POST['PERF_INCT_DESC'];}
		if($_POST['PEER_RWD'] == "on"){$PeerRwdF = 1;$PeerRwd = 0;} else {$PeerRwdF = 0;$PeerRwd = $peer_value;}
		if($_POST['PAY_HLD_F'] == "on"){$PayHldF = 1; $PayHldAmt = $_POST['PAY_HLD_AMT'];} else {$PayHldF = 0;$PayHldAmt = 0;}
		if($_POST['PAY_HLD_PD_F'] == "on"){$PayHldPdF = 1; $PayHldPdAmt = $_POST['PAY_HLD_TOT'];} else {$PayHldPdF = 0; $PayHldPdAmt = 0;}
		if($_POST['PEER_PRSN']==""){$PeerPrsn = 0;}else{$PeerPrsn = $_POST['PEER_PRSN'];}
		if($_POST['TOT_DIST']==""){$TotDist="0";}else{$TotDist=$_POST['TOT_DIST'];}
		if($_POST['REM_TYPE']==""){$RemTyp="";}else{$RemTyp=$_POST['REM_TYPE'];}
		if($_POST['AUTH_TRVL_AMT']==""){$TrvlAmt="0";}else{$TrvlAmt=$_POST['AUTH_TRVL_AMT'];}
		if($_POST['STY_CNT']==""){$styCnt="0";}else{$styCnt=$_POST['STY_CNT'];}
		if($_POST['STY_TOT_AMT']==""){$styTot="0";}else{$styTot=$_POST['STY_TOT_AMT'];}
		if($_POST['PNLTY'] == "on"){$peerPnlty = $pnlty_value;} else {$peerPnlty = 0;}
		if($_POST['PNLTY_PRSN']==""){$peerPnltyNbr = 0;}else{$peerPnltyNbr = $_POST['PNLTY_PRSN'];}
		if($_POST['DSBRS_CRDT']==""){$DsbrsCrdt=0;}else{$DsbrsCrdt=$_POST['DSBRS_CRDT'];}
		if($_POST['DED_DEF_CRDT']==""){$DedDefCrdt=0;}else{$DedDefCrdt=$_POST['DED_DEF_CRDT'];}
		if($_POST['Jasa_pmsngn']==""){$Jasa_pmsngn=0;}else{$Jasa_pmsngn=$_POST['Jasa_pmsngn'];}
		if($_POST['PAY_RWD_PCT'] ==""){$payRwdPct =0;}else{$payRwdPct = $_POST['PAY_RWD_PCT'];}
		if($_POST['PAY_RWD_AMT'] ==""){$payRwdAmt =0;}else{$payRwdAmt = $_POST['PAY_RWD_AMT'];}
		
		$Amt 	= 0;
		$Typ 	= '';
		if($PayHldF==1){$Amt=$PayHldAmt;$Typ='1'; $HldAmtTot+$Amt;} 
		if($PayHldPdF==1){$Amt=$PayHldPdAmt;$Typ='2';} 
		if($jumCekLst<1) {
			$queryLst 	= "INSERT INTO $PAY.PAY_HLD_LST (PRSN_NBR, PYMT_DTE, PAY_HLD_AMT, PAY_HLD_TYP, DEL_NBR, CRT_TS, CRT_NBR, UPD_TS, UPD_NBR) VALUES ('$PrsnNbr', '$PymtDte', '$Amt', '$Typ', '0', CURRENT_TIMESTAMP, '$_SESSION[personNBR]', CURRENT_TIMESTAMP, '$_SESSION[personNBR]')";
			mysql_query($queryLst,$cloud);
			$queryLst 	= str_replace($PAY,"PAY",$queryLst);
			mysql_query($queryLst,$local);
		} else {
			$queryLst 	= "UPDATE $PAY.PAY_HLD_LST SET PAY_HLD_AMT='$Amt', PAY_HLD_TYP='$Typ', UPD_TS=CURRENT_TIMESTAMP, UPD_NBR='$_SESSION[personNBR]' WHERE PRSN_NBR='$PrsnNbr' AND PYMT_DTE='$PymtDte'";
			mysql_query($queryLst,$cloud);
			$queryLst 	= str_replace($PAY,"PAY",$queryLst);
			mysql_query($queryLst,$local);
		}
		
		if($UpdateHldF>=$payTotMth)	{
			//update HLD_F yg di people menjadi 1 (sudah terpenuhi)
			$query 	= "UPDATE $PAY.PEOPLE SET HLD_F='1' WHERE PRSN_NBR='$PrsnNbr'";
		} else { //update HLD_F yg di people menjadi 0 (belum terpenuhi)
			$query 	= "UPDATE $PAY.PEOPLE SET HLD_F='0' WHERE PRSN_NBR='$PrsnNbr'";
		}
		mysql_query($query,$cloud);
		$query 	= str_replace($PAY,"PAY",$query);
		mysql_query($query,$local);
	
		//Process add new
		$query_cnt 	= "SELECT COUNT(*) AS CNT FROM $PAY.PAYROLL WHERE PRSN_NBR=".$PrsnNbr." AND PYMT_DTE='".$PymtDte."' AND DEL_NBR=0";
		$result_cnt = mysql_query($query_cnt,$cloud);
		$row_cnt 	= mysql_fetch_array($result_cnt);
		
		if($row_cnt['CNT']==0){
			$query 	= "INSERT INTO $PAY.PAYROLL (PRSN_NBR,PYMT_DTE) VALUES (".$PrsnNbr.",'".$PymtDte."')";
			$result = mysql_query($query,$cloud);
			$query 	= str_replace($PAY,"PAY",$query);
			$result = mysql_query($query,$local);	
		}

		//Proses pembuatan insert potongan
		$dedQ 		= $_POST['DED'];
		$DedDescs 	= "";
		$DedAmts    = "";
		for($i=1;$i<=$dedQ;$i++){
			$DedDesc  = "DED_DESC".$i;
			$DedAmt   = "DED_AMT".$i;

			if ($_POST[$DedDesc]!=''){
				if ($_POST[$DedDesc]=="Kosong"){
					$DedDescs.="";
					$DedAmts.="";
				}else{
					$DedDescs.= $_POST[$DedDesc]."+";
					$DedAmts .= $_POST[$DedAmt]."+";
				}
			}
			
		}
		$DedDescs 	= substr($DedDescs,0,-1);
		$DedAmts    = substr($DedAmts, 0, -1);

		//pencairan kas bon
		if ($DedDefCrdt>0){

			$queryDsb="UPDATE $PAY.PEOPLE
					SET DED_DEF=".$DedDefCrdt.",
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE PRSN_NBR=".$PrsnNbr;
		
			$resultDsb=mysql_query($queryDsb,$cloud);
			$queryDsb =str_replace($PAY,"PAY",$queryDsb);
			$resultDsb=mysql_query($queryDsb,$local);
		}

		
		# ================= UPDATE PAYROLL ======================= #	
		$query 	= "UPDATE $PAY.PAYROLL
		   			SET PYMT_DAYS=".$_POST['PYMT_DAYS'].",
		   				BASE_AMT=".$BaseAmt.",
		   				BASE_CNT=".$BaseCnt.",
		   				BASE_TOT=".$BaseTot.",
		   				ADD_AMT=".$AddAmt.",
		   				ADD_CNT=".$AddCnt.",
		   				ADD_TOT=".$AddTot.",
		   				OT_AMT=".$OTAmt.",
		   				OT_CNT=".$OTCnt.",
		   				OT_TOT=".$OTTot.",
		   				CONTRB_AMT=".$ContrbAmt.",
		   				BON_PCT=".$BonPct.",
		   				BON_MO_AMT=".$BonMoAmt.",
		   				RPRMN_PCT=".$LtrRprmnPct.",
		   				RPRMN_AMT=".$LtrRprmnAmt.",
		   				RPRMN_NBR=".$RprmnNbr.",
						CMSN_AMT=" . $Komisi . ",
						PEER_RWD=" . $PeerRwd . ",
						PEER_RWD_F=" . $PeerRwdF . ",
						PEER_RWD_PRSN=" . $PeerPrsn . ",
						PNLTY=".$peerPnlty.",
						PNLTY_PRSN=".$peerPnltyNbr.",
		   				DEBT_MO=".$DebtMo.",
		   				PAY_AMT=".$PayAmt.",
		   				BON_SNG_AMT=".$BonAmt.",
		   				DED_SNG_AMT=".$PotAmt.",
						PERF_INCT=".$PerfInct.",
						PERF_INCT_AMT=".$PerfInctAmt.",
						PERF_INCT_DESC='".$PerfInctDesc."',
						PAY_HLD_F=".$PayHldF.",
						PAY_HLD_AMT=".$PayHldAmt.",
						PAY_HLD_PD_F=".$PayHldPdF.",
						PAY_HLD_PD_AMT=".$PayHldPdAmt.",
						TOT_DIST=".$TotDist.",
						REM_TYPE='".$RemTyp."',
						AUTH_TRVL_AMT=".$TrvlAmt.",
						STY_CNT=".$styCnt.",
						STY_TOT_AMT=".$styTot.",
						DED_DESC='".$DedDescs."',
						DED_AMT ='".$DedAmts."', 
						PAY_MISC_DESC = 'Pemasangan',
						PAY_MISC_AMT = '".$Jasa_pmsngn."',
						PAY_RWD_PCT = '".$payRwdPct."',
						PAY_RWD_AMT = '".$payRwdAmt."',
						DSBRS_CRDT='".$DsbrsCrdt."',
						UPD_TS=CURRENT_TIMESTAMP,
						UPD_NBR=".$_SESSION['personNBR']."
						WHERE PRSN_NBR=".$PrsnNbr."
						AND PYMT_DTE='".$PymtDte."'";
	 	$result = mysql_query($query,$cloud);
		$query 	= str_replace($PAY,"PAY",$query);
		$result = mysql_query($query,$local);
		
		//echo $query;
		
		$queryTrv = "UPDATE AUTH_TRVL SET VRFD_TS=CURRENT_TIMESTAMP, VRFD_NBR='". $_SESSION['personNBR'] ."' WHERE AUTH_TRVL_NBR IN (". $TrvlNbr .")";
		mysql_query($queryTrv,$local);

		//update pemasangan stk_head tot_rem ke 0 dan pay_rem_ts
		$query  = "UPDATE $RTL.RTL_STK_HEAD SET PYMT_REM = TOT_AMT, TOT_REM = 0, PYMT_REM_TS = CURRENT_TIMESTAMP, VAL_PYMT_REM = '". $_SESSION['personNBR'] ."', PYMT_TYP = 'TRF' WHERE ORD_NBR IN ($ord_nbr) AND CAT_SUB_NBR = 273";
		// echo $query;
		$result = mysql_query($query,$cloud);
		
		$query 	= str_replace($RTL,"RTL",$query);
		$result = mysql_query($query,$local);
		
		
	}

	if($Security>=2) 
	{
		$hide='style="display:none !important;"';
	}

//Bonus calculation
if (($CoNbr == 1002) || ($CoNbr == 271) || ($CoNbr == 2996) || ($CoNbr == 2997) || ($CoNbr == 3110)) {
	$LastPymt 	= $PayBegDte;
	//echo $query."</br>".$LastPymt;
	
	$query 		= "SELECT BON_MULT FROM PAY.PEOPLE WHERE PRSN_NBR=$PrsnNbr";
	$result 	= mysql_query($query, $local);	
	$row 		= mysql_fetch_array($result);	
	if($row['BON_MULT']==''){$BonMult=1;}else{$BonMult=$row['BON_MULT'];}
	//echo $BonMult."</br>";
	//Mencari batas nominal mendapatkan bonus 
	$query 		= "SELECT FLEX_BASE_Q,BASE_PCT,DOC_BASE_Q,FLEX_INC_Q,DOC_INC_Q FROM PAY.PRN_DIG_BON_PLAN WHERE '".$PayEndDte."' BETWEEN BEG_DT AND END_DT";
	$result 	= mysql_query($query, $local);	
	$row 		= mysql_fetch_array($result);
	$BasePct 	= $row['BASE_PCT'];
	$FlexBaseQ 	= $row['FLEX_BASE_Q'];
	$FlexIncQ 	= $row['FLEX_INC_Q'];
	$DocBaseQ 	= $row['DOC_BASE_Q'];
	$DocIncQ 	= $row['DOC_INC_Q'];
	//echo 'BATAS '.$query.'<br/><br/>';
	//echo $BasePct." ".$FlexBaseQ." ".$FlexIncQ." ".$DocBaseQ." ".$DocIncQ."</br>";
	
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
					WHERE DTE BETWEEN '".$LastPymt."' AND '".$PayEndDte."' ";
	$result 	= mysql_query($query, $local);
	//echo 'LUNAS '.$query."</br><br/>";
	$row 		= mysql_fetch_array($result);
	$FLJ320P 	= $row['FLJ320P']/$div;
	$KMC6501 	= $row['KMC6501']/$div;	
	$RVS640 	= $row['RVS640']/$div;	
	$AJ1800F 	= $row['AJ1800F']/$div;	
	$HPL375 	= $row['HPL375']/$div;	
	$ATX67 		= $row['ATX67']/$div;
	//echo " Outdoor:".$FLJ320P." A3+:".$KMC6501." Indoor:".$RVS640." Fabric:".$AJ1800F." | ".$FlexBaseQ." ".$DocBaseQ." | ".$FlexIncQ." ".$DocIncQ."| ".$BasePct." </br>";

	$Bonus=0;
	if((($FLJ320P)>=$FlexBaseQ)&&($KMC6501>=$DocBaseQ)){
		$Bonus=$BasePct;
		//echo $Bonus." ";
		//echo $FLJ320P." ";
		if($FLJ320P>=$FlexIncQ){
			$FLJ320P=$FLJ320P-$FlexBaseQ;
			$Bonus=$Bonus+floor(($FLJ320P+$RVS640+$AJ1800F+$HPL375+$ATX67)/$FlexIncQ);//
			//echo $Bonus." ";
		}
		//echo $KMC6501." ";
		if($KMC6501>=$DocIncQ){
			$KMC6501=$KMC6501-$DocBaseQ;
			$Bonus=$Bonus+floor($KMC6501/$DocIncQ);
			//echo $Bonus." ";
		}
	}else{
		$Bonus=0;
	};

	$Bonus=$Bonus*$BonMult;
	if(!bonusTenure($PrsnNbr,90)){$Bonus=0;}
	//echo $Bonus;
	
	if ($CoNbr == 3110){
		$BonPrinting = simple_crypt(file_get_contents('http://'.$Url.'/web_service/get-reward.php?PRSN_NBR='.$PrsnNbr.'&PAY_BEG_DTE='.$PayBegDte.'&CO_NBR='.$CoNbr),'d');

		$Bonus     = ($Bonus + $BonPrinting)/2;
	}
}

$beginDateLast	= date('Y-m', strtotime('-1 month', strtotime(date('Y-m-d'))));
if (date('Y-m', strtotime($PayBegDte)) < date('Y-m', strtotime('-1 month', strtotime(date('Y-m-d'))))){
$PayBegDteLast = $PayBegDte;
	$queryMax 		= "SELECT PYMT_DTE, COUNT(PYMT_DTE) AS JUMLAH FROM PAY.PAYROLL WHERE LEFT(PYMT_DTE,7) = '".$beginDateLast."' GROUP BY PYMT_DTE ORDER BY COUNT(PYMT_DTE) DESC LIMIT 1";
	$resultMax 		= mysql_query($queryMax);
	$rowMax 		= mysql_fetch_array($resultMax);
	$PayBegDte 	    = $rowMax['PYMT_DTE'];

	//$MaxPymtDte		= '2018-06-27';
	
	if ($PayBegDteLast==''){
		$queryPpl = "SELECT HIRE_DTE FROM CMP.PEOPLE WHERE PRSN_NBR=".$PrsnNbr;
		$resultPpl= mysql_query($queryPpl, $local);
		$rowPpl   = mysql_fetch_array($resultPpl);

		$PayBegDte = $rowPpl['HIRE_DTE'];
	}
}

//Count Days
//$MaxPymtDte		= '2018-12-26';
$DateEndLast		= date('Y-m-t', strtotime($PayBegDte));//'2018-02-28';
$DateBeginNow		= date("Y-m-01");//'2018-03-01';
$DateEndNow		    = date("Y-m-d", strtotime($PayEndDte." -1 day"));//'2018-03-26';
$CurrentDate		= date('Y-m-d', strtotime($PayEndDte));//'2018-03-27';
$DateEndThisMonth	= date("Y-m-t");//'2018-03-31';
//echo 'MaxPymtDte: '.$MaxPymtDte.'# DateEndLast: '.$DateEndLast.'# DateBeginNow: '.$DateBeginNow.'# DateEndNow: '.$DateEndNow.'# CurrentDate: '.$CurrentDate.'<br/>';
$payrollSaving      = 0;

//=========== get location ====================//
$query_loc 	= "SELECT CO_NBR FROM CMP.PEOPLE WHERE PRSN_NBR = ".$PrsnNbr." ";
$result_loc	= mysql_query($query_loc, $local);
$row_loc	= mysql_fetch_array($result_loc);

$location 	= $row_loc['CO_NBR'];

//=======================================

$query_loc 	= "SELECT WORK_TM FROM PAY.PEOPLE WHERE PRSN_NBR = ".$PrsnNbr." ";
$result_loc	= mysql_query($query_loc, $local);
$row_loc	= mysql_fetch_array($result_loc);

$WorkTime	= $row_loc['WORK_TM'];

//=======================================

$array_loc		= array(1002,2997);
$default_loc	= "1002,2997";

if (in_array($location, $array_loc)) {
	$multiplier = 1;
}
else {
	$multiplier	= 2;
	
}

//=========== get number or workdays ====================//
$WorkDays	= date('t');

// if ()

//=========== get number of holidays ====================//Tidak digunakan
$query_hldy	= "SELECT 
						COUNT(HLDY.HLDY_DTE) AS CNT_HOLIDAY
					FROM PAY.HOLIDAY HLDY
					WHERE MONTH(HLDY.HLDY_DTE) = MONTH('".$PayBegDte."')
						AND YEAR(HLDY.HLDY_DTE) = YEAR('".$PayBegDte."')
					";
$result_hldy	= mysql_query($query_hldy, $local);
$row_hldy		= mysql_fetch_array($result_hldy);

$Holiday		= $row_hldy['CNT_HOLIDAY'];

//============== get number of holidays part (jumlah libur bulan ini sampai H-1 payroll)//Tidak Digunakan

$query_hldyPart	= "SELECT 
						COUNT(HLDY.HLDY_DTE) AS HOLIDAY_PART
					FROM PAY.HOLIDAY HLDY
					WHERE HLDY.HLDY_DTE >= '".$PayBegDte."'
						AND HLDY.HLDY_DTE <= '".$PayEndDte."'
					";
$result_hldyPart	= mysql_query($query_hldyPart, $local);
$row_hldyPart		= mysql_fetch_array($result_hldyPart);

$HolidayPart		= $row_hldyPart['HOLIDAY_PART'];

//============== get number of holidays full (jumlah libur selama periode payroll saat ini)

$query_hldyFull	= "SELECT 
						COUNT(HLDY.HLDY_DTE) AS HOLIDAY_FULL
					FROM PAY.HOLIDAY HLDY
					WHERE HLDY.HLDY_DTE >= '".$PayBegDte."'
						AND HLDY.HLDY_DTE <= '".$PayEndDte."'
					";
$result_hldyFull	= mysql_query($query_hldyFull, $local);
$row_hldyFull		= mysql_fetch_array($result_hldyFull);

$HolidayFull		= $row_hldyFull['HOLIDAY_FULL'];

//=========== jumlah hari masuk seharusnya (jumlah hari - libur) ====================//
if ($payrollSaving == 1){
	$EndDteItrvl    = $CurrentDate;
}else{
	$EndDteItrvl    = date('Y-m-d',strtotime($PayEndDte." +1 days")); 
}
$query_workday	= "SELECT DATEDIFF('".$EndDteItrvl."', '".$PayBegDte."') AS CNT_DAY";
$result_workday	= mysql_query($query_workday);
$row_workday	= mysql_fetch_array($result_workday);

$CountDays		= $row_workday['CNT_DAY'];

$WorkDay		= $CountDays - $HolidayFull;

//=========== jumlah hari dianggap masuk ====================//Tidak digunakan

$query_default	= "SELECT DATEDIFF('".$DateEndThisMonth."', '".$PayEndDte."') AS CNT_DEFAULT";
//echo $query_default;
$result_default	= mysql_query($query_default);
$row_default	= mysql_fetch_array($result_default);

$DayDefault		= $row_default['CNT_DEFAULT'];

//=========== get timeoff/leave of absense/cuti ====================//

function time_off($local, $PrsnNbr, $beginDate, $endDate, $multiplier) {

	$query_off		= "SELECT 
							COALESCE(SUM(CASE WHEN TM_OFF.TM_OFF_F = 1 
							THEN 
								COALESCE(DATEDIFF(TM_OFF.TM_OFF_END_DTE, TM_OFF.TM_OFF_BEG_DTE),0)+1
							ELSE 0 
							END 
							),0) AS CNT_TM_OFF
						FROM PAY.TM_OFF 
						WHERE TM_OFF.DEL_NBR = 0 
							AND TM_OFF.TM_OFF_F = 1
							AND DATE(TM_OFF.TM_OFF_BEG_DTE) >= '".$beginDate."'
							AND DATE(TM_OFF.TM_OFF_BEG_DTE) <= '".$endDate."'
							AND PRSN_NBR = ".$PrsnNbr."
						";
	$result_off		= mysql_query($query_off, $local);
	$row_off 		= mysql_fetch_array($result_off);

	$TimeOff		= $row_off['CNT_TM_OFF'];

	$arrayTimeOff	= array();

	$arrayTimeOff['CNT_TM_OFF']	= $TimeOff;

	return($arrayTimeOff);

}


//============================================================//

	
function count_days($local, $PrsnNbr, $beginDate, $endDate, $multiplier) {
	$query 		= "SELECT 
					    DATE_TS,
					    PRSN_NBR,
					    CO_NBR,
					    DAYNAME_PAY,
					    HOLIDAY,
						CLOK_IN_TS,
					    CLOK_OT_TS,
						WORKHOUR,
						SUM(CASE 
					        WHEN (CLOK_IN_TS IS NULL OR CLOK_OT_TS IS NULL) THEN 0.5 
							ELSE (
							CASE WHEN CO_NBR NOT IN (1002,2997) AND HOLIDAY = 0 AND CLOK_OT_TS IS NOT NULL 
									THEN 1
								WHEN CO_NBR IN (1002,2997) AND CLOK_OT_TS IS NOT NULL 
									THEN 1
								ELSE 0
								END
							)
							END) AS DAY_NORMAL,
						SUM(CASE 
					        WHEN CO_NBR NOT IN (1002,2997) 
								AND HOLIDAY = 1 
								AND CLOK_OT_TS IS NOT NULL 
								AND (COALESCE(WORKHOUR,0) DIV WORK_TM > 0)
							THEN (COALESCE(WORKHOUR,0) DIV WORK_TM)
							ELSE 0
							END
							) AS DAY_HOLIDAY,
					    SUM(
					        CASE 
					        WHEN CO_NBR NOT IN (1002,2997) AND HOLIDAY = 0 AND CLOK_OT_TS IS NOT NULL
					            THEN (COALESCE(WORKHOUR,0) - WORK_TM)
					        WHEN CO_NBR IN (1002,2997) AND CLOK_OT_TS IS NOT NULL
					            THEN (COALESCE(WORKHOUR,0) - WORK_TM)
					        ELSE 0
					        END
					    ) AS OT_NORMAL,
						SUM(
					        CASE 
					        WHEN (CO_NBR NOT IN (1002,2997) AND HOLIDAY = 1 AND COALESCE(WORKHOUR,0) > WORK_TM )
					            THEN (COALESCE(WORKHOUR,0) MOD WORK_TM) 
					        WHEN (CO_NBR NOT IN (1002,2997) AND HOLIDAY = 1 AND COALESCE(WORKHOUR,0) < WORK_TM)
					            THEN COALESCE(WORKHOUR,0) 
					        ELSE 0 END
					    )AS OT_HOLIDAY
					FROM
					(SELECT 
	                    CLOK_NBR, 
	                    PPL.CO_NBR,
	                    PPL.PRSN_NBR,
	                    DATE(CLOK_IN_TS) AS DATE_TS, 
	                    DAYNAME(DATE(CLOK_IN_TS)) AS DAYNAME_PAY,
	                    HLDY.HLDY_DTE,
	                    PPAY.WORK_TM,
	                    MAC.CLOK_IN_TS,
	                    MAC.CLOK_OT_TS,
	                    (CASE WHEN HLDY.HLDY_DTE IS NOT NULL THEN 1 ELSE 0 END) AS HOLIDAY,
	                    SUM(CASE 
	                        WHEN HLDY.HLDY_DTE IS NOT NULL
	                        THEN ((ROUND(TIME_TO_SEC(TIMEDIFF(CLOK_OT_TS,CLOK_IN_TS))/3600,2)) * ".$multiplier.") 
	                        ELSE (ROUND(TIME_TO_SEC(TIMEDIFF(CLOK_OT_TS,CLOK_IN_TS))/3600,2))
	                        END) AS WORKHOUR 
	                FROM PAY.MACH_CLOK MAC 
	                LEFT OUTER JOIN CMP.PEOPLE PPL 
	                    ON MAC.PRSN_NBR=PPL.PRSN_NBR 
			LEFT OUTER JOIN PAY.PEOPLE PPAY
				ON PPAY.PRSN_NBR=PPL.PRSN_NBR
	                LEFT JOIN PAY.HOLIDAY HLDY
	                    ON DATE(MAC.CLOK_IN_TS) = HLDY.HLDY_DTE
	                WHERE DATE(CLOK_IN_TS) 
	                    AND DATE(CLOK_IN_TS) >= '".$beginDate."' 
						AND DATE(CLOK_IN_TS) <= '".$endDate."'
						AND MAC.PRSN_NBR = ".$PrsnNbr."
	                GROUP BY PRSN_NBR,DATE(CLOK_IN_TS)
					) WORK
					";//echo "<pre>".$query."</pre>";
	$result		= mysql_query($query,$local);
	$row		= mysql_fetch_array($result);

	$day_normal		= $row['DAY_NORMAL'];
	$day_holiday	= $row['DAY_HOLIDAY'];
	$ot_normal		= $row['OT_NORMAL'];
	$ot_holiday		= $row['OT_HOLIDAY'];

	$arrayDays	= array();

	$arrayDays['DAY_NORMAL']	= $day_normal;
	$arrayDays['DAY_HOLIDAY']	= $day_holiday;
	$arrayDays['OT_NORMAL']		= $ot_normal;
	$arrayDays['OT_HOLIDAY']	= $ot_holiday;
	$arrayDays['query']			= $query;

	return($arrayDays);

}

if ($payrollSaving == 1){
	$ArrayTimeOffLast	= time_off($local, $PrsnNbr, $PayBegDte, $DateEndLast, $multiplier);
	$ArrayTimeOffNow	= time_off($local, $PrsnNbr, $DateBeginNow, $DateEndNow, $multiplier);
	
	$TimeOffLast	= $ArrayTimeOffLast['CNT_TM_OFF'];
	$TimeOffNow		= $ArrayTimeOffNow['CNT_TM_OFF'];
}else{
	$ArrayTimeOff	= time_off($local, $PrsnNbr, $PayBegDte, $PayEndDte, $multiplier);
	
	$TimeOffNow	= $ArrayTimeOff['CNT_TM_OFF'];
}

$ArrayLastMonth	= count_days($local, $PrsnNbr, $PayBegDte, $DateBeginLastMonth, $multiplier);

$DayNormalLastMonth	= $ArrayLastMonth['DAY_NORMAL'];//Tidak Digunakan

if ($payrollSaving == 1){
	$arrayLast		= count_days($local, $PrsnNbr, $PayBegDte, $DateEndLast, $multiplier);

	$DayNormalLast 	= $arrayLast['DAY_NORMAL'];
	$DayHolidayLast	= $arrayLast['DAY_HOLIDAY'];
	$OtNormalLast	= $arrayLast['OT_NORMAL'];
	$OtHolidayLast	= $arrayLast['OT_HOLIDAY'];
	
	$arrayNow		= count_days($local, $PrsnNbr, $DateBeginNow, $DateEndNow, $multiplier);

	$DayNormalNow 	= $arrayNow['DAY_NORMAL'];
	$DayHolidayNow	= $arrayNow['DAY_HOLIDAY'];
	$OtNormalNow	= $arrayNow['OT_NORMAL'];
	$OtHolidayNow	= $arrayNow['OT_HOLIDAY'];

	$DayMinus		= $WorkDay - ($DayNormalLast + $DayNormalNow + $TimeOffLast + $TimeOffNow);	//Jumlah Kekurangan Hari

	$DayNormal		= $DayNormalLast + $DayNormalNow;		//Jumlah Hari Kerja Normal
	$DayHoliday		= $DayHolidayLast + $DayHolidayNow;		//Jumlah Hari Lembur Libur (Kerja saat hari Libur)
	$OtNormal 		= $OtNormalLast + $OtNormalNow;			//Jumlah Jam Lembur Normal 
	$OtHoliday		= $OtHolidayLast + $OtHolidayNow;		//Jumlah Jam Lembur Libur (Kerja saat hari Libur)

	$Overtime		= $OtNormal + $OtHoliday;
}else{
	$arrayNow		= count_days($local, $PrsnNbr, $PayBegDte, $PayEndDte, $multiplier);

	$DayNormalNow 	= $arrayNow['DAY_NORMAL'];
	$DayHolidayNow	= $arrayNow['DAY_HOLIDAY'];
	$OtNormalNow	= $arrayNow['OT_NORMAL'];
	$OtHolidayNow	= $arrayNow['OT_HOLIDAY'];

	$DayMinus		= $WorkDay - ($DayNormalNow + $TimeOffNow);	//Jumlah Kekurangan Hari

	$DayNormal		= $DayNormalNow;		//Jumlah Hari Kerja Normal
	$DayHoliday		= $DayHolidayNow;		//Jumlah Hari Lembur Libur (Kerja saat hari Libur)
	$OtNormal 		= $OtNormalNow;			//Jumlah Jam Lembur Normal 
	$OtHoliday		= $OtHolidayNow;		//Jumlah Jam Lembur Libur (Kerja saat hari Libur)

	$Overtime		= $OtNormal + $OtHoliday;
}


//$InDays		= $DayHoliday + $DayNormalNow + $HolidayPart + $TimeOffNow + $DayDefault - $DayMinus;		//Total Hari Masuk

// echo "work day ".$WorkDay."<br/>";
// echo "count day ".$CountDays."<br/>";
// echo "holiday full ".$HolidayFull."<br/>";
// echo "day normal last ".$DayNormalLast."<br/>";
// echo "day normal now ".$DayNormalNow."<br/>";
// echo "time off last ".$TimeOffLast."<br/>";
// echo "time off now ".$TimeOffNow."<br/>";
// echo "Day Minus ".$DayMinus."<br/>";
// echo "workdays ".$WorkDay."<br/>";

if ($payrollSaving == 1){
	$InDays		= $DayHoliday + date('t') - $DayMinus;
}else{
	$InDays		= $DayHoliday + $CountDays - $DayMinus;
}

if($Overtime < 0)  {
	
	$InDays 		-= floor(abs($Overtime/$WorkTime));
	
	if((abs($Overtime) >= ($WorkTime/2)) && (abs($Overtime) < $WorkTime)) {
		$InDays 		-= 0.5;
	}
	
	$Overtime	= 0;
}
//echo $InDays;
$OtHours		= $Overtime;						//Total Jam Lembur
//echo $DayHoliday."+".$DayNormalNow."+".$HolidayPart."+".$TimeOffNow."+".$DayDefault."-".$DayMinus;

//======================================================================================================
//Perubahan absence yang tidak ada di leave of absence dan di timee off yang akan di input di time off
//======================================================================================================

function count_days_full($local, $PrsnNbr, $beginDate, $endDate, $multiplier) {
	$query 		= "SELECT 
					    DATE_TS,
					    PRSN_NBR,
					    CO_NBR,
					    DAYNAME_PAY,
					    HOLIDAY,
						CLOK_IN_TS,
					    CLOK_OT_TS,
						WORKHOUR,
						SUM(CASE 
					        WHEN (CLOK_IN_TS IS NULL OR CLOK_OT_TS IS NULL) THEN 1 
							ELSE (
							CASE WHEN CO_NBR NOT IN (1002,2997) AND HOLIDAY = 0 AND CLOK_OT_TS IS NOT NULL 
									THEN 1
								WHEN CO_NBR IN (1002,2997) AND CLOK_OT_TS IS NOT NULL 
									THEN 1
								ELSE 0
								END
							)
							END) AS DAY_NORMAL,
						SUM(CASE 
					        WHEN CO_NBR NOT IN (1002,2997) 
								AND HOLIDAY = 1 
								AND CLOK_OT_TS IS NOT NULL 
								AND (COALESCE(WORKHOUR,0) DIV WORK_TM > 0)
							THEN (COALESCE(WORKHOUR,0) DIV WORK_TM)
							ELSE 0
							END
							) AS DAY_HOLIDAY,
					    SUM(
					        CASE 
					        WHEN CO_NBR NOT IN (1002,2997) AND HOLIDAY = 0 AND CLOK_OT_TS IS NOT NULL
					            THEN (COALESCE(WORKHOUR,0) - WORK_TM)
					        WHEN CO_NBR IN (1002,2997) AND CLOK_OT_TS IS NOT NULL
					            THEN (COALESCE(WORKHOUR,0) - WORK_TM)
					        ELSE 0
					        END
					    ) AS OT_NORMAL,
						SUM(
					        CASE 
					        WHEN (CO_NBR NOT IN (1002,2997) AND HOLIDAY = 1 AND COALESCE(WORKHOUR,0) > WORK_TM )
					            THEN (COALESCE(WORKHOUR,0) MOD WORK_TM) 
					        WHEN (CO_NBR NOT IN (1002,2997) AND HOLIDAY = 1 AND COALESCE(WORKHOUR,0) < WORK_TM)
					            THEN COALESCE(WORKHOUR,0) 
					        ELSE 0 END
					    )AS OT_HOLIDAY
					FROM
					(SELECT 
	                    CLOK_NBR, 
	                    PPL.CO_NBR,
	                    PPL.PRSN_NBR,
	                    DATE(CLOK_IN_TS) AS DATE_TS, 
	                    DAYNAME(DATE(CLOK_IN_TS)) AS DAYNAME_PAY,
	                    HLDY.HLDY_DTE,
	                    PPAY.WORK_TM,
	                    MAC.CLOK_IN_TS,
	                    MAC.CLOK_OT_TS,
	                    (CASE WHEN HLDY.HLDY_DTE IS NOT NULL THEN 1 ELSE 0 END) AS HOLIDAY,
	                    SUM(CASE 
	                        WHEN HLDY.HLDY_DTE IS NOT NULL
	                        THEN ((ROUND(TIME_TO_SEC(TIMEDIFF(CLOK_OT_TS,CLOK_IN_TS))/3600,2)) * ".$multiplier.") 
	                        ELSE (ROUND(TIME_TO_SEC(TIMEDIFF(CLOK_OT_TS,CLOK_IN_TS))/3600,2))
	                        END) AS WORKHOUR 
	                FROM PAY.MACH_CLOK MAC 
	                LEFT OUTER JOIN CMP.PEOPLE PPL 
	                    ON MAC.PRSN_NBR=PPL.PRSN_NBR 
			LEFT OUTER JOIN PAY.PEOPLE PPAY
				ON PPAY.PRSN_NBR=PPL.PRSN_NBR
	                LEFT JOIN PAY.HOLIDAY HLDY
	                    ON DATE(MAC.CLOK_IN_TS) = HLDY.HLDY_DTE
	                WHERE DATE(CLOK_IN_TS) 
	                    AND DATE(CLOK_IN_TS) >= '".$beginDate."' 
						AND DATE(CLOK_IN_TS) <= '".$endDate."'
						AND MAC.PRSN_NBR = ".$PrsnNbr."
	                GROUP BY PRSN_NBR,DATE(CLOK_IN_TS)
					) WORK
					";
	$result		= mysql_query($query,$local);
	$row		= mysql_fetch_array($result);

	$day_normal		= $row['DAY_NORMAL'];
	$day_holiday	= $row['DAY_HOLIDAY'];
	$ot_normal		= $row['OT_NORMAL'];
	$ot_holiday		= $row['OT_HOLIDAY'];

	$arrayDays	= array();

	$arrayDays['DAY_NORMAL']	= $day_normal;
	$arrayDays['DAY_HOLIDAY']	= $day_holiday;
	$arrayDays['OT_NORMAL']		= $ot_normal;
	$arrayDays['OT_HOLIDAY']	= $ot_holiday;

	return($arrayDays);

}

function loa($local, $PrsnNbr, $beginDate, $endDate, $multiplier) {

	$query_loa		= "SELECT 
							COALESCE(SUM(CASE WHEN LOA.LOA_F = 1 
							THEN 
								COALESCE(DATEDIFF(LOA.LOA_END_DTE, LOA.LOA_BEG_DTE),0)+1
							ELSE 0 
							END 
							),0) AS CNT_LOA
						FROM PAY.LOA 
						WHERE LOA.DEL_NBR = 0 
							AND LOA.LOA_F = 1
							AND DATE(LOA.LOA_BEG_DTE) >= '".$beginDate."'
							AND DATE(LOA.LOA_BEG_DTE) <= '".$endDate."'
							AND PRSN_NBR = ".$PrsnNbr."
						";
	$result_loa		= mysql_query($query_loa, $local);
	$row_loa 		= mysql_fetch_array($result_loa);

	$Loa		    = $row_off['CNT_LOA'];

	$arrayLoa	    = array();

	$arrayLoa['CNT_LOA']	= $Loa;

	return($arrayLoa);
}

if ($payrollSaving == 1){
	$arrayLastFull		= count_days_full($local, $PrsnNbr, $PayBegDte, $DateEndLast, $multiplier);
	$DayNormalLastFull 	= $arrayLastFull['DAY_NORMAL'];

	$arrayNowFull		= count_days_full($local, $PrsnNbr, $DateBeginNow, $DateEndNow, $multiplier);
	$DayNormalNowFull 	= $arrayNowFull['DAY_NORMAL'];

	$DayMinusFull		= $WorkDay - ($DayNormalLastFull + $DayNormalNowFull + $TimeOffLast + $TimeOffNow);
}else{
	$arrayNowFull		= count_days_full($local, $PrsnNbr, $PayBegDte, $PayEndDte, $multiplier);
	$DayNormalNowFull 	= $arrayNowFull['DAY_NORMAL'];

	$DayMinusFull		= $WorkDay - ($DayNormalNowFull + $TimeOffNow);
}

$query_kontrak  = "SELECT EMPL_CNTRCT FROM CMP.PEOPLE WHERE PRSN_NBR=".$PrsnNbr;
$result_kontrak = mysql_query($query_kontrak,$local);
$row_kontrak    = mysql_fetch_array($result_kontrak);
$kontrak_kerja  = $row_kontrak['EMPL_CNTRCT'];
$displayAbsence ="display:none;";

if ($DayMinusFull>0 && $kontrak_kerja==5 ){
	if ($payrollSaving ==1){
		$ArrayLoaLast	= loa($local, $PrsnNbr, $PayBegDte, $DateEndLast, $multiplier);
		$ArrayLoaNow	= loa($local, $PrsnNbr, $DateBeginNow, $DateEndNow, $multiplier);
		
		$LoaLast	= $ArrayLoaLast['CNT_LOA'];
		$LoaNow		= $ArrayLoaNow['CNT_LOA'];

		$CntLoa     = $LoaLast + $LoaNow;
	}else{
		$ArrayLoaNow = loa($local, $PrsnNbr, $DateBeginNow, $PayEndDte, $multiplier);
		
		$LoaNow		 = $ArrayLoaNow['CNT_LOA'];
		$CntLoa      = $LoaNow;
	}

	$absence        = $DayMinusFull - $CntLoa;
	$begDtYear      = date('Y-01-01');
	$endDtYear      = date('Y-m-t',strtotime(date('Y-12-01')));
	$timeoffinYear  = time_off($local, $PrsnNbr, $begDtYear, $endDtYear, $multiplier);
	
	if ($DayMinusFull>8){
		$DayMinusFull=8-$timeoffinYear['CNT_TM_OFF'];
	}
	if ($payrollSaving ==1){
		$totalTimeOffInMonth = $TimeOffNow + $TimeOffLast;
	}else{
		$totalTimeOffInMonth = $TimeOffNow;
	}
	

	if ($absence>0 && $timeoffinYear['CNT_TM_OFF']<8 && $totalTimeOffInMonth<3){
		$totAbsence = $absence;

		//Jika cuti melebihi 8 selama satu bulan
		if ($absence>8){
			$absence= 8-$timeoffinYear['CNT_TM_OFF'];
			if ($absence > 3 ){
				$absence = 3;
			}
		}
		
		//Jika cuti tidak boleh melebihi limit cuti dalam satu tahun
		$timeOffAll = $timeoffinYear['CNT_TM_OFF']+$absence;
		if ($timeOffAll>8){
			$absence= 8-$timeoffinYear['CNT_TM_OFF'];
		}

		//Limit jika absen tidak boleh melebihi maksimal cuti dalam 1 bulan yaitu 3 hari
		$timeOffAllMonth = $absence + $totalTimeOffInMonth;
		if ($timeOffAllMonth>3){
			$absence = 3 - $totalTimeOffInMonth;
		}

		$query_abs = "SELECT * FROM 
							(SELECT ADDDATE('1970-01-01',T4.I*10000 + T3.I*1000 + T2.I*100 + T1.I*10 + T0.I) ALL_DATE FROM
							 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T0,
							 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T1,
							 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T2,
							 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T3,
							 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T4) V
						WHERE 
							ALL_DATE BETWEEN '$PayBegDte' AND '$PayEndDte'
							AND ALL_DATE NOT IN (
									SELECT * FROM
									(
										SELECT DATE(CLOK_IN_TS) AS DATE_IN
										FROM PAY.MACH_CLOK 
										WHERE 
											DATE(CLOK_IN_TS)>='$PayBegDte'
											AND DATE (CLOK_IN_TS)<='$PayEndDte'
											AND PRSN_NBR = $PrsnNbr
										GROUP BY DATE(CLOK_IN_TS)

										UNION ALL

										SELECT HLDY_DTE
										FROM PAY.HOLIDAY
										WHERE
											HLDY_DTE >='$PayBegDte' 
											AND HLDY_DTE<= '$PayEndDte'

										UNION ALL 

										SELECT ALL_DATE FROM 
										(SELECT ADDDATE('1970-01-01',T4.I*10000 + T3.I*1000 + T2.I*100 + T1.I*10 + T0.I) ALL_DATE FROM
										 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T0,
										 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T1,
										 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T2,
										 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T3,
										 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T4) V
										LEFT OUTER JOIN 
											(
												SELECT LOA_BEG_DTE,LOA_END_DTE 
												FROM PAY.LOA
												WHERE 
													DATE(LOA_BEG_DTE)>='$PayBegDte'
													AND DATE(LOA_BEG_DTE)<='$PayEndDte'
													AND PRSN_NBR=$PrsnNbr
													AND DEL_NBR=0
													AND LOA_F=1
											)LOA ON DATE_FORMAT(LOA_BEG_DTE,'%m-%Y') = DATE_FORMAT(ALL_DATE,'%m-%Y')
										WHERE ALL_DATE BETWEEN LOA_BEG_DTE AND LOA_END_DTE

										UNION ALL 

										SELECT ALL_DATE FROM 
										(SELECT ADDDATE('1970-01-01',T4.I*10000 + T3.I*1000 + T2.I*100 + T1.I*10 + T0.I) ALL_DATE FROM
										 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T0,
										 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T1,
										 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T2,
										 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T3,
										 (SELECT 0 I UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) T4) V
										LEFT OUTER JOIN 
											(
												SELECT TM_OFF_BEG_DTE,TM_OFF_END_DTE 
												FROM PAY.TM_OFF
												WHERE 
													DATE(TM_OFF_BEG_DTE)>='$PayBegDte'
													AND DATE(TM_OFF_END_DTE)<='$PayEndDte' 
													AND PRSN_NBR=$PrsnNbr
													AND DEL_NBR=0
													AND TM_OFF_F=1
											)TF ON DATE_FORMAT(TM_OFF_BEG_DTE,'%m-%Y') = DATE_FORMAT(ALL_DATE,'%m-%Y')
										WHERE ALL_DATE BETWEEN TM_OFF_BEG_DTE AND TM_OFF_END_DTE
									)ABSEN 
									ORDER BY DATE_IN ASC
								)
						ORDER BY ALL_DATE DESC
						LIMIT ".$absence;
		$result_abs = mysql_query($query_abs, $local);
		
		while ($row_abs=mysql_fetch_array($result_abs)) {
			$date_abs[] = $row_abs['ALL_DATE'];
		}
		$displayAbsence="";//print_r($query_abs);
	}
	
}

function GetRangesDate( $aDates ) {
	$aDates = array_unique( $aDates );
	sort( $aDates );
	$aGroups = array();
	for( $i = 0; $i < count( $aDates ); $i++ ) {
		if( $i > 0 && ( $aDates[$i-1] == date('Y-m-d',strtotime('-1 day',strtotime($aDates[$i]))) ))
			array_push( $aGroups[count($aGroups)-1], $aDates[$i] );
		else
			array_push( $aGroups, array( $aDates[$i] )); 
	}
	$aRanges = array();
	foreach( $aGroups as $aGroup ) {
		if( count( $aGroup ) == 1 )
			$aRanges[] =array('TM_OFF_BEG_DTE' => $aGroup[0], 'TM_OFF_END_DTE' => $aGroup[0]); 
		else
			$aRanges[] =array('TM_OFF_BEG_DTE' => $aGroup[0], 'TM_OFF_END_DTE' => $aGroup[count($aGroup)-1]);
		}
	return $aRanges;
}

//============================================================================================================
//Proses input absence pada time off
// ===========================================================================================================
if ($absF==1){
	foreach (GetRangesDate($date_abs) as $date) {
		if ($date['TM_OFF_BEG_DTE']!=''){
			$querys_tm_off = "SELECT COALESCE(MAX(TM_OFF_NBR)+1,0) AS NEW_NBR FROM $PAY.TM_OFF";
			$results_tm_off= mysql_query($querys_tm_off,$cloud);
			$rowNbr_tm_off = mysql_fetch_array($results_tm_off);

			$timeOffNbr = $rowNbr_tm_off['NEW_NBR'];

			$queryIn= "INSERT INTO $PAY.TM_OFF
							(TM_OFF_NBR, PRSN_NBR, TM_OFF_BEG_DTE, TM_OFF_END_DTE, TM_OFF_RSN, TM_OFF_F, CRT_TS,CRT_NBR,UPD_TS,UPD_NBR)
							VALUES (
								".$timeOffNbr.",
								".$PrsnNbr.",
								'".$date['TM_OFF_BEG_DTE']."',
								'".$date['TM_OFF_END_DTE']."',
								'Tidak ada kehadiran',
								1,
								CURRENT_TIMESTAMP,
								3,
								CURRENT_TIMESTAMP,
								3
							)";
			$resultsIn = mysql_query($queryIn,$cloud);
			$queryIn   = str_replace($PAY,"PAY",$queryIn);
			$resultsIn = mysql_query($queryIn,$local);
			echo "<script>location='payroll-edit.php?PRSN_NBR=".$PrsnNbr."&CO_NBR=".$CoNbr."';</script>";
			$absF=0;
		}
	}
}

//Check Peer to Peer and Pnlty
$queryPeer 		= "SELECT 
						CRT_NBR, PEER_TYP
					FROM PAY.PEER_FORM 
					WHERE 
						PEER_APV_F=1 
						AND DEL_NBR=0 
						AND PEER_DTE >='$PayBegDte'
						AND PEER_DTE <='$PayEndDte'
						AND PRSN_NBR=".$PrsnNbr."
					GROUP BY PEER_TYP";
$resultPeer 	= mysql_query($queryPeer);
while ($rowPeer = mysql_fetch_array($resultPeer)) {
	if ($rowPeer['PEER_TYP']==1){
		$peerToPeer = $rowPeer['CRT_NBR'];
	}else if ($rowPeer['PEER_TYP']==2){
		$peerPnlty 	= $rowPeer['CRT_NBR'];
	}
}



if ($rowDsb['CRDT_PRNC']!=''){
	$DedDefCrdt = round($rowDsb['CRDT_AMT']/$rowDsb['PYMT_NBR']);
}

//Mendapatkan perhitungan Marketing Performance Reward
include 'payroll-reward.php';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<link rel="stylesheet" href="framework/combobox/chosen.css"/>
<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />
<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />

<script type="text/javascript" src="framework/liveSearch/livepop.js"></script>
<script type="text/javascript" src="framework/functions/default.js"></script>
<script type="text/javascript" src="framework/liveSearch/livepop.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
<script src="framework/database/jquery.min.js"></script>
<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript" src="js/payroll-date-up-down.js"></script>
<script type="text/javascript">
	$.noConflict();
	jQuery(document).ready(function () {
        jQuery('.chosen-select').chosen();		
    });
	window.addEvent('domready', function() {
	//Datepicker
	new CalendarEightysix('textbox-id');
	//Calendar
	new CalendarEightysix('block-element-id');
	});
	MooTools.lang.set('id-ID', 'Date', {
		months:    ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
		days:      ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
		dateOrder: ['date', 'month', 'year', '/']
	});
	MooTools.lang.setLanguage('id-ID');
</script>
<script type="text/javascript">
	function checkP2p(){
		var p2p = document.getElementById('P2P').checked;
		
		if(p2p){			
			jQuery('#combo-peer').prop('disabled', false).trigger("chosen:updated");
		}else{			
			jQuery('#combo-peer').val(null).trigger("chosen:updated");
			jQuery('#combo-peer').prop('disabled',true).trigger("chosen:updated");
		}
	}

	function checkPnlty(){
		var p2p = document.getElementById('PNLTY').checked;
		
		if(p2p){			
			jQuery('#combo-peer-pnlty').prop('disabled', false).trigger("chosen:updated");
		}else{			
			jQuery('#combo-peer-pnlty').val(null).trigger("chosen:updated");
			jQuery('#combo-peer-pnlty').prop('disabled',true).trigger("chosen:updated");
		}
	}
</script>
<script type="text/javascript">
	function calcTrvl(){
		<?php
		$query	= "SELECT REM_TYPE,REM_LMT,REM_AMT FROM REM_SCHED";	
		$result	= mysql_query($query);
		while($row = mysql_fetch_array($result)){
			echo "if(document.getElementById('REM_TYPE').value == '".$row['REM_TYPE']."'){ \n";
				echo "document.getElementById('AUTH_TRVL_AMT').value=Math.ceil(document.getElementById('TOT_DIST').value*(1+".$row['REM_AMT'].")); \n";
			echo "}\n";
			echo "if(document.getElementById('REM_TYPE').value == ''){ \n";
				echo "document.getElementById('AUTH_TRVL_AMT').value=0; \n";
			echo "}\n";
		}
		?>
		calcPay();
	}
</script>
<script type="text/javascript">
	function calcLtr(){
		//$PayBasePar$PayAddPar
		<?php
		$query	= "SELECT RPRMN_NBR,RPRMN_DESC, RPRMN_PCT FROM PAY.RPRMN";	
		$result	= mysql_query($query);
		while($row = mysql_fetch_array($result)){
			echo "if(document.getElementById('LTR_RPRMN').value == '".$row['RPRMN_NBR']."'){ \n";
				echo "document.getElementById('LTR_RPRMN_AMT').value=Math.ceil(".$row['RPRMN_PCT']."/100*(".$PayBasePar."+".$PayAddPar.")); \n";
				echo "document.getElementById('LTR_RPRMN_PCT').value=".$row['RPRMN_PCT']."; \n";
			echo "}\n";

			echo "if(document.getElementById('LTR_RPRMN').value == ''){ \n";
				echo "document.getElementById('LTR_RPRMN_AMT').value=0; \n";
				echo "document.getElementById('LTR_RPRMN_PCT').value=0; \n";
			echo "}\n";
		}
		?>
		calcPay();
	}
</script>
<script type="text/javascript">
	function applyVal(sourceObj,destinationID)
	{
		document.getElementById(destinationID).value=sourceObj.value;
	}
	function applyAtt(checkObj)
	{
		if(checkObj.value=="on"){multi=1;}else{multi=0;}
		document.getElementById('BON_ATT_AMT').value=multi*document.getElementById('BASE_AMT').value+multi*document.getElementById('ADD_AMT').value;
		calcPay();
	}
	function getInt(objectID){
		if(document.getElementById(objectID).value=="")
		{
			return 0;
		}else{
			return parseInt(document.getElementById(objectID).value);
		}
	}

	function roundTo(number,to)
	{
		return Math.round(number/to)*to;
	}

	function calcPay()
	{
		var p2p 	= document.getElementById('P2P').checked;
		var HldF 	= document.getElementById('PAY_HLD_F').checked;
		var HldPdF 	= document.getElementById('PAY_HLD_PD_F').checked;
	    var peerVal = "<?php echo $peer_value;?>";
		var trvAmt 	= document.getElementById('AUTH_TRVL_AMT').value;
		var HldAmt 	= document.getElementById('PAY_HLD_AMT').value;
		var PdAmt 	= document.getElementById('PAY_HLD_TOT').value;
		var par 	= "<?php echo $rowPrm['PERF_INCT_PCT']; ?>";
		var max 	= roundTo(par/100*document.getElementById('BASE').value,5000);
		var pnlty 	= document.getElementById('PNLTY').checked;
		var pnltyVal= "<?php echo $pnlty_value;?>";
		var LtrAmt 	= document.getElementById('LTR_RPRMN_AMT').value;
		var ded 	= getInt('DED');
		var instll	= document.getElementById('Jasa_pmsngn').value;
		var mprc    = document.getElementById('PAY_RWD_PCT').value;


		document.getElementById('BASE_TOT').value=roundTo(document.getElementById('BASE_AMT').value*document.getElementById('BASE_CNT').value,5000);
		document.getElementById('BON_MO_AMT').value=roundTo(document.getElementById('BASE_TOT').value*document.getElementById('BON_PCT').value/100,5000);
		document.getElementById('ADD_TOT').value=roundTo(document.getElementById('ADD_AMT').value*document.getElementById('ADD_CNT').value,5000);
		document.getElementById('OT_TOT').value=roundTo(document.getElementById('OT_AMT').value*document.getElementById('OT_CNT').value,5000);
		document.getElementById('STY_TOT_AMT').value=roundTo(document.getElementById('STY_AMT').value*document.getElementById('STY_CNT').value,5000);
		document.getElementById('PAY_RWD_AMT').value=roundTo(document.getElementById('BASE_TOT').value*(document.getElementById('PAY_RWD_PCT').value/100),5000);

		
		var perf=document.getElementById('PERF_INCT').value*document.getElementById('PERF_INCT_AMT').value;
		if (perf>max){
			document.getElementById('PERF_INCT_TOT').value=max;
		} else if (perf<(max*-1)){
			document.getElementById('PERF_INCT_TOT').value=max*-1;
		} else {
			document.getElementById('PERF_INCT_TOT').value=perf;
		}
		
		document.getElementById('SUB_AMT').value=parseInt(document.getElementById('BASE_TOT').value)
							+parseInt(document.getElementById('ADD_TOT').value)
							+parseInt(document.getElementById('OT_TOT').value)
							+getInt('BON_MO_AMT')
							+getInt('CMSN_AMT')
							+parseInt(document.getElementById('PERF_INCT_TOT').value);
							

		document.getElementById('PAY_AMT').value=getInt('SUB_AMT')-getInt('DEBT_MO')+getInt('BON_SNG_AMT')-getInt('DED_SNG_AMT')+getInt('AUTH_TRVL_AMT')+getInt('STY_TOT_AMT')-getInt('CONTRB_AMT')-getInt('LTR_RPRMN_AMT')+getInt('DSBRS_CRDT');
		
		if(HldF){
			var totPay = parseInt(document.getElementById('BASE_TOT').value) + parseInt(document.getElementById('ADD_TOT').value);
			var PayHldDiv  = "<?php echo $PayHldDiv;?>";
			var payHldAmt = roundTo(totPay/parseInt(PayHldDiv),5000); // jumlah gaji ditahan 
			var HldAmtTot  = "<?php echo $HldAmtTot;?>"; // total gaji yang ditahan dr tabel PAY_HLD_LST
			var payTotMth  = "<?php echo $payTotMth;?>"; // total gaji 1 bulan PAY_BASE+PAY_ADD
			var depHldpay  = "<?php echo $payTotMth - $HldAmtTot ;?>"; //sisa gaji yang belum ditahan
			if (parseInt(HldAmtTot)<parseInt(payTotMth)){
				if (payHldAmt<=parseInt(depHldpay)){
					document.getElementById('PAY_HLD_AMT').value=payHldAmt;	
				}else{
					document.getElementById('PAY_HLD_AMT').value=depHldpay;
				}
			}else{
				document.getElementById('notif').innerHTML = "<img class='flat' style='border:0px;' src='img/error.png'>&nbsp;Gaji yang ditahan sudah terpenuhi";	
			}
		}else{
			document.getElementById('PAY_HLD_AMT').value=0;
			document.getElementById('notif').innerHTML = "";
		}
		
		var HldAmt = getInt('PAY_HLD_AMT');
		
		var deductionValues = 0;
		jQuery("input[id^='DED_AMT']").each(function(){
			var valAmt = jQuery(this).val();
			if (valAmt==''){valAmt=0;}
			deductionValues += parseInt(valAmt);
		});
		document.getElementById('DED_SNG_AMT').value=deductionValues;
		

		if (!p2p && HldF) {
			if (pnlty){
				document.getElementById('PAY_AMT').value = (getInt('SUB_AMT') - getInt('DEBT_MO') + getInt('BON_SNG_AMT') - getInt('DED_SNG_AMT') + getInt('AUTH_TRVL_AMT') + getInt('STY_TOT_AMT')-getInt('CONTRB_AMT')-getInt('LTR_RPRMN_AMT')+getInt('DSBRS_CRDT')+getInt('Jasa_pmsngn')) - pnltyVal - HldAmt;
			} else {
	        	document.getElementById('PAY_AMT').value = (getInt('SUB_AMT') - getInt('DEBT_MO') + getInt('BON_SNG_AMT') - getInt('DED_SNG_AMT') + getInt('AUTH_TRVL_AMT') + getInt('STY_TOT_AMT')- getInt('CONTRB_AMT')-getInt('LTR_RPRMN_AMT')+getInt('DSBRS_CRDT')+getInt('Jasa_pmsngn')) - peerVal - HldAmt;
			}
        } else if (!p2p && !HldF) {
        	if (pnlty){ 
        		document.getElementById('PAY_AMT').value = (getInt('SUB_AMT') - getInt('DEBT_MO') + getInt('BON_SNG_AMT') - getInt('DED_SNG_AMT') + getInt('AUTH_TRVL_AMT') + getInt('STY_TOT_AMT')-getInt('CONTRB_AMT') - getInt('LTR_RPRMN_AMT')+getInt('DSBRS_CRDT')+getInt('Jasa_pmsngn')) - pnltyVal;
        	} else {
				document.getElementById('PAY_AMT').value = (getInt('SUB_AMT') - getInt('DEBT_MO') + getInt('BON_SNG_AMT') - getInt('DED_SNG_AMT') + getInt('AUTH_TRVL_AMT') + getInt('STY_TOT_AMT')-getInt('CONTRB_AMT') - getInt('LTR_RPRMN_AMT')+getInt('DSBRS_CRDT')+getInt('Jasa_pmsngn')) - peerVal;
			}
		} else if (p2p && HldF) {
			if (pnlty){
				document.getElementById('PAY_AMT').value = (getInt('SUB_AMT') - getInt('DEBT_MO') + getInt('BON_SNG_AMT') - getInt('DED_SNG_AMT') + getInt('AUTH_TRVL_AMT') + getInt('STY_TOT_AMT')-getInt('CONTRB_AMT') - getInt('LTR_RPRMN_AMT')+getInt('DSBRS_CRDT')+getInt('Jasa_pmsngn')) - HldAmt  - pnltyVal;
			} else {
				document.getElementById('PAY_AMT').value = (getInt('SUB_AMT') - getInt('DEBT_MO') + getInt('BON_SNG_AMT') - getInt('DED_SNG_AMT') + getInt('AUTH_TRVL_AMT') + getInt('STY_TOT_AMT')-getInt('CONTRB_AMT') - getInt('LTR_RPRMN_AMT')+getInt('DSBRS_CRDT')+getInt('Jasa_pmsngn')) - HldAmt;
			}
		} else if (p2p && !HldF) {
			if (pnlty){
				document.getElementById('PAY_AMT').value = (getInt('SUB_AMT') - getInt('DEBT_MO') + getInt('BON_SNG_AMT') - getInt('DED_SNG_AMT') + getInt('AUTH_TRVL_AMT') + getInt('STY_TOT_AMT')-getInt('CONTRB_AMT') - getInt('LTR_RPRMN_AMT')+getInt('DSBRS_CRDT')+getInt('Jasa_pmsngn') - pnltyVal);
			} else{
				document.getElementById('PAY_AMT').value = (getInt('SUB_AMT') - getInt('DEBT_MO') + getInt('BON_SNG_AMT') - getInt('DED_SNG_AMT') + getInt('AUTH_TRVL_AMT') + getInt('STY_TOT_AMT')-getInt('CONTRB_AMT') - getInt('LTR_RPRMN_AMT')+getInt('DSBRS_CRDT')+getInt('Jasa_pmsngn'));
			}
		}
		
		if (HldPdF) {
			document.getElementById('PAY_AMT').value = getInt('PAY_AMT')+getInt('PAY_HLD_TOT');
		}

		var msg = "<img class='flat' style='border:0px;' src='img/error.png'>&nbsp;Gaji tidak boleh ditahan";
		var pay = document.getElementById('PAY_AMT').value;

		var absenceAllMonth = "<?php echo $totAbsence; ?>"; 
		
		if (parseInt(absenceAllMonth)>0){
			document.getElementById("AbsenStt").innerHTML="  Total absen "+absenceAllMonth+" Hari";
		}
	}

	function check(){
		var c2=document.getElementById('PAY_HLD_F'); //ditahan
		var c3=document.getElementById('PAY_HLD_PD_F'); //diberikan
		if (c2.checked) {
			document.getElementById("PAY_HLD_PD_F").disabled= true;
			document.getElementById("PAY_HLD_PD_F").checked= false;
		} else {
			document.getElementById("PAY_HLD_PD_F").disabled= false;
		}

		if (c3.checked){
			document.getElementById("PAY_HLD_F").disabled= true;
			document.getElementById("PAY_HLD_F").checked= false;
		} else {
			document.getElementById("PAY_HLD_F").disabled= false;
		}
	}

	function daysInMonth(iMonth,iYear)
	{
		return 32-new Date(iYear,iMonth, 32).getDate();
	}

	function recalcDays()
	{
		//document.getElementById('IN_DAYS').value=(parseInt(document.getElementById('PYMT_DAYS').value)||0)-(parseInt(document.getElementById('NBR_WORK_DAYS').value)||0)+(parseInt(document.getElementById('MACH_CLOK_DAY').value)||0)+(parseInt(document.getElementById('MAN_CLOK_DAY').value)||0);
		
		document.getElementById('IN_DAYS').value="<?php echo $InDays; ?>";
		
		//document.getElementById('OT_HOURS').value=(parseInt(document.getElementById('MACH_CLOK_HR').value)||0)-(parseInt(document.getElementById('MACH_CLOK_DAY').value)||0)*(parseInt(document.getElementById('NBR_WORK_HR_DAY').value)||0)+(parseInt(document.getElementById('MAN_CLOK_HR').value)||0);
		
		document.getElementById('OT_HOURS').value="<?php echo floor($OtHours); ?>";
		
		<?php
		
		$query_day = "SELECT 
			SUM(DATEDIFF(PD.PAY_END_DTE,PD.PAY_BEG_DTE) + 1) AS TOT_DAY
					FROM PAY.PAY_CONFIG_DTE PD
						WHERE PAY_ACT_F = 1
					";
						
		$result_day = mysql_query($query_day, $local);
		$row_day	= mysql_fetch_array($result_day);
					
		$days		= $row_day['TOT_DAY'];
		?>
		document.getElementById('PYMT_DAYS').value="<?php echo $days; ?>";
	
		console.log(<?php echo $days; ?>);
			//document.getElementById('PYMT_DAYS').value=daysInMonth(parseInt(document.getElementById('PYMT_DTE').value.substr(5,2))-1,parseInt(document.getElementById('PYMT_DTE').value.substr(0,4)));
			
					
		prevDays=document.getElementById('PYMT_DAYS').value;
		//alert(parseInt(document.getElementById('PYMT_DTE').value.substr(5,2))+parseInt(document.getElementById('PYMT_DTE').value.substr(0,4)));
		//document.getElementById('PYMT_DAYS').value=daysInMonth(parseInt(document.getElementById('PYMT_DTE').value.substr(5,2))-1,parseInt(document.getElementById('PYMT_DTE').value.substr(0,4)));
		
		document.getElementById('BASE_AMT').value=document.getElementById('BASE_AMT').value*prevDays/document.getElementById('PYMT_DAYS').value;
		document.getElementById('ADD_AMT').value=document.getElementById('ADD_AMT').value*prevDays/document.getElementById('PYMT_DAYS').value;
		calcPay();
	}

	<!--Batasan Bonus-->
	function truantBon(){
		bolos=document.getElementById('PYMT_DAYS').value-document.getElementById('IN_DAYS').value;
		sp   =document.getElementById('LTR_RPRMN_PCT').value;

		if(bolos<=4){
			document.getElementById('BON_PCT').value=document.getElementById('BON_PCT_BAK').value;
		}else{document.getElementById('BON_PCT').value=0;}

		if (bolos <=4 && sp >0){
			document.getElementById('PAY_RWD_PCT').value=document.getElementById('PAY_RWD_PCT_BAK').value;
			document.getElementById('CMSN_AMT').value=document.getElementById('CMSN_AMT_BAK').value;
		}else{
			 document.getElementById('PAY_RWD_PCT').value=0;
			document.getElementById('CMSN_AMT').value=0;
		}
	}
</script>
<script>
	function myFunction() {
		setTimeout(function () {
	        document.location.reload()
	    }, 100);
	}
</script>
<script type="text/javascript">
	function checkAbsence(){
		var absence       = "<?php echo $absence?>";
		var timeoffinYear = "<?php echo $timeoffinYear['CNT_TM_OFF'];?>";

		sisa_time_off     = 8-parseInt(timeoffinYear);

		if (absence>0 && timeoffinYear<8){
			window.scrollTo(0,0);
			parent.document.getElementById('absence').style.display='block';
			parent.document.getElementById('fade').style.display='block';
			var message="<span class='alert-title'>Konversi Absen </span><br/><br/>Pada karyawan tersebut memiliki "+absence+" hari absen dan memiliki sisa "+sisa_time_off+" hari cuti. Apakah Anda yakin untuk mengkoversikan absen tersebut ke cuti?";
			parent.document.getElementById('absence').getElementsByTagName("table")[0].getElementsByTagName("td")[1].innerHTML=message;
		}
	}
</script>
<script>
	parent.document.getElementById('absenceYes').onclick=
	function () { 
		parent.document.getElementById('content').src='payroll-edit.php?PRSN_NBR=<?php echo $PrsnNbr ?>&CO_NBR=<?php echo $CoNbr ?>&ABS_F=1';
		parent.document.getElementById('absence').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>
</head>

<body>
<div style="display:none;">
<button id="Refresh" onclick="myFunction()">Klik</button>
</div>

<script>
	parent.document.getElementById('payrollDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='<?php 	
		if($_GET['CO_PAY'] == "ALL") {
			echo "payroll-group.php";
		}else{
			echo "payroll.php";
		} ?>?CO_NBR=<?php if($_GET['CO_PAY'] == "ALL") { echo "ALL"; }else{ echo $CoNbr; } ?>&DEL=<?php echo $PrsnNbr; ?>&DATE=<?php echo $PymtDte; ?>';
		parent.document.getElementById('payrollDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>
<style type="text/css">
	.time-upDown{
		width:9px;
		float:right;
		font-size:8px;
		visibility:hidden;
		margin-right:1px;
	}
	.listUp:hover,.listDown:hover{
		background-color: #989898;
		color:#fff;
	}
	.HeadTab{
		width: 100%;
	}
	.CalLeft, .CalRight{
		width: 50%;
	}
	.TblLeft, .TblRight{
		width: 100%;
	}
</style>
<table class="submenu">
	<tr>
		<td class="submenu" style="background-color:">
			<?php
				$query 	= "SELECT PYMT_DTE
							FROM PAY.PAYROLL
							WHERE PRSN_NBR=".$PrsnNbr."
							AND DEL_NBR=0
							ORDER BY 1 DESC
							LIMIT 0,12";
				//echo $query;
				$result = mysql_query($query, $local);
				while($row=mysql_fetch_array($result))
				{
					echo "<a class='submenu' href='payroll-edit.php?PRSN_NBR=".$PrsnNbr."&PYMT_DTE=".$row['PYMT_DTE']."&CO_NBR=$CoNbr&CO_PAY=".$_GET['CO_PAY']."'><div class='";
					if($PymtDte==$row['PYMT_DTE']){echo "arrow_box";}else{echo "leftsubmenu";}
					echo "'>".$row['PYMT_DTE']."</div></a>";
				}
			?>	
		</td>
		<td class="subcontent">
			<?php if(($Security==0)&&($PymtDte!=0)) { ?>
				<div class="toolbar-only">
					<table class="toolbar">
						<tr>
							<td style="padding:0;margin:0;">
								<a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.document.getElementById('payrollDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class="fa fa-trash toolbar" style="cursor:pointer"></span></a>
							</td>
							<td align="right" style="padding:0;margin:0;">
								<a href="payroll-prn-dig-edit-print.php?PRSN_NBR=<?php echo $PrsnNbr; ?>&CONBR=<?php echo $_GET['CO_NBR']; ?>&PYMT_DTE=<?php echo $PymtDte; ?>&EMAIL=1"><span class="fa fa-paper-plane-o toolbar" style="cursor:pointer"></span></a> 
								<a href="payroll-prn-dig-edit-print.php?PRSN_NBR=<?php echo $PrsnNbr; ?>&CONBR=<?php echo $_GET['CO_NBR']; ?>&PYMT_DTE=<?php echo $PymtDte; ?>"><span class="fa fa-print toolbar" style="cursor:pointer"></span></a>
							</td>
						</tr>
					</table>
				</div>
			<?php } 
			
			if($PymtDte=="")
				{
					$query 	= "SELECT PPL.PRSN_NBR,NAME,POS_TYP,PPAY.PAY_TYP,PPAY.PAY_BASE,PPAY.PAY_ADD,PPAY.PAY_OT,PPAY.PAY_CONTRB,PPAY.PAY_MISC,PPAY.DED_DEF,HIRE_DTE
								FROM CMP.PEOPLE PPL 
								LEFT JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
								WHERE PPL.DEL_NBR=0 AND PPL.PRSN_NBR=".$PrsnNbr;
					
					
					$result = mysql_query($query, $local);
					$row 	= mysql_fetch_array($result);
					$PymtDte= date("Y-m-d H:i:s");
					
					$query_day = "SELECT 
							SUM(DATEDIFF(PD.PAY_END_DTE,PD.PAY_BEG_DTE) + 1) AS TOT_DAY
						FROM PAY.PAY_CONFIG_DTE PD
						WHERE PAY_ACT_F = 1
					";
						
					$result_day = mysql_query($query_day, $local);
					$row_day	= mysql_fetch_array($result_day);
					
					$days		= $row_day['TOT_DAY'];
					
				}else{
					//DEF_DED,CRDT_WK,CRDT_MO is still not used
					$query 	= "SELECT PAYR.PRSN_NBR
								,HIRE_DTE
								,PAY_HLD_F
								,PAY_HLD_PD_F
								,NAME
								,PYMT_DTE
								,PYMT_DAYS
								,BASE_AMT * PYMT_DAYS AS PAY_BASE
								,BASE_CNT
								,BASE_TOT
								,ADD_AMT * PYMT_DAYS AS PAY_ADD
								,ADD_CNT
								,ADD_TOT
								,OT_AMT AS PAY_OT
								,OT_CNT
								,OT_TOT
								,CONTRB_AMT
								,MISC_AMT
								,MISC_CNT
								,MISC_TOT
								,BON_ATT_AMT
								,BON_WK_AMT
								,BON_PCT
								,BON_MO_AMT
								,RPRMN_PCT
								,RPRMN_AMT
								,RPRMN_NBR
								,CRDT_WK
								,DEBT_WK AS DED_DEF
								,DEBT_MO
								,PAY_AMT
								,CRDT_AMT
								,PAYR.UPD_TS
								,PAYR.UPD_NBR
								,BON_SNG_AMT
								,DED_SNG_AMT
								,PERF_INCT
								,PERF_INCT_AMT
								,PERF_INCT_DESC
								,PEER_RWD_F
								,PEER_RWD_PRSN
								,TOT_DIST
								,REM_TYPE
								,AUTH_TRVL_AMT
								,PAY_HLD_AMT
								,STY_CNT
								,STY_TOT_AMT
								,PNLTY
								,PNLTY_PRSN
								,DED_DESC
								,DED_AMT
								,DSBRS_CRDT
								,PAY_RWD_PCT
								,PAY_RWD_AMT
							FROM PAY.PAYROLL PAYR
							INNER JOIN CMP.PEOPLE PPL ON PAYR.PRSN_NBR = PPL.PRSN_NBR
							WHERE PAYR.DEL_NBR = 0
								AND PAYR.PRSN_NBR = ".$PrsnNbr."
								AND PYMT_DTE = '".$PymtDte."'";
					$result = mysql_query($query, $local);
					$row 	= mysql_fetch_array($result);
					$days 	= $row['PYMT_DAYS'];

					if (!empty($row['DED_DESC'])) {
					    $row['DED_DESC'] = explode('+', $row['DED_DESC']);
					} else {
					    $row['DED_DESC'] = array(); 
					}
					$DedDescsQ = count($row['DED_DESC']);

					if (!empty($row['DED_AMT'])) {
					    $row['DED_AMT'] = explode('+', $row['DED_AMT']);
					} else {
					    $row['DED_AMT'] = array(); 
					}
					$DedAmtsQ = count($row['DED_AMT']);
				}
				
			?>
					
			<script type="text/javascript">
				window.onload = function () {
					var dedDescQ   = "<?php echo $DedDescsQ; ?>";
					var DedAmtQ    = "<?php echo $DedAmtsQ; ?>";
					var DedDescArr =  <?php echo json_encode($row['DED_DESC']); ?>;
					var DedAmtArr  =  <?php echo json_encode($row['DED_AMT']); ?>;
					
					for(i=1;i<=dedDescQ; i++){
						var num = i-1;
						if (i>1){
							document.getElementById('addButton').click();
						}
						document.getElementById('DED_DESC'+i).value=DedDescArr[num];
						document.getElementById('DED_AMT'+i).value=DedAmtArr[num];
						jQuery('#DED_DESC'+i).val(DedDescArr[num]).trigger("chosen:updated");
						
					}
					if (DedAmtQ=='' ||DedAmtQ==0){
						document.getElementById('DED_AMT1').value='';
						if (DedDescArr[parseInt(dedDescQ)-1]=='Kosong'){
							jQuery('#DED_DESC'+dedDescQ).val('').trigger("chosen:updated");
						}
					}else{
					document.getElementById('DED_DESC'+dedDescQ).value = DedDescArr[parseInt(dedDescQ)-1];
						document.getElementById('DED_AMT'+DedAmtQ).value = DedAmtArr[parseInt(DedAmtQ)-1];
						
						if (DedDescArr[parseInt(dedDescQ)-1]=='Kosong'){
							jQuery('#DED_DESC'+dedDescQ).val('').trigger("chosen:updated");
						}else{
							jQuery('#DED_DESC'+dedDescQ).val(DedDescArr[parseInt(dedDescQ)-1]).trigger("chosen:updated");
						}
					}
					
				};
					
			</script>		
			<form enctype="multipart/form-data" action="#" method="post" style="width:660px" onSubmit="return checkform();">
				<p>
					<h2>
						<?php echo $row['NAME'] ?>
					</h2>
					<h3>
						Perincian Gaji Karyawan Nomor Induk: <?php echo $row['PRSN_NBR'];if($row['PRSN_NBR']==""){echo "Nomor Baru";} ?>
					</h3>
					<br/>
					<!-- Calendar -->
					<?php 
						include  "payroll-calendar.php";
					?>
					<br/>
					
					<input name="PRSN_NBR" id="PRSN_NBR" value="<?php echo $row['PRSN_NBR']; ?>" type="hidden" />
					<input id="BASE" name="BASE" value="<?php echo $row['PAY_BASE']; ?>" type="hidden" />
					<input name="CRDT_DTE" value="<?php echo $row['CRDT_DTE']; ?>" type="hidden" />
					<input name="INST_NBR" value="<?php echo $row['INST_NBR']; ?>" type="hidden" />
					<input name="CRDT_PRNC" value="<?php echo $row['CRDT_PRNC']; ?>" type="hidden" />
					<input name="DED_DEF" value="<?php echo $row['DED_DEF']; ?>" type="hidden" />
					
					<table><?php echo $absensetot; ?>
						<?php if(($payTotMth-$HldAmtTot)>0){ ?>
						<tr><td colspan="2">
							<div class='print-digital-red' style='padding-left:8px;padding-right:8px;text-align:left;display:inline-block;width:582px;margin-top:2px;margin-bottom:4px'><b>Warning</b> -- Gaji yang ditahan belum terpenuhi. Payroll ini harus ditahan.</div>
						</td></tr>
						<?php } ?>
						<tr><td style='width:170px'>Tanggal gajian</td><td><input id="PYMT_DTE" name="PYMT_DTE" size="20" value="<?php echo $row['PYMT_DTE']; ?>"></input></td></tr>
						<script>
							new CalendarEightysix('PYMT_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
						</script>
						<tr>
							<td>Total absensi elektronik</td>
							<td colspan="2"><input id="MACH_CLOK_HR" size="5" value="<?php echo number_format($totHours,1); ?>"></input> jam dalam <input id="MACH_CLOK_DAY" size="5" value="<?php echo number_format($nbrDays,1); ?>"></input> hari</td>
						</tr>
						<tr>
							<td>Jumlah hari kerja</td>
							<td colspan="2"><input id="NBR_WORK_DAYS" size="5" value="<?php echo $total_hari_kerja;?>"></input> hari</td>
						</tr>
						<tr>
							<td>Jumlah jam kerja per hari</td>
							<td colspan="2"><input id="NBR_WORK_HR_DAY" size="5" value="8"></input> jam</td>
						</tr>
						<tr>
							<td>Total absensi manual</td>
							<td colspan="2"><input id="MAN_CLOK_HR" size="5" value=""> jam dan <input id="MAN_CLOK_DAY" size="5" value=""></input> hari</td>
						</tr>
						<tr style="height:10px"><td colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>
						<tr>
							<td>Masuk</td>
							<td colspan="2"><input id="IN_DAYS" size="5" onkeyup="applyVal(this,'BASE_CNT');applyVal(this,'ADD_CNT');truantBon();calcPay();calcTrvl();" value="<?php echo $row['BASE_CNT']; ?>"></input> hari dari total <input name="PYMT_DAYS" id="PYMT_DAYS" size="5" tabindex="-1" readonly value="<?php echo $days; ?>"></input> hari
							<div class='listable-btn' style='margin-left:2px'><span class='fa fa-refresh listable-btn' onclick="recalcDays();applyVal(document.getElementById('IN_DAYS'),'BASE_CNT');applyVal(document.getElementById('IN_DAYS'),'ADD_CNT');applyVal(document.getElementById('OT_HOURS'),'OT_CNT');truantBon();calcPay();calcTrvl();" ></span></div>
							</td>
						</tr>
						<tr>
							<td>Lembur</td>
							<td colspan="2"><input id='OT_HOURS' size="5" onkeyup="applyVal(this,'OT_CNT');calcPay();calcTrvl();" value="<?php echo $row['OT_CNT']; ?>"></input> jam</td>
						</tr>
						<?php echo $absensetot;?>
						<tr style="<?php echo $displayAbsence;?>">
							<td>Absen</td>
							<td colspan="2"><input id='ABSENCE' size="5" onkeyup="applyVal(this,'ABSENCE');calcPay();calcTrvl();" value="<?php echo $absence;?>" readonly ></input> hari
							<div class='listable-btn' style='margin-left:2px'><span class='fa fa-refresh listable-btn' onclick="checkAbsence()" ></span></div><span id="AbsenStt"></span>
							</td>
						</tr>
					</table>

					<table id="dataTable">
						<tr style="height:10px"><td colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>
						<?php
						if($days == 0) {	$PayBase	= 0; 	$PayAdd = 0; }
							else { 
								$PayBase	= round($row['PAY_BASE']/$days,0); 
								$PayAdd		= round($row['PAY_ADD']/$days,0); 
								
							}
						?>
						<tr <?php echo $hide;?>>
							<td>Gaji pokok</td>
							<td><input name="BASE_AMT" id="BASE_AMT" size="15" tabindex="-1" readonly onkeyup="calcPay();" value="<?php echo $PayBase; ?>"></input> X <input name="BASE_CNT" id="BASE_CNT" size="5" readonly tabindex="-1" onkeyup="calcPay();" value="<?php echo $row['BASE_CNT']; ?>"></input>&nbsp;</td>
							<td>= Rp. <input name="BASE_TOT" id='BASE_TOT' size="15" tabindex="-1" readonly onkeyup="calcPay();" value="<?php echo $row['BASE_TOT']; ?>"></td>
						</tr>

						<tr <?php echo $hide;?>><td>Bonus bulanan</td><td><input name="BON_PCT" id="BON_PCT" size="5" onkeyup="calcPay();" value="<?php if ($row['BON_PCT']!=""){echo $row['BON_PCT'];}else{echo $Bonus;} ?>"> %</td><td>= Rp. <input name="BON_MO_AMT" id="BON_MO_AMT" tabindex="-1" readonly size="15" onkeyup="calcPay();" value="<?php echo $row['BON_MO_AMT']; ?>"></td></tr>
						<!--Batasan Bonus-->
						<input name="BON_PCT_BAK" id="BON_PCT_BAK" size="5" type="hidden" onkeyup="calcPay();" value="<?php if ($row['BON_PCT']!=""){echo $row['BON_PCT'];}else{echo $Bonus;} ?>">

						<!-- Bonus Marketing Performance Reward-->
						<tr <?php echo $hide;?>>
							<td>Bonus Marketing Performance</td>
							<td>
								<input name="PAY_RWD_PCT" id="PAY_RWD_PCT" value="<?php if ($row['PAY_RWD_PCT'] !=''){echo $row['PAY_RWD_PCT'];}else{echo $Rwd;}?>" size="5" onkeyup="calcPay();"> %
							</td>
							<td>= Rp. 
								<input name="PAY_RWD_AMT" id="PAY_RWD_AMT" tabindex="-1" readonly size="15" onkeyup="calcPay();" value="<?php echo $row['PAY_RWD_AMT']; ?>">
							</td>
						</tr>
						<!--Batasan Bonus-->
						<input name="PAY_RWD_PCT_BAK" id="PAY_RWD_PCT_BAK" size="5" type="hidden" onkeyup="calcPay();" value="<?php if ($row['PAY_RWD_PCT']!=""){echo $row['PAY_RWD_PCT'];}else{echo $Rwd;} ?>">
<!--                    Tambahan Komisi Broker-->
                    <tr>
                        <td>Komisi</td>
                        <td>
                            <?php
                            // $bulan = date('m', strtotime($PymtDte));
                            // $tahun = date('Y', strtotime($PymtDte));

                            // $komisi_print = json_decode(calcKomisiPrint(date('m', strtotime($PymtDte)), date('Y', strtotime($PymtDte)), $PrsnNbr));
                            // $komisi_retail = json_decode(calcKomisiRetail(date('m', strtotime($PymtDte)), date('Y', strtotime($PymtDte)), $PrsnNbr));
                            // $komisi_sales = json_decode(calcKomisiSales(date('m', strtotime($PymtDte)), date('Y', strtotime($PymtDte)), $PrsnNbr));

                            // $total_komisi = 0;
                            // if (sizeof($komisi_print) > 0) {
                            //     foreach ($komisi_print as $key => $value) {
                            //         $total_komisi += $value->KOMISI;
                            //     }
                            // } else if (sizeof($komisi_retail) > 0) {
                            //     foreach ($komisi_retail as $key => $value) {
                            //         $total_komisi += $value->KOMISI;
                            //     }
                            // } else if (sizeof($komisi_sales) > 0) {
                            //     foreach ($komisi_sales as $key => $value) {
                            //         $total_komisi += $value->KOMISI;
                            //     }
                            // }

                            $total_komisi = ComissionGoods($PrsnNbr, $PayConfigNbr);
                            ?>
                        </td>
                        <td>= Rp. <input name="CMSN_AMT" id="CMSN_AMT" size="15" readonly tabindex="-1"
                                         value="<?php if ($row['CMSN_AMT']!=""){echo $row['CMSN_AMT'];}else{echo $total_komisi;} ?>" 
                                         onkeyup="calcPay();"/>
                            <!--Batasan Bonus-->
							<input name="CMSN_AMT_BAK" id="CMSN_AMT_BAK" size="5" type="hidden" onkeyup="calcPay();"
								   value="<?php if ($row['CMSN_AMT']!=""){echo $row['CMSN_AMT'];}else{echo $total_komisi;} ?>">
                        </td>
                    </tr>
<!--                    End Tambahan Komisi Broker-->
						<tr <?php echo $hide;?>>
							<td>Gaji lembur&nbsp;</td>
							<td><input name="OT_AMT" id="OT_AMT" size="15" tabindex="-1" readonly onkeyup="calcPay();" value="<?php echo $row['PAY_OT']; ?>"></input> X <input name="OT_CNT" id="OT_CNT" size="5" readonly tabindex="-1" onkeyup="calcPay();" value="<?php echo $row['OT_CNT']; ?>"></input>&nbsp;</td>
							<td>= Rp. <input name="OT_TOT" id="OT_TOT" size="15" tabindex="-1" readonly onkeyup="calcPay();" value="<?php echo $row['OT_TOT']; ?>"></td>
						</tr>
						
						<tr <?php echo $hide;?>>
							<td>Gaji tambahan</td>
							<td><input name="ADD_AMT" id="ADD_AMT" size="15" tabindex="-1" readonly onkeyup="calcPay();" value="<?php echo $PayAdd; ?>"></input> X <input name="ADD_CNT" id="ADD_CNT" size="5" tabindex="-1" readonly onkeyup="calcPay();" value="<?php echo $row['ADD_CNT']; ?>"></input>&nbsp;</td>
							<td>= Rp. <input name="ADD_TOT" id="ADD_TOT" size="15" tabindex="-1" readonly onkeyup="calcPay();" value="<?php echo $row['ADD_TOT']; ?>"></td>
						</tr>

						<tr <?php echo $hide;?>><td align="right" colspan="2"><strong>Jumlah&nbsp;</strong></td><td>= Rp. <input size="15" id="SUB_AMT" readonly tabindex="-1" value="<?php echo $row['BASE_TOT']+$row['BON_MO_AMT']+$row['OT_TOT']+$row['ADD_TOT']; ?>"></td></tr>

						<tr <?php echo $hide;?>><td>Gaji Kontribusi</td><td></td><td>= Rp. <input name="CONTRB_AMT" id="CONTRB_AMT" size="15" onkeyup="calcPay();" value="<?php if($row['CONTRB_AMT']!=''){echo $row['CONTRB_AMT'];} else { echo $PayContrb; } ?>" readonly></td></tr>
			
						<tr <?php echo $hide;?>><td>Cicilan Bon</td><td></td><td>= Rp. <input name="DEBT_MO" id="DEBT_MO" size="15" onkeyup="calcPay();" value="<?php 
						
						if($crdtPrsnc > 0)
						{
							echo '0';
						}
						else 
						{
							if($row['PYMT_DTE']=='') {
								if($RemCrdt<='0') { echo '0'; }
									else {
										if($row['DEBT_MO']!='') { echo $row['DEBT_MO']; }
											else { if($RemCrdt > $row['DED_DEF']) { echo $row['DED_DEF']; }
												else { echo $RemCrdt; }
												}
										}
							} 
							else
							{
							echo $row['DEBT_MO'];
							} 
						}
						
						?>"></td></tr>
						
					
						<tr <?php echo $hide;?>><td>Bonus</td><td></td><td>= Rp. <input name="BON_SNG_AMT" id="BON_SNG_AMT" size="15" onkeyup="calcPay();" value="<?php if($row['BON_SNG_AMT']!=''){echo $row['BON_SNG_AMT'];}else{echo '0'; }?>"></td></tr>
						<tr>
							<td>Pencairan Bon</td>
							<td></td>
							<td>= Rp. 
								<input name="DSBRS_CRDT" id="DSBRS_CRDT" size="15" onkeyup="calcPay();" 
									value="<?php if($row['DSBRS_CRDT']!=''){echo $row['DSBRS_CRDT'];}
													else{ if($crdtPrsnc!=''){ echo $crdtPrsnc;}else{echo '0';} }?>">
								<input name="DED_DEF_CRDT" id="DED_DEF_CRDT" size="15" value="<?php if($DedDefCrdt!=''){echo $DedDefCrdt;}else{echo '0'; }?>" type="hidden" >
							</td>
						</tr>

						<tr <?php echo $hide;?>>
						<td>Jasa Pemasangan</td>
						<td></td>
							<td>= Rp. 
								<input name="Jasa_pmsngn" id="Jasa_pmsngn" size="15" onkeyup="calcPay();" value="<?php if($totamt!=''){ echo $totamt;}else{echo '0';} ?>">
								<!--
								<input name="Jasa_pmsngn" id="Jasa_pmsngn" size="15" value="<?php if($totamt!=''){echo $totamt;}else{echo '0'; }?>" type="hidden" >
								-->
							</td>
						</tr>

						<tr>
							<td><b>Potongan</b> 
								<div  class="listable-btn" style="margin-left:2px;cursor:pointer;" id='addButton'>
									<span class='fa fa-plus listable-btn' style="margin-top: 1px;"  title="Add New"></span>
								</div>
							</td>
						</tr>
						<tr id="RowDed">
							<td colspan="2" id="TextBoxesGroup">
								<!-- <div id='TextBoxesGroup' style="margin-left: -1px;"> -->
									<div id="TextBoxDiv" style="width: 100%;">
										<div style="float: left;width: 46%;">
											<label style="padding-top: 0px;">Potongan</label>
										</div>
										<div style="float: left;width: 105px;-webkit-padding-start:10px;-webkit-padding-start:5px;">
											<select name="DED_DESC1" class="chosen-select" id="DED_DESC1" style="width: 100px;">
											<?php
												$potonganArray = array("Kesalahan","Cetak","Retail");
												genComboArrayVal2($potonganArray, $row['DED_DESC'],'Kosong');
											?>
											</select>
										</div>
										<div style="float: left;">
											X <input name="DED_AMT1" id="DED_AMT1" size="11" onkeyup="calcPay();" onchange="calcPay();">
										</div>
										<div class="listable-btn" id='removeButton1' style="float:left;margin-left:2px;cursor:pointer;margin-top: 5px;">
											<span class='fa fa-trash listable-btn' style="margin-top: 1px;" title="Remove" onclick="removePot('')"></span>
										</div>
									</div>
								<!-- </div> -->
							</td>
							<input name='DED' id='DED' type="hidden" size="5" value="1" />
							<input name="TOT_NET_DED" id="TOT_NET_DED" type="hidden" size="10" value="0" />
							<div style="clear:both"></div>
							
						</tr>
						<tr ><td colspan="2" style="text-align: right;"><b>Total Potongan</b></td><td>= Rp. <input name="DED_SNG_AMT" id="DED_SNG_AMT" size="15" onkeyup="calcPay();" value="<?php if($row['DED_SNG_AMT']!=''){echo $row['DED_SNG_AMT'];}else{echo '0';} ?>"></td></tr>
						<tr>
							<td>Surat Peringatan</td>
							<td>
								<select name="LTR_RPRMN" class="chosen-select" id="LTR_RPRMN" style="width: 200px;" onkeyup="calcPay();" onchange="calcLtr();" >
									<?php
									$query="SELECT RPRMN_NBR,RPRMN_DESC FROM PAY.RPRMN";
									genCombo($query, "RPRMN_NBR","RPRMN_DESC",$row['RPRMN_NBR'], "Kosong");
									?>
								</select>
							</td>
							<td>= Rp. <input name="LTR_RPRMN_AMT" id="LTR_RPRMN_AMT" size="15" onkeyup="calcPay();" value="<?php if($row['RPRMN_AMT']!=''){echo $row['RPRMN_AMT'];}else{echo '0';} ?>">

							<input type="hidden" name="LTR_RPRMN_PCT" id="LTR_RPRMN_PCT" value="<?php if($row['RPRMN_PCT']!=''){echo $row['RPRMN_PCT'];}else{echo '0';} ?>">
							</td>
						</tr>
						
						<tr>
							<td>Transportasi</td>
							<td><input name="TOT_DIST" id="TOT_DIST" size="5" onkeyup="calcTrvl();calcPay();" onChange="calcTrvl();" value="<?php if($row['AUTH_TRVL_AMT']!=''){echo $row['TOT_DIST'];}else{echo $TrvlAmt;} ?>"> X 
								<?php
								if($row['REM_TYPE']!=0){
									$disabled = "";
								}else{
									$disabled = "disabled";
								}
								?>
								<select name="REM_TYPE" class="chosen-select" id="REM_TYPE" style="width: 100px;" onkeyup="calcTrvl();calcPay();" onChange="calcTrvl();">
									<?php
									if($TrvlAmt !='' && $row['AUTH_TRVL_AMT'] == ""){ $remType = "MTC";}elseif($row['AUTH_TRVL_AMT']!= 0){$remType = $row['REM_TYPE'];}else{ $remType = "";}
									$query="SELECT REM_TYPE,REM_DESC,REM_LMT,REM_AMT FROM CMP.REM_SCHED";
									genCombo($query, "REM_TYPE","REM_DESC", $remType, "Kosong");
									?>
								</select>
							</td>
							<td>= Rp. <input name="AUTH_TRVL_AMT" id="AUTH_TRVL_AMT" size="15" onkeyup="calcPay();" onChange="calcPay();" value="<?php if($row['AUTH_TRVL_AMT']!=''){echo $row['AUTH_TRVL_AMT'];}else{echo '0';} ?>"></input></td>
						</tr>
						
						<tr>
							<td>Menginap</td>
							<td><input name="STY_AMT" id="STY_AMT" size="15" tabindex="-1" readonly onkeyup="calcPay();" value="<?php echo $stayAmount; ?>"></input> X <input name="STY_CNT" id="STY_CNT" size="5" tabindex="-1" onkeyup="calcPay();" value="<?php echo $row['STY_CNT']; ?>"></input>&nbsp;</td>
							<td>= Rp. <input name="STY_TOT_AMT" id="STY_TOT_AMT" size="15" tabindex="-1" readonly onkeyup="calcPay();" value="<?php echo $row['STY_TOT_AMT']; ?>"></td>
						</tr>

						<tr>
                        <td>Peer to peer</td>
                        <td>
                            <?php
                            $p2p = $row['PEER_RWD_F'];
                            if ($p2p == 1) {
                                $check = 'checked=""';
                            } else {
                                $check = '';
                                if ($peerToPeer!=''){
	                            $check = 'checked=""';
	                        }
                            }
                            ?>
                            <input name='PEER_RWD' id='P2P' type='checkbox' class='regular-checkbox'
                                   onchange="calcPay();checkP2p();" <?php echo $check; ?>/><label for="P2P"></label>
                        </td>
						</tr>
						<tr>
							<td>Oleh</td>
							<td>
							<?php
							if($row['PEER_RWD_PRSN']!=0){
								$disabled = "";
							}else{
								$disabled = "disabled";
								if ($peerToPeer!=''){
	                                 				$disabled = "";
	                            				}
							}
							?>
                            <select name="PEER_PRSN" class="chosen-select" id="combo-peer" style="width: 200px;" <?php echo $disabled;?>>
                                <?php
                                if ($row['PEER_RWD_PRSN']=='' || $row['PEER_RWD_PRSN']==0){
	                                if ($peerToPeer!=''){
	                                 	$peerToPeerNbr= $peerToPeer;
	                                }
	                            } else{
	                                $peerToPeerNbr=$row['PEER_RWD_PRSN'];
	                            }
                                $query="SELECT PPL.PRSN_NBR
											,NAME
											,POS_DESC
											,MAX(PYMT_DTE) AS PYMT_DTE
										FROM CMP.PEOPLE PPL
										LEFT JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
										INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP = POS.POS_TYP
										LEFT OUTER JOIN PAY.PAYROLL PAY ON PPL.PRSN_NBR = PAY.PRSN_NBR
										WHERE TERM_DTE IS NULL
											AND PPAY.PAY_TYP = 'MON'
											AND CO_NBR IN (
												SELECT CO_NBR
												FROM NST.PARAM_PAYROLL
												)
											AND PPL.DEL_NBR = 0
											AND (
												PAY.DEL_NBR = 0
												OR PAY.DEL_NBR IS NULL
												)
										GROUP BY PPL.PRSN_NBR
											,NAME
											,POS_DESC
										ORDER BY 2";
                                genCombo($query, "PRSN_NBR", "NAME", $peerToPeerNbr, "Kosong");
                                ?>
                            </select>
							</td>
						</tr>

						<tr>
                        <td>Peer Penalty</td>
                        <td>
                            <?php
                            $pnlty = $row['PNLTY_PRSN'];
                            if ($pnlty !=0) {
                                $check = 'checked=""';
                            } else {
                                $check = '';
				if ($peerPnlty!=''){
                                	$check = 'checked=""';
                                }
                            }
                            ?>
                            <input name='PNLTY' id='PNLTY' type='checkbox' class='regular-checkbox'
                                   onchange="calcPay();checkPnlty();" <?php echo $check; ?>/><label for="PNLTY"></label>
                        </td>
						</tr>
						<tr>
							<td>Oleh</td>
							<td>
							<?php
							if($row['PNLTY_PRSN']!=0){
								$disabled = "";
							}else{
								$disabled = "disabled";
								if ($peerPnlty!=''){
                                					$disabled = "";
                                				}
							}
							?>
                            <select name="PNLTY_PRSN" class="chosen-select" id="combo-peer-pnlty" style="width: 200px;" <?php echo $disabled;?>>
                                <?php
                                 if ($row['PNLTY_PRSN']=='' || $row['PNLTY_PRSN']==0){
                                 	if ($peerPnlty!=''){
                                 		$peerPnltyNbr= $peerPnlty;
                                 	}
                                 }else{
                                 	$peerPnltyNbr=$row['PNLTY_PRSN'];
                                 }

                                $query="SELECT PPL.PRSN_NBR
											,NAME
											,POS_DESC
											,MAX(PYMT_DTE) AS PYMT_DTE
										FROM CMP.PEOPLE PPL
										LEFT JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
										INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP = POS.POS_TYP
										LEFT OUTER JOIN PAY.PAYROLL PAY ON PPL.PRSN_NBR = PAY.PRSN_NBR
										WHERE TERM_DTE IS NULL
											AND PPAY.PAY_TYP = 'MON'
											AND CO_NBR IN (
												SELECT CO_NBR
												FROM NST.PARAM_PAYROLL
												)
											AND PPL.DEL_NBR = 0
											AND (
												PAY.DEL_NBR = 0
												OR PAY.DEL_NBR IS NULL
												)
										GROUP BY PPL.PRSN_NBR
											,NAME
											,POS_DESC
										ORDER BY 2";
                                genCombo($query, "PRSN_NBR", "NAME", $peerPnltyNbr, "Kosong");
                                ?>
                            </select>
							</td>
						</tr>

						<tr <?php echo $hide;?> style="height:10px"><td colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>
						
						<tr <?php echo $hide;?>>
							<td>Performance Incentive</td>
							<td><input type="hidden" name="PERF_INCT_AMT" id="PERF_INCT_AMT" size="15" tabindex="-1" readonly onkeyup="calcPay();" value="<?php echo $rowPrm['PERF_INCT_AMT']; ?>">
							</input><input name="PERF_INCT" id="PERF_INCT" size="5" tabindex="-1"  onkeyup="calcPay();" value="<?php echo $row['PERF_INCT']; ?>"></input>&nbsp;</td>
							<td>= Rp. <input name="PERF_INCT_TOT" id="PERF_INCT_TOT" size="15" tabindex="-1" readonly onkeyup="calcPay();" value="<?php echo $row['PERF_INCT_AMT']; ?>"></td>
						</tr>
						<tr <?php echo $hide;?>>
							<td>Deskripsi Performance Incentive</td>
							<td colspan="2"><input name="PERF_INCT_DESC" id="PERF_INCT_DESC" size="56" tabindex="-1" value="<?php echo $row['PERF_INCT_DESC']; ?>">
						</tr>
						<tr <?php echo $hide;?> style="height:10px"><td colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>
						
						<tr>
                        <td>Gaji Ditahan</td>
                        <td>
                            <?php
							$HoldDiff = ($PayBasePar+$PayAddPar)/$PayHldDiv; //jumlah gaji yg ditahan
							if ($row['PAY_HLD_F']==1){
								$Val2 = 1;
							} else if ($row['PAY_HLD_F']==0){
								$Val2 = 0;
							} else {
								if ($HoldVal == 0){ //jika masa kerjanya > 3 bulan
									$Val2 = 0;
								} else if (($HoldVal == 1)&&($rowJum['TOT_LIST']<3)){ //jika masa kerjanya < 3 bulan dan gaji yg di hold < 3 kali
									$Val2 = 1;
								}
							}
							
							//hold otomatis
							if (($HoldVal == 1)&&($rowJum['TOT_LIST']<3)){ //jika masa kerjanya < 3 bulan dan gaji yg di hold < 3 kali
								$Val2 = 1;
							}
							

                            if ($Val2 == 1) {
                                $check2 = 'checked=""';
                            } else {
                                $check2 = '';
                            }
                            
                            ?>
							<input name='PAY_HLD_F' id='PAY_HLD_F' type='checkbox' class='regular-checkbox' onchange="calcPay();check();" <?php echo $check2; ?>/><label for="PAY_HLD_F"></label>
							<span id="notif"></span>
							<input type='hidden' name='TIME_WORK' id='TIME_WORK' value='<?php echo $HoldVal; ?>'/>
							<input type='hidden' name='PAY_HLD_AMT' id='PAY_HLD_AMT' value='<?php echo $row['PAY_HLD_AMT']; ?>'/>
                        </td>
						</tr>
						<tr>
                        <td>Gaji Diberikan</td>
                        <td>
                            <?php
							$query_pd 	= "SELECT SUM(PAY_HLD_AMT) AS PAY_HLD_TOT FROM PAY.PAY_HLD_LST WHERE PRSN_NBR='$PrsnNbr' AND PAY_HLD_TYP='1' AND DEL_NBR='0'";
							$result_pd 	= mysql_query($query_pd);
							$row_pd 	= mysql_fetch_array($result_pd);
							$PayHldTot 	= $row_pd['PAY_HLD_TOT'];
							//echo $query_pd;
                            $Val3 = $row['PAY_HLD_PD_F'];
                            if ($Val3 == 1) {
                                $check3 = 'checked=""';
                            } else {
                                $check3 = '';
                            }
                            ?>
							<input name='PAY_HLD_PD_F' id='PAY_HLD_PD_F' type='checkbox' class='regular-checkbox' onchange='calcPay();check();' <?php echo $check3; ?>/><label for="PAY_HLD_PD_F"></label>
							<input type='hidden' name='PAY_HLD_TOT' id='PAY_HLD_TOT' value='<?php echo $PayHldTot; ?>'/>
                        </td>
						</tr>						
						
						<tr <?php echo $hide;?> style="height:10px"><td colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>
						<tr <?php echo $hide;?>><td align="right" colspan="2"><strong>Total&nbsp;</strong></td><td>= Rp. <input name="PAY_AMT" id="PAY_AMT" size="15" readonly tabindex="-1" value="<?php echo $row['PAY_AMT']; ?>"></td></tr>
						</span>
					</table>
					<table>
						<?php if ($Security<=2 && ($upperSecurity<=5 || $Finance<=1)) { ?>
						<tr style="std"><td colspan="3"><input class="process" type="submit" value="Simpan"/><div></div></td></tr>	
						<?php } ?>
						</tr>
					</table>		
				</p>
			</form>

		</td>
	</tr>
</table>	
<script type="text/javascript">
		jQuery(document).ready(function()
		{
	    	var counter = parseInt(document.getElementById('DED').value)+1;
	    	var CntPot  = parseInt('<?php echo count($potonganArray);?>');

		  	jQuery("#addButton").click(function () {
		  		var length  = parseInt(jQuery("input[id^='DED_AMT']").length)+1;
		  		var counter = parseInt(document.getElementById('DED').value)+1;
		  		
				if(length>CntPot){
			        return false;
				}
			
				var genComboArrayVal = "<?php echo genComboArrayVal($potonganArray, $row['DED_DESC'],'Kosong');?>";
				var newTextBoxDiv  = jQuery(document.createElement('div'))
				     .attr("id", 'TextBoxDiv' + counter);
				newTextBoxDiv.css({"width":"100%","padding-top":"10px"});
				newTextBoxDiv.after().html('<br/>'+
					'</div>'
					+'<div style="float: left;width: 46%;"><label>Potongan </label></div>' +
					'<div style="float: left;width: 105px;-webkit-padding-start:10px;-webkit-padding-start:5px;"><select name="DED_DESC'+counter+'" class="chosen" id="DED_DESC'+counter+'" style="width: 100px;">'+genComboArrayVal+
					'</select></div>'+
				      '<div style="float: left;width: 95px;">X <input type="text" size="11" name="DED_AMT' + counter + 
				      '" id="DED_AMT' + counter + '" onkeyup="calcPay();" onchange="calcPay();">'+
				      '<div class="listable-btn removeButton" id="removeButton'+counter+'" style="float:left;margin-left:2px;cursor:pointer;margin-top:5px;position: absolute;" onclick="removePot('+counter+')">'+
					'<span class="fa fa-trash listable-btn" style="margin-top: 1px;" title="Remove"></span>');
			
				newTextBoxDiv.appendTo("#TextBoxesGroup");
				jQuery('#TextBoxDiv'+counter).find("select").chosen();

				document.getElementById('DED').value=counter;
				counter++;	
		    });
	  	});

	  	function removePot(counter){
	  		
	  		jQuery("#TextBoxDiv" + counter).remove();
	  		// var newDed = parseInt(document.getElementById('DED').value)-1;
	  		// document.getElementById('DED').value=newDed;
		    calcPay();
	  	}
		</script>

<script type="text/javascript">
    var cicilanBon = jQuery('#DEBT_MO').val();
    function checkform() {
        if (cicilanBon > jQuery('#DEBT_MO').val()) {
            parent.document.getElementById('employeeCredit').style.display = 'block';
            parent.document.getElementById('fade').style.display = 'block';

            return false;
        }
        return true;
    }

    parent.document.getElementById('employeeCreditYes').onclick = function(){
        cicilanBon = 0;
        jQuery('form').submit();

        parent.document.getElementById('employeeCredit').style.display = 'none';
        parent.document.getElementById('fade').style.display = 'none';
    }
</script>

</body>
</html>