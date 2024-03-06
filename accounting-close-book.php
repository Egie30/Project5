<?php
require_once "framework/database/connect.php";
$bookNumber	= $_GET['BK_NBR'];
$bookBegin	= $_GET['BEG_DTE'];
$bookEnd	= $_GET['END_DTE'];
?>
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
</head>
		
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
			document.getElementById(DivName).innerHTML="<img class='icon' src='img/waitmini.gif'></div>"
		}
	}
</script>

</head>
<body>
<div class="toolbar">
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult"></div>


<script type="text/javascript">
	
	$("#mainResult").load("accounting-close-book-ls.php", function () {
        $("#mainTable").tablesorter({ widgets:["zebra"]});
    });
	
</script>

<script type="text/javascript">
	var url = new URI("accounting-close-book-ls.php");
	
	URI.removeQuery(url, "s");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
</script>
</body>
</html>			
