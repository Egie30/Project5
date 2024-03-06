<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";
require_once "framework/functions/dotmatrix.php";

$Type 			= $_GET['TYPE'];
$salestype 		= $_GET['TYP'];
$orderNumber 	= $_GET['ORD_NBR'];
$headtable 		= "$CMP.PRN_DIG_ORD_HEAD_EST";
$detailtable	= "$CMP.PRN_DIG_ORD_DET_EST";
$paymenttable	= "$CMP.PRN_DIG_ORD_PYMT_EST";

//Detail
$query = "SELECT HED.ORD_NBR AS ORD_NBR,
		ORD_TS,
		BUY_PRSN_NBR,
		PPL.NAME AS NAME_PPL,
		COM.NAME AS NAME_CO,
		COM.ADDRESS AS ADDRESS_CO,
		COM.ZIP AS ZIP_CO,
		COM.PHONE AS PHONE_CO,
		BUY_CO_NBR,
		CNS_CO_NBR,
		BIL_CO_NBR,
		BILCOM.NAME AS BIL_COM,
		BILCOM.ADDRESS AS BIL_ADDRESS,
		BILCOM.ZIP AS BIL_ZIP,
		BILCOM.PHONE AS BIL_PHONE,
		REF_NBR,
		ORD_TTL,
		PRN_CO_NBR,
		PRNCOM.NAME AS PRN_COM,
		PRNCOM.ADDRESS AS PRN_ADDRESS,
		PRNCOM.ZIP AS PRN_ZIP,
		PRNCOM.PHONE AS PRN_PHONE,
		PRNCOM.BNK_ACCT_NM AS PRN_BNK_ACCT_NM,
		PRNCOM.BNK_ACCT_NBR AS PRN_BNK_ACCT_NBR,
		PRNCOM.BNK_CO_NBR AS PRN_BNK_CO_NBR,
		COM_BNK.NAME AS NAME_BNK, 
		FEE_MISC,
		TAX_APL_ID,
		TAX_AMT,
		TOT_AMT,
		PYMT_DOWN,
		PYMT_REM,
		VAL_PYMT_DOWN,
		VAL_PYMT_REM,
		TOT_REM,
		SPC_NTE,
		JOB_LEN_TOT,
		SUM(PYMT.TND_AMT) AS TOT_PYMT,
		HED.ACTG_TYP, 
		PPLE.NAME AS CRT_NAME ,BO_HEAD_DESC, BO_BODY_DESC, BO_FOOT_DESC
	FROM ". $headtable ." HED
		LEFT OUTER JOIN ". $paymenttable ." PYMT ON HED.ORD_NBR=PYMT.ORD_NBR
		LEFT OUTER JOIN $CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
		LEFT OUTER JOIN $CMP.PEOPLE PPLE ON PPLE.PRSN_NBR=HED.CRT_NBR
		LEFT OUTER JOIN $CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
		LEFT OUTER JOIN $CMP.COMPANY PRNCOM ON HED.PRN_CO_NBR=PRNCOM.CO_NBR
		LEFT OUTER JOIN $CMP.COMPANY BILCOM ON HED.BIL_CO_NBR=BILCOM.CO_NBR
		LEFT OUTER JOIN $CMP.COMPANY COM_BNK ON PRNCOM.BNK_CO_NBR=COM_BNK.CO_NBR
	WHERE HED.ORD_NBR='" . $orderNumber . "' ";
$result = mysql_query($query);
$row 	= mysql_fetch_array($result);
//echo $query;
//exit();
$BuyName	= $row['NAME_PPL'];
$BuyCo		= $row['NAME_CO'];
$BuyAddress = $row['ADDRESS_CO'];
$BuyZip 	= $row['ZIP_CO'];
$BuyPhone 	= $row['PHONE_CO'];
$OrdTtl 	= $row['ORD_TTL'];
$RefNbr 	= $row['REF_NBR'];
$ActgTyp 	= $row['ACTG_TYP'];
$BodyLttr	= $row['BO_BODY_DESC'];
$FootLttr	= $row['BO_FOOT_DESC'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<script>parent.Pace.restart();</script>
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
	<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<link type="text/css" rel="stylesheet" href="framework/combobox/chosen.css">
	
</head>
<body>

<img class="toolbar-left" style="cursor:pointer" src="img/close.png" onclick="slideFormOut();"></a>

<form enctype="multipart/form-data" action="#" method="post" style="width: 100%; box-sizing: border-box;" name="signup" id="signup">
	<input id="ORD_NBR" name="ORD_NBR" type="hidden" value="<?php echo $row['ORD_NBR'];if($row['ORD_NBR']==""){echo "-1";} ?>"/>
	<table>
		<tr>
			<td>Kop Surat</td>
			<td>
				<!--
				<input type="checkbox" name="LETTER_HEAD" id="LETTER_HEAD"  <?php if($row['LETTER_HEAD']=="1"){echo "checked";} ?>>
				-->
				
				<input name='LETTER_HEAD' id='LETTER_HEAD' type='radio' class='regular-checkbox' <?php if($row['LETTER_HEAD']=="1"){echo "checked";} ?>/>
				<label for="LETTER_HEAD"></label>
				<label class='checkbox' for="LETTER_HEAD" style='cursor:pointer'>PT</label>&nbsp;&nbsp;&nbsp;&nbsp;
				
				<input name='LETTER_HEAD' id='LETTER_HEAD_C' type='radio' class='regular-checkbox' <?php if($row['LETTER_HEAD_C']=="1"){echo "checked";} ?>/>
				<label for="LETTER_HEAD_C"></label>
				<label class='checkbox' for="LETTER_HEAD_C" style='cursor:pointer'>Campus</label>&nbsp;&nbsp;&nbsp;&nbsp;
				
				<input name='LETTER_HEAD' id='LETTER_HEAD_P' type='radio' class='regular-checkbox' <?php if($row['LETTER_HEAD_P']=="1"){echo "checked";} ?>/>
				<label for="LETTER_HEAD_P"></label>
				<label class='checkbox' for="LETTER_HEAD_P" style='cursor:pointer'>Printing</label>

			</td>
		</tr>
		<tr>
			<td></td>
		<td>
				<input name='LETTER_HEAD' id='LETTER_HEAD_SU' type='radio' class='regular-checkbox' 
				<?php if($row['LETTER_HEAD_SU']=="1"){echo "checked";} ?>/>
					<label for="LETTER_HEAD_SU"></label>
					<label class='checkbox' for="LETTER_HEAD_SU" style='cursor:pointer'>Surabaya</label>
					
				<input name='LETTER_HEAD' id='LETTER_HEAD_SE' type='radio' class='regular-checkbox' 
				<?php if($row['LETTER_HEAD_SE']=="1"){echo "checked";} ?>/>
					<label for="LETTER_HEAD_SE"></label>
					<label class='checkbox' for="LETTER_HEAD_SE" style='cursor:pointer'>Semarang</label>
			</td>
		</tr>
		<tr>
			<td>Kepada</td>
			<td>
				<input type="text" name="NAME_OFR" id="NAME_OFR" style="width:300px;" value="<?php echo $row['NAME_PPL']; ?>">
			</td>
		</tr>
		<tr>
            <td>Perusahaan</td>
			<td>
				<input type="text" name="NAME_COM" id="NAME_COM" style="width:300px;" value="<?php echo $BuyCo; ?> <?php echo $BuyAddress; ?> <?php echo $BuyCity; ?> <?php echo $BuyZip; ?>">
			</td>
		</tr>
		<tr>
            <td>Label Tabel 1</td>
			<td>
				<input type="text" name="TITLE_TOP" id="TITLE_TOP" style="width:300px;" >
			</td>
		</tr>
		<tr>
            <td style="float:left">Footer Tabel 1</td>
			<td>
				<textarea style="width:300px;height:160px" readonly><?php echo $BodyLttr; ?></textarea>
			</td>
		</tr>
        <tr>
            <td>Label Tabel 2</td>
			<td>
				<input type="text" name="TITLE_BOTTOM" id="TITLE_BOTTOM" style="width:300px;" >
			</td>
        </tr>
		<tr>
            <td style="float:left">Footer Tabel 2 </td>
			<td>
				<textarea style="width:300px;height:160px" readonly><?php echo $FootLttr; ?></textarea>
			</td>
		</tr>
		<tr>
			<td align="center" colspan="2"><br>
			<input id='submit_button' class="process" name="submit" type="submit" value="Simpan" onclick="parent.parent.document.getElementById('content').contentDocument.getElementById('rightpane').src='print-digital-invoice-pdf.php?ORD_NBR=<?php echo $orderNumber; ?>&TYPE=TEXT&TYP=EST&LETTER_HEAD='+document.getElementById('LETTER_HEAD').checked+'&LETTER_HEAD_C='+document.getElementById('LETTER_HEAD_C').checked+'&LETTER_HEAD_P='+document.getElementById('LETTER_HEAD_P').checked+'&LETTER_HEAD_SU='+document.getElementById('LETTER_HEAD_SU').checked+'&LETTER_HEAD_SE='+document.getElementById('LETTER_HEAD_SE').checked+'&NAME_OFR='+document.getElementById('NAME_OFR').value+'&NAME_COM='+document.getElementById('NAME_COM').value+'&TITLE_TOP='+document.getElementById('TITLE_TOP').value+'&TITLE_BOTTOM='+document.getElementById('TITLE_BOTTOM').value;slideFormOut();"/>
			</td>
		</tr>
	</table>
</form>
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
</body>
</html>

