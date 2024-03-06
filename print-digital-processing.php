<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$security=getSecurity($_SESSION['userID'],"DigitalPrint");
	
	//Process filter
	$OrdSttId=$_GET['STT'];		
	if($OrdSttId=="ALL"){
		$where="WHERE HED.ORD_STT_ID LIKE '%' AND HED.DEL_NBR=0 AND DET.DEL_NBR=0";
	}elseif($OrdSttId=="DUE"){
		$where="WHERE TOT_REM>0 AND CMP_TS IS NOT NULL AND DATE_ADD(CMP_TS,INTERVAL COALESCE(PAY_TERM,0) DAY)<=CURRENT_TIMESTAMP AND HED.DEL_NBR=0 AND DET.DEL_NBR=0";
	}elseif($OrdSttId=="COL"){
		$where="WHERE TOT_REM>0 AND CMP_TS IS NOT NULL AND HED.DEL_NBR=0 AND DET.DEL_NBR=0";
	}else{
		$where="WHERE HED.ORD_STT_ID='".$OrdSttId."' AND HED.DEL_NBR=0 AND DET.DEL_NBR=0";
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

<script type="text/javascript" src="framework/functions/default.js"></script>
<script type="text/javascript" src="framework/database/jquery.min.js"></script>
<script>
	$(function() {
		$(".meter > span").each(function() {
			$(this)
				.data("origWidth", $(this).width())
				.width(0)
				.animate({
					width: $(this).data("origWidth")
				}, 1200);
		});
	});
</script>
	
<style>
	.meter { 
		height: 12px;  /* Can be anything */
		position: relative;
		margin: 0px 0px 1px 0px; /* Just for demo spacing */
		background: #eee;
		-moz-border-radius: 2px;
		-webkit-border-radius: 2px;
		border-radius: 2px;
		//border:1px solid #bbb;
		padding: 0px;
		//-webkit-box-shadow: inset 0 -1px 1px rgba(255,255,255,0.3);
		//-moz-box-shadow   : inset 0 -1px 1px rgba(255,255,255,0.3);
		//box-shadow        : inset 0 -1px 1px rgba(255,255,255,0.3);
	}
	.meter > span {
		display: block;
		height: 100%;
		   -webkit-border-top-right-radius: 2px;
		-webkit-border-bottom-right-radius: 2px;
		       -moz-border-radius-topright: 2px;
		    -moz-border-radius-bottomright: 2px;
		           border-top-right-radius: 2px;
		        border-bottom-right-radius: 2px;
		    -webkit-border-top-left-radius: 2px;
		 -webkit-border-bottom-left-radius: 2px;
		        -moz-border-radius-topleft: 2px;
		     -moz-border-radius-bottomleft: 2px;
		            border-top-left-radius: 2px;
		         border-bottom-left-radius: 2px;
		background-color: #12c44f;
		//background-image: -webkit-gradient(
		//  linear,
		//  left bottom,
		//  left top,
		//  color-stop(0, rgb(43,194,83)),
		//  color-stop(1, rgb(84,240,84))
		// );
		//background-image: -moz-linear-gradient(
		//  center bottom,
		//  rgb(43,194,83) 37%,
		//  rgb(84,240,84) 69%
		// );
		//-webkit-box-shadow: 
		//  inset 0 2px 9px  rgba(255,255,255,0.3),
		//  inset 0 -2px 6px rgba(0,0,0,0.4);
		//-moz-box-shadow: 
		//  inset 0 2px 9px  rgba(255,255,255,0.3),
		//  inset 0 -2px 6px rgba(0,0,0,0.4);
		//box-shadow: 
		//  inset 0 2px 9px  rgba(255,255,255,0.3),
		//  inset 0 -2px 6px rgba(0,0,0,0.4);
		position: relative;
		overflow: hidden;
	}
	.meter > span:after, .animate > span > span {
		content: "";
		position: absolute;
		top: 0; left: 0; bottom: 0; right: 0;
		//background-image: 
		//   -webkit-gradient(linear, 0 0, 100% 100%, 
		//      color-stop(.25, rgba(255, 255, 255, .2)), 
		//      color-stop(.25, transparent), color-stop(.5, transparent), 
		//      color-stop(.5, rgba(255, 255, 255, .2)), 
		//      color-stop(.75, rgba(255, 255, 255, .2)), 
		//      color-stop(.75, transparent), to(transparent)
		//   );
		//background-image: 
		//	-moz-linear-gradient(
		//	  -45deg, 
		//      rgba(255, 255, 255, .2) 25%, 
		//      transparent 25%, 
		//      transparent 50%, 
		//      rgba(255, 255, 255, .2) 50%, 
		//      rgba(255, 255, 255, .2) 75%, 
		//      transparent 75%, 
		//      transparent
		//   );
		z-index: 1;
		-webkit-background-size: 50px 50px;
		-moz-background-size: 50px 50px;
		-webkit-animation: move 2s linear infinite;
		   -webkit-border-top-right-radius: 2px;
		-webkit-border-bottom-right-radius: 2px;
		       -moz-border-radius-topright: 2px;
		    -moz-border-radius-bottomright: 2px;
		           border-top-right-radius: 2px;
		        border-bottom-right-radius: 2px;
		    -webkit-border-top-left-radius: 2px;
		 -webkit-border-bottom-left-radius: 2px;
		        -moz-border-radius-topleft: 2px;
		     -moz-border-radius-bottomleft: 2px;
		            border-top-left-radius: 2px;
		         border-bottom-left-radius: 2px;
		overflow: hidden;
	}
	
	.animate > span:after {
		display: none;
	}
	
	@-webkit-keyframes move {
	    0% {
	       background-position: 0 0;
	    }
	    100% {
	       background-position: 50px 50px;
	    }
	}
	
	.orange > span {
		background-color: #fbad06;
		//background-image: -moz-linear-gradient(top, #f1a165, #f36d0a);
		//background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f1a165),color-stop(1, //#f36d0a));
		//	background-image: -webkit-linear-gradient(#f1a165, #f36d0a); 
	}
		
	.red > span {
		background-color: #d92115;
		//background-image: -moz-linear-gradient(top, #f0a3a3, #f42323);
		//background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f0a3a3),color-stop(1, //#f42323));
		//background-image: -webkit-linear-gradient(#f0a3a3, #f42323);
	}
	
	.nostripes > span > span, .nostripes > span:after {
		-webkit-animation: none;
		background-image: none;
	}
</style>
	
</head>

<body style='width:calc(100% - 5px)'>

<?php if($delete){echo "<script>parent.document.getElementById('leftmenu').contentDocument.location.reload(true);</script>";} ?>

<!-- A little bug with the jquery
<div class="toolbar" style="border-bottom:1px solid #cacbcf;height:22px;">
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>
-->

<div class='tabmenusel' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>";selTabMenu(this);'>Semua</div>
<?php
	//Need enhancement in the future, this is to show what is available to be worked on.  The enhancement includes new table definition
	//$query="SELECT EQP.PRN_DIG_EQP
	//		FROM CMP.PRN_DIG_ORD_HEAD HED
	//		LEFT OUTER JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR
	//		LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
	//		LEFT OUTER JOIN CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
	//		LEFT OUTER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
	//		LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
	//		LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR $where
	//		GROUP BY EQP.PRN_DIG_EQP";
	//echo $query;
	//$result=mysql_query($query);
	//$alt="";
	//while($row=mysql_fetch_array($result)){
	//}
?>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=FLJ320P";selTabMenu(this);'>Solvent</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=RVS640";selTabMenu(this);'>Ecosolvent</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=AJ1800F";selTabMenu(this);'>Direct Fabric</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=MVJ1624,ATX67";selTabMenu(this);'>Heat Transfer</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=CLAM";selTabMenu(this);'>Cold Lamination</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=KMC6501";selTabMenu(this);'>Color A3+</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=KMC6501";selTabMenu(this);'>A3+ FS</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=KMC8000";selTabMenu(this);'>A3+ R2S</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=KMC1085";selTabMenu(this);'>A3+ R2P</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=CIR6000";selTabMenu(this);'>Photocopy</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=HLAM";selTabMenu(this);'>Hot Lamination</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=FXAP";selTabMenu(this);'>Digital Foil</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=EPST13";selTabMenu(this);'>Inkjet</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=LABSVCS";selTabMenu(this);'>Manual</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=HPL375";selTabMenu(this);'>Latex</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=RTRSM103";selTabMenu(this);'>Vacum Press</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=AA160UV";selTabMenu(this);'>UV Roll</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=A3UVDTF";selTabMenu(this);'>DTF UV</div>
<div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>&EQP=SGH6090";selTabMenu(this);'>UV Flatbed</div>
<hr style='height:1px;border:0px;background-color:#eeeeee'>

<iframe id="mainResult" src='print-digital-processing-list.php?STT=<?php echo $OrdSttId; ?>' style='height:calc(100% - 86px)'>

</iframe>

</body>
</html>

