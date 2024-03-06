<?php
	include "../framework/database/connect.php";
	include "../framework/functions/default.php";
	include "../framework/security/default.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" class='iframe'>

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script type="text/javascript" src="../framework/functions/default.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

<script type="text/javascript"> 
	var prsnNbr=0;
	var scanType="E";
	var prodNbr="";
	document.onkeyup=KeyCheck;       
	function KeyCheck(e)
	{
  		var KeyID=(window.event)?event.keyCode:e.keyCode;
  		if(KeyID==13){
  			if(scanType=='E'){
				document.getElementById('data').style.display='';
				syncGetContent('data','stage-name.php?PRSN_NBR='+document.getElementById('scan').value);
				eval(document.getElementById("runScriptName").innerHTML);
				if(scanType=='O'){
					prsnNbr=document.getElementById('scan').value;
				}
				document.getElementById('scan').value='';
			}else if(scanType=='O'){
				if(document.getElementById('scan').value.substr(0,1)=="P"){
					syncGetContent('header','stage-product.php?PROD_NBR='+document.getElementById('scan').value);
					eval(document.getElementById("runScriptOrder").innerHTML);
					if(scanType=='P'){
						document.getElementById('detail').style.display='';
						syncGetContent('detail','stage-detail.php?PROD_NBR='+document.getElementById('scan').value);
					}
					prodNbr=document.getElementById('scan').value;
					document.getElementById('scan').value='';
					scanType=='O';
				}else if(document.getElementById('scan').value.substr(0,1)=="S"){
					if(prodNbr!=''){
						syncGetContent('detail','stage-detail.php?SORT_BAY='+document.getElementById('scan').value+'&PROD_NBR='+prodNbr+'&PRSN_NBR='+prsnNbr);
						if(document.getElementById(document.getElementById('scan').value)){
							document.getElementById('scan-result').src="img/scan-valid.png";
							//document.getElementById(document.getElementById('scan').value).style.backgroundColor='#f3f2bc';
							//if(prodList!=''){prodList+=',';}	
							//prodList+=document.getElementById('scan').value;
						}else{
							document.getElementById('scan-result').src="img/scan-failed.gif";
						}
					}else{
						document.getElementById('scan-result').src="img/scan-failed.gif";
					}
					document.getElementById('scan').value='';
				}
				scanType='O';
			}
	  	}else{
			document.getElementById('scan').value+=String.fromCharCode(KeyID);
		}
	}
</script>

</head>

<body class='iframe'>
<input id='listener' style='position:absolute;top:-50px'>
<div class='title'>
	Product Staging<input name="scan"id="scan" style='width:0px;height:0px;padding:0px;border-color:#474747' readonly>
</div>
<div id='data' class='data'>
	<div class='scan'>
		<img src='img/generic.jpg' style='border-radius:50% 50% 50% 50%;width:50px;height:50px;vertical-align:middle'>&nbsp;&nbsp;Scan an employee ID
	</div>
</div>
			
</body>
</html>
