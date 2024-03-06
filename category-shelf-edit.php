<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$CatNbr=$_GET['CAT_SHLF_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Inventory");	
	//Process changes here
	if($_POST['CAT_SHLF_NBR']!="")
	{
		$CatNbr=$_POST['CAT_SHLF_NBR'];

		//Process add new
		if($CatNbr==-1){

			$query="SELECT COALESCE(MAX(CAT_SHLF_NBR),0)+1 AS NEW_NBR FROM RTL.CAT_SHLF";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$CatNbr=$row['NEW_NBR'];
			$query="INSERT INTO RTL.CAT_SHLF (CAT_SHLF_NBR) VALUES (".$CatNbr.")";
			$result=mysql_query($query);
		}
		
		$query="UPDATE RTL.CAT_SHLF
	   			SET	CAT_SHLF_DESC='".$_POST['CAT_SHLF_DESC']."'
				WHERE CAT_SHLF_NBR='".$CatNbr."'";
		//echo $query;
	   	$result=mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/functions/default.js"></script>

<body>

<script>
	parent.document.getElementById('catShelfDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='category-shelf.php?DEL_L=<?php echo $CatNbr ?>';
		parent.document.getElementById('catShelfDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

<?php
	$query="SELECT CAT_SHLF_NBR,CAT_SHLF_DESC
			FROM RTL.CAT_SHLF
			WHERE CAT_SHLF_NBR=".$CatNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($CatNbr!="")) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('catShelfDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class="fa fa-trash toolbar" style="cursor:pointer"></span></a></p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px" onSubmit="return checkform();">
	<p>
		<h2>
			Nomor Kategori: <?php echo $row['CAT_SHLF_NBR'];if($row['CAT_SHLF_NBR']==0){echo "Baru";} ?>
		</h2>		
		<input name="CAT_SHLF_NBR" value="<?php echo $row['CAT_SHLF_NBR'];if($row['CAT_SHLF_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Deskripsi</label><br />
		<input name="CAT_SHLF_DESC" value="<?php echo $row['CAT_SHLF_DESC']; ?>" type="text" size="50" /><br />
			
		<input class="process" type="submit" value="Simpan"/>
	
	</p>
</form>

<script>liveReqInit('livesearch','liveRequestResults','category-list-ls.php','','mainResult');</script>

</body>
</html>
