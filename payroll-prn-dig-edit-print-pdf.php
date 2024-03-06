<?php
require_once "framework/database/connect.php";
require_once "framework/dompdf/dompdf_config.inc.php";
require_once "framework/functions/default.php";
require_once "framework/functions/dotmatrix.php";
require_once "framework/functions/komisi.php";

$companyNumber 	= $_GET['CONBR'];
$personNumber 	= $_GET['PRSN_NBR'];
$payrollDate	= $_GET['PYMT_DTE'];

//Get Data Payroll
$query="SELECT 
	PAY.PRSN_NBR, 
	NAME, 
	EMAIL, 
	PYMT_DTE,
	PYMT_DAYS,
	BASE_AMT * PYMT_DAYS AS PAY_BASE,
	PEER_RWD,
	PEER_RWD_F,
	BASE_CNT,
	BASE_TOT,
	ADD_AMT * PYMT_DAYS AS PAY_ADD,
	ADD_CNT,ADD_TOT,OT_AMT AS PAY_OT,
	OT_CNT,
	OT_TOT,
	COALESCE(CONTRB_AMT, 0) CONTRB_AMT,
	MISC_AMT,
	MISC_CNT,
	CMSN_AMT,
	MISC_TOT,
	BON_ATT_AMT,
	BON_WK_AMT,
	BON_PCT,
	BON_MO_AMT,
	PPAY.BON_MULT,
	PPAY.DED_DEF AS DED_DEF_PRSN,
	CRDT_WK,
	DEBT_WK AS DED_DEF,
	DEBT_MO,
	PAY_AMT,
	CRDT_AMT,
	BON_SNG_AMT,
	DED_SNG_AMT,
	BNK_ACCT_NBR,
	COALESCE(PPAY.BONUS, 0) BONUS,
	COALESCE(PPAY.PAY_MISC, 0) PAY_MISC,
	PAY.PAY_HLD_AMT,
	PAY.PAY_HLD_F,
	PAY.PAY_HLD_PD_AMT,
	PAY.PAY_HLD_PD_F,
	TOT_DIST,
	REM_TYPE,
	AUTH_TRVL_AMT,
	STY_CNT,
	STY_TOT_AMT,
	DED_DESC,
	DED_AMT,
	RPRMN_AMT,
	RPRMN_NBR,
	DSBRS_CRDT,
	PAY_MISC_AMT,
	PAY_ADD_DESC,
	PAY_ADD_AMT,
	PAY_RWD_AMT,
	INS_AMT_PRSN
FROM PAY.PAYROLL PAY 
	INNER JOIN CMP.PEOPLE PPL ON PAY.PRSN_NBR = PPL.PRSN_NBR 
	LEFT JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR 
WHERE (PAY.DEL_NBR = 0 OR PAY.DEL_NBR IS NULL) 
	AND PAY.PRSN_NBR = ".$personNumber." 
	AND DATE (PYMT_DTE) >= ('".$payrollDate."' - INTERVAL 7 DAY) 
	AND (DATE (PYMT_DTE) <= '".$payrollDate."')";
$result=mysql_query($query);
$row=mysql_fetch_array($result);
	
//Get Data People
$sql="SELECT 
	PPL.CO_NBR AS PPL_CO_NBR,
	IFNULL(PPAY.PAY_BASE, 0) PAY_BASE,
	IFNULL(PPAY.PAY_ADD, 0) PAY_ADD,
	IFNULL(PPAY.PAY_OT, 0) PAY_OT,
	IFNULL(PPAY.PAY_CONTRB, 0) PAY_CONTRB,
	IFNULL(PPAY.PAY_MISC, 0) PAY_MISC,
	IFNULL(PPAY.DED_DEF, 0) DED_DEF,
	IFNULL(PPAY.BONUS, 0) BONUS,
	PPAY.BON_MULT,
	COALESCE((
		SELECT (
			SUM(CRDT_AMT) - (SELECT SUM(DEBT_MO) FROM PAY.PAYROLL WHERE PRSN_NBR = ". $personNumber .")
		)
		FROM PAY.EMPL_CRDT
		WHERE PRSN_NBR = ". $personNumber ." AND PYMT_DTE <= '". $payrollDate ."' AND CRDT_APV_FIN=1 AND CRDT_APV=1
	), 0) AV_CRD
FROM PAY.PEOPLE PPAY
	LEFT JOIN CMP.PEOPLE PPL ON PPAY.PRSN_NBR = PPL.PRSN_NBR
WHERE PPAY.PRSN_NBR = ".$personNumber;
$results = mysql_query($sql);
$people = mysql_fetch_array($results);
$CoNbr	= $people['PPL_CO_NBR'];

//Get Data Company
$query="SELECT 
	NAME,
	ADDRESS,
	CITY_NM, 
	ZIP,
	PHONE, 
	EMAIL 
FROM CMP.COMPANY COM 
LEFT OUTER JOIN CITY CTY ON CTY.CITY_ID=COM.CITY_ID 
WHERE CO_NBR = ".$CoNbr;
$result=mysql_query($query);
$company=mysql_fetch_array($result);
if($CoNbr=='271'){
	$ComPhone="(0274) 566936";
}else if($CoNbr=='1002'){
	$ComPhone="(0274) 6698111";
}else{
	$ComPhone="(0274) 586866";
}
ob_start();
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
		<table width="100%" border=0>
			<tbody>
				<tr>
					<td width="59%" class="text-left">
						<table width="100%" border=0>
							<tbody>
								<tr>
									<td class="text-left">Jumlah hari masuk kerja</td>
									<td class="text-left" colspan="5">: <?php echo $row['BASE_CNT']; ?> hari dari total <?php echo $row['PYMT_DAYS']; ?> hari periode ini</td>
								</tr>
								<tr>
									<td class="text-left">Jumlah Setengah Hari</td>
									<td class="text-left" colspan="5">: <?php echo $row_half['DAY_UPNORMAL']; ?></td>
								</tr>
								<tr>
									<td width="35%" class="text-left">Gaji pokok</td>
									<td width="20%" class="text-left">: Rp. <?php echo number_format($row['PAY_BASE'] / $row['PYMT_DAYS'],0,",","."); ?></td>
									<td width="0.5%" class="text-center"> x </td>
									<td width="14.5%" class="text-right"><?php echo $row['BASE_CNT']; ?> hari&nbsp;</td>
									<td width="10%" class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td width="20%" class="text-right"><?php echo number_format($row['BASE_TOT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-left">Bonus bulan ini</td>
									<td class="text-left" colspan="3">: <?php echo $row['BON_PCT']; ?> % </td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['BON_MO_AMT'],0,",","."); ?></td>
								</tr>
								
								<tr>
									<td class="text-left">Komisi</td>
									<td class="text-left" colspan="3">:</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['CMSN_AMT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-left">Gaji lembur</td>
									<td class="text-left">: Rp. <?php echo number_format($row['PAY_OT'],0,",","."); ?></td>
									<td class="text-center"> x </td>
									<td class="text-right"><?php echo $row['OT_CNT']; ?> jam&nbsp;</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['OT_TOT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-left">Gaji Tambahan</td>
									<td class="text-left">: Rp. <?php echo number_format($row['PAY_ADD'] / $row['PYMT_DAYS'],0,",","."); ?></td>
									<td class="text-center"> x </td>
									<td class="text-right"><?php echo $row['ADD_CNT']; ?> hari&nbsp;</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['ADD_TOT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-left">Transportasi</td>
									<td class="text-left">: Rp. <?php echo number_format($row['AUTH_TRVL_AMT'] / $row['TOT_DIST'],0,",","."); ?></td>
									<td class="text-center"> x </td>
									<td class="text-right"><?php echo $row['TOT_DIST']; ?> km&nbsp;</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['AUTH_TRVL_AMT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-left">Menginap</td>
									<td class="text-left">: Rp. <?php echo number_format($row['STY_TOT_AMT'] / $row['STY_CNT'],0,",","."); ?></td>
									<td class="text-center"> x </td>
									<td class="text-right"><?php echo $row['STY_CNT']; ?>&nbsp;</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['STY_TOT_AMT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-left">Jasa Pemasangan</td>
									<td class="text-left">:</td>
									<td class="text-left">&nbsp;</td>
									<td class="text-left">&nbsp;</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['PAY_MISC_AMT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td colspan="6">&nbsp;</td>
								</tr>
								<tr>
									<td class="text-right" colspan="4"><b>Jumlah</b></td>
									<td class="text-left"><b>=&nbsp;&nbsp;&nbsp;Rp.</b></td>
									<td class="text-right"><b><?php echo number_format($row['BASE_TOT'] + $row['BON_MO_AMT']+ $row['CMSN_AMT'] + $row['OT_TOT'] + $row['ADD_TOT'] + $row['AUTH_TRVL_AMT'] + $row['STY_TOT_AMT'] + $row['PAY_MISC_AMT'],0,",","."); ?></b></td>
								</tr>
								<tr>
									<td class="text-right" colspan="4"> Gaji Kontribusi</td>
									<td class="text-left">= - Rp.</td>
									<td class="text-right"><?php echo number_format($row['CONTRB_AMT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-right" colspan="4">Bon</td>
									<td class="text-left">= - Rp.</td>
									<td class="text-right"><?php echo number_format($row['DEBT_MO'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-right" colspan="4">Bonus</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['BON_SNG_AMT'],0,",","."); ?></td>
								</tr>
								<?php if ($row['PAY_RWD_AMT'] > 0){ ?>
								<tr>
									<td class="text-right" colspan="4">Marketing Performance Reward</td>
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
												<td class="text-right" colspan="4"><?php echo substr($PAY_ADD_DESC, 0, 22); ?></td>
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
													<td class="text-right" colspan="4">Potongan <?php echo $dedDesc[$i]; ?></td>
													<td class="text-left">= - Rp.</td>
													<td class="text-right"><?php echo number_format($dedAmt[$i],0,",","."); ?></td>
												</tr>
											<?php
											}
										}
								}else{
								?>
									<tr>
										<td class="text-right" colspan="4">Potongan</td>
										<td class="text-left">= - Rp.</td>
										<td class="text-right"><?php echo number_format($row['DED_SNG_AMT'],0,",","."); ?></td>
									</tr>
								<?php } ?>
								
								<tr>
									<td class="text-right" colspan="4">Peer to peer penalty</td>
									<td class="text-left">= - Rp.</td>
									<td class="text-right"><?php echo number_format($row['PEER_RWD'],0,",","."); ?></td>
								</tr>
								
								<?php if ($row['PAY_HLD_F']=='1'){ ?>
								<tr>
									<td class="text-right" colspan="4">Gaji ditahan</td>
									<td class="text-left">= - Rp.</td>
									<td class="text-right"><?php echo number_format($row['PAY_HLD_AMT'],0,",","."); ?></td>
								</tr>
								<?php } else if ($row['PAY_HLD_PD_F']=='1'){ ?>
								<tr>
									<td class="text-right" colspan="4">Gaji diberikan</td>
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
									<td class="text-right" colspan="4"><?php echo $rowPr['RPRMN_DESC']; ?></td>
									<td class="text-left">: Rp.</td>
									<td class="text-right"><?php echo number_format($row['RPRMN_AMT'],0,",","."); ?></td>
								</tr>
								<?php } ?>
								
								<?php if ($row['INS_AMT_PRSN'] > 0){ ?>
								<tr>
									<td class="text-right" colspan="4">BPJS</td>
									<td class="text-left">= - Rp.</td>
									<td class="text-right"><?php echo number_format($row['INS_AMT_PRSN'],0,",","."); ?></td>
								</tr>
								<?php } ?>
								
								<tr>
									<td class="text-right" colspan="4">Pencairan Bon</td>
									<td class="text-left">=&nbsp;&nbsp;&nbsp;Rp.</td>
									<td class="text-right"><?php echo number_format($row['PAY_HLD_AMT'],0,",","."); ?></td>
								</tr>
								<tr>
									<td class="text-right" colspan="4"><b>Total</b></td>
									<td class="text-left"><b>=&nbsp;&nbsp;&nbsp;Rp.</b></td>
									<td class="text-right"><b><?php echo number_format($row['PAY_AMT'],0,",","."); ?></b></td>
								</tr>
							</tbody>
						</table>
					</td>
					<td width="5%">&nbsp;</td>
					<td width="40%" class="text-left" valign="top">
						<table width="100%" border=0>
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
<?php
$html = ob_get_clean();

ob_end_clean();

//790, 250 old size

$pdf = new DOMPDF();
$pdf->load_html($html);
$pdf->set_paper('A5', 'landscape');
$pdf->render();
$pdf->stream("Payroll-".$orderNumber.".pdf", array("Attachment" => false));
?>