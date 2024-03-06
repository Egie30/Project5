<?php
	include "framework/database/connect.php";
	include "framework/functions/dotmatrix.php";
	include_once "framework/functions/default.php";
	include "framework/security/default.php";

	$PymtTyp			= $_GET['PYMT_TYP'];
	$OrderTitle			= $_GET['ORD_TTL'];
	$BankStatementNbr	= $_GET['BNK_STMT_NBR'];
	
	$security 			= getSecurity($_SESSION['userID'], "Accounting");

	if(($locked==1)||($_COOKIE["LOCK"] == "LOCK")){ $displaylock = "display:none;"; }
	
	//Process changes here
	if($_POST['BNK_STMT_NBR']!=""){
				
		$BankStatementNbr=$_POST['BNK_STMT_NBR'];
		
		if($BankStatementNbr==-1)
		{
			$query="SELECT COALESCE(MAX(BNK_STMT_NBR),0)+1 AS NEW_NBR FROM RTL.BNK_STMT";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$BankStatementNbr=$row['NEW_NBR'];
			$query="INSERT INTO RTL.BNK_STMT (BNK_STMT_NBR,CRT_TS,CRT_NBR) 
					VALUES (".$BankStatementNbr.",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
			$result=mysql_query($query);
			
			//echo $query;
		}

		if($_POST['VRFCTN_F']=="on"){$Verify=1;}else{$Verify=0;}
		
				//Take care of nulls
		if($_POST['textbox1']==""){$IdPymt="NULL";}
			else{
				if($_POST['VAL_Q']>1){
					$val 	= array();
					for($i=1;$i<=$_POST['VAL_Q'];$i++){
						$getName  = "textbox".$i;
						
						if($_POST[$getName] != "") {
							$val[]= $_POST[$getName];
						}

						
					}
					$IdPymt 	= "'".implode($val, "+")."'";
				}else{
					$IdPymt = "'".$_POST['textbox1']."'";
				}
			}
			
		$query="UPDATE RTL.BNK_STMT
	   			SET BNK_STMT_DTE='".$_POST['BNK_STMT_DTE']."',
					BNK_STMT_TYP='".$_POST['BNK_STMT_TYP']."',
					BNK_STMT_AMT='".$_POST['BNK_STMT_AMT']."',
					BNK_STMT_DESC='".$_POST['BNK_STMT_DESC']."',
					NTE='".$_POST['NTE']."',
					VRFCTN_F='".$Verify."',
					ACTG_TYP='".$_POST['ACTG_TYP']."',
					PYMT_DESC=".$IdPymt.",
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
				WHERE BNK_STMT_NBR=".$BankStatementNbr;
				
	   	$result=mysql_query($query);
		
		$value	= str_replace("'","",$IdPymt);
			
		$dataReg 		= explode("+", $value);
		$dataRegNbr    = implode(",", $dataReg);
		
		$query_reg		= "SELECT REG_NBR FROM RTL.CSH_REG WHERE TRSC_NBR IN (".$dataRegNbr.")";
		$result_reg		= mysql_query($query_reg);
		
		//echo $query_reg;
		
		$arrayPymt		= array();
		
		while($row_reg = mysql_fetch_array($result_reg)) {
			$arrayPymt[]	= $row_reg["REG_NBR"];
			
			$dataPymt.= $row_reg["REG_NBR"].',';
		}
		
		$dataPymtNbr	= rtrim($dataPymt,",");
				
		$query_pymt		= "UPDATE CMP.PRN_DIG_ORD_PYMT SET BNK_STMT_NBR = ".$BankStatementNbr." WHERE VAL_NBR IN (".$dataPymtNbr.") ";
		$result_pymt	= mysql_query($query_pymt);
		
		//echo $query_pymt;
		
	}

$query = "SELECT 
		BS.BNK_STMT_NBR,
		BS.BNK_STMT_DTE,
		BS.BNK_STMT_TYP,
		BS.BNK_STMT_DESC,
		BS.BNK_STMT_AMT,
		BS.NTE,
		BS.VRFCTN_F,
		BS.ACTG_TYP,
		BS.PYMT_DESC
	FROM RTL.BNK_STMT BS
	WHERE BS.BNK_STMT_NBR = ".$BankStatementNbr."
	GROUP BY BS.BNK_STMT_NBR ORDER BY 1 ASC";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	
	//echo $query;
	
	
	$dataValArr = explode("+", $row['PYMT_DESC']);
	$dataVal    = "'".implode("','", $dataValArr)."'";
	$cntVal     = count($dataValArr);

	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>

<script type="text/javascript">jQuery.noConflict()</script>
<link rel="stylesheet" href="framework/combobox/chosen.css">



</head>

<body>

<script>
	parent.document.getElementById('addressDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='bank-statement.php?DEL=<?php echo $BankStatementNbr ?>';
		parent.document.getElementById('addressDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none'; 
	};
</script>


<script type="text/javascript">
	window.onload = function () {
		var cntVal  = "<?php echo $cntVal; ?>",
		valArr  = [<?php echo $dataVal; ?>];

		for (i=0;i<cntVal;i++){
			var counter = i+1;
			var idNm = "textbox"+counter; 
			if (counter<cntVal){
				document.getElementById('addButton').click();
			} 
			document.getElementById(idNm).value=valArr[i];
			
			console.log(counter);
		}

		var lastVal = 'textbox'.$cntVal;
		document.getElementById(lastVal).value=valArr[i-1];
	};
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

	function calcQ(){
		document.getElementById('VAL_Q').value=getInt('VAL_Q')-1;
	}
</script>

<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('addressDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a></p>
	</div>
			
<form enctype="multipart/form-data" action="#" method="post" style="width:900px" onSubmit="return checkform();" id="signup" autocomplete="off">
	<p>
		<h2>
			Nomor Rekening Koran: 
			<?php if ($row['BNK_STMT_NBR'] == "") {
				echo "Baru";
			} else {
				echo $row['BNK_STMT_NBR'];
			}?>
		</h2>
				
		<input name="BNK_STMT_NBR" value="<?php echo $row['BNK_STMT_NBR'];if($row['BNK_STMT_NBR']==""){echo "-1";} ?>" type="hidden" />
		
		<label class='side'>Tipe</label>
		<select name="BNK_STMT_TYP" id="BNK_STMT_TYP" class="chosen-select" style="width:120px" <?php echo $stateEnable; ?> >
					<option value="">Pilih</option>
					<option value="CR" <?php echo ($row['BNK_STMT_TYP'] == 'CR') ? "selected" : ""; ?> >CR ( Masuk )</option>
					<option value="DB" <?php echo ($row['BNK_STMT_TYP'] == 'DB') ? "selected" : ""; ?> >DB ( Keluar )</option>
		</select><br /><div class="combobox"></div> 
				
		<label class='side'>Tanggal Rekening Koran</label>
		<input id="BNK_STMT_DTE" name="BNK_STMT_DTE" value="<?php echo $row['BNK_STMT_DTE']; ?>" type="text" size="30" /><br />
		<script>
			new CalendarEightysix('BNK_STMT_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		
		
		<label class='side'>Nominal</label>
		<input id="BNK_STMT_AMT" name="BNK_STMT_AMT" value="<?php echo $row['BNK_STMT_AMT']; ?>" type="text" size="30" /><br />
		
		<?php if($security <= 1) { ?>
		<label class='side'>Keterangan Rekening Koran</label>
		<input id="BNK_STMT_DESC" name="BNK_STMT_DESC" value="<?php echo $row['BNK_STMT_DESC']; ?>" type="text" size="100" /><br />
		<?php } ?>
		
		<label class='side'>Catatan</label>
		<input id="NTE" name="NTE" value="<?php echo $row['NTE']; ?>" type="text" size="80" /><br /> 
		
		<div style="<?php echo $displaylock; ?>">		
		<label class='side'>Rekening</label>
		<select name="ACTG_TYP" id="ACTG_TYP" class="chosen-select" style="width:88px" <?php echo $stateEnable; ?> >
					<option value="">Pilih</option>
					<option value="1" <?php echo ($row['ACTG_TYP'] == '1') ? "selected" : ""; ?> >1</option>
					<option value="2" <?php echo ($row['ACTG_TYP'] == '2') ? "selected" : ""; ?> >2</option>
					<option value="3" <?php echo ($row['ACTG_TYP'] == '3') ? "selected" : ""; ?> >3</option>
				</select><br /><div class="combobox"></div> 
		</div>
		
		<?php if($security == 0) { ?>
		<label class='side'>Verifikasi</label>
		<div class='side' style='top:4px'><input name='VRFCTN_F' id='VRFCTN_F' type='checkbox' class='regular-checkbox' <?php if($row['VRFCTN_F']=="1"){echo "checked";} ?>/>&nbsp;<label for="VRFCTN_F"></label></div> <br /> <br />
		
		<?php } ?>
				
		<div id='TextBoxesGroup' style="display: inline-block;">
			<div style="float:left;">
				<label style="padding-top: 0px;">Nomor Struk 1</label>
				<span class='fa fa-plus toolbar' style="cursor:pointer;padding-top: 0px;" id='addButton' title="Add New"></span>
				<span class='fa fa-trash toolbar' style="cursor:pointer;padding-top: 0px;" id='removeButton' title="Remove"></span>
				<div id="TextBoxDiv1">
					<input name='textbox1' id='textbox1' type="text" size="45" onkeydown="limits(this);" onkeyup="limits(this);">
					<div id="respon1" style="display: initial;margin-left: 10px;"></div>
				</div>
			</div>
		</div><br/>
		<input name='VAL_Q' id='VAL_Q' type="hidden" size="5" value="1" />
		
		<input  id='submit_button'  class='process submit_button' type='submit' value='Simpan' />
	</p>
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
	
	<script type="text/javascript">
	jQuery(document).ready(function(){
	    var counter = 2;
		jQuery("#addButton").click(function () {
				
			var newTextBoxDiv = jQuery(document.createElement('div'))
				.attr("id", 'TextBoxDiv' + counter);
			newTextBoxDiv.after().html('<div style="float:left;"><label>Nomor Struk '+ counter + '</label><br/>' +
				'<input type="text" size="45" name="textbox' + counter + 
				'" id="textbox' + counter + '" onkeydown="limits(this);" onkeyup="limits(this);"><div id="respon'+counter+'" style="display: initial;margin-left: 13px;"></div></div>');
			           
			newTextBoxDiv.appendTo("#TextBoxesGroup");

			counter++;	

			document.getElementById('VAL_Q').value=counter;
			calcQ();
		});

		jQuery("#removeButton").click(function () {
			if(counter==2){
				return false;
			}

			counter--;
			jQuery("#TextBoxDiv" + counter).remove();
			document.getElementById('VAL_Q').value=counter;	
		        calcQ();
		        calcAmt();
		});

		jQuery("#getButtonValue").click(function () {
			var msg = '';
			for(i=1; i<counter; i++){
				msg += "\n Diskon " + i + " : " + jQuery('#textbox' + i).val();
			}
			alert(msg);
		});
	});
</script>
</form>
<div></div>



</body>
</html>