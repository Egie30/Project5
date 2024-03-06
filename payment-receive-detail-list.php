<?php
include "framework/database/connect.php";
include "framework/security/default.php";

$UpperSec		= getSecurity($_SESSION['userID'],"Stationery");
$shippingNbr	= $_GET['SHP_CO_NBR'];
$pymtReceiveNbr	= $_GET['PYMT_RCV_NBR'];

$queryH="SELECT 
	TND_AMT
FROM RTL.PYMT_RCV 
WHERE PYMT_RCV_NBR=".$pymtReceiveNbr;
//echo $query;
$resultH=mysql_query($queryH);
$rowH=mysql_fetch_array($resultH);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tab/tabs.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/accounting.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	
	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/tab/tabs.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesort/tablesort.js"></script>
	<script type="text/javascript" src="framework/tablesort/customsort.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	<style>
		.tombol{
			font-size: 10pt;
			text-align: center;
			vertical-align: top;
			width: 70px;
			height: 34px;
			color: #fff;
			text-decoration: none;
			background-color: #3464bc;
			border-radius: 4px;
			-moz-border-radius: 4px;
			-webkit-border-radius: 4px;
			cursor: pointer;
			-webkit-appearance: none;
		}
	</style>
	<script>
	function checkConvert(){
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
		//alert(queryStr);
        if(queryStr==''){
            window.scrollTo(0,0);parent.parent.document.getElementById('convertBlank').style.display='block';
            parent.parent.document.getElementById('fade').style.display='block';
        }else{
			parent.document.getElementById('printDigitalPopupEditContent').src='payment-receive.php?TYPE=INVOICE&ORD_NBR='+queryStr.substr(0,queryStr.length-1);
			parent.document.getElementById('printDigitalPopupEdit').style.display='block';
			parent.document.getElementById('fade').style.display='block';
        }
    }
	</script>
</head>
<body>

<div class="toolbar">
	<p class="toolbar-left">
		<span style="font-size: 14pt;font-weight: 300;line-height: 80%;color: #3464bc;">
		<br>
		Pembayaran: Rp. <?php echo number_format($rowH['TND_AMT'],0,'.',',');?> 
		</span>
	</p>
	<p class="toolbar-right">
		<a href="javascript:void(0)" onClick="checkConvert()">
			<span class='fa fa-copy toolbar' style='cursor:pointer'></span>
		</a>
		
		<!--<input class='tombol' type='button' value='Bayar'/>-->
	</p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable" border=0>
		<thead>
			<tr>
				<th class="sortable">No.</th>
				<th class="sortable">Tanggal</th>
				<th class="sortable">Deskripsi</th>				
				<th class="sortable">Jumlah</th>
				<th class="sortable">Balance</th>
				<th class="nosort" align="center">
					<input name='SEL_IMG' id='SEL_IMG' type='checkbox' class='regular-checkbox' onclick="toggleCheckBox(this)"/>
					<label for='SEL_IMG' style='margin-top:5px;margin-right:0px'></label>
				</th>
			</tr>
		</thead>
		<tbody>
	<?php 
	$alt;
	$query="SELECT 
		HED.ORD_NBR,
		DATE(ORD_TS) AS DTE,
		CONCAT(COALESCE(HED.ORD_TTL,''),' ',COALESCE(COM.NAME,''),' ',COALESCE(PPL.NAME,'')) AS IVC_DESC,
		SUM(TOT_AMT)-COALESCE(SUM(PAY.TND_AMT),0) AS AMT
	FROM CMP.PRN_DIG_ORD_HEAD HED 
		INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
		LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
		LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
		LEFT JOIN (
			SELECT 
				PYMT.ORD_NBR,
				COALESCE(SUM(PYMT.TND_AMT),0) AS TND_AMT
			FROM CMP.PRN_DIG_ORD_PYMT PYMT
			WHERE PYMT.DEL_NBR = 0
			GROUP BY PYMT.ORD_NBR
		) PAY ON PAY.ORD_NBR = HED.ORD_NBR
		WHERE TOT_REM > 0 AND HED.DEL_NBR=0 AND HED.BUY_CO_NBR = ". $shippingNbr ."
	GROUP BY HED.ORD_NBR";
	//echo "<pre>".$query;
	$result = mysql_query($query);
	while ($row = mysql_fetch_array($result)) {
	?>
	<tr <?php echo $alt; ?> style ="cursor: pointer;" onclick="location.href='payment-receive-edit.php?PYMT_RCV_NBR=<?php echo $row['PYMT_RCV_NBR'];?>';">
		<td style="text-align:center;"><?php echo $row['ORD_NBR'];?></td>
		<td style="text-align:center;"><?php echo $row['DTE'];?></td>
		<td><?php echo $row['IVC_DESC'];?></td>
		<td style="text-align:right;"><?php echo number_format($row['AMT'],0,'.',',');?></td>
		<td style="text-align:right;"><?php echo number_format($balance,0,'.',',');?></td>
		<td style="text-align:center;">
			<input name='SEL_IMG_<?php echo $row['ORD_NBR'];?>' id='SEL_IMG_<?php echo $row['ORD_NBR'];?>' type='checkbox' class='regular-checkbox' onclick='event.cancelBubble=true;'/>
			<label for='SEL_IMG_<?php echo $row['ORD_NBR'];?>' style='margin-right:0px;margin-top:5px' onclick='event.cancelBubble=true;'></label>
		</td>
	</tr>
	<?php 
		if($alt==""){$alt="class='alt'";}else{$alt="";}
	}
	?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">
			<td class="std" style="text-align:left;font-weight:bold;" colspan="4">Balance:</td>
			<td class="std" style="text-align:right;font-weight:bold;" colspan="2"><?php echo number_format($results->total->TOT_REM, 0, ',', '.');?></td>
		</tr>
	</tfoot>
	</table>
</div>

<script>liveReqInit('livesearch','liveRequestResults','retail-type-ls.php','','mainResult');</script>
<script>fdTableSort.init();</script>
</body>
</html>