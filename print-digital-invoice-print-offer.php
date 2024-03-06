<?php

$com = "SELECT CO_NBR,
				NAME, 
				ADDRESS, 
				CIT.CITY_NM, 
				ZIP,
				PHONE,
				EMAIL
		FROM COMPANY COM
		JOIN CITY CIT ON COM.CITY_ID=CIT.CITY_ID
		WHERE CO_NBR ";

if (isset($_GET['LETTER_HEAD']) && $_GET['LETTER_HEAD'] == 'true') {
	$conbr = 2776;
} elseif (isset($_GET['LETTER_HEAD_C']) && $_GET['LETTER_HEAD_C'] == 'true') {
	$conbr = 1002;
} elseif (isset($_GET['LETTER_HEAD_P']) && $_GET['LETTER_HEAD_P'] == 'true') {
	$conbr = 271;
} elseif (isset($_GET['LETTER_HEAD_SE']) && $_GET['LETTER_HEAD_SE'] == 'true') {
	$conbr = 5451;
} elseif (isset($_GET['LETTER_HEAD_SU']) && $_GET['LETTER_HEAD_SU'] == 'true') {
	$conbr = 6188;
}

$com .= "= '" . $conbr . "'";

$result = mysql_query($com);
$rowCom 	= mysql_fetch_array($result);

$nameCompany = $rowCom['NAME'];
$zip	= $rowCom['ZIP'];
$city	= $rowCom['CITY_NM'];
$address = $rowCom['ADDRESS'];
$telephone = $rowCom['PHONE'];
$email = $rowCom['EMAIL'];

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
	margin-bottom: 30px;
	padding-top: 30px;
	font-weight: normal;
}

table {
	border-collapse: collapse;
	border: none;
}

#header {
	width:230px;
	font-family: Calibri, sans-serif;
	padding-bottom: 10px;
}

#header h1 {
	margin: 0;
	font-size:28px;
}

#body table{
	margin-bottom: 10px;
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
	bottom: 20px; /*bottom: 20px;*/
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
.orange {
    color: orange;
}

.blue {
    color: blue;
}

.red {
    color: red;
}


</style>
</head>
<body>
<header id="header">
    <table width="100%" border="">
        <tr>
            <td width="32%"><img src="<?php echo $img; ?>" height="200px" style="padding-top:-3px;"/></td>
        </tr>
		<tr>
            <td align="right" <?php if ($conbr == 1002) echo 'class="orange"'; else if ($conbr == 271) echo 'class="blue"'; else echo 'class="red"'; ?>><b>Perusahaan</b></td>
            <tr>
                <td style="line-height: 13px;" align="right"><?php echo $nameCompany; ?></td>
            </tr>
        </tr>       
        <tr>
            <td align="right" <?php if ($conbr == 1002) echo 'class="orange"'; else if ($conbr == 271) echo 'class="blue"'; else echo 'class="red"'; ?>><b>Address</b></td>
            <tr>
                <td style="line-height: 13px;" align="right"><?php echo $address." ".$city." ".$zip; ?></td>
            </tr>
        </tr>
        <tr>
            <td align="right" <?php if ($conbr == 1002) echo 'class="orange"'; else if ($conbr == 271) echo 'class="blue"'; else echo 'class="red"'; ?>><b>Telephone</b></td>
            <tr>
                <td align="right"><?php echo $telephone; ?></td>
            </tr>
        </tr>
        <tr>
            <td align="right" <?php if ($conbr == 1002) echo 'class="orange"'; else if ($conbr == 271) echo 'class="blue"'; else echo 'class="red"'; ?>><b>Email</b></td>
            <tr>
                <td align="right"><?php echo $email; ?></td>
            </tr>
        </tr>
    </table>
</header>



<footer id="footer" >
	<table width="100%">
		<tr>
			<td width="100%"><img src="img/footer.jpg" height="25px" style="padding-left: 250px;"/></td>
		</tr>
	</table>
</footer>

<section id="body">
	<table width="65%" align="right" cellpadding="3" style="line-height: 23px;" border="1">
		<tbody>
			<tr>
				<td colspan="6">
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
				LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
				LEFT OUTER JOIN CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
			WHERE ORD_NBR=".$orderNumber." AND ORD_DET_NBR_PAR IS NULL AND DET.DEL_NBR=0 AND DET.PRN_DIG_TYP != 'CUSTOM' 
			ORDER BY 1";
			$result = mysql_query( $query );
			$i=0;
			while($row = mysql_fetch_array($result)){
			$OrdQ		= '';
			$PrnDigPrc	= 0;
			$DiscAmt	= 0;
			$TotSub		= 0;
			$rowDetail	= '';
			if($row['PRN_DIG_TYP']=='PROD'){
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
					INNER JOIN
					(
						SELECT ORD_DET_NBR_PAR,
							SUM(ORD_Q) AS ORD_Q,
							SUM(FEE_MISC*ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS FEE_MISC,
							SUM(FAIL_CNT) AS FAIL_CNT,SUM(DISC_AMT*ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS DISC_AMT,
							SUM(VAL_ADD_AMT) AS VAL_ADD_AMT,
							SUM(TOT_SUB) AS TOT_SUB
						FROM ". $detailtable ." DET
						WHERE ORD_DET_NBR_PAR=".$row['ORD_DET_NBR']." AND DET.DEL_NBR=0 GROUP BY 1 ORDER BY 1
					)CLD ON DET.ORD_DET_NBR=CLD.ORD_DET_NBR_PAR
					LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
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
					LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
				WHERE ORD_DET_NBR=".$row['ORD_DET_NBR']." OR ORD_DET_NBR_PAR=".$row['ORD_DET_NBR']." AND DET.DEL_NBR=0 
				ORDER BY 1";
			}
			$resultc=mysql_query($query);
			while($rowc=mysql_fetch_array($resultc)){
				$SubTotal 	= $rowc['TOT_SUB'];
				$QTotal 	+= $rowc['ORD_Q'];
				$PriceTotal += $SubTotal;
				$price		=$rowc['PRN_DIG_PRC']+$rowc['VAL_ADD_AMT']+$rowc['FEE_MISC'];
				$tot_price 	= $price - $rowc['DISC_AMT'];
				if(($rowc['PRN_LEN']!="")&&($rowc['PRN_WID']!="")){
					$prnDim=" ".$rowc['PRN_LEN']."x".$rowc['PRN_WID'];
				}else{
					$prnDim="";
				}
				if ($i%2 == 0) {
					$color="#f2f2f2";
				}else{
					$color="#FFFFFF";
				}
				
		?>
			<tr bgcolor="<?php echo $color; ?>">
				<?php if(($rowc['ORD_DET_NBR_PAR']!="")&&($rowc['PRN_DIG_TYP']!='PROD')){?>
					<td width="1px" style="border-bottom: 1px solid #b3b3b3;">&nbsp;</td>
				<?php } ?>
					<td style="border-bottom: 1px solid #b3b3b3;" class="text-left" <?php if(($rowc['ORD_DET_NBR_PAR']=="")||($rowc['PRN_DIG_TYP']=='PROD')){ echo 'colspan=2'; } ?>><?php echo trim($rowc['DET_TTL']." ".str_replace('(Product)','',$rowc['PRN_DIG_DESC'])); ?></td>
					<td style="border-bottom: 1px solid #b3b3b3;" class="text-right"><?php echo number_format($rowc['ORD_Q'],0,",",",")?></td>
					<?php if($UpperSec != 8){ ?>
					<td style="border-bottom: 1px solid #b3b3b3;" class="text-center"><?php echo $prnDim;?></td>
					<td style="border-bottom: 1px solid #b3b3b3;" class="text-right"><?php echo number_format($tot_price,0,",",",")?></td>
					<td style="border-bottom: 1px solid #b3b3b3;" class="text-right"><?php echo number_format($rowc['TOT_SUB'],0,",",",")?></td>
					<?php } ?>
			</tr>
			<?php $i++; }} ?>
			<tr>
				<?php if($UpperSec != 8){ ?>
				<td colspan="4" class="text-right">&nbsp;</td>
				<?php } ?>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b>Tax</b></td>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b><?php echo number_format($TaxAmt,0,",",".");?></b></td>
			</tr>
			<tr>
				<?php if($UpperSec != 8){ ?>
				<td colspan="4" class="text-right">&nbsp;</td>
				<?php } ?>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b>Total</b></td>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b><?php echo number_format($TotAmt,0,",",".");?></b></td>
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
			LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
			LEFT OUTER JOIN CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
		WHERE ORD_NBR=".$orderNumber." AND ORD_DET_NBR_PAR IS NULL AND DET.DEL_NBR=0 AND DET.PRN_DIG_TYP = 'CUSTOM' 
		ORDER BY 1";
		$result = mysql_query( $query );
		$rows=mysql_num_rows($result);
		?>
		<tbody >
			<?php if($rows > 0){ ?>
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
			$i=0;
			while($row = mysql_fetch_array($result)){
			$OrdQ		= '';
			$PrnDigPrc	= 0;
			$DiscAmt	= 0;
			$TotSub		= 0;
			$rowDetail	= '';
			if($row['PRN_DIG_TYP']=='PROD'){
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
					INNER JOIN
					(
						SELECT ORD_DET_NBR_PAR,
							SUM(ORD_Q) AS ORD_Q,
							SUM(FEE_MISC*ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS FEE_MISC,
							SUM(FAIL_CNT) AS FAIL_CNT,SUM(DISC_AMT*ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS DISC_AMT,
							SUM(VAL_ADD_AMT) AS VAL_ADD_AMT,
							SUM(TOT_SUB) AS TOT_SUB
						FROM ". $detailtable ." DET
						WHERE ORD_DET_NBR_PAR=".$row['ORD_DET_NBR']." AND DET.DEL_NBR=0 GROUP BY 1 ORDER BY 1
					)CLD ON DET.ORD_DET_NBR=CLD.ORD_DET_NBR_PAR
					LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
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
					LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
				WHERE ORD_DET_NBR=".$row['ORD_DET_NBR']." OR ORD_DET_NBR_PAR=".$row['ORD_DET_NBR']." AND DET.DEL_NBR=0 
				ORDER BY 1";
			}
			$resultc=mysql_query($query);
			while($rowd=mysql_fetch_array($resultc)){
				$SubTotald 	= $rowd['TOT_SUB'];
				$QTotald 	+= $rowd['ORD_Q'];
				$PriceTotald += $SubTotald;
				$priced		=$rowd['PRN_DIG_PRC']+$rowd['VAL_ADD_AMT']+$rowd['FEE_MISC'];
				$tot_priced = $priced - $rowd['DISC_AMT'];
				if(($rowd['PRN_LEN']!="")&&($rowd['PRN_WID']!="")){
					$prnDimd=" ".$rowd['PRN_LEN']."x".$rowd['PRN_WID'];
				}else{
					$prnDimd="";
				}
				if ($i%2 == 0) {
					$color="#f2f2f2";
				}else{
					$color="#FFFFFF";
				}
				
		?>
			<tr bgcolor="<?php echo $color; ?>">
				<?php if(($rowd['ORD_DET_NBR_PAR']!="")&&($rowd['PRN_DIG_TYP']!='PROD')){?>
					<td width="1px" style="border-bottom: 1px solid #b3b3b3;">&nbsp;</td>
				<?php } ?>
					<td width="40%" style="border-bottom: 1px solid #b3b3b3;" class="text-left" <?php if(($rowd['ORD_DET_NBR_PAR']=="")||($rowd['PRN_DIG_TYP']=='PROD')){ echo 'colspan=2'; } ?>><?php echo trim($rowd['DET_TTL']." ".str_replace('(Custom)','',$rowd['PRN_DIG_DESC'])); ?></td>
					<td width="5%"  style="border-bottom: 1px solid #b3b3b3;" class="text-right"><?php echo number_format($rowd['ORD_Q'],0,",",",")?></td>
					<?php if($UpperSec != 8){ ?>
					<td width="15%" style="border-bottom: 1px solid #b3b3b3;" class="text-center"><?php echo $prnDimd;?></td>
					<td width="20%" style="border-bottom: 1px solid #b3b3b3;" class="text-right"><?php echo number_format($tot_priced,0,",",",")?></td>
					<td width="20%" style="border-bottom: 1px solid #b3b3b3;" class="text-right"><?php echo number_format($rowd['TOT_SUB'],0,",",",")?></td>
					<?php } ?>
			</tr>
			<?php $i++; }} ?>
			<tr>
				<?php if($UpperSec != 8){ ?>
				<td colspan="4" class="text-right">&nbsp;</td>
				<?php } ?>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b>Subtotal</b></td>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b><?php echo number_format($PriceTotald,0,",",".");?></b></td>
			</tr>
			<tr>
				<?php if($UpperSec != 8){ ?>
				<td colspan="4" class="text-right">&nbsp;</td>
				<?php } ?>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b>Tax</b></td>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b><?php echo number_format($TaxAmt,0,",",".");?></b></td>
			</tr>
			<tr>
				<?php if($UpperSec != 8){ ?>
				<td colspan="4" class="text-right">&nbsp;</td>
				<?php } ?>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b>Total</b></td>
				<td colspan="1" class="text-center" style="border-bottom: 1px solid #b3b3b3;"><b><?php echo number_format($TotAmt,0,",",".");?></b></td>
			</tr>
			<tr>			
				<td colspan="6">
					<div style="line-height: 11px;font-size:9px">
					<?php echo nl2br($FootLttr); ?> 
					<br></br><br></br><br></br>
				</td>
			</tr>
			<?php } ?>
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
						<br><b><?php echo $CrtName; ?></b><br/><b><?php echo $positionType; ?></b></br>
					</div>
				</td>
			</tr>
			</tr>
		</tbody>
		</table>
</section>


<div style="page-break-before: always;"></div>

<section id="body">
	<table width="65%" align="right" cellpadding="3" style="line-height: 15px;">
		<tbody>
			<tr>
				<th colspan="2" style="text-align:left;">Service Offerings Highlight</th>
			</tr>
			<tr>
				<th width="30%" style="border-top: 1px solid #b3b3b3;">Jenis</th>
				<th width="70%" style="border-top: 1px solid #b3b3b3;">Deskripsi/Contoh Ragam</th>
			</tr>
			<tr bgcolor="#f2f2f2">
				<td>Offset Spot Printing</td>
				<td>Single-color flyer, nota, formulir, ticket, letterhead, envelope, map, undangan, dsb.</td>
			</tr>
			<tr>
				<td>Offset Separation Printing</td>
				<td>Full-color flyer, brochure, poster, greeting card, snack box, dsb.</td>
			</tr>
			<tr bgcolor="#f2f2f2">
				<td>Screen Printing</td>
				<td>Sablon kain, umbul-umbul, ballpoint, acrylic, dsb.</td>
			</tr>
			<tr>
				<td>Apparel</td>
				<td>Tshirt katun, jersey, polo shirt, dsb.</td>
			</tr>
			<tr bgcolor="#f2f2f2">
				<td>Indoor Ultra High Resolution Printing</td>
				<td>Easy banner, luster, Ritrama, Avery, Graftac, vinyl China, canvas, dsb.</td>
			</tr>
			<tr>
				<td>Indoor Display</td>
				<td>X-banner, X-banner ball (pemberat), X-banner double sided, Y-banner, roll-up banner, mini x-banner, kiosk, event desk, backwall, kiwi-kiwi, slim acrylic display box, dsb.</td>
			</tr>
			<tr bgcolor="#f2f2f2">
				<td>Laser Cutting & Engraving</td>
				<td>Acrylic, impraboard, polyfoam, polywood, HPL, fabric, dsb.</td>
			</tr>
			
			<tr>
				<td>UV Flatbed</td>
				<td>Acrylic signage, polyfoam, polywood, HPL, keramik, handphone case, gantungan kunci, usb, powerbank, dsb.</td>
			</tr>
			<tr bgcolor="#f2f2f2">
				<td>Fabric Printing</td>
				<td>AUmbul-umbul, bendera, chained-flag, spanduk, soft sign, batik, textile, jersey, tote bag, pillow case, dsb.</td>
			</tr>
			
			<tr>
				<td>Print-on-Demand</td>
				<td>Annual report, business card, portfolio, sertifikat, menu, dsb.</td>
			</tr>
			<tr bgcolor="#f2f2f2">
				<td>Calendar</td>
				<td>Kalender meja, standard, triwulan, catur wulan, semi-lux, lux, kalender kerja, tahunan, harian, spiral, dsb.</td>
			</tr>
			
			<tr>
				<td>Stationery</td>
				<td>Ballpoint, pensil, hanging folder, acrylic display, kotak saran, stapler, dsb.</td>
			</tr>
			<tr bgcolor="#f2f2f2">
				<td>Paper</td>
				<td>HVS, ivory, art paper, texture paper, fancy, local, import, corrugated paper, continuous form dsb.</td>
			</tr>
			
			<tr>
				<td>ID Card</td>
				<td>PVC ID card, ID card holder, tali ID card, etc.</td>
			</tr>
			<tr bgcolor="#f2f2f2">
				<td>Pin</td>
				<td>Standard dan karet.</td>
			</tr>
			
			<tr>
				<td>Tent</td>
				<td>Tenda 2x2m, 3x3m, 4x4m, dsb.</td>
			</tr>
			<tr bgcolor="#f2f2f2">
				<td>Perijinan Iklan</td>
				<td>Sesuai dengan biaya yang ditetapkan oleh pemerintah.</td>
			</tr>
			
			<tr>
				<td>Jasa Pemasangan</td>
				<td>Rontek, baliho, billboard, umbul-umbul, spanduk, sticker, dsb.</td>
			</tr>
			<tr bgcolor="#f2f2f2">
				<td>Kontruksi</td>
				<td>Billboard, baliho, papan nama, neon box, dsb.</td>
			</tr>
			
			<tr>
				<td>Branding Mobil</td>
				<td>Pick-up, box, van, mini van, bus, dsb.</td>
			</tr>
			<tr bgcolor="#f2f2f2">
				<td>Sticker Cutting </td>
				<td>Sesuai dengan tingkat kesulitan pemotongan.</td>
			</tr>
			
			<tr>
				<td>Paper Cutting</td>
				<td>Rounded corner, punch, pon, dsb.</td>
			</tr>
			<tr bgcolor="#f2f2f2">
				<td>Merchandising</td>
				<td>Tas, packaging, sticker, box, gantungan kunci karet, gelang karet, dsb.</td>
			</tr>
			
			<tr>
				<td>Post Production</td>
				<td>Mesin lem dan lipat otomatis, jilid, perforasi, nomorator, binding, dsb.</td>
			</tr>
			<tr bgcolor="#f2f2f2">
				<td>Photocopy</td>
				<td>Off the glass, digital photocopy, dsb.</td>
			</tr>
		</tbody>
	</table>
</section>
</body>
</html>
</body>
</html>
