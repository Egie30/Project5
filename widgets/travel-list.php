<?php
    include "framework/database/connect.php";
    include "framework/functions/default.php";
    $Period=$_GET['PERIOD'];
    $userID=$_SESSION['userID'];
    $query="SELECT NAME,PRSN_NBR FROM CMP.PEOPLE PPL WHERE PRSN_ID='".$userID."'";
    $result=mysql_query($query);
    $row=mysql_fetch_array($result);
	$prsnNbr=$row['PRSN_NBR'];
    if($Period=='CURRENT'){
        $where="MONTH(DEST_TS)=MONTH(CURRENT_DATE) AND YEAR(DEST_TS)=YEAR(CURRENT_DATE)";
    }elseif($Period=='LAST'){
        $where="MONTH(DEST_TS)=MONTH(CURRENT_DATE - INTERVAL 1 MONTH) AND YEAR(DEST_TS)=YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
    }
    $query="SELECT ORIG_TS,DIST FROM CMP.AUTH_TRVL WHERE PRSN_NBR=$prsnNbr AND $where ORDER BY AUTH_TRVL_NBR";
    $result=mysql_query($query);
    
?>
<div class="navbar">
    <div class="navbar-inner">
        <div class="left sliding"><a href="#" class="back link color-nestor"><span class="fa fa-chevron-left"></span></a></div>
        <div class="center sliding">Travel List</div>
        <div class="right"><a href="#" class="open-panel link icon-only color-nestor"><span class="fa fa-bars"></span></a></div>
    </div>
</div>
<div class="pages navbar-through">
    <div data-page="travel-list" class="page">
        <div class="page-content contacts-content">
            <div class="list-block media-list contacts-block">
                <ul>
                    <?php
                        while($row=mysql_fetch_array($result)){
                            echo "<li>";
                            echo "<div class='item-inner item-content'>";
                            echo "<div class='item-subtitle-row'>";
                            echo "<div class='item-subtitle'>".parseDateTimeLiteralShort($row['ORIG_TS'])."</div>";
                            echo "<div class='item-after item-subtitle' style='color:#000000'>".number_format($row['DIST'],1,',','.')." km</div>";
                            echo "</div>";
                            echo "</div></li>";
                        }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>
