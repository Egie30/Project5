<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$CalNbr=$_GET['CAL_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Inventory");
	//Process changes here
	if($_POST['CAL_NBR']!="")
	{
		$CalNbr=$_POST['CAL_NBR'];

		//Process add new
		if($CalNbr==-1)
		{
			$query="SELECT MAX(CAL_NBR)+1 AS NEW_NBR FROM CMP.CAL_LST";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			//echo $query;
			$CalNbr=$row['NEW_NBR'];
			$query="INSERT INTO CMP.CAL_LST (CAL_NBR) VALUES (".$CalNbr.")";
			$result=mysql_query($query);
		}
		//Take care of nulls
		//if($_POST['MOV_DTE']==""){$MovDte="NULL";}else{$MovDte="'".$_POST['MOV_DTE']."'";}
		//if($_POST['MOV_CNT']==""){$MovCnt="0";}else{$MovCnt=$_POST['MOV_CNT'];}
		if($_POST['CAL_ID']==""){$CalID="0";}else{$CalID=$_POST['CAL_ID'];}
		if($_POST['CAL_PRC_BLK']==""){$CalPrcBlk="0";}else{$CalPrcBlk=$_POST['CAL_PRC_BLK'];}
		if($_POST['CAL_PRC_PRN']==""){$CalPrcPrn="0";}else{$CalPrcPrn=$_POST['CAL_PRC_PRN'];}

		$query="UPDATE CMP.CAL_LST
	   			SET CAL_ID=".$CalID.",
	   				CO_NBR=".$_POST['CO_NBR'].",
	   				CAL_TYP='".$_POST['CAL_TYP']."',
	   				CAL_DESC='".$_POST['CAL_DESC']."',
	   				CAL_PRC_BLK=".$CalPrcBlk.",
	   				CAL_PRC_PRN=".$CalPrcPrn.",
					UPD_DTE=CURRENT_DATE,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE CAL_NBR=".$CalNbr;
		//echo $query;
		$result=mysql_query($query);
		$_GET['CAL_NBR']=$CalNbr;
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

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

<link rel="stylesheet" href="framework/combobox/chosen.css">

<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>

<script type="text/javascript">
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

<script type="text/javascript" src="framework/functions/default.js"></script>

</head>

<!-- Need to find out why we have to do this to make sure the 'ghost' height is not over the availabe screen height -->
<body style='height:100px'>

<script>
	parent.document.getElementById('addressDelete').onclick=
	function () {
		parent.document.getElementById('content').src='calendar-list.php?DEL_L=<?php echo $CalNbr ?>';
		parent.document.getElementById('addressDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

<?php
 if ($_GET['CAL_NBR'] != ''){
	$query="SELECT CAL_NBR,CO_NBR,CAL_ID,CAL_TYP,CAL_DESC,CAL_PRC_BLK,CAL_PRC_PRN
						        FROM CMP.CAL_LST
								WHERE CAL_NBR=".$CalNbr."";
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	}

?>

<?php if(($Security==0)&&($CalNbr!=0)) { ?>
<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('addressDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a>	
</div>
<?php } ?>
<form enctype="multipart/form-data" action="#" method="post" style="width:600px">
	<p>
		<h2>
			Nomor Inventory: <?php echo $row['CAL_NBR'];if($row['CAL_NBR']==""){echo "Baru";} ?>
		</h2>
	    <input name="CAL_NBR" value="<?php echo $row['CAL_NBR'];if($row['CAL_NBR']==""){echo "-1";} ?>" type="hidden" />
		<label>Perusahaan</label><br /><div class='labelbox'></div>
		<select name="CO_NBR" class="chosen-select" style='width:550px'>
			<?php
				$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM
							INNER JOIN
							CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
				genCombo($query,"CO_NBR","CO_DESC",$row['CO_NBR']);
			?>
		</select><br /><div class="combobox"></div>
		<label>Kode Urut Kalender</label><br />
		<input name="CAL_ID" value="<?php echo $row['CAL_ID']; ?>" size="15" /><br />
		<label>Tipe Kalender</label><br /><div class='labelbox'></div>
		<select name="CAL_TYP" class="chosen-select" style='width:300px'>
			<?php
				$query="SELECT CAL_TYP,CAL_TYP_DESC
							FROM CMP.CAL_TYP ORDER BY 2";
				genCombo($query,"CAL_TYP","CAL_TYP_DESC",$row['CAL_TYP']);
			?>
		</select><br /><div class="combobox"></div>
		<label>Deskripsi</label><br />
		<input name="CAL_DESC" value="<?php echo $row['CAL_DESC']; ?>" size="75" /><br />
		<label>Harga Blanko</label><br />
		<input name="CAL_PRC_BLK" value="<?php echo $row['CAL_PRC_BLK']; ?>" size="15" /><br />
		<label>Harga Cetak</label><br />
		<input name="CAL_PRC_PRN" value="<?php echo $row['CAL_PRC_PRN']; ?>" size="15" /><br />
		<input class="process" type="submit" value="Simpan"/>
	</p>
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
</form>

<!--<script>liveReqInit('livesearch','liveRequestResults','inventory-activity-edit-ls.php','','mainResult');</script>

<script>
	<?php// if($row['CAL_ID']!="") { ?>
	getContent('liveRequestResults',"inventory-activity-edit-disp.php?CAL_ID=<?php //echo $row['CAL_ID']; ?>");
	<?php //} ?>
	document.getElementById('liveRequestResults').style.display="";	
	document.getElementById('livesearch').value="<?php //echo $row['CAL_ID']; ?>";
</script>
-->
<div></div>
</body>
</html>
