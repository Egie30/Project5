

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />


<script type="text/javascript" src="framework/functions/default.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />
<script src="framework/database/jquery.min.js"></script>
<script>
	function doprint(){
		var barcode = $('#BRC_LST').val();
		$.ajax({
		type: "POST",
		url: "retail-stock-barcode-custom-formatting.php",
		data: {BRCLST:barcode}
		}).done(function( result ) {
			parent.document.getElementById('retailStockBarcodeWhiteContent').src='retail-stock-edit-print-lead.php?ORD_NBR=CUSTOM&BRCLST='+result;
			parent.document.getElementById('retailStockBarcodeWhite').style.display='block';
			parent.document.getElementById('fade').style.display='block';
		});
	}
</script>
</head>

<body>
<div class="toolbar-only">
	<p class="toolbar-right">	
		<span class="fa fa-barcode toolbar" style='cursor:pointer' onclick="doprint()"></span>&nbsp
	</p>
</div>
		
<form enctype="multipart/form-data" action="#" method="post" style="width:700px" >
	<p>
		<span style="color:#000;">Masukkan barcode sesuai dengan data dan dipisahkan dengan spasi</span>
		<textarea name="BRC_LST" id="BRC_LST" style="width:690px;height:140px;"></textarea>
		<div style="width:100%;clear:both;margin-bottom:10px;"></div>
		<input class="process" name="submit" id="submit" type="button" value="Cetak" 
		onclick="doprint()"/>		
	</p>		
</form>

<div></div>	
	
</body>
</html>