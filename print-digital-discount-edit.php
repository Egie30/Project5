<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	$PlanTyp=$_GET['PLAN_TYP'];
	$Security=getSecurity($_SESSION['userID'],"Inventory");
	
	if($PlanTyp != "") { $headerRead = "readonly"; } else {  $headerRead = ""; }  
	
	
	//Process changes here
	if($_POST['PLAN_TYP']!="")
	{
		$j=downTable("PRN_DIG_VOL_PLAN_TYP","PLAN_TYP","CMP",$CMP,$local,$cloud);
		
		$PlanTyp 	= $_POST['PLAN_TYP'];
		$PlanNbr	= $_POST['PLAN_NBR'];
		
		//Take care of nulls and timestamps
		if($_POST['PLAN_DESC']==""){$PlanDesc="NULL";}else{$PlanDesc=$_POST['PLAN_DESC'];}
			
		//Process add new
		if($PlanNbr == -1)
		{
			$query="INSERT INTO $CMP.PRN_DIG_VOL_PLAN_TYP(PLAN_TYP) VALUES ('".$PlanTyp."')";
			$result=mysql_query($query);
			
			//echo $query;
			
			$result = mysql_query($query, $cloud);
			$query = str_replace($CMP, "CMP", $query);
			$result = mysql_query($query, $local);
		}
		
		$query="UPDATE $CMP.PRN_DIG_VOL_PLAN_TYP
				SET PLAN_DESC='".$PlanDesc."'
					WHERE PLAN_TYP='".$PlanTyp."'";
					
		//echo $query;
	   	
		$result = mysql_query($query, $cloud);
		$query = str_replace($CMP, "CMP", $query);
		$result = mysql_query($query, $local);
		
		//echo $query;
		
	   	$IvcTyp=$_POST['IVC_TYP'];
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

<script type="text/javascript" src="framework/functions/default.js"></script>

<link rel="stylesheet" href="framework/combobox/chosen.css">

<script type="text/javascript">
	//Get parameters
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
</script>

<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />

</head>

<body>

<script>
	parent.document.getElementById('invoiceDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='print-digital-discount.php?DEL=<?php echo $PlanTyp ?>';
		parent.document.getElementById('invoiceDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>


<div style="display:none;">
	<input id="refresh-list" type="button" value="Refresh" onclick="syncGetContent('edit-list','print-digital-discount-list.php?PLAN_TYP=<?php echo $PlanTyp; ?>');" />
	<input id="refresh-tot" type="button" value="Total" onclick="calcAmt();" />
</div>

<?php
	$query="SELECT PLAN_TYP,PLAN_DESC FROM CMP.PRN_DIG_VOL_PLAN_TYP WHERE PLAN_TYP='".$PlanTyp."' ORDER BY 2";
	//echo $query;
	$result=mysql_query($query, $local);
	$row=mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($PlanTyp!="")) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left">
		<a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.document.getElementById('invoiceDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a></p>
		<p class="toolbar-right">
		<a href="retail-stock-edit-pdf.php?ORD_NBR=<?php echo $PlanTyp; ?>&IVC_TYP=<?php echo $IvcTyp; ?>"><span class='fa fa-file-pdf-o toolbar' style="cursor:pointer"></span></a>
		<a href="retail-stock-edit-print-label.php?ORD_NBR=<?php echo $PlanTyp; ?>"><span class='fa fa-barcode toolbar' style="cursor:pointer"></span></a><span class='fa fa-tag toolbar' style="cursor:pointer" style='cursor:pointer' onclick="parent.document.getElementById('retailStockBarcodeWhiteContent').src='retail-stock-edit-print-lead.php?ORD_NBR=<?php echo $PlanTyp; ?>';parent.document.getElementById('retailStockBarcodeWhite').style.display='block';parent.document.getElementById('fade').style.display='block'"></span>
		<a href="retail-stock-edit-print.php?ORD_NBR=<?php echo $PlanTyp; ?>&PRN_TYP=<?php echo $IvcTyp; ?>"><span class='fa fa-print toolbar' style="cursor:pointer"></span></a>
		</p>
	</div>
	
<?php } ?>
			
<form enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="return checkform();">
	<p>
		<h2>
			Volume Discount : <?php echo $row['PLAN_TYP'];if($row['PLAN_TYP']==""){echo "Baru";} ?>
		</h2>
		<br />
		<!-- Header -->
		<div style="float:left;width:140px;">
		
			<input id="PLAN_NBR" name="PLAN_NBR" type="hidden" value="<?php echo $row['PLAN_TYP'];if($row['PLAN_TYP']==""){echo "-1";} ?>"/>
			
			<label>Kode</label>
			<input name="PLAN_TYP" id="PLAN_TYP" value="<?php echo $row['PLAN_TYP']; ?>" type="text" style="width:110px;" <?php echo $headerRead; ?> />
		</div>
		
		<div style="clear:both"></div>
		
		<div>
			<label>Volume Discount</label><br />
			<input name="PLAN_DESC" id="PLAN_DESC" value="<?php echo $row['PLAN_DESC']; ?>" type="text" style="width:545px;" /><br />	
		</div>
		
		
		<div style="clear:both"></div>
		
		<!-- listing -->
		<div id="edit-list" class="edit-list"></div>
		<script>getContent('edit-list','print-digital-discount-list.php?PLAN_TYP=<?php echo $PlanTyp; ?>');</script>
		
		<!-- Footer -->

		<div style="clear:both"></div>

		<input class="process" type="submit" value="Simpan" />		
		
		<script src="framework/database/jquery.min.js" type="text/javascript"></script>
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
	</p>		
</form>
<div></div>				
</body>
</html>