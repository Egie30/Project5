<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$CatNbr=$_GET['CAT_SUB_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Inventory");	
	//Process changes here
	if(($_POST['CAT_SUB_NBR']!="")&&($cloud!=false))
	{			
		$j=syncTable("CAT_SUB","CAT_SUB_NBR","RTL",$RTL,$local,$cloud);

		$CatNbr=$_POST['CAT_SUB_NBR'];
		
		if ($_POST['CAT_TYP_NBR'] == "") { $CatTypNumber = "NULL"; } else { $CatTypNumber = $_POST['CAT_TYP_NBR']; } 
		if ($_POST['CD_SUB_NBR'] == "") { $CdSubNumber = "NULL"; } else { $CdSubNumber = $_POST['CD_SUB_NBR']; } 
		
	
		//Process add new
		if($CatNbr==-1){

			$query="SELECT COALESCE(MAX(CAT_SUB_NBR),0)+1 AS NEW_NBR FROM $RTL.CAT_SUB";
			$result=mysql_query($query,$cloud);
			$row=mysql_fetch_array($result);
			$CatNbr=$row['NEW_NBR'];
			$query="INSERT INTO $RTL.CAT_SUB (CAT_SUB_NBR) VALUES (".$CatNbr.")";
			$result=mysql_query($query,$cloud);
			$query=str_replace($RTL,"RTL",$query);
			$result=mysql_query($query,$local);
		}
		
		$query="UPDATE $RTL.CAT_SUB
	   			SET	CAT_SUB_DESC='".$_POST['CAT_SUB_DESC']."',
	   			CAT_NBR=".$_POST['CAT_NBR'].",
				CAT_TYP_NBR=".$CatTypNumber.",
				CD_SUB_NBR = ".$CdSubNumber.",
				UPD_TS=CURRENT_TIMESTAMP,
				UPD_NBR=".$_SESSION['personNBR']."
				WHERE CAT_SUB_NBR=".$CatNbr;
		
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

<link rel="stylesheet" href="framework/combobox/chosen.css">

<body>
<?php
	if($cloud==false){
		echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";			
	}	
?>
<script>
	parent.document.getElementById('catSubDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='category-sub.php?DEL_L=<?php echo $CatNbr ?>';
		parent.document.getElementById('catSubDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

<?php
	$query="SELECT SUB.CAT_SUB_NBR,
				SUB.CAT_SUB_DESC, 
				CAT.CAT_NBR,
				CAT.CAT_DESC,
				TYP.CAT_TYP_NBR,
				TYP.CAT_TYP, 
				CDSUB.CD_SUB_NBR,
				CONCAT(CDCAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', CDSUB.CD_SUB_DESC) AS ACC_DESC
			FROM RTL.CAT_SUB SUB 
				INNER JOIN RTL.CAT CAT ON CAT.CAT_NBR=SUB.CAT_NBR
				LEFT JOIN RTL.CAT_TYP TYP ON SUB.CAT_TYP_NBR = TYP.CAT_TYP_NBR
				LEFT JOIN RTL.ACCTG_CD_SUB CDSUB ON SUB.CD_SUB_NBR = CDSUB.CD_SUB_NBR
				LEFT JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=CDSUB.CD_NBR
				LEFT JOIN RTL.ACCTG_CD_CAT CDCAT ON CDCAT.CD_CAT_NBR=ACC.CD_CAT_NBR
			WHERE SUB.DEL_NBR=0 AND SUB.CAT_SUB_NBR=".$CatNbr;
	
	//echo $query;
	
	$result=mysql_query($query,$local);
	$row=mysql_fetch_array($result);
	
	//print_r($row);
?>

<?php if(($Security==0)&&($CatNbr!="")) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('catSubDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class="fa fa-trash toolbar" style="cursor:pointer"></span></a></p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px" onSubmit="return checkform();">
	<p>
		<h2>
			Nomor Kategori: <?php echo $row['CAT_SUB_NBR'];if($row['CAT_SUB_NBR']==0){echo "Baru";} ?>
		</h2>		
		<input name="CAT_SUB_NBR" value="<?php echo $row['CAT_SUB_NBR'];if($row['CAT_SUB_NBR']==""){echo "-1";} ?>" type="hidden" />
		<label>Kategori</label><br /><div class='labelbox'></div>
		<select name="CAT_NBR" class="chosen-select"><br />
		<?php
			$query="SELECT CAT_NBR,CAT_DESC
					FROM RTL.CAT";
			genCombo($query,"CAT_NBR","CAT_DESC",$row['CAT_NBR']);
		?>
		</select><br /><div class="combobox"></div>

		<label>Deskripsi</label><br />
		<input name="CAT_SUB_DESC" value="<?php echo $row['CAT_SUB_DESC']; ?>" type="text" size="50" /><br />
		
		<label>Tipe</label><br /><div class='labelbox'></div>
		<select name="CAT_TYP_NBR" class="chosen-select" style="width:400px"><br />
		<?php
			$query="SELECT CAT_TYP_NBR,CAT_TYP
					FROM RTL.CAT_TYP";
			genCombo($query,"CAT_TYP_NBR","CAT_TYP",$row['CAT_TYP_NBR'], "Pilih Tipe");
		?>
		</select><br /><div class="combobox"></div>
		
		<label>Akun</label><br /><div class='labelbox'></div>
		<select name="CD_SUB_NBR" class="chosen-select" style="width:400px"><br />
		<?php
			$query="SELECT SUB.CD_SUB_NBR,
						CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR,' ',CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC
					FROM RTL.ACCTG_CD_SUB SUB
					LEFT OUTER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
					LEFT OUTER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
						WHERE SUB.DEL_NBR=0 AND ACC.DEL_NBR = 0";
			genCombo($query,"CD_SUB_NBR","ACC_DESC",$row['CD_SUB_NBR'], "Pilih Akun");
		?>
		</select><br /><div class="combobox"></div>
		
		<?php
			if($cloud!=false){	
				echo '<input class="process" type="submit" value="Simpan"/>';
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

<script>liveReqInit('livesearch','liveRequestResults','category-list-ls.php','','mainResult');</script>

</body>
</html>
