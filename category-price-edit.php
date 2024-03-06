<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$CatNbr=$_GET['CAT_PRC_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Inventory");	
	//Process changes here
	if(($_POST['CAT_PRC_NBR']!="")&&($cloud!=false))
	{
		$j=syncTable("CAT_PRC","CAT_PRC_NBR","RTL",$RTL,$local,$cloud);

		$CatNbr=$_POST['CAT_PRC_NBR'];

		//Process add new
		if($CatNbr==-1){

			$query="SELECT COALESCE(MAX(CAT_PRC_NBR),0)+1 AS NEW_NBR FROM $RTL.CAT_PRC";
			$result=mysql_query($query,$cloud);
			$row=mysql_fetch_array($result);
			$CatNbr=$row['NEW_NBR'];
			$query="INSERT INTO $RTL.CAT_PRC (CAT_PRC_NBR) VALUES (".$CatNbr.")";
			$result=mysql_query($query,$cloud);
			$query=str_replace($RTL,"RTL",$query);
			$result=mysql_query($query,$local);
		}
		
		//Take care of nulls
		if($_POST['CAT_PRC_AMT']==""){$CatPrcAmt="NULL";}else{$CatPrcAmt=$_POST['CAT_PRC_AMT'];}
		if($_POST['CAT_PRC_PCT']==""){$CatPrcPct="NULL";}else{$CatPrcPct=$_POST['CAT_PRC_PCT'];}
		if($_POST['CAT_PRC_RND']==""){$CatPrcRnd="NULL";}else{$CatPrcRnd=$_POST['CAT_PRC_RND'];}
		if($_POST['CAT_PRC_LES']==""){$CatPrcLes="NULL";}else{$CatPrcLes=$_POST['CAT_PRC_LES'];}		
		
		$query="UPDATE $RTL.CAT_PRC
	   			SET	CAT_PRC_DESC='".$_POST['CAT_PRC_DESC']."',
	   			CAT_PRC_AMT=".$CatPrcAmt.",
	   			CAT_PRC_PCT=".$CatPrcPct.",
				CAT_PRC_RND=".$CatPrcRnd.",
				CAT_PRC_LES=".$CatPrcLes.",
				UPD_TS=CURRENT_TIMESTAMP,
				UPD_NBR=".$_SESSION['personNBR']."
				WHERE CAT_PRC_NBR=".$CatNbr;
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

<script>
	parent.document.getElementById('catPriceDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='category-price.php?DEL_L=<?php echo $CatNbr ?>';
		parent.document.getElementById('catPriceDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

<?php
	$query="SELECT CAT_PRC_NBR,CAT_PRC_DESC,CAT_PRC_AMT,CAT_PRC_PCT,CAT_PRC_RND,CAT_PRC_LES
			FROM RTL.CAT_PRC
			WHERE CAT_PRC_NBR=".$CatNbr;
	//echo $query;
	$result=mysql_query($query,$local);
	$row=mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($CatNbr!="")) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('catPriceDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><?php if(($cloud!=false)&&(paramCloud()==1)){echo '<span class="fa fa-trash toolbar" style="cursor:pointer"></span>';} ?></a></p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px" onSubmit="return checkform();">
	<p>
		<h2>
			Nomor Kategori: <?php echo $row['CAT_PRC_NBR'];if($row['CAT_PRC_NBR']==0){echo "Baru";} ?>
		</h2>		
		<input name="CAT_PRC_NBR" value="<?php echo $row['CAT_PRC_NBR'];if($row['CAT_PRC_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Deskripsi</label><br />
		<input name="CAT_PRC_DESC" value="<?php echo $row['CAT_PRC_DESC']; ?>" type="text" size="50" /><br />
			
		<label>Persen</label><br />
		<input name="CAT_PRC_PCT" value="<?php echo $row['CAT_PRC_PCT']; ?>" type="text" size="20" /><br />

		<label>Jumlah</label><br />
		<input name="CAT_PRC_AMT" value="<?php echo $row['CAT_PRC_AMT']; ?>" type="text" size="20" /><br />

		<label>Pembulatan</label><br />
		<input name="CAT_PRC_RND" value="<?php echo $row['CAT_PRC_RND']; ?>" type="text" size="20" /><br />
		
		<label>Pengurangan</label><br />
		<input name="CAT_PRC_LES" value="<?php echo $row['CAT_PRC_LES']; ?>" type="text" size="20" /><br />		
		<?php
			if(($cloud!=false)&&(paramCloud()==1)){	
				echo "<input class='process' type='submit' value='Simpan'/>";
			}
		?>
	
	</p>
</form>

<script>liveReqInit('livesearch','liveRequestResults','category-list-ls.php','','mainResult');</script>

</body>
</html>
