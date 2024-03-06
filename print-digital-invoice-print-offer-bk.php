<?php
include "framework/security/default.php";

$UpperSec		= getSecurity($_SESSION['userID'],"Accounting");
$salestype 		= $_GET['TYP'];
$Type 			= $_GET['TYPE'];
$formattedOrderNumber = leadZero($orderNumber, 7);
$paper = array(0, 0, 21 * (72/2.54), 29.7 * (72/2.54));

$NameOfr		= $_GET['NAME_OFR'];
$NameCom		= $_GET['NAME_COM'];
$TitleTop		= $_GET['TITLE_TOP'];
$LetterHead		= $_GET['LETTER_HEAD'];
$TitleBottom	= $_GET['TITLE_BOTTOM'];

if($salestype == "EST"){
	$headtable 	= "CMP.PRN_DIG_ORD_HEAD_EST";
	$detailtable= "CMP.PRN_DIG_ORD_DET_EST";
}else{
	$headtable 	= "CMP.PRN_DIG_ORD_HEAD";
	$detailtable= "CMP.PRN_DIG_ORD_DET";
}

$query="SELECT NAME FROM PEOPLE PPL INNER JOIN POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='".$_SESSION['userID']."'";
$result=mysql_query($query);
$row=mysql_fetch_array($result);
$name=$row['NAME'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>Print Digital Invoice- <?php echo $orderNumber; ?></title>
<link rel="stylesheet" href="http://necolas.github.io/normalize.css/latest/normalize.css">
<style type="text/css">
@page {
	margin-top: 0px;
}
body {
	font-family: Calibri, sans-serif;
	margin: 175px .0cm;
	text-align: left;
	font-size:12px;
	line-height: 18px;
	margin-bottom: 60px;
	padding-top: 250px;
	font-weight: normal;
}

table {
	border-collapse: collapse;
	border: none;
}

#header {
	font-family: Calibri, sans-serif;
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
	bottom: 60px; /*bottom: 20px;*/
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
			<td width="32%"><img src="<?php echo $img; ?>" height="410px" style="padding-top:-3px;"/></td>
		</tr>
	</table>
</header>

<footer id="footer" >
	<table width="100%">
		<tr>
			<td width="100%"><img src="img/footer.png" width="250px" height="25px" style="padding-left: 250px;padding-top:50px"/></td>
		</tr>
	</table>
</footer>

<section id="body">
	<table width="65%" align="right" cellpadding="3" style="line-height: 23px;">
		<tbody>
			<tr>
				<td style="padding-top:-228px;" colspan="6">
					<div style="line-height: 13px;">
						Yogyakarta, <?php echo date("d F Y", strtotime($OrdDate)); ?>
					</div><br></br>
					<div style="line-height: 13px;padding-top:5px;">
						Kepada Yth. <br><b>Bapak/Ibu <?php echo $NameOfr; ?> <br/> <?php echo $NameCom; ?></b>
					</div><br></br>
					<div style="line-height: 13px;">
						Dengan Hormat, <br><?php echo nl2br($HeadLttr); ?></br><br></br><br></br>
					</div>
				</td>
				</tr>
			<tr>
				<td colspan="6">
				<div style="line-height: 13px;">
						<b><?php echo nl2br($TitleTop); ?></b>
					</div>
				</td>
			</tr>
			<tr>
				<th class="text-center" width="40%" colspan=2 style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Deskripsi</th>
				<th class="text-center" width="5%" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Jumlah</th>
				<?php if($UpperSec != 8){ ?>
				<th class="text-center" width="15%" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Ukuran</th>
				<th class="text-center" width="20%" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Harga/pc</th>
				<th class="text-center" width="20%" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Sub Total</th>
				<?php } ?>
			</tr>
			<?php
				$query="SELECT 
					ORD_DET_NBR,
					DET.ORD_NBR,
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
					TOT_SUB,
					ROLL_F,
					HND_OFF_TYP,
					HND_OFF_TS,
					SORT_BAY_ID,
					DET.PRN_DIG_TYP
				FROM ". $detailtable ." DET 
					LEFT OUTER JOIN $CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
					LEFT OUTER JOIN $CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
				WHERE ORD_NBR=".$orderNumber." AND ORD_DET_NBR_PAR IS NULL AND DET.DEL_NBR=0 
				ORDER BY 1";
				$result = mysql_query( $query );
				$i=0;
				while($row = mysql_fetch_array($result)){
				
				$OrdQ		= '';
				$PrnDigPrc	= 0;
				$DiscAmt	= 0;
				$TotSub		= 0;
				$rowDetail	= '';
				if($row['PRN_DIG_TYP']=='CUSTOM'){
					$query="SELECT 
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
						(COALESCE(CLD.TOT_SUB,0)+COALESCE(CLD.DISC_AMT,0)-COALESCE(CLD.FEE_MISC,0))/COALESCE(DET.ORD_Q,1)+ COALESCE(DET.FEE_MISC,0) AS PRN_DIG_PRC,
						COALESCE(CLD.FEE_MISC,0)/COALESCE(DET.ORD_Q,1) AS FEE_MISC,COALESCE(CLD.DISC_AMT,0)/COALESCE(DET.ORD_Q,1) AS DISC_AMT,
						CLD.VAL_ADD_AMT,
						CLD.TOT_SUB + (COALESCE(DET.FEE_MISC,0)*COALESCE(DET.ORD_Q,1)) AS TOT_SUB,
						COALESCE(DET.FEE_MISC,0) AS DET_FEE_MISC 
					FROM ". $detailtable ." DET 
						INNER JOIN(
							SELECT ORD_DET_NBR_PAR,
								SUM(ORD_Q) AS ORD_Q,
								SUM(FEE_MISC*ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS FEE_MISC,
								SUM(FAIL_CNT) AS FAIL_CNT,SUM(DISC_AMT*ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS DISC_AMT,
								SUM(VAL_ADD_AMT) AS VAL_ADD_AMT,
								SUM(TOT_SUB) AS TOT_SUB
							FROM ". $detailtable ." DET
							WHERE ORD_DET_NBR_PAR=".$row['ORD_DET_NBR']." AND DET.DEL_NBR=0 GROUP BY 1 ORDER BY 1
						)CLD ON DET.ORD_DET_NBR=CLD.ORD_DET_NBR_PAR
						LEFT OUTER JOIN $CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
					WHERE ORD_DET_NBR=".$row['ORD_DET_NBR']." AND DET.DEL_NBR=0 AND DET.PRN_DIG_TYP='PROD' 
					GROUP BY 1,2 
					ORDER BY 1";
				}else{
					$query="SELECT 
						ORD_DET_NBR,
						DET.ORD_NBR,
						ORD_DET_NBR_PAR,
						DET_TTL,
						PRN_DIG_DESC,
						DET.PRN_DIG_PRC,
						DET.PRN_DIG_TYP,
						ORD_Q,FIL_LOC,
						PRN_LEN,
						PRN_WID,
						FEE_MISC,
						FAIL_CNT,
						DISC_PCT,
						DISC_AMT,
						VAL_ADD_AMT,
						TOT_SUB
					FROM ". $detailtable ." DET 
						LEFT OUTER JOIN $CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
					WHERE ORD_DET_NBR=".$row['ORD_DET_NBR']." OR ORD_DET_NBR_PAR=".$row['ORD_DET_NBR']." AND DET.DEL_NBR=0 
					ORDER BY 1";
				}
				$resultc=mysql_query($query);
				while($rowc=mysql_fetch_array($resultc)){
					$PrnLen	= $row['PRN_LEN'];
					$PrnWid = $row['PRN_WID'];
					$SubTotal = $rowc['TOT_SUB'];
					$QTotal += $rowc['ORD_Q'];
					$PriceTotal += $SubTotal;
					if(($rowc['PRN_LEN']!="")&&($rowc['PRN_WID']!="")){$prnDim=" ".$rowc['PRN_LEN']."x".$rowc['PRN_WID'];}else{$prnDim="";}
					$price=$rowc['PRN_DIG_PRC']+$rowc['VAL_ADD_AMT']+$rowc['FEE_MISC'];
					$tot_price = $price - $rowc['DISC_AMT'];
					if ($i%2 == 0) {$color="#f2f2f2";}else{$color="#FFFFFF";}
							
			?>
			<tr bgcolor="<?php echo $color; ?>">
				<?php if(($rowc['ORD_DET_NBR_PAR']!="")&&($rowc['PRN_DIG_TYP']!='PROD')){?>
					<td width="1px" style="border-bottom: 1px solid #b3b3b3;">&nbsp;</td>
				<?php } ?>
					<td width="40%" style="border-bottom: 1px solid #b3b3b3;" class="text-left" <?php if(($rowc['ORD_DET_NBR_PAR']=="")||($rowc['PRN_DIG_TYP']=='PROD')){ echo 'colspan=2'; } ?>><?php echo trim($rowc['DET_TTL']." ".$rowc['PRN_DIG_DESC']); ?></td>
					<td width="5%"  style="border-bottom: 1px solid #b3b3b3;" class="text-center"><?php echo number_format($rowc['ORD_Q'],0,",",",")?></td>
					<?php if($UpperSec != 8){ ?>
					<td width="15%" style="border-bottom: 1px solid #b3b3b3;" class="text-center"><?php echo $prnDim?></td>
					<td width="20%" style="border-bottom: 1px solid #b3b3b3;" class="text-center"><?php echo number_format($price,0,",",",")?></td>
					<td width="20%" style="border-bottom: 1px solid #b3b3b3;" class="text-center"><?php echo number_format($rowc['TOT_SUB'],0,",",",")?></td>
					<?php } ?>
			</tr>
			<?php $i++; }} ?>
			<tr>
				<?php if($UpperSec != 8){ ?>
				<td colspan="4" class="text-right">&nbsp;</td>
				<?php } ?>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b>Total</b></td>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b><?php echo number_format($PriceTotal,0,",",".");?></b></td>
			</tr>
			<tr>			
				<td colspan="6">
					<div style="line-height: 11px;font-size:9px">
						<?php echo nl2br($BodyLttr); ?> 
						<br></br><br></br>
					</div>
				</td>
			</tr>
		</tbody>

		<tbody>
			<tr>
				<td colspan="6">
				<div style="line-height: 13px;">
						<b><?php echo nl2br($TitleBottom); ?></b>
					</div>
				</td>
			</tr>
			<tr>
				<th class="text-center" width="40%" colspan=2 style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Deskripsi</th>
				<th class="text-center" width="5%" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Jumlah</th>
				<?php if($UpperSec != 8){ ?>
				<th class="text-center" width="15%" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Ukuran</th>
				<th class="text-center" width="20%" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Harga/pc</th>
				<th class="text-center" width="20%" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Sub Total</th>
				<?php } ?>
			</tr>
			<?php
				$query="SELECT 
					ORD_DET_NBR,
					DET.ORD_NBR,DET_TTL,
					PRN_DIG_DESC,
					DET.PRN_DIG_PRC,ORD_Q,
					FIL_LOC,
					PRN_LEN,
					PRN_WID,
					FEE_MISC,
					FAIL_CNT,
					DISC_PCT,
					DISC_AMT,
					VAL_ADD_AMT,
					TOT_SUB,
					ROLL_F,
					HND_OFF_TYP,
					HND_OFF_TS,
					SORT_BAY_ID,
					DET.PRN_DIG_TYP
				FROM ". $detailtable ." DET 
					LEFT OUTER JOIN $CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
					LEFT OUTER JOIN $CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
				WHERE ORD_NBR=".$orderNumber." AND ORD_DET_NBR_PAR IS NULL AND DET.DEL_NBR=0 
				ORDER BY 1";
				$result = mysql_query($query );
				$i=0;
				while($row = mysql_fetch_array($result)){
					
				$OrdQ		= '';
				$PrnDigPrc	= 0;
				$DiscAmt	= 0;
				$TotSub		= 0;
				$rowDetail	= '';
				if($row['PRN_DIG_TYP']!='PROD' && $row['PRN_DIG_TYP']!='CUSTOM'){
					$query="SELECT 
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
						(COALESCE(CLD.TOT_SUB,0)+COALESCE(CLD.DISC_AMT,0)-COALESCE(CLD.FEE_MISC,0))/COALESCE(DET.ORD_Q,1)+ COALESCE(DET.FEE_MISC,0) AS PRN_DIG_PRC,
						COALESCE(CLD.FEE_MISC,0)/COALESCE(DET.ORD_Q,1) AS FEE_MISC,COALESCE(CLD.DISC_AMT,0)/COALESCE(DET.ORD_Q,1) AS DISC_AMT,
						CLD.VAL_ADD_AMT,
						CLD.TOT_SUB + (COALESCE(DET.FEE_MISC,0)*COALESCE(DET.ORD_Q,1)) AS TOT_SUB,
						COALESCE(DET.FEE_MISC,0) AS DET_FEE_MISC 
					FROM ". $detailtable ." DET 
						INNER JOIN(
							SELECT ORD_DET_NBR_PAR,
								SUM(ORD_Q) AS ORD_Q,
								SUM(FEE_MISC*ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS FEE_MISC,
								SUM(FAIL_CNT) AS FAIL_CNT,SUM(DISC_AMT*ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS DISC_AMT,
								SUM(VAL_ADD_AMT) AS VAL_ADD_AMT,
								SUM(TOT_SUB) AS TOT_SUB
							FROM ". $detailtable ." DET
							WHERE ORD_DET_NBR_PAR=".$row['ORD_DET_NBR']." AND DET.DEL_NBR=0 GROUP BY 1 ORDER BY 1
						)CLD ON DET.ORD_DET_NBR=CLD.ORD_DET_NBR_PAR
						LEFT OUTER JOIN $CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
					WHERE ORD_DET_NBR=".$row['ORD_DET_NBR']." AND DET.DEL_NBR=0 AND DET.PRN_DIG_TYP='PROD' 
					GROUP BY 1,2 
					ORDER BY 1";
				}else{
					$query="SELECT 
						ORD_DET_NBR,
						DET.ORD_NBR,
						ORD_DET_NBR_PAR,
						DET_TTL,
						PRN_DIG_DESC,
						DET.PRN_DIG_PRC,
						DET.PRN_DIG_TYP,
						ORD_Q,FIL_LOC,
						PRN_LEN,
						PRN_WID,
						FEE_MISC,
						FAIL_CNT,
						DISC_PCT,
						DISC_AMT,
						VAL_ADD_AMT,
						TOT_SUB
					FROM ". $detailtable ." DET 
						LEFT OUTER JOIN $CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
					WHERE ORD_DET_NBR=".$row['ORD_DET_NBR']." OR ORD_DET_NBR_PAR=".$row['ORD_DET_NBR']." AND DET.DEL_NBR=0 
					ORDER BY 1";
				}
				$resultd=mysql_query($query);
				while($rowd=mysql_fetch_array($resultd)){
					$PrnLen	= $row['PRN_LEN'];
					$PrnWid = $row['PRN_WID'];
					$SubTotal = $rowd['TOT_SUB'];
					$QTotal += $rowd['ORD_Q'];
					$PriceTot += $SubTotal;
					if(($rowd['PRN_LEN']!="")&&($rowd['PRN_WID']!="")){$prnDim=" ".$rowd['PRN_LEN']."x".$rowd['PRN_WID'];}else{$prnDim="";}
					$price=$rowd['PRN_DIG_PRC']+$rowd['VAL_ADD_AMT']+$rowd['FEE_MISC'];
					$tot_price = $price - $rowd['DISC_AMT'];
					if ($i%2 == 0) {$color="#f2f2f2";}else{$color="#FFFFFF";}
							
			?>
			<tr bgcolor="<?php echo $color; ?>">
				<?php if(($rowd['ORD_DET_NBR_PAR']!="")&&($rowd['PRN_DIG_TYP']!='PROD')){?>
					<td width="1px" style="border-bottom: 1px solid #b3b3b3;">&nbsp;</td>
				<?php } ?>
					<td width="40%" style="border-bottom: 1px solid #b3b3b3;" class="text-left" <?php if(($rowd['ORD_DET_NBR_PAR']=="")||($rowd['PRN_DIG_TYP']=='PROD')){ echo 'colspan=2'; } ?>><?php echo trim($rowd['DET_TTL']." ".$rowd['PRN_DIG_DESC']); ?></td>
					<td width="5%"  style="border-bottom: 1px solid #b3b3b3;" class="text-center"><?php echo number_format($rowd['ORD_Q'],0,",",",")?></td>
					<?php if($UpperSec != 8){ ?>
					<td width="15%" style="border-bottom: 1px solid #b3b3b3;" class="text-center"><?php echo $prnDim?></td>
					<td width="20%" style="border-bottom: 1px solid #b3b3b3;" class="text-center"><?php echo number_format($price,0,",",",")?></td>
					<td width="20%" style="border-bottom: 1px solid #b3b3b3;" class="text-center"><?php echo number_format($rowd['TOT_SUB'],0,",",",")?></td>
					<?php } ?>
			</tr>
			<?php $i++; }} ?>
			<tr>
				<?php if($UpperSec != 8){ ?>
				<td colspan="4" class="text-right">&nbsp;</td>
				<?php } ?>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b>Total</b></td>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b><?php echo number_format($PriceTot,0,",",".");?></b></td>
			</tr>
			<tr>			
				<td colspan="6">
					<div style="line-height: 11px;font-size:9px">
					<?php echo nl2br($FootLttr); ?> 
					<br></br><br></br><br></br>
				</td>
			</tr>
			<tr>
				<td colspan="6">
					<div style="line-height: 13px;font-size:12px">
						Demikian surat penawaran dari perusahaan kami, dan kami berharap dapat bekerja sama dengan perusahaan yang Bapak/Ibu pimpin. Atas perhatiannya kami mengucapkan terima kasih.
					<br></br><br></br><br></br>
				</td>
			</tr>
			<tr>
				<td colspan="6">
					<div style="line-height: 13px;font-size:12px">
					<br>Hormat kami,</br>
					<br></br><br></br><br></br><br></br><br></br><br></br>
				</td>
			</tr>
			<tr>
				<td colspan="6">
					<div style="line-height: 13px;">
						<br><b><?php echo $CrtName; ?></b><br/><b>Regional Account Marketing</b></br>
					</div>
				</td>
			</tr>
			</tr>
		</tbody>
		</table>
</section>
</body>
</html>

