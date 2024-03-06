<?php
	include "framework/functions/dotmatrix.php";
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	$TrnspNbr=$_GET['TRNSP_NBR'];

	$queryActg 	= "SELECT ACTG_TYP FROM RTL.TRNSP_HEAD WHERE TRNSP_NBR = '".$TrnspNbr."'";
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
	//echo $query;
	$result=mysql_query($query);
	$CmpDef=mysql_fetch_array($result);

    //Increment print count
    $query="UPDATE RTL.TRNSP_HEAD SET SLP_PRN_CNT=SLP_PRN_CNT+1 WHERE TRNSP_NBR=".$TrnspNbr;
    //echo $query;
    $resultb=mysql_query($query);
	
	$query="SELECT 
		TRNSP_NBR,
		THD.ORD_NBR,
		DATE_FORMAT(TRNSP_TS,'%d-%m-%Y') AS TRNSP_DT,
		TRNSP_TS,
		DATE_FORMAT(ORD_DTE,'%d-%m-%Y') AS ORD_DT,
		ORD_DTE,
		STT.TRNSP_STT_ID,
		STT.TRNSP_STT_DESC,
		COM.NAME AS NAME_COM,
		OHD.RCV_CO_NBR,
		THD.REF_NBR,
		THD.ORD_TTL,
		THD.DUE_TS,
		THD.SPC_NTE,
		SLP_PRN_CNT,
		OHD.IVC_PRN_CNT,
		THD.TRNSP_DESC
	FROM RTL.TRNSP_HEAD THD
		INNER JOIN RTL.TRNSP_STT STT ON THD.TRNSP_STT_ID=STT.TRNSP_STT_ID
		LEFT OUTER JOIN RTL.RTL_ORD_HEAD OHD ON THD.ORD_NBR=OHD.ORD_NBR
		LEFT OUTER JOIN CMP.COMPANY COM ON THD.RCV_CO_NBR=COM.CO_NBR
		LEFT OUTER JOIN CDW.PRN_DIG_TOP_CUST TOP ON OHD.RCV_CO_NBR=TOP.NBR
	WHERE TRNSP_NBR=".$TrnspNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
    //echo "<pre>".$query;
	
    $Suffix="P";
    if($row['TRNSP_STT_ID']=="ST"){
		$header=followSpace($CmpDef['NAME'],59)." TANDA AMBIL ".pSpace(33)."Nota Tanda Ambil No. ".leadZero($TrnspNbr,6)."-".leadZero($row['SLP_PRN_CNT'],2).chr(13).chr(10);
	} else if($row['TRNSP_STT_ID']=="RP"){
		$header=followSpace($CmpDef['NAME'],59)." TANDA TERIMA ".pSpace(31)."Nota Tanda Terima No. ".leadZero($TrnspNbr,6)."-".leadZero($row['SLP_PRN_CNT'],2).chr(13).chr(10);
	} else {
		$header=followSpace($CmpDef['NAME'],59)." SURAT JALAN ".pSpace(45)."Nota No. ".leadZero($TrnspNbr,6)."-".leadZero($row['SLP_PRN_CNT'],2).chr(13).chr(10);
	}

    $header.=followSpace($CmpDef['ADDRESS'].", ".$CmpDef['CITY_NM']." ".$CmpDef['ZIP'],110)."Tanggal Order: ".$row['ORD_DT'].chr(13).chr(10);
    if($row['TRNSP_STT_ID']=="ST"){
    	$header.=followSpace("Telp. ".$CmpDef['PHONE'].", E-Mail: ".$CmpDef['EMAIL'],110)."Tanggal Ambil: ".$row['TRNSP_DT'].chr(13).chr(10);
	} else if($row['TRNSP_STT_ID']=="RP"){
		$header.=followSpace("Telp. ".$CmpDef['PHONE'].", E-Mail: ".$CmpDef['EMAIL'],109)."Tanggal Terima: ".$row['TRNSP_DT'].chr(13).chr(10);
	} else {
		$header.=followSpace("Telp. ".$CmpDef['PHONE'].", E-Mail: ".$CmpDef['EMAIL'],104)."Tanggal Surat Jalan: ".$row['TRNSP_DT'].chr(13).chr(10);
	}
	$header.=pSpace(116)."No Order: ".leadZero($row['ORD_NBR'],6)."-".leadZero($row['IVC_PRN_CNT'],2).chr(14).chr(10);
	
	if(($row['TRNSP_STT_ID']=="ST")&&($row['TRNSP_DESC']!="")){
		$customer=$row['TRNSP_DESC'];
	} else if(($row['TRNSP_STT_ID']=="RP")){
		if($row['TRNSP_DESC']==""){
			$customer="- | (".$row['NAME_COM'].")";
		} else {
			$customer=$row['TRNSP_DESC']." | (".$row['NAME_COM'].")";
		}
	} else {
		$customer=trim($row['NAME_PPL']." ".$row['NAME_COM']);
		if($customer==""){$customer="Tunai";}
	}
	
	$prnHeader=chr(27)."(B".chr(12).chr(0).chr(5).chr(2).chr(-3).chr(11).chr(0).chr(2).leadZero($TrnspNbr,6);
	if($row['TRNSP_STT_ID']=="ST"){
		$prnHeader.=pSpace(42)."Pengambil: ".$customer.chr(10);
		$prnHeader.=pSpace(42)."Judul Pesanan: ".$row['ORD_TTL'].chr(13).chr(10);
	} else if($row['TRNSP_STT_ID']=="RP"){
		$prnHeader.=pSpace(42)."Penerima: ".$customer.chr(10);
		$prnHeader.=pSpace(42)."Judul Pesanan: ".$row['ORD_TTL'].chr(13).chr(10);
	} else {
		$prnHeader.=pSpace(42)."Tujuan: ".$customer.chr(10);
		$prnHeader.=pSpace(42)."Judul Pesanan: ".$row['ORD_TTL'].chr(13).chr(10);
	}

	if($row['TRNSP_STT_ID']=="ST"){
		$dspHeader.="Pengambil: ".$customer.chr(10);
		$dspHeader.="Judul Pesanan: ".$row['ORD_TTL'].chr(13).chr(10);
	} else if($row['TRNSP_STT_ID']=="RP"){
		$dspHeader.="Penerima: ".$customer.chr(10);
		$dspHeader.="Judul Pesanan: ".$row['ORD_TTL'].chr(13).chr(10);
	} else {
		$dspHeader.="Tujuan: ".$customer.chr(10);
		$dspHeader.="Judul Pesanan: ".$row['ORD_TTL'].chr(13).chr(10);
	}

	$header.=$dspHeader;
    $header.=str_repeat("-",135).chr(13).chr(10);
    $spacing=55;

    $header.=" Jumlah".pSpace(7)."Barcode".pSpace(50)."Deskripsi Barang";
	$header.=chr(13).chr(10);
    $header.=str_repeat("-",135).chr(13).chr(10);

	$string		= $header;
	$rowCount	= 0;
	$pageCount	= 0;
	$query="SELECT 
		TRNSP_DET_NBR,
		TRNSP_NBR,
		TRNSP_Q,
		TDT.ORD_DET_NBR,
		ODT.INV_DESC,
		TDT.DET_TTL AS TRSNP_TTL,
		INV.INV_BCD,
		INV.NAME AS NAME
	FROM RTL.TRNSP_DET TDT 
		LEFT OUTER JOIN RTL.RTL_ORD_DET ODT ON TDT.ORD_DET_NBR=ODT.ORD_DET_NBR 
		LEFT OUTER JOIN RTL.INVENTORY INV ON ODT.INV_NBR=INV.INV_NBR 
	WHERE TRNSP_NBR=".$TrnspNbr." AND TDT.DEL_NBR=0
	ORDER BY TDT.TRNSP_DET_NBR ASC";
    //echo $query;
	$result=mysql_query($query);
	while($rowd=mysql_fetch_array($result))
	{
		$rowCount++;	
		if($rowCount==12)
		{
		    $string.=str_repeat("-",135).chr(13).chr(10);
			$string.=chr(13).chr(10);
			$string.=pRow(4);
			$string.="Dilanjutkan ke halaman berikutnya".chr(13).chr(10);
			$string.=$header;
			$rowCount=1;
		}
		
		//$string.=leadSpace($rowd['TRNSP_Q'],7+$indent)." ";
		//$string.=pSpace(2)."".trim($rowd['INV_BCD']."".pSpace(10)."".trim($rowd['TRSNP_TTL']." ".$rowd['NAME']));
		
		$string.=leadSpace($rowd['TRNSP_Q'],7)."  ";
		$string.=followSpace($rowd['INV_BCD'],15)."  ";
		$string.=followSpace(trim($rowd['TRSNP_TTL'])." ".$rowd['NAME'],50)."   ";
		$string.=chr(13).chr(10);
	}
	
	if($rowCount!=11){
		$string.=pRow(11-$rowCount);
	}

    $spacing=19;
    $string.=str_repeat("-",135).chr(13).chr(10);
	$string.=chr(13).chr(10);
	if($row['TRNSP_STT_ID']=="ST"){
		$string.=pSpace(18+$spacing)."Pengambil".pSpace(40);
    	$string.="Petugas";
	} else {
		$string.=pSpace(18+$spacing)."Penerima".pSpace(40);
    	$string.="Pengantar";
	}
	$string.=chr(13).chr(10);
	$string.=pSpace(13+$spacing)."(________________)".pSpace(30)."(_________________)";
	$string.=chr(13).chr(10);
	$string.=chr(13).chr(10);
	$string.=chr(13).chr(10);
    $string.="Barang harap diperiksa dengan baik. Pengajuan klaim sesudah staff meninggalkan tempat tidak dilayani dan menjadi tanggung jawab pembeli.";
	$string.=chr(13).chr(10);

	echo "<pre style='font-size:8pt;letter-spacing:-1.25px;'>";
	echo $string;
	echo "</pre>";
	
	$string=str_replace($dspHeader,$prnHeader,$string);

	$fh=fopen("print-digital/".$TrnspNbr."$Suffix.txt", "w");
	fwrite($fh, chr(15).$string.chr(18));
	fclose($fh);
?>
