<?php
	error_reporting(0);
	include "framework/functions/dotmatrix.php";
	include "framework/database/connect.php";
	include "framework/functions/komisi.php";
	
	$CoNbr=$_GET['CONBR'];
	if($_GET['EMAIL']=="1"){
		if($_GET['AUTO']==1){
			$query="SELECT MAX(PYMT_DTE) PYMT_DTE FROM PAY.PAYROLL";
			$result=mysql_query($query);
			$LastPay=mysql_fetch_array($result);
			//All company=> (PPL.CO_NBR=271 OR PPL.CO_NBR=997 OR PPL.CO_NBR=889 OR PPL.CO_NBR=1002)
			
			if($CoNbr != "ALL") {
			$query="SELECT PAY.PRSN_NBR, PAY.PYMT_DTE, PPL.CO_NBR FROM PAY.PAYROLL PAY
					LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=PAY.PRSN_NBR WHERE PPL.DEL_NBR=0
					AND (PAY.DEL_NBR=0 OR PAY.DEL_NBR IS NULL) AND PPL.CO_NBR IN (".$CoNbr.") AND (PAY.PYMT_DTE >= ('".$LastPay['PYMT_DTE']."' - INTERVAL 7 DAY)) AND PAY.PYMT_DTE <='".$LastPay['PYMT_DTE']."' GROUP BY PAY.PRSN_NBR";
			}
			else {
			$query="SELECT PAY.PRSN_NBR, PAY.PYMT_DTE, PPL.CO_NBR FROM PAY.PAYROLL PAY
					LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=PAY.PRSN_NBR WHERE PPL.DEL_NBR=0
					AND (PAY.DEL_NBR=0 OR PAY.DEL_NBR IS NULL) AND (PPL.CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_PAYROLL)) AND (PAY.PYMT_DTE >= ('".$LastPay['PYMT_DTE']."' - INTERVAL 7 DAY)) AND PAY.PYMT_DTE <='".$LastPay['PYMT_DTE']."' GROUP BY PAY.PRSN_NBR";
			}
						
		$result=mysql_query($query);
			while($ppl=mysql_fetch_array($result)){
				printReceipt($ppl['PRSN_NBR'],$ppl['PYMT_DTE'],$CoNbr);
				//sleep(20);
			}
		}else{
			printReceipt($_GET['PRSN_NBR'],$_GET['PYMT_DTE'],$CoNbr);
		}
	
	}else{
      if($_GET['AUTO']==1){
	  $query="SELECT MAX(PYMT_DTE) PYMT_DTE FROM PAY.PAYROLL";
			$result=mysql_query($query);
			$LastPay=mysql_fetch_array($result);
		
		if($CoNbr != "ALL") {
			$query="SELECT PAY.PRSN_NBR, PAY.PYMT_DTE, PPL.CO_NBR FROM PAY.PAYROLL PAY
					LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=PAY.PRSN_NBR WHERE PPL.DEL_NBR=0
					AND (PAY.DEL_NBR=0 OR PAY.DEL_NBR IS NULL) AND PPL.CO_NBR IN (".$CoNbr.") AND (PAY.PYMT_DTE >= ('".$LastPay['PYMT_DTE']."' - INTERVAL 7 DAY)) AND PAY.PYMT_DTE <='".$LastPay['PYMT_DTE']."' GROUP BY PAY.PRSN_NBR";
			}
			else {
			$query="SELECT PAY.PRSN_NBR, PAY.PYMT_DTE, PPL.CO_NBR FROM PAY.PAYROLL PAY
					LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=PAY.PRSN_NBR WHERE PPL.DEL_NBR=0
					AND (PAY.DEL_NBR=0 OR PAY.DEL_NBR IS NULL) AND (PPL.CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_PAYROLL)) AND (PAY.PYMT_DTE >= ('".$LastPay['PYMT_DTE']."' - INTERVAL 7 DAY)) AND PAY.PYMT_DTE <='".$LastPay['PYMT_DTE']."' GROUP BY PAY.PRSN_NBR";
			}
		
		
		$resultd=mysql_query($query);
		while($rowd=mysql_fetch_array($resultd)){
			printReceipt($rowd['PRSN_NBR'],$rowd['PYMT_DTE'],$rowd['CO_NBR']);
		}
	  }else{
		printReceipt($_GET['PRSN_NBR'],$_GET['PYMT_DTE'],$CoNbr);
	  }
	}
	
	function printReceipt($PrsnNbr,$PymtDte,$CoNbr){
	$query="SELECT PAY.PRSN_NBR
				,NAME
				,EMAIL
				,PYMT_DTE
				,PYMT_DAYS
				,BASE_AMT * PYMT_DAYS AS PAY_BASE
				,PEER_RWD
				,PEER_RWD_F
				,BASE_CNT
				,BASE_TOT
				,ADD_AMT * PYMT_DAYS AS PAY_ADD
				,ADD_CNT
				,ADD_TOT
				,OT_AMT AS PAY_OT
				,OT_CNT
				,OT_TOT
				,COALESCE(CONTRB_AMT, 0) CONTRB_AMT
				,MISC_AMT
				,MISC_CNT
				,CMSN_AMT
				,MISC_TOT
				,BON_ATT_AMT
				,BON_WK_AMT
				,BON_PCT
				,BON_MO_AMT
				,PPAY.BON_MULT
				,PPAY.DED_DEF AS DED_DEF_PRSN
				,CRDT_WK
				,DEBT_WK AS DED_DEF
				,DEBT_MO
				,PAY_AMT
				,CRDT_AMT
				,PAY.UPD_TS
				,PAY.UPD_NBR
				,BON_SNG_AMT
				,DED_SNG_AMT
				,BNK_ACCT_NBR
				,COALESCE(PPAY.BONUS, 0) BONUS
				,COALESCE(PPAY.PAY_MISC, 0) PAY_MISC
				,PAY.PAY_HLD_AMT
				,PAY.PAY_HLD_F
				,PAY.PAY_HLD_PD_AMT
				,PAY.PAY_HLD_PD_F
				,TOT_DIST
				,REM_TYPE
				,AUTH_TRVL_AMT
				,STY_CNT
				,STY_TOT_AMT
				,DED_DESC
				,DED_AMT
				,RPRMN_AMT
				,RPRMN_NBR
				,DSBRS_CRDT
				,PAY_MISC_AMT
				,PAY_ADD_DESC
				,PAY_ADD_AMT
				,PAY_RWD_AMT
				,PAY.INS_AMT_PRSN
			FROM PAY.PAYROLL PAY
			INNER JOIN CMP.PEOPLE PPL ON PAY.PRSN_NBR = PPL.PRSN_NBR
			LEFT JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
			WHERE (
					PAY.DEL_NBR = 0
					OR PAY.DEL_NBR IS NULL
					)
				AND PAY.PRSN_NBR = ".$PrsnNbr."
				AND DATE (PYMT_DTE) >= ('".$PymtDte."' - INTERVAL 7 DAY)
				AND (DATE (PYMT_DTE) <= '".$PymtDte."')";
	//echo $query."----<br/><br/>";
	
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	//People
	$query="SELECT PPL.CO_NBR AS PPL_CO_NBR
				,IFNULL(PPAY.PAY_BASE, 0) PAY_BASE
				,IFNULL(PPAY.PAY_ADD, 0) PAY_ADD
				,IFNULL(PPAY.PAY_OT, 0) PAY_OT
				,IFNULL(PPAY.PAY_CONTRB, 0) PAY_CONTRB
				,IFNULL(PPAY.PAY_MISC, 0) PAY_MISC
				,IFNULL(PPAY.DED_DEF, 0) DED_DEF
				,IFNULL(PPAY.BONUS, 0) BONUS
				,PPAY.BON_MULT
				,COALESCE((
						SELECT (
								SUM(CRDT_AMT) - (
									SELECT SUM(DEBT_MO)
									FROM PAY.PAYROLL
									WHERE PRSN_NBR = $PrsnNbr
									)
								)
						FROM PAY.EMPL_CRDT
						WHERE PRSN_NBR = $PrsnNbr AND PYMT_DTE<= '$PymtDte' AND CRDT_APV_FIN=1 AND CRDT_APV=1
						), 0) AV_CRD
			FROM PAY.PEOPLE PPAY
			LEFT JOIN CMP.PEOPLE PPL ON PPAY.PRSN_NBR = PPL.PRSN_NBR
			WHERE PPAY.PRSN_NBR = ".$PrsnNbr;
	
	$result=mysql_query($query);
	$people=mysql_fetch_array($result);
	
	$CoNbr	= $people['PPL_CO_NBR'];
	//Company
	$query="SELECT NAME,ADDRESS,CITY_NM, ZIP,PHONE, EMAIL FROM CMP.COMPANY COM LEFT OUTER JOIN CITY CTY ON CTY.CITY_ID=COM.CITY_ID WHERE CO_NBR=".$CoNbr." ";
	$result=mysql_query($query);
	$company=mysql_fetch_array($result);
	if($CoNbr=='271'){$ComPhone="(0274) 566936";}else if($CoNbr=='1002'){$ComPhone="(0274) 6698111";}else{$ComPhone="(0274) 586866";}
	$header=followspace(ucfirst($company['NAME']),(56))."PERINCIAN GAJI KARYAWAN".leadspace("Nomor Induk : ".leadZero($PrsnNbr,6),56).chr(13).chr(10);
	
	$header.=followspace(ucfirst($company['ADDRESS']).", ".ucfirst($company['CITY_NM'])." ".ucfirst($company['ZIP']),70).leadspace("Tanggal Gajian : ".$row['PYMT_DTE'],65).chr(13).chr(10);
	$header.="Telp ".$ComPhone.", E-Mail: ".$company['EMAIL'].chr(13).chr(10);

	$header.=chr(13).chr(10);
	$customer=trim($row['NAME_PPL']." ".$row['NAME_COM']);
	
	$header.=followspace("Nama Karyawan: ".$row['NAME'],70);
	if($row['BNK_ACCT_NBR']!=''){$header.=leadspace("Nomor Rekening: ".$row['BNK_ACCT_NBR'],65);}
	$header.=chr(13).chr(10);

    $header.=str_repeat("-",135).chr(13).chr(10);

	$string=$header;
	$result=mysql_query($query);
	
	$beginDateLast	= date('Y-m', strtotime('-1 month', strtotime($_GET['PYMT_DTE'])));
	$queryMax 		= "SELECT PYMT_DTE, COUNT(PYMT_DTE) AS JUMLAH FROM PAY.PAYROLL WHERE LEFT(PYMT_DTE,7) = '".$beginDateLast."' GROUP BY PYMT_DTE ORDER BY COUNT(PYMT_DTE) DESC LIMIT 1";
	$resultMax 		= mysql_query($queryMax);
	$rowMax 		= mysql_fetch_array($resultMax);
	
	$beginDate 		= $rowMax['PYMT_DTE'];
	$endDate 		= date('Y-m-d', strtotime('-1 day', strtotime($_GET['PYMT_DTE'])));
	
	$query_half 	= "SELECT 
							DATE_TS,
							PRSN_NBR,
							CO_NBR,
							CLOK_IN_TS,
							CLOK_OT_TS,
							SUM(CASE 
								WHEN (CLOK_IN_TS IS NULL OR CLOK_OT_TS IS NULL) THEN 1 
								ELSE 0
								END) AS DAY_UPNORMAL
						FROM
						(SELECT 
							CLOK_NBR, 
							PPL.CO_NBR,
							PPL.PRSN_NBR,
							DATE(CLOK_IN_TS) AS DATE_TS, 
							MAC.CLOK_IN_TS,
							MAC.CLOK_OT_TS
						FROM PAY.MACH_CLOK MAC 
						LEFT OUTER JOIN CMP.PEOPLE PPL ON MAC.PRSN_NBR=PPL.PRSN_NBR 
						LEFT OUTER JOIN PAY.PEOPLE PPAY ON PPAY.PRSN_NBR=PPL.PRSN_NBR
						WHERE DATE(CLOK_IN_TS) 
							AND DATE(CLOK_IN_TS) >= '".$beginDate."' 
							AND DATE(CLOK_IN_TS) <= '".$endDate."'
							AND MAC.PRSN_NBR = '".$_GET['PRSN_NBR']."'
						GROUP BY PRSN_NBR,DATE(CLOK_IN_TS)
						) WORK";
	$result_half 	= mysql_query($query_half);
	$row_half 		= mysql_fetch_array($result_half);
	$off 			= $row_half['DAY_UPNORMAL'];
	
	//$string .= chr(13) . chr(10);
	$string .= followspace("      Jumlah hari masuk kerja: " . $row['BASE_CNT'] . " hari dari total " . $row['PYMT_DAYS'] . " hari periode ini", 76) . "        " . chr(13) . chr(10);
	if ($row_half['DAY_UPNORMAL'] == 0) {$off = 0;} else {$off = $row_half['DAY_UPNORMAL'];}
	$string .= followspace("      Jumlah Setengah Hari   : " . $off . " hari", 76) . "        " . chr(13) . chr(10);
    $string .= followspace("      Gaji pokok             : Rp. " . leadSpace($row['PAY_BASE'] / $row['PYMT_DAYS'], 7) . " x " . leadSpaceDec($row['BASE_CNT'], 2) . " hari =   Rp. " . leadspace($row['BASE_TOT'], 10), 76) . leadspace("", 10) . followspace("Gaji Pokok Per Bulan", 21) . ":" . " Rp. " . leadspace($people['PAY_BASE'], 11) . "           " . chr(13) . chr(10);
    $string .= followspace("      Bonus bulan ini        : " . leadSpace($row['BON_PCT'], 2) . " %                    =   Rp. " . leadSpace($row['BON_MO_AMT'], 10), 76) . leadspace("", 10) . followspace("Gaji Tambahan ", 21) . ":" . " Rp. " . leadspace($people['PAY_ADD'], 11) . "            " . chr(13) . chr(10);
    $string .= followspace("      Komisi                 :                         =   Rp. " . leadspace($row['CMSN_AMT'], 10), 76) . leadSpace("", 10) . followspace("Gaji Lembur", 21) . ":" . " Rp. " . leadspace($people['PAY_OT'], 11) . "            " . chr(13) . chr(10);
    $string .= followspace("      Gaji lembur            : Rp. " . leadSpace($row['PAY_OT'], 7) . " x " . leadspace($row['OT_CNT'], 2) . "   jam  =   Rp. " . leadspace($row['OT_TOT'], 10), 76) . leadspace("", 10) .followspace("Gaji Lain", 21) . ":" . " Rp. " . leadspace($people['PAY_MISC'], 11) . "            " . chr(13) . chr(10);
    $string .= followspace("      Gaji tambahan          : Rp. " . leadSpace($row['PAY_ADD'] / $row['PYMT_DAYS'], 7) . " x " . leadSpaceDec($row['ADD_CNT'], 2) . " hari =   Rp. " . leadspace($row['ADD_TOT'], 10), 76) . leadspace("", 10) . followspace("Gaji Kontribusi", 21) . ":" . " Rp. " . leadspace($row['CONTRB_AMT'], 11) . "            " . chr(13) . chr(10);
    if ($row['AUTH_TRVL_AMT'] == 0){$Transport = 0;}else{$Transport = $row['AUTH_TRVL_AMT']/$row['TOT_DIST'];}
    if ($row['TOT_DIST'] 	  == 0){$totDist = 0;}else{$totDist = $row['TOT_DIST'];}
	$string .= followspace("      Transportasi           : Rp. " . leadSpace($Transport, 7) . " x " . followspace($totDist, 6) . leadspace('km',3)." =   Rp. " . leadspace($row['AUTH_TRVL_AMT'], 10), 76) . leadspace("", 10) . followspace("Potongan Bon/Bulan", 21) . ":" . " Rp. " . leadspace($row['DED_DEF_PRSN'], 11) . "            " .  chr(13) . chr(10);
	if ($row['STY_CNT'] == 0){$menginap = 0;}else{$menginap = $row['STY_TOT_AMT'] / $row['STY_CNT'];}
	$string .= followspace("      Menginap               : Rp. " . leadSpace($menginap, 7) . " x " . leadspace($row['STY_CNT'], 2) . "   hari =   Rp. " . leadspace($row['STY_TOT_AMT'], 10), 76) . leadspace("", 10) . followspace("Bonus", 21) . ":" . " Rp. " . leadspace($people['BONUS'], 11) . "            " .  chr(13) . chr(10);
	if ($row['PAY_MISC_AMT'] == 0){$paymiscamt = 0;}else{$paymiscamt = $row['PAY_MISC_AMT'];}
	$string .= followspace("      Jasa Pemasangan        :     " .leadspace($row['xx'], 12) . "        =   Rp. " . leadspace($paymiscamt, 10), 76) . leadspace("", 10) . chr(13) . chr(10);
    $string .= followspace("      " . str_repeat("-", 68), 76) . leadspace("", 10) . followspace("Kelipatan Bonus", 21) . ":" . "     " . leadspaceDecimal($people['BON_MULT'], 11, 1) . "            "  .  chr(13) . chr(10);
	$string .= followspace("                                              Jumlah   =   Rp. " . leadSpace($row['BASE_TOT'] + $row['BON_MO_AMT']+ $row['CMSN_AMT'] + $row['OT_TOT'] + $row['ADD_TOT'] + $row['AUTH_TRVL_AMT'] + $row['STY_TOT_AMT'] + $row['PAY_MISC_AMT'], 10), 76) . leadspace("", 10) . followspace("Sisa Bon", 21) . ":" . " Rp. " . leadspace($people['AV_CRD'], 11).chr(13) . chr(10);
	$string .= followspace("                                       Gaji Kontribusi = - Rp. " . leadSpace($row['CONTRB_AMT'], 10), 76) . leadspace("", 10) . chr(13) . chr(10);
	$string .= followspace("                                              Bon      = - Rp. " . leadSpace($row['DEBT_MO'], 10), 76) . leadspace("", 10) . chr(13) . chr(10);
    $string .= followspace("                                              Bonus    =   Rp. " . leadSpace($row['BON_SNG_AMT'], 10), 76) . leadspace("", 10) . chr(13) . chr(10);

    //Marketing performance reward 
    if ($row['PAY_RWD_AMT']>0){
    	$string .= followspace("                       Marketing Performance Reward    =   Rp. " . leadSpace($row['PAY_RWD_AMT'], 10), 76) . leadspace("", 10) . chr(13) . chr(10);
    }
    
    //Extra
    if ($row['PAY_ADD_DESC']!=''){
    	$PayAddDesc = explode("+", $row['PAY_ADD_DESC']);
    	$PayAddAmt  = explode("+", $row['PAY_ADD_AMT']);

    	for ($i=0; $i <count($PayAddDesc); $i++) { 
    		if ($PayAddDesc[$i] =="TVP"){
    			$PAY_ADD_DESC = "Extra "."Travel Pemasangan";
    		}else{
    			$PAY_ADD_DESC = "Extra ".$PayAddDesc[$i];
    		}
    		if ($PayAddDesc[$i] !='Kosong' || $PayAddDesc[$i]!=''){
    			$string .= followspace("                                ".leadSpace(substr($PAY_ADD_DESC, 0, 22), 22)." =   Rp. " . leadSpace($PayAddAmt[$i], 10), 76) . " " . chr(13) . chr(10);
    		}else{
    			$string .= "";
    		}
    	}
    }

    //Potongan
   	if ($row['DED_DESC']!=''){
	    $dedDesc = explode("+", $row['DED_DESC']);
	    $dedAmt  = explode("+", $row['DED_AMT']);

	    for($i=0;$i<count($dedDesc);$i++){
	    	if ($dedDesc[$i]!='Kosong'){
	    		$string .= followspace("                                ".leadSpace("Potongan ".$dedDesc[$i], 22)." = - Rp. " . leadSpace($dedAmt[$i], 10), 76) . " " . chr(13) . chr(10);
	    	}else{
	    		$string .= "";
	    	}
	    	
	    }
	}else{
		$string .= followspace("                                              Potongan = - Rp. " . leadSpace($row['DED_SNG_AMT'], 10), 76) . " " . chr(13) . chr(10);
	}
	$string .= followspace("                                     Potongan Asuransi = - Rp. " . leadSpace($row['INS_AMT_PRSN'], 10), 76) . leadspace("", 10) . chr(13) . chr(10);
	
	$string .= followspace("                                  Peer to peer penalty = - Rp. " . leadSpace($row['PEER_RWD'], 10), 76) . " " . chr(13) . chr(10);
if ($row['PAY_HLD_F']=='1'){
    $string .= followspace("                                          Gaji ditahan = - Rp. " . leadSpace($row['PAY_HLD_AMT'], 10), 76) . " " . chr(13) . chr(10);
} else if ($row['PAY_HLD_PD_F']=='1'){
    $string .= followspace("                                        Gaji diberikan =   Rp. " . leadSpace($row['PAY_HLD_PD_AMT'], 10), 76) . " " . chr(13) . chr(10);
}
	if ($row['RPRMN_NBR']!=0){
		if ($row['LTR_RPRMN_AMT'] 	  == 0){$ltrrPrmnAmt = 0;}else{$ltrrPrmnAmt = $row['LTR_RPRMN_AMT'];}
		$queryPr = "SELECT RPRMN_DESC FROM PAY.RPRMN WHERE RPRMN_NBR=".$row['RPRMN_NBR'];
		$resultPr= mysql_query($queryPr);
		$rowPr   = mysql_fetch_array($resultPr);

		$string .= followspace("                                  ".leadSpace($rowPr['RPRMN_DESC'], 20)." = - Rp. " . leadSpace($row['RPRMN_AMT'], 10), 76) . " " . chr(13) . chr(10);
	}
	$string .= followspace("                                      Pencairan Bon    =   Rp. " . leadSpace($row['DSBRS_CRDT'], 10), 76) . leadspace("", 10) . chr(13) . chr(10);

    $string .= followspace("      " . str_repeat("-", 68), 76) . " " . chr(13) . chr(10);
    $string .= followspace("                                                 Total =   Rp. " . leadSpace($row['PAY_AMT'], 10), 76) . " " . chr(13) . chr(10);
    //$string .= chr(13) . chr(10);
    $string .= str_repeat("-", 135) . chr(13) . chr(10);

	if($_GET['EMAIL']=="1"){
	$string.=chr(13).chr(10).chr(13).chr(10).chr(13).chr(10);	
	}else{
	$string.=pSpace(100)."         Penerima".chr(13).chr(10);
	$string.=chr(13).chr(10);
	$string.=pSpace(100)."(_______________________)".chr(13).chr(10);
	}
	$string.=chr(13).chr(10);
	$string.="Terima kasih atas kinerja yang anda berikan, dan mari kita tingkatkan produktifitas dan kualitas untuk kemajuan bersama.".chr(13).chr(10);

	#=============tambahan perhitungan komisi=============
    $line = 1;
    $maximumLine = 20;
    $pages = 1;

    $bulan = date('m', strtotime($PymtDte));
    $tahun = date('Y', strtotime($PymtDte));

    $komisi_print = json_decode(calcKomisiPrint($bulan, $tahun, $PrsnNbr));
    $komisi_retail = json_decode(calcKomisiRetail($bulan, $tahun, $PrsnNbr));
    $komisi_sales = json_decode(calcKomisiSales($bulan, $tahun, $PrsnNbr));

    if (sizeof($komisi_print) > 0) {
        $string .= $header;

        $string .= followSpace('No Nota', 9) . followSpace('Perusahaan', 30) . followSpace('Equipment', 43) . followSpace('Sub Total', 17) . followSpace('Total ', 20) . followSpace('Komisi', 10) . chr(13) . chr(10);
        $string .= str_repeat("-", 135) . chr(13) . chr(10);

        $tmp_eqp = '';
        $tmp_total = 0;
        $i = 0;
        foreach ($komisi_print as $key => $value) {
            $perusahaan = $value->BUY_CO_NAME;
            if (strlen($perusahaan) >= 25) {
                $perusahaan = substr($perusahaan, 0, 23) . '';
            }

            $equipment = $value->PRN_DIG_EQP_DESC;
            if (strlen($equipment) >= 35) {
                $equipment = substr($equipment, 0, 33) . '...';
            }

            if ($tmp_eqp != $value->PRN_DIG_EQP) {
                $sub_total = 0;
                $tmp_total = 0;

                $sub_total = $value->TOTAL_METER;
                $tmp_total += $sub_total;
                $string .= followSpace($value->ORD_NBR, 8) . followSpace($perusahaan, 31) . followSpace($equipment, 40) . followSpace(leadSpace($sub_total, 6) . ' meter', 15) . followSpace(leadSpace($tmp_total, 6) . ' meter', 17) . chr(13) . chr(10);
            } else {
                $sub_total = $value->TOTAL_METER;
                $tmp_total += $sub_total;
                $komisi = $tmp_total * $value->PRC;

                if ($value->PRN_DIG_EQP != $komisi_print[$i + 1]->PRN_DIG_EQP) {
                    $string .= followSpace($value->ORD_NBR, 8) . followSpace($perusahaan, 31) . followSpace($equipment, 40) . followSpace(leadSpace($sub_total, 6) . ' meter', 15) . followSpace(leadSpace($tmp_total, 6) . ' meter', 15) . 'Rp ' . followSpace(leadSpace($value->PRC,6), 10) . 'Rp ' . leadSpace($komisi, 10) . chr(13) . chr(10);
                } else {
                    $string .= followSpace($value->ORD_NBR, 8) . followSpace($perusahaan, 31) . followSpace($equipment, 40) . followSpace(leadSpace($sub_total, 6) . ' meter', 15) . followSpace(leadSpace($tmp_total, 6) . ' meter', 17) . chr(13) . chr(10);
                }

            }

            $i++;
            $line++;

            if ($line == $maximumLine) {
                $string .= $header;
                $string .= followSpace('No Nota', 9) . followSpace('Perusahaan', 30) . followSpace('Equipment', 45) . followSpace('Sub Total', 13) . followSpace('Total ', 16) . followSpace('', 10) . followSpace('Komisi', 10) . chr(13) . chr(10);
                $string .= str_repeat("-", 135) . chr(13) . chr(10);
            }

            $tmp_eqp = $value->PRN_DIG_EQP;
        }

    } else if (sizeof($komisi_retail) > 0) {
        $string .= $header;

        $string .= followSpace('No Nota', 9) . followSpace('Perusahaan', 30) . followSpace('Kategori', 45) . followSpace('Minimal', 13) . followSpace('Total ', 16) . followSpace('Harga', 10) . followSpace('Komisi', 10) . chr(13) . chr(10);

        $string .= str_repeat("-", 135) . chr(13) . chr(10);
        foreach ($komisi_retail as $key => $value) {
            $perusahaan = $value->COMPANY;
            if (strlen($perusahaan) >= 25) {
                $perusahaan = substr($perusahaan, 0, 23) . '';
            }

            $string .= followSpace($value->ORD_NBR, 9) . followSpace($perusahaan, 30) . followSpace($value->CATEGORY, 25) . 'Rp ' . followSpace(leadSpace($value->MIN_Q, 9), 13) . 'Rp' . followSpace(leadSpace($value->TOTAL, 11), 17) . followSpace(leadSpace($value->PRC, 6) . ' %', 15) . 'Rp ' . leadSpace($value->KOMISI, 10) . chr(13) . chr(10);

            $line++;

            if ($line == $maximumLine) {
                $string .= $header;
                $string .= followSpace('No Nota', 9) . followSpace('Perusahaan', 30) . followSpace('Kategori', 45) . followSpace('Minimal', 13) . followSpace('Total Nota', 16) . followSpace('Komisi', 10) . chr(13) . chr(10);
                $string .= str_repeat("-", 135) . chr(13) . chr(10);
            }
        }

    } else if (sizeof($komisi_sales) > 0) {
        $string .= $header;

        $string .= followSpace('No Nota', 9) . followSpace('Perusahaan', 30) . followSpace('Kategori', 28) . followSpace('Minimal', 18) . followSpace('Total Nota', 16) . followSpace('', 18) . followSpace('Komisi', 10) . chr(13) . chr(10);

        $string .= str_repeat("-", 135) . chr(13) . chr(10);
        foreach ($komisi_sales as $key => $value) {
            $perusahaan = $value->COMPANY;
            if (strlen($perusahaan) >= 25) {
                $perusahaan = substr($perusahaan, 0, 23) . '';
            }

            $string .= followSpace($value->ORD_NBR, 9) . followSpace($perusahaan, 30) . followSpace($value->CATEGORY, 25) . 'Rp ' . followSpace(leadSpace($value->MIN_Q, 9), 17) . 'Rp' . followSpace(leadSpace($value->SUB_TOTAL, 11), 14) . followSpace('', 15) . 'Rp ' . leadSpace($value->KOMISI, 10) . chr(13) . chr(10);

            $line++;

            if ($line == $maximumLine) {
                $string .= $header;
                $string .= followSpace('No Nota', 9) . followSpace('Perusahaan', 30) . followSpace('Kategori', 45) . followSpace('Minimal', 13) . followSpace('Total ', 16) . followSpace('Komisi', 10) . chr(13) . chr(10);
                $string .= str_repeat("-", 135) . chr(13) . chr(10);
            }
        }

    }
    #=============end of tambahan perhitungan komisi=============
	if($_GET['EMAIL']!="1"){
	echo "<pre style='font-size:9pt;letter-spacing:-1.25px;'>";
	echo $string;
	echo "</pre>";
	}
	//exit();
	if($_GET['EMAIL']=="1"){
		$MailDir="print-digital/payroll/";
		$files = glob($MailDir . "*.txt"); 
		//echo "file:".$files;
		foreach($files as $file){ 
		  if(is_file($file))
			unlink($file); 
		}		
		$fh=fopen($MailDir.$PrsnNbr."-".$PymtDte.".txt", "w");
		fwrite($fh, chr(15).$string.chr(18));
		fclose($fh);
		include_once "framework/email/config.php"; 
		//include_once "framework/email/classes/class.phpmailer.php"; 
		include_once "framework/email/smtp/PHPMailerAutoload.php";
		$email = $row['EMAIL'];
		$mail	= new PHPMailer;  
		$mail->IsSMTP(); 
		$mail->Host = SMTP_HOST; 
		$mail->Port = SMTP_PORT; 
		$mail->SMTPAuth = true; 
		$mail->Username = SMTP_UNAME; 
		$mail->Password = SMTP_PWORD; 
		$mail->Subject = "Perincian Gaji Karyawan"; 
		$mail->AddAddress($email, $row['NAME']); 
		$mail->AddAttachment($MailDir.$PrsnNbr."-".$PymtDte.".txt");  
		$mail->SMTPOptions = array(
		    'ssl' => array(
		        'verify_peer' => false,
		        'verify_peer_name' => false,
		        'allow_self_signed' => true
		    )
		);
		$mail->MsgHTML("<span style='line-height:1.34em;color:rgb(153,153,153);font-size:9px;font-family:Geneva,Verdana,Arial,Helvetica,sans-serif'>This communication contains proprietary information and may be confidential. If you are not the intended recipient, the reading, copying, disclosure or other use of the contents of this e-mail is strictly prohibited and you are instructed to please delete this e-mail immediately.</span>"); 
		$send = $mail->Send(); //Send the mails
		if($send){
			echo "<pre style='font-size:9pt;letter-spacing:-1.25px;'><font style='color:#009933;'>Email sent to ".$email."... (".$PrsnNbr.") </font></pre>";
		}else{
			echo "<pre style='font-size:9pt;letter-spacing:-1.25px;'><font style='color:#FF3300;'>Email not sent to ".$email."... (".$PrsnNbr.") ".$mail->ErrorInfo." </font></pre>";
		}		
	}else{ 
		$fh=fopen("print-digital/payroll/".$PrsnNbr."-".$row['PYMT_DTE'].".txt", "w");
		fwrite($fh, chr(15).$string.chr(18));
		fclose($fh);
	}

	}
?>