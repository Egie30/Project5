/*
// +----------------------------------------------------------------------+
// | Orginial Code Care Of:                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 Bitflux GmbH                                      |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <devel@bitflux.ch>                              |
// |         http://blog.bitflux.ch/p1735.html                            |
// +----------------------------------------------------------------------+
//
//
// +----------------------------------------------------------------------+
// | Heavily Modified by Jeff Minard (07/09/04)                           |
// +----------------------------------------------------------------------+
// | Same stuff as above, yo!                                             |
// +----------------------------------------------------------------------+
// | Author: Jeff Minard <jeff-js@creatimation.net>                       |
// |         http://www.creatimation.net                                  |
// +----------------------------------------------------------------------+
//
//
// +----------------------------------------------------------------------+
// | What is this nonsense?? (07/09/04)                                   |
// +----------------------------------------------------------------------+
// | This is a script that, by using XMLHttpRequest javascript objects    |
// | you can quickly add some very click live interactive feed back to    |
// | your pages that reuire server side interaction.                      |
// |                                                                      |
// | For instance, you use this to emulate a "live searching" feature     |
// | wherein users type in key phrases, and once they have stopped typing |
// | the script will automatically search and retrive *without* a page    |
// | reload.
// |                                                                      |
// | In another instance, I use this to product live comments by passing  |
// | the text to a Textile class that parses it to valid HTML. After      |
// | parsing, the html is returned and displayed on the page as the       |
// | user types.                                                          | 
// +----------------------------------------------------------------------+
*/

/*--------------------------------------
	User configured variables
--------------------------------------*/
var inputId = ''; 
					// This is the id on the input/textarea that you want to use as the query.
									
var outputId = '';
 					// use this to have the results populate your own ID'd tag.
					// leave it blank and a div tag will automatically be added
					// with an id="liveSearchResults"
									
var processURI    = '';
					// this is the file that you request data from.
									
var emptyString   = '';
					// What to display in the results field when there's nothing
					// Leaving this null will cause the results field to be set to display: none

/*--------------------------------------
	Script Stuff
--------------------------------------*/
var liveReq = false;
var t = null;
var liveReqLast = "";
var isIE = false;

var inputElement;
var outputElement;
var mainElement;

// on !IE we only have to initialize it once
if (window.XMLHttpRequest) {
	liveReq = new XMLHttpRequest();
}

function liveReqInit(inputId_,outputId_,processURI_,emptyString_,mainId_) {

	inputId=inputId_;
	outputId=outputId_;
	processURI=processURI_;
	emptyString=emptyString_;
	mainId=mainId_;
	
	inputElement  = document.getElementById(inputId);
	outputElement = document.getElementById(outputId);
	mainElement = document.getElementById(mainId);
	
	if( inputElement == null || outputElement == null ) 
		return;
	
	
	if (navigator.userAgent.indexOf("Safari") > 0) {
		inputElement.addEventListener("keydown",liveReqStart,false);
		
	} else if (navigator.product == "Gecko") {
		inputElement.addEventListener("keypress",liveReqStart,false);
		
	} else {
		inputElement.attachEvent('onkeydown',liveReqStart);
		isIE = true;
	}
	
	if(emptyString == '') {
		// set the result field to hidden, or to default string
		outputElement.style.display = "none";
		mainElement.style.display = "";
	} else {
		outputElement.innerHTML = emptyString;
		mainElement.style.display = "none";
	}
}

//addLoadEvent(liveReqInit);

function liveReqStart() {
	if (t) {
		window.clearTimeout(t);
	}
	t = window.setTimeout("liveReqDoReq()",400);
}

function liveReqDoReq() {
	if (liveReqLast != inputElement.value && inputElement.value != "") {
		if (liveReq && liveReq.readyState < 4) {
			liveReq.abort();
		}
		if (window.XMLHttpRequest) {
		// branch for IE/Windows ActiveX version
		} else if (window.ActiveXObject) {
			liveReq = new ActiveXObject("Microsoft.XMLHTTP");
		}
		
		liveReq.onreadystatechange = liveReqProcessReqChange;
		var foundQuery=processURI.indexOf("?");
		if(foundQuery==-1){
			liveReq.open("GET", processURI + "?s=" + encodeURI(inputElement.value));
		} else {
			liveReq.open("GET", processURI + "&s=" + encodeURI(inputElement.value));
		}
		liveReqLast = inputElement.value;
		liveReq.send(null);
	} else if(inputElement.value == "") {
		if(emptyString == '') {
			outputElement.innerHTML = '';
			outputElement.style.display = "none";
			mainElement.style.display = "";
		} else {
			outputElement.innerHTML = emptyString;
			mainElement.style.display = "none";
		}
	}
}

function liveReqProcessReqChange() {
	outputElement.style.display = "block";
	if (liveReq.readyState == 4) {
		outputElement.innerHTML = liveReq.responseText;
		mainElement.style.display = "none";
		if(emptyString == '') {
			
		}
		//fdTableSort.init();
		$("#searchTable").tablesorter({ widgets:["zebra"]})
	}else if (liveReq.readyState == 1) {
		outputElement.innerHTML="<div align='center' style='padding:5px'><div class='spinnerMini'><div class='double-bounce1'></div><div class='double-bounce2'></div></div></div>";
		mainElement.style.display = "none";
	}
}