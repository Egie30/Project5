<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	include "framework/functions/dotmatrix.php";

	$OrdNbr		= $_GET['ORD_NBR'];
	$Lead		= $_GET['LEAD'];
	$BrcList	= $_GET['BRCLST'];
	$tableType	= $_GET['TYP'];

	if($tableType == "EST"){
		$headtable 	= "RTL.RTL_ORD_HEAD_EST";
		$detailtable= "RTL.RTL_ORD_DET_EST";
	}else{
		$headtable 	= "RTL.RTL_ORD_HEAD";
		$detailtable= "RTL.RTL_ORD_DET";
	}
	
	//Set label parameter here
	$ColNbr=6;
	$RowNbr=8;
	$LblWid=3.5;
	$LblHgt=2.3;
	$MdaWid=24.3;
	$BrcWid=2.5;
	$BrcHgt=0.6;
	$TopMgn=0.25;
	$Header="Champion Campus";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<style type="text/css">
	@page {margin:0cm;margin-top:<? echo $TopMgn; ?>cm;}
</style>

</head>

<body style='margin:0;padding:0;width:100%;font-family:arial'>
	<div style='text-align:center;width:100%;padding-top:6px'>
	<table style='width:<?php echo $MdaWid; ?>cm;background-color:#dddddd;margin-left:auto;margin-right:auto;page-break-after:always'>
		<tr style='height:<?php echo $LblHgt; ?>cm'>
			<?php
				$count=1;
				$line=1;
				$item=0;
				//Print leading spaces
				for($q=1;$q<=$Lead;$q++)
				{
					if($count==$ColNbr+1){
						$line++;
						if($line==$RowNbr+1){
							echo "</tr></table><table style='width:".$MdaWid."cm;background-color:#dddddd;margin-left:auto;margin-right:auto;page-break-after:always'><tr style='height:".$LblHgt."cm'>";
							$line=1;
							}else{
							echo "</tr><tr style='height:".$LblHgt."cm'>";
						}
						$count=1;
					}
					echo "<td style='text-align:center'>";
					echo "<div style='width:".$BrcWid."cm;height:".$BrcHgt."cm'></div>";
					echo "</td>";
					$count++;
					$item++;
				}
				
				if ($OrdNbr=="CUSTOM") 
				{
				$ArrBrc=explode(' ',$BrcList);
				foreach($ArrBrc as $value) 
				{
					if(strlen($value)<=8){
						$value=LeadZero($value,8);
					}
				//echo $value;
				$query="SELECT NAME,PRC FROM RTL.INVENTORY WHERE INV_BCD='$value'";
				//echo $query;
				$result=mysql_query($query);
				$row = mysql_fetch_array($result);
					if($count==$ColNbr+1){
						$line++;
						if($line==$RowNbr+1){
							echo "</tr></table><table style='width:".$MdaWid."cm;background-color:#dddddd;margin-left:auto;margin-right:auto;page-break-after:always'><tr style='height:".$LblHgt."cm'>";
							$line=1;
						}else{
							echo "</tr><tr style='height:".$LblHgt."cm'>";
						}
						$count=1;
					}
					for($q=1;$q<=count($value);$q++)
					{
						if ($row['NAME']<>""){
						if($count==$ColNbr+1){
							$line++;
							if($line==$RowNbr+1){
								echo "</tr></table><table style='width:".$MdaWid."cm;background-color:#dddddd;margin-left:auto;margin-right:auto;page-break-after:always'><tr style='height:".$LblHgt."cm'>";
								$line=1;
							}else{
								echo "</tr><tr style='height:".$LblHgt."cm'>";
							}
							$count=1;
						}
						echo "<td style='text-align:center'>";
						echo "<div style='font-size:8px;width:100%;padding-bottom:1px'>".$Header."</div>";
						echo "<img src='framework/barcode/retail-barcode.php?STRING=".$value."' style='width:".$BrcWid."cm;height:".$BrcHgt."cm'>";
						echo "<div style='font-size:6px;width:100%;margin-top:-3px'>".$row['NAME']."</div>";
						if($_GET['HRG']=='Y'){
							echo "<div style='font-size:8px;width:100%;'>".$value." <b>Rp. ".number_format($row['PRC'],0,",",".")."</b></div>";
						}else{	
							echo "<div style='font-size:8px;width:100%;'>".$value." <b></b></div>";
						}
						echo "</td>";
						$count++;
						$item++;
						}
					}
				}
				}
				else
				{
				$query="SELECT ORD_DET_NBR,INV.INV_BCD,INV.NAME,ORD_Q,PRC
				FROM RTL.RTL_ORD_DET DET LEFT OUTER JOIN RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR
				WHERE ORD_NBR=".$OrdNbr;
				//echo $query;
				$result=mysql_query($query);
				while($row=mysql_fetch_array($result))
				{
					if($count==$ColNbr+1){
						$line++;
						if($line==$RowNbr+1){
							echo "</tr></table><table style='width:".$MdaWid."cm;background-color:#dddddd;margin-left:auto;margin-right:auto;page-break-after:always'><tr style='height:".$LblHgt."cm'>";
							$line=1;
						}else{
							echo "</tr><tr style='height:".$LblHgt."cm'>";
						}
						$count=1;
					}
					for($q=1;$q<=$row['ORD_Q'];$q++)
					{
						if($count==$ColNbr+1){
							$line++;
							if($line==$RowNbr+1){
								echo "</tr></table><table style='width:".$MdaWid."cm;background-color:#dddddd;margin-left:auto;margin-right:auto;page-break-after:always'><tr style='height:".$LblHgt."cm'>";
								$line=1;
							}else{
								echo "</tr><tr style='height:".$LblHgt."cm'>";
							}
							$count=1;
						}
						echo "<td style='text-align:center'>";
						echo "<div style='font-size:8px;width:100%;padding-bottom:1px'>".$Header."</div>";
						echo "<img src='framework/barcode/retail-barcode.php?STRING=".$row['INV_BCD']."' style='width:".$BrcWid."cm;height:".$BrcHgt."cm'>";
						echo "<div style='font-size:6px;width:100%;margin-top:-3px'>".$row['NAME']."</div>";
						if($_GET['HRG']=='Y'){
						echo "<div style='font-size:8px;width:100%;'>".$value." <b>Rp. ".number_format($row['PRC'],0,",",".")."</b></div>";
						}else{	
						echo "<div style='font-size:8px;width:100%;'>".$value." <b></b></div>";
						}
						echo "</td>";
						$count++;
						$item++;
					}
				}
				}
				
				$mod=6-$item%6;
				if($mod<6){
					for($q=1;$q<=$mod;$q++)
					{
						echo "<td style='text-align:center'>";
						echo "<div style='width:".$BrcWid."cm;height:".$BrcHgt."cm'></div>";
						echo "</td>";
					}
				}
			?>
		</tr>
	</table>
	</div>
	<script>window.print()</script>
</body>
