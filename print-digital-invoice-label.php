<?php
include "framework/functions/dotmatrix.php";
include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";
include "framework/alert/alert.php";

$OrdNbr	= $_GET['ORD_NBR'];
$Lead	= $_GET['LEAD'];
$BrcList= $_GET['BRCLST'];
	
//Set label parameter here (in cm)
$ColNbr	= 1;
$RowNbr	= 1;

$MdaWid	= 19.5;
$MdaHgt	= 15.8;

$LblWid	= 9.5;
$LblHgt	= 5;

$BrcWid	= 5;
$BrcHgt	= 2.5;

$TopMgn	= 0.2;
$BotMgn	= 0.2;
$Header	= "Property of Champion";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<style type="text/css">
	.header{
		
	}
	.border-black {
		border-bottom: 1px solid #000000;
	}
	</style>
</head>
<body style='margin:0;padding:0;width:100%;font-family:arial'>
	<div style='text-align:center;width:100%;padding-top:0px'>
	<table style='width:<?php echo $MdaWid; ?>cm;margin-left:auto;margin-right:auto;page-break-after:always;border: 1px solid black;'>
		<tr style='height:<?php echo $LblHgt; ?>cm' valign='top'>
			<?php
				$count	= 1;
				$line	= 1;
				$item	= 0;
				//Print leading spaces
				for($q=1;$q<=$Lead;$q++){
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
				
				
				$query="SELECT 
					ORD_DET_NBR,
					DET.ORD_NBR,
					DET_TTL,
					GROUP_CONCAT(PRN_DIG_DESC) AS PRN_DIG_DESC,
					DET.PRN_DIG_PRC,
					ORD_Q,
					TOT_SUB, 
					DET.PRN_DIG_TYP
				FROM CMP.PRN_DIG_ORD_DET DET 
					LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
				WHERE ORD_NBR=".$OrdNbr." AND ORD_DET_NBR_PAR IS NULL AND DET.DEL_NBR=0 AND DET.ORD_NBR != 0 
				GROUP BY ORD_NBR
				ORDER BY 1";
				//echo "<pre>".$query;
				$result=mysql_query($query);
				while($row=mysql_fetch_array($result)){
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
					
					//Font Configuration
					$font='font-size:10px';
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
					?>
					<td style='font-size:14px;'>
						<table style='border: 1px solid red;'>
							<tr>
								<td class="border-black"><b>VM</b><td>
								<td class="border-black"><b>VISUAL MERCHANDISER</b><td>
							</tr>
							<tr>
								<td class="border-black"><b>SOS</b><td>
								<td class="border-black"><b>TBS SOLO SQUARE</b><td>
							</tr>
							<tr>
								<td class="border-black">14027<td>
								<td class="border-black">Alamat<td>
							</tr>
							<tr>
								<td class="border-black">03_B<td>
								<td class="border-black">SOLO SQUARE GF NO.3, JL. SLAMET RIYADI NO. 441-445<td>
							</tr>
							<tr>
								<td>ITEM<td>
								<td align="left"><?php echo $row['PRN_DIG_DESC'] ?><td>
							</tr>
						</table>
					</td>
					<td style='text-align:center;font-size:12px;'>
						<table>
							<tr>
								<td>VM<td>
								<td>VISUAL MERCHANDISER<td>
							</tr>
							<tr>
								<td>JSM<td>
								<td>TBS JAVA SUPERMAL SEMARANG<td>
							</tr>
							<tr>
								<td>14027<td>
								<td>Alamat<td>
							</tr>
							<tr>
								<td>03_B<td>
								<td>JAVA SUPERMALL LT. 1 RUANG 115A, JL. MT HARYONO 992-994<td>
							</tr>
							<tr>
								<td>ITEM<td>
								<td>SOLO SQUARE GF NO.3, JL. SLAMET RIYADI NO. 441-445<td>
							</tr>
						</table>
					</td>
					<!--
					echo "<td style='text-align:center;border: 1px solid red;'>";
						echo "<div style='float:left;width:33mm;'>";
							echo "<div align='left' style='font-size:11px;font-weight: bolder;width:100%;height:100%;padding-bottom:1px;margin-left:4px;'>Nama</div>";
							echo "<div align='left' style='".$font.";width:100%;height:10mm;margin:2px 0 0 4px;'>".$row['PRN_DIG_DESC']."</div>";
							echo "<div style='font-size:8px;width:100%;height:6mm;margin-left: 3px;text-align: left;'>".leadZero($row['ORD_DET_NBR'],14)."<br/>".parseDateOnly($row['CRT_TS'])."-".parseMonth($row['CRT_TS'])."-".parseYear($row['CRT_TS'])."</div>";
							echo "<div style='font-size:8px;width:100%;height:6mm;margin-left: 4px;text-align: left;'>".$row['INV_BCD']."<br>".leadZero($_SESSION['personNBR'],6)." ".date('d-m-Y  H:i:s')."</b></div>";
							echo "<div align='left' style='font-size:11px;font-weight: bolder;width:100%;height:100%;padding-bottom:1px;margin-left:4px;'>".$Header."</div>";
							echo "<div style='height:".$BotMgn."px'></div>";
						echo "</div>";
					echo "</td>";
						
					echo "<td style='text-align:center'>";
						echo "<div style='float:left;width:33mm;'>";
							echo "<div align='left' style='".$font.";width:100%;height:10mm;margin:2px 0 0 4px;'>".$row['DET_TTL']." ".$row['PRN_DIG_DESC']."</div>";
							echo "<div style='font-size:8px;width:100%;height:6mm;margin-left: 3px;text-align: left;'>".leadZero($row['ORD_DET_NBR'],14)."<br/>".parseDateOnly($row['CRT_TS'])."-".parseMonth($row['CRT_TS'])."-".parseYear($row['CRT_TS'])."</div>";
							echo "<div style='font-size:8px;width:100%;height:6mm;margin-left: 4px;text-align: left;'>".$row['INV_BCD']."<br>".leadZero($_SESSION['personNBR'],6)." ".date('d-m-Y  H:i:s')."</b></div>";
							echo "<div align='left' style='font-size:11px;font-weight: bolder;width:100%;height:100%;padding-bottom:1px;margin-left:4px;'>".$Header."</div>";
							echo "<div style='height:".$BotMgn."px'></div>";
						echo "</div>";
					echo "</td>";
					--.
					<?php
						$count++;
						$item++;
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
	<script>//window.print()</script>
</body>
