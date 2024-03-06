<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/functions/dotmatrix.php";

	$RtlTypNbr=$_GET['RTL_TYP_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Stationery");
	//Process changes here
	if($_POST['RTL_TYP_NBR']!="")
	{
		$RtlTypNbr=$_POST['RTL_TYP_NBR'];
		$new=false;

		//Process add new
		if($RtlTypNbr=="-1"){
			$query="SELECT COALESCE(MAX(RTL_TYP_NBR),0)+1 AS NEW_NBR FROM CMP.RTL_TYP";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$RtlTypNbr=$row['NEW_NBR'];
			//echo $query;

			$query="INSERT INTO CMP.RTL_TYP (RTL_TYP_NBR) VALUES (".$RtlTypNbr.")";
			$result=mysql_query($query);
			$new=true;
			//echo $query;			
		}
		
		//Take care of nulls
		if($_POST['RTL_NBR']==""){$RtlNbr="NULL";}else{$RtlNbr=$_POST['RTL_NBR'];}
		if($_POST['RTL_PRC']==""){$RtlPrc="NULL";}else{$RtlPrc=$_POST['RTL_PRC'];}
		if($_POST['RTL_BRC']==""){$RtlBrc="C".leadZero($RtlTypNbr,6);}else{$RtlBrc=$_POST['RTL_BRC'];}

		$query="UPDATE CMP.RTL_TYP
	   			SET RTL_NBR=".$RtlNbr.",
	   				RTL_PRC=".$RtlPrc.",
	   				RTL_BRC='".$RtlBrc."',
	   				UPD_NBR=".$_SESSION['personNBR'].",
	   				UPD_TS=CURRENT_TIMESTAMP";
	   	if($new){$query.=",EFF_D=CURRENT_DATE,END_D='2199-12-31'";}
		$query.=" WHERE RTL_TYP_NBR=".$RtlTypNbr;
		//echo $query;
	   	$result=mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

<script type="text/javascript" src="framework/functions/default.js"></script>

<body>

<script>
	parent.document.getElementById('retailTypeDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='retail-type.php?DEL_L=<?php echo $RtlTypNbr ?>';
		parent.document.getElementById('retailTypeDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>


<?php
	$query="SELECT RTL_TYP_NBR,RTL_BRC,RTL_NBR,RTL_PRC
			FROM RTL_TYP WHERE RTL_TYP_NBR=".$RtlTypNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($RtlTypNbr!="")) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('retailTypeDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a></p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:600px" onSubmit="return checkform();">
	<p>
		<h2>
			<?php echo $row['PRN_DIG_DESC'];if($row['PRN_DIG_DESC']==""){echo "Nama Baru";} ?>
		</h2>

		<h3>
			<?php echo $row['RTL_TYP_NBR'];if($row['RTL_TYP_NBR']==""){echo "Nomor Baru";} ?>
		</h3>

		<input name="RTL_TYP_NBR" value="<?php echo $row['RTL_TYP_NBR'];if($row['RTL_TYP_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Barcode</label><br />
		<input name="RTL_BRC" value="<?php echo $row['RTL_BRC']; ?>" type="text" size="15" onKeyPress="return event.keyCode!=13" /><br />

		<label>Cari Barang</label><br />
		<input type="text" id="livesearch" /></input>
		<div style="border:1px solid #dddddd;background:#ffffff" id="liveRequestResults"></div>
		<script>liveReqInit();</script><br />
		<label>Kode Barang</label><br />
		<input id="RTL_NBR" name="RTL_NBR" size="20" value="<?php echo $row['RTL_NBR']; ?>" readonly></input><br />
	
		<label>Harga</label><br />
		<input name="RTL_PRC" value="<?php echo $row['RTL_PRC']; ?>" type="text" size="15" /><br />
	
		<input class="process" type="submit" value="Simpan"/>
	
	</p>
</form>

<script>liveReqInit('livesearch','liveRequestResults','retail-type-edit-ls.php','','mainResult');</script>

<script>
	<?php if($row['RTL_NBR']!="") { ?>
	getContent('liveRequestResults',"retail-type-edit-disp.php?RTL_NBR=<?php echo $row['RTL_NBR']; ?>");
	<?php } ?>
	document.getElementById('liveRequestResults').style.display="";	
	document.getElementById('livesearch').value="<?php echo $row['RTL_NBR']; ?>";
</script>

</body>
</html>
