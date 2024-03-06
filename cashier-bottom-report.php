<?php
	$POSID=$_GET['POS_ID'];

	if($POSID!=""){
		include "framework/database/connect-cashier.php";
		include "framework/functions/default.php";
		include "framework/functions/dotmatrix.php";
			
		//Generate receipt
		$receipt ="  Yogyakarta 55232 Telp.(0274) 586866".chr(13).chr(10);
		$receipt.="          campus@champs.asia".chr(13).chr(10).chr(13).chr(10);
		$receipt.="             SALES REPORT".chr(13).chr(10);		
		$receipt.="----------------------------------------".chr(13).chr(10);
		//         1234567890123456789012345678901234567890
		//                  1         2         3         4
		
		//Listing
		$query="SELECT CASE WHEN CSH_FLO_TYP IN ('DP','FL') THEN 'Digital Printing' ELSE CAT_DESC END AS CAT_DESC,CAT_SUB_DESC,SUM(RTL_Q) AS RTL_Q,SUM(TND_AMT) AS TND_AMT
			FROM CSH.CSH_REG
			WHERE DATE(CRT_TS)='2014-02-11' AND RTL_BRC<>''
			GROUP BY CASE WHEN CSH_FLO_TYP IN ('DP','FL') THEN 'Digital Printing' ELSE CAT_DESC END,CAT_SUB_DESC
			ORDER BY 1,2";
		$result=mysql_query($query);
		//echo $query;
		while($row=mysql_fetch_array($result)){
		/*	$receipt.=followSpace(trim($row['CAT_DESC']." ".$row['CAT_SUB_DESC']),19);
			$receipt.=leadSpace($row['RTL_Q'],5);
			$receipt.=" Rp. ";
			$receipt.=leadSpace($row['TND_AMT'],11).chr(13).chr(10); */
			$TotNet+=$row['TND_AMT'];
			$TotItem+=$row['RTL_Q'];
		}
		
		$receipt.=followSpace("DESCRIPTION",24)." VALUE ".chr(13).chr(10);
		$receipt.="----------------------------------------".chr(13).chr(10);
		$receipt.=followSpace("TOTAL (".number_format($TotItem,0,",",".")." item)",24)." Rp. ".leadSpace($TotNet,11).chr(13).chr(10);
		
		$query="SELECT SUM(TND_AMT) AS TND_AMT
			FROM CSH.CSH_REG
			WHERE DATE(CRT_TS)='2014-02-11' AND CSH_FLO_PART='B' AND CSH_FLO_TYP='DS'
			GROUP BY CAT_DESC,CAT_SUB_DESC
			ORDER BY CAT_DESC,CAT_SUB_DESC";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$discAmt=$row['TND_AMT'];
		if($TotNet>0){
			$discPct=$discAmt/$TotNet*100;
		}else{
			$discPct=0;
		}
		$receipt.=followSpace("DISKON (".number_format($discPct,0,",",".")."%)",24)." Rp. ".leadSpace($discAmt,11).chr(13).chr(10);

		$query="SELECT SUM(TND_AMT) AS TND_AMT
			FROM CSH.CSH_REG
			WHERE DATE(CRT_TS)='2014-02-11' AND CSH_FLO_PART='B' AND CSH_FLO_TYP='SU'
			GROUP BY CAT_DESC,CAT_SUB_DESC
			ORDER BY CAT_DESC,CAT_SUB_DESC";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$surAmt=$row['TND_AMT'];
		if($TotNet>0){
			$surPct=$surAmt/($TotNet-$discAmt)*100;
		}else{
			$surPct=0;
		}
		$receipt.=followSpace("CARD FEE (".number_format($surPct,0,",",".")."%)",24)." Rp. ".leadSpace($surAmt,11).chr(13).chr(10);

		$receipt.="----------------------------------------".chr(13).chr(10);
		$receipt.=followSpace("NETT",24)." Rp. ".leadSpace($TotNet-$discAmt,11).chr(13).chr(10).chr(13).chr(10);
		
		//Tambahan Pembayaran Start
		
		
			//Credit Start
				$query="SELECT  SUM(TND_AMT) AS TND_AMT
			FROM CSH.CSH_REG
			WHERE DATE(CRT_TS)='2014-02-11'  AND PYMT_TYP='CRT'";
		$result=mysql_query($query);
		//echo $query;
		while($row=mysql_fetch_array($result)){
			
			$TotNetCrt+=$row['TND_AMT'];
			
		}
		
			//Credit End
			//Debit Start
				$query="SELECT  SUM(TND_AMT) AS TND_AMT
			FROM CSH.CSH_REG
			WHERE DATE(CRT_TS)='2014-02-11'  AND PYMT_TYP='DEB'";
		$result=mysql_query($query);
		//echo $query;
		while($row=mysql_fetch_array($result)){
			
			$TotNetDeb+=$row['TND_AMT'];
			
		}
		
			//Debit End
			//Modal Awal Start
				$query="SELECT TND_AMT
			FROM CSH.CSH_REG
			WHERE DATE(CRT_TS)='2014-02-11'  AND PYMT_TYP='CSH' AND CSH_FLO_TYP='RA'";
		$result=mysql_query($query);
		//echo $query;
		while($row=mysql_fetch_array($result)){
			
			$Modal+=$row['TND_AMT'];
			
		}
		
			//Modal Awal End
			//Uang di laci Start
				$query="SELECT TND_AMT
			FROM CSH.CSH_REG
			WHERE DATE(CRT_TS)='2014-02-11'  AND PYMT_TYP='CSH' AND CSH_FLO_TYP='DE'";
		$result=mysql_query($query);
		//echo $query;
		while($row=mysql_fetch_array($result)){
			
			$UangLaci+=$row['TND_AMT'];
			
		}
		
			//Uang di laci End
			
		$receipt.="Rincian Pembayaran                    ".chr(13).chr(10);
		$receipt.="----------------------------------------".chr(13).chr(10);
		$receipt.=followSpace("DESCRIPTION",24)." VALUE ".chr(13).chr(10);
		$receipt.="----------------------------------------".chr(13).chr(10);
		$receipt.=followSpace("Modal Awal",24)." Rp. ".leadSpace(($Modal),11).chr(13).chr(10);
		$receipt.=followSpace("TUNAI ",24)." Rp. ".leadSpace($TotNet-$TotNetCrt-$TotNetDeb-$discAmt,11).chr(13).chr(10);
		$receipt.=followSpace("Credit ",24)." Rp. ".leadSpace($TotNetCrt,11).chr(13).chr(10);
		$receipt.=followSpace("Debit ",24)." Rp. ".leadSpace($TotNetDeb,11).chr(13).chr(10);
		$receipt.="----------------------------------------".chr(13).chr(10);
		$receipt.=followSpace("Bruto Komputer",24)." Rp. ".leadSpace(($TotNet-$discAmt+$Modal),11).chr(13).chr(10).chr(13).chr(10);
		
		$receipt.="Tunai+Modal                    ".chr(13).chr(10);
		$receipt.="----------------------------------------".chr(13).chr(10);
		$receipt.=followSpace("DESCRIPTION",24)." VALUE ".chr(13).chr(10);
		$receipt.="----------------------------------------".chr(13).chr(10);
		$receipt.=followSpace("Drawer",24)." Rp. ".leadSpace($UangLaci,11).chr(13).chr(10);
		$receipt.=followSpace("Komputer",24)." Rp. ".leadSpace($TotNet-$TotNetCrt-$TotNetDeb-$discAmt+$Modal,11).chr(13).chr(10);
		$receipt.="----------------------------------------".chr(13).chr(10);
		$receipt.=followSpace("Selisih",24)." Rp. ".leadSpace(($UangLaci-($TotNet-$TotNetCrt-$TotNetDeb-$discAmt+$Modal)),11).chr(13).chr(10);
		
		//Tambahan End
		
		$receipt.=chr(13).chr(10);
		$receipt.="          ".leadZero($POSID,2)." ".date("d-m-Y")." ". date("H:m:s").chr(13).chr(10);
		$receipt.=chr(13).chr(10);
		$receipt.=chr(13).chr(10);
		$receipt.=chr(13).chr(10);
		$receipt.="             The Kopi Tugu".chr(13).chr(10);
		$receipt.="        Lt.1 The Jayan Building   ".chr(13).chr(10);
		$receipt.="        Jl. Pangeran Mangkubumi 38".chr(13).chr(10);
		//         123456789012345678901234567890123456789012


		//echo "<PRE>$receipt</PRE>";

		$fh=fopen("cash-register/TES/".$POSID .".txt","w");
		fwrite($fh,$receipt.chr(27).chr(112).chr(48).chr(25).chr(250));
		fclose($fh);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	
</head>

<body style='padding:0px;margin:0px'>

<table style="width:100%;height:24px;font-family:'HelveticaNeue','Helvetica Neue',Helvetica,Arial, sans-serif;"><tr><td style='border:0px'>
	<table>
		<tr>
			<td style='font-size:11pt;border:0px' colspan=6>
				<b>Petunjuk Perintah Operasi</b>
			</td>
		</tr>
		<tr>
			<td style='width:241px;font-size:8pt;color:#999999;border:0px;vertical-align:top'>
				<b>Digit Pertama</b><br/>
				= Permulaan operasi<br/>
				- Hapus item sesuai barcode<br/>				
			</td>
			<td style='width:241px;font-size:8pt;color:#999999;border:0px;vertical-align:top''>
				<b>Digit Kedua</b><br/>
				X Ganti jumlah item terakhir<br/>
				D Diskon jumlah<br/>				
				P Diskon persen<br/>
				Z Hapus semua diskon
			</td>
			<td style='width:300px;font-size:8pt;color:#999999;border:0px;vertical-align:top''>
				<b>Digit Kedua</b><br/>
				T Pembayaran tunai<br/>
				C Pembayaran dengan credit card<br/>				
				B Pembayaran dengan debit card<br/>
				K Pembayaran dengan cek<br/>
				F Pembayaran melalui transfer bank<br/>
			</td>
			<td style='width:243px;font-size:8pt;color:#999999;border:0px;vertical-align:top''>
				<b>Digit Kedua</b><br/>
				N Hapus semua pembayaran<br/>
				R Pengembalian barang<br/>
				U Keluar Uang Tunai<br/>
				M Member diskon<br/>
			</td>
			<td style='width:243px;font-size:8pt;color:#999999;border:0px;vertical-align:top''>
				<b>Digit Kedua</b><br/>
				O Pembayaran Printing<br/>
				V Batal Pembayaran Printing<br/>
				S Tambah card fee<br/>
				L Hapus card fee<br/>
			</td>
			<td style='width:243px;font-size:8pt;color:#999999;border:0px;vertical-align:top''>
				<b>Digit Kedua</b><br/>
				J Tambah PPN<br/>
				A Hapus PPN<br/>
				E Pembayaran Sales<br/>
				G Hapus Pembayaran Sales<br/>
			</td>
		</tr>
	</table>
</td>

<td style='width:400px;border:0px;text-align:right;vertical-align:top'>
	<div id='bottom-control'>
	</div>
</td></tr></table>

</body>
</html>