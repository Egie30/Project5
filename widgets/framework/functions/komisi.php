<?php

/**
 * perhitungan komisi print digital
 * @param $bulan
 * @param $tahun
 * @param null $prsn_nbr
 * @return string
 */
function calcKomisiPrint($bulan, $tahun, $prsn_nbr = null)
{    
    $query = "SELECT hed.ORD_NBR,hed.ORD_TS,hed.BUY_CO_NBR,co.NAME AS BUY_CO_NAME,
                co.ACCT_EXEC_NBR,ppl.NAME AS ACCT_EXEC_NAME,det.PRN_DIG_TYP,typ.PRN_DIG_DESC,typ.PRN_DIG_EQP,
                eq.PRN_DIG_EQP_DESC, det.ORD_Q,det.PRN_LEN,det.PRN_WID,
                ((det.PRN_LEN*det.PRN_WID)*det.ORD_Q) AS TOTAL_METER,
                ppl.BRKR_PLAN_TYP,eqp.MIN_Q,eqp.PRC,
                det.TOT_SUB,hed.TOT_AMT,SUM(pymt.TND_AMT) as TND_AMT
                FROM PRN_DIG_ORD_HEAD hed
                LEFT JOIN PRN_DIG_ORD_DET det ON det.ORD_NBR = hed.ORD_NBR
                LEFT JOIN PRN_DIG_TYP typ ON typ.PRN_DIG_TYP = det.PRN_DIG_TYP
                LEFT JOIN PRN_DIG_EQP eq ON eq.PRN_DIG_EQP = typ.PRN_DIG_EQP
                LEFT JOIN COMPANY co ON co.CO_NBR = hed.BUY_CO_NBR
                LEFT JOIN PEOPLE ppl ON ppl.PRSN_NBR = co.ACCT_EXEC_NBR
                LEFT JOIN PRN_DIG_ORD_PYMT pymt ON pymt.ORD_NBR = hed.ORD_NBR
                RIGHT JOIN PRN_DIG_BRKR_TYP_EQP eqp ON eqp.PRN_DIG_EQP = typ.PRN_DIG_EQP AND eqp.PLAN_TYP = ppl.BRKR_PLAN_TYP
                WHERE 1=1 ";
    if ($prsn_nbr != null) {
        $query .= "AND ppl.PRSN_NBR = $prsn_nbr ";
    }
    $query .= "AND co.ACCT_EXEC_NBR IS NOT NULL AND ppl.BRKR_PLAN_TYP != '' AND hed.TOT_REM <= 0
                AND MONTH(pymt.CRT_TS) = ".$bulan." AND YEAR(pymt.CRT_TS) = ".$tahun."
                GROUP BY hed.ORD_NBR,det.ORD_DET_NBR
                HAVING SUM(pymt.TND_AMT) >= hed.TOT_AMT
                ORDER BY hed.ORD_NBR, ppl.PRSN_NBR, eq.PRN_DIG_EQP";

    $rs = mysql_query($query);
    $rows = array();
    while ($row = mysql_fetch_array($rs)) {
        $komisi = 0;
        if ($row['TOTAL_METER'] >= $row['MIN_Q']) {
            $total_meter = $row['TOTAL_METER'] - $row['MIN_Q'];
            $komisi = $total_meter * $row['PRC'];
        }
        $data = array();
        $data['ORD_NBR'] = $row['ORD_NBR'];
        $data['BUY_CO_NBR'] = $row['BUY_CO_NBR'];
        $data['BUY_CO_NAME'] = $row['BUY_CO_NAME'];
        $data['PRSN_NBR'] = $row['ACCT_EXEC_NBR'];
        $data['PRSN_NAME'] = $row['ACCT_EXEC_NAME'];
        $data['BRKR_PLAN_TYP'] = $row['BRKR_PLAN_TYP'];
        $data['PRN_DIG_TYP'] = $row['PRN_DIG_TYP'];
        $data['PRN_DIG_DESC'] = $row['PRN_DIG_DESC'];
        $data['PRN_DIG_EQP_DESC'] = $row['PRN_DIG_EQP_DESC'];
        $data['TOTAL_METER'] = $row['TOTAL_METER'];
        $data['MIN_Q'] = $row['MIN_Q'];
        $data['PRC'] = $row['PRC'];
        $data['KOMISI'] = $komisi;

        array_push($rows, $data);
    }

    return json_encode($rows);
}

/**
 * perhitungan komisi retail
 * @param $bulan
 * @param $tahun
 * @param null $prsn_nbr
 * @return string
 */
function calcKomisiRetail($bulan, $tahun, $prsn_nbr = null)
{
    $query = "SELECT head.ORD_NBR,co.CO_NBR,ppl.PRSN_NBR,co.NAME AS COMPANY,ppl.NAME AS PRSN_NAME,
                det.INV_NBR,cat.CAT_DESC,inv.NAME AS INVENTORY,
                typcat.MIN_Q,typcat.PRC,det.TOT_SUB,head.TOT_AMT,ppl.BRKR_PLAN_TYP
                FROM
                rtl.rtl_stk_head head
                LEFT JOIN rtl.rtl_stk_det det ON det.ORD_NBR = head.ORD_NBR
                LEFT JOIN rtl.inventory inv ON inv.INV_NBR = det.INV_NBR
                LEFT JOIN rtl.cat cat ON cat.CAT_NBR = inv.CAT_NBR
                LEFT JOIN cmp.company co ON co.CO_NBR = head.RCV_CO_NBR
                LEFT JOIN cmp.people ppl ON co.ACCT_EXEC_NBR = ppl.PRSN_NBR
                RIGHT JOIN cmp.prn_dig_brkr_typ_cat typcat ON typcat.PLAN_TYP = ppl.BRKR_PLAN_TYP AND typcat.CAT_NBR = cat.CAT_NBR
                WHERE MONTH(head.UPD_TS) = " . $bulan . " AND YEAR(head.UPD_TS) = " . $tahun . "
                AND ppl.BRKR_PLAN_TYP != '' ";

    if ($prsn_nbr != null) {
        $query .= " AND ppl.PRSN_NBR = " . $prsn_nbr . " ";
    }

    $query .= "AND head.TOT_REM <= 0
                GROUP BY ppl.PRSN_NBR,cat.CAT_NBR
                HAVING SUM(det.TOT_SUB) >= head.TOT_AMT";

    $rs = mysql_query($query);

    $rows = array();
    while ($row = mysql_fetch_array($rs)) {
        $komisi = 0;
        if ($row['TOTAL'] >= $row['MIN_Q']) {
            $komisi = ($row['TOTAL'] * $row['PRC']) / 100;
        }

        $data = array();
        $data['ORD_NBR'] = $row['REG_NBR'];
        $data['PRSN_NBR'] = $row['PRSN_NBR'];
        $data['PRSN_NAME'] = $row['NAME'];
        $data['COMPANY'] = $row['CO_NAME'];
        $data['CATEGORY'] = $row['CAT_DESC'];
        $data['BRKR_PLAN_TYP'] = $row['BRKR_PLAN_TYP'];
        $data['MIN_Q'] = $row['MIN_Q'];
        $data['TOTAL'] = $row['TOTAL'];
        $data['PRC'] = $row['PRC'];
        $data['KOMISI'] = $komisi;

        array_push($rows, $data);
    }

    return json_encode($rows);
}


/**
 * perhitungan komisi sales
 * @param $bulan
 * @param $tahun
 * @param null $prsn_nbr
 * @return string
 */
function calcKomisiSales($bulan, $tahun, $prsn_nbr = null)
{
    $query = "SELECT head.ORD_NBR,co.CO_NBR,ppl.PRSN_NBR,co.NAME AS COMPANY,ppl.NAME AS PRSN_NAME,
                det.INV_NBR,cat.CAT_DESC,inv.NAME AS INVENTORY,
                typcat.MIN_Q,typcat.PRC,SUM(det.TOT_SUB) AS TOT_SUB,head.TOT_AMT,ppl.BRKR_PLAN_TYP
                FROM
                rtl.rtl_stk_head head
                LEFT JOIN rtl.rtl_stk_det det ON det.ORD_NBR = head.ORD_NBR
                LEFT JOIN rtl.inventory inv ON inv.INV_NBR = det.INV_NBR
                LEFT JOIN rtl.cat cat ON cat.CAT_NBR = inv.CAT_NBR
                LEFT JOIN cmp.company co ON co.CO_NBR = head.RCV_CO_NBR
                LEFT JOIN cmp.people ppl ON co.ACCT_EXEC_NBR = ppl.PRSN_NBR
                RIGHT JOIN cmp.prn_dig_brkr_typ_cat typcat ON typcat.PLAN_TYP = ppl.BRKR_PLAN_TYP AND typcat.CAT_NBR = cat.CAT_NBR
                WHERE MONTH(head.UPD_TS) = " . $bulan . " AND YEAR(head.UPD_TS) = " . $tahun . "
                AND ppl.BRKR_PLAN_TYP != '' ";

    if ($prsn_nbr != null) {
        $query .= " AND ppl.PRSN_NBR = " . $prsn_nbr . " ";
    }

    $query .= "AND head.TOT_REM <= 0
                GROUP BY ppl.PRSN_NBR,cat.CAT_NBR, co.CO_NBR";

    $rs = mysql_query($query);

    $rows = array();
    while ($row = mysql_fetch_array($rs)) {
        /**
         * yg dihitung buat komisi adalah sub totalnya, bukan totalnya.
         * karena bisa jadi total 1  nota, ada detail yang bukan merupakan kategori dari setting komisi.
         * jadi yang dihitung hanyalah kategori terkait, otomatis totalnya lebih besar dari sub total
         */
        $komisi = 0;
        if ($row['TOT_SUB'] >= $row['MIN_Q']) {
            $komisi = ($row['TOT_SUB'] * $row['PRC']) / 100;
        }
        $data = array();
        $data['ORD_NBR'] = $row['ORD_NBR'];
        $data['PRSN_NBR'] = $row['PRSN_NBR'];
        $data['PRSN_NAME'] = $row['PRSN_NAME'];
        $data['CO_NBR'] = $row['CO_NBR'];
        $data['COMPANY'] = $row['COMPANY'];
        $data['CATEGORY'] = $row['CAT_DESC'];
        $data['INVENTORY'] = $row['INVENTORY'];
        $data['BRKR_PLAN_TYP'] = $row['BRKR_PLAN_TYP'];
        $data['MIN_Q'] = $row['MIN_Q'];
        $data['SUB_TOTAL'] = $row['TOT_SUB'];
        $data['TOTAL'] = $row['TOT_AMT'];
        $data['PRC'] = $row['PRC'];
        $data['KOMISI'] = $komisi;

        array_push($rows, $data);
    }

    return json_encode($rows);
}

function ComissionGoods($PrsnNbr,$PayConfigNbr){
    $query = "SELECT 
			SUM(TOT_CMSN) AS TOT_CMSN
		FROM $CDW.PAY_OUT_CMSN
		WHERE ACCT_EXEC_NBR = '".$PrsnNbr."' AND PAY_CONFIG_NBR = '".$PayConfigNbr."'
		GROUP BY ACCT_EXEC_NBR";
    $result= mysql_query($query, $cloud);
    $row   = mysql_fetch_array($result);

    return floor($row['TOT_CMSN']);
}
