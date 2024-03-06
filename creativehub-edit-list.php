<?php

include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";

// FIXME: Security clearance

$OrdNbr = mysql_escape_string($_GET['ORD_NBR']);

if (empty($_GET['ORD_NBR'])) {
    exit();
} else {
    if ($_GET['ORD_NBR'] == '-1') {
        $new = true;
        $notallowed = "cursor: not-allowed;";
    }
}

$Show = $_GET['SHOW'];
$Deleted = false;
if ($Show == "NO") {
    $Deleted = true;
    $WhereDel = "DET.DEL_NBR<>0";
} else {
    $WhereDel = "DET.DEL_NBR=0";
}

if ($_GET['DEL_D']) {
    $DelDet = mysql_escape_string($_GET['DEL_D']);
    $query = "UPDATE CMP.RTL_ORD_DET 
    SET DEL_NBR=" . $_SESSION['personNBR'] . ", UPD_TS=CURRENT_TIMESTAMP
    WHERE ORD_NBR=" . $OrdNbr . " AND ORD_DET_NBR=" . $DelDet;
    $result = mysql_query($query);

    $queryd = "SELECT ORD_DET_NBR FROM CMP.RTL_ORD_DET WHERE ORD_DET_NBR_PAR = " . $DelDet;
    $resultd = mysql_query($queryd);
    while ($rowd = mysql_fetch_array($resultd)) {
        $query = "UPDATE CMP.RTL_ORD_DET SET DEL_NBR=" . $_SESSION['personNBR']
            . ",UPD_TS=CURRENT_TIMESTAMP WHERE ORD_NBR=" . $OrdNbr . " AND ORD_DET_NBR=" . $rowd['ORD_DET_NBR'];
        $result = mysql_query($query);
        if (file_exists("creativehub\\" . $rowd['ORD_DET_NBR'])) {
            unlink("creativehub\\" . $rowd['ORD_DET_NBR']);
        }
    }
    if (file_exists("creativehub\\" . $DelDet)) {
        unlink("creativehub\\" . $DelDet);
    }
}

// FIXME: Journaling.
$query = "SELECT DET.ORD_DET_NBR,
       DET.ORD_NBR,
       DET.DET_TTL,
       DET.BEG_TS,
       DET.END_TS,
       DET.ORD_Q,
       DET.FIL_LOC,
       DET.FEE_MISC,
       DET.DISC_PCT,
       DET.DISC_AMT,
       DET.TOT_SUB,
       DET.PRC,
       CAT.CATEGORY,
       ROOM.RM_DESC,
       TBL.TBL_DESC,
       TYP.RTL_ORD_DESC,
       WIFI.WIFI_UNM,
       WIFI.WIFI_PWD
        FROM CMP.RTL_ORD_DET DET
        LEFT JOIN  CMP.RTL_ORD_TYP TYP ON TYP.RTL_ORD_TYP = DET.ORD_TYP
        LEFT JOIN  CMP.RTL_ORD_TYP_CAT CAT ON TYP.CAT_ID = CAT.CAT_ID
        LEFT JOIN  CMP.ROOM ROOM ON ROOM.RM_NBR = DET.RM_NBR
        LEFT JOIN  CMP.TABEL TBL ON TBL.TBL_NBR = DET.TBL_NBR
        LEFT JOIN CMP.WIFI_VCHR WIFI ON WIFI.REF_NBR = DET.ORD_DET_NBR
       WHERE DET.ORD_NBR=" . $OrdNbr . "
       AND DET.ORD_DET_NBR_PAR IS NULL
       AND DET.ORD_NBR != 0
       AND " . $WhereDel . "
       ORDER BY 1";
//echo "<pre>" . $query . "</pre>";
$result = mysql_query($query);
$rowCount = mysql_num_rows($result);

if ($rowCount == 0 && $Deleted) {
    exit();
}

function createDescription($row)
{
    $desc = "";
    if ( ! empty($row['RTL_ORD_DESC'])) {
        $desc = $row['CATEGORY'] . ' - ' . $row['RTL_ORD_DESC'];
    }
    if ( ! empty($row['DET_TTL'])) {
        $desc .= ' ' . $row['DET_TTL'];
    }
    if ( ! empty($row['RM_DESC'])) {
        $desc .= '<br> [<b>Ruang</b>: ' . $row['RM_DESC'] . ']';
    }
    if ( ! empty($row['TBL_DESC'])) {
        $desc .= '<br> [<b>Meja</b>: ' . $row['TBL_DESC'] . ']';
    }
    if ( ! empty($row['WIFI_UNM'])) {
        $desc .= '<br> (<b>User</b>: ' . $row['WIFI_UNM'] . ' <b>Pass</b>: ' . $row['WIFI_PWD'] . ')';
    }
    if ( ! empty($row['BEG_TS'] || $row['END_TS'])) {
        $desc .= '<br> (<b>Tanggal Mulai </b>: ' . $row['BEG_TS'] . "<br> <b>Tanggal Selesai </b>: " . $row['END_TS'] . ')';
    }
    return trim($desc);
}

?>
<!DOCTYPE html>
<div>
    <table id="<?php echo $Deleted ? "deleted" : "edit" ?>-table">
        <thead>
        <tr>
            <th class="listable">Jum</th>
            <th class="listable">Deskripsi</th>
            <!--            <th class="listable">Room</th>-->
            <!--            <th class="listable">Table</th>-->
            <th class="listable upper-sec">Harga</th>
            <th class="listable upper-sec">Lain2</th>
            <th class="listable upper-sec">Disc</th>
            <th class="listable upper-sec">Total</th>
            <th class="listable">
                <div class="listable-btn" style="<?php echo $notallowed ?>">
                <span title="Tambah Layanan" class="fa fa-plus listable-btn">
                </span>
                </div>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($rowCount == 0):
            echo "<tr class='nodata'><td colspan='8'>Belum ada item</td></tr>";
        else:
            $TotNet = 0;
            while ($row = mysql_fetch_array($result)):
                $TotNet += $row['TOT_SUB'];
                $DESC = createDescription($row);
                $file = (file_exists("creativehub\\" . $row['ORD_DET_NBR'])) ? "" : "hide";
                ?>
                <tr class="item" data-detail="<?php echo $row['ORD_DET_NBR'] ?>"
                    title="<?php echo strip_tags($DESC) ?>">
                    <td><?php echo $row['ORD_Q'] ?></td>
                    <td><?php echo $DESC ?></td>
                    <!--                    <td>--><?php //echo $row['RM_DESC']
                    ?><!--</td>-->
                    <!--                    <td>--><?php //echo $row['TBL_DESC']
                    ?><!--</td>-->
                    <td><?php echo number_format($row['PRC'], 0, ",", ".") ?></td>
                    <td><?php echo number_format($row['FEE_MISC'], 0, ",", ".") ?></td>
                    <td><?php echo number_format($row['DISC_AMT'], 0, ",", ".") ?> (<?php echo $row['DISC_PCT'] ?: 0 ?>
                        %)
                    </td>
                    <td><?php echo number_format($row['TOT_SUB'], 0, ",", ".") ?></td>
                    <td>
                        <div class='listable-btn link-btn <?php echo $file ?>' title="Unduh file">
                            <span class='fa fa-link listable-btn'></span>
                        </div>
                        <div class="listable-btn nest-btn" title="Tambah item anak">
                            <span class="fa fa-list-ul listable-btn" title="Tambah item anak"></span>
                        </div>
                        <div class="listable-btn trash-btn" title="Hapus item">
                            <span class="fa fa-trash listable-btn" title="Hapus item"></span>
                        </div>
                    </td>
                </tr>
                <?php
                // Nest item
                $query = "SELECT DET.ORD_DET_NBR,
                           DET.ORD_NBR,
                           DET.DET_TTL,
                           DET.ORD_Q,
                           DET.FIL_LOC,
                           DET.FEE_MISC,
                           DET.DISC_PCT,
                           DET.DISC_AMT,
                           DET.TOT_SUB,
                           DET.PRC,
                           CAT.CATEGORY,
                           ROOM.RM_DESC,
                           TBL.TBL_DESC,
                           TYP.RTL_ORD_DESC,
                           WIFI.WIFI_UNM,
                           WIFI.WIFI_PWD
                            FROM CMP.RTL_ORD_DET DET
                            LEFT JOIN  CMP.RTL_ORD_TYP TYP ON TYP.RTL_ORD_TYP = DET.ORD_TYP
                            LEFT JOIN  CMP.RTL_ORD_TYP_CAT CAT ON TYP.CAT_ID = CAT.CAT_ID
                            LEFT JOIN  CMP.ROOM ROOM ON ROOM.RM_NBR = DET.RM_NBR
                            LEFT JOIN  CMP.TABEL TBL ON TBL.TBL_NBR = DET.TBL_NBR
                            LEFT JOIN CMP.WIFI_VCHR WIFI ON WIFI.REF_NBR = DET.ORD_DET_NBR
                           WHERE DET.ORD_DET_NBR_PAR = " . $row['ORD_DET_NBR'] . "
                           AND DET.ORD_NBR != 0
                           AND " . $WhereDel . "
                           ORDER BY 1";
                $resultc = mysql_query($query);
                $rowCount = mysql_num_rows($resultc);
                if ($rowCount > 0):
                    while ($rowc = mysql_fetch_array($resultc)):
                        $TotNet += $rowc['TOT_SUB'];
                        $DESC = createDescription($rowc);
                        $filec = (file_exists("creativehub\\" . $rowc['ORD_DET_NBR'])) ? "" : "hide";
                        ?>
                        <tr class="item item-nest" data-detail="<?php echo $rowc['ORD_DET_NBR'] ?>"
                            title="<?php echo $DESC ?>">
                            <td>
                                <?php echo $rowc['ORD_Q'] ?>
                            </td>
                            <td><?php echo $DESC ?></td>
                            <!--                            <td>--><?php //echo $row['RM_DESC']
                            ?><!--</td>-->
                            <!--                            <td>--><?php //echo $row['TBL_DESC']
                            ?><!--</td>-->
                            <td><?php echo number_format($rowc['PRC'], 0, ",", ".") ?></td>
                            <td><?php echo number_format($rowc['FEE_MISC'], 0, ",", ".") ?></td>
                            <td><?php echo number_format($rowc['DISC_AMT'], 0, ",", ".") ?>
                                (<?php echo $rowc['DISC_PCT'] ?: 0 ?>%)
                            </td>
                            <td><?php echo number_format($rowc['TOT_SUB'], 0, ",", ".") ?></td>
                            <td>
                                <div class='listable-btn link-btn <?php echo $filec ?>' title="Unduh file">
                                    <span class='fa fa-link listable-btn'></span>
                                </div>
                                <div class="listable-btn trash-btn" title="Hapus item">
                                    <span class="fa fa-trash listable-btn" title="Hapus item"></span>
                                </div>
                            </td>
                        </tr>
                    <?php
                    endwhile; // End while
                endif; // End if nest item
            endwhile; // End while
        endif; // End else ?>
        </tbody>
    </table>
    <input type="hidden" id="<?php echo $Deleted ? "DEL_" : "" ?>TOT_NET" value="<?php echo $TotNet; ?>"/>
</div>