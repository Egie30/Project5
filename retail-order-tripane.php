<?php
include "framework/database/connect.php";
include "framework/functions/default.php";

$OrdSttId	= $_GET['STT'];
$IvcTyp		= $_GET['IVC_TYP'];
$type		= $_GET['TYP'];
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
<table class="pane-adjust" style='height:100%;padding:0px;'>
	<tr style='height:100%'>
		<td class="leftpane-adjust">
			<!-- Set minimum width -->
			<div class="leftpane-adjust" style="height:100%;overflow-x:hidden;-webkit-overflow-scrolling:touch">
			<iframe id="leftpane" borderframe=0 src="retail-order.php?IVC_TYP=<?php echo $IvcTyp;?>&TYP=<?php echo $type; ?>&STT=<?php echo $OrdSttId; ?>&GOTO=TOP" style="width:100%;overflow:hidden;height:calc(100% - 4px);" onmouseover="this.focus();"></iframe></div>
		</td>
		<td style='padding-left:10px;border-bottom:0px;border-left:#dddddd 1px solid;-webkit-overflow-scrolling:touch;'>
			<!-- Match equal height -->
			<div class="rightpane-adjust" style="width:100%;overflow-x:hidden;-webkit-overflow-scrolling:touch;"></div>
			<iframe id="rightpane" borderframe=0 style='height:calc(100% - 3px);width:100%;border-right:10px;overflow:hidden'></iframe>
		</td>
	</tr>
</table>
</body>
</html>