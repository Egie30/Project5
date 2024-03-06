<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/functions/dotmatrix.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	
	$Security=getSecurity($_SESSION['userID'],"Inventory");
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");
	$Acc=getSecurity($_SESSION['userID'],"Accounting");
	$OrdNbr	= $_GET['CAP_NBR'];
	$IvcTyp	= $_GET['IVC_TYP'];
	$type	= $_GET['TYP'];
	
	//Process changes here
	if($_POST['CAP_NBR']!="")
	{
		$OrdNbr=$_POST['CAP_NBR'];
		
		//Take care of nulls and timestamps
		if($_POST['SHP_CO_NBR']==""){$ShpCoNbr="NULL";}else{$ShpCoNbr=$_POST['SHP_CO_NBR'];}
		if($_POST['RCV_CO_NBR']==""){$RcvCoNbr="NULL";}else{$RcvCoNbr=$_POST['RCV_CO_NBR'];}
		if($_POST['RCV_PRSN_NBR']==""){$RcvPrsnNbr="NULL";}else{$RcvPrsnNbr=$_POST['RCV_PRSN_NBR'];}
		if($_POST['TOT_AMT']==""){$TotAmt=0;}else{$TotAmt=$_POST['TOT_AMT'];}
		if($_POST['TOT_REM']==""){$TotRem=0;}else{$TotRem=$_POST['TOT_REM'];}
		if($_POST['PYMT_DOWN']==""){$PymtDown=0;}else{$PymtDown=$_POST['PYMT_DOWN'];}
		if($_POST['PYMT_REM']==""){$PymtRem=0;}else{$PymtRem=$_POST['PYMT_REM'];}
		if($_POST['SPC_NTE']==""){$SpcNote="";}else{$SpcNote=$_POST['SPC_NTE'];}
		
		//Process add new
		if($OrdNbr==-1){
			$query="SELECT COALESCE(MAX(CAP_NBR),0)+1 AS NEW_NBR FROM CMP.PRN_DIG_CAP";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$OrdNbr=$row['NEW_NBR'];
			$query="INSERT INTO CMP.PRN_DIG_CAP (CAP_NBR) VALUES (".$OrdNbr.")";
			$result=mysql_query($query);
			$create="CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=".$_SESSION['personNBR'].",";
			//echo $query."<br>";
			$new=true;
		}
		
		$query="UPDATE CMP.PRN_DIG_CAP SET 
			PYMT_DTE='".$_POST['PYMT_DTE']."',
			SHP_CO_NBR=".$ShpCoNbr.",
			RCV_CO_NBR=".$RcvCoNbr.",
			RCV_PRSN_NBR=".$RcvPrsnNbr.",
			PYMT_DOWN=".$PymtDown.",
			PYMT_REM=".$PymtRem.",
			TOT_AMT=".$TotAmt.",
			TOT_REM=".$TotRem.",
			SPC_NTE='".$SpcNote."',
			UPD_TS=CURRENT_TIMESTAMP,
			UPD_NBR=".$_SESSION['personNBR']."
			WHERE CAP_NBR=".$OrdNbr;
		//echo $query;
	   	$result=mysql_query($query);
		$changed=true;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
<script type="text/javascript" src="framework/functions/default.js"></script>
<link rel="stylesheet" href="framework/combobox/chosen.css">

<script type="text/javascript">

	var salesTax=getParam("tax","ppn");

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

<script type="text/javascript">
	function getInt(objectID)
	{
		if(document.getElementById(objectID).value=="")
		{
			return 0;
		}else{
			return parseInt(document.getElementById(objectID).value);
		}
	}
	
	function getFloat(objectID)
	{
		if(document.getElementById(objectID).value=="")
		{
			return 0;
		}else{
			return parseFloat(document.getElementById(objectID).value);
		}
	}
	
	function calcAmt(){
		document.getElementById('TOT_AMT').value=getInt('TOT_NET');
		document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('PYMT_DOWN')-getInt('PYMT_REM');
	}
</script>



<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />
</head>

<body>

<script>
	parent.parent.document.getElementById('invoiceDeleteYes').onclick=
	function () { 
		parent.parent.document.getElementById('content').src='print-digital-cap.php?DEL_CAP_NBR=<?php echo $OrdNbr ?>';
		parent.parent.document.getElementById('invoiceDelete').style.display='none';
		parent.parent.document.getElementById('fade').style.display='none';
	};
</script>
<div style="display:none;">
	<input id="refresh-list" type="button" value="Refresh" onclick="syncGetContent('edit-list','print-digital-cap-edit-list.php?CAP_NBR=<?php echo $OrdNbr; ?>');" />
	<input id="refresh-tot" type="button" value="Total" onclick="calcAmt();" />
</div>

<?php
$query="SELECT 
	CAP_NBR,
	PYMT_DTE,
	SHP_CO_NBR,
	RCV_CO_NBR,
	RCV_PRSN_NBR,
	TOT_AMT,
	TOT_REM,
	PYMT_DOWN,
	PYMT_REM,
	SPC_NTE,
	HED.CRT_TS,
	HED.CRT_NBR,
	HED.UPD_TS,
	HED.UPD_NBR
FROM CMP.PRN_DIG_CAP HED
	LEFT OUTER JOIN CMP.PEOPLE CRT ON HED.CRT_NBR=CRT.PRSN_NBR
	LEFT OUTER JOIN CMP.PEOPLE UPD ON HED.UPD_NBR=UPD.PRSN_NBR
WHERE CAP_NBR=".$OrdNbr;
$result=mysql_query($query);
$row=mysql_fetch_array($result);
?>
<?php if($OrdNbr!=0) { ?>
	<div class="toolbar-only">
		<?php if ($Security==0){  ?>
		<p class="toolbar-left"><a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.parent.document.getElementById('invoiceDelete').style.display='block';parent.parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer;>"></span></a></p>
		<?php } ?>
		<!--
		<p class="toolbar-right">
			<a href="retail-order-edit-print.php?CAP_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>&PRN_TYP=SL"><span class='fa fa-print toolbar'></span></a>
		</p>
		-->
	</div>
	
<?php } ?>
			
<form enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="return checkform();">
	<p>
		<h3>
            Nota CAP
        </h3>
		<h2>
			<?php echo $row['CAP_NBR'];if($row['CAP_NBR']==""){echo "Baru";} ?>
		</h2>
		
		<!-- Header -->
		
		<div style="float:left;width:140px;">
			<input id="CAP_NBR" name="CAP_NBR" type="hidden" value="<?php echo $row['CAP_NBR'];if($row['CAP_NBR']==""){echo "-1";} ?>"/>
			<label>Tanggal Dibayar</label>
			<?php 
				if($row['PYMT_DTE']==""){$OrdDte="";}else{$OrdDte=parseDate($row['PYMT_DTE']);}
			?>
			<input name="PYMT_DTE" id="PYMT_DTE" value="<?php echo $OrdDte; ?>" type="text" style="width:110px;" />
			<script>
				new CalendarEightysix('PYMT_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
		</div>
		
		<div>
			<label>Penerima</label><br>
			<select name="RCV_PRSN_NBR" class="chosen-select" style="width:560px">
				<?php
					$query="SELECT PRSN_NBR,CONCAT(NAME,' ',MBR_NBR,' ',ADDRESS,' ',CITY_NM) AS PRSN_DESC
							FROM CMP.PEOPLE PPL INNER JOIN CMP.CITY CIT ON PPL.CITY_ID=CIT.CITY_ID 
							WHERE PPL.DEL_NBR=0 AND PPL.APV_F=1
							ORDER BY 2";
					genCombo($query,"PRSN_NBR","PRSN_DESC",$row['RCV_PRSN_NBR'],"Kosong");
				?>
			</select>
		</div>
		<div style="clear:both"></div>
		
		<div style="float:left;padding-left:140px;">
			<label>Penerima</label><br>
			<select name="RCV_CO_NBR" style="width:560px;" class="chosen-select">
				<?php
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$row['RCV_CO_NBR'],"Tunai");
				?>
			</select>
		</div>
		<div style="clear:both"></div>
		
		<div style="float:left;padding-left:140px;margin-top:10px;">
			<label>Pengirim</label><br>
			<select name="SHP_CO_NBR" style="width:560px" class="chosen-select">
				<?php
					if($row['SHP_CO_NBR'] == ""){if($IvcTyp != "RC"){$ShpCoID = $CoNbrDef;}} else {$ShpCoID = $row['SHP_CO_NBR'];}
					$query = "SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$ShpCoID);
				?>
			</select>
		</div>
		<div style="clear:both"></div>
	
		<!-- listing -->
		<div id="edit-list" class="edit-list"></div>
		<script>getContent('edit-list','print-digital-cap-edit-list.php?CAP_NBR=<?php echo $OrdNbr; ?>');</script>
		<!-- Footer -->
		<table style="padding:0px;margin-bottom:10px" id="payment">
		<tr>
			<td style='padding:0px;width:350px'>
				<!-- payment -->
				<div class='total'>
					<table>
						<tr class='total'>
							<td style='font-weight:bold;color:#3464bc;padding-left:7px'>Total</td>
							<td style="text-align:right">
								<input name="TOT_AMT" id="TOT_AMT" value="<?php echo $row['TOT_AMT']; ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
							<td></td>
						</tr>
						<tr class='total'>
							<td style='font-weight:bold;color:#3464bc;padding-left:7px'>Uang Muka</td>
							<td style="text-align:right">
								<input name="PYMT_DOWN" id="PYMT_DOWN" value="<?php echo $row['PYMT_DOWN']; ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" onkeyup="calcAmt();" onchange="calcAmt();"/>
							</td>
							<td></td>
						</tr>

						<tr class='total'>
							<td style='font-weight:bold;color:#3464bc;padding-left:7px'>Pelunasan</td>
							<td style="text-align:right">
								<input name="PYMT_REM" id="PYMT_REM" value="<?php echo $row['PYMT_REM']; ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" onkeyup="calcAmt();" onchange="calcAmt();"/>
							</td>
							<td></td>
						</tr>
						<tr class='total'>
							<td style='font-weight:bold;color:#3464bc;border:0px;padding-left:7px'>Sisa</td>
							<td style="text-align:right;border:0px">
								<input name="TOT_REM" id="TOT_REM" value="<?php echo $row['TOT_REM']; ?>" type="text" style="width:100px;border:none;text-align:right" readonly />	
							</td>
							<td style="border:0px">
                                <div class='listable-btn' style='margin-left:5px'><span class='fa fa-refresh listable-btn' onclick="calcAmt();" ></span></div>
							</td>
						</tr>
					</table>
				</div>
			</td>
			<td style='padding:0px;vertical-align:top;padding-left:20px;'>
				<label>Catatan</label><br />
			<textarea name="SPC_NTE" style="width:320px;height:80px;"><?php echo $row['SPC_NTE']; ?></textarea>
			</td>
		</tr>
		</table>
		
		<div style="width:100%;clear:both;margin-bottom:10px;"></div>
		<input class="process" type="submit" value="Simpan" />		
		</p>		
	</form>	
	<script type="text/javascript"  src="framework/database/jquery.min.js"></script>
	<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
	<script type="text/javascript">
		jQuery.noConflict();
		var config = {
			'.chosen-select'           : {},
			'.chosen-select-deselect'  : {allow_single_deselect:true},
			'.chosen-select-no-single' : {disable_search_threshold:10},
			'.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
			'.chosen-select-width'     : {width:"95%"}
		}
		for (var selector in config) {
			jQuery(selector).chosen(config[selector]);
		}
	</script>
</body>
</html>