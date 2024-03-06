<?php
    include "framework/database/connect.php";
    if($_COOKIE["DeviceAuth"]==""){
        setCookie("DeviceAuth","y",time()+7*24*3600);
        $auth=true;
    }else{
        setCookie("DeviceAuth","",time()-3600);
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
                        This device has been successfully 
                        <?php
                            if($auth){
                                echo "authorized.";
                            }else{
                                echo "deauthorized.";
                            }
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
