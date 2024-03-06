<?php
@header("Connection: close\r\n");
include "framework/database/connect.php";
include "framework/functions/default.php";

$filter_date = $_GET['FLTR_DATE'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="framework/combobox/chosen.css">

	<script type="text/javascript" src="framework/functions/default.js"></script>
	
	
	<script type="text/javascript">

	function getCheckboxValue(name) 
	{
		var checkbox = document.getElementById(name).checked;
		
		if (checkbox) 
		{
			return 1;
		} 
		else 
		{
			return 0;
		}
	}

	function formSubmit() 
	{	
		var REV_F  = getCheckboxValue('REV_F');
		console.log(REV_F);
		parent.document.getElementById('content').src='scorecard-pdf.php?FLTR_DATE=<?php echo $filter_date; ?>&REV_F='+REV_F;
		parent.document.getElementById('printDigitalReason').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	}
	</script>
</head>
<body>
<span class='fa fa-times toolbar' style='cursor:pointer' onclick="parent.document.getElementById('printDigitalReason').style.display='none';parent.document.getElementById('fade').style.display='none'"></span><br><br>
<form enctype="multipart/form-data" action="#" method="post" style="width: 100%; box-sizing: border-box;"  autofocus>
	<div class='side' style='top:4px'>
		<input name='REV_F' id='REV_F' type='checkbox' class='regular-checkbox' />&nbsp;
		<label for="REV_F"></label>
		<span style="display: inline-block;position: relative;top: -3px;">Column Revenue Tidak Ditampilkan</span>
	</div>
	<div class='combobox'></div>

	<input class="process" type="button" value="Print" onclick="formSubmit()"/>
</form>


</body>
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
	};

	for (var selector in config) {
		jQuery(selector).chosen(config[selector]);
	}
	
</script>
</html>


