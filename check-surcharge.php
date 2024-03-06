<?php
require_once "framework/database/connect-cashier.php";
require_once "framework/security/default.php";

$query = "SELECT CRT_CRD_SUR FROM NST.PARAM_GLBL";
$result = mysql_query($query, $rtl);
$row = mysql_fetch_array($result);
$creditCardSur = $row['CRT_CRD_SUR'];

$query  = "SELECT SUM(CASE WHEN RTL_Q > 0 THEN RTL_Q ELSE 0 END) AS RTL_Q,
	SUM(CASE WHEN RTL_Q < 0 THEN RTL_Q ELSE 0 END) AS RTR_Q,
	SUM(COALESCE(CSH_FLO_MULT, 1)*TND_AMT) AS TND_AMT,
	SUM(CASE WHEN REG.CSH_FLO_TYP='RT' THEN COALESCE(DISC_AMT, 0) ELSE 0 END) AS DISC_AMT,
	SUM(CASE WHEN REG.CSH_FLO_TYP='RT' THEN (COALESCE(DISC_PCT, 0)/100)*(COALESCE(CSH_FLO_MULT, 1)*TND_AMT) ELSE 0 END) AS DISC_PCT
FROM RTL.CSH_REG REG
	LEFT OUTER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
WHERE ((REG.CSH_FLO_TYP IN ('RT') AND RTL_BRC <> '') OR REG.CSH_FLO_TYP IN ('DP', 'FL', 'ED', 'GP', 'IV')) AND REG.TRSC_NBR='" . $_GET['TRSC_NBR'] . "'
ORDER BY REG.CRT_TS DESC";
$result = mysql_query($query, $rtl);
$row    = mysql_fetch_array($result);
$totalBruto = $row['TND_AMT'] - ($row['DISC_AMT'] + $row['DISC_PCT']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<script type="text/javascript">parent.Pace.restart();</script>
	<style type="text/css">
		.btnprocess {
			font-family: 'San Francisco Display', 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, sans-serif;
			font-size:10pt;
			text-align:center;
			vertical-align:top;
			width:100px;
			height:34px;
			padding: 10px 15px;
			color: #fff;
			text-decoration: none;
			background-color: #989898;
			border:none;
			border-radius:4px;
			-moz-border-radius:4px;
			-webkit-border-radius:4px;
			cursor:pointer;
			-webkit-appearance:none;
	</style>
	<script type="text/javascript">
	function getInt(objectID){
		if(document.getElementById(objectID).value==""){
			return 0;
		}else{
			return parseInt(document.getElementById(objectID).value);
		}
	}
	function getFloat(objectID){
		if(document.getElementById(objectID).value==""){
			return 0;
		}else{
			return parseFloat(document.getElementById(objectID).value);
		}
	}
	function calcPay(){
		document.getElementById('VALUE_CHG').value=Math.round(document.getElementById('VALUE_SUR').value* (getInt('TOT_AMT') / 100));
	}
	</script>
</head>
<body onLoad="document.getElementById('VALUE_SUR').focus();">

<div style="height:100%; overflow:auto;">
	<span class='fa fa-times' style="cursor:pointer" onclick="document.getElementById('popupLogin').style.display = 'none';document.getElementById('fadeCashier').style.display = 'none';"/></span>

	<form enctype="multipart/form-data" action="cashier-listing.php?POS_ID=<?php echo $POSID ?>&TRSC_NBR=<?php echo $_GET['TRSC_NBR'] ?>&ACTION=<?php echo $_GET['ACTION']; ?>&VALUE=<?php echo $_GET['VALUE']; ?>" method="GET" style="width: 100%; box-sizing: border-box;" autofocus>
		<table>
			<input type="hidden" name="POS_ID" value="<?php echo $POSID; ?>"/>
			<input type="hidden" name="TRSC_NBR" value="<?php echo $_GET['TRSC_NBR']; ?>"/>
			<input type="hidden" name="ACTION" value="<?php echo $_GET['ACTION']; ?>"/>
			<input type="hidden" name="TOT_AMT" id="TOT_AMT" value="<?php echo $totalBruto; ?>"/>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td style="text-align:center;">
					<span class="btnprocess" onclick="location.href='cashier-listing.php?POS_ID=<?php echo $POSID ?>&TRSC_NBR=<?php echo $_GET['TRSC_NBR'] ?>&ACTION=<?php echo $_GET['ACTION']; ?>&VALUE=0.2';" id="VALUE_SUR1">0.2 %</span>
					<span class="btnprocess" onclick="location.href='cashier-listing.php?POS_ID=<?php echo $POSID ?>&TRSC_NBR=<?php echo $_GET['TRSC_NBR'] ?>&ACTION=<?php echo $_GET['ACTION']; ?>&VALUE=0.8';" id="VALUE_SUR1">0.8 %</span>
					<span class="btnprocess" onclick="location.href='cashier-listing.php?POS_ID=<?php echo $POSID ?>&TRSC_NBR=<?php echo $_GET['TRSC_NBR'] ?>&ACTION=<?php echo $_GET['ACTION']; ?>&VALUE=<?php echo $creditCardSur; ?>';" id="VALUE_SUR1"><?php echo $creditCardSur; ?> %</span>
					&nbsp;&nbsp;
					<input id="VALUE_SUR" name="VALUE" type="text" style="width:100px;height:30px;margin-top:-5px;" onkeyup="calcPay();" onchange="calcPay();" autofocus /> %
				</td>
			</tr>
			<tr>
				<td style="text-align:center;">
					<span>Card Fee</span><br/>
					<input id="VALUE_CHG" name="VALUE_CHG" type="text" style="width:300px;" autofocus />
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td style="text-align:center;">
					<input type="submit" class="process" style="cursor:pointer;" value="Tambah" />
				</td>
			</tr>
		</table>
	</form>
</div>
</body>
</html>