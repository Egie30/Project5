<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$CatNbr=$_GET['CAT_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Inventory");	
	//Process changes here
	if(($_POST['CAT_NBR']!="")&&($cloud!=false))
	{
		$j=syncTable("CAT","CAT_NBR","RTL",$RTL,$local,$cloud);
	
		$CatNbr=$_POST['CAT_NBR'];

		//Process add new
		if($CatNbr==-1){

			$query="SELECT COALESCE(MAX(CAT_NBR),0)+1 AS NEW_NBR FROM $RTL.CAT";
			$result=mysql_query($query,$cloud);
			$row=mysql_fetch_array($result);
			$CatNbr=$row['NEW_NBR'];
			$query="INSERT INTO $RTL.CAT (CAT_NBR) VALUES (".$CatNbr.")";
			$result=mysql_query($query,$cloud);
			$query=str_replace($RTL,"RTL",$query);
			$result=mysql_query($query,$local);
		}
		
		$query="UPDATE $RTL.CAT
	   			SET	CAT_DESC='".$_POST['CAT_DESC']."',
				UPD_TS=CURRENT_TIMESTAMP,
				UPD_NBR=".$_SESSION['personNBR']."
	   			WHERE CAT_NBR='".$CatNbr."'";
		//echo $query;
	   	$result=mysql_query($query,$cloud);
		$query=str_replace($RTL,"RTL",$query);
		$result=mysql_query($query,$local);
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
<?php
	if($cloud==false){
		echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";			
	}	
?>

<script>
	parent.document.getElementById('catDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='category.php?DEL_L=<?php echo $CatNbr ?>';
		parent.document.getElementById('catDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

<?php
	$query="SELECT CAT_NBR,CAT_DESC
			FROM RTL.CAT
			WHERE CAT_NBR=".$CatNbr;
	//echo $query;
	$result=mysql_query($query,$local);
	$row=mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($CatNbr!="")) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('catDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><?php if(($cloud!=false)&&(paramCloud()==1)){echo '<span class="fa fa-trash toolbar" style="cursor:pointer"></span>'; }?></a></p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px" onSubmit="return checkform();">
	<p>
		<h2>
			Nomor Kategori: <?php echo $row['CAT_NBR'];if($row['CAT_NBR']==0){echo "Baru";} ?>
		</h2>		
		<input name="CAT_NBR" value="<?php echo $row['CAT_NBR'];if($row['CAT_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Deskripsi</label><br />
		<input name="CAT_DESC" value="<?php echo $row['CAT_DESC']; ?>" type="text" size="50" /><br />
		<?php
			if(($cloud!=false)&&(paramCloud()==1)){	
				echo '<input class="process" type="submit" value="Simpan"/>';
			}
		?>
	</p>
</form>

<script>liveReqInit('livesearch','liveRequestResults','category-list-ls.php','','mainResult');</script>

</body>
</html>
