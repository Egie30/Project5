<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/alert/alert.php";
	include "framework/security/default.php";
	include "framework/functions/dotmatrix.php";
	date_default_timezone_set("Asia/Jakarta");
	
	$TrnspNbr=$_GET['TRNSP_NBR'];
    if($TrnspNbr==''){exit;}

	$Security 	= getSecurity($_SESSION['userID'],"DigitalPrint");
	$UpperSec 	= getSecurity($_SESSION['userID'],"Executive");
	$CashSec 	= getSecurity($_SESSION['userID'],"Finance");
	$Acc 		= getSecurity($_SESSION['userID'],"Accounting");

    //Process changes here
	if($_POST['TRNSP_NBR']!="")
	{
		$TrnspNbr=$_POST['TRNSP_NBR'];
		
		//Take care of nulls and timestamps
		if($_POST['ORD_NBR']==""){$OrdNbr="NULL";}else{$OrdNbr=$_POST['ORD_NBR'];}
		if($_POST['SHP_CO_NBR']==""){$ShpCoNbr="NULL";}else{$ShpCoNbr=$_POST['SHP_CO_NBR'];}
		//if($_POST['BUY_CO_NBR']==""){$BuyCoNbr="NULL";}else{$BuyCoNbr=$_POST['BUY_CO_NBR'];}
		if($_POST['VIA_CO_NBR']==""){$ViaCoNbr="NULL";}else{$ViaCoNbr=$_POST['VIA_CO_NBR'];}
		if($_POST['RCV_CO_NBR']==""){$RcvCoNbr="NULL";}else{$RcvCoNbr=$_POST['RCV_CO_NBR'];}
		if($_POST['CAR_CO_NBR']==""){$CarCoNbr="NULL";}else{$CarCoNbr=$_POST['CAR_CO_NBR'];}
		if($_POST['COR_PRSN_NBR']==""){$CorPrsnNbr="NULL";}else{$CorPrsnNbr=$_POST['COR_PRSN_NBR'];}
		if($_POST['DUE_DTE']==""){$DueTS="NULL";}else{$DueTS="'".$_POST['DUE_DTE']." ".$_POST['DUE_HR'].":".$_POST['DUE_MIN'].":00'";}
		if($_POST['PU_DTE']==""){$PUTS="NULL";}else{$PUTS="'".$_POST['PU_DTE']." ".$_POST['PU_TME']."'";}
		if($_POST['DL_DTE']==""){$DLTS="NULL";}else{$DLTS="'".$_POST['DL_DTE']." ".$_POST['DL_TME']."'";}
		if($_POST['ACTG_TYP']==""){$ActgType=0;}else{$ActgType=$_POST['ACTG_TYP'];}

		//Process add new
		if($TrnspNbr==-1)
		{
			$query="SELECT COALESCE(MAX(TRNSP_NBR),0)+1 AS NEW_NBR FROM CMP.TRNSP_HEAD";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$TrnspNbr=$row['NEW_NBR'];
			$query="INSERT INTO CMP.TRNSP_HEAD (TRNSP_NBR) VALUES (".$TrnspNbr.")";
			$result=mysql_query($query);
			$create="CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=".$_SESSION['personNBR'].",";
			$new=true;

		}
		
		//Process status journal
	   	if($_POST['TRNSP_STT_ID']!="")
	   	{
	   		$query="SELECT TRNSP_STT_ID FROM CMP.TRNSP_HEAD WHERE TRNSP_NBR=$TrnspNbr";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			if($row['TRNSP_STT_ID']!=$_POST['TRNSP_STT_ID'])
			{
				$query="INSERT INTO CMP.JRN_TRNSP (TRNSP_NBR,TRNSP_STT_ID,CRT_TS,CRT_NBR)
						VALUES (".$TrnspNbr.",'".$_POST['TRNSP_STT_ID']."',CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
				//echo $query;
				$resultp=mysql_query($query);
			}
		}

		$query="UPDATE CMP.TRNSP_HEAD
				SET TRNSP_TS='".$_POST['TRNSP_DTE']." ".$_POST['TRNSP_TME']."',
					ORD_NBR=".$OrdNbr.",
	   				TRNSP_STT_ID='".$_POST['TRNSP_STT_ID']."',
                    ORD_TTL='".$_POST['ORD_TTL']."',
					DUE_TS=".$DueTS.",
					SHP_CO_NBR=".$ShpCoNbr.",
					VIA_CO_NBR=".$ViaCoNbr.",
					RCV_CO_NBR=".$RcvCoNbr.",
					CAR_CO_NBR=".$CarCoNbr.",
					COR_PRSN_NBR=".$CorPrsnNbr.",
					REF_NBR='".$_POST['REF_NBR']."',
					PU_TS=".$PUTS.",
					DL_TS=".$DLTS.",
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR'].",
					ACTG_TYP = ".$ActgType.",
					TRNSP_DESC = '".$_POST['TRNSP_DESC']."'
					WHERE TRNSP_NBR=".$TrnspNbr;
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
	function stampTime(comboBox){
		switch (comboBox.value) {
			case "PU" : document.getElementById('PU_DTE').value=getCurDate();document.getElementById('PU_TME').value=getCurTime();break;
			case "DL" : document.getElementById('DL_TME').value=getCurTime();document.getElementById('DL_DTE').value=getCurDate();break;
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
	parent.parent.document.getElementById('transportDeleteYes').onclick=
	function () {
		parent.parent.document.getElementById('content').contentDocument.getElementById('leftpane').src='transport.php?DEL=<?php echo $TrnspNbr ?>';
		parent.parent.document.getElementById('transportDelete').style.display='none';
		parent.parent.document.getElementById('fade').style.display='none';
	};
    
	<?php
		if($new){
			echo "parent.document.getElementById('leftpane').contentDocument.location.reload(true);";	
		}else{
			if($changed){
				echo "parent.parent.document.getElementById('leftmenu').contentDocument.location.reload(true);";
			}
		}
	?>
</script>
<?php
	//Make sure there is no error so the page load is halted.
	if($new){exit;}
?>

<div style="display:none;">
	<input id="refresh-list" type="button" value="Refresh" onclick="syncGetContent('edit-list','transport-edit-list.php?TRNSP_NBR=<?php echo $TrnspNbr; ?>');" />
	<input id="refresh-tot" type="button" value="Total" onclick="calcAmt();" />
</div>

<?php
	$query="SELECT TRNSP_NBR,TRNSP_TS,THD.TRNSP_STT_ID,TRNSP_STT_DESC,BUY_PRSN_NBR,NBR,PPL.NAME AS NAME_PPL,BCO.NAME AS NAME_CO,BUY_CO_NBR,THD.REF_NBR,THD.ORD_TTL,THD.DUE_TS,SHP_CO_NBR,VIA_CO_NBR,RCV_CO_NBR,CAR_CO_NBR,COR_PRSN_NBR,THD.PU_TS,THD.PU_VAL,THD.DL_TS,THD.DL_VAL,THD.SPC_NTE,THD.CRT_TS,THD.CRT_NBR,THD.UPD_TS,THD.UPD_NBR,CRT.NAME AS NAME_CRT,UPD.NAME AS NAME_UPD,THD.ORD_NBR,THD.ACTG_TYP,THD.TRNSP_DESC
			FROM CMP.TRNSP_HEAD THD
			LEFT JOIN CMP.TRNSP_STT STT ON THD.TRNSP_STT_ID=STT.TRNSP_STT_ID
            LEFT OUTER JOIN CMP.PRN_DIG_ORD_HEAD OHD ON THD.ORD_NBR=OHD.ORD_NBR
			LEFT OUTER JOIN CMP.PEOPLE PPL ON OHD.BUY_PRSN_NBR=PPL.PRSN_NBR
			LEFT OUTER JOIN CMP.PEOPLE CPL ON THD.COR_PRSN_NBR=CPL.PRSN_NBR
			LEFT OUTER JOIN CMP.COMPANY BCO ON OHD.BUY_CO_NBR=BCO.CO_NBR
			LEFT OUTER JOIN CMP.COMPANY SCO ON THD.SHP_CO_NBR=SCO.CO_NBR
			LEFT OUTER JOIN CMP.COMPANY VCO ON THD.VIA_CO_NBR=VCO.CO_NBR
			LEFT OUTER JOIN CMP.COMPANY RCO ON THD.RCV_CO_NBR=RCO.CO_NBR
			LEFT OUTER JOIN CMP.COMPANY CCO ON THD.CAR_CO_NBR=CCO.CO_NBR
			LEFT OUTER JOIN CMP.PEOPLE CRT ON THD.CRT_NBR=CRT.PRSN_NBR
			LEFT OUTER JOIN CMP.PEOPLE UPD ON THD.UPD_NBR=UPD.PRSN_NBR
			LEFT OUTER JOIN CDW.PRN_DIG_TOP_CUST TOP ON OHD.BUY_CO_NBR=TOP.NBR
			WHERE TRNSP_NBR=".$TrnspNbr;
    //echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	
	
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
	
	if($UpperSec==6){
		$headerSelect=" ";
		}
?>

<?php
	if($changed){
        $query="SELECT NBR FROM CDW.PRN_DIG_TOP_CUST";
        $resultc=mysql_query($query);
		while($rowc=mysql_fetch_array($resultc)){
			$TopCusts[]=strval($rowc['NBR']);
		}

		//Copy the innerHTML from transport.php
		$due=strtotime($row['DUE_TS']);
		if(strtotime("now")>$due){
            $dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#d92115'></span></div>";
        }elseif(strtotime("now + ".$row['JOB_LEN_TOT']." minute")>$due){
           $dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#fbad06'></span></div>";			
        }else{
            $dot="";
        }
		//echo $due." ".strtotime("now")." ".strtotime("now + ".$row['JOB_LEN_TOT']." minute")."<br>";
				
		$newStr="<div style='font-weight:bold;color:#666666;font-size:12pt;display:inline;float:left'>".$row['TRNSP_NBR']."</div>";
		$newStr.="<div style='display:inline;float:right;'>".parseDateTimeLiteralShort($row['DUE_TS'])."</div>";
		if ((in_array($row['BUY_CO_NBR'],$TopCusts)) || $row['SPC_NTE']!="" || $row['DL_CNT']>0 || $row['PU_CNT']>0 || $row['NS_CNT']>0 || $row['NS_CNT']> 0 || $row['IVC_PRN_CNT']>0){
			$newStr.="<div style='clear:both'></div>";
			$newStr.="<div style='display:inline;float:left;'>";
	        if(in_array($row['BUY_CO_NBR'],$TopCusts)){
	            $newStr.="<div class='listable'><span class='fa fa-star listable'></span></div>";
	        }				
			if($row['NBR']!=""){
				$newStr.="<div class='listable'><span class='fa fa-star listable'></span></div>";
			}				
			if($row['SPC_NTE']!=""){
				$newStr.="<div class='listable'><span class='fa fa-comment listable'></span></div>";
			}
			if($row['DL_CNT']>0){
				$newStr.="<div class='listable'><span class='fa fa-truck listable' style='margin-left:-1px'></span></div>";
			}
			if($row['PU_CNT']>0){
				$newStr.="<div class='listable'><span class='fa fa-shopping-cart listable'></span></div>";
			}
			if($row['NS_CNT']>0){
				$newStr.="<div class='listable'><span class='fa fa-flag listable'></span></div>";
			}
			if($row['IVC_PRN_CNT']>0){
				$newStr.="<div class='listable'><span class='fa fa-print listable'></span></div>";
			}
			$newStr.="&nbsp;</div>";
		}
		$newStr.="<div style='clear:both'></div>";
		if(trim($row['NAME_PPL']." ".$row['NAME_CO'])==""){$name="Tunai";}else{$name=trim($row['NAME_PPL']." ".$row['NAME_CO']);}
		$newStr.=$dot;
		$newStr.="<div style='font-weight:700;color:#3464bc'>".$name."</div>";
		$newStr.="<div>".$row['ORD_TTL']."</div>";
		$newStr.="<div>".parseDateShort($row['ORD_TS'])."";
		$newStr.="<span style='font-weight:700'>".$row['TRNSP_STT_DESC']."</span>";
		$newStr.='&nbsp;'.$row['ORD_NBR'];
		$newStr.="<span style='float:right;style='color:#888888'>".number_format($row['TYPE_CNT'],0,'.',',')." Jenis ";
		$newStr.="".number_format($row['ITEM_CNT'],0,'.',',')." buah";
		$newStr.="</span></div></div>";
		echo "<script>";
		//echo "alert('a');";
		echo "parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$TrnspNbr."').style.opacity=0;";
		echo "parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$TrnspNbr."').style.filter='alpha(opacity=0)';";
		echo "parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$TrnspNbr."').innerHTML=".chr(34).$newStr.chr(34).";";
		echo "fadeIn(parent.document.getElementById('leftpane').contentWindow.document.getElementById('O".$TrnspNbr."'));";
		echo "</script>";
	}
?>

<div class="toolbar-only">
	<?php if(($Security==0)&&($TrnspNbr!=0)) { ?>
    <p class="toolbar-left"><a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.parent.document.getElementById('transportDelete').style.display='block';parent.parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a></p>
	<?php } ?>
	<p class="toolbar-right">
		<a href="transport-edit-print-pdf.php?TRNSP_NBR=<?php echo $TrnspNbr; ?>"><span class='fa fa-file-pdf-o toolbar'></span></a>
		<a href="transport-edit-print.php?TRNSP_NBR=<?php echo $TrnspNbr; ?>"><span class='fa fa-print toolbar'></span></a>
	</p>
</div>
			
<form id='mainForm' enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="enableCombos(this);">
	<p>
        <h3>
        	<?php 
	        	if($row['TRNSP_STT_ID']=="ST"){
	        		echo "Nota Tanda Ambil";
	        	} else if($row['TRNSP_STT_ID']=="RP"){
	        		echo "Nota Tanda Terima";
	        	} else {
	        		echo "Nota Pengiriman";
	        	}
        	?> 
        </h3>
		<h2>
			<?php echo $row['TRNSP_NBR'];if($row['TRNSP_NBR']==""){echo "Baru";} ?>
            <!--<div class='print-digital-grey' style='vertical-align:4px'><?php echo $row['ORD_NBR']; ?></div>-->
		</h2>
		<!-- Header -->
		<div style="float:left;width:140px;">
			<input id="TRNSP_NBR" name="TRNSP_NBR" type="hidden" value="<?php echo $row['TRNSP_NBR'];if($row['TRNSP_NBR']==""){echo "-1";} ?>"/>
			
			<label>Tanggal Nota</label>
			<?php 
				if($row['TRNSP_TS']==""){$TrnspDte="";}else{$TrnspDte=parseDate($row['TRNSP_TS']);}
			?>
			<input name="TRNSP_DTE" id="TRNSP_DTE" value="<?php echo $TrnspDte; ?>" type="text" style="width:110px;" <?php echo $headerRead; ?> />
			<?php if($headerRead!="readonly"){ ?>
			<script>
				new CalendarEightysix('TRNSP_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
			<?php } ?>
		</div>
		<div>
			<label>Judul Pesanan</label><br />
			<input name="ORD_TTL" id="ORD_TTL" value="<?php echo $row['ORD_TTL']; ?>" type="text" style="width:545px;" <?php echo $headerRead; ?> /><br />	
		</div>
		<div style="clear:both"></div>
		
		<div style="float:left;width:140px">
			<label>Waktu Nota</label><br />
			<?php
				if($row[TRNSP_TS]==""){$TrnspTme=date("G:i:s");}else{$TrnspTme=parseTime($row['TRNSP_TS']);}
			?>
			<input name="TRNSP_TME" id="TRNSP_TME" value="<?php echo $TrnspTme; ?>" type="text" style="width:110px;" <?php echo $headerRead; ?> />
			<?php if($headerRead!="readonly"){ ?>
            <div class='listable-btn'><span class='fa fa-clock-o listable-btn' style='font-size:14px' onclick="document.getElementById('TRNSP_TME').value=getCurTime();"></span></div>
			<?php } ?>

			<div <?php if(in_array($row['TRNSP_STT_ID'],array("ST","RP"))){ echo "style='display:none;'"; } ?>>
            <label>Tanggal Dijanjikan</label><br />
			<?php 
				if($row['DUE_TS']==""){$DueDte="";}else{$DueDte=parseDate($row['DUE_TS']);}
			?>
			<input name="DUE_DTE" id="DUE_DTE" value="<?php echo $DueDte; ?>" type="text" style="width:110px;" <?php echo $headerRead; ?> />
			<?php if($headerRead!="readonly"){ ?>
			<script>
				new CalendarEightysix('DUE_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', <?php if($DueDte==""){echo "'defaultDate': 'tomorrow',";} ?> 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
			<?php } ?>

			<label>Waktu Dijanjikan</label><br /><div class="labelbox"></div>
			<?php
				if($row['DUE_TS']==""){if(strval(date("G"))>12){$DueHr="14";}else{$DueHr="11";}}else{$DueHr=parseHour($row['DUE_TS']);}
			?>
			<select class="chosen-select" style='width:53px' name="DUE_HR" <?php echo $headerEnable; ?> ><br /><div class='labelbox'></div>
				<?php genComboArrayVal(array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23'),$DueHr); ?>
			</select>
			<?php
				if($row['DUE_TS']==""){$DueMin="00";}else{$DueMin=parseMinute($row['DUE_TS']);}
			?>
			<select class="chosen-select" style='width:53px' name="DUE_MIN" <?php echo $headerEnable; ?> ><br />
				<?php genComboArrayVal(array('00','15','30','45'),$DueMin); ?>
			</select>
			</div>
		</div>
		<div>
			<label style='width:150px;height:25px'>Nama Pembeli</label>
			<select name="BUY_PRSN_NBR" class="chosen-select" style="width:400px" <?php echo "$fixedHead $headerEnable"; ?> >
				<?php
					$query="SELECT PRSN_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS PRSN_DESC
							FROM CMP.PEOPLE PPL INNER JOIN CMP.CITY CIT ON PPL.CITY_ID=CIT.CITY_ID WHERE PPL.DEL_NBR = 0 ORDER BY 2";
					genCombo($query,"PRSN_NBR","PRSN_DESC",$row['BUY_PRSN_NBR'],"Kosong");
				?>
			</select><br />
            <label style='width:150px;height:25px'>Perusahaan Pembeli</label>
            <select name="BUY_CO_NBR" class="chosen-select" style="width:400px" <?php echo $headerEnable; ?> >
				<?php
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID WHERE COM.DEL_NBR = 0 ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$row['BUY_CO_NBR'],"Tunai");
				?>
			</select>
			<div <?php if(!in_array($row['TRNSP_STT_ID'],array("ST","RP"))){ echo "style='display:none;'"; } ?>>
			<label style='width:150px;height:25px;padding-left: 140px;'><?php if($row['TRNSP_STT_ID']=="ST") { echo 'Nama Pengambil'; } else if($row['TRNSP_STT_ID']=="RP"){ echo 'Nama Penerima'; } ?></label>
			<input name="TRNSP_DESC" id="TRNSP_DESC" value="<?php echo $row['TRNSP_DESC']; ?>" type="text" style="width:393px;" <?php echo $headerRead; ?> />
			</div>
			<div <?php if(in_array($row['TRNSP_STT_ID'],array("ST","RP"))){ echo "style='display:none;'"; } ?>>
            <label style='width:150px;height:25px'>Lokasi Pengirim</label>
            <select name="SHP_CO_NBR" class="chosen-select" style="width:400px" <?php echo $headerEnable; ?> >
				<?php
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID WHERE COM.DEL_NBR = 0 ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$row['SHP_CO_NBR'],"Sama dengan diatas");
				?>
			</select>
            <label style='width:150px;height:25px'>Lokasi Penerima</label>
            <select name="RCV_CO_NBR" class="chosen-select" style="width:400px" <?php echo $headerEnable; ?> >
				<?php
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID WHERE COM.DEL_NBR = 0 ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$row['RCV_CO_NBR'],"Sama dengan diatas");
				?>
			</select>
			</div>
			<div <?php if(in_array($row['TRNSP_STT_ID'],array("ST","RP"))){ echo "style='display:none;'"; } ?>>
            <label style='width:150px;height:25px'>Perusahaan Pengirim</label>
			<select name="CAR_CO_NBR" class="chosen-select" style="width:400px" <?php echo $headerEnable; ?> >
				<?php
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID WHERE COM.DEL_NBR = 0 ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$row['CAR_CO_NBR'],"Kosong");
				?>
			</select>
            <label style='width:150px;height:25px'>Petugas Kirim</label>
            <select name="COR_PRSN_NBR" class="chosen-select" style="width:400px" <?php echo $headerEnable; ?> >
				<?php
					$query="SELECT PRSN_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS PRSN_DESC
							FROM CMP.PEOPLE PPL INNER JOIN CMP.CITY CIT ON PPL.CITY_ID=CIT.CITY_ID WHERE PPL.DEL_NBR = 0 ORDER BY 2";
					genCombo($query,"PRSN_NBR","PRSN_DESC",$row['COR_PRSN_NBR'],"Kosong");
				?>
			</select>
			</div>
		</div>
		<div style="clear:both"></div>
		<div style="float:left;width:140px;">
            <label>No. Referensi</label><br />
			<input name="REF_NBR" id="REF_NBR" value="<?php echo $row['REF_NBR']; ?>" type="text" style="width:110px;" <?php echo $headerRead; ?> />
		</div>
		
		<div style="float:left;width:140px;">
            		<label>No. Nota</label><br />
			<input name="ORD_NBR" id="ORD_NBR" value="<?php echo $row['ORD_NBR']; ?>" type="text" style="width:110px;" <?php echo $headerRead; ?> /><br/>
		</div>

		<div <?php if(in_array($row['TRNSP_STT_ID'],array("ST","RP"))){ echo "style='display:none;'"; } ?>>
		<div style="float:left;width:140px;">
			<label>Status</label><br /><div class='labelbox'></div>
			<select name="TRNSP_STT_ID" class="chosen-select" style="width:120px" onchange="stampTime(this)" <?php echo $stateEnable; ?> >
				<?php
					$query="SELECT TRNSP_STT_ID,TRNSP_STT_DESC,TRNSP_STT_ORD
							FROM CMP.TRNSP_STT $headerSelect WHERE TRNSP_STT_ID NOT IN ('ST','RP') ORDER BY 3";
					genCombo($query,"TRNSP_STT_ID","TRNSP_STT_DESC",$row["TRNSP_STT_ID"]);
				?>
			</select><br /><div class="combobox"></div>
		</div>
		</div>

		<div <?php if (($Acc == 0) && ($locked == 0) && ($_COOKIE["LOCK"] != "LOCK")) { echo "style=''"; } else { echo "style='display:none;'"; } ?> >
			<label>Rekening</label><div class="labelbox"></div>
			<select name="ACTG_TYP" id="ACTG_TYP" class="chosen-select" style="width:74px">
					<option value="">Pilih</option>
					<option value="1" <?php echo ($row['ACTG_TYP'] == '1') ? "selected" : ""; ?> >1</option>
					<option value="2" <?php echo ($row['ACTG_TYP'] == '2') ? "selected" : ""; ?> >2</option>
					<option value="3" <?php echo ($row['ACTG_TYP'] == '3') ? "selected" : ""; ?> >3</option>
			</select><br /><div class="combobox"></div>
		</div>
    
		<div style="clear:both;"></div>
		
		<!-- listing -->
		<div id="edit-list" class="edit-list"></div>
		<script>getContent('edit-list','transport-edit-list.php?TRNSP_NBR=<?php echo $TrnspNbr; ?>');</script>
		
		<!-- Footer -->
			<div <?php if(in_array($row['TRNSP_STT_ID'],array("ST","RP"))){ echo "style='display:none;'"; } ?>>
				<div style="float:left;width:140px;">
					<label>Tanggal Diambil</label>
					<?php 
						if($row['PU_TS']==""){$PuDte="";}else{$PuDte=parseDate($row['PU_TS']);}
					?>
					<input name="PU_DTE" id="PU_DTE" value="<?php echo $PuDte; ?>" type="text" style="width:110px;" readonly />
				</div>

				<div style="float:left;width:140px;">
					<label>Waktu Diambil</label>
					<?php
						if($row['PU_TS']==""){$PuTme="";}else{$PuTme=parseTime($row['PU_TS']);}
					?>
					<input name="PU_TME" id="PU_TME" value="<?php echo $PuTme; ?>" type="text" style="width:110px;" readonly />
				</div>

				<div style="float:left;width:140px;">
					<label>Tanggal Diantar</label>
					<?php 
						if($row['DL_TS']==""){$DlDte="";}else{$DlDte=parseDate($row['DL_TS']);}
					?>
					<input name="DL_DTE" id="DL_DTE" value="<?php echo $DlDte; ?>" type="text" style="width:110px;" readonly />
				</div>

				<div style="float:left;width:140px;">
					<label>Waktu Diantar</label>
					<?php
						if($row['DL_TS']==""){$DlTme="";}else{$DlTme=parseTime($row['DL_TS']);}
					?>
					<input name="DL_TME" id="DL_TME" value="<?php echo $DlTme; ?>" type="text" style="width:110px;" readonly />
				</div>
			</div>
						
		<div style="clear:both"></div>

		<!-- This can be removed in the future.
		<div style="float:left;width:690px;">
			<label>Catatan</label><br />
			<textarea name="SPC_NTE" style="width:690px;height:40px;"><?php echo $row['SPC_NTE']; ?></textarea>
		</div>
		-->
			
		<input class="process" type="submit" value="Simpan"/>		
		
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
<table style="margin:0px;<?php //if($UpperSec>=6){echo "display:none;";} ?>">
	<tr>
	<td style="padding:0px;border:0px;vertical-align:top">
		<div class="conv">
			<textarea id="CONV" style="width:270px;height:40px;" <?php if($row['TRNSP_NBR']==""){echo "disabled='disabled'";} ?> onkeyup="if(event.keyCode==13){document.getElementById('converse').click();this.value='';}"></textarea>
			<?php
				//Old special note backward compatibility effort.
				if($row['SPC_NTE']!=""){
					$alt="-alt";
				}
			?>
            <div class='listable-btn' style='vertical-align:top;margin-left:1px;margin-top:1px'><span class='fa fa-pencil listable-btn' style="<?php if($row['TRNSP_NBR']==""){echo "display:none";} ?>" onclick="getContent('conversation','transport-edit-conversation.php?CONV='+document.getElementById('CONV').value+'&CMPT_NBR=<?php echo $TrnspNbr; ?>&ALT=<?php echo $alt; ?>');"></span></div>
			<?php
				if(($row['SPC_NTE']!="")&&($row['SPC_NTE']!="CONV_THRD")){
					echo "<div class='conv-item'>";
					echo $row['SPC_NTE']." &nbsp;<span class='fa fa-user'></span> ".shortName($row['NAME_CRT'])." &nbsp;<span class='fa fa-clock-o'></span> ";
					$time=strtotime($row['UPD_TS']);
					echo humanTiming($time)." yang lalu";
					echo "</div>";
				}
			?>
			<div id="conversation"></div>
			<script>getContent('conversation','transport-edit-conversation.php?CMPT_NBR=<?php echo $TrnspNbr; ?>&ALT=<?php echo $alt; ?>');</script>
		</div>
	</td>
	<td style="padding:0px;border:0px;vertical-align:top">
		<div class="userLog"><?php echo $row['CRT_TS']." ".shortName($row['NAME_CRT'])." membuat<br />\n"; ?>
			<?php echo $row['UPD_TS']." ".shortName($row['NAME_UPD'])." ubah akhir<br />\n"; ?>
			<?php
				$query="SELECT TRNSP_STT_DESC,CRT_TS,NAME
						FROM CMP.JRN_TRNSP JRN INNER JOIN
						CMP.TRNSP_STT STT ON JRN.TRNSP_STT_ID=STT.TRNSP_STT_ID INNER JOIN
						CMP.PEOPLE PPL ON PPL.PRSN_NBR=CRT_NBR
						WHERE TRNSP_NBR=".$TrnspNbr." ORDER BY CRT_TS";
				$result=mysql_query($query);
				while($row=mysql_fetch_array($result)){
					echo " ".$row['CRT_TS']." ".shortName($row['NAME'])." ".strtolower($row['TRNSP_STT_DESC'])."<br />\n";
				}
			?>
		</div>
	</td>
	</tr>
</table>
</body>
</html>
