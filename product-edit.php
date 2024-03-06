<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	$ProdNbr=$_GET['PROD_NBR'];
	$Security=getSecurity($_SESSION['userID'],"Inventory");
	
	if($ProdNbr != "") { $headerRead = "readonly"; } else {  $headerRead = ""; }  
	
	
	//Process changes here
	if($_POST['PROD_NBR']!="")
	{
		$ProdNbr 	= $_POST['PROD_NBR'];
		
		//Take care of nulls and timestamps
		if($_POST['PROD_DESC']==""){$ProdDesc="NULL";}else{$ProdDesc=$_POST['PROD_DESC'];}
			
		//Process add new
		if($ProdNbr == -1)
		{
			$query  = "SELECT MAX(PROD_NBR)+1 AS NEW_NBR FROM $CMP.PROD_LST";
			$result = mysql_query($query,$cloud);
			$row    = mysql_fetch_array($result);
			$ProdNbr= $row['NEW_NBR'];
			$query  = "INSERT INTO $CMP.PROD_LST(PROD_NBR,CRT_TS,CRT_NBR) 
					   VALUES ('',CURRENT_TIMESTAMP,'".$_SESSION['personNBR']."')";
			$result = mysql_query($query,$cloud);
			$query  = str_replace($CMP,"CMP",$query);
			$result = mysql_query($query,$local);
		}
		
		
		$query     = "UPDATE $CMP.PROD_LST
						SET PROD_DESC='".$ProdDesc."', 
							PROD_PRC='".$_POST['PROD_PRC']."',
							UPD_TS=CURRENT_TIMESTAMP,
							UPD_NBR='".$_SESSION['personNBR']."'
						WHERE PROD_NBR='".$ProdNbr."'";
		//echo $query;
	   	$result   = mysql_query($query,$cloud);
		$query    = str_replace($CMP,"CMP",$query);
		$result   = mysql_query($query,$local);
	   	$IvcTyp   = $_POST['IVC_TYP'];
	}
	
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" href="framework/combobox/chosen.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />

<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
<script type="text/javascript" src="framework/functions/default.js"></script>
<script>parent.Pace.restart();</script>
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

	function calcAmt(){
		document.getElementById('PROD_PRC').value=getInt('TOT_NET');
		
	}
</script>
<script type="text/javascript">
	function doStuff()
	{
	  //do some things
	  setTimeout(refSubTot, 500); //wait ten seconds before continuing
	}

	function refSubTot(){
		document.getElementById('refresh-tot').click();
	}
</script>
<script>
	parent.document.getElementById('invoiceDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='product.php?DEL=<?php echo $ProdNbr; ?>';
		parent.document.getElementById('invoiceDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>

</head>
<body>

<div style="display:none;">
	<input id="refresh-list" type="button" value="Refresh" onclick="syncGetContent('edit-list','product-list.php?PROD_NBR=<?php echo $ProdNbr; ?>');" />
	<input id="refresh-tot" type="button" value="Total" onclick="calcAmt();" />
</div>

<?php
	$query  = "SELECT PROD_NBR,PROD_DESC,PROD_PRC FROM CMP.PROD_LST WHERE PROD_NBR='".$ProdNbr."' ORDER BY 2";
	$result = mysql_query($query,$local);
	$row    = mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($ProdNbr!="")) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left">
		<a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.document.getElementById('invoiceDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a></p>
	</div>	
<?php } ?>
			
<form enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="return checkform();">
	<p>
		<h2>
			Nomor Produk : <?php echo $row['PROD_NBR'];if($row['PROD_NBR']==""){echo "Baru";} ?>
		</h2>
		<br />
		<!-- Header -->
		<div style="float:left;width:140px;">
			<input id="PROD_NBR" name="PROD_NBR" type="hidden" value="<?php echo $row['PROD_NBR'];if($row['PROD_NBR']==""){echo "-1";} ?>"/>
			
			<label>Nama Produk</label>
			<input name="PROD_DESC" id="PROD_DESC" value="<?php echo $row['PROD_DESC']; ?>" type="text" style="width:200px;" />
		</div>
		
		<div style="clear:both"></div>
		
		
		
		
		<!-- listing -->
		<div id="edit-list" class="edit-list"></div>
		<script>getContent('edit-list','product-list.php?PROD_NBR=<?php echo $ProdNbr; ?>');</script>
		
		<div style="clear:both"></div>
		<div>
			<label>Harga Produk</label><br />
			<input name="PROD_PRC" id="PROD_PRC" value="<?php echo $row['PROD_PRC']; ?>" type="text" style="width:100px;" /><div class='listable-btn' style='margin-left:5px'><span class='fa fa-refresh listable-btn' onclick="calcAmt();" ><br />	
		</div>

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
		   	};
			for (var selector in config) {
				jQuery(selector).chosen(config[selector]);
			}
		</script>

	</p>		
</form>
<div></div>				
</body>
</html>
