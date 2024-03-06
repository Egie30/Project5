<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/functions/dotmatrix.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	
	$Security=getSecurity($_SESSION['userID'],"Inventory");
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");
	$Acc=getSecurity($_SESSION['userID'],"Accounting");
	$OrdNbr	= $_GET['ORD_NBR'];
	$IvcTyp	= $_GET['IVC_TYP'];
	$type	= $_GET['TYP'];
	
	if($type == "EST"){
		$headtable 	= "RTL.RTL_ORD_HEAD_EST";
		$detailtable= "RTL.RTL_ORD_DET_EST";
	}else{
		$headtable 	= "RTL.RTL_ORD_HEAD";
		$detailtable= "RTL.RTL_ORD_DET";
	}
	
	//Process changes here
	if($_POST['ORD_NBR']!="")
	{
		$OrdNbr=$_POST['ORD_NBR'];
		
		//Take care of nulls and timestamps
		if($_POST['RCV_CO_NBR']==""){$RcvCoNbr="NULL";}else{$RcvCoNbr=$_POST['RCV_CO_NBR'];}
		if($_POST['SHP_CO_NBR']==""){$ShpCoNbr="NULL";}else{$ShpCoNbr=$_POST['SHP_CO_NBR'];}
		if($_POST['BIL_CO_NBR']==""){$BilCoNbr="NULL";}else{$BilCoNbr=$_POST['BIL_CO_NBR'];}
		if($_POST['REF_NBR']==""){$refCoNbr="NULL";}else{$refCoNbr=$_POST['REF_NBR'];}
		if($_POST['FEE_MISC']==""){$FeeMisc="NULL";}else{$FeeMisc=$_POST['FEE_MISC'];}
		if($_POST['TOT_AMT']==""){$TotAmt="NULL";}else{$TotAmt=$_POST['TOT_AMT'];}		
		if($_POST['PYMT_DOWN']==""){$PymtDown="NULL";}else{$PymtDown=$_POST['PYMT_DOWN'];}
		if($_POST['PYMT_REM']==""){$PymtRem="NULL";}else{$PymtRem=$_POST['PYMT_REM'];}
		if($_POST['TOT_REM']==""){$TotRem="NULL";}else{$TotRem=$_POST['TOT_REM'];}
		if($_POST['TAX_AMT']==""){$TaxAmt="NULL";}else{$TaxAmt=$_POST['TAX_AMT'];}
		if($_POST['DL_DTE']==""){$DLTS="NULL";}else{$DLTS="'".$_POST['DL_DTE']." ".$_POST['DL_TME']."'";}
		if($_POST['SLS_PRSN_NBR']==""){$SlsPrsnNbr="NULL";}else{$SlsPrsnNbr=$_POST['SLS_PRSN_NBR'];}
		if($_POST['ACTG_TYP']==""){$ActgType=0;}else{$ActgType=$_POST['ACTG_TYP'];}
		
		//Process add new
		if($OrdNbr==-1)
		{
			$query="SELECT COALESCE(MAX(ORD_NBR),0)+1 AS NEW_NBR FROM ". $headtable ."";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$OrdNbr=$row['NEW_NBR'];
			$query="INSERT INTO ". $headtable ." (ORD_NBR) VALUES (".$OrdNbr.")";
			$result=mysql_query($query);
			$create="CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=".$_SESSION['personNBR'].",";
			$new=true;
		}
		
	   	//Process status journal
	   	if($_POST['ORD_STT_ID']!=""){
	   		$query="SELECT ORD_STT_ID FROM ". $headtable ." WHERE ORD_NBR = ".$OrdNbr;
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			if($row['ORD_STT_ID']!=$_POST['ORD_STT_ID']){
				if($type != "EST"){
					$query="INSERT INTO RTL.JRN_SLS (ORD_NBR, ORD_STT_ID, CRT_TS, CRT_NBR)
						VALUES (".$OrdNbr.",'".$_POST['ORD_STT_ID']."',CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
					//echo $query."<br>";
					$resultp=mysql_query($query);
				}
			}
		}
		
		$query="UPDATE ". $headtable ."
				SET ORD_DTE='".$_POST['ORD_DTE']."',
					ORD_STT_ID='".$_POST['ORD_STT_ID']."',
					RCV_CO_NBR=".$RcvCoNbr.",
					BIL_CO_NBR=".$BilCoNbr.",
					REF_NBR='".$_POST['REF_NBR']."',
					ORD_TTL='".mysql_real_escape_string($_POST['ORD_TTL'])."',
					IVC_TYP='".$_POST['IVC_TYP']."',
					SHP_CO_NBR=".$_POST['SHP_CO_NBR'].",
					FEE_MISC=".$FeeMisc.",
					TOT_AMT=".$TotAmt.",
					PYMT_DOWN=".$PymtDown.",
					PYMT_REM=".$PymtRem.",
					TOT_REM=".$TotRem.",
					DL_TS=".$DLTS.",
					SPC_NTE='".$_POST['SPC_NTE']."',".$create."
					TAX_APL_ID='".$_POST['TAX_APL_ID']."',
					TAX_AMT=".$TaxAmt.",
					TAX_IVC_NBR='".$_POST['TAX_IVC_NBR']."',
					SLS_PRSN_NBR=".$SlsPrsnNbr.",
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR'].",
					ACTG_TYP = ".$ActgType."
					WHERE ORD_NBR=".$OrdNbr;
		//echo $query;
	   	$result=mysql_query($query);

		if($_POST['ORD_STT_ID']=='CP'){
			
			$query 	= "UPDATE ". $headtable ." SET CMP_TS=CURRENT_TIMESTAMP WHERE ORD_NBR=".$OrdNbr." AND CMP_TS IS NULL";
			
			mysql_query($query);
		
		}

		$changed=true;
	   	$IvcTyp=$_POST['IVC_TYP'];
		//break;
	}
		//calculate status purchasing order
		
		if($OrdNbr<>'0'){
			$query="SELECT SUM(ORD_Q) VPO FROM ". $detailtable ." WHERE ORD_NBR=$OrdNbr;";
			//echo $query."<br>";
			$result=mysql_query($query);
			$r=mysql_fetch_array($result);
			$VPO=$r['VPO'];
			
			$query="SELECT SUM(ORD_Q) VORD FROM ". $detailtable ." JOIN ". $headtable ." ON RTL_ORD_HEAD.ORD_NBR=RTL_ORD_DET.ORD_NBR WHERE REF_NBR=$OrdNbr;";
			//echo $query."<br>";
			$result=mysql_query($query);
			$r=mysql_fetch_array($result);
			if($r['VORD']!=""){
				$VStat=$r['VORD'];
				if($VStat==0){
					$VStat=$VPO;
				}
				$VStat=100/($VPO/$VStat);
				$query="UPDATE ". $headtable ." SET REF_NBR=$VStat WHERE ORD_NBR=$OrdNbr;";
				//echo $query;
				//mysql_query($query);
				$VStat=($VStat/100)*545;
			}else{
				$VStat=0;
			}
		}else{
			$VStat=0;
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
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
<script type="text/javascript" src="framework/functions/default.js"></script>
<link rel="stylesheet" href="framework/combobox/chosen.css">

<script type="text/javascript">

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
	
	function getFloat(objectID)
	{
		if(document.getElementById(objectID).value=="")
		{
			return 0;
		}else{
			return parseFloat(document.getElementById(objectID).value);
		}
	}

	function calcAmt(){
		<?php
		$query	= "SELECT ORD_DTE FROM ". $headtable ." WHERE DEL_F=0 AND ORD_NBR =".$OrdNbr;
		//echo $query;
		$result	= mysql_query($query);
		$row	= mysql_fetch_array($result);
		$orderDte	= $row['ORD_DTE'];
		?>
		switch (document.getElementById('TAX_APL_ID').value) {
			case "E" : 
			document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_MISC');
			document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('TOT_PAY')-getInt('TND_AMT');
			document.getElementById('TAX_AMT').value="";
			document.getElementById('TAX_PCT').value="";
			break;
			case "I" : 
			document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_MISC');
			document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('TOT_PAY')-getInt('TND_AMT');
			<?php if($orderDte < '2022-04-01' && $OrdNbr != 0){ ?>
			document.getElementById('TAX_PCT').value= 0.1 * 100;
			<?php }else{ ?>
			document.getElementById('TAX_PCT').value=parseFloat(getParam("tax","ppn")) * 100;
			<?php } ?>
			document.getElementById('TAX_AMT').value=getInt('TOT_AMT')*(getFloat('TAX_PCT')/100);
			break;
			case "A" : 
			document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_MISC');
			<?php if($orderDte < '2022-04-01'){ ?>
			document.getElementById('TAX_PCT').value= 0.1 * 100;
			<?php }else{ ?>
			document.getElementById('TAX_PCT').value=parseFloat(getParam("tax","ppn")) * 100;
			<?php } ?>
			document.getElementById('TAX_AMT').value=getInt('TOT_AMT')*(getFloat('TAX_PCT')/100);
			document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_MISC')+getInt('TAX_AMT');
			document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('TOT_PAY')-getInt('TND_AMT');
			break;
		}
	}
	function calcTax(){
		document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_MISC');
		document.getElementById('TAX_AMT').value=Math.round(getInt('TOT_AMT')*(getFloat('TAX_PCT')/100));
		document.getElementById('TOT_AMT').value=getInt('TOT_NET')+getInt('FEE_MISC')+getInt('TAX_AMT');
		document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('TOT_PAY')-getInt('TND_AMT');
	}
</script>

<script type="text/javascript">
function calcAmtChild(TOT_NET){
		switch (document.getElementById('TAX_APL_ID').value) {
		case "E" : 
		document.getElementById('TOT_AMT').value=parseInt(TOT_NET)+getInt('FEE_MISC');
		document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('TOT_PAY')-getInt('TND_AMT');
		document.getElementById('TAX_AMT').value="";
		break;
		case "I" : 
		document.getElementById('TOT_AMT').value=parseInt(TOT_NET)+getInt('FEE_MISC');
		document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('TOT_PAY')-getInt('TND_AMT');
		document.getElementById('TAX_AMT').value=getInt('TOT_AMT')*parseFloat(getParam("tax","ppn"));
		break;
		case "A" : 
		document.getElementById('TOT_AMT').value=parseInt(TOT_NET)+getInt('FEE_MISC');
		document.getElementById('TAX_AMT').value=getInt('TOT_AMT')*parseFloat(getParam("tax","ppn"));
		document.getElementById('TOT_AMT').value=parseInt(TOT_NET)+getInt('FEE_MISC')+getInt('TAX_AMT');
		document.getElementById('TOT_REM').value=getInt('TOT_AMT')-getInt('TOT_PAY')-getInt('TND_AMT');
		break;
	}
}
</script>

<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />
<style type="text/css">
	#box{ border:1px solid #ccc; width:545px; height:20px;background:#ffffff; }
	#perc{ background:#ccc; height:20px; width:<?php echo $VStat; ?>px;}
</style>
</head>

<body>

<script>
	parent.parent.document.getElementById('invoiceDeleteYes').onclick=
	function () { 
		parent.parent.document.getElementById('content').contentDocument.getElementById('leftpane').src='retail-order.php?DEL=<?php echo $OrdNbr ?>&TYP=<?php echo $type; ?>';
		parent.parent.document.getElementById('invoiceDelete').style.display='none';
		parent.parent.document.getElementById('fade').style.display='none';
	};
	
	parent.parent.document.getElementById('transportCreateYes').onclick=
    function () {
        createDelivery();
		parent.parent.document.getElementById('transportCreate').style.display='none';
		parent.parent.document.getElementById('fade').style.display='none'; 
	};

	parent.parent.document.getElementById('signtakeCreateYes').onclick=
    function () {
        createSignTake();
		parent.parent.document.getElementById('signtakeCreate').style.display='none';
		parent.parent.document.getElementById('fade').style.display='none'; 
	};
	
	<?php
		if($new){
			echo "parent.document.getElementById('leftpane').contentDocument.location.reload(true);";	
		}else{
			if($changed && $IvcTyp == "SL"){
				echo "parent.parent.document.getElementById('leftmenu').contentDocument.location.reload(true);";
			}
		}
	?>
	
	function checkDelivery(){
        var c=document.getElementsByTagName('input');
        var queryStr='';
            for(var i=0;i<c.length;i++){
            if(c[i].type=='checkbox') {
                if(c[i].name.substr(0,8)=='SEL_IMG_'){
                    if(c[i].checked){;
                        queryStr+=c[i].name.substr(8,c[i].name.length-8)+',';
                    }
                }
            }
        }
        if(queryStr==''){
            window.scrollTo(0,0);parent.parent.document.getElementById('transportBlank').style.display='block';
            parent.parent.document.getElementById('fade').style.display='block';
        }else{
            window.scrollTo(0,0);parent.parent.document.getElementById('transportCreate').style.display='block';
            parent.parent.document.getElementById('fade').style.display='block';
        }
    }
	
	function createDelivery(){
        var c=document.getElementsByTagName('input');
        var queryStr='';
            for(var i=0;i<c.length;i++){
            if(c[i].type=='checkbox') {
                if(c[i].name.substr(0,8)=='SEL_IMG_'){
                    if(c[i].checked){;
                        queryStr+=c[i].name.substr(8,c[i].name.length-8)+',';
                    }
                }
            }
        }
        if(queryStr==''){return;}
        //parent.parent.document.getElementById('transport').click();
        parent.parent.document.getElementById('leftmenu').src='retail-order-lm.php?STT=IN';
        queryStr='retail-transport-tripane.php?ORD_NBR=<?php echo $OrdNbr; ?>&ORD_DET_NBR='+queryStr.substr(0,queryStr.length-1)+'&STT=ADD&STT_TYP=IN';
        parent.location.href=queryStr;
        //alert(queryStr);
    }
</script>
<script>
	function checkConvert2(){
		parent.document.getElementById('printDigitalPopupEditContent').src='retail-order-edit-print-popup.php?ORD_NBR=<?php echo $OrdNbr; ?>';
		parent.document.getElementById('printDigitalPopupEdit').style.display='block';
		parent.document.getElementById('fade').style.display='block';
	};
</script>

<div style="display:none;">
	<input id="refresh-list" type="button" value="Refresh" onclick="syncGetContent('edit-list','retail-order-edit-list.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>');" />
	<input id="refresh-list-del" type="button" value="Refresh" onclick="syncGetContent('edit-list-del','print-digital-edit-list.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>');" />
	<input id="refresh-tot" type="button" value="Total" onclick="calcAmt();" />
</div>

<?php
	$query="SELECT ORD_NBR,
		ORD_DTE,
		IVC_TYP,
		REF_NBR,
		ORD_TTL,
		SHP_CO_NBR,
		SHP.NAME AS SHP_NAME,
		RCV_CO_NBR,
		RCV.NAME AS RCV_NAME,
		HED.ORD_STT_ID,
		ORD_STT_DESC,
		SLS_PRSN_NBR,
		FEE_MISC,
		TOT_AMT,
		TAX_APL_ID,
		TAX_AMT,
		TAX_IVC_NBR,
		PYMT_DOWN,
		PYMT_REM,
		TOT_REM,
		DL_TS,
		SPC_NTE,
		HED.ACTG_TYP,
		HED.BIL_CO_NBR,
		HED.CMP_TS,
		HED.CRT_TS,
		HED.CRT_NBR,
		CRT.NAME AS NAME_CRT,
		HED.UPD_TS,
		HED.UPD_NBR,
		UPD.NAME AS NAME_UPD
	FROM ". $headtable ." HED
		INNER JOIN RTL.ORD_STT STT ON HED.ORD_STT_ID = STT.ORD_STT_ID 
		LEFT OUTER JOIN CMP.COMPANY RCV ON HED.RCV_CO_NBR = RCV.CO_NBR 
		LEFT OUTER JOIN CMP.COMPANY SHP ON HED.SHP_CO_NBR = SHP.CO_NBR
		LEFT OUTER JOIN CMP.PEOPLE CRT ON HED.CRT_NBR=CRT.PRSN_NBR
		LEFT OUTER JOIN CMP.PEOPLE UPD ON HED.UPD_NBR=UPD.PRSN_NBR
	WHERE ORD_NBR=".$OrdNbr;
	// echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	
	
	if($changed){
		//Mirror the innerHTML from print-digital.php
		$due		= strtotime($row['ORD_DTE']);
		$OrdSttId	= $row['ORD_STT_ID'];
		if((strtotime("now")>$due) && ($statusID != "NE")){
			$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#d92115'></span></div>";
		}elseif((strtotime("now")>$due) && ($statusID != "NE")){
			$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#fbad06'></span></div>";				
		}else{
			$dot="";
		}
				
		$newStr="<div style='font-weight:bold;color:#666666;font-size:12pt;display:inline;float:left'>".$row['ORD_NBR']."</div>";
		$newStr.="<div style='display:inline;float:right;'>".parseDateTimeLiteralShort($row['DL_TS'])."</div>";
		$newStr.="<div style='clear:both'></div>";
		if(trim($row['RCV_NAME'])==""){$name="Tunai";}else{$name=trim($row['RCV_NAME']);}
		$newStr.= $dot;
		$newStr.="<div style='font-weight:700;color:#3464bc'>".$name."</div>";
		$newStr.="<div>".$row['ORD_TTL']."</div>";
		$newStr.="<div>".$row['ORD_DTE']."&nbsp;";
		$newStr.="<span style='font-weight:700'>".$row['ORD_STT_DESC']."</span>";
		$newStr.="<span style='float:right;style='color:#888888'>";
		if($row['TOT_REM']==0){
		$newStr.="<div class='listable' style='display:inline;float:left'><span class='fa fa-circle listable' style='font-size:8pt;color:#3464bc'></span></div>";
        }elseif($row['TOT_AMT']==$row['TOT_REM']){
		$newStr.="<div class='listable' style='display:inline;float:left'><span class='fa fa-circle-o listable' style='font-size:8pt;color:#3464bc'></span></div>";
        }else{
		$newStr.="<div class='listable' style='display:inline;float:left'><span class='fa fa-dot-circle-o listable' style='font-size:8pt;color:#3464bc'></span></div>";
        }
		$newStr.="&nbsp;Rp. ".number_format($row['TOT_AMT'],0,'.',',');
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

<?php if($OrdNbr!=0) { ?>
	<div class="toolbar-only">
		<?php if ($Security==0){  ?>
		<p class="toolbar-left"><a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.parent.document.getElementById('invoiceDelete').style.display='block';parent.parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer;>"></span></a></p>
		<?php } ?>
		<p class="toolbar-right">
			<a href="retail-order-edit-pdf.new.php?ORD_NBR=<?php echo $OrdNbr; ?>&IVC_TYP=<?php echo $IvcTyp; ?>&TYP=<?php echo $type; ?>&TYPE=PRINT"><span class='fa fa-file-powerpoint-o toolbar' style="cursor:pointer"></span></a>

			<a href="retail-order-edit-pdf.new.php?ORD_NBR=<?php echo $OrdNbr; ?>&IVC_TYP=<?php echo $IvcTyp; ?>&TYP=<?php echo $type; ?>&TYPE=PDF"><span class='fa fa-file-pdf-o toolbar' style="cursor:pointer"></span></a>
			
			<a href="javascript:void(0)" onclick="slideFormIn('retail-order-edit-print-popup.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>');"><span class='fa fa-file-pdf-o toolbar' style="cursor:pointer"></span></a>
			
			<a href="javascript:void(0)" onClick="checkDelivery()">
				<span class='fa fa-truck fa-flip-horizontal toolbar' style='cursor:pointer'></span>
			</a>
			<!--
			<a href="retail-order-edit-print-orddetnbr.php?ORD_NBR=<?php echo $OrdNbr; ?>"><span style="cursor:pointer" class="fa fa-tag toolbar" ></span></a>

			<img src="img/tag.png" class="toolbar-middle" style='cursor:pointer' onclick="parent.document.getElementById('retailStockBarcodeWhiteContent').src='retail-stock-edit-print-lead.php?ORD_NBR=<?php echo $OrdNbr; ?>';parent.document.getElementById('retailStockBarcodeWhite').style.display='block';parent.document.getElementById('fade').style.display='block'">
			-->
			<a href="retail-order-edit-print.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>&PRN_TYP=SL"><span class='fa fa-print toolbar'></span></a>
		</p>
	</div>
	
<?php } ?>
			
<form enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="return checkform();">
	<p>
		<h3>
            Nota Penjualan
        </h3>
		<h2>
			<?php echo $row['ORD_NBR'];if($row['ORD_NBR']==""){echo "Baru";} ?>
		</h2>
		
		<!-- Header -->
		
		<div style="float:left;width:140px;">
			<input id="ORD_NBR" name="ORD_NBR" type="hidden" value="<?php echo $row['ORD_NBR'];if($row['ORD_NBR']==""){echo "-1";} ?>"/>
			<input id="IVC_TYP" name="IVC_TYP" type="hidden" value="<?php echo $row['IVC_TYP'];if($row['IVC_TYP']==""){echo $IvcTyp;} ?>"/>
			<label>Tanggal Nota</label>
			<?php 
				if($row['ORD_DTE']==""){$OrdDte="";}else{$OrdDte=parseDate($row['ORD_DTE']);}
			?>
			<input name="ORD_DTE" id="ORD_DTE" value="<?php echo $OrdDte; ?>" type="text" style="width:110px;" />
			<script>
				new CalendarEightysix('ORD_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
			
		</div>
		
		<div>
			<label>Judul Pesanan</label><br />
			<input name="ORD_TTL" id="ORD_TTL" value="<?php echo htmlentities($row['ORD_TTL'],ENT_QUOTES); ?>" type="text" style="width:545px;" <?php echo $headerRead; ?> /><br />	
		</div>
		<div style="clear:both"></div>
		
		<div style="float:left;width:140px;">
			<label>Tanggal Diterima</label>
			<?php 
				if($row['DL_TS']==""){$DLDte="";}else{$DLDte=parseDate($row['DL_TS']);}
			?>
			<input name="DL_DTE" id="DL_DTE" value="<?php echo $DLDte; ?>" type="text" style="width:110px;" />
			<script>
				new CalendarEightysix('DL_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
			</script>
		</div>	
		
		<div>
			<label>Pengirim</label><br>
			<select name="SHP_CO_NBR" style="width:545px" class="chosen-select">
				<?php
					if($row['SHP_CO_NBR'] == ""){if($IvcTyp != "RC"){$ShpCoID = $CoNbrDef;}} else {$ShpCoID = $row['SHP_CO_NBR'];}
					$query = "SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$ShpCoID);
				?>
			</select>
		</div>
		<div style="clear:both"></div>
		
		<div style="float:left;width:140px;">
			<label>Waktu Diterima</label>
			<?php
				if($row['DL_TS']==""){$DLTme=date("G:i:s");}else{$DLTme=parseTime($row['DL_TS']);}
			?>
			<input name="DL_TME" id="DL_TME" value="<?php echo $DLTme; ?>" type="text" style="width:110px;" readonly />
			<img class="action-icon" src="img/clock.png" onclick="document.getElementById('DL_TME').value=getCurTime();">
		</div>	

		<div>
			<label>Penerima</label><br>
			<select name="RCV_CO_NBR" style="width:545px;" class="chosen-select">
				<?php
					if($row['RCV_CO_NBR']=="")
					{
						if($IvcTyp=="RC")
						{
							$RcvCoID=$CoNbrDef;
						}
					}
					else
					{
						$RcvCoID=$row['RCV_CO_NBR'];
					}
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$RcvCoID,"Tunai");
				?>
			</select>
		</div>
		

		  <?php
				//Check for bad debt -- will add debt ceiling, cash transaction, and offender recording soon
				if($row['RCV_CO_NBR']!=''){
					$query="SELECT COUNT(*) AS NBR_ORD,SUM(TOT_REM) AS TOT_REM,LAST_DAY(DATE_ADD(MIN(ORD_DTE),INTERVAL COALESCE(PAY_TERM,32) DAY)) AS DATE_MIN,LAST_DAY(DATE_ADD(MAX(ORD_DTE),INTERVAL COALESCE(PAY_TERM,32) DAY)) AS DATE_MAX FROM ". $headtable ." HED INNER JOIN CMP.COMPANY COM ON HED.RCV_CO_NBR=COM.CO_NBR WHERE RCV_CO_NBR=".$row['RCV_CO_NBR']." AND TOT_REM>0 AND LAST_DAY(DATE_ADD(ORD_DTE,INTERVAL COALESCE(PAY_TERM,32) DAY))<=CURRENT_DATE AND HED.DEL_F=0";
					 // echo $query;
					$resultd=mysql_query($query);
					$rowd=mysql_fetch_array($resultd);
					if($rowd['TOT_REM']>0){
						echo "<div class='print-digital-red' style='padding-left:8px;padding-right:8px;text-align:left;display:inline-block;width:538px;margin-top:2px;margin-bottom:4px'><b>Warning</b> -- ".$rowd['NBR_ORD']." nota dengan total Rp. ".number_format($rowd['TOT_REM'],0,',','.')." telah jatuh tempo dan belum lunas.  Transaksi ini harus dibayar tunai sebelum nota jatuh tempo dilunasi.</div>";
					}
				}
			?>

		<div style="clear:both"></div>
		<div style="float:left;width:140px;">
			<label>No. Referensi</label>
			<input name="REF_NBR" id="REF_NBR" value="<?php echo $row['REF_NBR']; ?>" type="text" style="width:110px;" />
		</div>

		<div>
		<label>Pihak Yang Ditagih</label><br>
            <select name="BIL_CO_NBR" class="chosen-select" style="width:545px" >
				<?php
					$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID 
							WHERE COM.DEL_NBR=0 
							ORDER BY 2";
					genCombo($query,"CO_NBR","CO_DESC",$row['BIL_CO_NBR'],"Sama dengan diatas");
				?>
			</select>
		</div>

		<div style="float:left;width:130px;margin-top:5px;">
		<label>PPN</label><br />
		<select name="TAX_APL_ID" id="TAX_APL_ID"  class="chosen-select" style="width:120px" onchange="calcAmt()" <?php echo $stateEnable; ?> >
		<?php
			if($row["TAX_APL_ID"]==""){$TaxApl="E";}else{$TaxApl=$row["TAX_APL_ID"];}
			$query="SELECT TAX_APL_ID,TAX_APL_DESC
					FROM CMP.TAX_APL ORDER BY SORT";
			genCombo($query,"TAX_APL_ID","TAX_APL_DESC",$TaxApl);
		?>
		</select><br /><div class="combobox"></div>
		</div>
		
		<div style="float:left;width:120px;margin-top:5px;">
			<label>Status</label><br />
			<select name="ORD_STT_ID" class="chosen-select" style="width:110px" onchange="stampTime(this)" <?php echo $stateEnable; ?> >
				<?php
					$query="SELECT ORD_STT_ID,ORD_STT_DESC,ORD_STT_ORD
							FROM RTL.ORD_STT ORDER BY 3";
					genCombo($query,"ORD_STT_ID","ORD_STT_DESC",$row["ORD_STT_ID"]);
				?>
			</select><br /><div class="combobox"></div>
		</div>

		<?php 
		
		if ($Acc == 0) { $style1 = 'style="float:left;width:210px;margin-top:5px;"'; 
						 $style2 = 'style="width:200px;"'; } 
			else { $style1 = ""; $style2 ='style="width:284px;"';  }
		?>

		<div <?php echo $style1 ?>>
				<label>Sales</label><br />
				<select name="SLS_PRSN_NBR" id="SLS_PRSN_NBR" class="chosen-select" <?php echo $style2 ?> <?php echo $stateEnable; ?> >
				<?php
				$querySls	= "SELECT CO_NBR_CMPST FROM NST.PARAM_PAYROLL WHERE CO_NBR = ".$CoNbrDef." ";
				$resultSls	= mysql_query($querySls);
				$rowSls		= mysql_fetch_array($resultSls);
				$CoNbrSls	= $rowSls['CO_NBR_CMPST'];

				if($row["SLS_PRSN_NBR"]=="") { $SlsPrsnNbr=""; } else { $SlsPrsnNbr = $row["SLS_PRSN_NBR"]; }
				$query 	= "SELECT PRSN_NBR,NAME FROM CMP.PEOPLE WHERE CO_NBR IN (". $CoNbrSls .") AND TERM_DTE IS NULL";
				// echo $query;
				genCombo($query,"PRSN_NBR","NAME",$SlsPrsnNbr,"Corporate");
				?>
				</select><br /><div class="combobox"></div>
		</div>	

		<div <?php if (($Acc == 0) && ($locked == 0) && ($_COOKIE["LOCK"] != "LOCK")) { echo "style=''"; } else { echo "style='display:none;'"; } ?> >	
		<div style="float:left;width:90px;margin-top:5px;">
			<label>Rekening</label><br/>
			<select name="ACTG_TYP" id="ACTG_TYP" class="chosen-select" style="width:85px;">
					<option value="">Pilih</option>
					<option value="1" <?php echo ($row['ACTG_TYP'] == '1') ? "selected" : ""; ?>>1</option>
					<option value="2" <?php echo ($row['ACTG_TYP'] == '2') ? "selected" : ""; ?>>2</option>
					<option value="3" <?php echo ($row['ACTG_TYP'] == '3') ? "selected" : ""; ?>>3</option>
			</select><br /><div class="combobox"></div>
		</div>
		</div>
				
		<!-- listing -->
		<div id="edit-list" class="edit-list"></div>
		<script>getContent('edit-list','retail-order-edit-list.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>');</script>
		
		<!-- Footer -->
		<table style="padding:0px;margin-bottom:10px" id="payment">
		<tr>
			<td style='padding:0px;width:380px'>
				<!-- payment -->
				<div class='total'>
					<table>
						<tr class='total'>
							<td style='padding-left:7px;width:150px'>
								Biaya Tambahan
							</td>
							<td style="text-align:right;width:150px">
								<input name="FEE_MISC" id="FEE_MISC" value="<?php echo $row['FEE_MISC']; ?>" onkeyup="calcAmt();" onchange="calcAmt();" type="text" style="margin:1px;width:100px;border:none;text-align:right" <?php echo $footerRead; ?> />
							</td>
							<td style='width:30px'>
							</td>
						</tr>
						<tr class='total'>
							<td style='padding-left:7px'>
								PPN
							</td>
							<td style="text-align:right">
								<input name="TAX_PCT" id="TAX_PCT" value="0" onkeyup="calcTax();" onchange="calcTax();" type="text" style="margin:1px;width:50px;border:none;text-align:right;<?php if ($Acc <= 1) { echo ""; } else { echo "display:none;"; } ?>"/>
								<input name="TAX_AMT" id="TAX_AMT" value="<?php echo $row['TAX_AMT']; ?>" type="text" style="margin:1px;width:90px;border:none;text-align:right" readonly />
							</td>
							<td>
							</td>
						</tr>
						<tr class='total'>
							<td style='font-weight:bold;color:#3464bc;padding-left:7px'>
								Total Nota
							</td>
							<td style="text-align:right">
								<input name="TOT_AMT" id="TOT_AMT" value="<?php echo $row['TOT_AMT']; ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
							</td>
							<td>
							</td>
						</tr>
						
						<!-- Payment AJAX-->
						<td id="pay" colspan="3" style='padding:0px'></td>
						<script>getContent('pay','retail-order-payment-list.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>');</script>
						
						<tr class='total'>
							<td style='font-weight:bold;color:#3464bc;border:0px;padding-left:7px'>
								Sisa
							</td>
							<td style="text-align:right;border:0px">
								<input name="TOT_REM" id="TOT_REM" value="<?php echo $row['TOT_REM']; ?>" type="text" style="width:100px;border:none;text-align:right" readonly />	
							</td>
							<td style="border:0px">
                                <div class='listable-btn' style='margin-left:5px'><span class='fa fa-refresh listable-btn' onclick="calcAmt();" ></span></div>
							</td>
						</tr>
					</table>
				</div>
			</td>
			<td style='padding:0px;vertical-align:bottom;'>
				<div style="float:left;width:280px;">
					<label>No Faktur Pajak</label>
					<input name="TAX_IVC_NBR" id="TAX_IVC_NBR" value="<?php echo $row['TAX_IVC_NBR']; ?>" type="text" style="width:250px;" />
				</div>
				
				<div style="clear:both"></div><br><br>
				
				<div style="float:left;width:140px;<?php if($Acc != 0){echo "display:none;";} ?>">
					<label>Tanggal Selesai </label>
					<?php 
						if($row['CMP_TS']==""){$CmpDte="";}else{$CmpDte=parseDate($row['CMP_TS']);}
					?>
					<input name="CMP_DTE" id="CMP_DTE" value="<?php echo $CmpDte; ?>" type="text" style="width:110px;" readonly />
				</div>

				<div style="float:left;width:140px;<?php if($Acc != 0){echo "display:none;";} ?>">
					<label>Waktu Selesai</label>
					<?php
						if($row['CMP_TS']==""){$CmpTme="";}else{$CmpTme=parseTime($row['CMP_TS']);}
					?>
					<input name="CMP_TME" id="CMP_TME" value="<?php echo $CmpTme; ?>" type="text" style="width:110px;" readonly />
				</div>
			</td>
		</tr>
		</table>

		<div style="float:left;width:145px;">
			<label>Catatan</label><br />
			<textarea name="SPC_NTE" style="width:690px;height:40px;"><?php echo $row['SPC_NTE']; ?></textarea>
		</div>
		<div style="width:100%;clear:both;margin-bottom:10px;"></div>
			
		<input class="process" type="submit" value="Simpan" />		
		</p>		
		</form>	
		
		<?php 
		if ($UpperSec<=0) { 
			$queryCount="SELECT COUNT(ORD_DET_NBR) AS CNT_DEL FROM ". $detailtable ." WHERE ORD_NBR=".$OrdNbr." AND DEL_NBR<>0";
			$resultCount=mysql_query($queryCount);
			$rowCount=mysql_fetch_array($resultCount);
			if ($rowCount['CNT_DEL']>0) {
				$style="";
			} else {
				$style="display:none;";
			}
		?>
		<div style="clear:both;"></div>
		<form style="width:700px;<?php echo $style; ?>">
			<!-- listing -->
			<div id="edit-list-del" class="edit-list"></div>
			<script>getContent('edit-list-del','retail-order-edit-list.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>&SHOW=NO');</script>
		</form>
		<?php } ?>
		
		<script type="text/javascript"  src="framework/database/jquery.min.js"></script>
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
			<?php if ($_GET['IVC_TYP'] != "") {?>			
				var $leftMenu = jQuery(parent.document.getElementById('leftmenu')).contents().find(".sub");
				
				$leftMenu.find("div").removeClass("leftmenusel").addClass("leftmenu");
				$leftMenu.find("#retail-<?php echo strtolower($_GET['IVC_TYP']);?>").removeClass("leftmenu").addClass("leftmenusel");
			<?php }?>
		</script>

		<script type="text/javascript">     
		jQuery(document).ready(function() {
			<?php 
				$query="SELECT TOT_REM
						FROM RTL_ORD_HEAD
						WHERE ORD_NBR=".$OrdNbr;
				$results=mysql_query($query);
				$rows=mysql_fetch_array($results);
				$totRem= $rows['TOT_REM'];
			?>
				var totRem = <?php echo ($totRem > 0) ? $totRem : 0;?>;
				$("#TND_AMT").keyup(function(){
					$("#amount-message").html();
					var maximumamount = 0;
					maximumamount = totRem;
						
					if ($('#TOT_REM').val() < 0) {
						$('#converse').hide();
		                $('#TND_AMT').css("margin-right","4px");
					}else {
						$('#converse').show();
		                $('#TND_AMT').css("margin-right","0px");
					}
				});
		});
		</script>
		
</body>
</html>