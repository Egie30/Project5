<?php
$paper = array(0, 0, 21 * (72/2.54), 29.7 * (72/2.54));

$TrnspNbr	= $_GET['TRNSP_NBR'];
$query="SELECT NAME FROM PEOPLE PPL INNER JOIN POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='".$_SESSION['userID']."'";
$result=mysql_query($query);
$row=mysql_fetch_array($result);
$name=$row['NAME'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>Invoice - Champion</title>
<style type="text/css">
@page {
	margin-top: 0px;
}
body {
	font-family: Calibri, sans-serif;
	margin: 180px .0cm;
	text-align: left;
	font-size:12px;
	line-height: 20px;
	margin-bottom: 30px;
	padding-top: 0px;
	font-weight: normal;
}

table {
	border-collapse: collapse;
	border: none;
}

#header {
	font-family: arial, sans-serif;
	line-height: 16px;
	padding-top: 20px;
	padding-bottom: 10px;
}

#header h1 {
	margin: 0;
	font-size:28px;
}

#body table{
	margin-bottom: 15px;
}

#body table:last-child{
	margin-bottom: 0;
}

#header, #footer {
	position: fixed;
	left: 0;
	right: 0;
}

#header {
	top: 0;
}

#footer {
	font-size:10px;
	bottom: 100px;
}

.border-gold {
	border-color: rgb(92,51,23);
}

.border-black {
	border-color: #000000;
}

.text-center {
	text-align:center;
}

.text-left {
	text-align:left;
}

.text-right {
	text-align:right;
}
</style>
</head>
<body>
<header id="header">
	<table width="100%">
		<tr>
			<td colspan="2" align="center"><b>
			<?php if($row['TRNSP_STT_ID']=="ST"){ ?>
			SURAT JALAN
			<?php } else if($row['TRNSP_STT_ID']=="RP"){?>
			TANDA TERIMA
			<?php } else {?>
			SURAT JALAN
			<?php } ?>
			</b></td>
		</tr>
		<tr>
			<td width="70%">
				<b><?php echo $shipperName; ?></b><br/>
				<?php echo $shipperAddress.", ".$shipperCity.", ".$shipperZip; ?><br/>
				Telp. <?php echo $shipperPhone; ?><br/>
				Email : <?php echo $shipperEmail; ?>
			</td>
			<td align="right">
				<div>
					Nota No. <?php echo leadZero($TrnspNbr,6)."-".$printTransCount; ?><br/>
					Tanggal Order: <?php echo $orderDate; ?><br/>
					<?php  if($transportStatus=="ST"){ ?>
					Tanggal Ambil: <?php echo $transportDate; ?><br/>
					<?php } else if($transportStatus=="RP"){ ?>
					Tanggal Terima: <?php echo $transportDate; ?><br/>
					<?php }else{ ?>
					Tanggal Surat Jalan: <?php echo $transportDate; ?><br/>
					<?php } ?>
					No Order: <?php echo leadZero($orderNumber,6)."-".$printorderCount; ?><br/>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2">
				<?php
				if(($transportStatus=="ST")&&($transportDesc!="")){
					$customer=$transportDesc;
				} else if(($transportStatus=="RP")){
					if($transportDesc==""){
						$customer="- | (".$RcvCompany.")";
					} else {
						$customer=$transportDesc." | (".$RcvCompany.")";
					}
				} else {
					$customer=trim($RcvPeople." ".$RcvCompany);
					if($customer==""){$customer="Tunai";}
				}
				?>
				Tujuan: <b><?php echo $customer; ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				Order Title : <b><?php echo $orderTitle; ?></b>
			</td>
		</tr>
	</table>
</header>
<footer id="footer">
	<table width="100%" style="font-size:12px">
		<tr>
			<td align="center" width="50%">
				Penerima<br><br><br>(______________________)
			</td>
			<td align="center">
				Pengantar<br><br><br>(______________________)
			</td>
		</tr>
		
		<tr>
			<td colspan="2" style="font-size:10px;" align="center">
				Barang harap diperiksa dengan baik. Pengajuan klaim sesudah staff meninggalkan tempat tidak dilayani dan menjadi tanggung jawab pembeli.
			</td>
		</tr>
	</table>
</footer>

<section id="body">
	<table width="100%">
			<tr style="border-bottom: 4px #000000;">	
				<th class="text-center" width="10%" style="border-bottom: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Jumlah</th>
				<th class="text-center" width="90d%" style="border-bottom: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Deskripsi</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if($row['TRNSP_STT_ID']=="RP"){
				$query 	= "SELECT 
					TDET.TRNSP_DET_NBR,
					TDET.TRNSP_Q,
					CONCAT(COALESCE(INV.NAME,''),' ',DET.INV_DESC) AS INV_NAME,
					TDET.ORD_DET_NBR,
					TDET.DET_TTL
				FROM CMP.TRNSP_DET TDET 
					LEFT JOIN RTL.RTL_STK_DET DET ON TDET.ORD_DET_NBR=DET.ORD_DET_NBR 
					LEFT JOIN RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR 
					WHERE TDET.DEL_NBR=0 AND TDET.TRNSP_NBR=".$TrnspNbr." 
					ORDER BY TDET.TRNSP_DET_NBR ASC";
			} else {
				$query="SELECT 
					TRNSP_DET_NBR,
					TDT.ORD_DET_NBR,
					ODT.ORD_NBR,
					ODT.DET_TTL AS DET_TTL,
					TDT.DET_TTL AS TRNSP_TTL,
					PRN_DIG_DESC,
					TRNSP_Q,
					ORD_Q,
					FIL_LOC,
					PRN_LEN,
					PRN_WID
				FROM TRNSP_DET TDT 
					LEFT OUTER JOIN CMP.PRN_DIG_ORD_DET ODT ON TDT.ORD_DET_NBR=ODT.ORD_DET_NBR
					LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON ODT.PRN_DIG_TYP=TYP.PRN_DIG_TYP
				WHERE TRNSP_NBR=".$TrnspNbr." AND TDT.DEL_NBR=0 ORDER BY 1";
			}
			$result=mysql_query($query);
			while($rowd=mysql_fetch_array($result)){
			if ($i%2 == 0) {$color="#f2f2f2";}else{$color="#FFFFFF";}
			?>
			<tr bgcolor="<?php echo $color; ?>">
				<td style="padding-right:15px;" class="text-right"><?php echo $rowd['TRNSP_Q'];?></td>
				<td>
					<?php
						echo trim(leadZero($rowd['ORD_DET_NBR'],6)." ".leadZero($rowd['TRNSP_DET_NBR'],6)." ".trim($rowd['DET_TTL']." ".$rowd['PRN_DIG_DESC'].$prnDim." ".$rowd['TRNSP_TTL']));
					?>
				</td>					
			</tr>
			<?php $i++; } ?>
		</tbody>
	</table>
</section>
</body>
</html>
