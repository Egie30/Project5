<?php 
	//Mendapatkan parameter untuk perhitungan marketing performance reward
	$query = "SELECT RWD_M_BASE_Q,
					 RWD_S_BASE_Q,
					 RWD_M_INC_Q,
					 RWD_S_INC_Q,
					 RWD_M_INC_PCT,
					 RWD_S_INC_PCT
					 FROM NST.PARAM_LOC";
	$result = mysql_query($query);
	$rowPL  = mysql_fetch_array($result);
	
	$RwdMBaseQ = $rowPL['RWD_M_BASE_Q'];
	$RwdSBaseQ = $rowPL['RWD_S_BASE_Q'];
	$RwdMIncQ  = $rowPL['RWD_M_INC_Q'];
	$RwdSIncQ  = $rowPL['RWD_S_INC_Q'];
	$RwdMIncPct= $rowPL['RWD_M_INC_PCT'];
	$RwdSIncPct= $rowPL['RWD_S_INC_PCT'];

	//Mendapatkan rata-rata penjualan payroll sebelumnya 
	$queryPCOld = "SELECT PRSN_NBR, M_AVG_ALL, S_AVG_ALL
						FROM CDW.PAY_RWD RWD
						RIGHT JOIN (
							SELECT PAY_CONFIG_NBR 
								FROM PAY.PAY_CONFIG_DTE 
								WHERE ('".$PayBegDte."' - INTERVAL 1 DAY) >= PAY_BEG_DTE 
									AND ('".$PayBegDte."' - INTERVAL 1 DAY) <= PAY_END_DTE
						)CON ON CON.PAY_CONFIG_NBR = RWD.PAY_CONFIG_NBR  
						LIMIT 1";
	$ResPCOld    = mysql_query($queryPCOld); 
	$rowPCOld    = mysql_fetch_array($ResPCOld);
	$mAvgAll = $rowPCOld['M_AVG_ALL']; 
	$sAvgAll = $rowPCOld['S_AVG_ALL']; 

	//Mengambil data reward penjualan payroll 
	$queryPR  = "SELECT M_Q,S_Q,M_AVG, S_AVG
						FROM CDW.PAY_RWD
						WHERE PAY_CONFIG_NBR = '".$PayConfigNbr."' AND PRSN_NBR = ".$PrsnNbr;
	$resultPR = mysql_query($queryPR); //echo $queryPR;
	$rowPR  = mysql_fetch_array($resultPR);
	if ($rowPR['M_AVG']!=''){
	$meter  = ($rowPR['M_AVG']/$mAvgAll)*$rowPR['M_Q'];//echo $rowPR['M_AVG'];
	}
	if ($rowPR['S_AVG']!=''){
	$lembar = ($rowPR['S_AVG']/$sAvgAll)*$rowPR['S_Q'];
	}

	if ($meter >= $RwdMBaseQ){
		$rwdMeter = floor(($meter - $RwdMBaseQ)/$RwdMIncQ);
	}

	if ($lembar >= $RwdSBaseQ){
		$rwdLembar= floor(($lembar - $RwdSBaseQ)/$RwdSIncQ);
	}

	$Rwd = $rwdMeter + $rwdLembar;

	if ($Rwd <=0){$Rwd = 0;}