<?php
$salestype 		= $_GET['TYP'];
$formattedOrderNumber = leadZero($orderNumber, 7);
$paper = array(0, 0, 21 * (72/2.54), 29.7 * (72/2.54));

$OrdNbr		= $_GET['ORD_NBR'];
$IvcTyp		= $_GET['IVC_TYP'];
$tableType	= $_GET['TYP'];

if($tableType == "EST"){
	$headtable 	= "RTL.RTL_ORD_HEAD_EST";
	$detailtable= "RTL.RTL_ORD_DET_EST";
}else{
	$headtable 	= "RTL.RTL_ORD_HEAD";
	$detailtable= "RTL.RTL_ORD_DET";
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
<title>Invoice - Champion</title>
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
	padding-top: 270px;
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
	bottom: 20px;
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
					No. <?php echo $formattedOrderNumber; ?><br/>Ref. <?php echo $RefNumber; ?><br/><?php echo $OrdDate; ?>
				</div>
				<div style="line-height: 13px;padding-top:5px;">
					<b><?php echo $FromCompany; ?> <?php echo $ShpName; ?></b><br/><?php echo $ShpAddress; ?><br/> <?php echo $ShpZip; ?> <?php echo $ShpPhone; ?>
				</div>
				<div style="line-height: 13px;padding-top:5px;">
					<b><?php echo $ReceivingCompany; ?> <?php echo $RcvName; ?></b><br/><?php echo $RcvAddress; ?><br/><?php echo $RcvCity; ?> <?php echo $RcvZip; ?>
				</div>

				<?php if($BilName != "") {?>
				<div style="line-height: 13px;padding-top:5px;">
					<b><?php echo $BillTo; ?> <?php echo $BilName; ?></b><br/><?php echo $BilAddress; ?><br/><?php echo $BilCity; ?> <?php echo $BilZip; ?>
				</div>
				<?php }else{ ?>
				<div style="line-height: 13px;padding-top:5px;">
					<?php if ($RcvName!=''){ ?>
					<b><?php echo $BillTo; ?> <?php echo $RcvName; ?></b><br/><?php echo $RcvAddress; ?><br/><?php echo $RcvCity; ?> <?php echo $RcvZip; ?>
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
	<table width="100%">
		<tr>
			<td width="32%">&nbsp;</td>
			<td width="3%">&nbsp;</td>
			<td width="65%">
				<table width="100%" style="font-size:12px">
					<tr>
						<td width="100%">
							<div style="border-bottom:1px solid #cccccc;white-space: nowrap;">Received by</div>
						</td>
						<td width="100%">
							<div style="border-bottom:1px solid #cccccc;white-space: nowrap;">Sold by</div>
						</td>
					</tr>
					<tr>
						<td width="100%" colspan="2">
							<div><b>Printed by <?php echo $name; ?> on <?php echo date('d:m:Y') ?> at <?php echo date('H:i:s'); ?> </b></div>
						</td>
					</tr>
				</table>			
			</td>
		</tr>
	</table>
</footer>
<br\>
<section id="body">
	
	<table width="67%" align="right" cellpadding="3">
		
			<tr style="border-bottom: 4px #000000;">	
				<th class="text-center" width="55%" style="border-bottom: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Description</th>
				<th class="text-center" width="15%" style="border-bottom: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Qty</th>
				<th class="text-center" width="5%" style="border-bottom: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Disc</th>
				<th class="text-right" width="15%" style="border-bottom: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Price/Rate</th>
				<th class="text-right" width="15%" style="border-bottom: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Total</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$query = "SELECT 			
					PYMT_DOWN,
					PYMT_REM,
					TOT_REM,
					COALESCE(HED.TAX_APL_ID, 0) AS TAX_APL_ID,
					COALESCE(HED.TAX_AMT,0) AS TAX_AMT,
					ORD_DET_NBR,
					HED.ORD_NBR,
					DET.INV_NBR,
					INV.INV_BCD,
					INV.PRC,
					INV.NAME,
					INV.SIZE,
					CLR.COLR_DESC,
					INV.THIC,
					INV.WEIGHT,
					UNT.UNIT_DESC,
					DET.INV_DESC,
					ORD_Q,
					DET.INV_PRC,
					DET.FEE_MISC AS DET_MISC,
					DET.DISC_PCT,
					DET.DISC_AMT,
					TOT_SUB,
					HED.FEE_MISC AS HED_MISC,SPC_NTE,
					HED.CRT_TS,
					HED.CRT_NBR,
					DET.UPD_TS,
					DET.UPD_NBR
				FROM ". $detailtable ." DET 
					LEFT OUTER JOIN RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR
					LEFT OUTER JOIN ". $headtable ." HED ON DET.ORD_NBR=HED.ORD_NBR
					LEFT OUTER JOIN CMP.INV_COLR CLR ON INV.COLR_NBR=CLR.COLR_NBR
					LEFT OUTER JOIN RTL.UNIT_TYP UNT ON INV.CNT_X_TYP=UNT.UNIT_TYP
				WHERE DET.DEL_NBR = 0 AND DET.ORD_NBR=".$OrdNbr."
				ORDER BY DET.ORD_DET_NBR ASC";
				$result = mysql_query( $query );
				$i=0;
				while($row = mysql_fetch_array($result)){
					$PriceTotal 	+= $row['TOT_SUB'];
					$PymtRem		=$row['PYMT_REM'];
					//$totalPayment	=$row['PYMT_DOWN'] + $row['PYMT_REM'];
					$TotRem			=$row['TOT_REM'];
					$headMisc		= $row['HED_MISC'];
					$QTotal 		+= $row['ORD_Q'];
					$SpcNte 		= $row['SPC_NTE'];
					
					
					if($IvcTyp != "SL"){
							$TotalRemain = $PriceTotal + $row['HED_MISC'] - $row['PYMT_DOWN'] - $row['PYMT_REM']+ $row['TAX_AMT'];
					}else{
						$TotalRemain = $row['TOT_REM'];
					}
					$TotalAmount = $PriceTotal + $row['HED_MISC'] + $row['TAX_AMT'];
					$TaxAmount	 = $row['TAX_AMT'];
					
				if ($i%2 == 0) {$color="#f2f2f2";}else{$color="#FFFFFF";}
				?>
			<tr bgcolor="<?php echo $color; ?>">
				<td class="text-left" style="border-bottom: 1px solid #b3b3b3;">
					<?php 
					echo $row['NAME']." ".$row['SIZE']." ".$row['COLR_DESC']." ".$row['THIC']." ".$row['INV_DESC'];
					if($row['ORD_X']!=''){echo " Uk ".$row['ORD_X'];}
					if($row['ORD_Y']!=''){echo "x".$row['ORD_Y'];}
					if($row['ORD_Z']!=''){echo "x".$row['ORD_Z'];}
					?>
				</td>
				<td class="text-center" style="border-bottom: 1px solid #b3b3b3;"><?php echo $row['ORD_Q']." ".$row['UNIT_DESC'];?></td>
				<td class="text-center" style="border-bottom: 1px solid #b3b3b3;"><?php echo number_format($row['DISC_PCT'])."/".number_format($row['DISC_AMT'],0,",",".");?></td>
				<td class="text-right" style="border-bottom: 1px solid #b3b3b3;"><?php echo number_format($row['PRC'],0,",",".");?></td>
				<td class="text-right" style="border-bottom: 1px solid #b3b3b3;"><?php echo number_format($row['TOT_SUB'],0,",",".");?></td>					
			</tr>
			<?php $i++; } ?>
			
			<tr>
				<td colspan="1" class="text-right">&nbsp;</td>
				<td colspan="3" class="text-right">Subtotal </td>
				<td colspan="1" class="text-right"><?php echo number_format($PriceTotal,0,",",".");?></td>
			</tr>
			<tr>
				<td colspan="1" class="text-right">&nbsp;</td>
				<td colspan="3" class="text-right">Tax </td>
				<td colspan="1" class="text-right"><?php echo number_format($TaxAmount,0,",",".");?></td>
			</tr>
			<tr>
				<td colspan="1" class="text-right">&nbsp;</td>
				<td colspan="3" class="text-right">S&H </td>
				<td colspan="1" class="text-right"><?php echo number_format($headMisc,0,",",".");?></td>
			</tr>
			<tr>
				<td colspan="1" class="text-right">&nbsp;</td>
				<td colspan="3" class="text-right" style="border-bottom: 1px solid #b3b3b3;background-color: #f2f2f2;"><b>TOTAL Rp. </b> </td>
				<td colspan="1" class="text-right" style="border-bottom: 1px solid #b3b3b3;background-color: #f2f2f2;"><b><?php echo number_format($TotalAmount,0,",",".");?></b></td>
			</tr>
			<tr>
				<td colspan="1" class="text-right">&nbsp;</td>
				<td colspan="3" class="text-right" style="border-bottom: 1px solid #b3b3b3;">Bayar </td>
				<td colspan="1" class="text-right" style="border-bottom: 1px solid #b3b3b3;"><?php echo number_format($totalPayment,0,",",".");?></td>
			</tr>
			<tr>
				<td colspan="1" class="text-right">&nbsp;</td>
				<td colspan="3" class="text-right" style="border-bottom: 1px solid #b3b3b3;background-color: #f2f2f2;"><b>SISA Rp. </b> </td>
				<td colspan="1" class="text-right" style="border-bottom: 1px solid #b3b3b3;background-color: #f2f2f2;"><b><?php echo number_format($TotalRemain,0,",",".");?></b></td>
			</tr>
		</tbody>
	</table>
</section>
</body>
</html>
