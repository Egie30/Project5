<?php
    include "framework/database/connect.php";
    $userID=$_SESSION['userID'];
    $query="SELECT NAME,PRSN_NBR FROM CMP.PEOPLE PPL WHERE PRSN_ID='".$userID."'";
    $result=mysql_query($query);
    $row=mysql_fetch_array($result);
	$prsnNbr=$row['PRSN_NBR'];
    $query="SELECT ORIG_TS,DEST_TS FROM CMP.AUTH_TRVL WHERE PRSN_NBR=$prsnNbr ORDER BY AUTH_TRVL_NBR DESC";
    $result=mysql_query($query);
    $row=mysql_fetch_array($result);
    if(($row['ORIG_TS']!='')&&($row['DEST_TS']=='')){
        $travel=true;
    }else{
        $travel=false;
    }
?>
<div class="navbar">
    <div class="navbar-inner">
        <div class="left sliding"><a href="#" class="back link color-nestor"><span class="fa fa-chevron-left"></span></a></div>
            <div class="center sliding">Travel</div>
            <div class="right"><a href="#" class="open-panel link icon-only color-nestor"><span class="fa fa-bars"></span></a></div>
        </div>
</div>
<div class="pages navbar-through">
    <div data-page="travel-map" class="page">
        <div class="page-content contacts-content">
            <div class="content-block contacts-block">
                <div class="content-block-inner">
                    <p>
                        <img id="map" src="https://maps.googleapis.com/maps/api/staticmap?center=36.071531,-94.123345&zoom=17&size=400x400&scale=2&markers=size:large%7Ccolor:red%7C36.071531,-94.123345&key=AIzaSyArBlVIq10YHbnJKHU0b7tCgU-oom9DDq8" width="100%" frameborder="0" style="border:0">
                    </p>
                    <p id="coordinate">
                    </p>
                    <div class="row">
                        <?php if(!$travel){ ?>
                            <div class="col-50"><a id="geocode" class="button button-big button-fill color-nestor">Start</a></div>
                            <div class="col-50"><a href="#" id="recenter" class="button button-big button-fill color-gray">Recenter</a></div>
                        <?php }else{ ?>
                            <div class="col-50"><a id="geocode" class="button button-big button-fill color-nestor">Finish</a></div>
                            <div class="col-50"><a href="#" id="recenter" class="button button-big button-fill color-gray">Reset</a></div>
                        <?php } ?>
                    </div>
                    <p>Semua travel yang menggunakan kendaraan pribadi dan yang akan melalui proses reimbursement membutuhkan geotagging secara elektronik dengan GPS.  Pastikan menginput tag di tempat awal perjalanan dan di tempat akhir perjalanan disetiap segmen travel yang dilakukan.</p>
                    <p>
                </div>
            </div>
            <div class="list-block contacts-block">
                <ul>
                    <li><a href="travel-list.php?PERIOD=CURRENT" class="item-link item-content">
                        <div class="item-inner"> 
                            <div class="item-title">Travel Bulan Ini</div>
                        </div></a></li>
                    <li><a href="travel-list.php?PERIOD=LAST" class="item-link item-content">
                        <div class="item-inner"> 
                            <div class="item-title">Travel Bulan Lalu</div>
                        </div></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
