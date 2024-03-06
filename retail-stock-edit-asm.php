<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";

$security    = getSecurity($_SESSION['userID'], "Inventory");
$orderNumber = $_GET['ORD_NBR'];
$invoiceType = $_GET['IVC_TYP'];

if ($_GET['CNV'] != "") {
	$sql="SELECT COALESCE(MAX(ORD_NBR),0)+1 AS NEW_NBR FROM RTL.RTL_STK_HEAD";
	$results=mysql_query($sql);
	$rows=mysql_fetch_array($results);
	$OrdNbrNew=$rows['NEW_NBR'];
	
	$query = "INSERT INTO RTL.RTL_STK_HEAD (ORD_NBR, ORD_DTE, SHP_CO_NBR, REF_NBR,
					RCV_CO_NBR, IVC_TYP, FEE_MISC, DISC_PCT, DISC_AMT,
					TOT_AMT, PYMT_DOWN, PYMT_REM, TOT_REM, DL_TS, SPC_NTE, IVC_PRN_CNT, DEL_F,
					CRT_TS, CRT_NBR, UPD_TS, UPD_NBR,
					TAX_APL_ID, TAX_AMT, SLS_PRSN_NBR, SLS_TYP_ID)
					SELECT 
					'" . $OrdNbrNew . "', ORD_DTE, SHP_CO_NBR, 
					CASE WHEN REF_NBR <> '' THEN CONCAT(REF_NBR, ' (', ORD_NBR, ')') ELSE ORD_NBR END AS REF_NBR,
					RCV_CO_NBR, '" . $_GET['CNV'] . "', FEE_MISC, DISC_PCT, DISC_AMT,
					TOT_AMT, PYMT_DOWN, PYMT_REM, TOT_REM, CURRENT_TIMESTAMP AS DL_TS, SPC_NTE, 0, 0,
					CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ", CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ",
					TAX_APL_ID, TAX_AMT, SLS_PRSN_NBR, SLS_TYP_ID
					FROM RTL.RTL_STK_HEAD WHERE ORD_NBR=" . $orderNumber . "
				";
    //echo $query."<br><br>";
    $result = mysql_query($query);
    //$newOrderNumber = mysql_insert_id();
    
    $query  = "SELECT ORD_DET_NBR FROM RTL.RTL_STK_DET WHERE ORD_NBR=" . $orderNumber;
    $result = mysql_query($query);
    
    while ($row = mysql_fetch_array($result)) {
        $query = "INSERT INTO RTL.RTL_STK_DET (
					SELECT 
					(SELECT COALESCE(MAX(ORD_DET_NBR),0)+1 AS ORD_DET_NBR FROM RTL.RTL_STK_DET) ORD_DET_NBR, " . $OrdNbrNew . " AS ORD_NBR, INV_NBR, INV_DESC, (ORD_Q * -1) AS ORD_Q, ORD_X, ORD_Y, ORD_Z, INV_PRC, FEE_MISC, DISC_PCT, DISC_AMT, TOT_SUB, ORD_DET_NBR_REF, SER_NBR, NTE,CURRENT_TIMESTAMP AS CRT_TS, " . $_SESSION['personNBR'] . " AS CRT_NBR, CURRENT_TIMESTAMP AS UPD_TS, " . $_SESSION['personNBR'] . " AS UPD_NBR
					FROM RTL.RTL_STK_DET WHERE ORD_DET_NBR=" . $row['ORD_DET_NBR'] . "
				)";
			 
        mysql_query($query);
    }
    
    $invoiceType = $_GET['CNV'];
	header('Location: retail-stock-edit.php?IVC_TYP='.$invoiceType.'&ORD_NBR='.$OrdNbrNew);
   // $orderNumber = $newOrderNumber;
}

// Auto detect invoice type
if ($invoiceType == "" && $orderNumber != "") {
    $query       = "SELECT IVC_TYP FROM RTL.RTL_STK_HEAD WHERE ORD_NBR=" . $orderNumber;
    $result      = mysql_query($query);
    $row         = mysql_fetch_array($result);
    $invoiceType = $row['IVC_TYP'];
}

//Process changes here
if ($_POST['ORD_NBR'] != "") {
    $orderNumber = $_POST['ORD_NBR'];
    
    //Take care of nulls and timestamps
    if ($_POST['RCV_CO_NBR'] == "") {
        $RcvCoNbr = "NULL";
    } else {
        $RcvCoNbr = $_POST['RCV_CO_NBR'];
    }

    if ($_POST['SHP_CO_NBR'] == "") {
        $ShpCoNbr = "NULL";
    } else {
        $ShpCoNbr = $_POST['SHP_CO_NBR'];
    }

    if ($_POST['FEE_MISC'] == "") {
        $FeeMisc = "NULL";
    } else {
        $FeeMisc = $_POST['FEE_MISC'];
    }

    if ($_POST['TOT_AMT'] == "") {
        $TotAmt = "NULL";
    } else {
        $TotAmt = $_POST['TOT_AMT'];
    }

    if ($_POST['PYMT_DOWN'] == "") {
        $PymtDown = "NULL";
    } else {
        $PymtDown = $_POST['PYMT_DOWN'];
    }

    if ($_POST['PYMT_REM'] == "") {
        $PymtRem = "NULL";
    } else {
        $PymtRem = $_POST['PYMT_REM'];
    }

    if ($_POST['TOT_REM'] == "") {
        $TotRem = "NULL";
    } else {
        $TotRem = $_POST['TOT_REM'];
    }

    if ($_POST['DL_DTE'] == "") {
        $DLTS = "NULL";
    } else {
        $DLTS = "'" . $_POST['DL_DTE'] . " " . $_POST['DL_TME'] . "'";
    }

    if ($_POST['TAX_AMT'] == "") {
        $TaxAmt = "NULL";
    } else {
        $TaxAmt = $_POST['TAX_AMT'];
    }

    if ($_POST['TAX_APL_ID'] == "") {
        $TAX_APL_ID = "E";
    } else {
        $TAX_APL_ID = $_POST['TAX_APL_ID'];
    }

    if ($_POST['SLS_PRSN_NBR'] == "") {
        $SlsPrsnNbr = "NULL";
    } else {
        $SlsPrsnNbr = $_POST['SLS_PRSN_NBR'];
    }
    
    //Process add new
    if ($orderNumber == -1) {
        $query       = "SELECT COALESCE(MAX(ORD_NBR),0)+1 AS NEW_NBR FROM RTL.RTL_STK_HEAD";
        $result      = mysql_query($query);
        $row         = mysql_fetch_array($result);
        $orderNumber = $row['NEW_NBR'];
        $query       = "INSERT INTO RTL.RTL_STK_HEAD (ORD_NBR, CRT_TS, CRT_NBR) VALUES (" . $orderNumber . ", CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ")";
        $result      = mysql_query($query);
        
    }
    
    //Process payment journal
    if ($_POST['PYMT_DOWN'] != "") {
        $query  = "SELECT PYMT_DOWN,PYMT_REM FROM RTL.RTL_STK_HEAD WHERE ORD_NBR=$orderNumber";
        $result = mysql_query($query);
        $row    = mysql_fetch_array($result);
        if ($row['PYMT_DOWN'] != $_POST['PYMT_DOWN']) {
            $query   = "INSERT INTO RTL.JRN_CSH_FLO (DIV_ID,NM_TBL,ORD_NBR,CSH_FLO_TYP,CSH_AMT,CRT_TS,CRT_NBR)
						VALUES ('PRN','RTL_STK_HEAD'," . $orderNumber . ",'DP'," . $_POST['PYMT_DOWN'] . ",CURRENT_TIMESTAMP," . $_SESSION['personNBR'] . ")";
            //echo $query;
            $resultp = mysql_query($query);
        }
    }
    
    if ($_POST['PYMT_REM'] != "") {
        $query  = "SELECT PYMT_DOWN,PYMT_REM FROM RTL.RTL_STK_HEAD WHERE ORD_NBR=$orderNumber";
        $result = mysql_query($query);
        $row    = mysql_fetch_array($result);
        if ($row['PYMT_REM'] != $_POST['PYMT_REM']) {
            $query   = "INSERT INTO RTL.JRN_CSH_FLO (DIV_ID,NM_TBL,ORD_NBR,CSH_FLO_TYP,CSH_AMT,CRT_TS,CRT_NBR)
						VALUES ('PRN','RTL_STK_HEAD'," . $orderNumber . ",'FL'," . $_POST['PYMT_REM'] . ",CURRENT_TIMESTAMP," . $_SESSION['personNBR'] . ")";
            //echo $query;
            $resultp = mysql_query($query);
        }
    }
    
    $query       = "UPDATE RTL.RTL_STK_HEAD
				SET ORD_DTE='" . $_POST['ORD_DTE'] . "',
					RCV_CO_NBR=" . $RcvCoNbr . ",
					SHP_CO_NBR=" . $ShpCoNbr . ",
					REF_NBR='" . $_POST['REF_NBR'] . "',
					IVC_TYP='" . $_POST['IVC_TYP'] . "',
					FEE_MISC=" . $FeeMisc . ",
					TOT_AMT=" . $TotAmt . ",
					PYMT_DOWN=" . $PymtDown . ",
					PYMT_REM=" . $PymtRem . ",
					TOT_REM=" . $TotRem . ",
					DL_TS=" . $DLTS . ",
					SPC_NTE='" . $_POST['SPC_NTE'] . "',
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=" . $_SESSION['personNBR'] . ",
					TAX_APL_ID='" . $TAX_APL_ID . "',
					TAX_AMT=" . $TaxAmt . ",
					SLS_PRSN_NBR=" . $SlsPrsnNbr . "
					WHERE ORD_NBR=" . $orderNumber;
    //echo $query;
    $result      = mysql_query($query);
    $invoiceType = $_POST['IVC_TYP'];
}

	$query 		= "SELECT IVC_DESC FROM RTL.IVC_TYP WHERE IVC_TYP ='$invoiceType'";
    $result 	= mysql_query($query);
	$row 		= mysql_fetch_array($result);
    $IvcDesc 	= $row['IVC_DESC'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="framework/combobox/chosen.css">
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
	
	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
</head>
<body>
<div style="display:none;">
	<input id="refresh-list" type="button" value="Refresh" />
	<input id="refresh-tot" type="button" value="Total" onclick="calcAmt();" />
</div>

<script type="text/javascript">
	parent.document.getElementById('invoiceDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='retail-stock.php?IVC_TYP=<?php echo $invoiceType; ?>&DEL_R=<?php echo $orderNumber ?>';
		parent.document.getElementById('invoiceDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
	document.getElementById('refresh-list').onclick=
	function () { 
		getContent('edit-list-hed','retail-stock-edit-list-asm.php?ORD_NBR=<?php echo $orderNumber;?>&IVC_TYP=<?php echo $invoiceType;?>&DTL_TYP=HED');
		getContent('edit-list','retail-stock-edit-list-asm.php?ORD_NBR=<?php echo $orderNumber;?>&IVC_TYP=<?php echo $invoiceType;?>');
	};
</script>

<?php
	$query="SELECT ORD_NBR,ORD_DTE,IVC_TYP,REF_NBR,SHP_CO_NBR,RCV_CO_NBR,FEE_MISC,TOT_AMT,PYMT_DOWN,PYMT_REM,TOT_REM,DL_TS,SPC_NTE,HED.CRT_TS,HED.CRT_NBR,HED.UPD_TS,HED.UPD_NBR,
			TAX_APL_ID,TAX_AMT,SLS_PRSN_NBR
			FROM RTL.RTL_STK_HEAD HED
			WHERE ORD_NBR=".$orderNumber;
			//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<?php if(($security==0)&&($orderNumber!=0)) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left">
			<a title="Hapus" href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.document.getElementById('invoiceDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a>
		</p>
		<p class="toolbar-right">
			<?php if ($invoiceType == "AS") {?>
				<a title="Convert as Disassembly" href="retail-stock-edit-asm.php?ORD_NBR=<?php echo $orderNumber;?>&IVC_TYP=<?php echo $invoiceType;?>&CNV=DS"><span class='fa fa-copy toolbar' style="cursor:pointer"></span></a>
			<?php } elseif ($invoiceType == "DS") { ?>
				<a title="Convert as Assembly" href="retail-stock-edit-asm.php?ORD_NBR=<?php echo $orderNumber;?>&IVC_TYP=<?php echo $invoiceType;?>&CNV=AS"><span class='fa fa-copy toolbar' style="cursor:pointer"></span></a>
			<?php } ?>
			<a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('retailStockBarcodeWhiteContent').src='retail-stock-edit-print-lead.php?ORD_NBR=<?php echo $orderNumber; ?>';parent.document.getElementById('retailStockBarcodeWhite').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-barcode toolbar' style="cursor:pointer"></span></a>
			
            <a href="retail-stock-edit-print.php?ORD_NBR=<?php echo $orderNumber;?>&IVC_TYP=<?php echo $invoiceType;?>&PRN_TYP=<?php echo $invoiceType; ?>"><span class='fa fa-print toolbar' style="cursor:pointer"></span></a>
		</p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="return checkform();">
	<p>
		<h2>
			Nota <?php echo $IvcDesc; ?>
		</h2>	
		<h3>
			Nomor nota: <?php echo $row['ORD_NBR'];if($row['ORD_NBR']==""){echo "Baru";} ?>
		</h3>
		
		<!-- Header -->
		<div style="float:left;width:140px;">
			<input id="ORD_NBR" name="ORD_NBR" type="hidden" value="<?php echo $row['ORD_NBR'];if($row['ORD_NBR']==""){echo "-1";} ?>"/>
			<input id="IVC_TYP" name="IVC_TYP" type="hidden" value="<?php echo $row['IVC_TYP'];if($row['IVC_TYP']==""){echo $invoiceType;} ?>"/>
			
			<label>Tanggal Diterima</label>
			<?php 
				if($row['DL_TS']==""){$DLDte="";}else{$DLDte=parseDate($row['DL_TS']);}
			?>
			<input name="DL_DTE" id="DL_DTE" value="<?php echo $DLDte; ?>" type="text" style="width:110px;" />
			<script>
				new CalendarEightysix('DL_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
			</script>
		</div>
		</div>

		<div>
			<label>Pengirim</label><br /><div class='labelbox'></div>
			<select name="SHP_CO_NBR" id="SHP_CO_NBR" style="width:550px" class="chosen-select">
				<?php
					if($row['SHP_CO_NBR']==""){if($IvcTyp!="RC"){$ShpCoID=$CoNbrDef;}}else{$ShpCoID=$row['SHP_CO_NBR'];}
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID WHERE COM.APV_F=1 ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$ShpCoID,"");
				?>
			</select><br /><div class="combobox"></div>
		</div>
		<div style="clear:both"></div>
		
		<div style="float:left;width:140px;">
			<label>Waktu Diterima</label>
			<?php
				if($row['DL_TS']==""){$DLTme=date("H:i:s");}else{$DLTme=parseTime($row['DL_TS']);}
			?>
			<input name="DL_TME" id="DL_TME" value="<?php echo $DLTme; ?>" type="text" style="width:110px;"/>
		</div>	

		<div>
			<label>Penerima</label><br /><div class='labelbox'></div>
			<select name="RCV_CO_NBR" id="RCV_CO_NBR" style="width:550px" class="chosen-select">
				<?php
					if($row['RCV_CO_NBR']==""){if($IvcTyp==""){$RcvCoID=$CoNbrDef;}}else{$RcvCoID=$row['RCV_CO_NBR'];}
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID WHERE COM.APV_F=1 ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$RcvCoID,"");
				?>
			</select><br /><div class="combobox"></div>
		</div>
		<div style="clear:both"></div>

		<div style="float:left;width:140px;">
			<input id="ORD_NBR" name="ORD_NBR" type="hidden" value="<?php echo $row['ORD_NBR'];if($row['ORD_NBR']==""){echo "-1";} ?>"/>
			
			<label>Tanggal Nota</label>
			<?php 
				if($row['ORD_DTE']==""){$OrdDte="";}else{$OrdDte=parseDate($row['ORD_DTE']);}			?>
			<input name="ORD_DTE" id="ORD_DTE" value="<?php echo $OrdDte; ?>" type="text" style="width:110px;" />
			<script>
				new CalendarEightysix('ORD_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
		</div>
		
		<div style="float:left;width:140px;">
			<label>No. Referensi</label><br />
			<input name="REF_NBR" id="REF_NBR" value="<?php echo $row['REF_NBR']; ?>" type="text" style="width:110px;" />
		</div>
	
		<div style="clear:both"></div>

		<!-- listing -->
		<div id="edit-list-hed" class="edit-list"></div>
		<script type="text/javascript">getContent('edit-list-hed','retail-stock-edit-list-asm.php?ORD_NBR=<?php echo $orderNumber;?>&IVC_TYP=<?php echo $invoiceType;?>&DTL_TYP=HED');</script>
		
		<!-- listing -->
		<div id="edit-list" class="edit-list"></div>
		<script type="text/javascript">getContent('edit-list','retail-stock-edit-list-asm.php?ORD_NBR=<?php echo $orderNumber;?>&IVC_TYP=<?php echo $invoiceType;?>');</script>
		
		<!-- Footer -->
		<div style="float:left;width:140px;display: none;">
			<label>Biaya Tambahan</label><br />
			<input name="FEE_MISC" id="FEE_MISC" value="<?php echo $row['FEE_MISC']; ?>" onkeyup="calcAmt();" onchange="calcAmt();" type="text" style="width:110px;" /><br />	
		</div>
		<div style="float:left;width:120px;">
			<label>Total Nota</label><br />
			<input name="TOT_AMT" id="TOT_AMT" value="<?php echo $row['TOT_AMT']; ?>" type="text" class="inputmask currency" style="width:110px;" readonly /><br />	
		</div>

		<div style="float:left;width:140px;display: none;">
			<label>Uang Muka</label><br />
			<input name="PYMT_DOWN" id="PYMT_DOWN" value="<?php echo $row['PYMT_DOWN']; ?>" onkeyup="calcAmt();" onchange="calcAmt();"  type="text" style="width:110px;" /><br />	
		</div>

		<div style="float:left;width:140px;display: none;">
			<label>Pelunasan</label><br />
			<input name="PYMT_REM" id="PYMT_REM" value="<?php echo $row['PYMT_REM']; ?>" onkeyup="calcAmt();" onchange="calcAmt();"  type="text" style="width:110px;" /><br />	
		</div>

		<div style="float:left;width:140px;">
			<div style="display: none;">
			<label>Sisa</label><br />
			<input name="TOT_REM" id="TOT_REM" value="<?php echo $row['TOT_REM']; ?>" type="text" style="width:110px;" readonly />
			</div><br/>
			<img title="Hitung Subtotal" src="img/calc.png" style="cursor:pointer" onclick="calcAmt();" ><br />
		</div>
		
		<div style="clear:both"></div>

		<div style="float:left;width:145px;">
			<label>Catatan</label><br />
			<textarea name="SPC_NTE" style="width:690px;height:40px;"><?php echo $row['SPC_NTE']; ?></textarea>
		</div>

		<div style="clear:both"></div>

		<input class="process" id="submit" type="submit" value="Simpan" />
		
		<script type="text/javascript">
			function calcAmt(){
				var totalNet = parseNumber(document.getElementById('TOT_NET').value),
					totalFee = parseNumber(document.getElementById('FEE_MISC').value),
					totalDP = parseNumber(document.getElementById('PYMT_DOWN').value),
					totalRemaining = parseNumber(document.getElementById('PYMT_REM').value),
					totalAmount = totalNet + totalFee;
					
				document.getElementById('TOT_AMT').value = totalAmount;
				document.getElementById('TOT_REM').value = totalAmount - totalDP - totalRemaining;
			}

			<?php if ($_GET['CNV'] != "") {?>			
				var $leftMenu = jQuery(parent.document.getElementById('leftmenu')).contents().find(".sub");
				
				$leftMenu.find("div").removeClass("leftmenusel").addClass("leftmenu");
				$leftMenu.find("#retail-<?php echo strtolower($_GET['CNV']);?>").removeClass("leftmenu").addClass("leftmenusel");
			<?php }?>
		</script>
	</p>		
</form>
</body>
</html>
