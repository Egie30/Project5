<?php
$formattedOrderNumber = leadZero($orderNumber, 7);
$paper = array(0, 0, 21 * (72/2.54), 29.7 * (72/2.54)); // 1 cm = 72/2.54 pt

$OrdNbr=$_GET['ORD_NBR'];
$IvcTyp=$_GET['IVC_TYP'];

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
	margin-top: -3px;
}
body {
	font-family: Calibri, sans-serif;
	margin: 175px .5cm;
	text-align: right;
	font-size:12px;
	line-height: 18px;
	margin-bottom: 75px;
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
	bottom: 50px;
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

<?php 
if($invoiceType=="PO")
{ 
	$style = 'padding-top: 310px;';
}
else
{
	$style = 'padding-top: 250px;';
}

?>

<body style="<?php echo $style?>"">
<header id="header">
	<table width="100%">
		<tbody>
			<tr>
				<td width="30%">
					<img src="<?php echo $img; ?>" height="410px" />
				</td>
				<td width="3%"></td>
				<td width="67%" style="text-align: left;vertical-align: text-top;line-height: 140%;padding-top:200px;">
					<h1 style="padding-top:9.5px;"> <?php echo $Title; ?></h1><br/>
					No. <?php echo $formattedOrderNumber; ?>
					<br>No. Ref. <?php echo $RefNumber; ?></br>
					<br/><?php echo $OrdDate; ?><br/><br/>
					<?php if($invoiceType == "PO"){ ?>
						<?php echo $FromCompany; ?> 
						<b style="vertical-align: text-top;"><?php echo $RcvName; ?></b><br/>
						<?php echo $RcvAddress; ?><br/><?php echo $RcvCity; ?> <?php echo $RcvZip; ?><br/><br/>
						<?php echo $ReceivingCompany; ?> 
						<b style="vertical-align: text-top;"><?php echo $ShpName; ?></b><br/>
						<?php echo $ShpAddress; ?><br/><?php echo $ShpCity; ?>  <?php echo $ShpZip; ?><br/><br/>

						<?php if($BilName != "") {?>
						<?php echo $BillTo; ?> 
						<b style="vertical-align: text-top;"><?php echo $BilName; ?></b><br/>
						<?php echo $BilAddress; ?><br/><?php echo $BilCity; ?> <?php echo $BilZip; ?><br/><br/>
						<?php }else{ ?>
						<?php echo $BillTo; ?> 
						<b style="vertical-align: text-top;"><?php echo $ShpName; ?></b><br/>
						<?php echo $ShpAddress; ?><br/><?php echo $ShpCity; ?>  <?php echo $ShpZip; ?><br/><br/>
						<?php } ?>

					<?php }else{ ?>
						<?php echo $FromCompany; ?> 
						<b style="vertical-align: text-top;"><?php echo $ShpName; ?></b><br/>
						<?php echo $ShpAddress; ?><br/><?php echo $ShpCity; ?>  <?php echo $ShpZip; ?><br/><br/>
						
						<?php echo $ReceivingCompany; ?> <b style="vertical-align: text-top;"><?php echo $RcvName; ?></b><br/>
						<?php echo $RcvAddress; ?><br/><?php echo $RcvCity; ?> <?php echo $RcvZip; ?>
					<?php } ?>

				</td>
			</tr>			
		</tbody>
	</table>
</header>

<footer id="footer">
	<table width="100%" style="font-size:12px">
			<tr>
				<td width="100%" class="text-center">
					<div class="text-center"><b>Printed by <?php echo $name; ?> on <?php echo date('d:m:Y') ?> at <?php echo date('H:i:s'); ?> </b></div>
				</td>
			</tr>
	</table>
</footer>
<br/>
<section id="body">
	
	<table width="67%" align="right" cellpadding="3">
		
			<tr style="border-bottom: 4px #000000;">	
				<th class="text-center" width="65%" style="border-bottom: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Description</th>
				<th class="text-center" width="5%" style="border-bottom: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Qty</th>
				<th class="text-right" width="15%" style="border-bottom: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Price/Rate</th>
				<th class="text-right" width="15%" style="border-bottom: 1px solid #b3b3b3;border-top: 1px solid #b3b3b3;">Disc</th>
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
							DET.INV_DESC,
							ORD_Q,
							ORD_X,
							ORD_Y,
							ORD_Z,
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
						FROM RTL.RTL_STK_DET DET 
							LEFT OUTER JOIN RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR
							LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR
						WHERE DET.ORD_NBR=".$OrdNbr."
						ORDER BY DET.ORD_DET_NBR ASC";
				$result = mysql_query( $query );
				$i=0;
				while($row = mysql_fetch_array($result)){
					$PriceTotal 	+= $row['TOT_SUB'];
					$PymtRem		=$row['PYMT_REM'];
					$totalPayment	=$row['PYMT_DOWN'] + $row['PYMT_REM'];
					$TotRem			=$row['TOT_REM'];
					$headMisc		= $row['HED_MISC'];
					$QTotal 		+= $row['ORD_Q'];
					$SpcNte 		= $row['SPC_NTE'];
					
					
					$TotalRemain = $row['TOT_REM'];
					
					if($row['TAX_APL_ID'] == "A"){
						$TotalAmount = $PriceTotal + $row['HED_MISC'] + $row['TAX_AMT'];
					}else{
						$TotalAmount = $PriceTotal + $row['HED_MISC'];
					}
					$TaxAmount	 = $row['TAX_AMT'];
					
				if ($i%2 == 0) {$color="#f2f2f2";}else{$color="#FFFFFF";}
				?>
			<tr bgcolor="<?php echo $color; ?>">
				<td class="text-left" style="border-bottom: 1px solid #b3b3b3;">
					<?php 
					echo $row['NAME']." ".$row['INV_DESC'];
					if($row['ORD_X']!=''){echo " Uk ".$row['ORD_X'];}
					if($row['ORD_Y']!=''){echo "x".$row['ORD_Y'];}
					if($row['ORD_Z']!=''){echo "x".$row['ORD_Z'];}
					?>
				</td>
				<td class="text-center" style="border-bottom: 1px solid #b3b3b3;"><?php echo $row['ORD_Q'];?></td>
				<td class="text-right" style="border-bottom: 1px solid #b3b3b3;"><?php echo number_format($row['INV_PRC'],0,",",".");?></td>
				<td class="text-right" style="border-bottom: 1px solid #b3b3b3;"><?php echo number_format($row['DISC_PCT'],0,",",".")."/".number_format($row['DISC_AMT'],0,",",".");?></td>
				<td class="text-right" style="border-bottom: 1px solid #b3b3b3;"><?php echo number_format($row['TOT_SUB'],0,",",".");?></td>					
			</tr>
			<?php $i++; } ?>
			<?php if($IvcTyp == "SL"){ ?>
			<tr>
				<td colspan="2" class="text-right">&nbsp;</td>
				<td colspan="2" class="text-right">Subtotal </td>
				<td colspan="1" class="text-right"><?php echo number_format($PriceTotal,0,",",".");?></td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="2" class="text-right">&nbsp;</td>
				<td colspan="2" class="text-right">Tax </td>
				<td colspan="1" class="text-right"><?php echo number_format($TaxAmount,0,",",".");?></td>
			</tr>
			<tr>
				<td colspan="2" class="text-right">&nbsp;</td>
				<td colspan="2" class="text-right">S&H </td>
				<td colspan="1" class="text-right"><?php echo number_format($headMisc,0,",",".");?></td>
			</tr>
			<tr>
				<td colspan="2" class="text-right">&nbsp;</td>
				<td colspan="2" class="text-right" style="border-bottom: 1px solid #b3b3b3;background-color: #f2f2f2;"><b>TOTAL Rp. </b> </td>
				<td colspan="1" class="text-right" style="border-bottom: 1px solid #b3b3b3;background-color: #f2f2f2;"><b><?php echo number_format($TotalAmount,0,",",".");?></b></td>
			</tr>
			<tr>
				<td colspan="2" class="text-right">&nbsp;</td>
				<td colspan="2" class="text-right" style="border-bottom: 1px solid #b3b3b3;">Bayar </td>
				<td colspan="1" class="text-right" style="border-bottom: 1px solid #b3b3b3;"><?php echo number_format($totalPayment,0,",",".");?></td>
			</tr>
			<tr>
				<td colspan="2" class="text-right">&nbsp;</td>
				<td colspan="2" class="text-right" style="border-bottom: 1px solid #b3b3b3;background-color: #f2f2f2;"><b>SISA Rp. </b> </td>
				<td colspan="1" class="text-right" style="border-bottom: 1px solid #b3b3b3;background-color: #f2f2f2;"><b><?php echo number_format($TotalRemain,0,",",".");?></b></td>
			</tr>
			
			<?php if(in_array($IvcTyp, array('PO','RC'))){?>
			<tr>
				<td colspan="5">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="5" style="text-align: left;vertical-align: text-top;line-height: 140%;">
					<?php if($IvcTyp =='PO'){?>
					Account Name &nbsp;&nbsp;&nbsp;: <b><?php echo $RcvAcctNm; ?></b><br/>
					Bank Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <b><?php echo $RcvBank; ?></b><br/>
					Account Number : <b><?php echo $RcvAcctNbr; ?></b>
					<?php }else{ ?>
					Account Name &nbsp;&nbsp;&nbsp;: <b><?php echo $ShpAcctNm; ?></b><br/>
					Bank Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <b><?php echo $ShpBank; ?></b><br/>
					Account Number : <b><?php echo $ShpAcctNbr; ?></b>
					<?php } ?>
				</td>
			</tr>
			<?php } ?>
			<?php if($SpcNte !=''){?>
			<tr >
				<td colspan="4"><b>Notes:</b></td>
			</tr>
			<tr>
				<td colspan="4"><?php echo $SpcNte; ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</section>
</body>
</html>
