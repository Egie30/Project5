<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";

	$HandOffTyp=$_GET['HND_OFF_TYP'];
	$Security=getSecurity($_SESSION['userID'],"DigitalPrint");
	//Process changes here
	if($_POST['HND_OFF_TYP']!="")
	{
		$j=syncTable("HND_OFF_TYP","HND_OFF_TYP","CMP",$CMP,$local,$cloud);

		$HandOffTyp=$_POST['HND_OFF_TYP'];

		$query="SELECT HND_OFF_TYP FROM $CMP.HND_OFF_TYP WHERE HND_OFF_TYP='$HandOffTyp'";
		$result=mysql_query($query,$cloud);
		$row=mysql_fetch_array($result);
		//echo $query;
		
		//Process add new
		if($row['HND_OFF_TYP']==""){
			//echo "Here";
			$query="INSERT INTO $CMP.HND_OFF_TYP (HND_OFF_TYP) VALUES ('".$HandOffTyp."')";
			$result=mysql_query($query,$cloud);
			echo $query;
			$query=str_replace($CMP,"CMP",$query);
			$result=mysql_query($query,$local);
			echo $query;
		}

		$query="UPDATE $CMP.HND_OFF_TYP
	   			SET HND_OFF_DESC='".$_POST['HND_OFF_DESC']."'
					WHERE HND_OFF_TYP='".$HandOffTyp."'";
		echo $query;
		$result=mysql_query($query,$cloud);
		$query=str_replace($CMP,"CMP",$query);
		echo $query;
		$result=mysql_query($query,$local);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>

<link rel="stylesheet" href="framework/combobox/chosen.css">
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<script type="text/javascript" src="framework/functions/default.js"></script>

<body>

<script>
	parent.document.getElementById('printDigitalTypeDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='print-digital-hand-off.php?DEL_L=<?php echo $HandOffTyp ?>';
		parent.document.getElementById('printDigitalTypeDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>


<?php
	$query="SELECT HND_OFF_TYP,HND_OFF_DESC
			FROM CMP.HND_OFF_TYP
			WHERE HND_OFF_TYP='".$HandOffTyp."'";
	//echo $query;
	$result=mysql_query($query,$local);
	$row=mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($HandOffTyp!="")) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('printDigitalTypeDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a></p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:600px" onSubmit="return checkform();">
	<p>
		<h2>
			<?php echo $row['HND_OFF_TYP'];if($row['HND_OFF_TYP']==""){echo "Baru";} ?>
		</h2>

		<input name="HND_OFF_TYP" value="<?php echo $row['HND_OFF_TYP'];if($row['HND_OFF_TYP']==""){echo "-1";} ?>" type="hidden" />

		<?php
		
		if($row['HND_OFF_TYP'] == '') {
		echo "<label>Kode</label><br />";
		echo "<input name='HND_OFF_TYP' value='".$row['HND_OFF_TYP']."' type='text' size='15' /><br />";
		}
		echo "<label>Serah Terima</label><br />";
		echo "<input name='HND_OFF_DESC' value='".$row['HND_OFF_DESC']."' type='text' size='80' /><br />";

	
			if($cloud!=false){
				echo "<input class='process' type='submit' value='Simpan'/>";
			}
		?>
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

	</p>
</form>

<script>liveReqInit('livesearch','liveRequestResults','print-digital-hand-off-ls.php','','mainResult');</script>

<!--
<script>
	<?php if($row['INV_NBR']!="") { ?>
	getContent('liveRequestResults',"print-digital-type-edit-disp.php?INV_NBR=<?php echo $row['INV_NBR']; ?>");
	<?php } ?>
	document.getElementById('liveRequestResults').style.display="";	
	document.getElementById('livesearch').value="<?php echo $row['INV_NBR']; ?>";
</script>
-->

</body>
</html>
