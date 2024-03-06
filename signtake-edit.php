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
		if($_POST['RCV_CO_NBR']==""){$RcvCoNbr="NULL";}else{$RcvCoNbr=$_POST['RCV_CO_NBR'];}
		if($_POST['ACTG_TYP']==""){$ActgType=0;}else{$ActgType=$_POST['ACTG_TYP'];}

		//Process add new
		if($TrnspNbr==-1)
		{
			$query 		= "SELECT COALESCE(MAX(TRNSP_NBR),0)+1 AS NEW_NBR FROM CMP.TRNSP_HEAD";
			$result 	= mysql_query($query);
			$row 		= mysql_fetch_array($result);
			$TrnspNbr 	= $row['NEW_NBR'];
			$query 		= "INSERT INTO CMP.TRNSP_HEAD (TRNSP_NBR) VALUES (".$TrnspNbr.")";
			$result 	= mysql_query($query);
			$create 	= "CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=".$_SESSION['personNBR'].",";
			$new 		= true;
		}
		
		//Process status journal
	   	if($_POST['TRNSP_STT_ID']!="")
	   	{
	   		$query 	= "SELECT TRNSP_STT_ID FROM CMP.TRNSP_HEAD WHERE TRNSP_NBR=$TrnspNbr";
			$result = mysql_query($query);
			$row 	= mysql_fetch_array($result);
			if($row['TRNSP_STT_ID']!=$_POST['TRNSP_STT_ID'])
			{
				$query="INSERT INTO CMP.JRN_TRNSP (TRNSP_NBR,TRNSP_STT_ID,CRT_TS,CRT_NBR)
						VALUES (".$TrnspNbr.",'".$_POST['TRNSP_STT_ID']."',CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
				//echo $query;
				$resultp=mysql_query($query);
			}
		}

		$query 	= "UPDATE CMP.TRNSP_HEAD
				SET	RCV_CO_NBR=".$RcvCoNbr.",
					REF_NBR='".$_POST['REF_NBR']."',
					ORD_NBR=".$OrdNbr.",
					ORD_TTL='".$_POST['ORD_TTL']."',
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR'].",
					ACTG_TYP = ".$ActgType.",
					TRNSP_DESC = '".$_POST['TRNSP_DESC']."'
					WHERE TRNSP_NBR=".$TrnspNbr;
        //echo $query;
	   	$result = mysql_query($query);	   	
	   	$changed= true;
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
		parent.parent.document.getElementById('content').contentDocument.getElementById('leftpane').src='signtake.php?DEL=<?php echo $TrnspNbr ?>';
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
	<input id="refresh-list" type="button" value="Refresh" onclick="syncGetContent('edit-list','signtake-edit-list.php?TRNSP_NBR=<?php echo $TrnspNbr; ?>');" />
	<input id="refresh-tot" type="button" value="Total" onclick="calcAmt();" />
</div>

<?php
	$query 	= "SELECT 
	        		THD.TRNSP_NBR,
	        		THD.DUE_TS,
	        		THD.TRNSP_TS,
	        		THD.RCV_CO_NBR,
	        		THD.ACTG_TYP,
	        		THD.TRNSP_DESC,
				THD.ORD_TTL,
	        		COM2.NAME AS NAME_CO,
	        		SUB.CAT_SUB_DESC,
	        		STT.TRNSP_STT_DESC,
	        		THD.ORD_NBR,
	        		COUNT(*) AS TYPE_CNT,
	        		SUM(TDE.TRNSP_Q) AS ITEM_CNT
	        	FROM CMP.TRNSP_HEAD THD 
	        	LEFT JOIN CMP.TRNSP_DET TDE ON THD.TRNSP_NBR=TDE.TRNSP_NBR 
	       		LEFT JOIN CMP.TRNSP_STT STT ON THD.TRNSP_STT_ID=STT.TRNSP_STT_ID
	       		LEFT JOIN RTL.RTL_STK_HEAD HED ON THD.ORD_NBR=HED.ORD_NBR 
	       		LEFT JOIN CMP.COMPANY COM ON HED.RCV_CO_NBR=COM.CO_NBR
	       		LEFT JOIN CMP.COMPANY COM2 ON HED.SHP_CO_NBR=COM2.CO_NBR
	       		LEFT JOIN RTL.CAT_SUB SUB ON HED.CAT_SUB_NBR=SUB.CAT_SUB_NBR
	       		WHERE THD.DEL_NBR=0 AND STT.TRNSP_STT_ID='RP' AND THD.TRNSP_NBR=".$TrnspNbr."
	       		GROUP BY THD.TRNSP_NBR
	       		ORDER BY THD.TRNSP_NBR DESC";
    //echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);

	
?>

<?php
	if($changed){	
		$newStr="<div style='font-weight:bold;color:#666666;font-size:12pt;display:inline;float:left'>".$row['TRNSP_NBR']."</div>";
		$newStr.="<div style='display:inline;float:right;'>".parseDateTimeLiteralShort($row['DUE_TS'])."</div>";
		$newStr.="<div style='clear:both'></div>";
		$newStr.="<div style='font-weight:700;color:#3464bc'>".$row['NAME_CO']."</div>";
		$newStr.="<div>".$row['CAT_SUB_DESC']."</div>";
		$newStr.="<span style='font-weight:700'>".$row['TRNSP_STT_DESC']."</span>";
		$newStr.="&nbsp;".$row['ORD_NBR'];
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
		<a href="transport-edit-print.php?TRNSP_NBR=<?php echo $TrnspNbr; ?>"><span class='fa fa-print toolbar'></span></a>
	</p>
</div>
			
<form id='mainForm' enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="">
	<p>
        <h3>
        	Nota Tanda Terima
        </h3>
		<h2>
			<?php echo $row['TRNSP_NBR'];if($row['TRNSP_NBR']==""){echo "Baru";} ?>
            <!--<div class='print-digital-grey' style='vertical-align:4px'><?php echo $row['ORD_NBR']; ?></div>-->
		</h2>
		<!-- Header -->
		<div style="float:left;width:140px;">
			<input id="TRNSP_NBR" name="TRNSP_NBR" type="hidden" value="<?php echo $row['TRNSP_NBR'];if($row['TRNSP_NBR']==""){echo "-1";} ?>"/>
			<input id="TRNSP_STT_ID" name="TRNSP_STT_ID" type="hidden" value="RP"/>
			
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

		</div>

		<div>
			<label style='width:150px;height:25px'>Nama Pengirim</label>
			<select name="BUY_PRSN_NBR" class="chosen-select" style="width:400px" <?php echo "$fixedHead $headerEnable"; ?> >
				<?php
					$query="SELECT PRSN_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS PRSN_DESC
							FROM CMP.PEOPLE PPL INNER JOIN CMP.CITY CIT ON PPL.CITY_ID=CIT.CITY_ID WHERE PPL.DEL_NBR = 0 ORDER BY 2";
					genCombo($query,"PRSN_NBR","PRSN_DESC",$row['BUY_PRSN_NBR'],"Kosong");
				?>
			</select><br />
            <label style='width:150px;height:25px'>Perusahaan Pengirim</label>
            <select name="RCV_CO_NBR" class="chosen-select" style="width:400px" <?php echo $headerEnable; ?> >
				<?php
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID WHERE COM.DEL_NBR = 0 ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$row['RCV_CO_NBR'],"Tunai");
				?>
			</select>
			
			<label style='width:150px;height:25px;padding-left: 140px;'>Nama Penerima</label>
			<input name="TRNSP_DESC" id="TRNSP_DESC" value="<?php echo $row['TRNSP_DESC']; ?>" type="text" style="width:393px;" <?php echo $headerRead; ?> />

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
		<script>getContent('edit-list','signtake-edit-list.php?TRNSP_NBR=<?php echo $TrnspNbr; ?>');</script>
		
						
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
