<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$OrdNbr = $_GET['ORD_NBR'];
	$type	= $_GET['TYP'];
	$beg 	= $_GET['BEG'];
	if($_POST['RSN_DEL_NBR']!=""){
		/*
		if ($_POST['RSN_DEL_NBR']==4){
			$Reason	= $_POST['RSN_DEL_DESC_ADD'];
		} else {
		*/
			$query_rsn	= "SELECT RSN_DEL_DESC FROM CMP.RSN_DEL WHERE RSN_DEL_NBR='".$_POST['RSN_DEL_NBR']."'";
			$result_rsn	= mysql_query($query_rsn);
			$row_rsn	= mysql_fetch_array($result_rsn);
			$Reason		= $row_rsn['RSN_DEL_DESC'];
		//}
		
		$query	= "INSERT INTO CMP.RSN_DEL_ORD VALUES('','$OrdNbr','".$_POST['RSN_DEL_NBR']."',CURRENT_TIMESTAMP, '".$_SESSION['personNBR']."')";
		mysql_query($query);
		
		if($beg=='LIST'){
		?>
			<script type="text/javascript">
			parent.parent.document.getElementById('content').src='print-digital-list.php?DEL=<?php echo $OrdNbr; ?>&STT=<?php echo $_GET['STT']; ?>&TYP=<?php echo $type; ?>&BEG=<?php echo $beg; ?>';
			parent.parent.document.getElementById('printDigitalReason').style.display='none';
			parent.parent.document.getElementById('fade').style.display='none';	
			</script>
		<?php	
		} else {
		?>
			<script type="text/javascript">
			parent.parent.document.getElementById('content').contentDocument.getElementById('leftpane').src='print-digital.php?DEL=<?php echo $OrdNbr; ?>&STT=<?php echo $_GET['STT']; ?>&TYP=<?php echo $type; ?>&BEG=<?php echo $beg; ?>';
			parent.parent.document.getElementById('printDigitalReason').style.display='none';
			parent.parent.document.getElementById('fade').style.display='none';
			</script>
		<?php
		}
	}
	
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/functions/default.js"></script>

<link rel="stylesheet" href="framework/combobox/chosen.css">

</head>

<body>

<img class="toolbar-left" style="cursor:pointer" src="img/close.png" onclick="parent.parent.document.getElementById('printDigitalReason').style.display='none';parent.parent.document.getElementById('fade').style.display='none';parent.parent.document.getElementById('printDigitalReasonContent').src='about:blank';"></a>

<form enctype="multipart/form-data" action="#" method="post" style="width:280px;">	
<table>
		<tr>
			<td>Alasan</td>
			<td>
				<select style="width:220px;" name="RSN_DEL_NBR" id="RSN_DEL_NBR" class="chosen-select"> 
				<?php
					$query="SELECT RSN_DEL_NBR, RSN_DEL_DESC FROM CMP.RSN_DEL";
					genCombo($query,"RSN_DEL_NBR","RSN_DEL_DESC","RSN_DEL_DESC","");
				?>
				</select> <?php //echo $query; ?>
			</td>
		</tr>
		<!--
		<tr style="display:none;" class="typeresult">
			<td>Alasan</td>
			<td><input name="RSN_DEL_DESC_ADD" type="text" size="25" /><br /></td>
		</tr>
		-->
		
	</table>
		<input class="process" type="submit" value="Simpan"/>
		<script src="framework/database/jquery.min.js" type="text/javascript"></script>
		<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
		<script type="text/javascript">
			var config = {
				'.chosen-select'           : {},
				'.chosen-select-deselect'  : {allow_single_deselect:true},
				'.chosen-select-no-single' : {disable_search_threshold:10},
				'.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
				'.chosen-select-width'     : {width:"95%"}
		   	}
			for (var selector in config) {
				$(selector).chosen(config[selector]);
			}
		</script>
		
		<script type="text/javascript">
		/*
			jQuery.noConflict();
			jQuery(document).ready(function() {
				jQuery('#RSN_DEL_NBR').change(function(){
					if(jQuery('#RSN_DEL_NBR').val() == '4'){  
						jQuery('.typeresult').fadeIn('fast');
					} else {
						jQuery('.typeresult').fadeOut('fast');
					}
				});
			});
		*/
		</script>
</form>
</body>
</html>
