<?php
    include "framework/database/connect.php";
    include "framework/functions/geo.php";
    $Action=$_GET['ACTION'];
    $lat=$_GET['LAT'];
    $lng=$_GET['LNG'];
    $userID=$_SESSION['userID'];
    $query="SELECT NAME,PRSN_NBR FROM CMP.PEOPLE PPL WHERE PRSN_ID='".$userID."'";
    $result=mysql_query($query);
    $row=mysql_fetch_array($result);
	$prsnNbr=$row['PRSN_NBR'];
    if($Action=="START"){
        $query="INSERT INTO CMP.AUTH_TRVL (ORIG_LAT,ORIG_LNG,PRSN_NBR) VALUES ($lat,$lng,$prsnNbr)";
        $result=mysql_query($query);
    }elseif($Action=="RESET"){
        $query="DELETE FROM CMP.AUTH_TRVL WHERE PRSN_NBR=$prsnNbr AND DEST_TS IS NULL";
        $result=mysql_query($query);
    }elseif($Action=="FINISH"){
        $query="SELECT ORIG_LAT,ORIG_LNG FROM CMP.AUTH_TRVL WHERE PRSN_NBR=$prsnNbr ORDER BY AUTH_TRVL_NBR DESC";
        $result=mysql_query($query);
        $row=mysql_fetch_array($result);
        $dLat=$lat;$dLng=$lng;$oLat=$row['ORIG_LAT'];$oLng=$row['ORIG_LNG'];
        $dist=distVGCD($oLat,$oLng,$dLat,$dLng);
        $query="SELECT MAX(AUTH_TRVL_NBR) MAX_NBR FROM CMP.AUTH_TRVL WHERE PRSN_NBR=$prsnNbr AND DEST_TS IS NULL";
        $result=mysql_query($query);
        $row=mysql_fetch_array($result);
        $maxNbr=$row['MAX_NBR'];
        $query="UPDATE CMP.AUTH_TRVL SET DEST_LAT=$lat,DEST_LNG=$lng,DEST_TS=NOW(),DIST=$dist WHERE AUTH_TRVL_NBR=$maxNbr";
        $result=mysql_query($query);
    }
?>
<div class="navbar">
    <div class="navbar-inner">
        <div class="left sliding"><a href="#" class="back link color-nestor" data-force="true" data-ignore-cache="true"><span class="fa fa-chevron-left"></span></a></div>
            <div class="center sliding">Travel Detail</div>
            <div class="right"><a href="#" class="open-panel link icon-only color-nestor"><span class="fa fa-bars"></span></a></div>
        </div>
</div>
<div class="pages navbar-through">
    <div data-page="travel-go" class="page">
        <div class="page-content contacts-content">
            <div class="content-block contacts-block">
                <div class="content-block-inner">
                    <p>
                        <img id="map" src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $lat; ?>,<?php echo $lng; ?>&zoom=17&size=400x400&scale=2&markers=size:large%7Ccolor:red%7C<?php echo $lat; ?>,<?php echo $lng; ?>&key=AIzaSyArBlVIq10YHbnJKHU0b7tCgU-oom9DDq8" width="100%" frameborder="0" style="border:0">
                    </p>
                    <p>
                        <?php
                            //echo $query;
                            if($Action=="START"){
                        ?>
                        Travel telah dimulai.<br><br>
                        Jangan lupa untuk mengakhiri perjalanan di Nestor.  Apabila perjalanan tidak diakhiri, maka data tidak dianggap valid.<br><br>
                        <?php
                            }elseif($Action=="RESET"){
                        ?>
                        Travel telah direset.<br><br>
                        Perjalanan yang dimulai telah dihapus.<br><br>
                        <?php
                            }elseif($Action=="FINISH"){
                        ?>
                        Travel telah selesai.<br><br>
                        <?php
                            }
                        ?>
                        <?php echo date('l jS \of F Y h:i:s A'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
