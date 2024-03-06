<?php
	include "framework/functions/dotmatrix.php";
	include "framework/database/connect.php";
	
	$OrdNbr=$_GET['ORD_NBR'];
	$ordTyp=$_GET['ORD_TYP'];
	$selConbr=$_GET['SEL_CO_NBR'];
	$buyConbr=$_GET['BUY_CO_NBR'];
	$prnConbr=$_GET['PRN_CO_NBR'];


	//Get default company
	$query="SELECT NAME,ADDRESS,ZIP,PHONE,CITY_NM,EMAIL FROM CMP.COMPANY COM LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID WHERE CO_NBR=".$selConbr."";
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$Shp=$row['NAME']." ".$row['ADDRESS']." ".$row['CITY_NM'];
	//Log print count
	$query="UPDATE RTL.RTL_STK_HEAD SET IVC_PRN_CNT=IVC_PRN_CNT+1 WHERE ORD_NBR=".$OrdNbr;
	$result=mysql_query($query);

	//Get invoice type description
	$query="SELECT ORD_TYP, ORD_DESC FROM CMP.ORD_TYP WHERE ORD_TYP='".$ordTyp."'";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$ordDesc=strtoupper($row['ORD_DESC']);
	//echo $query;
	$query="SELECT ORD_NBR,ORD_DTE,REF_NBR,REQ_NBR,ORD_TYP,SEL_CO_NBR,BUY_CO_NBR,ORD_TTL,PRN_DTE,PRN_CO_NBR,FEE_FLM,FEE_MISC,CMP_DTE,PYMT_DOWN,PYMT_REM,TOT_AMT,SPC_NTE,PU_DTE,TOT_REM
							FROM CMP.CAL_ORD_HEAD WHERE ORD_NBR=".$OrdNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$ordDte=$row['ORD_DTE'];
	$ordTtl=$row['ORD_TTL'];
	
	$query="SELECT NAME,ADDRESS,ZIP,PHONE,CITY_NM,EMAIL FROM CMP.COMPANY COM LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID WHERE CO_NBR=".$buyConbr."";
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$Rcv=$row['NAME']." ".$row['ADDRESS']." ".$row['CITY_NM'];
	
	$query="SELECT NAME,ADDRESS,ZIP,PHONE,CITY_NM,EMAIL FROM CMP.COMPANY COM LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID WHERE CO_NBR=".$prnConbr."";
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$prn=$row['NAME']." ".$row['ADDRESS']." ".$row['CITY_NM'];
	
	if ($ordTyp=="INV"){
	$header="".$ordDesc." KALENDER".pSpace(50-strlen($ordDesc)).pSpace(58)."Nota No. : ".leadZero($OrdNbr,6)."-".leadZero($row['IVC_PRN_CNT'],3).chr(13).chr(10);
	}else{
	$header="NOTA ".$ordDesc.pSpace(50-strlen($ordDesc)).pSpace(58)."Nota No. : ".leadZero($OrdNbr,6)."-".leadZero($row['IVC_PRN_CNT'],3).chr(13).chr(10);
	}
	$prnHeader.=chr(27)."(B".chr(12).chr(0).chr(5).chr(2).chr(-3).chr(11).chr(0).chr(2).leadZero($OrdNbr,6);
	$PrnHeader.=pSpace(42)."Judul Pesanan : ".followSpace($ordTtl,52)."Tanggal Nota //: ".$ordDte.chr(13).chr(10);
	$prnHeader.=pSpace(42)."Penjual       : ".followSpace($Shp,56).chr(13).chr(10);
	$prnHeader.=pSpace(42)."Pembeli       : ".followSpace($Rcv,52).chr(13).chr(10);
	$prnHeader.=pSpace(42)."Pencetak      : ".followSpace($prn,50).chr(13).chr(10);
	
	$dspHeader.="Judul Pesanan : ".followSpace($ordTtl,93)."Tanggal Nota : ".$ordDte.chr(13).chr(10);
	$dspHeader.="Penjual       : ".followSpace($Shp,97).chr(13).chr(10);
	$dspHeader.="Pembeli       : ".followSpace($Rcv,98).chr(13).chr(10);
	$dspHeader.="Pencetak      : ".followSpace($prn,99).chr(13).chr(10);
	$header.=$dspHeader;
    $header.=str_repeat("-",135).chr(13).chr(10);
	if ($PrnTyp=="PO" || $PrnTyp=="SL"){
	$header.=" Jumlah                                Deskripsi Pesanan                                     Harga            Disc             Subtotal".chr(13).chr(10);
	}else{
    	$header.=" Jumlah       Kode                     Deskripsi                    Harga       Disc      Klem      warna       lain2         Subtotal".chr(13).chr(10);
	}
    $header.=str_repeat("-",135).chr(13).chr(10);

	$string=$header;
	$rowCount=0;
	$pageCount=0;
	$query="SELECT ORD_DET_NBR,DET.CAL_NBR,CONCAT(CO_ID,CAL_ID,CAL_TYP) AS CAL_CODE,CAL_DESC,ORD_Q,CASE WHEN PRN_F=1 THEN CAL_PRC_PRN ELSE CAL_PRC_BLK END AS CAL_PRC,FEE_CLM,FEE_CLR,FEE_MISC,FAIL_CNT,DISC_AMT,TOT_SUB
				FROM CMP.CAL_ORD_DET DET
					INNER JOIN CMP.CAL_LST LST ON DET.CAL_NBR=LST.CAL_NBR
					INNER JOIN CMP.COMPANY COM ON LST.CO_NBR=COM.CO_NBR
				WHERE ORD_NBR=".$OrdNbr."";
		//echo $query;
	$result=mysql_query($query);
	while($rowd=mysql_fetch_array($result))
	{
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
			
		$string.=leadSpace($rowd['ORD_Q'],9)."  ";
		$string.=followSpace($rowd['CAL_CODE'],11)."  ";
		$string.=followSpace($rowd['CAL_DESC'],40)."  ";
		/*if(($PrnTyp=="SL")||($PrnTyp=="PO")){
			$string.=leadSpace($rowd['INV_PRC']+$rowd['FEE_MISC'],24)."  ";
			$string.=leadSpace($rowd['DISC_AMT'],14)."  ";
			$string.=leadSpace($rowd['TOT_SUB'],19)."  ";
		}else{
		*/	$string.=leadSpace($rowd['CAL_PRC'],9)."  ";
			$string.=leadSpace($rowd['DISC_AMT'],9)."  ";
			$string.=leadSpace($rowd['FEE_CLM'],9)."  ";
			$string.=leadSpace($rowd['FEE_CLR'],9)."  ";
			$string.=leadSpace($rowd['FEE_MISC'],9)."  ";
			$string.=leadSpace($rowd['TOT_SUB'],13);
		//}
		$string.=chr(13).chr(10);
		$totSub+=$rowd['TOT_SUB'];
		//$totPrc+=$rowd['PRC']*$rowd['ORD_Q'];
		//$totCnt+=$rowd['ORD_Q'];
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
	if ($PrnTyp=="SL" || $PrnTyp=="PO"){
		$Summary1="Biaya Tambahan ".leadSpace($row['FEE_MISC'],18);
		$Summary2=leadSpace("Total",35).leadSpace($row['TOT_AMT'],19);
		$Summary3=leadSpace("Uang Muka",116).leadSpace($row['PYMT_DOWN'],19);
		$Summary4=leadSpace("Pelunasan",35).leadSpace($row['PYMT_REM'],19);
		$Summary5=leadSpace("Sisa",116).leadSpace($row['TOT_REM'],19);
		$Summary6="";
	
		if($row['TAX_APL_ID']=="I"){
			$Summary6=leadSpace("Jumlah PPN",16).leadSpace($row['TAX_AMT'],19);
			}else if($row['TAX_APL_ID']=="A"){
			$Summary2=leadSpace("PPN",35).leadSpace($row['TAX_AMT'],19);
			$Summary3=leadSpace("Total",116).leadSpace($row['TOT_AMT'],19);
			$Summary4=leadSpace("Uang Muka",35).leadSpace($row['PYMT_DOWN'],19);
			$Summary5=leadSpace("Pelunasan",116).leadSpace($row['PYMT_REM'],19);
			$Summary6=leadSpace("Sisa",16).leadSpace($row['TOT_REM'],19);
		}

		$string.=pSpace(91).$Summary1.chr(13).chr(10);
		$string.=pSpace(14)."     Penerima     ".pSpace(31)."     Penjual      ".$Summary2.chr(13).chr(10);
		$string.=$Summary3.chr(13).chr(10);
		$string.=pSpace(13)."(_________________)".pSpace(30)."(_________________)".$Summary4.chr(13).chr(10);
		$string.=$Summary5.chr(13).chr(10);
		$string.="Terima kasih atas kepercayaan anda. Silakan hubungi kami untuk produk stationery/paper yang lain.   ".$Summary6;
	}else{
		$string.=pSpace(103)."TOTAL";
		$string.=leadSpace($totSub,15);
		$string.=pSpace(10);
		$string.=leadSpace($totPrc,15).chr(13).chr(10);
		$string.="      Pembuat     ".pSpace(5)."     Penerima     ".pSpace(5)."     Mengetahui      ".chr(13).chr(10).chr(13).chr(10).chr(13).chr(10);
		$string.="(________________)".pSpace(5)."(_________________)".pSpace(5)."(_________________)";
	}
	$string.=chr(13).chr(10);

	echo "<pre style='font-size:9pt;letter-spacing:-1.25px;'>";
	echo $string;
	echo "</pre>";
	
	if($PrnTyp=='SL'){
		$string=str_replace($dspHeader,$prnHeader,$string);
	}

	$fh=fopen("print-digital/R".$OrdNbr.".txt", "w");
	fwrite($fh, chr(15).$string.chr(18));
	fclose($fh);
?>
