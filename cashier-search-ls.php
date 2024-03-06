<?php
require_once "framework/database/connect-cashier.php";



$ignorePrefix = array("=", "-", ":", "<", ">");
$searchQuery  = strtoupper($_REQUEST["s"]);

echo "<div style='padding:5px;'>";

if (!in_array($searchQuery[0], $ignorePrefix)) {
    $whereClauses = array("INV.DEL_NBR=0", "SPL.DEL_NBR=0");

    if ($searchQuery != "") {
        $searchQuery = explode(" ", $searchQuery);

        foreach ($searchQuery as $query) {
            $query = trim($query);

            if (empty($query)) {
                continue;
            }

            if (strrpos($query, '%') === false) {
                $query = '%' . $query . '%';
            }
            $whereClauses[] = "(
                INV.INV_NBR LIKE '" . $query . "'
                OR INV.INV_BCD LIKE '" . $query . "'
                OR INV.NAME LIKE '" . $query . "'
                OR CAT.CAT_DESC LIKE '" . $query . "'
                OR SUB.CAT_SUB_DESC LIKE '" . $query . "'
                OR SPL.NAME LIKE '" . $query . "'
                OR DSC.CAT_DISC_DESC LIKE '" . $query . "'
                OR SHLF.CAT_SHLF_DESC LIKE '" . $query . "'
                OR PRC.CAT_PRC_DESC LIKE '" . $query . "'
				OR THIC LIKE '" . $query . "'
				OR SIZE LIKE '" . $query . "'
				OR CASE 
					WHEN COLR_DESC = '' THEN CONCAT(TRIM(INV.NAME),' ',TRIM(THIC),' ',TRIM(SIZE),' ',TRIM(WEIGHT)) LIKE '" . $query . "'
					WHEN THIC = '' THEN CONCAT(TRIM(INV.NAME),' ',TRIM(SIZE),' ',TRIM(WEIGHT)) LIKE '" . $query . "'
					ELSE CONCAT(INV.NAME,' ',THIC,' ',SIZE,' ',WEIGHT) LIKE '" . $query . "'
				END
            )";
        }
    }

    $whereClauses = implode(" AND ", $whereClauses);
    
    //Search for inventory number
    $query = "SELECT INV.INV_NBR,
                INV.CAT_NBR,
                CAT.CAT_DESC,
                INV.CAT_SUB_NBR,
                SUB.CAT_SUB_DESC,
                INV.NAME,
                INV.INV_BCD,
                INV.INV_PRC,
                INV.PRC - COALESCE(COALESCE(DSC.CAT_DISC_PCT / 100 * INV.PRC, DSC.CAT_DISC_AMT), 0) AS PRC,
                SPL.NAME AS CO_NAME,
                SPL.TAX_F,
                INV.CAT_SHLF_NBR,
                SHLF.CAT_SHLF_DESC,
                INV.CAT_PRC_NBR,
                PRC.CAT_PRC_DESC,
                DSC.CAT_DISC_NBR,
                DSC.CAT_DISC_DESC,
                DSC.CAT_DISC_AMT,
                DSC.CAT_DISC_PCT,
                COALESCE(COALESCE(DSC.CAT_DISC_PCT / 100 * INV.PRC, DSC.CAT_DISC_AMT), 0) AS DISC
            FROM RTL.INVENTORY INV  
                LEFT OUTER JOIN RTL.CAT CAT ON INV.CAT_NBR=CAT.CAT_NBR 
                LEFT OUTER JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR 
                LEFT OUTER JOIN RTL.CAT_SHLF SHLF ON INV.CAT_DISC_NBR=SHLF.CAT_SHLF_NBR
                LEFT OUTER JOIN RTL.CAT_PRC PRC ON INV.CAT_PRC_NBR=PRC.CAT_PRC_NBR
                LEFT OUTER JOIN (
                    SELECT
                        CAT_DISC_NBR,
                        CAT_DISC_DESC,
                        CAT_DISC_AMT,
                        CAT_DISC_PCT
                    FROM RTL.CAT_DISC
                ) DSC ON INV.CAT_DISC_NBR=DSC.CAT_DISC_NBR 
                LEFT OUTER JOIN CMP.COMPANY SPL ON INV.CO_NBR=SPL.CO_NBR
				LEFT OUTER JOIN CMP.INV_COLR CLR ON INV.COLR_NBR= CLR.COLR_NBR 
            WHERE ". $whereClauses ."
            ORDER BY INV.UPD_TS DESC LIMIT 500";

    $result = mysql_query($query, $rtl);
	
    if (mysql_num_rows($result) > 0) {
        echo "<b>Pilih dari daftar inventaris dibawah ini:</b>";
        echo "<table style='width:520px;padding:0px;margin:0px' class='std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable'>";
        
        while ($row = mysql_fetch_array($result)) {
            echo "<tr style='cursor:pointer' onclick=\"doListingRequest('" . $row['INV_BCD'] . "', 'ADD');\">";
            echo "<td>";
            echo $row['NAME'];
            echo " <span style='color:#999999'>" . $row['INV_BCD'] . "</span><br/>";
            echo $row['CAT_DESC'] . " " . $row['CAT_SUB_DESC'] . " <span style='color:#999999'>" . $row['INV_NBR'] . "</span> " . $row['CO_NAME'] . $disc . "</div>";
            if ($row['DISC'] != 0) {
                echo "<br/>" . $row['CAT_DISC_DESC'] . " <span style='color:#999999'>(" . number_format(-1 * $row['DISC'], 0, ",", ".") . ")</span>";
            }
            echo "</td>";
            echo "<td style='vertical-align:top;text-align:right'><b>" . number_format($row['PRC'], 0, ",", ".") . "</b></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<b>Maaf, kata kunci yang Anda gunakan tidak tersedia didalam daftar inventaris.</b>";
    }
}

if ($searchQuery[0] == ":") {
    if (substr($searchQuery, 1) == ">") {
        $activeType = 1;
    } else {
        $activeType = 2;
    }

    //Check to see if any open transaction
    $query  = "SELECT TRSC_NBR, Q_NBR, DATE(CRT_TS) AS CRT_TS, TIME(CRT_TS) AS CRT_TIME, ACT_F FROM RTL.CSH_REG WHERE (ACT_F=1 OR ACT_F = 2) AND POS_ID = " . $POSID . " AND DATE(CRT_TS)=CURRENT_DATE GROUP BY TRSC_NBR ORDER BY CRT_TS DESC";
    $result = mysql_query($query, $rtl);
	
	if (mysql_num_rows($result) > 0) {
        echo "<b>Pilih dari daftar transaksi dibawah ini:</b>";
        echo "<table class='std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable' style='width:520px;padding:0px;margin:0px;margin-top:10px'>";
        
        while ($row = mysql_fetch_array($result)) {
			$activeType = $row['ACT_F'];
			
            if ($activeType == 2) {
				echo "hold";
                $onclick = "doListingRequest('" . $row['Q_NBR'] . "', 'OHL');";
            } else {
                $onclick .= "parent.document.getElementById('listing').src='cashier-listing.php?POS_ID=" . $POSID . "';showListingView();";
            }

            echo "<tr style='cursor:pointer' onclick=\"" . $onclick . "\">";

            echo "<td style='font-size:10pt;vertical-align:top'>";
            echo "<b>" . $row['Q_NBR'] . "</b>";
            echo "</td>";
            echo "<td style='color:#999999;vertical-align:top'>";
            echo $row['CRT_TS'];
            echo "<hr/>";
            echo $row['CRT_TIME'];
			echo "<hr/>";
			if($row['ACT_F'] == 1) { echo "Active"; } 
				else if ($row['ACT_F'] == 2) { echo "Hold"; }
			echo "</td>";
            echo "<td style='vertical-align:top;text-align:right;padding-right:0px;'>";

            //Retail entries
            $queryRetail  = "SELECT REG_NBR,TRSC_NBR,REG.CO_NBR,REG.RTL_BRC,RTL_Q,REG.RTL_PRC,INV.NAME AS NAME_DESC,COALESCE(DISC_AMT,0) AS DISC_AMT,COALESCE(DISC_PCT,0) AS DISC_PCT,TND_AMT,ORD_NBR,CSH_FLO_DESC,REG.CSH_FLO_TYP,CSH_FLO_MULT,PYMT_DESC,REG.PYMT_TYP,ACT_F
                                FROM RTL.CSH_REG REG LEFT OUTER JOIN
                                     RTL.COMPANY COM ON REG.CO_NBR=COM.CO_NBR LEFT OUTER JOIN
                                     RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP LEFT OUTER JOIN
                                     RTL.PYMT_TYP PAY ON REG.PYMT_TYP=PAY.PYMT_TYP LEFT OUTER JOIN RTL.INVENTORY INV ON REG.RTL_BRC=INV.INV_BCD
                                WHERE (INV.DEL_NBR=0 OR REG.CSH_FLO_TYP='FL' OR REG.CSH_FLO_TYP='DP' OR REG.CSH_FLO_TYP='GP' ) AND TRSC_NBR=".$row['TRSC_NBR']."
                                AND REG.RTL_BRC <> ''
                                ORDER BY CRT_TS DESC";
			
            $resultRetail = mysql_query($queryRetail, $rtl);
            $altRetail    = "";

            echo "<table class='std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable' style='border:1px solid #eeeeee;overflow:scroll;width:100%'>";

            while ($rowRetail = mysql_fetch_array($resultRetail)) {
                if ($rowRetail['DISC_AMT'] != 0) {
                    $disc = " <span style='color:#999999'>(Disc Rp. " . number_format($rowRetail['DISC_AMT'], 0, ",", ".") . ")</span>";
                } else if ($rowRetail['DISC_PCT'] != 0) {
                    $disc = " <span style='color:#999999'>(Disc " . number_format($rowRetail['DISC_PCT'], 0, ",", ".") . "%)</span>";
                } else {
                    $disc = "";
                }
                
                echo "<tr>";
                
                if ($rowRetail['CSH_FLO_TYP'] == 'PN') {
                    echo "<td style='text-align:left'>" . $rowRetail['NAME_DESC'] . " (PPN) <br/><span style='color:#999999'>" . $rowRetail['RTL_BRC'] . "</span> " . $rowRetail['RTL_Q'] . " x @ Rp. " . number_format($rowRetail['RTL_PRC'], 0, ",", ".") . $disc . "</td>";
                } else {
                    echo "<td style='text-align:left'>" . $rowRetail['NAME_DESC'] . "<br/><span style='color:#999999'>" . $rowRetail['RTL_BRC'] . "</span> " . $rowRetail['RTL_Q'] . " x @ Rp. " . number_format($rowRetail['RTL_PRC'], 0, ",", ".") . $disc . "</td>";
                }
                
                if ($rowRetail['DISC_PCT'] != 0) {
                    $DiscVal = ($rowRetail['CSH_FLO_MULT'] * $rowRetail['TND_AMT']) * ($rowRetail['DISC_PCT'] / 100);
                } else {
                    $DiscVal = $rowRetail['DISC_AMT'];
                }
                
                echo "<td style='text-align:right;vertical-align:top'><b>Rp. " . number_format(($rowRetail['CSH_FLO_MULT'] * $rowRetail['TND_AMT']) - $DiscVal, 0, ",", ".") . "</b></td>";
                echo "</tr>";
            }

            echo "</table>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<b>Tidak dapat menemukan daftar transaksi yang diinginkan.</b>";
    }
}

echo "</div>";
?>
