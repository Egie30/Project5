<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$LogNbr=$_GET['LOG_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Inventory");
	//Process changes here
	if($_POST['LOG_NBR']!="")
	{
		$LogNbr=$_POST['LOG_NBR'];

		//Process add new
		if($LogNbr==-1)
		{
			$query="SELECT COALESCE(MAX(LOG_NBR),0)+1 AS NEW_NBR FROM CMP.INV_LOG";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			//echo $query;
			$LogNbr=$row['NEW_NBR'];
			$query="INSERT INTO CMP.INV_LOG (LOG_NBR) VALUES (".$LogNbr.")";
			$result=mysql_query($query);
		}
		//Take care of nulls
		if($_POST['MOV_DTE']==""){$MovDte="NULL";}else{$MovDte="'".$_POST['MOV_DTE']."'";}
		if($_POST['MOV_CNT']==""){$MovCnt="0";}else{$MovCnt=$_POST['MOV_CNT'];}

		$query="UPDATE CMP.INV_LOG
				SET INV_NBR=".$_POST['INV_NBR'].",
				    MOV_DTE=".$MovDte.",
				    MOV_TYP='".$_POST['MOV_TYP']."',
				    WHSE_NBR=".$_POST['WHSE_NBR'].",
				    MOV_CNT=".$MovCnt.",
				    UPD_DTE=CURRENT_DATE,
				    UPD_NBR=".$_SESSION['personNBR']."
				    WHERE LOG_NBR=".$LogNbr;
		//echo $query;
		$result=mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
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
	parent.document.getElementById('inventoryActivityDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='inventory-activity.php?DEL_A=<?php echo $LogNbr ?>';
		parent.document.getElementById('inventoryActivityDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

<?php
	$query="SELECT LOG_NBR,MOV_DTE,MOV_TYP,WHSE_NBR,MOV_CNT,NAME,LOG.INV_NBR
			  FROM CMP.INV_LOG LOG JOIN CMP.INVENTORY INV ON LOG.INV_NBR=INV.INV_NBR
			  WHERE LOG_NBR=".$LogNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($LogNbr!=0)) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('inventoryActivityDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:600px">
	<p>
		<h2>
			<?php echo $row['NAME'];if($row['NAME']==""){echo "Nama Baru";} ?>
		</h2>
		<h3>
	  		Nomor Stock: <?php echo $row['LOG_NBR'];if($row['LOG_NBR']==""){echo "Baru";} ?>
	  	</h3>

	    <input name="LOG_NBR" value="<?php echo $row['LOG_NBR'];if($row['LOG_NBR']==""){echo "-1";} ?>" type="hidden" />
		<label>Tanggal aktivitas</label><br />
		<input id="MOV_DTE" name="MOV_DTE" size="20" value="<?php echo $row['MOV_DTE']; ?>"></input><br />
		<script>new CalendarEightysix('MOV_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
		<label>Cari Barang</label><br />
		<input type="text" id="livesearch" /></input>
		<div style="border:1px solid #dddddd;background:#ffffff" id="liveRequestResults"></div>
		<script>liveReqInit();</script><br />
		<label>Kode barang</label><br />
		<input id="INV_NBR" name="INV_NBR" size="20" value="<?php echo $row['INV_NBR']; ?>" readonly></input><br />
		<label>Aktivitas</label><br /><div class='labelbox'></div>
		<select name="MOV_TYP" class="chosen-select">
			<?php
				$query="SELECT MOV_TYP,MOV_DESC
					FROM CMP.INV_MOV ORDER BY 2";
				genCombo($query,"MOV_TYP","MOV_DESC",$row['MOV_TYP']);
			?>
		</select><br /><div class="combobox"></div>
		<label>Gudang</label><br /><div class='labelbox'></div>
		<select name="WHSE_NBR" class="chosen-select">
			<?php
				$query="SELECT WHSE_NBR,WHSE_DESC
						FROM CMP.WHSE_LOC ORDER BY 2";
				genCombo($query,"WHSE_NBR","WHSE_DESC",$row['WHSE_NBR']);
			?>
		</select><br /><div class="combobox"></div>
		<label>Jumlah</label><br />
		<input name="MOV_CNT" value="<?php echo $row['MOV_CNT']; ?>" size="15" /><br />
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

<script>liveReqInit('livesearch','liveRequestResults','inventory-activity-edit-ls.php','','mainResult');</script>

<script>
	<?php if($row['INV_NBR']!="") { ?>
	getContent('liveRequestResults',"inventory-activity-edit-disp.php?INV_NBR=<?php echo $row['INV_NBR']; ?>");
	<?php } ?>
	document.getElementById('liveRequestResults').style.display="";	
	document.getElementById('livesearch').value="<?php echo $row['INV_NBR']; ?>";
</script>

<div></div>
</body>
</html>