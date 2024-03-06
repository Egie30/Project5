<?php
	include "framework/functions/dotmatrix.php";
	include "framework/database/connect.php";
	
	$OrdNbr		= $_GET['ORD_NBR'];
	$PrnTyp		= $_GET['PRN_TYP'];
	$tableType	= $_GET['TYP'];

	if($tableType == "EST"){
		$headtable 		= "RTL.RTL_ORD_HEAD_EST";
		$detailtable	= "RTL.RTL_ORD_DET_EST";
		$paymenttable	= "RTL.RTL_ORD_PYMT_EST";
	}else{
		$headtable 		= "RTL.RTL_ORD_HEAD";
		$detailtable	= "RTL.RTL_ORD_DET";
		$paymenttable	= "RTL.RTL_ORD_PYMT";
	}

	//Get default company
	$query="SELECT NAME,ADDRESS,ZIP,PHONE,CITY_NM,EMAIL FROM CMP.COMPANY COM LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID WHERE CO_NBR=$CoNbrDef";
	$result=mysql_query($query);
	$CmpDef=mysql_fetch_array($result);

	//Log print count
	$query="UPDATE ". $headtable ." SET IVC_PRN_CNT=IVC_PRN_CNT+1 WHERE ORD_NBR=".$OrdNbr;
	$result=mysql_query($query);

	//actgtyp
	$queryActg 	= "SELECT ACTG_TYP FROM ". $headtable ."  WHERE ORD_NBR = '".$OrdNbr."'";
	$resultActg = mysql_query($queryActg);
	$rowActg 	= mysql_fetch_array($resultActg);

	//Get invoice type description
	$query 	= "SELECT IVC_DESC FROM RTL.IVC_TYP WHERE IVC_TYP='".$PrnTyp."'";
	$result = mysql_query($query);
	$row 	= mysql_fetch_array($result);
	$IvcTyp = strtoupper($row['IVC_DESC']);
	$IvcTyp = "SALES";


	//echo $query;
	$query="SELECT ORD_NBR,DATE_FORMAT(CRT_TS,'%d-%m-%Y') AS CRT_DT,DATE_FORMAT(ORD_DTE,'%d-%m-%Y') AS ORD_DT,COALESCE(FEE_MISC, 0) AS FEE_MISC,COALESCE(PYMT_DOWN, 0) AS PYMT_DOWN,COALESCE(PYMT_REM, 0) AS PYMT_REM,COALESCE(DISC_AMT, 0) AS DISC_AMT,COALESCE(TOT_REM, 0) AS TOT_REM, 
			SHP.NAME AS SHP_NAME,
			CONCAT(SHP.ADDRESS,', ',SCT.CITY_NM) AS SHP_ADDRESS,
			SHP.PHONE AS SHP_PHONE,
			SHP.EMAIL AS SHP_EMAIL,
			SHP.ZIP AS SHP_ZIP,
			CONCAT(RCV.ADDRESS,', ',RCT.CITY_NM) AS RCV_ADDRESS,
			RCV.NAME AS RCV_NAME,
			RCV.PHONE AS RCV_PHONE,
			CONCAT(BILCOM.ADDRESS,', ',RCT.CITY_NM) AS BILCOM_ADDRESS,
			BILCOM.NAME AS BILCOM_NAME,
			BILCOM.PHONE AS BILCOM_PHONE,
			HED.ORD_TTL,
			REF_NBR,IVC_PRN_CNT,COALESCE(TAX_APL_ID, 0) AS TAX_APL_ID,COALESCE(TAX_AMT,0) AS TAX_AMT,COALESCE(TOT_AMT, 0) AS TOT_AMT
			FROM ". $headtable ." HED
			LEFT OUTER JOIN CMP.COMPANY SHP ON HED.SHP_CO_NBR=SHP.CO_NBR
			LEFT OUTER JOIN CMP.CITY SCT ON SHP.CITY_ID=SCT.CITY_ID
			LEFT OUTER JOIN CMP.COMPANY RCV ON HED.RCV_CO_NBR=RCV.CO_NBR
			LEFT OUTER JOIN CMP.COMPANY BILCOM ON HED.BIL_CO_NBR=BILCOM.CO_NBR
			LEFT OUTER JOIN CMP.CITY RCT ON RCV.CITY_ID=RCT.CITY_ID
			WHERE ORD_NBR=".$OrdNbr;
	//echo $query;
	$result = mysql_query($query);
	$row 	= mysql_fetch_array($result);

	$queryv  = "SELECT PHONE,EMAIL 
				FROM CMP.COMPANY COM 
				WHERE CO_NBR = $CoNbrDef";
	// echo $queryv;
	$resultv = mysql_query($queryv);
	$CmpLoc  = mysql_fetch_array($resultv);

	//Get default company
	if($rowActg['ACTG_TYP']==1)
	{
		$query 	= "SELECT NAME,ADDRESS,ZIP,PHONE,CITY_NM,EMAIL 
					FROM CMP.COMPANY COM 
					LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID 
					WHERE CO_NBR = $CoNbrPkp";
	} 
	$result = mysql_query($query);
	$CmpDef = mysql_fetch_array($result);	

	if($rowActg['ACTG_TYP']==1)
	{
		$Shp 	 = $CmpDef['NAME'];
		$Address = $CmpDef['ADDRESS'];
		$Zip 	 = $CmpDef['ZIP'];
		$citynm  = $CmpDef['CITY_NM'];
	}
	else
	{
		$Shp=$row['SHP_NAME'];
		$Address = $row['SHP_ADDRESS'];
		$Zip 	 = $row['SHP_ZIP'];
		$citynm  = $row['CITY_NM'];
	}

	// $header = followSpace($CmpDef['NAME'],59)."NOTA PRINTING".pSpace(45)."Nota No. ".leadZero($OrdNbr,6)."-".leadZero($row['IVC_PRN_CNT'],2).chr(13).chr(10);
	$header = followSpace($Shp,57)."NOTA ".$IvcTyp.pSpace(50-strlen($IvcTyp)).pSpace(3)."Nota No. ".leadZero($OrdNbr,7)."-".leadZero($row['IVC_PRN_CNT'],2).chr(13).chr(10);


	$Rcv=$row['RCV_NAME'];

	if ($row['BILCOM_NAME'] == '')
	{
		$bill = $row['RCV_NAME']." ".$row['RCV_ADDRESS']." ".$row['RCV_PHONE'];
	}
	else
	{
		$bill =  $row['BILCOM_NAME']." ".$row['BILCOM_ADDRESS']." ".$row['BILCOM_PHONE'];
	}
	
	$prnHeader.=chr(27)."(B".chr(12).chr(0).chr(5).chr(2).chr(-3).chr(11).chr(0).chr(2).leadZero($OrdNbr,6);
	$prnHeader.=pSpace(42)."Pengirim: ".followSpace($Shp,58)."Tanggal Nota: ".$row['ORD_DT'].chr(13).chr(10);
	$prnHeader.=pSpace(42)."Penerima: ".followSpace($Rcv,54).chr(13).chr(10);
	

	$dspHeader.=followSpace($Address.", ".$citynm." ".$Zip,111)."Tanggal Nota: ".$row['ORD_DT'].chr(13).chr(10);
	$dspHeader.=followSpace("Telp. ".$CmpLoc['PHONE'].", E-Mail: ".$CmpLoc['EMAIL'],108).chr(13).chr(10).chr(13).chr(10);

	$dspHeader.=followSpace("Pelanggan: ",11).$Rcv.chr(10);
	$dspHeader.=followSpace("Judul Pesanan: ",15).$row['ORD_TTL'].chr(13).chr(10);

	// $dspHeader.="Pengirim: ".followSpace($Shp,99).chr(13).chr(10);
	// $dspHeader.="Penerima: ".$Rcv.chr(13).chr(10);
	// $dspHeader.="Bill to: ".$bill.chr(13).chr(10);
	$header.=$dspHeader;
    $header.=str_repeat("-",135).chr(13).chr(10);
	if ($PrnTyp=="PO" || $PrnTyp=="SL"){
	$header.=" Jumlah                                  Deskripsi Pesanan                                   Harga            Disc             Subtotal".chr(13).chr(10);
	}else{
    	$header.=" Jumlah  Barcode                            Nama                             Harga       Lain2       Disc           Subtotal".chr(13).chr(10);
	}
    $header.=str_repeat("-",135).chr(13).chr(10);

	$string=$header;
	$rowCount=0;
	$pageCount=0;
	$query="SELECT 
		ORD_DET_NBR,
		ORD_NBR,
		DET.INV_NBR,
		INV.INV_BCD,
		INV.NAME,
		INV_DESC,
		INV.SIZE,
		CLR.COLR_DESC,
		INV.THIC,
		INV.WEIGHT,
		UNIT_DESC,
		ORD_Q,
		DET.INV_PRC,
		DET.PRC,
		COALESCE(FEE_MISC, 0) AS FEE_MISC,
		COALESCE(DISC_PCT, 0) AS DISC_PCT,
		COALESCE(DISC_AMT, 0) AS DISC_AMT,
		COALESCE(TOT_SUB, 0) AS TOT_SUB,
		CRT_TS,
		CRT_NBR,
		DET.UPD_TS,
		DET.UPD_NBR
	FROM ". $detailtable ." DET 
		LEFT OUTER JOIN RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR
		LEFT OUTER JOIN CMP.INV_COLR CLR ON INV.COLR_NBR=CLR.COLR_NBR
		LEFT OUTER JOIN RTL.UNIT_TYP UNT ON INV.CNT_X_TYP=UNT.UNIT_TYP
	WHERE DET.DEL_NBR = 0 AND ORD_NBR=".$OrdNbr."
	ORDER BY DET.ORD_DET_NBR ASC";
	//echo $query;
	$result=mysql_query($query);
	while($rowd=mysql_fetch_array($result))
	{

		if($rowd['ORD_X']!=''){ $X= " Uk ".$rowd['ORD_X']; }elseif($rowd['ORD_X']=='' || $rowd['ORD_X']==Null){$X="";}
		if($rowd['ORD_Y']!=''){ $Y= "x".$rowd['ORD_Y']; }elseif($rowd['ORD_Y']=='' || $rowd['ORD_Y']==Null){$Y="";}
		if($rowd['ORD_Z']!=''){ $Z= "x".$rowd['ORD_Z']; }elseif($rowd['ORD_Z']=='' || $rowd['ORD_Z']==Null){$Z="";}
		
		$rowCount++;	
		if($rowCount==15)
		{
		    $string.=str_repeat("-",135).chr(13).chr(10);
		    if($PrnTyp=="Invoice"){
				$string.=pSpace(107)."Total Halaman ".leadSpace($TotNet,14);
			}
			$string.=chr(13).chr(10);
			$string.=pRow(3);
			$string.="Dilanjutkan ke halaman berikutnya".chr(13).chr(10);
			$string.=$header;
			$rowCount=1;
		}
			
		$string.=leadSpace($rowd['ORD_Q'],7)."  ";
		$string.=followSpace($rowd['INV_BCD'],10)."  ";
		$string.=followSpace($rowd['NAME']." ".$rowd['SIZE']." ".$rowd['COLR_DESC']." ".$rowd['THIC']." ".trim($rowd['WEIGHT'])." ".$rowd['INV_DESC']."".$X."".$Y."".$Z,50)."   ";
		if(($PrnTyp=="SL")||($PrnTyp=="PO")){
			$string.=leadSpace($rowd['PRC'],24)."  ";
			//$string.=leadSpace($rowd['FEE_MISC'],14)."  ";
			$string.=leadSpace(number_format($rowd['DISC_PCT'],0,",",".")."/".number_format($rowd['DISC_AMT'],0,",","."),19)."  ";
			$string.=leadSpace($rowd['TOT_SUB'],13);
		}else{
			$string.=leadSpace($rowd['PRC'],9)."  ";
			$string.=leadSpace($rowd['FEE_MISC'],9)."  ";
			$string.=leadSpace(number_format($rowd['DISC_PCT'],0,",",".")."/".number_format($rowd['DISC_AMT'],0,",","."),13)."  ";
			// $string.=leadSpace($rowd['PRC'],9)."  ";
			$string.=leadSpace($rowd['TOT_SUB'],13);
		}
		$string.=chr(13).chr(10);
		$totSub+=$rowd['TOT_SUB'];
		$totPrc+=$rowd['PRC']*$rowd['ORD_Q'];
		$totCnt+=$rowd['ORD_Q'];
	}
	if($PrnTyp=="SL"){
		if($rowCount!=12){
			$string.=pRow(12-$rowCount);
		}
	}else{
		if($rowCount<=14){
			$string.=pRow(14-$rowCount);
		}
	}
    $string.=str_repeat("-",135).chr(13).chr(10);
	$string.=leadSpace("",9)."  ";
	if ($PrnTyp=="SL" || $PrnTyp=="PO" || $PrnTyp=="RC"){
		$Summary1="Biaya Tambahan ".leadSpace($row['FEE_MISC'],18);
		$Summary2=leadSpace("Total",35).leadSpace($row['TOT_AMT'],19);
		$Summary3=leadSpace("Uang Muka",116).leadSpace($row['PYMT_DOWN'],19);
		$Summary4=leadSpace("Pelunasan",35).leadSpace($row['PYMT_REM'],19);
		$Summary5=leadSpace("Sisa",35).leadSpace($row['TOT_REM'],19);
		$Summary6="";
	
		if($row['TAX_APL_ID']=="I")
		{
			$Summary6=leadSpace("Jumlah PPN",16).leadSpace($row['TAX_AMT'],19);
		}else if($row['TAX_APL_ID']=="A"){
			$Summary2=leadSpace("PPN",35).leadSpace($row['TAX_AMT'],19);
			$Summary3=leadSpace("Total",116).leadSpace($row['TOT_AMT'],19);
			$Summary4=leadSpace("Uang Muka",35).leadSpace($row['PYMT_DOWN'],19);
			$Summary5=leadSpace("Pelunasan",116).leadSpace($row['PYMT_REM'],19);
			$Summary6=leadSpace("Sisa",16).leadSpace($row['TOT_REM'],19);
		}

		$string.=pSpace(91).$Summary1.chr(13).chr(10);
		if ($PrnTyp=="RC"){
			$string.="      Pembuat     ".pSpace(5)."     Penerima     ".pSpace(5)."     Mengetahui      ".pSpace(14).$Summary2.chr(13).chr(10);
			$string.=$Summary3.chr(13).chr(10);
			$string.="(________________)".pSpace(5)."(_________________)".pSpace(5)."(_________________)".pSpace(14).$Summary4.chr(13).chr(10);
			$string.=$Summary5.chr(13).chr(10);
			$string.=$Summary6;
		}else if ($PrnTyp=="SL"){
			$string.="      Pembuat     ".pSpace(7)."     Pengirim     ".pSpace(5)."     Penerima      ".pSpace(14).$Summary2.chr(13).chr(10);
			$string.=$Summary3.chr(13).chr(10);
			$string.=pSpace(81).$Summary4.chr(13).chr(10);
			$string.="(________________)".pSpace(6)."(_________________)".pSpace(5)."(_________________)".pSpace(14).$Summary5.chr(13).chr(10);
			$string.=pSpace(5)."".$_SESSION['userID'];
			//$string.=$Summary5;
			//$string.=$Summary6;
		} else {
			$string.=pSpace(14)."     Penerima     ".pSpace(31)."     Penjual      ".$Summary2.chr(13).chr(10);
			$string.=$Summary3.chr(13).chr(10);
			$string.=pSpace(13)."(_________________)".pSpace(30)."(_________________)".$Summary4.chr(13).chr(10);
			$string.=$Summary5.chr(13).chr(10);
			$string.="Terima kasih atas kepercayaan anda. Silakan hubungi kami untuk produk stationery/paper yang lain.   ".$Summary6;
		}
	}else{

		// Pembayaran
		if($row['TAX_APL_ID']=="E"){
		$totLine1="Biaya Tambahan ".leadSpace($row['FEE_MISC'],11);
		$totLine2="         Total ".leadSpace($row['TOT_AMT'],14);
		
		$querypymt="SELECT TND_AMT,TOT_REM,PYMT.CRT_TS FROM ". $paymenttable ." PYMT 
					LEFT OUTER JOIN ". $headtable ." HED ON PYMT.ORD_NBR=HED.ORD_NBR
					WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=".$OrdNbr." ORDER BY PYMT.CRT_TS ASC";
		$resultpymt=mysql_query($querypymt);
		$rowttl=mysql_num_rows($resultpymt);
		$rowpym=mysql_fetch_array($resultpymt);
			if($rowttl == 1 && $rowpym['TOT_REM']==0){$TotAmt=0;}else{$TotAmt=$rowpym['TND_AMT'];}
			$totLine3="     Uang Muka ".leadSpace($TotAmt,16);
		
		$querypymt="SELECT TND_AMT,TOT_REM,PYMT.CRT_TS FROM ". $paymenttable ." PYMT 
					LEFT OUTER JOIN ". $headtable ." HED ON PYMT.ORD_NBR=HED.ORD_NBR
					WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=".$OrdNbr." ORDER BY PYMT.CRT_TS DESC";
		$resultpymt=mysql_query($querypymt);
		$rowpym=mysql_fetch_array($resultpymt);
			if($rowpym['TOT_REM'] == 0){$TotAmt=$rowpym['TND_AMT'];}else{$TotAmt=0;}
			$totLine4="Pelunasan ".leadSpace($TotAmt,13);
		
		$totLine5="          Sisa ".leadSpace($row['TOT_REM'],14);
		$totLine6="";
	}elseif($row['TAX_APL_ID']=="A"){
		$totLine1="Biaya Tambahan ".leadSpace($row['FEE_MISC'],11);
		$totLine2="           PPN ".leadSpace($row['TAX_AMT'],14);
		$totLine3="         Total ".leadSpace($row['TOT_AMT'],14);
		
		$querypymt="SELECT TND_AMT,TOT_REM,PYMT.CRT_TS FROM ". $paymenttable ." PYMT 
					LEFT OUTER JOIN ". $headtable ." HED ON PYMT.ORD_NBR=HED.ORD_NBR
					WHERE PYMT.ORD_NBR=".$OrdNbr." ORDER BY PYMT.CRT_TS ASC";
		$resultpymt=mysql_query($querypymt);
		$rowttl=mysql_num_rows($resultpymt);
		$rowpym=mysql_fetch_array($resultpymt);
			if($rowttl == 1 && $rowpym['TOT_REM']==0){$TotAmt=0;}else{$TotAmt=$rowpym['TND_AMT'];}
			$totLine4="     Uang Muka ".leadSpace($TotAmt,16);
		
		$querypymt="SELECT TND_AMT,TOT_REM,PYMT.CRT_TS FROM ". $paymenttable ." PYMT 
					LEFT OUTER JOIN ". $headtable ." HED ON PYMT.ORD_NBR=HED.ORD_NBR
					WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=".$OrdNbr." ORDER BY PYMT.CRT_TS DESC";
		$resultpymt=mysql_query($querypymt);
		$rowpym=mysql_fetch_array($resultpymt);
			if($rowpym['TOT_REM'] == 0){$TotAmt=$rowpym['TND_AMT'];}else{$TotAmt=0;}
			$totLine5="Pelunasan ".leadSpace($TotAmt,13);
		
		$totLine6="          Sisa ".leadSpace($row['TOT_REM'],14);
	}elseif($row['TAX_APL_ID']=="I"){
		$totLine1="Biaya Tambahan ".leadSpace($row['FEE_MISC'],11);
		$totLine2="         Total ".leadSpace($row['TOT_AMT'],14);
		$querypymt="SELECT TND_AMT,TOT_REM,PYMT.CRT_TS FROM ". $paymenttable ." PYMT 
					LEFT OUTER JOIN ". $headtable ." HED ON PYMT.ORD_NBR=HED.ORD_NBR
					WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=".$OrdNbr." ORDER BY PYMT.CRT_TS ASC";
		$resultpymt=mysql_query($querypymt);
		$rowttl=mysql_num_rows($resultpymt);
		$rowpym=mysql_fetch_array($resultpymt);
			if($rowttl == 1 && $rowpym['TOT_REM']==0){$TotAmt=0;}else{$TotAmt=$rowpym['TND_AMT'];}
			$totLine3="     Uang Muka ".leadSpace($TotAmt,16);
		
		$querypymt="SELECT TND_AMT,TOT_REM,PYMT.CRT_TS FROM ". $paymenttable ." PYMT 
					LEFT OUTER JOIN ". $headtable ." HED ON PYMT.ORD_NBR=HED.ORD_NBR
					WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=".$OrdNbr." ORDER BY PYMT.CRT_TS DESC";
		$resultpymt=mysql_query($querypymt);
		$rowpym=mysql_fetch_array($resultpymt);
			if($rowpym['TOT_REM'] == 0){$TotAmt=$rowpym['TND_AMT'];}else{$TotAmt=0;}
			$totLine4="Pelunasan ".leadSpace($TotAmt,13);

		$totLine5="          Sisa ".leadSpace($row['TOT_REM'],14);
		$totLine6="    Jumlah PPN ".leadSpace($row['TAX_AMT'],14);
	}

		$string.=leadSpace("Subtotal ",72).leadSpace($totSub,17).leadSpace("Tax      ",12).leadSpace($row['TAX_AMT'],14).chr(13).chr(10);
		$string.="      Pembuat     ".pSpace(7)."     Penerima     ".pSpace(6)."     Mengetahui      ";
		$string.=pSpace(4).$totLine1.pSpace(3)."TOTAL Rp.".leadSpace($row['TOT_AMT'],14)."".chr(13).chr(10);
		$string.=pSpace(69).$totLine3.pSpace(3).$totLine4. chr(13).chr(10);
		$string.="(________________)".pSpace(7)."(_________________)".pSpace(5)."(_________________)"."".pSpace(35)."Sisa Rp. ".leadSpace($row['TOT_REM'],14)."".chr(13).chr(10);
	}
	$string.=chr(13).chr(10);

	echo "<pre style='font-size:9pt;letter-spacing:-1.25px;'>";
	echo $string;
	echo "</pre>";
	
	if($PrnTyp=='SL'){
		$string=str_replace($dspHeader,$prnHeader,$string);
	}

	$fh=fopen("print-digital/ORD-".$OrdNbr.".txt", "w");
	fwrite($fh, chr(15).$string.chr(18));
	fclose($fh);

?>
