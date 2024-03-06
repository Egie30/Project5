<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	include "framework/slack/slack.php";
	
	$PymtDte 	= $_GET['PYMT_DTE'];
	$executive 	= getSecurity($_SESSION['userID'],"Executive");
	$finance 	= getSecurity($_SESSION['userID'],"Finance");
	$slack 		= false;
	$TimeOffNbr = $_GET['TM_OFF_NBR'];
	$PrsnNbr	= $_GET['PRSN_NBR'];
	$monthtm	= $_GET['TM_OFF_M'];
	$yeartm		= $_GET['TM_OFF_Y'];

	$filter_date=str_replace("+"," ",$_GET['FLTR_DATE']);
	if ($filter_date!="") {
		$data	= explode(" ",$filter_date);
		$month	= $data[0];
		$year	= $data[1];
	}
	
	if ($_GET['PRSN_NBR']!=0){
		$PrsnNbr 	= $_GET['PRSN_NBR'];
	}else{
		if ($executive<1){
			$PrsnNbr 	= '';
		}else{
			$PrsnNbr 	= $_SESSION['personNBR'];
		}
	}

	if($cloud!=false){
		//Process changes here
		if($_POST['PRSN_NBR']!="")
		{
			$j=syncTable("EMPL_CRDT","PRSN_NBR,PYMT_DTE","PAY",$PAY,$local,$cloud);
			$PrsnNbr=$_POST['PRSN_NBR'];
			$PymtDte=$_POST['PYMT_DTE'];

			//Process add new
			$query="SELECT COUNT(*) AS CNT, CRDT_APV, CRDT_APV_FIN  FROM $PAY.EMPL_CRDT WHERE DEL_NBR=0 AND PRSN_NBR=".$PrsnNbr." AND PYMT_DTE='".$PymtDte."'";
			$result=mysql_query($query,$local,$cloud);
			$row=mysql_fetch_array($result);
			$CeateCek = $row['CNT'];
			if($row['CNT']==0)
			{
				$query="INSERT INTO $PAY.EMPL_CRDT (PRSN_NBR,PYMT_DTE, CRT_TS, CRT_NBR) VALUES (".$PrsnNbr.",'".$PymtDte."',CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
				$result=mysql_query($query,$cloud);
				$query=str_replace($PAY,"PAY",$query);
				$result=mysql_query($query,$local);

			}
			
			//Take care of nulls
			if($_POST['PYMT_NBR']==""){$PymtNbr="0";}else{$PymtNbr=$_POST['PYMT_NBR'];}		
			if($_POST['CRDT_PRNC']==""){$CrdtPrnc="0";}else{$CrdtPrnc=round($_POST['CRDT_PRNC']);}
			if($_POST['CRDT_AMT']==""){$CrdtAmt="0";}else{$CrdtAmt=$_POST['CRDT_AMT'];}		
			if($_POST['CRDT_DEF']==""){$CrdtDef="0";}else{$CrdtDef=$_POST['CRDT_DEF'];}
			if($_POST['DED_DEF']==""){$CrdtDef="0";}else{$CrdtDef=round($_POST['DED_DEF']);}
			if($_POST['CRDT_F']=="on"){$CrdtF=1;}else{$CrdtF=0;}
			if($_POST['CRDT_RSN']==""){$crdtRsn="NULL";}else{$crdtRsn="'".$_POST['CRDT_RSN']."'";}
			if($_POST['CRDT_APV_FIN']=="on"){$crdtApvFin=1;$crdtApvFinNbr=$_SESSION['personNBR'];}else{$crdtApvFin=0;$crdtApvFinNbr=0;}
			if($_POST['CRDT_APV']=="on"){$crdtApv=1;$crdtApvNbr=$_SESSION['personNBR'];}else{$crdtApv=0;$crdtApvNbr=0;}
			if($_POST['DSBRS_TYP']==''){$DsbrsTyp='NULL';}else{$DsbrsTyp=$_POST['DSBRS_TYP'];}

			if ($row['CRDT_APV_FIN']==0){
				$Upd_flag = "CRDT_APV_FIN = ".$crdtApvFin.",
						CRDT_APV_FIN_NBR=".$crdtApvFinNbr.",";
			} elseif ($row['CRDT_APV'] == 0){
				$Upd_flag = "CRDT_APV = ".$crdtApv.",
							 CRDT_APV_NBR = ".$crdtApvNbr.",";
			}
		
			$query="UPDATE $PAY.EMPL_CRDT
	   					SET	CRDT_AMT=".$CrdtAmt.",
						PYMT_NBR=".$PymtNbr.",
						CRDT_PRNC=".$CrdtPrnc.",
						CRDT_F = ".$CrdtF.",
						CRDT_RSN =".$crdtRsn.",
						DSBRS_TYP='".$DsbrsTyp."',
						".$Upd_flag."
						UPD_NBR=".$_SESSION['personNBR'].",
						UPD_TS =CURRENT_TIMESTAMP
					WHERE PRSN_NBR=".$PrsnNbr."
						AND PYMT_DTE='".$PymtDte."'";
			// ECHO $query;
			$result=mysql_query($query,$cloud);
			$query=str_replace($PAY,"PAY",$query);
			$result=mysql_query($query,$local);

		
			if($_POST['DED_DEF']==""){$DedDef="0";}else{$DedDef=$_POST['DED_DEF'];}
			
			if ($crdtApv==1 && $DsbrsTyp=='TRF'){
				$query="UPDATE $PAY.PEOPLE
					SET DED_DEF=".$DedDef.",
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE PRSN_NBR=".$PrsnNbr;
				
				//echo $query;
				
				$result=mysql_query($query,$cloud);
				$query=str_replace($PAY,"PAY",$query);
				$result=mysql_query($query,$local);	
			}

		}
		
		
		//checking employee credit
		$query_credit="SELECT (SUM(CRDT.CRDT_AMT)-(SELECT COALESCE(SUM(PAY.DEBT_MO),0) 
						FROM $PAY.PAYROLL PAY WHERE PAY.PRSN_NBR='".$PrsnNbr."' AND PAY.DEL_NBR=0)) REM_CRDT   
						FROM $PAY.EMPL_CRDT CRDT WHERE CRDT.PRSN_NBR='".$PrsnNbr."' AND CRDT.DEL_NBR=0 AND CRDT_APV=1";
		$result_credit=mysql_query($query_credit, $cloud);
		//$result_credit=mysql_query($query_credit, $local);
		$row_credit=mysql_fetch_array($result_credit);	
		$RemCrdt=$row_credit['REM_CRDT'];
		
		//echo $query_credit."<br /><br />";
		
		if($RemCrdt == '') { $RemCrdt = 0; } else { $RemCrdt = $row_credit['REM_CRDT']; } 

		
		$query_hire 	= "SELECT TIMESTAMPDIFF(MONTH, PPL.HIRE_DTE , CURRENT_DATE) AS HIRE_DTE FROM $CMP.PEOPLE PPL WHERE PRSN_NBR='".$PrsnNbr."'";
		$result_hire	= mysql_query($query_hire, $cloud);
		$row_hire		= mysql_fetch_array($result_hire);

		//echo $query_hire;
		
		$HireDte		= $row_hire['HIRE_DTE'];

		if($HireDte == '') { $HireDte = 0; } else { $HireDte = $row_hire['HIRE_DTE']; } 
		
		//end of checking employee credit
		
	}
	$PPlFinance = array(706,368);
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<link rel="stylesheet" href="framework/combobox/chosen.css">

<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>

<script type="text/javascript">
	window.addEvent('domready', function() {
	//Datepicker
	new CalendarEightysix('textbox-id');
	//Calendar
	new CalendarEightysix('block-element-id');
	});
	MooTools.lang.set('id-ID', 'Date', {
		months:    ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
		days:      ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
		dateOrder: ['date', 'month', 'year', '/']
	});
	MooTools.lang.setLanguage('id-ID');
</script>

<script type="text/javascript">
	function checkform()
	{
		if(document.getElementById('NAME').value=="")
		{
			window.scrollTo(0,0);
			document.getElementById('nameBlank').style.display='block';document.getElementById('fade').style.display='block';
			return false;
		}

		return true;
	}
</script>

	
<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />

	
</head>

<body>

<script>
	parent.document.getElementById('payrollDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='kas-bon.php?CO_NBR=<?php echo $CoNbr; ?>&DEL_L=<?php echo $PrsnNbr; ?>&PYMT_DTE=<?php echo $PymtDte; ?>';
		parent.document.getElementById('payrollDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>

<script>
	parent.document.getElementById('checkHireYes').onclick=
	function () { 
		parent.document.getElementById('checkHire').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>

<script>
	parent.document.getElementById('checkCreditYes').onclick=
	function () { 
		parent.document.getElementById('checkCredit').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>

<table class="submenu">
	<tr>
		<?php if (in_array($_SESSION['personNBR'], $PPlFinance) || $executive<1){ ?>
		<td class="submenu">
			<?php
					$query="SELECT PYMT_DTE
							FROM PAY.EMPL_CRDT
							WHERE PRSN_NBR='".$PrsnNbr."'
							AND MONTH(PYMT_DTE)= '".$month."'
							AND YEAR(PYMT_DTE) = '".$year."'
							AND DEL_NBR = 0
							ORDER BY 1 DESC
							LIMIT 0,12";

					
					$result=mysql_query($query, $local, $cloud);
					while($row=mysql_fetch_array($result))
					{
						echo "<a class='submenu' href='kasbontes.php?PRSN_NBR=".$PrsnNbr."&PYMT_DTE=".$row['PYMT_DTE']."&FLTR_DATE=".$_GET['FLTR_DATE']."'><div class='";
						if($PymtDte==$row['PYMT_DTE']){echo "arrow_box";}else{echo "leftsubmenu";}
						echo "'>".$row['PYMT_DTE']."</div></a>";
					}				
			?>	
		</td>
		<?php } ?>
		<td class="subcontent">

			<?php if(($executive==0)&&($PymtDte!=0)) { ?>
				<div class="toolbar-only">
				<p class="toolbar-left"><a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.document.getElementById('payrollDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class="fa fa-trash toolbar" style="cursor:pointer"></span></a></p>
				</div>
			<?php } ?>
		
		
			<?php
			
				if($PymtDte=="" || $PymtDte==0)
				{
					$query_cek="SELECT PPL.PRSN_NBR,PPL.NAME,PPL.POS_TYP,PPL.PAY_TYP,PP.PAY_BASE,PP.PAY_ADD,PP.PAY_OT,PP.PAY_MISC,PP.DED_DEF
							FROM CMP.PEOPLE PPL
							LEFT OUTER JOIN PAY.PEOPLE PP ON PPL.PRSN_NBR = PP.PRSN_NBR
							WHERE PPL.PRSN_NBR='".$PrsnNbr."'";
				}
				
				else
				{
					$query_cek="SELECT BON.PRSN_NBR AS PRSN_NBR,PPL.NAME,PP.PAY_BASE,PP.PAY_ADD,PYMT_DTE,CRDT_AMT,PP.DED_DEF,BON.UPD_TS,BON.UPD_NBR,CRDT_PRNC,PYMT_NBR, CRDT_F, CRDT_RSN, CRDT_APV, CRDT_APV_FIN, PPF.NAME AS FIN_NAME, PPA.NAME AS PPA_NAME,BON.DSBRS_TYP
							FROM PAY.EMPL_CRDT BON 
							INNER JOIN CMP.PEOPLE PPL ON BON.PRSN_NBR=PPL.PRSN_NBR
							LEFT OUTER JOIN PAY.PEOPLE PP ON PPL.PRSN_NBR=PP.PRSN_NBR
							LEFT OUTER JOIN CMP.PEOPLE PPF ON PPF.PRSN_NBR = BON.CRDT_APV_FIN_NBR
							LEFT OUTER JOIN CMP.PEOPLE PPA ON PPA.PRSN_NBR = BON.CRDT_APV_NBR
							WHERE BON.DEL_NBR=0 AND BON.PRSN_NBR='".$PrsnNbr."' AND PYMT_DTE='".$PymtDte."'";
				}
				
				$result_cek=mysql_query($query_cek,$local);
				$row_cek=mysql_fetch_array($result_cek);
			
				if ($row_cek['CRDT_APV_FIN']==1){
					$disabledFin = "disabled";
				}else{
					$displayFin  = "display:none;";
				}

				if ($row_cek['CRDT_APV']==0){
					$displayAdm  = "display:none;";
				}

				// QUERY UNTUK CEK TAGIHAN 
				$query_cek_tagihan = "SELECT BUY_PRSN_NBR, 
										SUM(TOT_REM) AS TOT_REM,
										DATE(ORD_TS) AS ORD_TS
									FROM CMP.PRN_DIG_ORD_HEAD
									WHERE BUY_PRSN_NBR = '".$PrsnNbr."'
									GROUP BY BUY_PRSN_NBR";

				$result_cek_tagihan	= mysql_query($query_cek_tagihan);
				$row_cek_tagihan 	= mysql_fetch_array($result_cek_tagihan);
				

				// QUERY UNTUK MENGHITUNG JUMLAH MASUK KERJA
				$hitung_hari = date('Y-m-d', strtotime('-31 days'));

				$query_cek_absen = "SELECT PRSN_NBR, CLOK_IN_TS, CLOK_OT_TS
									FROM PAY.MACH_CLOK
									WHERE PRSN_NBR = '$PrsnNbr' 
									AND DATE(CLOK_IN_TS) >= '$hitung_hari'";
				
				$result_cek_absen = mysql_query($query_cek_absen);
				$totalDays = mysql_num_rows($result_cek_absen);
				

				// QUERY UNTUK MENGHITUNG JUMLAH TANGGAL MERAH
				$query_cek_off = "SELECT *,
									COUNT(HLDY_DTE) AS TOTAL_LIBUR
									FROM PAY.HOLIDAY 
									WHERE HLDY_DTE >= DATE_SUB(NOW(), INTERVAL -30 DAY)";
				
				$result_cek_off = mysql_query($query_cek_off);
				$row_cek_off = mysql_fetch_array($result_cek_off);


				//QUERY UNTUK MENGHITUNG CUTI 
				if($monthtm == "" && $yeartm == ""){
					$query_cek_timeoff="SELECT 
											TM_OFF_NBR,
											PRSN_NBR,
											CONCAT(TM_OFF_BEG_DTE,' - ',TM_OFF_END_DTE) AS TM_OFF_DTE,
											TM_OFF_BEG_DTE,
											TM_OFF_END_DTE,
											TM_OFF_RSN,
											TM_OFF_F,
											DATEDIFF(TM_OFF_END_DTE,TM_OFF_BEG_DTE)+1 AS CNT_TM_OFF,
											(CASE WHEN TM_OFF_F=1 THEN DATEDIFF(TM_OFF_END_DTE,TM_OFF_BEG_DTE)+1 ELSE 0 END) AS CNT_DTE
										FROM PAY.TM_OFF  
										WHERE PRSN_NBR = ". $PrsnNbr ." AND DEL_NBR=0
										GROUP BY TM_OFF_NBR DESC";
									}else{
					$query_cek_timeoff="SELECT 
										TM_OFF_NBR,
										PRSN_NBR,
										CONCAT(TM_OFF_BEG_DTE,' - ',TM_OFF_END_DTE) AS TM_OFF_DTE,
										TM_OFF_BEG_DTE,
										TM_OFF_END_DTE,
										TM_OFF_RSN,
										TM_OFF_F,
										DATEDIFF(TM_OFF_END_DTE,TM_OFF_BEG_DTE)+1 AS CNT_TM_OFF,
										(CASE WHEN TM_OFF_F=1 THEN DATEDIFF(TM_OFF_END_DTE,TM_OFF_BEG_DTE)+1 ELSE 0 END) AS CNT_DTE
							FROM PAY.TM_OFF  
							WHERE PRSN_NBR=".$PrsnNbr." 
								AND MONTH(TM_OFF_BEG_DTE)='".$month."' 
								AND YEAR(TM_OFF_BEG_DTE)='".$year."'
								AND DEL_NBR=0
							GROUP BY TM_OFF_NBR
							ORDER BY TM_OFF_NBR DESC";
						}

				$result_cek_timeoff	= mysql_query($query_cek_timeoff);
				$row_cek_timeoff	= mysql_fetch_array($result_cek_timeoff);

				$totalHariMasuk = $totalDays + $row_cek_timeoff['CNT_TM_OFF'] + $row_cek_off['TOTAL_LIBUR'];

			?>

			<form enctype="multipart/form-data" action="#" method="post" style="width:600px" onSubmit="return checkform();">
				<p>
					<?php if ($row_cek['PRSN_NBR']!=""){?>
					<h2>
						<?php echo $row_cek['NAME'] ?>
					</h2>
									
					<h3>
						Bon Karyawan Nomor Induk: <?php echo $row_cek['PRSN_NBR'];if($row_cek['PRSN_NBR']==""){echo "Nomor Baru";} ?>
					</h3>
					<input name="PRSN_NBR" value="<?php echo $row_cek['PRSN_NBR']; ?>" type="hidden" />
					<table class="flat">
					
					<?php } else { ?>	
					<h3>
						Bon Karyawan Nomor Induk: <?php echo $row_cek['PRSN_NBR'];if($row_cek['PRSN_NBR']==""){echo "Nomor Baru";} ?>
					</h3>

					<table class="flat">
							<tr class="flat">
								<td class="flat" width="325px"><label style="padding-bottom:5px;">Nama Karyawan</label></td>
								<td class="flat" width="325px">
								<div class='side'><select name="PRSN_NBR" id="PRSN_NBR" class="chosen-select" style="width:325px">
								<?php
									$query="SELECT PRSN_NBR, NAME 
											FROM CMP.PEOPLE 
											WHERE TERM_DTE IS NULL AND DEL_NBR=0 
												AND CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_COMPANY)	
											ORDER BY NAME ASC";
									genCombo($query,"PRSN_NBR","NAME",$row['PRSN_NBR'],"Kosong",$local);
								?>
								</td>
							</tr>
							<input name="PAY_BASE" id="PAY_BASE" value="<?php echo $row_cek['PAY_BASE']; ?>"  type="hidden" />
							<input name="PAY_ADD" id="PAY_ADD" value="<?php echo $row_cek['PAY_ADD']; ?>"  type="hidden" />
						<?php } ?>							
						
						<tr class="flat">
							<td class="flat" width="150px">Tanggal &nbsp;&nbsp;</td>
							<td class="flat" width="600px">
								<input id="PYMT_DTE" name="PYMT_DTE" size="20" value="<?php if ($row_cek['PYMT_DTE']==''){echo date('Y-m-d');}else{ echo $row_cek['PYMT_DTE'];} ?>"<?php if ($executive >=1) { echo "readonly";}?>></input>
							</td>
						</tr>
						<?php if ($executive <1) {?>
							<script>
								new CalendarEightysix('PYMT_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
							</script>
						<?php } ?>		
						<tr class="flat">
							<td class="flat">Bon Pokok</td>
							<td class="flat"><input size="20" id="CRDT_PRNC" name="CRDT_PRNC" value="<?php echo $row_cek['CRDT_PRNC']; ?>"></input>&nbsp;&nbsp;&nbsp; 
							<span id="ALERT"></span><span id="ALERT_BON"></span></td>
						</tr>
						<tr class="flat">
							<td class="flat">Periode cicilan</td>
							<td class="flat"><input size="20" id="PYMT_NBR" name="PYMT_NBR" value="<?php echo $row_cek['PYMT_NBR']; ?>"></input>&nbsp;&nbsp;&nbsp; <span id="ALERT_PYMT" ></span></td>
						</tr>
						
						<tr class="flat">
							<td class="flat">Jumlah bon</td>
							<td class="flat"><input size="20" readonly id="CRDT_AMT" name="CRDT_AMT" value="<?php echo $row_cek['CRDT_AMT']; ?>"></input></td>
						</tr>

						<tr class="flat">
						<?php 
							if($_GET['PYMT_DTE'] != '') { if ($row_cek['CRDT_AMT']!=''){ $cicilan = round($row_cek['CRDT_AMT']/$row_cek['PYMT_NBR']);} } 
								else { if($RemCrdt <= 0) { $cicilan = "0"; } else { $cicilan = $row_cek['DED_DEF']; } } 
						?>
							<td class="flat">Cicilan per bulan</td>
							<td class="flat"><input readonly id="DED_DEF" name="DED_DEF" value="<?php echo $cicilan;?>" type="text" size="20" />&nbsp;&nbsp;&nbsp;<span id="ALERT_INSTALLMENT" ></span></td>
						</tr>	

						<tr class="flat" style="display: none;">
							<td class="flat">Credit</td>
							<td class="flat"><input name='CRDT_F' id='CRDT_F' type='checkbox' class='regular-checkbox' <?php if($row_cek['CRDT_F']=="1"){echo "checked";} ?>/>&nbsp;<label for="CRDT_F"></td>
						</tr>
						
						<tr class="flat">
							<td class="flat"><label style="padding-bottom:5px;">Alasan</label><br/></td>
							<td class="flat"><textarea id="CRDT_RSN" name="CRDT_RSN" rows="4" cols="50" style="position: inherit;"><?php echo $row_cek['CRDT_RSN']; ?></textarea><br /></td>
						</tr>

						<?php if ($executive>=1){
							$display= "display:none";
						}?>
						<tr class="flat" style="<?php echo $display;?>">
							<td class="flat">Tipe Pencairan</td>
							<td class="flat">
								<select name="DSBRS_TYP" id="DSBRS_TYP" class="chosen-select" style="width:325px">
									<option value="PAY" <?php if ($row_cek['DSBRS_TYP']=='PAY'){echo "selected";}?>>Payroll</option>
									<option value="TRF" <?php if ($row_cek['DSBRS_TYP']=='TRF'){echo "selected";}?>>Transfer</option>
								</select>
							</td>
						</tr>

						<?php if (in_array($_SESSION['personNBR'], $PPlFinance) || $executive <1){?>
						<tr class="flat">
							<td class="flat">Approve Finance</td>
							<td class="flat"><input name='CRDT_APV_FIN' id='CRDT_APV_FIN' type='checkbox' class='regular-checkbox' <?php if($row_cek['CRDT_APV_FIN']=="1"){echo "checked";} ?> <?php echo $disabledFin;?>/>&nbsp;<label for="CRDT_APV_FIN"></label><span style="position: absolute;<?php echo $displayFin; ?>">Disetujui oleh <?php echo $row_cek['FIN_NAME']?> </span></td>
						</tr>
						<?php }?>

						<?php if ($executive <1){?>
						
						<tr class="flat">
							<td class="flat">Approve</td>
							<td class="flat"><input name='CRDT_APV' id='CRDT_APV' type='checkbox' class='regular-checkbox' <?php if($row_cek['CRDT_APV']=="1"){echo "checked";} ?>/>&nbsp;<label for="CRDT_APV"></label><span style="position: absolute;<?php echo $displayAdm; ?>">Disetujui oleh <?php echo $row_cek['PPA_NAME']?> </span></td>
						</tr>
						<?php }?>
						
						<?php
						if($_GET['PYMT_DTE'] != "") {
						?>
							<tr class="flat" style="height:10px"><td class="flat" colspan="2"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>
							<?php 
								if($cloud != false && paramCloud() == 1 &&
									(empty($row_cek['CRDT_APV']) ||
									empty($row_cek['CRDT_APV_FIN']) ||
									$row_cek['CRDT_APV_FIN'] == 0 ||
									$executive < 1 && $row_cek['CRDT_APV'] == 0 ||
									$finance < 1 && $row_cek['CRDT_APV_FIN'] == 0) &&
									$row_cek_tagihan['TOT_REM'] <= 0 && $totalHariMasuk >= 30){
										echo '<tr class="flat" style="flat"><td class="flat" colspan="2"><input class="process" id="submit_button" type="submit" value="Simpan"/><div></div></td></tr>';
								}else{
									if ($totalHariMasuk < 30) {
										echo '<tr><td class="flat" colspan="2"><p>Jumlah hari masuk anda kurang</p></td></tr>';
									}
									if ($row_cek_tagihan['TOT_REM'] > 0) {
										echo '<tr><td class="flat" colspan="2"><p>Anda masih memiliki tagihan sebesar Rp.' . $row_cek_tagihan['TOT_REM'] . '</p></td></tr>';
									}
								}

							}else if($row_cek['CRDT_PRNC'] != "") {
									if(($cloud!=false)&&(paramCloud()==1) && 
										(($row_cek['CRDT_APV']=='') || 
										($row_cek['CRDT_APV']==0))){
											echo '<tr class="flat" style="flat"><td class="flat" colspan="2"><input class="process" id="submit_button" type="submit" value="Simpan"/><div></div></td></tr>';
										}
							}else{
								if (($_GET['PYMT_DTE'] == "") && ($HireDte < 6)) {
							?>
								<!-- checkCredit : get from index.php -->
								<script type="text/javascript">
									window.scrollTo(0,0);
									window.top.document.getElementById('checkHire').style.display='block';window.top.document.getElementById('fade').style.display='block';
								</script>
							<?php
								}else if (($_GET['PYMT_DTE'] == "") && ($RemCrdt > 0)){ 
							?>
							<!-- checkCredit : get from index.php -->
								<script type="text/javascript">
									window.scrollTo(0,0);
									window.top.document.getElementById('checkCredit').style.display='block';window.top.document.getElementById('fade').style.display='block';
								</script>
							<?php 
								}else{
								}
								echo '<tr class="flat" style="height:10px"><td class="flat" colspan="2"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>';
								if ($cloud != false && paramCloud() == 1) {
									if (($row_cek['CRDT_APV'] == '' || $row_cek['CRDT_APV'] == 0) && 
										$row_cek_tagihan['TOT_REM'] <= 0 && $totalHariMasuk >= 30) {
										echo '<tr class="flat" style="flat"><td class="flat" colspan="2"><input class="process" id="submit_button" type="submit" value="Simpan"/><div></div></td></tr>';
									} else {
										if ($totalHariMasuk < 30) {
											echo '<tr><td colspan="2"><p>Jumlah hari masuk anda kurang</p></td></tr>';
											}
										if ($row_cek_tagihan['TOT_REM'] > 0) {
											echo '<tr><td colspan="2"><p>Anda masih memiliki tagihan sebesar Rp.'.$row_cek_tagihan['TOT_REM'].'</p></td></tr>';
											}
										}
									} 	
								}	
							?>
					</table>		
				</p>		
			</form>
		</td>
	</tr>
</table>

<script src="framework/jquery/jquery-latest.min.js" type="text/javascript"></script>

<script type="text/javascript">
	
	jQuery.noConflict();
	
	(function($) {
        $(document).ready(function() {

		var payBase = "<?php echo $row_cek['PAY_BASE']; ?>";
		var payAdd = "<?php echo $row_cek['PAY_ADD']; ?>";

		var param = 2;
		
		$('#CRDT_PRNC, #PYMT_NBR').on('keyup change click', function() {
				if (payBase == ""){ payBase = $('#PAY_BASE').val();}
				if (payAdd  == ""){ payAdd = $('#PAY_ADD').val();}
					
				var CrdtPrnc = $('#CRDT_PRNC').val();
				var pymtNbr = $('#PYMT_NBR').val();
				
				if(CrdtPrnc != '') {
					if( CrdtPrnc > (4*payBase)) {
						$('#ALERT_BON').html('<img src="img/error.png" style="border:none;"> Maksimum 4 x gaji pokok');
						//bonPok 	= 4*payBase;
						bonPok 	= CrdtPrnc;
						$(':input[type="submit"]').prop('disabled', true);
						jQuery(':input[type="submit"]').hide();
					}
					else {
						$('#ALERT_BON').html('');
						bonPok 	= CrdtPrnc;
						$(':input[type="submit"]').prop('disabled', false);
						jQuery(':input[type="submit"]').show();
					}
				}
				
				if( pymtNbr != '') {
					if(pymtNbr > 10) {
						$('#ALERT_PYMT').html('<img src="img/error.png" style="border:none;"> Maksimum 10 x cicilan');
						$(':input[type="submit"]').prop('disabled', true);
						jQuery(':input[type="submit"]').hide();
					}
					else if(pymtNbr <= 0){
						$('#ALERT_PYMT').html('<img src="img/error.png" style="border:none;"> Minimum 1x cicilan ');
						$(':input[type="submit"]').prop('disabled', true);
						jQuery(':input[type="submit"]').hide();
						}
						else {
							$('#ALERT_PYMT').html('');
							$(':input[type="submit"]').prop('disabled', false);
							jQuery(':input[type="submit"]').show();
						}
				}
				
			/*if((pymtNbr.value!='') 
			&& (pymtNbr >= 0)
			&& (pymtNbr <= 10) 
			&& (CrdtPrnc !='') 
			&& (CrdtPrnc <= (4*payBase)) 
			)*/
			
			if((pymtNbr.value!='') && (pymtNbr >= 0) && (CrdtPrnc !='')) 
			{
		
				if(pymtNbr < 1){
					pymtNbr=1;
					var bonTot	= 0;
					var dedDef	= 0;
				}
				else if(pymtNbr == 1) {
					bonPok = CrdtPrnc;
					var totBase = Math.round(payBase+payAdd);
					console.log(totBase);
					if (CrdtPrnc > (4*payBase)) {
						$('#ALERT_BON').html('<img src="img/error.png" style="border:none;"> Maksimum gaji pokok + gaji tambahan');
						var bonTot	= 0;
						var dedDef	= 0;
					}
					else {
					var bonTot	= bonPok;
					var dedDef	= bonPok;
					}
				}
				else {
					
					var dedDef	= Math.round( ((param/100) * bonPok) / ( 1 - Math.pow( ( 1 + (param/100)), - pymtNbr) ));
					
					var bonTot	= Math.round( dedDef * pymtNbr );
					
					if(dedDef > (65/100)*(payBase+payAdd)) {
						$('#ALERT_INSTALLMENT').html('<img src="img/error.png" style="border:none;"> Maks cicilan adalah 65% dari gaji pokok+gaji tambahan');
					}
					else {
						$('#ALERT_INSTALLMENT').html('');
					}
				
				}
			
				$('#CRDT_AMT').val(bonTot);
				$('#DED_DEF').val(dedDef);
				}

			});
		});
    })(jQuery);
</script>

<script src="framework/database/jquery.min.js" type="text/javascript"></script>
<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
		jQuery.noConflict();
		var config = {
			'.chosen-select'           : {},
			'.chosen-select-deselect'  : {allow_single_deselect:true},
			'.chosen-select-no-single' : {disable_search_threshold:10},
			'.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
			'.chosen-select-width'     : {width:"95%"}
	   	};
		for (var selector in config) {
			jQuery(selector).chosen(config[selector]);
		}
</script>
<script type="text/javascript">
	(function($) {

        $(document).ready(function() {
        	$('#PRSN_NBR').on('change', function() {
        		var PrsnNbr = $('#PRSN_NBR').val();
        		$.ajax({
        			url:"kas-bon-ajax.php",
        			type:"POST",
        			dataType:"html",
        			data:"PRSN_NBR="+ PrsnNbr,
        			success: function(data){
        				respon = JSON.parse(data);
        				
        				$('#PAY_BASE').val(respon.PAY_BASE); 
        				$('#PAY_ADD').val(respon.PAY_ADD); 
    				},
    				error: function(error){
				         console.log("Error:");
				         console.log(error);
				    }
        		});
        	});
        });
    })(jQuery);	
</script>
</body>
</html>