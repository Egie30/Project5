<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";

	error_reporting(0);

	$OrdSttId   = $_GET['STT'];
    $OrdSttIdRtl= $_GET['STTRTL'];
    $RightPane  = "signtake.php?STT=".$OrdSttId."&GOTO=TOP";
    $SttTyp     = $_GET['STT_TYP'];

    if($OrdSttIdRtl=="ADD"){
        $OrdNbr         = $_GET['ORD_NBR'];
        $OrdDetNbrStr   = $_GET['ORD_DET_NBR'];
        $query          = "SELECT ORD_NBR,SHP_CO_NBR,ORD_DTE,ACTG_TYP FROM RTL.RTL_STK_HEAD WHERE ORD_NBR=$OrdNbr";
        //echo $query."<br>";
        $result         = mysql_query($query);
        $row            = mysql_fetch_array($result);

        if($row['SHP_CO_NBR']==''){$BuyCoNbr='NULL';}else{$BuyCoNbr=$row['SHP_CO_NBR'];}

        $OrdTtl         = '';
        $DueTs          = $row['ORD_DTE'];
        $ActgTyp        = $row['ACTG_TYP'];

        $query          = 'SELECT COALESCE(MAX(TRNSP_NBR),0)+1 AS NEW_NBR FROM CMP.TRNSP_HEAD';
        $result         = mysql_query($query);
        $row            = mysql_fetch_array($result);
        $TrnspNbr       = $row['NEW_NBR'];

        $query          = "INSERT INTO CMP.TRNSP_HEAD (TRNSP_NBR,ORD_NBR,DUE_TS,ORD_TTL,SHP_CO_NBR,RCV_CO_NBR,TRNSP_STT_ID,ACTG_TYP,TRNSP_DESC,CRT_NBR,UPD_NBR) VALUES (".$TrnspNbr.",".$OrdNbr.",'".$DueTs."','".$OrdTtl."',".$CoNbrDef.",".$BuyCoNbr.",'".$SttTyp."','".$ActgTyp."','',".$_SESSION['personNBR'].",".$_SESSION['personNBR'].")";
        //echo $query."<br>";
        $result         = mysql_query($query);
        $OrdDetNbrs     = explode(',',$OrdDetNbrStr);
        //print_r($OrdDetNbrs);
        foreach($OrdDetNbrs as $OrdDetNbr){
            $query      = "SELECT ORD_DET_NBR,ORD_Q FROM RTL.RTL_STK_DET WHERE ORD_DET_NBR=$OrdDetNbr";
            //echo $query."<br>";
            $result     = mysql_query($query);
            $row        = mysql_fetch_array($result);
            $OrdQ       = $row['ORD_Q'];
            $query      = 'SELECT COALESCE(MAX(TRNSP_DET_NBR),0)+1 AS NEW_NBR FROM CMP.TRNSP_DET';
            $result     = mysql_query($query);
            $row        = mysql_fetch_array($result);
            $TrnspDetNbr= $row['NEW_NBR'];
            $query      = "SELECT SUM(TDET.TRNSP_Q) AS TOT_Q FROM CMP.TRNSP_DET TDET LEFT JOIN CMP.TRNSP_HEAD THED ON TDET.TRNSP_NBR=THEAD.TRNSP_NBR WHERE TDET.DEL_NBR=0 AND TDET.ORD_DET_NBR=$OrdDetNbr AND THED.TRNSP_STT_ID='RP'";
            $resultp    = mysql_query($query);
            $rowp       = mysql_fetch_array($resultp);
            $TotQ       = $rowp['TOT_Q'];
            $RemQ       = $OrdQ-$TotQ;
            $query      = "INSERT INTO CMP.TRNSP_DET (TRNSP_DET_NBR,TRNSP_NBR,TRNSP_Q,ORD_DET_NBR,CRT_NBR,UPD_NBR) VALUES (".$TrnspDetNbr.",".$TrnspNbr.",".$RemQ.",".$OrdDetNbr.",".$_SESSION['personNBR'].",".$_SESSION['personNBR'].")";
            //echo $query."<br>";
            $result     = mysql_query($query);
        }
        $RightPane      = "signtake.php?STT=RP&GOTO=".$TrnspNbr;
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
    <style>
        td.leftpane-adjust{
            width:200px;
        }
        div.leftpane-adjust{
            width:250px;
        }
        div.rightpane-adjust{
            width:100%;
        }
        table.pane-adjust{
            width:100%;
        }
        @media only screen and (min-width: 1105px){
            td.leftpane-adjust{
                width:300px;
            }
            div.leftpane-adjust{
                width:355px;
            }
            div.rightpane-adjust{
                width:100%;
            }
            table.pane-adjust{
                width:100%;
            }
        }
    </style>
</head>

<body>
<table class="pane-adjust" style='width:100%;height:100%'>
	<tr style='height:100%'>
		<td class="leftpane-adjust">
			<!-- Set minimum width -->
			<div class="leftpane-adjust" style="height:100%;overflow-x:hidden;-webkit-overflow-scrolling:touch;">
			<iframe id="leftpane" borderframe=0 src='<?php echo $RightPane; ?>' style="width:100%;overflow:hidden;height:calc(100% - 3px);" onmouseover="this.focus();"></iframe></div>
		</td>
		<td style='padding-left:10px;border-bottom:0px;border-left:#dddddd 1px solid;-webkit-overflow-scrolling:touch;'>
			<!-- Match equal height -->
			<div  class="rightpane-adjust" style="width:100%;overflow-x:hidden;-webkit-overflow-scrolling:touch;"></div>
			<iframe id="rightpane" borderframe=0 style='height:calc(100% - 3px);width:100%;border-right:10px;overflow:hidden'></iframe>
		</td>
	</tr>
</table>
</body>
</html>

<script type="text/javascript" src="framework/functions/default.js"></script>
<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
<link rel="stylesheet" href="framework/combobox/chosen.css">
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
<script type="text/javascript"  src="framework/database/jquery.min.js"></script>
<script type="text/javascript">
    var $leftMenu = $(parent.document.getElementsByTagName('td'));
    // var 
    console.log($(document.getElementsByTagName('td')).find('leftmenusel'));
    console.log("oke");
                
    //$leftMenu.removeClass("leftmenusel");
    $leftMenu.find("#STRTL").click();
</script>