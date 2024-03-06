// Change frame URL
function changeUrl(frameName,url) {
	iframeElm=document.getElementById(frameName);
	iframeElm.src=url;
}

function changeSiblingUrl(frameName,url) {
	iframeElm=parent.document.getElementById(frameName);
	iframeElm.src=url;
}

// Top menu selector
function selTopMenu(img){
	var container=img.parentNode;
	var imgs=container.getElementsByTagName('img');
	for(var count=0;count<imgs.length;count++){
		var curImg=imgs[count];
		if(curImg.className=='topmenusel'){
			curImg.className='topmenu';
		}
	}
	if(img.tagName=='IMG'){
        img.className='topmenusel';
    }
	var spans=container.getElementsByTagName('span');
	for(var count=0;count<spans.length;count++){
		var curSpan=spans[count];
		if(curSpan.className=='fa-stack fa-1x topmenusel'){
			curSpan.className='fa-stack fa-1x topmenu';
		}
	}
	if(img.tagName=='SPAN'){
        img.className='fa-stack fa-1x topmenusel';
    }
}

// Left menu mobile selector
function selLeftMenuMobile(img){
	var container=img.parentNode;
	var imgs=container.getElementsByTagName('img');
	for(var count=0;count<imgs.length;count++){
		var curImg=imgs[count];
		curImg.className='leftmenu';
	}
	img.className='leftmenusel';
}

// Left menu mobile selector
function selLeftMenuMobileStick(img){
	img.className='leftmenusel';
}

// Left menu selector
function selLeftMenu(div){
	var container=div.parentNode;
	var divs=container.getElementsByTagName('div');
	for(var count=0;count<divs.length;count++){
		var curDiv=divs[count];
		curDiv.className='leftmenu';
	}
	div.className='leftmenusel';
    parent.document.getElementById('menu').style.display='none';
    if(parent.document.getElementById('leftmenu-items').style.left=='0px'){
        if(window.parent.document.body.clientWidth<1280){
            parent.document.getElementById('leftmenu-items').style.left='-200px';
        }
    }
}

// Tab menu selector
function selTabMenu(div){
	var container=div.parentNode;
	var divs=container.getElementsByTagName('div');
	for(var count=0;count<divs.length;count++){
		var curDiv=divs[count];
		curDiv.className='tabmenu';
	}
	div.className='tabmenusel';
}


// Multiple asynchronous javascript and XML call
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
		}
	}else if(http_request.readyState==1){
		document.getElementById(DivName).innerHTML="<div align='center' style='padding:5px'><div class='spinner'><div class='double-bounce1'></div><div class='double-bounce2'></div></div></div>";
	}
}

//Mini Synchronous call
function syncGetContent(DivName,url){
    document.getElementById(DivName).innerHTML="<div align='center' style='padding:5px'><div class='spinner'><div class='double-bounce1'></div><div class='double-bounce2'></div></div></div>"
    var xmlhttp = null;
    var doc = null;
    if (document.all)
       xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
    else
       xmlhttp = new XMLHttpRequest();
    if (xmlhttp)
    {
       xmlhttp.open('GET',url,false);
       xmlhttp.send(null);
       document.getElementById(DivName).innerHTML=xmlhttp.responseText;
    }
}

//Synchronous call
function syncGetContentMini(DivName,url){
    document.getElementById(DivName).innerHTML="<div align='center' style='padding:5px'><div class='spinnerMini'><div class='double-bounce1'></div><div class='double-bounce2'></div></div></div>"
    var xmlhttp = null;
    var doc = null;
    if (document.all)
       xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
    else
       xmlhttp = new XMLHttpRequest();
    if (xmlhttp)
    {
       xmlhttp.open('GET',url,false);
       xmlhttp.send(null);
       document.getElementById(DivName).innerHTML=xmlhttp.responseText;
    }
}

// Timestamp related functions
function getCurDate(){
	var currentDate = new Date();
	var years=currentDate.getFullYear();
	var months=currentDate.getMonth()+1;
	if(months<10){
		months="0"+months;
	}
	var dates=currentDate.getDate();
	if(dates<10){
		dates="0"+dates;
	}

	return years+'-'+months+'-'+dates;
}

function getCurTime(){
	var currentTime = new Date();
	var hours=currentTime.getHours();
	if(hours<10){
		hours="0"+hours;
	}
	var minutes=currentTime.getMinutes();
	if(minutes<10){
		minutes="0"+minutes;
	}
	var seconds=currentTime.getSeconds();
	if(seconds<10){
		seconds="0"+seconds;
	}
	return hours+':'+minutes+':'+seconds;
}

//Get parameter stored in data/param.xml
function getParam(paramName,paramNode){
	xmlhttp=new XMLHttpRequest();
	xmlhttp.open("GET","data/param.xml",false);
	xmlhttp.send();
	xmlDoc=xmlhttp.responseXML;
	var data=xmlDoc.getElementsByTagName(paramName);
	return data[0].getElementsByTagName(paramNode)[0].childNodes[0].nodeValue;
}

//Luhn generator and validator
 function calcLuhn(Luhn)
 {
    var sum=0;
    for(i=0;i<Luhn.length;i++)
    {
		sum+=parseInt(Luhn.substring(i,i+1));
    }
	var delta=new Array (0,1,2,3,4,-4,-3,-2,-1,0);
	for(i=Luhn.length-1;i>=0;i-=2)
    {		
		var deltaIndex=parseInt(Luhn.substring(i,i+1));
		var deltaValue=delta[deltaIndex];	
		sum += deltaValue;
	}	
	var mod10=sum%10;
	mod10=10-mod10;	
	if(mod10==10)
	{		
		mod10=0;
	}
	return mod10;
 }

 function validLuhn(Luhn)
 {
	var LuhnDigit=parseInt(Luhn.substring(Luhn.length-1,Luhn.length));
	var LuhnLess=Luhn.substring(0,Luhn.length-1);
	if(Calculate(LuhnLess)==parseInt(LuhnDigit))
	{
		return true;
	}	
	return false;
 }
 
// Tripane left pane selector
function selLeftPane(div){
	var container=div.parentNode;
	var divs=container.getElementsByTagName('div');
	for(var count=0;count<divs.length;count++){
		var curDiv=divs[count];
		if(curDiv.id.substr(0,1)=="O"){
			//curDiv.style.border='none';
            curDiv.style.backgroundColor='#ffffff';
			//curDiv.style.padding='6px 5px 4px 5px';
		}
	}
	//div.style.border='1px solid #959faf';
    div.style.backgroundColor='#eef8fb';
	//div.style.padding='6px 5px 4px 5px';
}
function deSelLeftPane(){
	var container=document.getElementById('mainResult');
	var divs=container.getElementsByTagName('div');
	for(var count=0;count<divs.length;count++){
		var curDiv=divs[count];
		if(curDiv.id.substr(0,1)=="O"){
			//curDiv.style.border='none';
            curDiv.style.backgroundColor='#ffffff';
			//curDiv.style.padding='6px 5px 4px 5px';
		}
	}
	var container=document.getElementById('liveRequestResults');
	var divs=container.getElementsByTagName('div');
	for(var count=0;count<divs.length;count++){
		var curDiv=divs[count];
		if(curDiv.id.substr(0,1)=="O"){
			//curDiv.style.border='none';
            curDiv.style.backgroundColor='#ffffff';
			//curDiv.style.padding='6px 5px 4px 5px';
		}
	}
}

//Fade without eval
function fadeOut(element) {
    var op=1;  // initial opacity
    var timer=setInterval(function(){
        if(op<=0.1){
            clearInterval(timer);
            //element.style.display='none';
        }
        element.style.opacity=op;
        element.style.filter='alpha(opacity='+op*100+")";
        op-=op* 0.1;
    },50);
}

function fadeIn(element) {
    var op=0;  // initial opacity
    var timer=setInterval(function(){
        if(op>=1){
            clearInterval(timer);
        }
        element.style.opacity=op;
        element.style.filter='alpha(opacity='+op*100+")";
        op+=0.1;
    },50);
}

function slideFormIn(form){
    parent.parent.document.getElementById('fade').style.opacity='0';
    parent.parent.document.getElementById('siteFormContent').src=form;
    parent.parent.document.getElementById('site-canvas').style.left='-450px';
    parent.parent.document.getElementById('fade').style.display='block';
    
    setTimeout(
        function(){
            parent.parent.document.getElementById('fade').style.opacity='0.5';
            parent.parent.document.getElementById('fade').style.webkitTransition='opacity 0.3s';
            parent.parent.document.getElementById('fade').style.mozTransition='opacity 0.3s';
            parent.parent.document.getElementById('fade').style.transition='opacity 0.3s'
        },1
    );
}

function slideFormOut(){
    parent.document.getElementById('site-canvas').style.left='0px';
    parent.document.getElementById('fade').style.opacity='0';
    setTimeout(
        function(){
            parent.document.getElementById('fade').style.display='none';
            parent.document.getElementById('fade').style.opacity='0.5';
        },300
    );
}

function pushFormIn(form){
    parent.document.getElementById('fade').style.opacity='0';
    parent.document.getElementById('siteFormContent').src=form;
    parent.document.getElementById('site-form').style.left='calc(100% - 450px)';
    parent.document.getElementById('fade').style.display='block';
    
    setTimeout(
        function(){
            parent.document.getElementById('fade').style.webkitTransition='opacity 0.3s';
            parent.document.getElementById('fade').style.mozTransition='opacity 0.3s';
            parent.document.getElementById('fade').style.transition='opacity 0.3s'
            parent.document.getElementById('fade').style.opacity='0.5';
        },100
    );
}

function pushFormOut(){
    parent.document.getElementById('site-form').style.left='100%';
    parent.document.getElementById('fade').style.opacity='0';
    setTimeout(
        function(){
            parent.document.getElementById('fade').style.display='none';
            parent.document.getElementById('fade').style.opacity='0.5';
        },300
    );
}

function toggleCheckBox(source){
    var c=document.getElementsByTagName('input');
    for (var i=0;i<c.length;i++){
        if (c[i].type=='checkbox') {
            c[i].checked=source.checked;
        }
    }
}

function number_format(number,decimals,dec_point,thousands_sep){
// Strip all characters but numerical ones.
  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
toFixedFix = function (n, prec) {
var k = Math.pow(10, prec);
return '' + Math.round(n * k) / k;
    };
// Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
return s.join(dec);
}

function parseNumber(number) {
    // The unary "+" operator is the fastest method for type-converting a string into a number.
    //number = (+number);
                
    number = Number(number).valueOf();
                
    // Indicates whether the result is not valid number
    // e.g. Infinity or NaN
    if (isNaN(number) || number === number/0 || number === "") {
        number = 0;
    }
                
    return number;
}