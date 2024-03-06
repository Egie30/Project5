<?php
include_once ('framework/database/connect.php');
include_once ('framework/functions/default.php');
include_once ('framework/security/default.php');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <script>if (top.Pace) top.Pace.restart()</script>
        <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
        <link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />
        <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="framework/combobox/chosen.css">
		<script type="text/javascript" src="framework/functions/default.js"></script>
        <script src="framework/database/jquery.min.js" type="text/javascript"></script>
        <script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
        <style>
            .close-btn {
                position: absolute;
                top: 0px;
                left: 0px;
                cursor: pointer;
            }
            .first{
                border-bottom: 1px solid #dddddd ;
            } 
        </style>
    </head>
    <body>
        <?php
           
            if(isset($_GET['start'])) {
                $start_date = $_GET['start'];

                $sql = "SELECT DET.ORD_DET_NBR,
                                DET.ORD_NBR,
                                HED.ORD_NBR,
                                HED.ORD_BEG_TS,
                                HED.ORD_END_TS,
                                HED.NAME,
                                DET.DET_TTL,
                                HED.ORD_TTL,
                                DET.ORD_Q,
                                DET.TOT_SUB,
                                HED.TOT_AMT,
                                DET.PRC,
                                CAT.CATEGORY,
                                ROOM.RM_DESC,
                                TBL.TBL_DESC,
                                TYP.RTL_ORD_DESC,
                                WIFI.WIFI_UNM,
                                WIFI.WIFI_PWD,
                                HED.ORD_STT_DESC
                        FROM CMP.RTL_ORD_DET DET
                        INNER JOIN ( 
                                SELECT HED.ORD_NBR,
                                    HED.ORD_BEG_TS,
                                    HED.ORD_END_TS,
                                    HED.ORD_TTL,
                                    HED.TOT_AMT,
                                    STT.ORD_STT_DESC,
                                    BUY.NAME
                                FROM CMP.RTL_ORD_HEAD HED
                                LEFT JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
                                LEFT JOIN CMP.COMPANY BUY ON HED.BUY_CO_NBR=BUY.CO_NBR
                                WHERE DATE(HED.ORD_BEG_TS) = '$start_date'
                            ) HED ON HED.ORD_NBR = DET.ORD_NBR
                        LEFT JOIN CMP.RTL_ORD_TYP TYP ON TYP.RTL_ORD_TYP = DET.ORD_TYP
                        LEFT JOIN CMP.RTL_ORD_TYP_CAT CAT ON TYP.CAT_ID = CAT.CAT_ID
                        INNER JOIN CMP.ROOM ROOM ON ROOM.RM_NBR = DET.RM_NBR
                        LEFT JOIN CMP.TABEL TBL ON TBL.TBL_NBR = DET.TBL_NBR
                        LEFT JOIN CMP.WIFI_VCHR WIFI ON WIFI.REF_NBR = DET.ORD_DET_NBR
                        WHERE DET.ORD_NBR != 0 AND DET.DEL_NBR= 0"; 
                            // echo "<pre>". $sql;
                $show = mysql_query($sql);

                function createDescription($row){
                    $desc = "";
                    if ( ! empty($row['RTL_ORD_DESC'])) {
                        $desc = $row['CATEGORY'] . ' - ' . $row['RTL_ORD_DESC'];
                    }
                    if ( ! empty($row['DET_TTL'])) {
                        $desc .= ' ' . $row['DET_TTL'];
                    }
                    if ( ! empty($row['RM_DESC'])) {
                        $desc .= ' [<b>Ruang</b>: ' . $row['RM_DESC'] . ']';
                    }
                    if ( ! empty($row['TBL_DESC'])) {
                        $desc .= ' [<b>Meja</b>: ' . $row['TBL_DESC'] . ']';
                    }
                    // if ( ! empty($row['WIFI_UNM'])) {
                    //     $desc .= ' (<b>User</b>: ' . $row['WIFI_UNM'] . ' <b>Pass</b>: ' . $row['WIFI_PWD'] . ')';
                    // }
                    return trim($desc);
                }

            if(mysql_num_rows($show) > 0) {
        ?>

        <div id="mainResult" class="printDigitalPopupEditContent">
            <table style="width: 100%">
                <tbody style="line-height: 10px;">
                    <div>
                        <span class='fa fa-times close-btn' onclick="closePopup()"></span>
                    </div>
                    <div>
                        <br><br><br>
                        <?php
                            $desk = '';
                            $add = false; 
                            while ($row = mysql_fetch_assoc($show)) {
                                $DESC = createDescription($row);
                                if ($desk != $row['ORD_NBR']) {
                                    if($add) { 
                                        echo '<tr><td class="first"></td></tr>';
                                        $add = false; 
                                    }
                        ?>
                        <tr data-order="<?php echo $row['ORD_NBR']; ?>">
                            <td style="font-weight: bold;text-align:left;font-size: 15px;">No. Nota : <?php echo $row['ORD_NBR']; ?></td>
                        </tr>
                        <tr data-order="<?php echo $row['ORD_NBR']; ?>">
                            <td style="font-weight: bold;text-align:left;font-size: 15px; color: #3464bc;"><?php echo $row['NAME']; ?></td>
                        </tr>
                        <tr data-order="<?php echo $row['ORD_NBR']; ?>">
                            <td style="text-align:left;">Judul Nota : <?php echo $row['ORD_TTL']; ?></td>
                        </tr>
                        <tr data-order="<?php echo $row['ORD_NBR']; ?>">
                            <td style="text-align:left;white-space:nowrap;float:left;">Mulai: <?php echo $row['ORD_BEG_TS']; ?></td>
                            <td style="text-align:right;white-space:nowrap; float:right;">Selesai: <?php echo $row['ORD_END_TS']; ?></td>
                        </tr>
                        <tr data-order="<?php echo $row['ORD_NBR']; ?>">
                            <td style="font-weight: bold;text-align:left;float:left;">Status : <?php echo $row['ORD_STT_DESC']; ?></td>
                            <td style="text-align:right;float:right;">Total Nota : Rp. <?php echo number_format($row['TOT_AMT'], 0, ",", ".") ?></td>
                        </tr>
                        <tr data-order="<?php echo $row['ORD_NBR']; ?>">
                            <td style="text-align:left;">Item : </td>
                        </tr>
                        <?php }?>
                        <tr data-order="<?php echo $row['ORD_NBR']; ?>">
                            <td><li><?php echo $DESC ?></li></td>
                            <?php
                                $desk = $row['ORD_NBR'];
                                $add = true; 
                            }
                            if($add) { 
                                echo '<tr><td class="first"></td></tr>';
                            }?>
                        </tr>
                    </div>
                </tbody>
            </table>
        </div>
        <?php
            } else {
            echo "<p>Data not found</p>";
            }
        } 
        ?>
        <script>
            function closePopup() {
            parent.document.getElementById('printDigitalPopupEdit').style.display='none';
            parent.document.getElementById('fade').style.display='none';
            }
        </script>
        <script>
            $(document).ready(function () {
                window.focus();
                $('#mainResult').on('click', 'tbody tr', function () {
                    var content = top.document.getElementById('content');
                    const ord_nbr = $(this).data('order'); 
                    let url = new URL('creativehub-edit.php', content.src);
                    url.searchParams.set('BEG', 'LIST');
                    url.searchParams.set('ORD_NBR', ord_nbr);
                    content.src = url.href;
                    closePopup()
                });
            });
        </script>
    </body>
</html>
