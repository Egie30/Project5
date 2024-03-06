//AJAX Live Populate Field
function livePop(searchStr,fileName,targetObject){
	if (searchStr==""){
		document.getElementById(targetObject).value="";
		return;
	} 
	if (window.XMLHttpRequest){
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}else{
		// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}

	xmlhttp.onreadystatechange=function(){
		if (xmlhttp.readyState==4 && xmlhttp.status==200){
			document.getElementById(targetObject).value=xmlhttp.responseText;
			//Need an empty function if not needed on the main page
			postLive();
		}
	}
	xmlhttp.open("GET",fileName+"?q="+searchStr,true);
	xmlhttp.send();
}
