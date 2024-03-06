<?php

// FIXME: Is creativehub using top cust?
$query = "SELECT NBR FROM CDW.PRN_DIG_TOP_CUST";
$topcust = mysql_query($query);
while ($row = mysql_fetch_array($topcust)) {
    $TopCusts[] = strval($row['NBR']);
}
if ( ! isset($result)) {
    exit(1);
}
while ($row = mysql_fetch_array($result)) {
    //Traffic light control
    $due = strtotime($row['DUE_TS']);
    $OrdSttId = $row['ORD_STT_ID'];
    $icon = [];

    if ((strtotime("now") > $due) && (in_array($OrdSttId, ["NE", "RC", "QU", "PR", "FN"]))) {
        $dot = "red";
    } elseif ((strtotime("now + " . $row['JOB_LEN_TOT'] . " minute") > $due)
        && (in_array($OrdSttId, ["NE", "RC", "QU", "PR", "FN"]))
    ) {
        $dot = "yellow";
    } else {
        $dot = "";
    }

    if (in_array($row['BUY_CO_NBR'], $TopCusts)) {
        $icon[] = "fa-star";
    }
    if ($row['SPC_NTE'] != "" && $row['SPC_NTE'] != "NULL") {
        $icon[] = ['icon' => "fa-comment listable", 'title' => "Ada Catatan"];
    }
    if ($row['DL_CNT'] > 0) {
        $icon[] = "fa-truck";
    }
    if ($row['PU_CNT'] > 0) {
        $icon[] = "fa-shopping-cart";
    }
    if ($row['NS_CNT'] > 0) {
        $icon[] = "fa-flag listable";
    }
    if ($row['IVC_PRN_CNT'] > 0) {
        $icon[] = ['icon' => "fa-print listable", 'title' => "Invoice Tercetak " . $row['IVC_PRN_CNT']];
    }
    if ($row['SLM_HRS'] >= 48 && $row['ORD_STT_ID'] != "CP") {
        $icon[] = ['icon' => "fa-history listable", 'title' => "Jatuh Tempo"];
    }

    if (trim($row['NAME_PPL'] . " " . $row['NAME_CO']) == "") {
        $name = "Tunai";
    } else {
        $name = trim($row['NAME_PPL'] . " " . $row['NAME_CO']);
    }

    if ($row['TOT_REM'] == 0) {
        $tot_icon = "fa-circle";
    } elseif ($row['TOT_AMT'] == $row['TOT_REM']) {
        $tot_icon = "fa-circle-o";
    } else {
        $tot_icon = "fa-dot-circle-o";
    }

    ?>

    <div id="O<?php echo $row['ORD_NBR'] ?>" class="tripane-list" data-order="<?php echo $row['ORD_NBR'] ?>">
        <div class="order-number"><?php echo $row['ORD_NBR'] ?></div>
        <div class="order-time"><?php echo parseDateTimeLiteralShort($row['DUE_TS']) ?></div>
        <?php
        if (count($icon) > 0) { ?>
            <div class="order-icons">
                <?php
                foreach ($icon as $i) { ?>
                    <div class="listable">
                        <span class="fa <?php echo $i['icon'] ?>" title="<?php echo $i['title'] ?>"></span>
                    </div>
                    <?php
                } // foreach ?>
            </div>
            <?php
        } // if ?>
        <div class="order-name"><?php echo $name ?>
            <span class='fa fa-circle listable <?php echo $dot ?>'></span>
        </div>
        <div class="order-title"><?php echo htmlentities($row['ORD_TTL'], ENT_QUOTES) ?></div>
        <div class="order-date"><?php echo parseDateShort($row['ORD_BEG_TS']) ?></div>
		<div class="order-title" style="background-color: <?php echo $row['RM_COLR'] ?>; width: 10px; height: 10px; border-radius: 20%;"></div>
        <span class="order-status"><?php echo parseDateShort($row['DUE_DTE']); ?> <span style='font-weight:700;'><?php echo $row['ORD_STT_DESC'] ?></span></span>
        <span class="order-total">Rp. <?php echo number_format($row['TOT_AMT'], 0, ',', '.') ?></span>
        <span class="fa <?php echo $tot_icon ?> listable payment-status"></span>

    </div>
    <?php
}
// While end ?>