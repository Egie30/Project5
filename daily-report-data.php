<?php

include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";
date_default_timezone_set("Asia/Jakarta");

$Security = getSecurity($_SESSION['userID'],"Finance");

$query    = "SELECT TYP.LOG_ERROR_TYP_NBR, 
                    -- TYP.LOG_ERROR_DESC,
                    (CASE WHEN LOG.LOG_ERROR_TYP_NBR = '1' THEN CONCAT(TYP.LOG_ERROR_DESC,' 3 Bulan Kemarin') ELSE TYP.LOG_ERROR_DESC END) AS LOG_ERROR_DESC,
                    LOG.TOT_ORD
             FROM CMP.LOG_ERROR_TYP TYP
             LEFT JOIN ( SELECT 
                                COUNT(LOG.ORD_NBR) AS TOT_ORD, 
                                LOG.LOG_ERROR_TYP_NBR AS LOG_ERROR_TYP_NBR
                         FROM CDW.LOG_ERROR_ORD LOG 
                         LEFT JOIN CMP.LOG_ERROR_TYP TYP ON LOG.LOG_ERROR_TYP_NBR = TYP.LOG_ERROR_TYP_NBR
                         LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED ON LOG.ORD_NBR = HED.ORD_NBR
                         WHERE (CASE WHEN LOG.LOG_ERROR_TYP_NBR = '1' THEN DATE(LOG.UPD_TS) >= CURRENT_DATE - INTERVAL 3 MONTH ELSE DATE(LOG.UPD_TS) <= CURRENT_DATE END)
                         GROUP BY TYP.LOG_ERROR_DESC
                        ) LOG ON LOG.LOG_ERROR_TYP_NBR = TYP.LOG_ERROR_TYP_NBR";
// echo "<pre>".$query;
$result    = mysql_query($query);
while($row = mysql_fetch_array($result))
{
    if($colNbr==4)
    {
        echo "<div class='w-100'></div>";
        $colNbr=0;
    }

    echo "<div id='layout' class='col' style='cursor:pointer; border:1px solid #cccccc;color:#6A6969;margin:3px;border-radius:3px;padding-left:10px;padding-right:10px;padding-top:5px;padding-bottom:5px' onclick=".chr(34)."changeSiblingUrl('content','print-digital-list-log-error.php?ORD_NBR=".$row['ORD_NBR']."&LOG_TYP=".$row['LOG_ERROR_TYP_NBR']."&DATE=1');".chr(34)."> ";

    echo "<div class='row'><div class='col-4' style='font-weight:500;'></div>";
    echo "<div class='col-8' style='text-align:right;'></div></div>";
    echo $row['LOG_ERROR_DESC']."<br>";
    echo "<span class='badge' 
                text-align:left;
                font-size:10pt;    
                vertical-align:1px;
                overflow:hidden;
                text-overflow:ellipsis;    
                white-space:nowrap;'>".$row['TOT_ORD']."</span>&nbsp;";
    echo "</div>";
    $colNbr++;
}

while($colNbr<4)
{
    echo "<div class='col' style='background-color:rgba(0,0,0,0);margin:3px;border-radius:3px;padding-left:10px;padding-right:10px;padding-top:5px;padding-bottom:5px'>";
    echo "</div>";
    $colNbr++;
}



?>