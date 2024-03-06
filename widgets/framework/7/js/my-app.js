// Initialize your app
var myApp = new Framework7({
    swipeBackPage: true,
    swipePanel: 'left',
    animateNavBackIcon: true,
    cache: true,
    //cacheDuration: 1000*5,
});

// Export selectors engine
var $$ = Dom7;

// Add view
var mainView = myApp.addView('.view-main', {
    // Because we use fixed-through navbar we can enable dynamic navbar
    dynamicNavbar: true
});

// Show/hide preloader for remote ajax loaded pages
// Probably should be removed on a production/local app
$$(document).on('ajaxStart', function (e) {
    if (e.detail.xhr.requestUrl.indexOf('autocomplete-languages.json') >= 0) {
        // Don't show preloader for autocomplete demo requests
        return;
    }
    myApp.showIndicator();
});
$$(document).on('ajaxComplete', function (e) {
    if (e.detail.xhr.requestUrl.indexOf('autocomplete-languages.json') >= 0) {
        // Don't show preloader for autocomplete demo requests
        return;
    }
    myApp.hideIndicator();
});

/* ===== Pull To Refresh Demo ===== */
/* myApp.onPageInit('pull-to-refresh', function (page) {
    // Pull to refresh content
    var ptrContent = $$('.pull-to-refresh-content');
    //var ptrContent = $$(page.container).find('.pull-to-refresh-content');
    // Add 'refresh' listener on it
    ptrContent.on('refresh', function (e) {
        setTimeout(function () {
            //window.location=window.location.href + '?' + new Date().getTime();
            location.reload();
            //mainView.router.refreshPage();
            //alert('a');
            myApp.pullToRefreshDone();
        }, 1000); 
    });
}); */

myApp.onPageInit('travel-map',function (page){
    var x=document.getElementById("coordinate");
    var y=document.getElementById("map");
    var z=document.getElementById("geocode");
    var w=document.getElementById("recenter");

    function getLocation(){
        if (navigator.geolocation){
            navigator.geolocation.getCurrentPosition(showPosition, showError);
        } else { 
            x.innerHTML="Geolocation is not supported by this browser.";
        }
    }

    function showPosition(position){
        if(z.innerHTML=="Start"){
            action="START";
        }else{
            action="FINISH";
            w.href="travel-go.php?ACTION=RESET&LAT="+position.coords.latitude+"&LNG="+position.coords.longitude;
        }
        //x.innerHTML=position.coords.latitude+','+position.coords.longitude;
        x.innerHTML="Current position found.";
        y.src="https://maps.googleapis.com/maps/api/staticmap?center="+position.coords.latitude+','+position.coords.longitude+"&zoom=17&size=400x400&scale=2&markers=size:large%7Ccolor:red%7C"+position.coords.latitude+","+position.coords.longitude+"&key=AIzaSyArBlVIq10YHbnJKHU0b7tCgU-oom9DDq8";
        z.href="travel-go.php?ACTION="+action+"&LAT="+position.coords.latitude+"&LNG="+position.coords.longitude;
    }

    function showError(error){
        switch(error.code){
            case error.PERMISSION_DENIED:
                x.innerHTML="Geolocation request denied by user."
                break;
            case error.POSITION_UNAVAILABLE:
                x.innerHTML="Location information is unavailable."
                break;
            case error.TIMEOUT:
                x.innerHTML="Request timed out."
                break;
            case error.UNKNOWN_ERROR:
                x.innerHTML="An unknown error occurred."
                break;
        }
    }
    
    getLocation();
    
    $$('#recenter').on('click',function(){
        if(document.getElementById('recenter').innerHTML=="Recenter"){
            getLocation();
        }
    });
});

myApp.onPageInit('settings',function (page){
    $$('#mobile-access').on('click',function(){
        var mobileAccess=document.getElementById("mobile-access");
        var d = new Date();
        var x=new XMLHttpRequest();
        if(mobileAccess.checked){
            d.setTime(d.getTime()+(5*60*1000));
            var expires="expires="+d.toGMTString();
            document.cookie="MobileAccess=Y;"+expires+";path=/";
            x.open("GET","cookies.php",true);
            x.send();
        }else{
            d.setTime(d.getTime()-(7*24*60*60*1000));
            var expires="expires="+d.toGMTString();
            document.cookie="MobileAccess=;"+expires+";path=/";
            x.open("GET","cookies.php",true);
            x.send();
        }
    }); 
});

myApp.onPageInit('index',function (page){
    displayChart();
});