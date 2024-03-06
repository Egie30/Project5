<?php
require_once "framework/database/connect.php";
require_once "framework/dompdf/dompdf_config.inc.php";
require_once "framework/functions/default.php";
require_once "framework/functions/dotmatrix.php";
require_once "framework/functions/komisi.php";
?>
<!DOCTYPE html>
<html>
<head>
<style type="text/css">
@page {
	margin: 10px;
}
body {
	font-family: Calibri, sans-serif;
	margin: 100px .0cm;
	text-align: left;
	font-size:12px;
	margin-bottom: 10px;
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
	margin-top: 5px;
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
	bottom: 70px;
	border-top:1px solid #000000;
}

.border-gold {
	border-color: rgb(92,51,23);
}

.border-black {
	border-color: #000000;
	border-bottom:1px solid;
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
	<header id="header" class="border-black">
		<table width="100%" border=0><?php //echo $sql; ?>
			<tr>
				<td width="30%" class="text-left"><?php echo ucfirst($company['NAME']); ?></td>
				<td width="40%" class="text-center"><b>PERINCIAN GAJI KARYAWAN</b></td>
				<td width="30%" class="text-right"> Nomor Induk : <?php echo leadZero($personNumber,6); ?></td>
			</tr>
			<tr>
				<td colspan="2" class="text-left"><?php echo ucfirst($company['ADDRESS']); ?> <?php echo ucfirst($company['CITY_NM']); ?> <?php echo ucfirst($company['ZIP']); ?></td>
				<td class="text-right">Tanggal Gajian : <?php echo $row['PYMT_DTE']; ?></td>
			</tr>
			<tr>
				<td colspan="2" class="text-left">Telp <?php echo $ComPhone; ?>, E-Mail: <?php echo $company['EMAIL']; ?></td>
				<td class="text-right">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" class="text-left">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" class="text-left">Nama Karyawan: <?php echo $row['NAME']; ?></td>
				<td class="text-right">Nomor Rekening: <?php echo $row['BNK_ACCT_NBR']; ?></td>
			</tr>
		</table>
	</header>
	<?php
	$beginDateLast	= date('Y-m', strtotime('-1 month', strtotime($_GET['PYMT_DTE'])));
	$queryMax 		= "SELECT 
			PYMT_DTE, 
			COUNT(PYMT_DTE) AS JUMLAH 
		FROM PAY.PAYROLL 
		WHERE LEFT(PYMT_DTE,7) = '".$beginDateLast."' 
		GROUP BY PYMT_DTE 
		ORDER BY COUNT(PYMT_DTE) DESC LIMIT 1";
	$resultMax 		= mysql_query($queryMax);
	$rowMax 		= mysql_fetch_array($resultMax);
	$beginDate 		= $rowMax['PYMT_DTE'];
	$endDate 		= date('Y-m-d', strtotime('-1 day', strtotime($_GET['PYMT_DTE'])));
	
	$query_half 	= "SELECT 
		DATE_TS,
		PRSN_NBR,
		CO_NBR,
		CLOK_IN_TS,
		CLOK_OT_TS,
		SUM(CASE 
			WHEN (CLOK_IN_TS IS NULL OR CLOK_OT_TS IS NULL) THEN 1 
			ELSE 0
		END) AS DAY_UPNORMAL
	FROM(
		SELECT 
			CLOK_NBR, 
			PPL.CO_NBR,
			PPL.PRSN_NBR,
			DATE(CLOK_IN_TS) AS DATE_TS, 
			MAC.CLOK_IN_TS,
			MAC.CLOK_OT_TS
		FROM PAY.MACH_CLOK MAC 
			LEFT OUTER JOIN CMP.PEOPLE PPL ON MAC.PRSN_NBR=PPL.PRSN_NBR 
			LEFT OUTER JOIN PAY.PEOPLE PPAY ON PPAY.PRSN_NBR=PPL.PRSN_NBR
		WHERE DATE(CLOK_IN_TS) 
			AND DATE(CLOK_IN_TS) >= '".$beginDate."' 
			AND DATE(CLOK_IN_TS) <= '".$endDate."'
			AND MAC.PRSN_NBR = '".$_GET['PRSN_NBR']."'
			GROUP BY PRSN_NBR,DATE(CLOK_IN_TS)
	) WORK";
	$result_half 	= mysql_query($query_half);
	$row_half 		= mysql_fetch_array($result_half);
	?>
	<section id="body">
		<table width="100%" border=1>
			<tbody>
				<tr>
					<td width="59%" class="text-left">
						<table width="100%" border=1>
							<tbody>
								<tr>
									<td class="text-left">Jumlah hari masuk kerja</td>
									<td class="text-left" colspan="4">: <?php echo $row['BASE_CNT']; ?> hari dari total <?php echo $row['PYMT_DAYS']; ?> hari periode ini</td>
								</tr>
								<tr>
									<td class="text-left">Jumlah Setengah Hari</td>
									<td class="text-left" colspan="4">: <?php echo $row_half['DAY_UPNORMAL']; ?></td>
								</tr>
								<tr>
									<td width="35%" class="text-left">Gaji pokok</td>
									<td width="20%" class="text-left">: Rp. <?php echo number_format($row['PAY_BASE'] / $row['PYMT_DAYS'],0,",","."); ?></td>
									<td width="15%" class="text-left"> x <?php echo $row['BASE_CNT']; ?> hari</td>
									<td width="2%" class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td width="27%" class="text-right"><?php echo number_format($row['BASE_TOT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-left">Bonus bulan ini</td>
									<td class="text-left" colspan="2">: <?php echo $row['BON_PCT']; ?> % </td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['BON_MO_AMT'],0,",","."); ?></td>
								</tr>
								
								<tr>
									<td class="text-left">Komisi</td>
									<td class="text-left" colspan="2">:</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['CMSN_AMT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-left">Gaji lembur</td>
									<td class="text-left">: Rp. <?php echo number_format($row['PAY_OT'],0,",","."); ?></td>
									<td class="text-left"> x <?php echo $row['OT_CNT']; ?> jam</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['OT_TOT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-left">Gaji Tambahan</td>
									<td class="text-left">: Rp. <?php echo number_format($row['PAY_ADD'] / $row['PYMT_DAYS'],0,",","."); ?></td>
									<td class="text-left"> x <?php echo $row['ADD_CNT']; ?> hari</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['ADD_TOT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-left">Transportasi</td>
									<td class="text-left">: Rp. <?php echo number_format($row['AUTH_TRVL_AMT'] / $row['TOT_DIST'],0,",","."); ?></td>
									<td class="text-left"> x <?php echo $row['TOT_DIST']; ?> km</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['AUTH_TRVL_AMT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-left">Menginap</td>
									<td class="text-left">: Rp. <?php echo number_format($row['STY_TOT_AMT'] / $row['STY_CNT'],0,",","."); ?></td>
									<td class="text-left"> x <?php echo $row['STY_CNT']; ?></td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['STY_TOT_AMT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-left">Jasa Pemasangan</td>
									<td class="text-left">:</td>
									<td class="text-left">&nbsp;</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['PAY_MISC_AMT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td colspan="5">&nbsp;</td>
								</tr>
								<tr>
									<td class="text-right" colspan="3"><b>Jumlah</b></td>
									<td class="text-left"><b>=&nbsp;&nbsp;&nbsp;Rp.</b></td>
									<td class="text-right"><b><?php echo number_format($row['BASE_TOT'] + $row['BON_MO_AMT']+ $row['CMSN_AMT'] + $row['OT_TOT'] + $row['ADD_TOT'] + $row['AUTH_TRVL_AMT'] + $row['STY_TOT_AMT'] + $row['PAY_MISC_AMT'],0,",","."); ?></b></td>
								</tr>
								<tr>
									<td class="text-right" colspan="3"> Gaji Kontribusi</td>
									<td class="text-left">= - Rp.</td>
									<td class="text-right"><?php echo number_format($row['CONTRB_AMT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-right" colspan="3">Bon</td>
									<td class="text-left">= - Rp.</td>
									<td class="text-right"><?php echo number_format($row['DEBT_MO'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-right" colspan="3">Bonus</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['BON_SNG_AMT'],0,",","."); ?></td>
								</tr>
								<?php if ($row['PAY_RWD_AMT'] > 0){ ?>
								<tr>
									<td class="text-right" colspan="3">Marketing Performance Reward</td>
									<td class="text-left">: Rp.</td>
									<td class="text-right"><?php echo number_format($row['PAY_RWD_AMT'],0,",","."); ?></td>
								</tr>
								<?php } ?>
								
								<?php 
								if ($row['PAY_ADD_DESC']!=''){
									$PayAddDesc = explode("+", $row['PAY_ADD_DESC']);
									$PayAddAmt  = explode("+", $row['PAY_ADD_AMT']);

									for ($i=0; $i <count($PayAddDesc); $i++) {
										if ($PayAddDesc[$i] =="TVP"){
											$PAY_ADD_DESC = "Extra "."Travel Pemasangan";
										}else{
											$PAY_ADD_DESC = "Extra ".$PayAddDesc[$i];
										}
										if ($PayAddDesc[$i] !='Kosong' || $PayAddDesc[$i]!=''){
											?>
											<tr>
												<td class="text-right" colspan="3"><?php echo substr($PAY_ADD_DESC, 0, 22); ?></td>
												<td class="text-left">: Rp.</td>
												<td class="text-right"><?php echo number_format($PayAddAmt[$i],0,",","."); ?></td>
											</tr>
										<?php
										}
									}
								}
								?>
								
								<?php 
								if ($row['DED_DESC']!=''){
										$dedDesc = explode("+", $row['DED_DESC']);
										$dedAmt  = explode("+", $row['DED_AMT']);

										for($i=0;$i<count($dedDesc);$i++){
											if ($dedDesc[$i]!='Kosong'){
												?>
												<tr>
													<td class="text-right" colspan="3">Potongan <?php echo $dedDesc[$i]; ?></td>
													<td class="text-left">= - Rp.</td>
													<td class="text-right"><?php echo number_format($dedAmt[$i],0,",","."); ?></td>
												</tr>
											<?php
											}
										}
								}else{
								?>
									<tr>
										<td class="text-right" colspan="3">Potongan</td>
										<td class="text-left">= - Rp.</td>
										<td class="text-right"><?php echo number_format($row['DED_SNG_AMT'],0,",","."); ?></td>
									</tr>
								<?php } ?>
								
								<tr>
									<td class="text-right" colspan="3">Peer to peer penalty</td>
									<td class="text-left">= - Rp.</td>
									<td class="text-right"><?php echo number_format($row['PEER_RWD'],0,",","."); ?></td>
								</tr>
								
								<?php if ($row['PAY_HLD_F']=='1'){ ?>
								<tr>
									<td class="text-right" colspan="3">Gaji ditahan</td>
									<td class="text-left">= - Rp.</td>
									<td class="text-right"><?php echo number_format($row['PAY_HLD_AMT'],0,",","."); ?></td>
								</tr>
								<?php } else if ($row['PAY_HLD_PD_F']=='1'){ ?>
								<tr>
									<td class="text-right" colspan="3">Gaji diberikan</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['PAY_HLD_PD_AMT'],0,",","."); ?></td>
								</tr>
								<?php } ?>
								
								<?php
								if ($row['RPRMN_NBR']!=0){
									if ($row['LTR_RPRMN_AMT'] 	  == 0){$ltrrPrmnAmt = 0;}else{$ltrrPrmnAmt = $row['LTR_RPRMN_AMT'];}
									$queryPr = "SELECT RPRMN_DESC FROM PAY.RPRMN WHERE RPRMN_NBR=".$row['RPRMN_NBR'];
									$resultPr= mysql_query($queryPr);
									$rowPr   = mysql_fetch_array($resultPr);
								?>
								<tr>
									<td class="text-right" colspan="3"><?php echo $rowPr['RPRMN_DESC']; ?></td>
									<td class="text-left">: Rp.</td>
									<td class="text-right"><?php echo number_format($row['RPRMN_AMT'],0,",","."); ?></td>
								</tr>
								<?php } ?>
								
								<tr>
									<td class="text-right" colspan="3">Pencairan Bon</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['PAY_HLD_AMT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-right" colspan="3"><b>Total</b></td>
									<td class="text-left"><b>=&nbsp;&nbsp;&nbsp;Rp.</b></td>
									<td class="text-right"><b><?php echo number_format($row['PAY_AMT'],0,",","."); ?></b></td>
								</tr>
							</tbody>
						</table>
					</td>
					<td width="1%">&nbsp;</td>
					<td width="40%" class="text-left" valign="top">
						<table width="100%" border=1>
							<tbody>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td width="60%" class="text-left">Gaji Pokok Per Bulan</td>
									<td width="10%" class="text-left">: Rp.</td>
									<td width="30%" class="text-right"><?php echo number_format($people['PAY_BASE'],0,",","."); ?></td>
								</tr>
								<tr>
									<td width="60%" class="text-left">Gaji Tambahan</td>
									<td width="10%" class="text-left">: Rp.</td>
									<td width="30%" class="text-right"><?php echo number_format($people['PAY_ADD'],0,",","."); ?></td>
								</tr>
								<tr>
									<td width="60%" class="text-left">Gaji Lembur</td>
									<td width="10%" class="text-left">: Rp.</td>
									<td width="30%" class="text-right"><?php echo number_format($people['PAY_OT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td width="60%" class="text-left">Gaji Lain-lain</td>
									<td width="10%" class="text-left">: Rp.</td>
									<td width="30%" class="text-right"><?php echo number_format($people['PAY_MISC'],0,",","."); ?></td>
								</tr>
								<tr>
									<td width="60%" class="text-left">Gaji Kontribusi</td>
									<td width="10%" class="text-left">: Rp.</td>
									<td width="30%" class="text-right"><?php echo number_format($row['CONTRB_AMT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td width="60%" class="text-left">Potongan Bon/Bulan</td>
									<td width="10%" class="text-left">: Rp.</td>
									<td width="30%" class="text-right"><?php echo number_format($row['DED_DEF_PRSN'],0,",","."); ?></td>
								</tr>
								<tr>
									<td width="60%" class="text-left">Bonus</td>
									<td width="10%" class="text-left">: Rp.</td>
									<td width="30%" class="text-right"><?php echo number_format($people['BONUS'],0,",","."); ?></td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td width="60%" class="text-left">Kelipatan Bonus</td>
									<td width="10%" class="text-left">: Rp.</td>
									<td width="30%" class="text-right"><?php echo number_format($people['BON_MULT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td width="60%" class="text-left">Sisa Bon</td>
									<td width="10%" class="text-left">: Rp.</td>
									<td width="30%" class="text-right"><?php echo number_format($people['AV_CRD'],0,",","."); ?></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</section>

	<footer id="footer">
		<table width="100%" border=0>
			<tbody>
				<tr>
					<td width="70%" class="text-left" valign="bottom">
						Terima kasih atas kinerja yang anda berikan, dan mari kita tingkatkan produktifitas dan kualitas untuk kemajuan bersama.
					</td>
					<td class="text-center">Penerima <br><br><br><br>(<?php echo dispNameScreen(shortName($row['NAME'])); ?>)</td>
				</tr>
			</tbody>
		</table>
	</footer>
</body>
</html>