<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/alert/alert.php";
	include "framework/security/default.php";
	include "framework/functions/dotmatrix.php";
	date_default_timezone_set("Asia/Jakarta");
	
	$OrdNbr=$_GET['ORD_NBR'];
	$ordTyp=$_GET['ORD_TYP'];
	//$Security=getSecurity($_SESSION['userID'],"DigitalPrint");
	$Security=getSecurity($_SESSION['userID'],"Inventory");
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");
	$CashSec=getSecurity($_SESSION['userID'],"Finance");

	//Process changes here
	if($_POST['ORD_NBR']!="")
	{
		$OrdNbr=$_POST['ORD_NBR'];
		
		//Take care of nulls and timestamps
		if($_POST['BUY_CO_NBR']==""){$BuyCoNbr="0";}else{$BuyCoNbr=$_POST['BUY_CO_NBR'];}
		if($_POST['ORD_DTE']==""){$OrdDte="NULL";}else{$OrdDte="'".$_POST['ORD_DTE']."'";}
		if($_POST['PRN_DTE']==""){$PrnDte="NULL";}else{$PrnDte="'".$_POST['PRN_DTE']."'";}
		if($_POST['PRN_CO_NBR']==""){$PrnCoNbr="0";}else{$PrnCoNbr=$_POST['PRN_CO_NBR'];}
		if($_POST['TOT_AMT']==""){$TotAmt="NULL";}else{$TotAmt=$_POST['TOT_AMT'];}
		if($_POST['FEE_FLM']==""){$FeeFlm="NULL";}else{$FeeFlm=$_POST['FEE_FLM'];}
		if($_POST['FEE_MISC']==""){$FeeMisc="NULL";}else{$FeeMisc=$_POST['FEE_MISC'];}
		if($_POST['TAX_AMT']==""){$TaxAmt="NULL";}else{$TaxAmt=$_POST['TAX_AMT'];}
		if($_POST['PYMT_DOWN']==""){$PymtDown="NULL";}else{$PymtDown=$_POST['PYMT_DOWN'];}
		if($_POST['PYMT_REM']==""){$PymtRem="NULL";}else{$PymtRem=$_POST['PYMT_REM'];}		
		if($_POST['TOT_REM']==""){$TotRem="NULL";}else{$TotRem=$_POST['TOT_REM'];}
		if($_POST['CMP_DTE']==""){$CmpDte="NULL";}else{$CmpDte="'".$_POST['CMP_DTE']."'";}
		if($_POST['PU_DTE']==""){$PUDte="NULL";}else{$PUDte="'".$_POST['PU_DTE']."'";}
		

		//Process add new
		if($OrdNbr==-1)
		{
			$query="SELECT MAX(ORD_NBR)+1 AS NEW_NBR FROM CMP.CAL_ORD_HEAD";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$OrdNbr=$row['NEW_NBR'];
			$query="INSERT INTO CMP.CAL_ORD_HEAD (ORD_NBR) VALUES (".$OrdNbr.")";
			$result=mysql_query($query);

		}
		

		$query="UPDATE CMP.CAL_ORD_HEAD
	   			SET ORD_DTE=".$OrdDte.",
	   				SEL_CO_NBR=".$_POST['SEL_CO_NBR'].",
	   				BUY_CO_NBR=".$BuyCoNbr.",
	   				REF_NBR='".$_POST['REF_NBR']."',
	   				REQ_NBR='".$_POST['REQ_NBR']."',
	   				ORD_TYP='".$_POST['ORD_TYP']."',
	   				ORD_TTL='".$_POST['ORD_TTL']."',
					PRN_DTE=".$PrnDte.",
					PRN_CO_NBR=".$PrnCoNbr.",	   				
					TOT_AMT=".$TotAmt.",
					FEE_FLM=".$FeeFlm.",
					FEE_MISC=".$FeeMisc.",
					TAX_APL_ID='".$_POST['TAX_APL_ID']."',
					TAX_AMT=".$TaxAmt.",
					PYMT_DOWN=".$PymtDown.",
					PYMT_REM=".$PymtRem.",
					TOT_REM=".$TotRem.",
					CMP_DTE=".$CmpDte.",
					PU_DTE=".$PUDte.",
	   				SPC_NTE='".$_POST['SPC_NTE']."',
					UPD_DTE=CURRENT_DATE,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE ORD_NBR=".$OrdNbr;
		$_GET['ORD_NBR']=$OrdNbr;
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

<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />

<script type="text/javascript"  src="framework/database/jquery.min.js"></script>
<script type="text/javascript">
	jQuery.noConflict();
</script>

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
	/*
	function calcAmt(){
		document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_FLM')+getInt('FEE_MISC');
		document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('PYMT_DOWN')-getInt('PYMT_REM');
	}
	*/
	function calcAmt(){
		switch (document.getElementById('TAX_APL_ID').value) {
			case "E" : 
			document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_FLM')+getInt('FEE_MISC');
			document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('PYMT_DOWN')-getInt('PYMT_REM');
			document.getElementById('TAX_AMT').value="";
			break;
			case "I" : 
			document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_FLM')+getInt('FEE_MISC');
			document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('PYMT_DOWN')-getInt('PYMT_REM');
			document.getElementById('TAX_AMT').value=getInt('TOT_AMT')*parseFloat(getParam("tax","ppn"));
			break;
			case "A" : 
			document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_FLM')+getInt('FEE_MISC');
			document.getElementById('TAX_AMT').value=getInt('TOT_AMT')*parseFloat(getParam("tax","ppn"));
			document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_FLM')+getInt('FEE_MISC')+getInt('TAX_AMT');
			document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('PYMT_DOWN')-getInt('PYMT_REM');
			break;
		}
	}
	function stampTime(comboBox){
		switch (comboBox.value) {
			case "RD" : document.getElementById('CMP_TME').value=getCurTime();document.getElementById('CMP_DTE').value=getCurDate();break;
			case "CP" : document.getElementById('PU_DTE').value=getCurDate(); document.getElementById('PU_TME').value=getCurTime();break;
		}
	}
	
	//This is to make sure that the value of each combo is submitted
	function enableCombos(button){
		
		var container=button.parentNode;
		var combos=container.getElementsByTagName('select');
		for(var count=0;count<combos.length;count++){
			var curCombo=combos[count];
			curCombo.disabled=false;
		}
	}
		
	function attach_file(p_script_url){
      // create new script element, set its relative URL, and load it
      script=document.createElement('script');
      script.src=p_script_url;
      document.getElementsByTagName('head')[0].appendChild(script);
	}
	
</script>

<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />

</head>

<body>

<script>
	parent.parent.document.getElementById('retailTypeDeleteYes').onclick=
	function () { 
		parent.parent.document.getElementById('content').src='calendar-list-order.php?ORD_TYP=<?php echo $ordTyp; ?>&DEL=<?php echo $OrdNbr ?>';
		parent.parent.document.getElementById('retailTypeDelete').style.display='none';
		parent.parent.document.getElementById('fade').style.display='none';
	};
	
<?php
	/*	if($new){
			echo "parent.document.getElementById('leftpane').contentDocument.location.reload(true);";	
		}else{
			if($changed){
				echo "parent.parent.document.getElementById('leftmenu').contentDocument.location.reload(true);";
				}
		}*/
	?>
</script>
<?php
	//Make sure there is no error so the page load is halted.
	if($new){exit;}
?>

<div style="display:none;">
	<input id="refresh-list" type="button" value="Refresh" onclick="syncGetContent('edit-list','calendar-edit-list.php?ORD_NBR=<?php echo $OrdNbr; ?>');" />
	<input id="refresh-tot" type="button" value="Total" onclick="calcAmt();" />
</div>

<?php
if ($_GET['ORD_NBR'] != ''){
	$query="SELECT ORD_NBR,ORD_DTE,REF_NBR,REQ_NBR,ORD_TYP,SEL_CO_NBR,BUY_CO_NBR,ORD_TTL,PRN_DTE,PRN_CO_NBR,FEE_FLM,FEE_MISC,CMP_DTE,PYMT_DOWN,PYMT_REM,TOT_AMT,SPC_NTE,PU_DTE,TOT_REM,TAX_APL_ID,TAX_AMT
							FROM CMP.CAL_ORD_HEAD WHERE ORD_NBR=".$OrdNbr;
							
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	}
	
	//Process security and process
	$headerRead="";
	$headerEnable="";
	$headerSelect="";
	$footerRead="";
	if($Security==1){
		if(in_array($row["ORD_STT_ID"],array('RD','DL','CP'))){
			$headerRead="readonly";
			$headerEnable="disabled";
			$headerSelect="WHERE ORD_STT_ID IN ('RD','DL','CP','".$row["ORD_STT_ID"]."')";
		}elseif(in_array($row["ORD_STT_ID"],array('PR','FN'))){
			$headerRead="readonly";
			$headerEnable="disabled";
			$headerSelect="WHERE ORD_STT_ID IN ('".$row["ORD_STT_ID"]."')";
		}elseif(in_array($row["ORD_STT_ID"],array('NE','RC','QU'))){
			$headerSelect="WHERE ORD_STT_ID IN ('NE','RC','QU','".$row["ORD_STT_ID"]."')";
		}
	}
	if($Security==2){
		$headerRead="readonly";
		$headerEnable="disabled";
		$footerRead="readonly";
		$headerSelect="WHERE ORD_STT_ID IN ('QU','PR','FN','RD','".$row["ORD_STT_ID"]."')";
		if(!in_array($row["ORD_STT_ID"],array('QU','PR','FN','RD'))){$stateEnable="disabled";}
	}
	
	if($UpperSec==7){
		$headerSelect=" ";
		}
	
?>

<?php
	if($changed){
		//Copy the innerHTML from print-digital.php
		$due=strtotime($row['DUE_TS']);
		$OrdSttId=$row['ORD_STT_ID'];
		if((strtotime("now")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
			$back="print-digital-red";
		}elseif((strtotime("now + ".$row['JOB_LEN_TOT']." minute")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
			$back="print-digital-yellow";				
		}else{
			$back="print-digital-white";
		}
		//echo $due." ".strtotime("now")." ".strtotime("now + ".$row['JOB_LEN_TOT']." minute")."<br>";
				
		$newStr="<div style='font-weight:bold;color:#666666;font-size:11pt;display:inline;float:left'>".$row['ORD_NBR']."</div>";
		$newStr.="<div class='$back' style='display:inline;width:100px;float:right;'>".parseDateShort($row['DUE_TS'])." ".parseHour($row['DUE_TS']).":".parseMinute($row['DUE_TS'])."</div>";
		$newStr.="<div style='display:inline;float:right;padding-top:2px'>";
		if($row['NBR']!=""){
			$newStr.="<img class='listable' src='img/favorite.png'>";
		}				
		if($row['SPC_NTE']!=""){
			$newStr.="<img class='listable' src='img/conversation.png'>";
		}
		if($row['DL_CNT']>0){
			$newStr.="<img class='listable' src='img/truck.png'>";
		}
		if($row['PU_CNT']>0){
			$newStr.="<img class='listable' src='img/cart.png'>";
		}
		if($row['NS_CNT']>0){
			$newStr.="<img class='listable' src='img/flag.png'>";
		}
		if($row['IVC_PRN_CNT']>0){
			$newStr.="<img class='listable' src='img/printed.png'>";
		}
		$newStr.="&nbsp;</div>";
		$newStr.="<div style='clear:both'></div>";
		if(trim($row['NAME_PPL']." ".$row['NAME_CO'])==""){$name="Tunai";}else{$name=trim($row['NAME_PPL']." ".$row['NAME_CO']);}
		$newStr.="<div style='font-weight:bold;color:#3464bc'>".$name."</div>";
		$newStr.="<div>".$row['ORD_TTL']."</div>";
		$newStr.="<div>".parseDateShort($row['ORD_TS'])."&nbsp;";
		$newStr.="<span style='font-weight:bold'>".$row['ORD_STT_DESC']."</span>";
		$newStr.="<span style='float:right;style='color:#888888'>Rp. ".number_format($row['TOT_REM'],0,'.',',')."/";
		$newStr.="Rp. ".number_format($row['TOT_AMT'],0,'.',',');
		$newStr.="</span></div>";
		echo "<script>";
		//echo "alert('a');";
		echo "parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$OrdNbr."').style.opacity=0;";
		echo "parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$OrdNbr."').style.filter='alpha(opacity=0)';";
		echo "parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$OrdNbr."').innerHTML=".chr(34).$newStr.chr(34).";";
		echo "fadeIn(parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$OrdNbr."'));";
		echo "</script>";
	}
?>


<?php if(($Security==0)&&($OrdNbr!=0)) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.document.getElementById('retailTypeDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a></p>
		<p class="toolbar-right">
		<a href="calendar-edit-print.php?ORD_NBR=<?php echo $OrdNbr; ?>&ORD_TYP=<?php echo $row['ORD_TYP']; ?>&SEL_CO_NBR=<?php echo $row['SEL_CO_NBR']; ?>&BUY_CO_NBR=<?php echo $row['BUY_CO_NBR']?>&PRN_CO_NBR=<?php echo $row['PRN_CO_NBR']?>"><img style="cursor:pointer" class="toolbar-right" src="img/print.png"></a>
		</p>
	</div>
<?php } ?>
		




			
<form id='mainForm' enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="enableCombos(this);">
	<p>
		<h2>
			Nomor nota: <?php echo $row['ORD_NBR'];if($row['ORD_NBR']==""){echo "Baru";} ?>
		</h2>
		
		<!-- Header -->
		<div style="float:left;width:140px;">
			<input id="ORD_NBR" name="ORD_NBR" type="hidden" value="<?php echo $row['ORD_NBR'];if($row['ORD_NBR']==""){echo "-1";} ?>"/>
			
			<label>Tanggal Nota</label>
			<?php 
				if($row['ORD_DTE']==""){$OrdDte="";}else{$OrdDte=parseDate($row['ORD_DTE']);}
			?>
			<input name="ORD_DTE" id="ORD_DTE" value="<?php echo $OrdDte; ?>" type="text" style="width:115px;" <?php echo $headerRead; ?> />
			<?php if($headerRead!="readonly"){ ?>
			<script>
				new CalendarEightysix('ORD_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
			<?php } ?>
		</div>
		<div>
		<div style="float:left;width:140px;">
			<label>No. Order</label><br />
			<input name="REQ_NBR" id="REQ_NBR" value="<?php echo $row['REQ_NBR']; ?>" type="text" style="width:115px;" <?php echo $headerRead; ?> />
		</div>
		<div>
			<div style="float:left;width:140px;">
			<label>Judul Pesanan</label><br />
			<input name="ORD_TTL" id="ORD_TTL" value="<?php echo $row['ORD_TTL']; ?>" type="text" style="width:405px;" <?php echo $headerRead; ?> /><br />	
		</div>

		<div style="clear:both"></div>
		
			<div style="float:left;width:140px;">
			<label>No. Referensi</label><br />
			<input name="REF_NBR" id="REF_NBR" value="<?php echo $row['REF_NBR']; ?>" type="text" style="width:115px;" <?php echo $headerRead; ?> />
		</div>
		<div>
				<div style="float:left;width:140px;">
				<label>Nama Penjual</label><br /><div class='labelbox'></div>
				<select name="SEL_CO_NBR" class="chosen-select" style="width:550px" <?php echo $headerEnable; ?> >
					<?php
					/*	$query="SELECT PRSN_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS PRSN_DESC
								FROM CMP.PEOPLE PPL INNER JOIN CMP.CITY CIT ON PPL.CITY_ID=CIT.CITY_ID ORDER BY 2";
						genCombo($query,"PRSN_NBR","PRSN_DESC",$row['BUY_PRSN_NBR'],"Tunai");
					*/	
						if($row['SEL_CO_NBR']==""){$SelCoID=1;}else{$SelCoID=$row['SEL_CO_NBR'];}
										$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
												FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
										genCombo($query,"CO_NBR","CO_DESC",$SelCoID);
					?>
				</select><br /><div class="combobox"></div>
			</div>
		
		<div style="clear:both"></div>
		
		<div style="float:left;width:140px;">		
			<label>Tanggal Dijanjikan</label><br />
			<?php 
				if($row['PRN_DTE']==""){$DueDte="";}else{$DueDte=parseDate($row['PRN_DTE']);}
			?>
			<input name="PRN_DTE" id="DUE_DTE" value="<?php echo $DueDte; ?>" type="text" style="width:115px;" <?php echo $headerRead; ?> />
			<?php if($headerRead!="readonly"){ ?>
			<script>
				new CalendarEightysix('DUE_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', <?php if($DueDte==""){echo "'defaultDate': 'tomorrow',";} ?> 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
			<?php } ?>
		</div>
		
		<div>
			<label>Nama Pembeli</label><br /><div class='labelbox'></div>
			<select name="BUY_CO_NBR" class="chosen-select" style="width:550px" <?php echo $headerEnable; ?> >
				<?php
				
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
											FROM CMP.COMPANY COM INNER JOIN CMP	.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
									genCombo($query,"CO_NBR","CO_DESC",$row['BUY_CO_NBR'],"Tunai");
				?>
			</select><br /><div class="combobox"></div>
		</div>

		<div style="clear:both;"></div>
		
		<div style="float:left;width:140px;">
			<label>Jenis Nota </label><br /><div class='labelbox'></div>
			<select name="ORD_TYP" class="chosen-select" style="width:120px" onchange="stampTime(this)" <?php echo $stateEnable; ?> >
				<?php
					$query="SELECT ORD_TYP,ORD_DESC FROM CMP.ORD_TYP ORDER BY 2 ASC";
					$OrdType=$row['ORD_TYP'];
					if ($OrdType==""){
						$OrdType=$ordTyp;
					}
					genCombo($query,"ORD_TYP","ORD_DESC",$OrdType);
				?>
			</select><br /><div class="combobox"></div>
		</div>
		
		<div style="float:left;width:140px;">
			<label>PPN</label><br /><div class='labelbox'></div>
			<select name="TAX_APL_ID" id="TAX_APL_ID"  class="chosen-select" onchange="calcAmt()" <?php echo $stateEnable; ?> >
			<?php
				if($row["TAX_APL_ID"]==""){$TaxApl="E";}else{$TaxApl=$row["TAX_APL_ID"];}
				$query="SELECT TAX_APL_ID,TAX_APL_DESC
						FROM CMP.TAX_APL ORDER BY SORT";
				genCombo($query,"TAX_APL_ID","TAX_APL_DESC",$TaxApl);
			?>
			</select><br /><div class="combobox"></div>
		</div>
		
		<div>
			<label>Nama Pencetak</label><br /><div class='labelbox'></div>
			<select name="PRN_CO_NBR" class="chosen-select" style="width:410px" <?php echo $headerEnable; ?> >
				<?php
					//if($row['PRN_CO_NBR']==""){$PrnCoID=$CoNbrDef;}else{$PrnCoID=$row['PRN_CO_NBR'];}
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
											FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
									genCombo($query,"CO_NBR","CO_DESC",$PrnCoID,"Kosong");
				?>
			</select><br /><div class="combobox"></div>
		</div>
		<div style="clear:both;"></div>
		
		
		
		<!-- listing -->
		<div id="edit-list" class="edit-list"></div>
		<script>getContent('edit-list','calendar-edit-list.php?ORD_NBR=<?php echo $OrdNbr; ?>');</script>
		
		<!-- Footer -->
		<div style="float:left;width:140px;">
			<label>Ongkos Extra</label><br />
			<input name="FEE_MISC" id="FEE_MISC" value="<?php echo $row['FEE_MISC']; ?>" onkeyup="calcAmt();" onchange="calcAmt();" type="text" style="width:110px;" <?php echo $footerRead; ?> /><br />	
		</div>

		<div style="float:left;width:140px;">
			<label>Ongkos Film</label><br />
			<input name="FEE_FLM" id="FEE_FLM" value="<?php echo $row['FEE_FLM']; ?>" onkeyup="calcAmt();" onchange="calcAmt();" type="text" style="width:110px;" <?php echo $footerRead; ?> /><br />
		</div>
		
		<div style="float:left;width:140px;">
			<label>PPN</label><br />
			<input name="TAX_AMT" id="TAX_AMT" value="<?php echo $row['TAX_AMT']; ?>" onkeyup="calcAmt();" onchange="calcAmt();" type="text" style="width:110px;" <?php echo $footerRead; ?> /><br />	
		</div>

		<div style="float:left;width:140px;">
			<label>Total Nota</label><br />
			<input name="TOT_AMT" id="TOT_AMT" value="<?php echo $row['TOT_AMT']; ?>" type="text" style="width:110px;" readonly /><br />	
		</div>

		<div style="float:left;width:140px;">
			<label>Tanggal Diambil</label>
			<?php 
				if($row['PU_DTE']==""){$PUDte="";}else{$PUDte=parseDate($row['PU_DTE']);}
			?>	
			
			<input name="PU_DTE" id="PU_DTE" value="<?php echo $PUDte; ?>" type="text" style="width:110px;" <?php echo $headerRead; ?> />
			<?php if($headerRead!="readonly"){ ?>
			<script>
				new CalendarEightysix('PU_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', <?php if($DueDte==""){echo "'defaultDate': null,'prefill':false,";} ?> 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
			<?php } ?>
		</div>

		<div style="clear:both"></div>
		
		<div style="float:left;width:140px;">
			<label>Uang Titipan</label><br />
			<input name="PYMT_DOWN" id="PYMT_DOWN" value="<?php echo $row['PYMT_DOWN']; ?>" <?php if((($row['PYMT_DOWN']!="")&&($row['VAL_PYMT_DOWN']!=""))){echo "readonly";} ?> onkeyup="calcAmt();document.getElementById('paymentDown').style.display='none';" onchange="calcAmt();"  type="text" style="width:110px;" <?php echo $footerRead; ?> />	
			<span <?php if(!(
				(($row['PYMT_DOWN']!=0)||($row['PYMT_DOWN']!='')) && 
				($row['VAL_PYMT_REM']=='') &&
				($row['VAL_PYMT_DOWN']=='') &&
				($CashSec<=2)
				)){echo "style='display:none'";} ?>><img id="paymentDown" src="img/register.png" style="cursor:pointer" />
			</span>
		</div>

		<div style="float:left;width:140px;">
			<label>Pelunasan</label><br />
			<input name="PYMT_REM" id="PYMT_REM" value="<?php echo $row['PYMT_REM']; ?>" <?php if((($row['PYMT_REM']!="")&&($row['VAL_PYMT_REM']!=""))){echo "readonly";} ?> onkeyup="calcAmt();document.getElementById('paymentFulfillment').style.display='none';" onchange="calcAmt();"  type="text" style="width:110px;" <?php echo $footerRead; ?> />	
		</div>

		<div style="float:left;width:140px;">
			<label>Sisa</label>
			<input name="TOT_REM" id="TOT_REM" value="<?php echo $row['TOT_REM']; ?>" type="text" style="width:110px;" readonly />
			<img src="img/calc.png" style="cursor:pointer" onclick="calcAmt();" >
		</div>
		
		<div style="float:left;width:140px;">
			<label>Tanggal Jadi</label>
			<?php 
				if($row['CMP_DTE']==""){$CmpDte="";}else{$CmpDte=parseDate($row['CMP_DTE']);}
			?>
			<input name="CMP_DTE" id="CMP_DTE" value="<?php echo $CmpDte; ?>" type="text" style="width:110px;" readonly />
			<?php if($headerRead!="readonly"){ ?>
			<script>
				new CalendarEightysix('CMP_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', <?php if($DueDte==""){echo "'defaultDate': null,'prefill':false,";} ?> 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
			<?php } ?>
		</div>	
		
		<div style="clear:both"></div>

		

		<!-- This can be removed in the future.
		<div style="float:left;width:690px;">
			<label>Catatan</label><br />
			<textarea name="SPC_NTE" style="width:690px;height:40px;"><?php echo $row['SPC_NTE']; ?></textarea>
		</div>
		-->
			
		<div style="float:left;width:145px;">
			<label>Catatan</label><br />
			<textarea name="SPC_NTE" style="width:690px;height:40px;"><?php echo $row['SPC_NTE']; ?></textarea>
		</div>
		<div style="clear:both"></div>
		<input class="process" type="submit" value="Simpan"/>	
		<div style="clear:both"></div>		
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
				jQuery(selector).chosen(config[selector]);
			}
		</script>

	</p>		
</form>

</body>
</html>
