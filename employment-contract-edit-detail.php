<?php
	// @header("Connection: close\r\n"); 
	error_reporting(0);
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";

	$Security = getSecurity($_SESSION['userID'],"Remote");

	//echo 'aaa'.$Security;

	$EmplCntrctNbr	= $_GET['EMPL_CNTRCT_NBR'];
	$PRSN_NBR       = $_GET['PRSN_NBR'];
	// echo $PRSN_NBR;
	$changed	= false;
	$addNew		= false;

	if ($_POST['EMPL_CNTRCT_NBR'] != "") 
	{			
		$EmplCntrctNbr = $_POST['EMPL_CNTRCT_NBR'];

		if($EmplCntrctNbr == -1)
		{
			$addNew=true;
		
			$query     		= " SELECT COALESCE(MAX(EMPL_CNTRCT_NBR),0)+1 AS NEW_NBR FROM $CMP.EMPL_CNTRCT";
			// echo $query;
			$result    		= mysql_query($query,$cloud);
			$row 	  		= mysql_fetch_array($result);
			$EmplCntrctNbr  = $row['NEW_NBR'];

			$query     		= " INSERT INTO $CMP.EMPL_CNTRCT (EMPL_CNTRCT_NBR) VALUES (".$EmplCntrctNbr.")";
			$result    		= mysql_query($query,$cloud);
		}

		$query  = " UPDATE $CMP.EMPL_CNTRCT SET
	   			 	EMPL_CNTRCT_TYP = '".$_POST['EMPL_CNTRCT_TYP']."',
	   				PRSN_NBR		= '".$PRSN_NBR."',
	   				BEG_DTE 		= '".$_POST['BEG_DTE']."',
					END_DTE 		= '".$_POST['END_DTE']."',
					DEL_NBR 		= '".$_SESSION['personNBR']."',
					CRT_TS 			= CURRENT_TIMESTAMP,
					CRT_NBR 		= '".$_SESSION['personNBR']."',
					UPD_TS 			= CURRENT_TIMESTAMP,
					UPD_NBR 		= '".$_SESSION['personNBR']."'
					WHERE EMPL_CNTRCT_NBR = '".$EmplCntrctNbr."' ";
		// echo $query;
	   	$result   = mysql_query($query,$cloud);


	   	$query   = "UPDATE $CMP.PEOPLE SET EMPL_CNTRCT = '".$_POST['EMPL_CNTRCT_TYP']."' WHERE PRSN_NBR=".$PRSN_NBR;
	   	// echo $query;
	   	$result  = mysql_query($query,$cloud);
		$query   = str_replace($CMP,"CMP",$query);
		$result  = mysql_query($query,$local);

	   	$changed  = true;
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
<script type="text/javascript" src="framework/functions/default.js"></script>
<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
<script type="text/javascript" src="framework/functions/default.js"></script>
    
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

<script type="text/javascript">jQuery.noConflict()</script>
<link rel="stylesheet" href="framework/combobox/chosen.css">

<body>

<?php
	if($changed)
	{
		echo "<script>";
		echo "parent.document.getElementById('content').contentDocument.getElementById('rightpane').contentDocument.getElementById('refresh-list').click();";
		echo "parent.document.getElementById('content').contentDocument.getElementById('rightpane').contentDocument.getElementById('refresh-tot').click();";
		echo "</script>";
	}

	if($addNew) { $EmplCntrctNbr = 0; }
?>

<span class='fa fa-times toolbar' style='margin-left:10px' onclick="pushFormOut();"></span></a>

<?php
	$queryz  = "SELECT EMPL.EMPL_CNTRCT_NBR,
					   EMPL.PRSN_NBR,
					   EMPL.EMPL_CNTRCT_TYP,
					   EMPL.BEG_DTE,
					   EMPL.END_DTE,
					   EMPL.DEL_NBR,
					   EMPL.CRT_TS,
					   EMPL.CRT_NBR,
					   EMPL.UPD_TS,
					   EMPL.UPD_NBR	
			   FROM $CMP.EMPL_CNTRCT EMPL
			   WHERE EMPL.EMPL_CNTRCT_NBR = ".$EmplCntrctNbr;
	// echo $query;
	$resultz = mysql_query($queryz,$cloud);
	$row     = mysql_fetch_array($resultz);

?>

<script>
	parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();
</script>


<form enctype="multipart/form-data" id="formchg" action="#" method="post" style="width:450px" onSubmit="return checkform();">
	<table>
		<input name="EMPL_CNTRCT_NBR" id="EMPL_CNTRCT_NBR" value="<?php echo $row['EMPL_CNTRCT_NBR'];if($row['EMPL_CNTRCT_NBR']==""){echo "-1";$addNew=true;} ?>" type="hidden" />
		<tr>
			<td style="width:100px">Jenis Kontrak
			<br>
				<select class="chosen-select" name="EMPL_CNTRCT_TYP" id="EMPL_CNTRCT_TYP" style="width:150px;">
			            <?php
			                $query = "SELECT EMPL_CNTRCT_TYP, EMPL_CNTRCT_DESC FROM CMP.EMPL_CNTRCT_TYP";
			                genCombo($query, "EMPL_CNTRCT_TYP", "EMPL_CNTRCT_DESC", $row['EMPL_CNTRCT_TYP']);  
			            ?>
	 			</select>
			</td>
		</tr>
		<tr>
			<td style='width:100px'>
			Tanggal Mulai
			<br>
				<input name="BEG_DTE" id="BEG_DTE" value="<?php echo $row['BEG_DTE'] ?>" type="text" style="width:110px;" />
				<script>
					new CalendarEightysix('BEG_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
				</script>
			</td>
		</tr>

		<tr>
			<td style='width:100px'>
			Tanggal Selesai
			<br>
				<input name="END_DTE" id="END_DTE" value="<?php echo $row['END_DTE'] ?>" type="text" style="width:110px;" />
				<script>
					new CalendarEightysix('END_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
				</script>
			</td>
		</tr>
	</table>

	<?php if($Security==5) { ?>
	<input class="process" id="process" type="submit" value="<?php if($addNew){echo 'Tambah';}else{echo 'Simpan';} ?>"/>
	<?php }?>

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
