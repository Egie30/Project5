<?php 

	$POSID=$_GET['POS_ID'];
	$POSIP=$_GET['POSIP'];
	
	if($POSID != "") { require_once "framework/database/connect-cashier.php"; }
		else { require_once "framework/database/connect.php"; }

	require_once "framework/functions/default.php";
	require_once "framework/security/default.php";
	require_once "framework/alert/alert.php";
	require_once "framework/functions/dotmatrix.php";
	
	if($POSID == "") {
		$Security = getSecurity($_SESSION['userID'],"Executive");
	}
	
	$CshDayDte= $_GET['CSH_DAY_DTE'];
	
	/*
	if ($_GET['CSH_DAY_DTE'] == '') {	$CshDayDte 	= date('Y-m-d');	}
		else {	$CshDayDte 	= $_GET['CSH_DAY_DTE'];		}
	*/
	
	if ($_GET['S_NBR'] == '') {	$SNbr 	= 1;	}
		else {	$SNbr 	= $_GET['S_NBR'];	}
	
	$typ 	  = $_GET['TYP'];
		
	if( $_POST['CSH_DAY_DTE'] != "")
	{
	
			if($_POST['CSH_DAY_DTE']== -1){$CshDayDte= date('Y-m-d'); } else { $CshDayDte= $_POST['CSH_DAY_DTE']; }
			if($_POST['CSH_DTE']== -1){$CshDate= $CshDayDte; } else { $CshDate= $_POST['CSH_DTE']; }
			
			if($_POST['REF_BNK_NBR']==""){$RefBnkNbr="NULL";}else{$RefBnkNbr="'".$_POST['REF_BNK_NBR']."'";}
			if($_POST['S_NBR']==""){$SNbr="0";}else{$SNbr=$_POST['S_NBR'];}
			if($_POST['CSH_IN_DRWR']!="0"){$CshInDrwr=str_replace('.', '', $_POST['CSH_IN_DRWR']);}
			if($_POST['TOT_AMT']!="0"){$TotAmt=str_replace('.', '', $_POST['TOT_AMT']);}
			if($_POST['CSH_REG']!="0"){$CshReg=str_replace('.', '', $_POST['CSH_REG']);}
			if($_POST['DEP_DTE']==""){$DepDte="NULL";}else{$DepDte="'".$_POST['DEP_DTE']."'";}
			if($_POST['VRFD_F']==""){$VrfdF="0";$VrfdTs="NULL";$VrfdNbr="0";}else{$VrfdF="1";$VrfdTs="CURRENT_TIMESTAMP";$VrfdNbr=$_SESSION['personNBR'];}
			
		$bank = array("PT","CV","PR", "AD");
		
		for($i = 1; $i <= 4; $i++) {
		
			if ($_POST['CSH_DAY_NBR'] == -1) {
			
				$query		= "SELECT COALESCE(MAX(CSH_DAY_NBR),0)+1 AS NEW_NBR FROM RTL.CSH_DAY";
				$result		= mysql_query($query);
				$row		= mysql_fetch_array($result);
				$CshDayNbr	= $row['NEW_NBR'];
				
				$query		= "INSERT INTO RTL.CSH_DAY (CSH_DAY_NBR,CSH_DAY_DTE, S_NBR,ACCT,CSH_PRSN_NBR,CRT_TS,CRT_NBR) 
								VALUES (".$CshDayNbr.",'".$CshDayDte."',".$SNbr.",'".$bank[$i-1]."',".$_SESSION['personNBR'].",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
				$result		= mysql_query($query);
				
				//echo $query."<br /><br /><br />";
			}
		
			$textCHK	= "CHK_AMT".$i;
			$textCSH	= "CSH_AMT".$i;
			$textDEP	= "DEP_DTE".$i;
			
			$ChkAmt		= $_POST[$textCHK];
			$CshAmt		= $_POST[$textCSH];
			$DepDte		= $_POST[$textDEP];
			
			
			if (($i == 1) || ($i == 2) || ($i == 3)) { $CshInDrwr = 0; } 
			if ($i == 4) { $CshInDrwr = str_replace('.', '', $_POST['CSH_IN_DRWR']); }
			
			if (($i==1)||($i==2)||($i==3)){
				$CshReg = 0;
			} else {
				$CshReg = str_replace('.', '', $_POST['CSH_REG']);
			}
			
			$TotAmt		= $ChkAmt+$CshAmt+$CshInDrwr;
			
			
		
			//csh reg masih disamakan dengan uang di laci
			$query		= "UPDATE RTL.CSH_DAY 
							SET CSH_DAY_DTE= '".$CshDayDte."',
								REF_BNK_NBR=".$RefBnkNbr.",
								S_NBR=".$SNbr.",
								CHK_AMT=".$ChkAmt.",
								CSH_AMT=".$CshAmt.",
								CSH_IN_DRWR=".$CshInDrwr.",
								TOT_AMT=".$TotAmt.",
								CSH_REG=".$CshReg.", 
								DEP_DTE= '".$DepDte."',
								VRFD_F=".$VrfdF.",
								VRFD_TS=".$VrfdTs.",
								VRFD_NBR=".$VrfdNbr.",
								UPD_TS=CURRENT_TIMESTAMP,
								UPD_NBR=".$_SESSION['personNBR']." ";
							
							if($POSID != "") {
								$query.=",POS_ID = ".$POSID." ";
							}
							$query.=" WHERE CSH_DAY_DTE= '".$CshDate."' AND S_NBR = ".$SNbr." AND ACCT = '".$bank[$i-1]."' ";
							
			mysql_query($query);
			
			//echo $query."<br /><br /><br />";
		}
	
		if ($_GET['CSH_DAY_DTE'] == '') {
			$CshDay 	= date('Y-m-d');
		}
		else {
			$CshDay 	= $_GET['CSH_DAY_DTE'];
		}
		
		echo "<script type='text/javascript'>parent.document.getElementById('bottom').src='http://".$POSIP."/cash-day-report-edit-print.php?CSH_DAY_DTE=".$CshDay."&S_NBR=".$SNbr."&POS_ID=".$POSID."&CSH=".$_SESSION['userID']."';</script>";	
		
	}
	
	
	
	
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	
	<?php 
	if ($POSID != "") { 
	echo '<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src="framework/jquery-freezeheader/js/jquery.freezeheader.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>';
	}
	else {
	
	echo '<script>parent.Pace.restart();</script>
		<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
		<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
		<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
		<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
		<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
		<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>

		<script type="text/javascript">jQuery.noConflict()</script>
		<link rel="stylesheet" href="framework/combobox/chosen.css">';
	}	?>

<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />

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
		function getInt(objectID)
		{
			if(document.getElementById(objectID).value=="")
			{
				return 0;
			}else{
				return parseInt(document.getElementById(objectID).value);
			}
		}

		function calcAmt(){
			document.getElementById('CORRTN_AMT').value=getInt('CSH_REG')-getInt('CSH_IN_DRWR');
		}
	</script>

	<script>
		parent.document.getElementById('addressDeleteYes').onclick=
		function () {
			var typ = '<?php echo $typ; ?>';
			console.log(typ);
			if (typ=='CAD'||typ=='CAR'){
				parent.document.getElementById('content').src='cash-day-report.php?DEL_A=<?php echo $CshDayDte; ?>&S_NBR=<?php echo $SNbr; ?>&TYP=<?php echo $typ;?>';				
			}else{
				parent.document.getElementById('content').src='dep-cash-day-report.php?DEL_A=<?php echo $CshDayDte; ?>&S_NBR=<?php echo $SNbr; ?>';
			}
			parent.document.getElementById('addressDelete').style.display='none';
			parent.document.getElementById('fade').style.display='none';
		};
	</script>
	
</head>


<body style='height:100px'>
	<?php
		$query= 'SELECT  CSH_DAY_NBR,
					DATE(CSH_DAY_DTE) AS CSH_DAY_DTE,
					REF_BNK_NBR,
					S_NBR,
					SUM(CASE WHEN ACCT="PT" THEN COALESCE(CHK_AMT,0) END) AS CHK_AMT1,
					SUM(CASE WHEN ACCT="CV" THEN COALESCE(CHK_AMT,0) END) AS CHK_AMT2,
					SUM(CASE WHEN ACCT="PR" THEN COALESCE(CHK_AMT,0) END) AS CHK_AMT3,
					SUM(CASE WHEN ACCT="AD" THEN COALESCE(CHK_AMT,0) END) AS CHK_AMT4,
					SUM(CASE WHEN ACCT="PT" THEN COALESCE(CSH_AMT,0) END) AS CSH_AMT1,
					SUM(CASE WHEN ACCT="CV" THEN COALESCE(CSH_AMT,0) END) AS CSH_AMT2,
					SUM(CASE WHEN ACCT="PR" THEN COALESCE(CSH_AMT,0) END) AS CSH_AMT3,
					SUM(CASE WHEN ACCT="AD" THEN COALESCE(CSH_AMT,0) END) AS CSH_AMT4,
					SUM(CASE WHEN ACCT="PT" THEN COALESCE(CSH_IN_DRWR,0) END) AS CSH_IN_DRWR1,
					SUM(CASE WHEN ACCT="CV" THEN COALESCE(CSH_IN_DRWR,0) END) AS CSH_IN_DRWR2,
					SUM(CASE WHEN ACCT="PR" THEN COALESCE(CSH_IN_DRWR,0) END) AS CSH_IN_DRWR3,
					SUM(CASE WHEN ACCT="AD" THEN COALESCE(CSH_IN_DRWR,0) END) AS CSH_IN_DRWR4,
					SUM(CSH_IN_DRWR) AS CSH_IN_DRWR,
					SUM(CASE WHEN ACCT="PT" THEN COALESCE(TOT_AMT,0) END) AS TOT_AMT1,
					SUM(CASE WHEN ACCT="CV" THEN COALESCE(TOT_AMT,0) END) AS TOT_AMT2,
					SUM(CASE WHEN ACCT="PR" THEN COALESCE(TOT_AMT,0) END) AS TOT_AMT3,
					SUM(CASE WHEN ACCT="AD" THEN COALESCE(TOT_AMT,0) END) AS TOT_AMT4,
					SUM(TOT_AMT) AS TOT_AMT,
					SUM(CASE WHEN ACCT="PT" THEN COALESCE(CSH_REG,0) END) AS CSH_REG1,
					SUM(CASE WHEN ACCT="CV" THEN COALESCE(CSH_REG,0) END) AS CSH_REG2,
					SUM(CASE WHEN ACCT="PR" THEN COALESCE(CSH_REG,0) END) AS CSH_REG3,
					SUM(CASE WHEN ACCT="AD" THEN COALESCE(CSH_REG,0) END) AS CSH_REG4,
					SUM(CSH_REG) AS CSH_REG,
					CSH_PRSN_NBR,
					DATE(DEP_DTE) AS DEP_DTE,
					VRFD_F,
					SUM(CASE WHEN ACCT="PT" THEN COALESCE((CSH_REG-CSH_IN_DRWR),0) END) AS CORRTN_AMT1,
					SUM(CASE WHEN ACCT="CV" THEN COALESCE((CSH_REG-CSH_IN_DRWR),0) END) AS CORRTN_AMT2,
					SUM(CASE WHEN ACCT="PR" THEN COALESCE((CSH_REG-CSH_IN_DRWR),0) END) AS CORRTN_AMT3,
					SUM(CSH_REG-CSH_IN_DRWR) AS CORRTN_AMT,
					PPL.NAME AS CSH_PRSN_NAME,
					PLE.NAME AS DEP_NAME,
					VRF.NAME AS VRFD_NAME
				FROM RTL.CSH_DAY CAD
				LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR =CAD.CRT_NBR 
				LEFT OUTER JOIN CMP.PEOPLE PLE ON PLE.PRSN_NBR=CAD.UPD_NBR
				LEFT OUTER JOIN CMP.PEOPLE VRF ON VRF.PRSN_NBR=CAD.VRFD_NBR
				WHERE DATE(CSH_DAY_DTE)= "'.$CshDayDte.'" AND S_NBR='.$SNbr.'';
		
		//echo "<pre>".$query;
		
		$result	= mysql_query($query);
		$row 	= mysql_fetch_array($result);
		
		$query_date	= "SELECT 
							ACCT,
							DATE(DEP_DTE) AS DEP_DTE
						FROM RTL.CSH_DAY
						WHERE CSH_DAY_DTE = '".$CshDayDte."'
							AND S_NBR = ".$SNbr." ";
							
		$result_date	= mysql_query($query_date);
		while($row_date = mysql_fetch_array($result_date)) {
		
			if ($row_date['ACCT'] == 'PT') { $DepDate1 = $row_date['DEP_DTE']; }
			if ($row_date['ACCT'] == 'CV') { $DepDate2 = $row_date['DEP_DTE']; }
			if ($row_date['ACCT'] == 'PR') { $DepDate3 = $row_date['DEP_DTE']; }
			if ($row_date['ACCT'] == 'AD') { $DepDate4 = $row_date['DEP_DTE']; }

		}
		
	?>
	

	<div <?php if (($locked == 0) && ($POSID == "")) { echo "style='display:none'"; } ?>>
	<table class="flat">
	<tr class="flat"><td class="flat" colspan="3" style="font-weight:bold;">Saran Setoran Bank</td></tr>		

		<tr class="flat" >
				<td >
				<tr><td class="flat">Rekening 1</td><td><input name="TOT_AMT_PT" id="TOT_AMT_PT" type="text"> </td></tr>
				<tr><td class="flat">Rekening 2</td><td><input name="TOT_AMT_CV" id="TOT_AMT_CV" type="text"> </td></tr>
				<tr><td class="flat">Rekening 3</td><td><input name="TOT_AMT_PR" id="TOT_AMT_PR" type="text"> </td></tr>
				<tr><td class="flat">Rekening 4</td><td><input name="TOT_AMT_AD" id="TOT_AMT_AD" type="text"> </td></tr>
				</td>
		</tr>
	</table>
	</div>	

	<div class="toolbar-only">
		<?php if(($Security==0)&&($CshDayDte !=0)) { ?>
			<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('addressDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class="fa fa-trash toolbar" style="cursor:pointer"></span></a></p>
		<?php } ?>
		
		<p class="toolbar-right">
		<a href="cash-day-report-edit-print.php?CSH_DAY_DTE=<?php echo $CshDayDte; ?>&S_NBR=<?php echo $SNbr; ?>&POS_ID=<?php echo $POSID; ?>"><span class='fa fa-print toolbar'></span></a>
		</p>
	</div>
	
	
	<form enctype="multipart/form-data" action="#" method="post" style="width:600px">
		
		<input name="CSH_DAY_NBR" value="<?php echo $row['CSH_DAY_NBR'];if($row['CSH_DAY_NBR']==""){echo "-1";} ?>" type="hidden" />
		
		<input name="CSH_DAY_DTE" value="<?php echo $row['CSH_DAY_DTE'];if($row['CSH_DAY_DTE']==""){echo "-1";} ?>" type="hidden" />
		
		<input name="CSH_DTE" value="<?php echo $row['CSH_DAY_DTE'];if($row['CSH_DAY_DTE']==""){echo "-1";} ?>" type="hidden" />
				
				
		<label class='side'>Tanggal</label>
		<input id="CSH_DAY_DTE" name="CSH_DAY_DTE" value="<?php if ($row['CSH_DAY_DTE']==''){echo date("Y-m-d");}else{echo $row['CSH_DAY_DTE'];} ?>" type="text" size="30" /><br />
		<script>
			new CalendarEightysix('CSH_DAY_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		
		<label class='side'>No. Referensi</label>
		<input name="REF_BNK_NBR" value="<?php echo $row['REF_BNK_NBR']; ?>" type="text" size="30" style="padding-top: 10px;" /><br />	
		
		<label class='side'>Shift</label>
		<div class='side'>
			<select class="chosen-select" style='width:53px' name="S_NBR" onchange="val_default()"><br />
					<?php genComboArrayVal(array('1','2','3'),$row['S_NBR']); ?>
			</select><br />
		</div>
			<label class='side'>Cek/Giro 1</label>
			<input name="CHK_AMT1" id="CHK_AMT1" value="<?php echo $row['CHK_AMT1']; ?>" type="text" size="30" style="padding-top: 10px;" class="inputmask currency" onkeyup="CalTotal();calcAmt();" /><br />
			
			<label class='side'>Cek/Giro 2</label>
			<input name="CHK_AMT2" id="CHK_AMT2" value="<?php echo $row['CHK_AMT2']; ?>" type="text" size="30" style="padding-top: 10px;" class="inputmask currency" onkeyup="CalTotal();calcAmt();" /><br />
			
			<label class='side'>Cek/Giro 3</label>
			<input name="CHK_AMT3" id="CHK_AMT3" value="<?php echo $row['CHK_AMT3']; ?>" type="text" size="30" style="padding-top: 10px;" class="inputmask currency" onkeyup="CalTotal();calcAmt();" /><br />
			
			<label class='side'>Cek/Giro 4</label>
			<input name="CHK_AMT4" id="CHK_AMT4" value="<?php echo $row['CHK_AMT4']; ?>" type="text" size="30" style="padding-top: 10px;" class="inputmask currency" onkeyup="CalTotal();calcAmt();" /><br />
			
			<label class='side'>Setoran 1</label>
			<input name="CSH_AMT1" id="CSH_AMT1" value="<?php echo $row['CSH_AMT1']; ?>" type="text" size="30" class="inputmask currency" onkeyup="CalTotal();calcAmt();" />
			
			<input id="DEP_DTE1" name="DEP_DTE1" value="<?php if ($DepDate1==''){echo date("Y-m-d");}else{echo $DepDate1;} ?>" type="text" size="30" /><br />
			<script>
				new CalendarEightysix('DEP_DTE1', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
			</script>
			
			<label class='side'>Setoran 2</label>
			<input name="CSH_AMT2" id="CSH_AMT2" value="<?php echo $row['CSH_AMT2']; ?>" type="text" size="30" class="inputmask currency" onkeyup="CalTotal();calcAmt();" />
			
			<input id="DEP_DTE2" name="DEP_DTE2" value="<?php if ($DepDate2==''){echo date("Y-m-d");}else{echo $DepDate2;} ?>" type="text" size="30" /><br />
			<script>
				new CalendarEightysix('DEP_DTE2', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
			</script>
						
			<label class='side'>Setoran 3</label>
			<input name="CSH_AMT3" id="CSH_AMT3" value="<?php echo $row['CSH_AMT3']; ?>" type="text" size="30" class="inputmask currency" onkeyup="CalTotal();calcAmt();" />
			
			<input id="DEP_DTE3" name="DEP_DTE3" value="<?php if ($DepDate3==''){echo date("Y-m-d");}else{echo $DepDate3;} ?>" type="text" size="30" /><br />
			<script>
				new CalendarEightysix('DEP_DTE3', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
			</script>
			
			<label class='side'>Setoran 4</label>
			<input name="CSH_AMT4" id="CSH_AMT4" value="<?php echo $row['CSH_AMT4']; ?>" type="text" size="30" class="inputmask currency" onkeyup="CalTotal();calcAmt();" />
		
			<input id="DEP_DTE4" name="DEP_DTE4" value="<?php if ($DepDate4==''){echo date("Y-m-d");}else{echo $DepDate4;} ?>" type="text" size="30" /><br />
			<script>
				new CalendarEightysix('DEP_DTE4', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
			</script>
			
		<label class='side'>Uang di laci</label>
		<input name="CSH_IN_DRWR" id="CSH_IN_DRWR" value="<?php echo $row['CSH_IN_DRWR']; ?>" type="text" size="30" class="inputmask currency" onkeyup="CalTotal();calcAmt();"/><br />
	
		<label class='side'>Total</label>
		<input name="TOT_AMT" id="TOT_AMT" value="<?php echo $row['TOT_AMT']; ?>" size="30" class="inputmask currency" readonly /><br />
		
			
		<label class='side'>Kas Register</label>
		<input name="CSH_REG" id="CSH_REG" value="<?php echo $row['CSH_REG']; ?>" type="text" size="30" onkeyup="calcAmt();" onchange="calcAmt()" class="inputmask currency"/><br />
	
		<label class='side'>Selisih</label>
		<input name="CORRTN_AMT" id="CORRTN_AMT" type="text" size="30"  value="<?php echo $row['CORRTN_AMT']; ?>" class="inputmask currency"/><br />

		<?php 
		if ($row['CSH_PRSN_NBR']==""){
			$Prsn = $_SESSION['personNBR'];
		} else {
			$Prsn = $row['CSH_PRSN_NBR'];
		}
		?>
		
		<label class='side'>Penyetor Kasir</label>
		<input type='text' size="30" value="<?php echo $row['CSH_PRSN_NAME'];?>" readonly /><br/>
		
		<input id="DEP_DTE" type="hidden" name="DEP_DTE" value="<?php if ($row['CSH_DAY_DTE']==''){echo date("Y-m-d");}else{echo $row['CSH_DAY_DTE'];} ?>" type="text" size="30" />
				
		
		<?php if (isset($row['CSH_DAY_NBR'])){ ?>
		<label class='side'>Penyetor Bank</label>
		<input type='text' size="30" value="<?php echo $row['DEP_NAME'];?>" readonly /><br/>
		<?php } ?>
		
		
		<?php 
		
		if($POSID == "") {
			if ($Security < 1){ ?>
				<label class='side'>Verifikasi</label>
				<div class='side' style='top:4px'><input name='VRFD_F' id='VRFD_F' type='checkbox' class='regular-checkbox' <?php if($row['VRFD_F']=="1"){echo "checked";} ?>/>&nbsp;<label for="VRFD_F"></label></div>
				<div class='combobox'></div>
		<?php 
			} 
		}
		?>
		
		<?php if ($row['VRFD_F']==1){ ?>
		<label class='side'>Di verifikasi oleh</label>
		<input type='text' size="30" value="<?php echo $row['VRFD_NAME'];?>" readonly/><br/>
		<?php } ?>

		<input class='process' type='submit' value='Simpan'/>

	</p>
	
	
<script type="text/javascript">

function val_default() {
	document.getElementById("CHK_AMT1").value = 0;
	document.getElementById("CHK_AMT2").value = 0;
	document.getElementById("CHK_AMT3").value = 0;
	document.getElementById("CHK_AMT4").value = 0;
	document.getElementById("CSH_AMT1").value = 0;
	document.getElementById("CSH_AMT2").value = 0;
	document.getElementById("CSH_AMT3").value = 0;
	document.getElementById("CSH_AMT4").value = 0;
	document.getElementById("CSH_IN_DRWR").value = 0;
	document.getElementById("TOT_AMT").value = 0;
	document.getElementById("CSH_REG").value = 0;
	document.getElementById("CORRTN_AMT").value = 0;
}

if(document.getElementById("TOT_AMT").value == "") {
	document.getElementById("CHK_AMT1").value = 0;
	document.getElementById("CHK_AMT2").value = 0;
	document.getElementById("CHK_AMT3").value = 0;
	document.getElementById("CHK_AMT4").value = 0;
	document.getElementById("CSH_AMT1").value = 0;
	document.getElementById("CSH_AMT2").value = 0;
	document.getElementById("CSH_AMT3").value = 0;
	document.getElementById("CSH_AMT4").value = 0;
	document.getElementById("CSH_IN_DRWR").value = 0;
	document.getElementById("TOT_AMT").value = 0;
	document.getElementById("CSH_REG").value = 0;
	document.getElementById("CORRTN_AMT").value = 0;
}

function CalTotal() {
	var TOT_AMT_PT	= document.getElementById("TOT_AMT_PT").value;
	var TOT_AMT_CV	= document.getElementById("TOT_AMT_CV").value;
	var TOT_AMT_PR	= document.getElementById("TOT_AMT_PR").value;
	
    var CHK_AMT1    = document.getElementById("CHK_AMT1").value;
    var CHK_AMT2    = document.getElementById("CHK_AMT2").value;
    var CHK_AMT3    = document.getElementById("CHK_AMT3").value;
	var CHK_AMT4    = document.getElementById("CHK_AMT4").value;
	
	var CSH_AMT1    = document.getElementById("CSH_AMT1").value;
	var CSH_AMT2    = document.getElementById("CSH_AMT2").value;
	var CSH_AMT3    = document.getElementById("CSH_AMT3").value;
	var CSH_AMT4    = document.getElementById("CSH_AMT4").value;
	
	var CSH_IN_DRWR	= document.getElementById("CSH_IN_DRWR").value;
	
	var TOT1		= parseInt(CHK_AMT1)+parseInt(CHK_AMT2)+parseInt(CHK_AMT3)+parseInt(CHK_AMT4)+parseInt(CSH_AMT1)+parseInt(CSH_AMT2)+parseInt(CSH_AMT3)+parseInt(CSH_AMT4)+parseInt(CSH_IN_DRWR);
	document.getElementById("TOT_AMT").value=TOT1;
	
}

</script>

<?php if ($POSID == "") { ?>
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
	   	}
		for (var selector in config) {
			jQuery(selector).chosen(config[selector]);
		}
	</script>
<?php } ?>
</form>

	
		<script type="text/javascript">
			$(document).ready(function () {
					
					$.ajax({
						type: "GET",
						url: 'get-deposit.php',
						data: 'POS_ID=<?php echo $POSID; ?>&CO_NBR=<?php echo $CoNbrDef; ?>',
						success: function (data) {								
							var json = $.parseJSON(data);										
												
							//alert(json);
							
							$('#OMSET_PT').val(json.OMSET_PT);
							$('#OMSET_CV').val(json.OMSET_CV);
							$('#OMSET_PR').val(json.OMSET_PR);
							$('#OMSET_AD').val(json.OMSET_AD);
							
							$('#TOT_AMT_PT').val(json.PT);
							$('#TOT_AMT_CV').val(json.CV);
							$('#TOT_AMT_PR').val(json.PR);
							$('#TOT_AMT_AD').val(json.AD);
						}
					})
				
			});
		</script>
		
</body>
</html>