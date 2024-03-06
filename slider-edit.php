<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$SldrNbr=$_GET['SLDR_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Inventory");	
	//Process changes here
	if($_POST['SLDR_NBR']!="")
	{
		$SldrNbr=$_POST['SLDR_NBR'];

		//Process add new
		if($SldrNbr==-1){

			$query="SELECT COALESCE(MAX(SLDR_NBR),0)+1 AS NEW_NBR FROM SLIDER";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$SldrNbr=$row['NEW_NBR'];
			$query="INSERT INTO SLIDER (SLDR_NBR) VALUES (".$SldrNbr.")";
			$result=mysql_query($query);
			
		}

 		if(!empty($_FILES['SLDR_IMG']['tmp_name'])){
			move_uploaded_file($_FILES['SLDR_IMG']['tmp_name'],"img/slider/".$_FILES['SLDR_IMG']['name']); 
			$file=",SLDR_IMG='".$_FILES['SLDR_IMG']['name']."'";
		}

		if($_POST['SLDR_STAT']=="on"){$SldrStat=1;}else{$SldrStat=0;}

		$query="UPDATE CMP.SLIDER
	   			SET	SLDR_NAME='".$_POST['SLDR_NAME']."',SLDR_STAT=$SldrStat $file WHERE SLDR_NBR=".$SldrNbr;
	   	$result=mysql_query($query);
	   	//echo $query;
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src="framework/functions/default.js"></script>

<body>

<script>
	parent.document.getElementById('sliderDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='slider.php?DEL_S=<?php echo $SldrNbr ?>';
		parent.document.getElementById('sliderDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

<?php
	
$query="SELECT SLDR_NBR,SLDR_NAME,SLDR_IMG,SLDR_STAT FROM CMP.SLIDER WHERE SLDR_NBR=".$SldrNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($SldrNbr!="")) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('sliderDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a></p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px" onSubmit="return checkform();">
	<p>
		<h2>
			Nomor Slide: <?php echo $row['SLDR_NBR'];if($row['SLDR_NBR']==0){echo "Baru";} ?>
		</h2>		
		<input name="SLDR_NBR" value="<?php echo $row['SLDR_NBR'];if($row['SLDR_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Deskripsi</label><br />
		<input name="SLDR_NAME" value="<?php echo $row['SLDR_NAME']; ?>" type="text" size="50" /><br />
		<?php
			/*
			if($row['SLDR_IMG']!=""){
				echo "<img src='img/slider/video/".$row['SLDR_IMG']."' style='width:200px;height:120px'><br /><br />";
			}
			*/
		?>
		<video id="hero-video" loop="" muted="" autoplay="" poster="img/background1.jpg" class="bg-video" width="200" height="120">
			<source src="img/slider/<?php echo $row['SLDR_IMG']; ?>" type="video/mp4">
		</video><br />
		
		<label>File (800x480px)</label><br />
		<div class='browse' onclick="document.getElementById('SLDR_IMG').click();">Browse ...<input class="browse" id="SLDR_IMG" name="SLDR_IMG" type="file" style="border:0px;" /></div>
		<div class="combobox"></div>
		<input name='SLDR_STAT' id='SLDR_STAT' type='checkbox' class='regular-checkbox' <?php if($row['SLDR_STAT']!="0"){echo "checked";} ?>/>&nbsp;<label for="SLDR_STAT"></label><label class='checkbox' for="SLDR_STAT" style='cursor:pointer'>Aktif</label><br /><div class="combobox"></div>
		<input class="process" type="submit" value="Simpan"/>
	
	</p>
</form>

<script>liveReqInit('livesearch','liveRequestResults','category-list-ls.php','','mainResult');</script>

</body>
</html>
