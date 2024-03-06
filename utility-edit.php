<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/functions/dotmatrix.php";
	
	$UtlNbr=$_GET['UTL_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Finance");

	$result=mysql_query("SELECT CO_NBR_DEF FROM NST.PARAM_LOC");
	$param=mysql_fetch_array($result);	
	//Process changes here

	if($_POST['UTL_NBR']!="")
	{
		$UtlNbr=$_POST['UTL_NBR'];

		//Process add new
		if($UtlNbr==-1)
		{
			$query="SELECT COALESCE(MAX(UTL_NBR),0)+1 AS NEW_NBR FROM CMP.UTILITY";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			//echo $query;
			$UtlNbr=$row['NEW_NBR'];
			$query="INSERT INTO CMP.UTILITY (UTL_NBR,CRT_TS,CRT_NBR) VALUES (".$UtlNbr.",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
			$result=mysql_query($query);
			//echo $query;
		
		}
		//Take care of nulls
		if($_POST['PRSN_NBR']==""){$PrsnNbr="NULL";}else{$PrsnNbr=$_POST['PRSN_NBR'];}
		if($_POST['CO_NBR']==""){$CoNbr="NULL";}else{$CoNbr=$_POST['CO_NBR'];}
		if($_POST['UTL_Q']==""){$UtlQ="0";}else{$UtlQ=$_POST['UTL_Q'];}
		if($_POST['UTL_AMT']==""){$UtlAmt="0";}else{$UtlAmt=$_POST['UTL_AMT'];}
		if($_POST['UTL_ADD']==""){$UtlAdd="0";}else{$UtlAdd=$_POST['UTL_ADD'];}
		if($_POST['TOT_SUB']==""){$TotSub="0";}else{$TotSub=$_POST['TOT_SUB'];}
		$query="UPDATE CMP.UTILITY
	   			SET UTL_TYP='".$_POST['UTL_TYP']."',
	   				UTL_DTE='".$_POST['UTL_DTE']."',
	   				UTL_CO_NBR=".$param['CO_NBR_DEF'].",
	   				PRSN_NBR=".$PrsnNbr.",
	   				CO_NBR=".$CoNbr.",
	   				REF_NBR_INT='".$_POST['REF_NBR_INT']."',
	   				REF_NBR_EXT='".$_POST['REF_NBR_EXT']."',
					UTL_Q=".$UtlQ.",
	   				UTL_AMT=".$UtlAmt.",
	   				UTL_ADD='".$UtlAdd."',
	   				TOT_SUB='".$TotSub."',
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE UTL_NBR=".$UtlNbr;
		$result=mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>

<script type="text/javascript">jQuery.noConflict()</script>
<link rel="stylesheet" href="framework/combobox/chosen.css">

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

</head>
<body>

<script>
	parent.document.getElementById('expenseDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='utility.php?DEL_A=<?php echo $UtlNbr ?>';
		parent.document.getElementById('expenseDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
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
		document.getElementById('TOT_SUB').value=getInt('UTL_Q')*getInt('UTL_AMT')+getInt('UTL_ADD');
	}
	
	//This needs to live somewhere in the table or parameter 
	function checkform()
	{
		var limit=<?php if($Security==0){echo "1000000000";}elseif($Security==1){echo "20000000";}elseif($Security==2){echo "250000";}else{echo "0";} ?>;
		if(document.getElementById('TOT_SUB').value>limit)
		{
			window.scrollTo(0,0);
			parent.document.getElementById('expenseOverLimit').style.display='block';parent.document.getElementById('fade').style.display='block';
			return false;
		}

		return true;
	}

</script>


<?php
	$query="SELECT UTL_NBR,UTL_DTE,UTL_TYP,UTL_CO_NBR,PRSN_NBR,CO_NBR,REF_NBR_INT,REF_NBR_EXT,UTL_Q,UTL_AMT,UTL_ADD,TOT_SUB
			FROM CMP.UTILITY UTL
			WHERE UTL_NBR=".$UtlNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<div class="toolbar-only">
	<?php if(($Security==0)&&($UtlNbr!=0)) { ?>
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('expenseDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class="fa fa-trash toolbar" style="cursor:pointer"></span></a></p>
	<?php } ?>
</div>

<form enctype="multipart/form-data" action="#" method="post" style="width:500px" onSubmit="return checkform();">
	<p>
		<h3>
			Nomor: <?php echo $row['UTL_NBR'];if($row['UTL_NBR']==""){echo "Baru";} ?>
		</h3>

		<input name="UTL_NBR" value="<?php echo $row['UTL_NBR'];if($row['UTL_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Tanggal</label><br />
		<input name="UTL_DTE" id="UTL_DTE" value="<?php if(!empty($row['UTL_DTE'])) {echo $row['UTL_DTE'];}else{ echo date("Y-m-d");} ?>" type="text" size="15" /><br />
		<script>
			new CalendarEightysix('UTL_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>

		<label>Jenis</label><br /><div class='labelbox'></div>
		<select name="UTL_TYP" class="chosen-select">
			<?php
				$query="SELECT UTL_TYP,UTL_DESC
						FROM CMP.UTL_TYP ORDER BY 2";
				genCombo($query,"UTL_TYP","UTL_DESC",$row['UTL_TYP']);
			?>
		</select><br /><div class="combobox"></div>

		<label>Nama Petugas</label><br /><div class='labelbox'></div>
		<select name="PRSN_NBR" style='width:450px' class="chosen-select">
			<?php
				$query="SELECT PRSN_NBR,NAME AS PRSN_DESC
						FROM CMP.PEOPLE PPL INNER JOIN
						CMP.CITY CIT ON PPL.CITY_ID=CIT.CITY_ID WHERE CO_NBR=".$param['CO_NBR_DEF']." AND TERM_DTE IS NULL ORDER BY 2";
				genCombo($query,"PRSN_NBR","PRSN_DESC",$row['PRSN_NBR'],"Kosong");
			?>
		</select><br /><div class="combobox"></div>

		<label>Client</label><br /><div class='labelbox'></div>
		<select name="CO_NBR" style='width:450px' class="chosen-select">
			<?php
				$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
						FROM CMP.COMPANY COM INNER JOIN
						CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
				genCombo($query,"CO_NBR","CO_DESC",$row['CO_NBR'],"Kosong");
			?>
		</select><br /><div class="combobox"></div>
	
		<label>Nomor referensi internal</label><br />
		<input name="REF_NBR_INT" value="<?php echo $row['REF_NBR_INT']; ?>" type="text" size="50" /><br />
	
		<label>Nomor referensi eksternal</label><br />
		<input name="REF_NBR_EXT" value="<?php echo $row['REF_NBR_EXT']; ?>" type="text" size="50" /><br />
	
		<label>Jumlah</label><br />
		<input name="UTL_Q" id="UTL_Q" value="<?php echo $row['UTL_Q']; ?>" size="15" onkeyup="calcAmt();" onchange="calcAmt();" /><br />
	
		<label>Nominal</label><br />
		<input name="UTL_AMT" id="UTL_AMT" value="<?php echo $row['UTL_AMT']; ?>" type="text" size="15" onkeyup="calcAmt();" /><br />
	
		<label>Tambahan biaya</label><br />
		<input name="UTL_ADD" id="UTL_ADD" value="<?php echo $row['UTL_ADD']; ?>" type="text" size="15" onkeyup="calcAmt();" /><br />
	
		<label>Total</label><br />
		<input name="TOT_SUB" id="TOT_SUB" value="<?php echo $row['TOT_SUB']; ?>" type="text" size="15" onkeyup="calcAmt();" readonly /><br />
		
		<?php
			//if($Security <=1){
				echo "<input class='process' type='submit' value='Bayar'/>";
			//}
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
		   	}
			for (var selector in config) {
				jQuery(selector).chosen(config[selector]);
			}
		</script>
	</form>
</body>
</html>