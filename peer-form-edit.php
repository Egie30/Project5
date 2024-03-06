<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$Accounting 	= getSecurity($_SESSION['userID'],"Accounting");
		
	$PeerFormNbr	= $_GET['PEER_FORM_NBR'];
	$PrsnNbr 		= $_GET['PRSN_NBR'];
	
	//Process changes here
	if(($_POST['PEER_FORM_NBR']!="")&&($cloud!=false))
	{
		$j=syncTable("PEER_FORM","PEER_FORM_NBR","PAY",$PAY,$local,$cloud);

		$PeerFormNbr=$_POST['PEER_FORM_NBR'];

		//Process add new
		if($PeerFormNbr==-1){

			$query="SELECT COALESCE(MAX(PEER_FORM_NBR),0)+1 AS NEW_NBR FROM $PAY.PEER_FORM";
			$result=mysql_query($query,$cloud);
			$row=mysql_fetch_array($result);
			$PeerFormNbr=$row['NEW_NBR'];
			$query="INSERT INTO $PAY.PEER_FORM (PEER_FORM_NBR,CRT_TS,CRT_NBR) VALUES (".$PeerFormNbr.",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
			$result=mysql_query($query,$cloud);
			$query=str_replace($PAY,"PAY",$query);
			$result=mysql_query($query,$local);
		}
		
		//Take care of nulls
		if($_POST['PEER_TYP']==""){$PeerTyp="NULL";}else{$PeerTyp=$_POST['PEER_TYP'];}
		if($_POST['PEER_DTE']==""){$PeerDte="NULL";}else{$PeerDte="'".$_POST['PEER_DTE']."'";}
		if($_POST['PRSN_NBR']==""){$PrsnNbr="NULL";}else{$PrsnNbr=$_POST['PRSN_NBR'];}
		if($_POST['PEER_RSN']==""){$PeerRsn="NULL";}else{$PeerRsn="'".$_POST['PEER_RSN']."'";}
		if($_POST['PEER_CMNT']==""){$PeerCmnt="NULL";}else{$PeerCmnt="'".$_POST['PEER_CMNT']."'";}		
		
		$query="UPDATE $PAY.PEER_FORM SET	
					PEER_TYP=".$PeerTyp.",
					PEER_DTE=".$PeerDte.",
					PRSN_NBR=".$PrsnNbr.",
					PEER_RSN=".$PeerRsn.",
					PEER_CMNT=".$PeerCmnt.",
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
				WHERE PEER_FORM_NBR=".$PeerFormNbr;
		//echo $query;
	   	$result=mysql_query($query,$cloud);
		$query=str_replace($PAY,"PAY",$query);
		$result=mysql_query($query,$local);
		//$queryData =$query;
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
		parent.document.getElementById('content').src='peer-form-detail.php?DEL_L=<?php echo $PeerFormNbr; ?>&PRSN_NBR=<?php echo $PrsnNbr;?>';
		parent.document.getElementById('DeleteData').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

<?php
	$query="SELECT 
				PEER_FORM_NBR,
				PEER_TYP,
				PEER_DTE,
				PRSN_NBR,
				PEER_RSN,
				PEER_CMNT,
				PEER_APV_F
			FROM PAY.PEER_FORM
			WHERE PEER_FORM_NBR=".$PeerFormNbr;
	//echo $query;
	$result=mysql_query($query,$local);
	$row=mysql_fetch_array($result);
	
	if (mysql_num_rows($result)==0){
		$row['PEER_DTE']=date('Y-m-d');
	}

	if ($Accounting<8){
		$displayCmnt= "display:block;";
		$displaySimpan = "display:block;";
		$displayDelete = "display:block;";
	}else{
		if ($row['PEER_CMNT']!='' || empty($row['PEER_CMNT'])){
			$displayCmnt="display:none";
		}

		if ($row['PEER_APV_F']==1){
			$displaySimpan= "display:none;";
		}

		$displayDelete="display:none;";
	}

?>

<?php if(($Security==0)&&($PeerFormNbr!="" && $PeerFormNbr!=0)) { ?>
	<div class="toolbar-only" style="<?php echo $displayDelete;?>">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('DeleteData').style.display='block';parent.document.getElementById('fade').style.display='block'"><?php if(($cloud!=false)&&(paramCloud()==1)){echo '<span class="fa fa-trash toolbar" style="cursor:pointer"></span>';} ?></a></p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px" onSubmit="return checkform();">
	<p>
		<h2>
			Nomor Peer: <?php echo $row['PEER_FORM_NBR'];if($row['PEER_FORM_NBR']==0){echo "Baru";} ?>
		</h2>		
		<input name="PEER_FORM_NBR" value="<?php echo $row['PEER_FORM_NBR'];if($row['PEER_FORM_NBR']==""){echo "-1";} ?>" type="hidden" />
			
		<label style="padding-bottom:5px;">Jenis Peer</label><br />
		<div class='side'><select name="PEER_TYP" class="chosen-select" style="width:325px">
		<?php
			$query="SELECT PEER_TYP_NBR, PEER_TYP_DESC
					FROM PAY.PEER_TYP ORDER BY 1";
			genCombo($query,"PEER_TYP_NBR","PEER_TYP_DESC",$row['PEER_TYP'],"",$local);
		?>
		</select></div><div class="labelbox"></div><div class="labelbox"></div>

		<label style="padding-bottom:5px;">Nama Penerima</label><br />
		<div class='side'><select name="PRSN_NBR" class="chosen-select" style="width:325px">
		<?php
			$query="SELECT PRSN_NBR, NAME
					FROM CMP.PEOPLE 
					WHERE CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_PAYROLL) AND TERM_DTE IS NULL AND DEL_NBR=0
					ORDER BY 2";
			genCombo($query,"PRSN_NBR","NAME",$row['PRSN_NBR'],"Kosong",$local);
		?>
		</select></div><div class="labelbox"></div><div class="labelbox"></div>
		
		<label>Tanggal</label><br />
		<input id="PEER_DTE" name="PEER_DTE" value="<?php echo $row['PEER_DTE']; ?>" type="text" size="15" /><br />
		<script>
			new CalendarEightysix('PEER_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>

		<label style="padding-bottom:5px;">Alasan</label><br />
		<textarea name="PEER_RSN" rows="5" cols="50"><?php echo $row['PEER_RSN'];?></textarea><br />		

		<label style="<?php echo $displayCmnt;?>">Komentar</label><br/>
		<textarea style="<?php echo $displayCmnt;?>" name="PEER_CMNT" rows="5" cols="50"><?php echo $row['PEER_CMNT'];?></textarea>

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
