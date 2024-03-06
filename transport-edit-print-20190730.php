<?php
	include "framework/functions/dotmatrix.php";
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	$TrnspNbr=$_GET['TRNSP_NBR'];
	//Get default company
	$query="SELECT NAME,ADDRESS,ZIP,PHONE,CITY_NM,EMAIL FROM CMP.COMPANY COM LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID WHERE CO_NBR=$CoNbrDef";
	//echo $query;
	$result=mysql_query($query);
	$CmpDef=mysql_fetch_array($result);

    //Increment print count
    $query="UPDATE CMP.TRNSP_HEAD SET SLP_PRN_CNT=SLP_PRN_CNT+1 WHERE TRNSP_NBR=".$TrnspNbr;
    //echo $query;
    $resultb=mysql_query($query);
	
	$query="SELECT TRNSP_NBR,THD.ORD_NBR,DATE_FORMAT(TRNSP_TS,'%d-%m-%Y') AS TRNSP_DT,TRNSP_TS,DATE_FORMAT(ORD_TS,'%d-%m-%Y') AS ORD_DT,ORD_TS,STT.TRNSP_STT_ID,STT.TRNSP_STT_DESC,BUY_PRSN_NBR,PPL.NAME AS NAME_PPL,COM.NAME AS NAME_COM,RCV_CO_NBR,THD.REF_NBR,THD.ORD_TTL,THD.DUE_TS,THD.SPC_NTE,SLP_PRN_CNT,OHD.IVC_PRN_CNT
			FROM CMP.TRNSP_HEAD THD
			INNER JOIN CMP.TRNSP_STT STT ON THD.TRNSP_STT_ID=STT.TRNSP_STT_ID
            LEFT OUTER JOIN CMP.PRN_DIG_ORD_HEAD OHD ON THD.ORD_NBR=OHD.ORD_NBR
			LEFT OUTER JOIN CMP.PEOPLE PPL ON OHD.BUY_PRSN_NBR=PPL.PRSN_NBR
			LEFT OUTER JOIN CMP.COMPANY COM ON THD.RCV_CO_NBR=COM.CO_NBR
			LEFT OUTER JOIN CDW.PRN_DIG_TOP_CUST TOP ON OHD.BUY_CO_NBR=TOP.NBR
			WHERE TRNSP_NBR=".$TrnspNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
    //echo $query;
	
	if($row['IVC_PRN_CNT']==1){
		//Copy the innerHTML from print-digital.php
		$due=strtotime($row['DUE_TS']);
		$OrdSttId=$row['ORD_STT_ID'];
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
			$newStr.="<img class='listable' src='img/favorite.png'>";
		}				
		if($row['SPC_NTE']!=""){
			$newStr.="<img class='listable' src='img/conversation.png'>";
		}
		if($row['DL_CNT']>0){
			$newStr.="<img class='listable' src='img/truck.png'>";
		}
		if($row['PU_CNT']>0){
			$newStr.="<img class='listable' src='img/cart.png'>";
		}
		if($row['NS_CNT']>0){
			$newStr.="<img class='listable' src='img/flag.png'>";
		}
		if($row['IVC_PRN_CNT']>0){
			$newStr.="<img class='listable' src='img/printed.png'>";
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

    $Suffix="P";
    $header=followSpace($CmpDef['NAME'],59)." SURAT JALAN ".pSpace(45)."Nota No. ".leadZero($TrnspNbr,6)."-".leadZero($row['SLP_PRN_CNT'],2).chr(13).chr(10);
	
	
    $header.=followSpace($CmpDef['ADDRESS'].", ".$CmpDef['CITY_NM']." ".$CmpDef['ZIP'],110)."Tanggal Order: ".$row['ORD_DT'].chr(13).chr(10);
    $header.=followSpace("Telp. ".$CmpDef['PHONE'].", E-Mail: ".$CmpDef['EMAIL'],104)."Tanggal Surat Jalan: ".$row['TRNSP_DT'].chr(13).chr(10);
$header.=pSpace(116)."No Order: ".leadZero($row['ORD_NBR'],6)."-".leadZero($row['IVC_PRN_CNT'],2).chr(14).chr(10);
	
	$customer=trim($row['NAME_PPL']." ".$row['NAME_COM']);
	if($customer==""){$customer="Tunai";}
	
	$prnHeader=chr(27)."(B".chr(12).chr(0).chr(5).chr(2).chr(-3).chr(11).chr(0).chr(2).leadZero($TrnspNbr,6);
	$prnHeader.=pSpace(42)."Tujuan: ".$customer.chr(10);
	$prnHeader.=pSpace(42)."Judul Pesanan: ".$row['ORD_TTL'].chr(13).chr(10);
	
	$dspHeader.="Tujuan: ".$customer.chr(10);
	$dspHeader.="Judul Pesanan: ".$row['ORD_TTL'].chr(13).chr(10);

	$header.=$dspHeader;
    $header.=str_repeat("-",135).chr(13).chr(10);
    $spacing=55;

    $header.=" Jumlah".pSpace($spacing)."Deskripsi Barang";
	$header.=chr(13).chr(10);
    $header.=str_repeat("-",135).chr(13).chr(10);

	$string=$header;
	$rowCount=0;
	$pageCount=0;
	$query="SELECT TRNSP_DET_NBR,TDT.ORD_DET_NBR,ODT.ORD_NBR,ODT.DET_TTL AS DET_TTL,TDT.DET_TTL AS TRNSP_TTL,PRN_DIG_DESC,TRNSP_Q,ORD_Q,FIL_LOC,PRN_LEN,PRN_WID
                FROM TRNSP_DET TDT LEFT OUTER JOIN
                CMP.PRN_DIG_ORD_DET ODT ON TDT.ORD_DET_NBR=ODT.ORD_DET_NBR LEFT OUTER JOIN
                CMP.PRN_DIG_TYP TYP ON ODT.PRN_DIG_TYP=TYP.PRN_DIG_TYP
                WHERE TRNSP_NBR=".$TrnspNbr." AND TDT.DEL_NBR=0 ORDER BY 1";
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
		
		$string.=leadSpace($rowd['TRNSP_Q'],7+$indent)." ";
		if(($rowd['PRN_LEN']!="")&&($rowd['PRN_WID']!="")){$prnDim=" ".$rowd['PRN_LEN']."x".$rowd['PRN_WID'];}else{$prnDim="";}
		$string.=trim(leadZero($rowd['ORD_DET_NBR'],6)." ".leadZero($rowd['TRNSP_DET_NBR'],6)." ".trim($rowd['DET_TTL']." ".$rowd['PRN_DIG_DESC'].$prnDim." ".$rowd['TRNSP_TTL']));
		$string.=chr(13).chr(10);
	}
	
	if($rowCount!=11){
		$string.=pRow(11-$rowCount);
	}

    $spacing=19;
    $string.=str_repeat("-",135).chr(13).chr(10);
	$string.=chr(13).chr(10);
	$string.=pSpace(18+$spacing)."Penerima".pSpace(40);
    $string.="Pengantar";
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
