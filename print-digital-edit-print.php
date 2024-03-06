<?php
	include "framework/functions/dotmatrix.php";
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";

	$UpperSec = getSecurity($_SESSION['userID'],"Accounting");
	
	$OrdNbr	= $_GET['ORD_NBR'];
	$PrnTyp	= $_GET['PRN_TYP'];
	$type	= $_GET['TYP'];
	
	if($type == "EST"){
		$headtable 		= "CMP.PRN_DIG_ORD_HEAD_EST";
		$detailtable	= "CMP.PRN_DIG_ORD_DET_EST";
		$paymenttable	= "CMP.PRN_DIG_ORD_PYMT_EST";
		$tableAdd		= "CMP.PRN_DIG_ORD_VAL_ADD_EST";
	}else{
		$headtable 		= "CMP.PRN_DIG_ORD_HEAD";
		$detailtable	= "CMP.PRN_DIG_ORD_DET";
		$paymenttable	= "CMP.PRN_DIG_ORD_PYMT";
		$tableAdd		= "CMP.PRN_DIG_ORD_VAL_ADD";
	}

	if ($_GET["LIST_DET_NBR"] == "SESSION") {
    	$ListDetNbr = $_SESSION["LIST_DET_NBR"];
	} else {
		$ListDetNbr = explode(" ", $_GET["LIST_DET_NBR"]);
	}

	$ArrayList 	= array_count_values($ListDetNbr);

	$queryActg 	= "SELECT ACTG_TYP FROM ".$headtable."  WHERE ORD_NBR = '".$OrdNbr."'";
	$resultActg = mysql_query($queryActg);
	$rowActg 	= mysql_fetch_array($resultActg);

	//Get default company
	if($rowActg['ACTG_TYP']==1){
		$query 	= "SELECT NAME,ADDRESS,ZIP,PHONE,CITY_NM,EMAIL 
					FROM CMP.COMPANY COM 
					LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID 
					WHERE CO_NBR = $CoNbrPkp";
	} else {
		$query 	= "SELECT NAME,ADDRESS,ZIP,PHONE,CITY_NM,EMAIL 
					FROM CMP.COMPANY COM 
					LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID 
					WHERE CO_NBR = $CoNbrDef";
	}
	$result = mysql_query($query);
	$CmpDef = mysql_fetch_array($result);	

	$query 	= "SELECT PHONE,EMAIL 
				FROM CMP.COMPANY COM 
				WHERE CO_NBR = $CoNbrDef";
	$result = mysql_query($query);
	$CmpLoc = mysql_fetch_array($result);
	
	//Increment print count
	if($PrnTyp=="Invoice"){		
		$query 	= "UPDATE ". $headtable ." SET IVC_PRN_CNT=IVC_PRN_CNT+1 WHERE ORD_NBR=".$OrdNbr;
		$resultb= mysql_query($query);
	}
	
	$query 		= "SELECT 
						ORD_NBR,
						DATE_FORMAT(ORD_TS,'%d-%m-%Y') AS ORD_DT,
						ORD_TS,
						DATE_FORMAT(CMP_TS,'%d-%m-%Y') AS CMP_DT,
						STT.ORD_STT_ID,
						STT.ORD_STT_DESC,
						BUY_PRSN_NBR,
						PPL.NAME AS NAME_PPL,
						COM.NAME AS NAME_COM,
						BUY_CO_NBR,
						REF_NBR,
						ORD_TTL,
						DUE_TS,
						PRN_CO_NBR,
						FEE_MISC,
						TAX_APL_ID,
						TAX_AMT,
						TOT_AMT,
						PYMT_DOWN,
						PYMT_REM,
						TOT_REM,
						DL_CNT,
						PU_CNT,
						NS_CNT,
						DATE_FORMAT(CMP_TS,'%d-%m-%Y') AS CMP_DT,
						PU_TS,
						SPC_NTE,
						HED.CRT_TS,
						HED.CRT_NBR,
						HED.UPD_TS,
						HED.UPD_NBR,
						IVC_PRN_CNT,
						NBR
				FROM ". $headtable ." HED
				INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
				LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
				LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
				LEFT OUTER JOIN CDW.PRN_DIG_TOP_CUST TOP ON HED.BUY_CO_NBR=TOP.NBR
				WHERE ORD_NBR=".$OrdNbr;
	$result 	= mysql_query($query);
	$row 		= mysql_fetch_array($result);
	
	if($row['IVC_PRN_CNT']==1){
		//Copy the innerHTML from print-digital.php
		$due 		= strtotime($row['DUE_TS']);
		$OrdSttId 	= $row['ORD_STT_ID'];
		if((strtotime("now")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
			$back="print-digital-red";
		}elseif((strtotime("now + ".$row['JOB_LEN_TOT']." minute")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
			$back="print-digital-yellow";				
		}else{
			$back="print-digital-white";
		}
		//echo $due." ".strtotime("now")." ".strtotime("now + ".$row['JOB_LEN_TOT']." minute")."<br>";		
		$newStr="<div style='font-weight:bold;color:#666666;font-size:11pt;display:inline;float:left'>".$row['ORD_NBR']."</div>";
		$newStr.="<div class='$back' style='display:inline;width:100px;float:right;'>".parseDateShort($row['DUE_TS'])." ".parseHour($row['DUE_TS']).":".parseMinute($row['DUE_TS'])."</div>";
		$newStr.="<div style='display:inline;float:right;padding-top:2px'>";
		if($row['NBR']!=""){
			$newStr.="<div class='listable'><span class='fa fa-star listable'></span></div>";
		}				
		if($row['SPC_NTE']!=""){
			$newStr.="<div class='listable'><span class='fa fa-comment listable'></span></div>";
		}
		if($row['DL_CNT']>0){
			$newStr.="<div class='listable'><span class='fa fa-truck listable' style='margin-left:-1px'></span></div>";
		}
		if($row['PU_CNT']>0){
			$newStr.="<div class='listable'><span class='fa fa-shopping-cart listable'></span></div>";
		}
		if($row['NS_CNT']>0){
			$newStr.="<div class='listable'><span class='fa fa-flag listable'></span></div>";
		}
		if($row['IVC_PRN_CNT']>0){
			$newStr.="<div class='listable'><span class='fa fa-print listable'></span></div>";
		}
		$newStr.="&nbsp;</div>";
		$newStr.="<div style='clear:both'></div>";
		if(trim($row['NAME_PPL']." ".$row['NAME_COM'])==""){$name="Tunai";}else{$name=trim($row['NAME_PPL']." ".$row['NAME_COM']);}
		$newStr.="<div style='font-weight:bold;color:#3464bc'>".$name."</div>";
		$newStr.="<div>".$row['ORD_TTL']."</div>";
		$newStr.="<div>".parseDateShort($row['ORD_TS'])."&nbsp;";
		$newStr.="<span style='font-weight:bold'>".$row['ORD_STT_DESC']."</span>";
		$newStr.="<span style='float:right;style='color:#888888'>Rp. ".number_format($row['TOT_REM'],0,'.',',')."/";
		$newStr.="Rp. ".number_format($row['TOT_AMT'],0,'.',',');
		$newStr.="</span></div>";
		echo "<script type='text/javascript' src='framework/functions/default.js'></script>";
		echo "<script>";
		//echo "alert('a');";
		echo "parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$OrdNbr."').style.opacity=0;";
		echo "parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$OrdNbr."').style.filter='alpha(opacity=0)';";
		echo "parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$OrdNbr."').innerHTML=".chr(34).$newStr.chr(34).";";
		echo "fadeIn(parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$OrdNbr."'));";
		echo "</script>";
	}

	if($PrnTyp=="Invoice"){		
		$Suffix="I";
		$header=followSpace($CmpDef['NAME'],59)."NOTA PRINTING".pSpace(45)."Nota No. ".leadZero($OrdNbr,6)."-".leadZero($row['IVC_PRN_CNT'],2).chr(13).chr(10);
	} elseif($PrnTyp="PackingSlip"){
		$Suffix="P";
		$header=followSpace($CmpDef['NAME'],59)." SURAT JALAN ".pSpace(45)."Nota No. ".leadZero($OrdNbr,6)."-".leadZero($row['IVC_PRN_CNT'],2).chr(13).chr(10);
	}
	
	$header.=followSpace($CmpDef['ADDRESS'].", ".$CmpDef['CITY_NM']." ".$CmpDef['ZIP'],110)."Tanggal Order: ".$row['ORD_DT'].chr(13).chr(10);
	$header.=followSpace("Telp. ".$CmpLoc['PHONE'].", E-Mail: ".$CmpLoc['EMAIL'],108)."Tanggal Selesai: ".$row['CMP_DT'].chr(13).chr(10);

	$header.=chr(13).chr(10);
	$customer=trim($row['NAME_PPL']." ".$row['NAME_COM']);
	if($customer==""){$customer="Tunai";}
	
	$prnHeader=chr(27)."(B".chr(12).chr(0).chr(5).chr(2).chr(-3).chr(11).chr(0).chr(2).leadZero($OrdNbr,6);
	$prnHeader.=pSpace(42)."Pelanggan: ".$customer.chr(10);
	$prnHeader.=pSpace(42)."Judul Pesanan: ".$row['ORD_TTL'].chr(13).chr(10);
	
	$dspHeader.="Pelanggan: ".$customer.chr(10);
	$dspHeader.="Judul Pesanan: ".$row['ORD_TTL'].chr(13).chr(10);

	$header.=$dspHeader;
    $header.=str_repeat("-",135).chr(13).chr(10);

	if($PrnTyp=="Invoice"){
		$spacing=39;
	} elseif($PrnTyp="PackingSlip") {
		$spacing=55;
	}


    $header.=" Jumlah".pSpace($spacing)."Deskripsi Pesanan";
	if($PrnTyp=="Invoice"){
		if($UpperSec != 8){
	    	$header.=pSpace(40)."Harga".pSpace(7)."Disc".pSpace(8)."Subtotal";
		}
	}
	$header.=chr(13).chr(10);
    $header.=str_repeat("-",135).chr(13).chr(10);

	$string 	= $header;
	$rowCount 	= 0;
	$pageCount 	= 0;
	$query 		= "SELECT 
						ORD_DET_NBR,
						DET.ORD_NBR,
						DET_TTL,
						DET.PRN_DIG_TYP,
						PRN_DIG_DESC,
						DET.PRN_DIG_PRC,
						ORD_Q,
						FIL_LOC,
						PRN_LEN,
						PRN_WID,
						FEE_MISC,
						FAIL_CNT,
						DISC_PCT,
						DISC_AMT,
						VAL_ADD_AMT,
						TOT_SUB
				FROM ". $detailtable ." DET 
				LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
				WHERE ORD_NBR=".$OrdNbr." 
					AND ORD_DET_NBR_PAR IS NULL 
					AND DET.DEL_NBR=0 ORDER BY 1";
	//echo $query;
	$result 	= mysql_query($query);
	while($rowd=mysql_fetch_array($result)){
		if($rowd['PRN_DIG_TYP']=='PROD'){
            $query 	= "SELECT 
            				ORD_DET_NBR,
            				DET.ORD_NBR,
            				CLD.ORD_DET_NBR_PAR,
            				DET_TTL,
            				DET.PRN_DIG_TYP,
            				PRN_DIG_DESC,
            				DET.ORD_Q,
            				FIL_LOC,
            				PRN_LEN,
            				PRN_WID,
            				(COALESCE(CLD.TOT_SUB,0)+COALESCE(CLD.DISC_AMT,0)-COALESCE(CLD.FEE_MISC,0))/COALESCE(DET.ORD_Q,1) AS PRN_DIG_PRC,
            				COALESCE(CLD.FEE_MISC,0)/COALESCE(DET.ORD_Q,1) AS FEE_MISC,
            				COALESCE(CLD.DISC_AMT,0)/COALESCE(DET.ORD_Q,1) AS DISC_AMT,
            				CLD.VAL_ADD_AMT,
            				CLD.TOT_SUB + (COALESCE(DET.FEE_MISC,0) * COALESCE(DET.ORD_Q,1)) AS TOT_SUB,
            				COALESCE(DET.FEE_MISC,0) AS DET_FEE_MISC
						FROM ". $detailtable ." DET 
						LEFT OUTER JOIN (
							SELECT 
								ORD_DET_NBR_PAR,
								SUM(ORD_Q) AS ORD_Q,
								SUM(FEE_MISC*ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS FEE_MISC,
								SUM(FAIL_CNT) AS FAIL_CNT,
								SUM(DISC_AMT*ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS DISC_AMT,
								SUM(VAL_ADD_AMT) AS VAL_ADD_AMT,
								SUM(TOT_SUB) AS TOT_SUB
							FROM ". $detailtable ." DET
							WHERE ORD_DET_NBR_PAR=".$rowd['ORD_DET_NBR']." 
								AND DET.DEL_NBR=0 
							GROUP BY 1 ORDER BY 1
						) CLD ON DET.ORD_DET_NBR=CLD.ORD_DET_NBR_PAR
    					LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
    					WHERE ORD_DET_NBR=".$rowd['ORD_DET_NBR']." 
    						AND DET.DEL_NBR=0 
    						AND DET.PRN_DIG_TYP='PROD' 
    					GROUP BY 1,2 ORDER BY 1";
        } else {
            $query 	= "SELECT 
            				ORD_DET_NBR,
            				DET.ORD_NBR,
            				ORD_DET_NBR_PAR,
            				DET_TTL,
            				PRN_DIG_DESC,
            				DET.PRN_DIG_PRC,
            				ORD_Q,
            				FIL_LOC,
            				PRN_LEN,
            				PRN_WID,
            				FEE_MISC,
            				FAIL_CNT,
            				DISC_PCT,
            				DISC_AMT,
            				VAL_ADD_AMT,
            				TOT_SUB
						FROM ". $detailtable ." DET 
						LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
						WHERE ORD_DET_NBR=".$rowd['ORD_DET_NBR']." 
							OR ORD_DET_NBR_PAR=".$rowd['ORD_DET_NBR']." 
							AND DET.DEL_NBR=0 ORDER BY 1";
        }
        //echo $query;
		$resultc 	= mysql_query($query);
		while($rowc=mysql_fetch_array($resultc)){
			$rowCount++;	
			if($rowCount==12){
			    $string.=str_repeat("-",135).chr(13).chr(10);
			    if($PrnTyp=="Invoice"){
					$string.=pSpace(107)."Total Halaman ".leadSpace($TotNet,14);
				}
				$string.=chr(13).chr(10);
				$string.=pRow(4);
				$string.="Dilanjutkan ke halaman berikutnya".chr(13).chr(10);
				$string.=$header;
				$rowCount=1;
			}
		
			if(($rowc['ORD_DET_NBR_PAR']!="")&&($rowc['PRN_DIG_TYP']!='PROD')){$indent=7;}else{$indent=0;}
			$string.=leadSpace($rowc['ORD_Q'],7+$indent)." ";
			if(($rowc['PRN_LEN']!="")&&($rowc['PRN_WID']!="")){
				$prnDim=" ".$rowc['PRN_LEN']."x".$rowc['PRN_WID'];
			} else { $prnDim=""; }
			$prnDesc=trim(leadZero($rowc['ORD_DET_NBR'],6)." ".trim($rowc['DET_TTL']." ".$rowc['PRN_DIG_DESC'].$prnDim));
			$string.=followSpace($prnDesc,88-$indent)."  ";
			$price=$rowc['PRN_DIG_PRC']+$rowc['VAL_ADD_AMT']+$rowc['FEE_MISC']+$rowc['DET_FEE_MISC'];
			
			if($PrnTyp=="Invoice"){
				if($UpperSec != 8){
					$string.=leadSpace($price,10)."  ";
					$string.=leadSpace($rowc['DISC_AMT'],10)."  ";
					$string.=leadSpace($rowc['TOT_SUB'],13)."  ";
				}
			}
			$string.=chr(13).chr(10);
		
			$query 	= "SELECT 
							ORD_VAL_ADD_NBR,
							VAL_ADD_Q,
							VAL_ADD_DESC,
							VAD.VAL_ADD_PRC,
							FEE_MISC,
							DISC_PCT,
							DISC_AMT,
							TOT_SUB
				 		FROM ".$tableAdd." VAD 
				 		LEFT OUTER JOIN	CMP.PRN_DIG_VAL_ADD_TYP TYP ON VAD.VAL_ADD_TYP=TYP.VAL_ADD_TYP
				 		WHERE ORD_DET_NBR=".$rowc['ORD_DET_NBR'];
		$resulta=mysql_query($query);
		$pageCount++;
		while($rowa=mysql_fetch_array($resulta)){
			$rowCount++;	
			if($rowCount==12){
			    $string.=str_repeat("-",135).chr(13).chr(10);
		       	if($PrnTyp=="Invoice"){
					$string.=pSpace(107)."Total Halaman ".leadSpace($TotNet,14);
				}
				$string.=chr(13).chr(10);
				$string.=pRow(4);
				$string.="Dilanjutkan ke halaman berikutnya".chr(13).chr(10);
				$string.=$header;
				$rowCount=1;
			}
			
			$string.=pSpace(15).leadSpace($rowa['VAL_ADD_Q'],7)."  ";
			$string.=followSpace($rowa['VAL_ADD_DESC'],72)."  ";
			$price=$rowa['VAL_ADD_PRC']+$rowa['FEE_MISC'];
			if($PrnTyp=="Invoice"){
				if($UpperSec != 8){
					$string.=leadSpace($price,10)."  ";
					$string.=leadSpace($rowa['DISC_AMT'],10)."  ";
					$string.=leadSpace($rowa['TOT_SUB'],13)."  ";
				}
			}
			$string.=chr(13).chr(10);
			$TotNet+=$rowa['TOT_SUB'];
		}
		$TotNet+=$rowc['TOT_SUB'];
		}
	}
	
	if($rowCount!=11){
		$string.=pRow(11-$rowCount);
	}

	if($row['TAX_APL_ID']=="E"){
		$totLine1="Biaya Tambahan ".leadSpace($row['FEE_MISC'],14);
		$totLine2="         Total ".leadSpace($row['TOT_AMT'],14);
		
		$querypymt="SELECT SUM(TND_AMT) AS TND_AMT,TOT_REM,PYMT.CRT_TS FROM ". $paymenttable ." PYMT 
					LEFT OUTER JOIN ". $headtable ." HED ON PYMT.ORD_NBR=HED.ORD_NBR
					WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=".$OrdNbr." ORDER BY PYMT.CRT_TS ASC";
		$resultpymt=mysql_query($querypymt);
		$rowttl=mysql_num_rows($resultpymt);
		$rowpym=mysql_fetch_array($resultpymt);
			if($rowttl == 1 && $rowpym['TOT_REM']==0){$TotAmt=0;}else{$TotAmt=$rowpym['TND_AMT'];}
			$totLine3="     Uang Muka ".leadSpace($TotAmt,14);
		
		$querypymt="SELECT SUM(TND_AMT) AS TND_AMT,TOT_REM,PYMT.CRT_TS FROM ". $paymenttable ." PYMT 
					LEFT OUTER JOIN ". $headtable ." HED ON PYMT.ORD_NBR=HED.ORD_NBR
					WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=".$OrdNbr." ORDER BY PYMT.CRT_TS DESC";
		$resultpymt=mysql_query($querypymt);
		$rowpym=mysql_fetch_array($resultpymt);
			if($rowpym['TOT_REM'] == 0){$TotAmt=$rowpym['TND_AMT'];}else{$TotAmt=0;}
			$totLine4="     Pelunasan ".leadSpace($TotAmt,14);
		
		$totLine5="          Sisa ".leadSpace($row['TOT_REM'],14);
		$totLine6="";
	}elseif($row['TAX_APL_ID']=="A"){
		$totLine1="Biaya Tambahan ".leadSpace($row['FEE_MISC'],14);
		$totLine2="           PPN ".leadSpace($row['TAX_AMT'],14);
		$totLine3="         Total ".leadSpace($row['TOT_AMT'],14);
		
		$querypymt="SELECT SUM(TND_AMT) AS TND_AMT,TOT_REM,PYMT.CRT_TS FROM ". $paymenttable ." PYMT 
					LEFT OUTER JOIN ". $headtable ." HED ON PYMT.ORD_NBR=HED.ORD_NBR
					WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=".$OrdNbr." ORDER BY PYMT.CRT_TS ASC";
		$resultpymt=mysql_query($querypymt);
		$rowttl=mysql_num_rows($resultpymt);
		$rowpym=mysql_fetch_array($resultpymt);
			if($rowttl == 1 && $rowpym['TOT_REM']==0){$TotAmt=0;}else{$TotAmt=$rowpym['TND_AMT'];}
			$totLine4="     Uang Muka ".leadSpace($TotAmt,14);
		
		$querypymt="SELECT SUM(TND_AMT) AS TND_AMT,TOT_REM,PYMT.CRT_TS FROM ". $paymenttable ." PYMT 
					LEFT OUTER JOIN ". $headtable ." HED ON PYMT.ORD_NBR=HED.ORD_NBR
					WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=".$OrdNbr." ORDER BY PYMT.CRT_TS DESC";
		$resultpymt=mysql_query($querypymt);
		$rowpym=mysql_fetch_array($resultpymt);
			if($rowpym['TOT_REM'] == 0){$TotAmt=$rowpym['TND_AMT'];}else{$TotAmt=0;}
			$totLine5="     Pelunasan ".leadSpace($TotAmt,14);
		
		$totLine6="          Sisa ".leadSpace($row['TOT_REM'],14);
	}elseif($row['TAX_APL_ID']=="I"){
		$totLine1="Biaya Tambahan ".leadSpace($row['FEE_MISC'],14);
		$totLine2="         Total ".leadSpace($row['TOT_AMT'],14);
		$querypymt="SELECT SUM(TND_AMT) AS TND_AMT,TOT_REM,PYMT.CRT_TS FROM ". $paymenttable ." PYMT 
					LEFT OUTER JOIN ". $headtable ." HED ON PYMT.ORD_NBR=HED.ORD_NBR
					WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=".$OrdNbr." ORDER BY PYMT.CRT_TS ASC";
		$resultpymt=mysql_query($querypymt);
		$rowttl=mysql_num_rows($resultpymt);
		$rowpym=mysql_fetch_array($resultpymt);
			if($rowttl == 1 && $rowpym['TOT_REM']==0){$TotAmt=0;}else{$TotAmt=$rowpym['TND_AMT'];}
			$totLine3="     Uang Muka ".leadSpace($TotAmt,14);
		
		$querypymt="SELECT SUM(TND_AMT) AS TND_AMT,TOT_REM,PYMT.CRT_TS FROM ". $paymenttable ." PYMT 
					LEFT OUTER JOIN ". $headtable ." HED ON PYMT.ORD_NBR=HED.ORD_NBR
					WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=".$OrdNbr." ORDER BY PYMT.CRT_TS DESC";
		$resultpymt=mysql_query($querypymt);
		$rowpym=mysql_fetch_array($resultpymt);
			if($rowpym['TOT_REM'] == 0){$TotAmt=$rowpym['TND_AMT'];}else{$TotAmt=0;}
			$totLine4="     Pelunasan ".leadSpace($TotAmt,14);

		$totLine5="          Sisa ".leadSpace($row['TOT_REM'],14);
		$totLine6="    Jumlah PPN ".leadSpace($row['TAX_AMT'],14);
	}
    $string.=str_repeat("-",135).chr(13).chr(10);
   	if($PrnTyp=="Invoice"){
		$string.=pSpace(106).$totLine1;
	}
	$string.=chr(13).chr(10);
	if($PrnTyp=="Invoice"){
		$spacing=0;
	}elseif($PrnTyp="PackingSlip"){
		$spacing=21;
	}

	$string.=pSpace(18+$spacing)."Penerima".pSpace(40);
   	if($PrnTyp=="Invoice"){
		$string.=" Penjual ";
		$string.=pSpace(31).$totLine2;
	}elseif($PrnTyp="PackingSlip"){
		$string.="Pengantar";
	}
	$string.=chr(13).chr(10);
   	if($PrnTyp=="Invoice"){
		$string.=pSpace(106).$totLine3;
	}
	$string.=chr(13).chr(10);
	$string.=pSpace(13+$spacing)."(________________)".pSpace(30)."(_________________)";
   	if($PrnTyp=="Invoice"){
		$string.=pSpace(26).$totLine4;
	}
	$string.=chr(13).chr(10);
   	if($PrnTyp=="Invoice"){
		$string.=pSpace(106).$totLine5;
	}
	$string.=chr(13).chr(10);
	if($PrnTyp=="Invoice"){
		$string.="Terima kasih atas kepercayaan anda. Silakan hubungi kami untuk pelayanan digital printing yang lain.".pSpace(6).$totLine6;
	}elseif($PrnTyp="PackingSlip"){
		$string.="Barang harap diperiksa dengan baik. Pengajuan klaim sesudah staff meninggalkan tempat tidak dilayani dan menjadi tanggung jawab pembeli.";
	}

	$string.=chr(13).chr(10);

	echo "<pre style='font-size:8pt;letter-spacing:-1.25px;'>";
	echo $string;
	echo "</pre>";
	
	$string=str_replace($dspHeader,$prnHeader,$string);

	$fh=fopen("print-digital/".$OrdNbr."$Suffix.txt", "w");
	fwrite($fh, chr(15).$string.chr(18));
	fclose($fh);
?>