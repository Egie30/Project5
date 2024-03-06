<?php
	include "framework/functions/dotmatrix.php";
	include "framework/database/connect.php";
	
	$OrdNbr=$_GET['ORD_NBR'];
	$PrnTyp=$_GET['PRN_TYP'];

	//Get default company
	$query="SELECT NAME,ADDRESS,ZIP,PHONE,CITY_NM,EMAIL FROM CMP.COMPANY COM LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID WHERE CO_NBR=$CoNbrDef";
	$result=mysql_query($query);
	$CmpDef=mysql_fetch_array($result);
	//Log print count
	$query="UPDATE RTL.RTL_STK_HEAD SET IVC_PRN_CNT=IVC_PRN_CNT+1 WHERE ORD_NBR=".$OrdNbr;
	$result=mysql_query($query);

	//Get invoice type description
	$query="SELECT IVC_DESC FROM RTL.IVC_TYP WHERE IVC_TYP='".$PrnTyp."'";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$IvcTyp=strtoupper($row['IVC_DESC']);
	//echo $query;
	$query="SELECT ORD_NBR,DATE_FORMAT(CRT_TS,'%d-%m-%Y') AS CRT_DT,DATE_FORMAT(ORD_DTE,'%d-%m-%Y') AS ORD_DT,FEE_MISC,PYMT_DOWN,PYMT_REM,DISC_AMT,TOT_REM, 
			SHP.NAME AS SHP_NAME,SHP.ADDRESS AS SHP_ADDRESS,RCV.ADDRESS AS RCV_ADDRESS,RCV.NAME AS RCV_NAME,REF_NBR,IVC_PRN_CNT,TAX_APL_ID,TAX_AMT,TOT_AMT
			FROM RTL.RTL_STK_HEAD HED
			LEFT OUTER JOIN CMP.COMPANY SHP ON HED.SHP_CO_NBR=SHP.CO_NBR
			LEFT OUTER JOIN CMP.CITY SCT ON SHP.CITY_ID=SCT.CITY_ID
			LEFT OUTER JOIN CMP.COMPANY RCV ON HED.RCV_CO_NBR=RCV.CO_NBR
			LEFT OUTER JOIN CMP.CITY RCT ON RCV.CITY_ID=RCV.CITY_ID
			WHERE ORD_NBR=".$OrdNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$header="NOTA ".$IvcTyp.pSpace(50-strlen($IvcTyp)).pSpace(62)."Nota No. ".leadZero($OrdNbr,6)."-".leadZero($row['IVC_PRN_CNT'],2).chr(13).chr(10);
	$Shp=$row['SHP_NAME']." ".$row['SHP_ADDRESS'];
	$Rcv=$row['RCV_NAME']." ".$row['RCV_ADDRESS'];
	
	$prnHeader.=chr(27)."(B".chr(12).chr(0).chr(5).chr(2).chr(-3).chr(11).chr(0).chr(2).leadZero($OrdNbr,6);
	$prnHeader.=pSpace(42)."Pengirim: ".followSpace($Shp,58)."Tanggal Nota: ".$row['ORD_DT'].chr(13).chr(10);
	$prnHeader.=pSpace(42)."Penerima: ".followSpace($Rcv,54).chr(13).chr(10);
	
	$dspHeader.="Pengirim: ".followSpace($Shp,101)."Tanggal Nota: ".$row['ORD_DT'].chr(13).chr(10);
	$dspHeader.="Penerima: ".followSpace($Rcv,100).chr(13).chr(10);
	$header.=$dspHeader;
    $header.=str_repeat("-",135).chr(13).chr(10);
	if ($PrnTyp=="PO" || $PrnTyp=="SL"){
	$header.=" Jumlah                                  Deskripsi Pesanan                                   Harga            Disc             Subtotal".chr(13).chr(10);
	}else{
    	$header.=" Jumlah  Barcode                            Nama                             Faktur       Disc       Subtotal       Jual       Subtotal".chr(13).chr(10);
	}
    $header.=str_repeat("-",135).chr(13).chr(10);

	$string=$header;
	$rowCount=0;
	$pageCount=0;
	$query="SELECT ORD_DET_NBR,ORD_NBR,DET.INV_NBR,INV.INV_BCD,INV.NAME,INV_DESC,ORD_Q,DET.INV_PRC,PRC,FEE_MISC,DISC_PCT,DISC_AMT,TOT_SUB,CRT_TS,CRT_NBR,DET.UPD_TS,DET.UPD_NBR
				FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR
				WHERE ORD_NBR=".$OrdNbr."
				ORDER BY DET.ORD_DET_NBR ASC";
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
			
		$string.=leadSpace($rowd['ORD_Q'],7)."  ";
		$string.=followSpace($rowd['INV_BCD'],10)."  ";
		$string.=followSpace($rowd['NAME']." ".$rowd['INV_DESC'],50)."   ";
		if(($PrnTyp=="SL")||($PrnTyp=="PO")){
			$string.=leadSpace($rowd['INV_PRC']+$rowd['FEE_MISC'],24)."  ";
			$string.=leadSpace($rowd['DISC_AMT'],14)."  ";
			$string.=leadSpace($rowd['TOT_SUB'],19)."  ";
		}else{
			$string.=leadSpace($rowd['INV_PRC']+$rowd['FEE_MISC'],9)."  ";
			$string.=leadSpace($rowd['DISC_AMT'],9)."  ";
			$string.=leadSpace($rowd['TOT_SUB'],13)."  ";
			$string.=leadSpace($rowd['PRC'],9)."  ";
			$string.=leadSpace($rowd['PRC']*$rowd['ORD_Q'],13);
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
		$string.=pSpace(79)."TOTAL";
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
