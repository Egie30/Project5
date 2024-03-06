<?php
include "framework/functions/dotmatrix.php";
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";

	$OrdNbr=$_GET['ORD_NBR'];
	$Lead=$_GET['LEAD'];
	$BrcList=$_GET['BRCLST'];
	
	//Set label parameter here (in cm)
	$ColNbr=1;
	$RowNbr=1;
	$LblWid=3.4;
	$MdaWid=10.7;
	$BrcWid=2.5;
	$BrcHgt=1.3;
	$TopMgn=0;
	$BotMgn=0;
	//Mozilla/Firefox
	$LblHgt=2.7;
	//Chrome
	//$LblHgt=1.6;
	//Real Measurement
	//$LblHgt=1.825625;

	$Header="Property of Champion";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<style type="text/css">
.bcd90{
float:left;
margin:0px;
width:0.5cm;
margin:7mm 0 0 -3mm;
transform:rotate(90deg);
-ms-transform:rotate(90deg); /* IE 9 */
-webkit-transform:rotate(90deg); /* Safari and Chrome */
} 
.rotasi{
-webkit-transform: rotate(90deg);
-moz-transform: rotate(90deg);
}
</style>

</head>

<body style='margin:0;padding:0;width:100%;font-family:arial'>
	<div style='text-align:center;width:100%;padding-top:0px'>
	<table style='width:<?php echo $MdaWid; ?>cm;background-color:#dddddd;margin-left:auto;margin-right:auto;page-break-after:always'>
		<tr style='height:<?php echo $LblHgt; ?>cm' valign='top'>
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
				
				
				$query="SELECT ORD_DET_NBR,INV.INV_BCD,INV.NAME,ORD_Q,PRC,DET.CRT_TS AS CRT_TS
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
					
					//Font Configuration 
					if((strlen($row['NAME'])>=30)AND(strlen($row['NAME'])<=35)){
											$font='font-size:12px';
										} 
					else if(strlen($row['NAME'])>=35){
											$font='font-size:10px';
										}
					else{$font='font-size:15px';}
					
					
					
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
						//echo "<div style='font-size:8px;width:100%;padding-bottom:1px'>".$Header."</div>";
						echo "<div style='float:left;height:27mm;width:16mm;'><img  class='bcd90' src='framework/barcode/retail-barcode.php?STRING=P".$row['ORD_DET_NBR']."' style='width:".$BrcWid."cm;height:".$BrcHgt."cm'></div>";
						echo "<div style='float:left;width:33mm;'>";
						echo "<div align='left' style='".$font.";width:100%;height:10mm;margin:2px 0 0 4px;'>".$row['NAME']."</div>";
						echo "<div style='font-size:8px;width:100%;height:6mm;margin-left: 3px;text-align: left;'>".leadZero($row['ORD_DET_NBR'],14)."<br/>".parseDateOnly($row['CRT_TS'])."-".parseMonth($row['CRT_TS'])."-".parseYear($row['CRT_TS'])."</div>";
						echo "<div style='font-size:8px;width:100%;height:6mm;margin-left: 4px;text-align: left;'>".$row['INV_BCD']."<br>".leadZero($_SESSION['personNBR'],6)." ".date('d-m-Y  H:i:s')."</b></div>";
						echo "<div align='left' style='font-size:11px;font-weight: bolder;width:100%;height:100%;padding-bottom:1px;margin-left:4px;'>".$Header."</div>";
						echo "<div style='height:".$BotMgn."px'></div>";
						echo "</div></td>";
						
						echo "<td style='text-align:center'>";
						//echo "<div style='font-size:8px;width:100%;padding-bottom:1px'>".$Header."</div>";
						echo "<div style='float:left;height:27mm;width:16mm;'><img  class='bcd90' src='framework/barcode/retail-barcode.php?STRING=P".$row['ORD_DET_NBR']."' style='width:".$BrcWid."cm;height:".$BrcHgt."cm'></div>";
						echo "<div style='float:left;width:33mm;'>";
						echo "<div align='left' style='".$font.";width:100%;height:10mm;margin:2px 0 0 4px;'>".$row['NAME']."</div>";
						echo "<div style='font-size:8px;width:100%;height:6mm;margin-left: 3px;text-align: left;'>".leadZero($row['ORD_DET_NBR'],14)."<br/>".parseDateOnly($row['CRT_TS'])."-".parseMonth($row['CRT_TS'])."-".parseYear($row['CRT_TS'])."</div>";
						echo "<div style='font-size:8px;width:100%;height:6mm;margin-left: 4px;text-align: left;'>".$row['INV_BCD']."<br>".leadZero($_SESSION['personNBR'],6)." ".date('d-m-Y  H:i:s')."</b></div>";
						echo "<div align='left' style='font-size:11px;font-weight: bolder;width:100%;height:100%;padding-bottom:1px;margin-left:4px;'>".$Header."</div>";
						echo "<div style='height:".$BotMgn."px'></div>";
						echo "</div></td>";
						
						
						$count++;
						$item++;
					}
				}
				
				
				$mod=$ColNbr-$item%$ColNbr;
				if($mod<$ColNbr){
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
