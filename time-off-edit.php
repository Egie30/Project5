<?php error_reporting(0);
	include "framework/database/connect-cloud.php";
	include_once "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/slack/slack.php";
	
	$Accounting 	= getSecurity($_SESSION['userID'],"Accounting");
	$TmOffNbr = $_GET['TM_OFF_NBR'];
	// $slack 		= false;

	//Process changes here
	if(($_POST['TM_OFF_NBR']!="")&&($cloud!=false)){
		$j=syncTable("TM_OFF","TM_OFF_NBR","PAY",$PAY,$local,$cloud);

		$TmOffNbr=$_POST['TM_OFF_NBR'];

		//Process add newcoalesce
		if($TmOffNbr==-1)
		{
			$query="SELECT COALESCE(MAX(TM_OFF_NBR),0)+1 AS NEW_NBR FROM $PAY.TM_OFF";
			$result=mysql_query($query,$cloud);
			$row=mysql_fetch_array($result);
			$TmOffNbr=$row['NEW_NBR'];
			$query="INSERT INTO $PAY.TM_OFF (TM_OFF_NBR,CRT_NBR,CRT_TS) VALUES 
					(".$TmOffNbr.",".$_SESSION['personNBR'].",CURRENT_TIMESTAMP)";
			$result=mysql_query($query,$cloud);
			$query=str_replace($PAY,"PAY",$query);
			$result=mysql_query($query,$local);
		}
		
		//Take care of nulls
		if ($_POST['PRSN_NBR']==''){$PrsnNbr='NULL';}else{$PrsnNbr=$_POST['PRSN_NBR'];}
		if ($_POST['TM_OFF_BEG_DTE']==''){$TmOffBegDte='NULL';}else{$TmOffBegDte="'".$_POST['TM_OFF_BEG_DTE']."'";}
		if ($_POST['TM_OFF_END_DTE']==''){$TmOffEndDte='NULL';}else{$TmOffEndDte="'".$_POST['TM_OFF_END_DTE']."'";}
		if ($_POST['TM_OFF_RSN']==''){$TmOffRsn='NULL';}else{$TmOffRsn="'".mysql_escape_string($_POST['TM_OFF_RSN'])."'";}
		
		$query="UPDATE $PAY.TM_OFF
	   			SET PRSN_NBR=".$PrsnNbr.",
	   				TM_OFF_BEG_DTE=".$TmOffBegDte.",
	   				TM_OFF_END_DTE=".$TmOffEndDte.",
					TM_OFF_RSN=".$TmOffRsn.",
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE TM_OFF_NBR=".$TmOffNbr;
		//echo $query;
	   	$result=mysql_query($query,$cloud);
		$query=str_replace($PAY,"PAY",$query);
		$result=mysql_query($query,$local);
		// $slack =true;
	}

		$query="SELECT 
				TM_OFF_NBR,
				TMO.PRSN_NBR,
				TM_OFF_BEG_DTE,
				TM_OFF_END_DTE,
				TM_OFF_RSN,
				TM_OFF_F,
				PPL.NAME AS PPL_NAME,
				COM.NAME AS COM_NAME,
				CRT.NAME AS CRT_NAME,
				UPD.NAME AS UPD_NAME
			FROM PAY.TM_OFF TMO
			LEFT OUTER JOIN CMP.PEOPLE PPL ON TMO.PRSN_NBR=PPL.PRSN_NBR
			LEFT OUTER JOIN CMP.PEOPLE CRT ON CRT.PRSN_NBR=TMO.CRT_NBR
			LEFT OUTER JOIN CMP.PEOPLE UPD ON UPD.PRSN_NBR=TMO.UPD_NBR
			LEFT OUTER JOIN CMP.COMPANY COM ON PPL.CO_NBR=COM.CO_NBR 
			WHERE TM_OFF_NBR=".$TmOffNbr;
	$result=mysql_query($query,$local);
	$row=mysql_fetch_array($result);
	
	// echo $query;

	if (!($Accounting<8)){
		if ($row['TM_OFF_F']!='' && $row['TM_OFF_F']==1){
			$display= "display:none;";
		}
	}

	//Process Slack Webhook
    // if($slack){
    // 	$slackChannelName = "time-off";
    // 	if ($_POST['TM_OFF_NBR'] == -1){
    // 		$message="*".$row['PPL_NAME']."* mengajukan *time off* tanggal *".$row['TM_OFF_BEG_DTE']."* - *".$row['TM_OFF_END_DTE']."* untuk: \n";
    // 		$message.=">>>".$row['TM_OFF_RSN'];
    // 	}else{
    // 		$message="*".$row['UPD_NAME']."* mengubah *Time off* atas nama *".$row['PPL_NAME']."* pada tanggal *".$row['TM_OFF_BEG_DTE']."* - *".$row['TM_OFF_END_DTE']."*.";
    // 	}
	// //echo $slackChannelName;
    //     slackChampion($message,$slackChannelName);
    // }
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

<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>

<script type="text/javascript">jQuery.noConflict();</script>

<script> 
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

<script>
	parent.document.getElementById('DeleteDataYes').onclick=
	function () { 
		parent.document.getElementById('content').src='time-off-detail.php?DEL_A=<?php echo $TmOffNbr; ?>&PRSN_NBR=<?php echo $row['PRSN_NBR'];?>';
		parent.document.getElementById('DeleteData').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>
</head>

<body>
<?php if(($Accounting<8)&&($TmOffNbr!=0)) { ?>
	<?php if ($row['TM_OFF_F']!=1){?>
		<div class="toolbar-only">
			<p class="toolbar-left"><?php if(($cloud!=false)&&(paramCloud()==1)){ ?><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('DeleteData').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a><?php } ?></p>
		</div>
	<?php } ?>
<?php } ?>
			
<form enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="return checkform();" id="signup" autocomplete="off">
	<p>
		<h2>
			<?php
				if((!$cloud)&&($row['TM_OFF_NBR']=="")){
					echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";
				}
			?>
		</h2>
		<h3>
			Nomor : <?php echo $row['TM_OFF_NBR'];if($row['TM_OFF_NBR']==""){echo "Baru";} ?>
		</h3>
		
		<input name="TM_OFF_NBR" value="<?php echo $row['TM_OFF_NBR'];if($row['TM_OFF_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label style="padding-bottom:5px;">Nama Karyawan</label><br/>
		<div class='side'><select name="PRSN_NBR" class="chosen-select" style="width:325px">
		<?php
			if ($Accounting<8){
				if ($row['PRSN_NBR']==''){
					$row['PRSN_NBR']=$_SESSION['personNBR'];
				}
				$whereClause=" AND EMPL_CNTRCT=5 ";
			}else{
				$whereClause=" AND PRSN_NBR=".$_SESSION['personNBR'];
			}
			$query="SELECT PRSN_NBR, NAME 
					FROM CMP.PEOPLE 
					WHERE TERM_DTE IS NULL AND DEL_NBR=0 
						AND CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_COMPANY)
						".$whereClause."
					ORDER BY NAME ASC";
			genCombo($query,"PRSN_NBR","NAME",$row['PRSN_NBR'],"",$local);
		?>
		</select></div><div class="labelbox"></div><div class="labelbox"></div>
		
		<label>Tanggal Cuti</label><br/>
		<input id="TM_OFF_BEG_DTE" name="TM_OFF_BEG_DTE" value="<?php echo $row['TM_OFF_BEG_DTE']; ?>" type="text" size="15" style="margin-right: 5px;" onkeyup="checkDate();"/>
		<script>
			new CalendarEightysix('TM_OFF_BEG_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script> - 
		<input id="TM_OFF_END_DTE" name="TM_OFF_END_DTE" value="<?php echo $row['TM_OFF_END_DTE']; ?>" type="text" size="15" style="margin-left: 5px;"/>
		<span style="padding-left: 10px;display: none;" id="warning"><span class="fa fa-warning" style="font-size:14px;padding-right: 5px;"></span><span id="message-warning"></span></span><br />
		<script>
			new CalendarEightysix('TM_OFF_END_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>

		<label style="padding-bottom:5px;">Alasan</label><br/>
		<textarea id="TM_OFF_RSN" name="TM_OFF_RSN" rows="4" cols="50" ><?php echo $row['TM_OFF_RSN']; ?></textarea><br />
		
		<?php
			if ($row['TM_OFF_F']!=1){
				if(($cloud!=false)&&(paramCloud()==1)){
						echo "<input  id='submit_button'  class='process submit_button' type='submit' value='Simpan' style='".$display."'/>";
				}
			}
		?>
		
	</p>
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

		var dNow = new Date();
		dNow.setDate(dNow.getDate()-1);

		var tmOffBegDte = new CalendarEightysix('TM_OFF_BEG_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true }),
			tmOffEndDte = new CalendarEightysix('TM_OFF_END_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });

		tmOffEndDte.addEvent('change', function(date) {
			var diff = new Date(tmOffEndDte.getDate() - tmOffBegDte.getDate());
			var days = (diff/1000/60/60/24)+1;
			var diff_cur = new Date(tmOffBegDte.getDate() - dNow);
			var days_cur = parseInt(diff_cur/1000/60/60/24);
			
			if (days>=4){
				jQuery('#message-warning').html("Cuti tidak bisa diambil 3 hari berturut-turut.");
				jQuery('span#warning').show();
				jQuery(':input[type="submit"]').prop('disabled', true);
				jQuery(':input[type="submit"]').hide();
			}
			else if(days_cur<3){
				jQuery('#message-warning').html("Pengajuan cuti hanya dapat dilakukan 3 hari sebelum tanggal cuti.");
				jQuery('span#warning').show();
				jQuery(':input[type="submit"]').prop('disabled', true);
				jQuery(':input[type="submit"]').hide();
			}
			else{
				jQuery('span#warning').hide();
				jQuery(':input[type="submit"]').prop('disabled', false);
				jQuery(':input[type="submit"]').show();
			}
			
		});

		tmOffBegDte.addEvent('change', function(date) {
			var diff = new Date(tmOffEndDte.getDate() - tmOffBegDte.getDate());
			var days = (diff/1000/60/60/24)+1;
			var diff_cur = new Date(tmOffBegDte.getDate() - dNow);
			var days_cur = parseInt(diff_cur/1000/60/60/24);
			
			if (days>=4){
				jQuery('#message-warning').html("Cuti tidak bisa diambil 3 hari berturut-turut.");
				jQuery('span#warning').show();
				jQuery(':input[type="submit"]').prop('disabled', true);
				jQuery(':input[type="submit"]').hide();
			}
			
			else if(days_cur <3){
				jQuery('#message-warning').html("Pengajuan cuti hanya dapat dilakukan 3 hari sebelum tanggal cuti.");
				jQuery('span#warning').show();
				jQuery(':input[type="submit"]').prop('disabled', true);
				jQuery(':input[type="submit"]').hide();
			}
			
			else{
				jQuery('span#warning').hide();
				jQuery(':input[type="submit"]').prop('disabled', false);
				jQuery(':input[type="submit"]').show();
			}
		});
		
		jQuery("#TM_OFF_BEG_DTE,#TM_OFF_END_DTE").keyup(function(){
    		var begDt = new Date(jQuery("#TM_OFF_BEG_DTE").val());
    		var endDt = new Date(jQuery('#TM_OFF_END_DTE').val());

    		var diff = new Date(endDt - begDt);
			var days = (diff/1000/60/60/24)+1;
			var diff_cur = new Date(begDt - dNow);
			var days_cur = parseInt(diff_cur/1000/60/60/24);
			
			if (days>=4){
				jQuery('#message-warning').html("Cuti tidak bisa diambil 3 hari berturut-turut.");
				jQuery('span#warning').show();
				jQuery(':input[type="submit"]').prop('disabled', true);
				jQuery(':input[type="submit"]').hide();
			}
			
			else if(days_cur<3){
				jQuery('#message-warning').html("Pengajuan cuti hanya dapat dilakukan 3 hari sebelum tanggal cuti.");
				jQuery('span#warning').show();
				jQuery(':input[type="submit"]').prop('disabled', true);
				jQuery(':input[type="submit"]').hide();
			}
			
			else{
				jQuery('span#warning').hide();
				jQuery(':input[type="submit"]').prop('disabled', false);
				jQuery(':input[type="submit"]').show();
			}
		});

	</script>
</form>
<div></div>

</body>
</html>