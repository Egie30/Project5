<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";

	$OrdSttId	= $_GET['STT'];
    $RightPane	= "retail-transport.php?STT=".$OrdSttId."&GOTO=TOP";
	$SttTyp     = $_GET['STT_TYP'];

	if($OrdSttId=="ADD"){
        $OrdNbr         = $_GET['ORD_NBR'];
        $OrdDetNbrStr   = $_GET['ORD_DET_NBR'];
        $query          = "SELECT ORD_NBR,RCV_CO_NBR,ORD_TTL,ACTG_TYP FROM RTL.RTL_ORD_HEAD WHERE ORD_NBR=$OrdNbr";
        //echo $query."<br>";
        $result         = mysql_query($query);
        $row            = mysql_fetch_array($result);

        if($row['RCV_CO_NBR']==''){$BuyCoNbr='NULL';}else{$BuyCoNbr=$row['RCV_CO_NBR'];}

        $OrdTtl         = $row['ORD_TTL'];
        //$DueTs          = $row['DUE_TS'];
        $ActgTyp        = $row['ACTG_TYP'];

        $query          = 'SELECT COALESCE(MAX(TRNSP_NBR),0)+1 AS NEW_NBR FROM RTL.TRNSP_HEAD';
		$result         = mysql_query($query);
		$row            = mysql_fetch_array($result);
		$TrnspNbr       = $row['NEW_NBR'];

		$query          = "INSERT INTO RTL.TRNSP_HEAD (TRNSP_NBR,ORD_NBR,DUE_TS,ORD_TTL,SHP_CO_NBR,RCV_CO_NBR,TRNSP_STT_ID,ACTG_TYP,TRNSP_DESC,CRT_NBR,UPD_NBR) VALUES (".$TrnspNbr.",".$OrdNbr.",CURRENT_TIMESTAMP,'".$OrdTtl."',".$CoNbrDef.",".$BuyCoNbr.",'".$SttTyp."','".$ActgTyp."','',".$_SESSION['personNBR'].",".$_SESSION['personNBR'].")";
        //echo $query."<br>";
		$result         = mysql_query($query);
        $OrdDetNbrs     = explode(',',$OrdDetNbrStr);

        foreach($OrdDetNbrs as $OrdDetNbr){
            $query      = "SELECT ORD_DET_NBR,ORD_Q FROM RTL.RTL_ORD_DET WHERE ORD_DET_NBR=$OrdDetNbr";
            $result     = mysql_query($query);
            $row        = mysql_fetch_array($result);
            $OrdQ       = $row['ORD_Q'];
            $query      = 'SELECT COALESCE(MAX(TRNSP_DET_NBR),0)+1 AS NEW_NBR FROM RTL.TRNSP_DET';
            $result     = mysql_query($query);
            $row        = mysql_fetch_array($result);
            $TrnspDetNbr= $row['NEW_NBR'];
            $query      = "SELECT SUM(TRNSP_Q) AS TOT_Q FROM RTL.TRNSP_DET WHERE DEL_NBR=0 AND ORD_DET_NBR=$OrdDetNbr";
            $resultp    = mysql_query($query);
            $rowp       = mysql_fetch_array($resultp);
            $TotQ       = $rowp['TOT_Q'];
            $RemQ       = $OrdQ-$TotQ;
            $query      = "INSERT INTO RTL.TRNSP_DET (TRNSP_DET_NBR,TRNSP_NBR,TRNSP_Q,ORD_DET_NBR,CRT_NBR,UPD_NBR) VALUES (".$TrnspDetNbr.",".$TrnspNbr.",".$RemQ.",".$OrdDetNbr.",".$_SESSION['personNBR'].",".$_SESSION['personNBR'].")";
            //echo $query."<br>";
            $result     = mysql_query($query);
        }
        //Process new shipment request here
        if($SttTyp=="IN"){
            $RightPane      = "retail-transport.php?STT=IN&GOTO=".$TrnspNbr;
        } else if($SttTyp=="ST"){
            $RightPane      = "retail-transport.php?STT=ST&GOTO=".$TrnspNbr;
        }
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src="framework/functions/default.js"></script>
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
<script src="framework/database/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
	<?php if ($_GET['STT'] = "") {?>			
		var $leftMenu = jQuery(parent.parent.document.getElementById('leftmenu')).contents().find(".sub");
				
		$leftMenu.find("div").removeClass("leftmenusel").addClass("leftmenu");
		$leftMenu.find("#retail-shipping").removeClass("leftmenu").addClass("leftmenusel");
	<?php }?>
</script>
</body>
</html>