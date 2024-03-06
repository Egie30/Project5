<?php
include "framework/security/default.php";

$UpperSec		= getSecurity($_SESSION['userID'],"Accounting");
$salestype 		= $_GET['TYP'];
$formattedOrderNumber = leadZero($orderNumber, 7);
$paper = array(0, 0, 21 * (72/2.54), 29.7 * (72/2.54));

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
	margin-bottom: 80px;
	padding-top: 250px;
	font-weight: normal;
}

table {
	border-collapse: collapse;
	border: none;
}

#header {
	font-family: arial, sans-serif;
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
	bottom: 70px; /*bottom: 20px;*/
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
			<td width="2%">&nbsp;</td>
			<td width="65%" style="padding-top:205px;">
				<div style="font-size:33px;"><?php echo $Title; ?></div>
				<div style="line-height: 13px;">
					No. <?php echo $formattedOrderNumber; ?><br/>Ref. <?php echo $RefNbr; ?><br/><?php echo $OrdDate; ?>
				</div>
				<div style="line-height: 13px;padding-top:5px;">
					<b><?php echo $FromCompany; ?> <?php echo $PrnName; ?></b><br/><?php echo $PrnAddress; ?><br/> <?php echo $PrnZip; ?> <?php echo $PrnPhone; ?>
				</div>
				<div style="line-height: 13px;padding-top:5px;">
					<?php if ($BuyName !=''){ ?>
					<b><?php echo $Customer; ?> <?php echo $BuyName; ?></b><br/><?php echo $BuyAddress; ?> <?php echo $BuyCity; ?> <?php echo $BuyZip; ?>
				<?php } else { ?>
					<b><?php echo $Customer; ?> <?php echo $BuyPrsnName; ?></b>
				<?php } ?>
				</div>

				<?php if($BilName != "") {?>
				<div style="line-height: 13px;padding-top:5px;">
					<b><?php echo $BillTo; ?> <?php echo $BilName; ?></b><br/><?php echo $BilAddress; ?> <?php echo $BilCity; ?> <?php echo $BilZip; ?>
				</div>
				<?php }else{ ?>
				<div style="line-height: 13px;padding-top:5px;">
					<?php if ($BuyName!=''){ ?>
					<b><?php echo $BillTo; ?> <?php echo $BuyName; ?></b><br/><?php echo $BuyAddress; ?> <?php echo $BuyCity; ?> <?php echo $BuyZip; ?>
				<?php } else { ?>
					<b><?php echo $BillTo; ?> <?php echo $BuyPrsnName; ?></b>
				<?php } ?>

				</div>
				<?php } ?>
				<div style="line-height: 13px;padding-top:5px;">
					Order Title :<b><?php echo $OrdTtl; ?></b>
				</div>
			</td>
		</tr>
	</table>
</header>

<footer id="footer">
	<table width="100%" style="font-size:11px" border=0>
		<?php if($ActgTyp == 1){ ?>
		<tr>
			<td align="center" colspan="3"><b>Please make a payment to:</b></td>
		</tr>
		<tr>
			<td width="100%" style="padding-bottom: 5px;">
				<div style="font-size: 11px;line-height: 13px;">
					Account Name: <b><?php echo $AccountName; ?></b><br/>
					Account Number: <b><?php echo $AccountNbr; ?></b><br/>
					Bank Name: <b><?php echo $AccountBank; ?></b>
				</div>
			</td>
			<td width="100%" style="padding-bottom: 5px;">
				<div style="font-size: 11px;line-height: 13px;">
					Account Name: <b>Champion Multikarya Pandhega, PT</b><br/>
					Account Number: <b>002901777777300</b><br/>
					Bank Name: <b>Bank Rakyat Indonesia</b>
				</div>
			</td>
		</tr>
		<?php } ?>
		
		<?php if($ActgTyp == 2){ ?>
		<tr>
			<td align="center" colspan="3"><b>Please make a payment to:</b></td>
		</tr>
		<tr>
			<td style="padding-bottom: 5px;">
				<div style="font-size: 11px;line-height: 13px;">
					Account Name: <b><?php echo $AccountName; ?></b><br/>
					Account Number: <b><?php echo $AccountNbr; ?></b><br/>
					Bank Name: <b><?php echo $AccountBank; ?></b>
				</div>
			</td>
			<td style="padding-bottom: 5px;">
				<div style="font-size: 11px;line-height: 13px;">
					Account Name: <b>Champion Campus, CV</b><br/>
					Account Number: <b>1370013702572</b><br/>
					Bank Name: <b>Bank Mandiri</b>
				</div>
			</td>
			<td style="padding-bottom: 5px;">
				<div style="font-size: 11px;line-height: 13px;">
					Account Name: <b>Champion Campus, CV</b><br/>
					Account Number: <b>056111000140</b><br/>
					Bank Name: <b>Bank BPD DIY</b>
				</div>
			</td>
		</tr>
		<?php } ?>
	</table>
	<table width="100%">
		<tr>
			<td><div style="border-bottom:1px solid #cccccc;white-space: nowrap;">Received by</div></td>
			<td><div style="border-bottom:1px solid #cccccc;white-space: nowrap;">Sold by</div></td>
		</tr>
		<tr>
			<td width="100%" colspan="2">
				<div><b>Printed by <?php echo $name; ?> on <?php echo date('d:m:Y') ?> at <?php echo date('H:i:s'); ?> </b></div>
			</td>
		</tr>
	</table>	
</footer>

<section id="body">
	<table width="65%" align="right" cellpadding="3" style="line-height: 23px;">
		<thead>
			<tr>
				<th class="text-center" width="55%" colspan=2 style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Description</th>
				<th class="text-center" width="5%" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Qty</th>
				<?php if($UpperSec != 8){ ?>
				<th class="text-right" width="15%" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Price/Rate</th>
				<th class="text-right" width="15%" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Disc</th>
				<th class="text-right" width="15%" style="border-top: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Total</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php
						$query="SELECT ORD_DET_NBR,DET.ORD_NBR,DET_TTL,PRN_DIG_DESC,DET.PRN_DIG_PRC,ORD_Q,FIL_LOC,PRN_LEN,PRN_WID,FEE_MISC,FAIL_CNT,DISC_PCT,DISC_AMT,VAL_ADD_AMT,TOT_SUB,ROLL_F,HND_OFF_TYP,HND_OFF_TS,SORT_BAY_ID,DET.PRN_DIG_TYP
								FROM ". $detailtable ." DET 
									LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
									LEFT OUTER JOIN CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
								WHERE ORD_NBR=".$orderNumber." AND ORD_DET_NBR_PAR IS NULL AND DET.DEL_NBR=0 ORDER BY 1";
							
						$result = mysql_query( $query );
						$i=0;
						while($row = mysql_fetch_array($result)){
						
						$OrdQ='';
						$PrnDigPrc=0;
						$DiscAmt=0;
						$TotSub=0;
						$rowDetail='';
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
										LEFT OUTER JOIN
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
						while($rowc=mysql_fetch_array($resultc))
							{
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
					<td width="55%" style="border-bottom: 1px solid #b3b3b3;" class="text-left" <?php if(($rowc['ORD_DET_NBR_PAR']=="")||($rowc['PRN_DIG_TYP']=='PROD')){ echo 'colspan=2'; } ?>><?php echo trim($rowc['DET_TTL']." ".$rowc['PRN_DIG_DESC'].$prnDim); ?></td>
					<td width="5%"  style="border-bottom: 1px solid #b3b3b3;" class="text-right"><?php echo number_format($rowc['ORD_Q'],0,",",",")?></td>
					<?php if($UpperSec != 8){ ?>
					<td width="15%" style="border-bottom: 1px solid #b3b3b3;" class="text-right"><?php echo number_format($price,0,",",",")?></td>
					<td width="15%" style="border-bottom: 1px solid #b3b3b3;" class="text-right"><?php echo number_format($rowc['DISC_PCT'],0,",",".")."/".number_format($rowc['DISC_AMT'],0,",",".")?></td>
					<td width="15%" style="border-bottom: 1px solid #b3b3b3;" class="text-right"><?php echo number_format($rowc['TOT_SUB'],0,",",",")?></td>
					<?php } ?>
			</tr>
			<?php $i++; }} ?>
			<tr>
				<?php if($UpperSec != 8){ ?>
				<td colspan="3" class="text-right">&nbsp;</td>
				<?php } ?>
				<td colspan="2" class="text-right">Subtotal</td>
				<td colspan="1" class="text-right"><?php echo number_format($PriceTotal,0,",",".");?></td>
			</tr>
			<tr>
				<?php if($UpperSec != 8){ ?>
				<td colspan="3" class="text-right">&nbsp;</td>
				<?php } ?>
				<td colspan="2" class="text-right">Tax</td>
				<td colspan="1" class="text-right"><?php echo number_format($TaxAmt,0,",",".");?></td>
			</tr>
			<tr>
				<?php if($UpperSec != 8){ ?>
				<td colspan="3" class="text-right">&nbsp;</td>
				<?php } ?>
				<td colspan="2" class="text-right">S&H</td>
				<td colspan="1" class="text-right"><?php echo number_format($FeeMisc,0,",",".");?></td>
			</tr>
			<tr>
				<?php if($UpperSec != 8){ ?>
				<td colspan="3" class="text-right">&nbsp;</td>
				<?php } ?>
				<td colspan="2" class="text-right" style="border-bottom: 1px solid #b3b3b3;background-color: #f2f2f2;"><b>TOTAL Rp.</b></td>
				<td colspan="1" class="text-right" style="border-bottom: 1px solid #b3b3b3;background-color: #f2f2f2;"><b><?php echo number_format($TotAmt,0,",",".");?></b></td>
			</tr>
			<tr>
				<?php if($UpperSec != 8){ ?>
				<td colspan="3" class="text-right">&nbsp;</td>
				<?php } ?>
				<td colspan="2" class="text-right">Paid</td>
				<td colspan="1" class="text-right"><?php echo number_format($TotPymt,0,",",".");?></td>
			</tr>
			<tr>
				<?php if($UpperSec != 8){ ?>
				<td colspan="3" class="text-right">&nbsp;</td>
				<?php } ?>
				<td colspan="2" class="text-right" style="border-bottom: 1px solid #b3b3b3;background-color: #f2f2f2;"><b>BALANCE Rp. </b> </td>
				<td colspan="1" class="text-right" style="border-bottom: 1px solid #b3b3b3;background-color: #f2f2f2;"><b><?php echo number_format($TotRem,0,",",".");?></b></td>
			</tr>
		</tbody>
	</table>
</section>
</body>
</html>