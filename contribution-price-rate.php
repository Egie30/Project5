<?php
require_once "framework/database/connect.php";
include "framework/functions/default.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	
	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	
<script>
	function getContent(DivName,url) {
		var http_request=false;
		if(window.XMLHttpRequest){ // Mozilla, Safari, ...
			http_request=new XMLHttpRequest();
			if(http_request.overrideMimeType){
				http_request.overrideMimeType('text/xml');
				//See note below about this line
			}
		}else if(window.ActiveXObject){ // IE
			try{
				http_request=new ActiveXObject("Msxml2.XMLHTTP");
				}catch(e){
				try{
					http_request=new ActiveXObject("Microsoft.XMLHTTP");
				}catch(e){}
			}
		}
		if(!http_request){
			alert('Cannot create an XMLHTTP instance');
			document.getElementById(DivName).innerHTML='';
			return false;
		}
		http_request.onreadystatechange=function(){alertContents(http_request,DivName);};
		http_request.open('GET',url,true);
		http_request.send(null);
	}
	function alertContents(http_request,DivName){
		if(http_request.readyState==4){
			if(http_request.status==200){
				document.getElementById(DivName).innerHTML=http_request.responseText;
			}else{
				alert('There was a problem with the request.');
			}console.log(http_request);
		}else if(http_request.readyState==1){
			document.getElementById(DivName).innerHTML="Please Wait...";
		}
	}
</script>
</head>
<body>
	<h2>Kunci NAB Reksa Dana</h2>	
	<table>
	<tr>
		<td>
			<div>
			<div style='border:none;background:none;color:gray' id='posting-reksadana'></div>
			<div style='border:1px #cccccc solid;padding:5px;width:100px;margin-top:5px;margin-bottom:5px;cursor:pointer;border-radius:3px'
			onclick="getContent('posting-reksadana','framework/stock-price/reksadana-bca.php?personNBR=<?php echo $_SESSION['personNBR']?>');selLeftMenu(this);">Process</div><br />
			</div>
		</td>
	</tr>
	</table>
	
	<table>
		<?php
		$query = "SELECT CNBTN_PRC_DTE, PRC, UPD_TS FROM PAY.CNBTN_PRC ORDER BY CNBTN_PRC_NBR DESC LIMIT 1";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		?>
		<tr>
			<td colspan="2">Update Terakhir :</td>
		</tr>
		<tr>
			<td>Tanggal NAB</td>
			<td>: <?php echo parseDateShort($row['CNBTN_PRC_DTE']); ?></td>
		</tr>
		<tr>
			<td>NAB</td>
			<td>: Rp. <?php echo $row['PRC']; ?></td>
		</tr>
		<tr>
			<td>Tanggal Update</td>
			<td>: <?php echo parseDateShort($row['UPD_TS'])." ".parseHour($row['UPD_TS']).":".parseMinute($row['UPD_TS']); ?></td>
		</tr>
	</table>
	
</body>
</html>


