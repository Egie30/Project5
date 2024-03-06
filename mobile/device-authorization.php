<?php
    include "framework/database/connect.php";
    if($_COOKIE["DeviceAuth"]==""){
        $auth=true;
    }else{
        $auth=false;
    }
?>
<div class="navbar">
    <div class="navbar-inner">
        <div class="left sliding"><a href="#" class="back link color-nestor"><span class="fa fa-chevron-left"></span></a></div>
            <div class="center sliding">Device Authorization</div>
            <div class="right"><a href="#" class="open-panel link icon-only color-nestor"><span class="fa fa-bars"></span></a></div>
        </div>
</div>
<div class="pages navbar-through">
    <div data-page="device-authorization" class="page">
        <div class="page-content contacts-content">
            <div class="content-block contacts-block">
                <div class="content-block-inner">
                    <p>
                        Please make sure that this is the device intended to be authorized/deauthorized.  Once authorized, this device will be automatically deauthorized if there is no activity for 7 days.
                    </p>
                    <div class="row">
                        <div class="col-50">
                            <a href="device-authorization-go.php" class="button button-big button-fill color-nestor">
                                <?php
                                    if($auth){
                                        echo "Authorize";
                                    }else{
                                        echo "Deauthorize";
                                    }
                                ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
