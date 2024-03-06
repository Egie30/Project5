<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/slack/slack.php";
	
	$Accounting 	= getSecurity($_SESSION['userID'],"Accounting");
		
	$LoaNbr		= $_GET['LOA_NBR'];
	$PrsnNbr 	= $_GET['PRSN_NBR'];
	$slack 		= false;
	$create     = false;
	
	//Process changes here
	if(($_POST['LOA_NBR']!="")&&($cloud!=false))
	{
		$j=syncTable("LOA","LOA_NBR","PAY",$PAY,$local,$cloud);

		$LoaNbr=$_POST['LOA_NBR'];

		//Process add new
		if($LoaNbr==-1){

			$query="SELECT COALESCE(MAX(LOA_NBR),0)+1 AS NEW_NBR FROM $PAY.LOA";
			$result=mysql_query($query,$cloud);
			$row=mysql_fetch_array($result);
			$LoaNbr=$row['NEW_NBR'];
			$query="INSERT INTO $PAY.LOA (LOA_NBR,CRT_TS,CRT_NBR) VALUES (".$LoaNbr.",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
			$result=mysql_query($query,$cloud);
			$query=str_replace($PAY,"PAY",$query);
			$result=mysql_query($query,$local);
			$create=true;
		}
		
		//Take care of nulls
		if($_POST['PRSN_NBR']!=""){$PrsnNbr="'".$_POST['PRSN_NBR']."'";}
		if($_POST['LOA_BEG_DTE']==""){$LoaBegDte="NULL";}else{$LoaBegDte="'".$_POST['LOA_BEG_DTE']."'";}
		if($_POST['LOA_END_DTE']==""){$LoaEndDte="NULL";}else{$LoaEndDte="'".$_POST['LOA_END_DTE']."'";}
		if($_POST['LOA_RSN']==""){$LoaRsn="NULL";}else{$LoaRsn="'".$_POST['LOA_RSN']."'";}
		
		$query="UPDATE $PAY.LOA SET
					PRSN_NBR = ".$PrsnNbr.",
					LOA_BEG_DTE=".$LoaBegDte.",
					LOA_END_DTE=".$LoaEndDte.",
					LOA_RSN=".$LoaRsn.",
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
				WHERE LOA_NBR=".$LoaNbr;
		// echo $query;
	   	$result=mysql_query($query,$cloud);
		$query=str_replace($PAY,"PAY",$query);
		$result=mysql_query($query,$local);
		//$queryData =$query;
		$slack =true;
	}

	if ($slack){
		$slackChannelName = "time-off";

		$query = "SELECT LOA.PRSN_NBR,
						 PPL.NAME,
						 COM.NAME AS COM_NAME,
						 LOA.LOA_BEG_DTE,
						 LOA.LOA_END_DTE,
						 LOA.LOA_RSN,
						 UPD.NAME AS UPD_NAME
				  FROM PAY.LOA 
				  LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR = LOA.PRSN_NBR
				  LEFT OUTER JOIN CMP.PEOPLE UPD ON UPD.PRSN_NBR = LOA.UPD_NBR
				  LEFT OUTER JOIN CMP.COMPANY COM ON COM.CO_NBR = PPL.CO_NBR
				  WHERE LOA_NBR=".$LoaNbr;
		$result = mysql_query($query, $local);
		$rowSl  = mysql_fetch_array($result);
		
		if ($create){
			$massage = "*".$rowSl['NAME']."* mengajukan *Leave Of Absence* tanggal *".$rowSl['LOA_BEG_DTE']."* - *".$rowSl['LOA_END_DTE']."* untuk:\n";
			$massage.= ">>>".$rowSl['LOA_RSN'];
		}else{
			$massage = "*".$rowSl['UPD_NAME']."* merubah *Leave Of Absence* atas nama *".$rowSl['NAME']."* pada tanggal *".$rowSl['LOA_BEG_DTE']."* - *".$rowSl['LOA_END_DTE']."*.";
		}
		
		slackChampion($massage,$slackChannelName);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" href="framework/combobox/chosen.css">
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />

<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
<script type="text/javascript">jQuery.noConflict();</script>
<!--<script type="text/javascript" src="framework/functions/default.js"></script>-->
<body>

<script> 
	
//jQuery.noConflict();
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
		parent.document.getElementById('content').src='leave-of-absence-detail.php?DEL_L=<?php echo $LoaNbr; ?>&PRSN_NBR=<?php echo $PrsnNbr;?>';
		parent.document.getElementById('DeleteData').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

<?php
	$query="SELECT 
				LOA_NBR,
				PRSN_NBR,
				LOA_BEG_DTE,
				LOA_END_DTE,
				LOA_RSN,
				LOA_F
			FROM PAY.LOA
			WHERE LOA_NBR=".$LoaNbr;
	//echo $query;
	$result=mysql_query($query,$local);
	$row=mysql_fetch_array($result);
	
	if (mysql_num_rows($result)==0){
		$row['LOA_BEG_DTE']=date('Y-m-d');
		$row['LOA_END_DTE']=date('Y-m-d');
	}

	if ($Accounting<8){
		$displayCmnt= "display:block;";
		$displaySimpan = "display:block;";
		$displayDelete = "display:block;";
	}else{

		if ($row['LOA_F']==1){
			$displaySimpan= "display:none;";
		}

		$displayDelete="display:none;";
	}

?>

<?php if(($Security==0)&&($LoaNbr!="" && $LoaNbr!=0)) { ?>
	<div class="toolbar-only" style="<?php echo $displayDelete;?>">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('DeleteData').style.display='block';parent.document.getElementById('fade').style.display='block'"><?php if(($cloud!=false)&&(paramCloud()==1)){echo '<span class="fa fa-trash toolbar" style="cursor:pointer"></span>';} ?></a></p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px" onSubmit="return checkform();">
	<p>
		<h2>
			Nomor Izin: <?php echo $row['LOA_NBR'];if($row['LOA_NBR']==0){echo "Baru";} ?>
		</h2>		
		<input name="LOA_NBR" value="<?php echo $row['LOA_NBR'];if($row['LOA_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label style="padding-bottom:5px;">Nama Pembuat</label><br />
		<div class='side'><select name="PRSN_NBR" class="chosen-select" style="width:325px">
		<?php
			if ($Accounting<8){
				if ($row['PRSN_NBR']==''){
					$PrsnNbr=$_SESSION['personNBR'];
				}else{
					$PrsnNbr=$row['PRSN_NBR'];
				}
			}else{
				if ($row['PRSN_NBR']==''){
					$PrsnNbr 		= $_SESSION['personNBR'];
					$whereClause	= " AND PRSN_NBR= ".$_SESSION['personNBR'];
				}else{
					$PrsnNbr 		= $row['PRSN_NBR'];
					$whereClause	= " AND PRSN_NBR= ".$row['PRSN_NBR'];
				}
			}
			$query="SELECT PRSN_NBR, NAME
					FROM CMP.PEOPLE 
					WHERE CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_PAYROLL) AND TERM_DTE IS NULL AND DEL_NBR=0 ".$whereClause."
					ORDER BY 2";
			genCombo($query,"PRSN_NBR","NAME",$PrsnNbr,"Kosong",$local);
		?>
		</select></div><div class="labelbox"></div><div class="labelbox"></div>
		<label>Tanggal</label><br />
		<input id="LOA_BEG_DTE" name="LOA_BEG_DTE" value="<?php echo $row['LOA_BEG_DTE']; ?>" type="text" size="15" style="margin-right: 5px;"/>
		<script>
			new CalendarEightysix('LOA_BEG_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script> - 

		<input id="LOA_END_DTE" name="LOA_END_DTE" value="<?php echo $row['LOA_END_DTE']; ?>" type="text" size="15" style="margin-left: 5px;"/><br />
		<script>
			new CalendarEightysix('LOA_END_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>

		<label style="padding-bottom:5px;">Alasan</label><br />
		<textarea name="LOA_RSN" rows="5" cols="50"><?php echo $row['LOA_RSN'];?></textarea><br />		

		<?php
			if(($cloud!=false)&&(paramCloud()==1)){	
				
				echo "<input class='process' type='submit' value='Simpan' style='".$displaySimpan."'/>";
			}
		?>
	
	</p>
</form>

<!--<script>liveReqInit('livesearch','liveRequestResults','change-log-ls.php','','mainResult');</script>-->

</body>

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
</html>
