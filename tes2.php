<?php
    // ini_set("include_path", '/home/sbsjewel/php:' . ini_get("include_path")); 
    require_once "framework/database/connect.php";
    require_once "framework/functions/dotmatrix.php";
    require_once "framework/phpExcel/Classes/PHPExcel.php"; 

    $Type = isset($_GET['TYPE']) ? $_GET['TYPE'] : '';
    $salestype = isset($_GET['TYP']) ? $_GET['TYP'] : '';
    $orderNumber = isset($_GET['ORD_NBR']) ? $_GET['ORD_NBR'] : '';

    if ($salestype == "EST") {
        $headtable = "CMP.PRN_DIG_ORD_HEAD_EST";
        $detailtable = "CMP.PRN_DIG_ORD_DET_EST";
        $paymenttable = "CMP.PRN_DIG_ORD_PYMT_EST";
    } else {
        $headtable = "CMP.PRN_DIG_ORD_HEAD";
        $detailtable = "CMP.PRN_DIG_ORD_DET";
        $paymenttable = "CMP.PRN_DIG_ORD_PYMT";
    }

    // $Title = 'invoice';
    $query = "SELECT HED.ORD_NBR,
        ORD_TS,
        BUY_PRSN_NBR,
        PPL.NAME AS NAME_PPL,
        COM.NAME AS NAME_CO,
        COM.ADDRESS AS ADDRESS_CO,
        COM.ZIP AS ZIP_CO,
        COM.PHONE AS PHONE_CO,
        BUY_CO_NBR,
        CNS_CO_NBR,
        BIL_CO_NBR,
        BILCOM.NAME AS BIL_COM,
        BILCOM.ADDRESS AS BIL_ADDRESS,
        BILCOM.ZIP AS BIL_ZIP,
        BILCOM.PHONE AS BIL_PHONE,
        REF_NBR,
        ORD_TTL,
        PRN_CO_NBR,
        PRNCOM.NAME AS PRN_COM,
        PRNCOM.ADDRESS AS PRN_ADDRESS,
        PRNCOM.ZIP AS PRN_ZIP,
        PRNCOM.PHONE AS PRN_PHONE,
        PRNCOM.BNK_ACCT_NM AS PRN_BNK_ACCT_NM,
        PRNCOM.BNK_ACCT_NBR AS PRN_BNK_ACCT_NBR,
        PRNCOM.BNK_CO_NBR AS PRN_BNK_CO_NBR,
        COM_BNK.NAME AS NAME_BNK, 
        FEE_MISC,
        TAX_APL_ID,
        TAX_AMT,
        TOT_AMT,
        PYMT_DOWN,
        PYMT_REM,
        VAL_PYMT_DOWN,
        VAL_PYMT_REM,
        TOT_REM,
        SPC_NTE,
        JOB_LEN_TOT,
        SUM(PYMT.TND_AMT) AS TOT_PYMT,
        HED.ACTG_TYP,
        CRT.NAME AS CRT_NAME,
        POS_DESC ";
    if ($salestype == "EST") {
        $query .= ",BO_HEAD_DESC, BO_BODY_DESC, BO_FOOT_DESC";
    }
    $query .= "
    FROM " . $headtable . " HED
        LEFT OUTER JOIN " . $paymenttable . " PYMT ON HED.ORD_NBR=PYMT.ORD_NBR
        LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
        INNER JOIN CMP.PEOPLE CRT ON CRT.PRSN_NBR=HED.CRT_NBR
        INNER JOIN CMP.POS_TYP TYP ON TYP.POS_TYP=CRT.POS_TYP
        LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
        LEFT OUTER JOIN CMP.COMPANY PRNCOM ON HED.PRN_CO_NBR=PRNCOM.CO_NBR
        LEFT OUTER JOIN CMP.COMPANY BILCOM ON HED.BIL_CO_NBR=BILCOM.CO_NBR
        LEFT OUTER JOIN CMP.COMPANY COM_BNK ON PRNCOM.BNK_CO_NBR=COM_BNK.CO_NBR
    WHERE HED.ORD_NBR='" . $orderNumber . "' AND PYMT.DEL_NBR=0";

    $result = mysql_query($query);
    $row = mysql_fetch_array($result);

    $OrdTtl = $row['ORD_TTL'];
    $RefNbr = $row['REF_NBR'];
    $ActgTyp = $row['ACTG_TYP'];
    $TaxAmt  = $row['TAX_AMT'];
    $FeeMisc = $row['FEE_MISC'];

    // Initialize PHPExcel object
    $excel = new PHPExcel();
    $excel->getProperties()->setTitle("Invoice Data")->setDescription("Invoice Data");
    $sheet = $excel->setActiveSheetIndex(0);

    $sheet->getStyle('B4:B5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    $sheet->mergeCells('A1:E3');

    // Determine which logo to use based on the seller
    $img = '';
    if ($row['PRN_COM'] == 'Champion Campus') {
        $img = "img/print/excel-campus.png";
    } elseif ($row['PRN_COM'] == 'Champion Printing') {
        $img = "img/print/excel-printing.png";
    }else {
        $img = "img/print/default.png";
    }

    // Insert the logo
    if ($img != '' && file_exists($img)) {
        $drawing = new PHPExcel_Worksheet_Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath($img);
        $drawing->setCoordinates('A1');
        $drawing->setHeight(50);
        $drawing->setWorksheet($sheet);
    }

    // Data Excel
    $sheet->getStyle('A6:A16')->getFont()->setBold(true); 
    $sheet->getStyle('B8')->getFont()->setBold(true); 
    $sheet->getStyle('B10')->getFont()->setBold(true); 
    $sheet->getStyle('B12')->getFont()->setBold(true); 
    $sheet->getStyle('B14')->getFont()->setBold(true); 

    $sheet->setCellValue('A4', 'Invoice');
    $sheet->setCellValue('A5', 'No');
    // $sheet->setCellValue('B2', ':');
    $sheet->setCellValue('B5', $orderNumber);
    $sheet->setCellValue('A6', 'Ref.');
    // $sheet->setCellValue('B3', ':');
    $sheet->setCellValue('B6', $RefNbr);

    // $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A7', 'Invoice Date');
    $sheet->setCellValue('B7', $row['ORD_TS']);
    $sheet->setCellValue('A8', 'From');
    // $sheet->setCellValue('B5', ':');
    $sheet->setCellValue('B8', $row['PRN_COM']);

    // $sheet->mergeCells('A6:H6');
    // $sheet->mergeCells('A8:H8');
    // $sheet->mergeCells('A10:H10');

    $sheet->setCellValue('B9', $row['PRN_ADDRESS']);
    $sheet->setCellValue('A10', 'Customer');
    // $sheet->setCellValue('B7', ':');
    $sheet->setCellValue('B10', $row['NAME_CO'] ? $row['NAME_CO'] : 'Tunai');
    $sheet->setCellValue('B11', $row['ADDRESS_CO']."".$row['ZIP_CO']);
    $sheet->setCellValue('A12', 'Bill To');
    // $sheet->setCellValue('B9', ':');
    $sheet->setCellValue('B12', $row['NAME_CO'] ? $row['NAME_CO'] : 'Tunai');
    $sheet->setCellValue('B13', $row['ADDRESS_CO']."".$row['ZIP_CO']);
    $sheet->setCellValue('A14', 'Order Title');
    // $sheet->setCellValue('B11', ':');
    $sheet->setCellValue('B14', $OrdTtl);


    $sheet->getStyle('A16:E16')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->freezePane('A17');


    $sheet->setCellValue('A16', 'QTY')->getStyle('A16')->getFont()->setBold(true);
    $sheet->setCellValue('B16', 'Description')->getStyle('B16')->getFont()->setBold(true);
    $sheet->setCellValue('C16', 'Price/Rate')->getStyle('C16')->getFont()->setBold(true);
    $sheet->setCellValue('D16', 'Disc')->getStyle('D16')->getFont()->setBold(true);
    $sheet->setCellValue('E16', 'Total')->getStyle('E16')->getFont()->setBold(true);


    $sheet->getColumnDimension('A')->setWidth(13); 
    $sheet->getColumnDimension('B')->setWidth(65);
    $sheet->getColumnDimension('C')->setWidth(10);
    $sheet->getColumnDimension('D')->setWidth(10);
    $sheet->getColumnDimension('E')->setWidth(10);
    // $sheet->getColumnDimension('F')->setWidth(10);
    // $sheet->getColumnDimension('G')->setWidth(15);
    // $sheet->getColumnDimension('H')->setWidth(20);

    $rowIndex = 17; 
    $queryDetails = "SELECT ORD_DET_NBR,DET.ORD_NBR,DET_TTL,PRN_DIG_DESC,DET.PRN_DIG_PRC,ORD_Q,FIL_LOC,PRN_LEN,PRN_WID,FEE_MISC,FAIL_CNT,DISC_PCT,DISC_AMT,VAL_ADD_AMT,TOT_SUB,ROLL_F,HND_OFF_TYP,HND_OFF_TS,SORT_BAY_ID,DET.PRN_DIG_TYP
                    FROM " . $detailtable . " DET 
                        LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
                        LEFT OUTER JOIN CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
                    WHERE ORD_NBR=" . $orderNumber . " AND ORD_DET_NBR_PAR IS NULL AND DET.DEL_NBR=0 ORDER BY 1";

    $resultDetails = mysql_query($queryDetails);
    while ($rowDetails = mysql_fetch_array($resultDetails)) {

        if($rowDetails['PRN_DIG_TYP']=='PROD'){
            $query="SELECT 
                        ORD_DET_NBR,
                        DET.ORD_NBR,
                        CLD.ORD_DET_NBR_PAR,
                        DET_TTL,
                        DET.PRN_DIG_TYP,
                        PRN_DIG_DESC,
                        DET.ORD_Q,
                        FIL_LOC,
                        PRN_LEN,
                        PRN_WID,
                        (COALESCE(CLD.TOT_SUB,0)+COALESCE(CLD.DISC_AMT,0)-COALESCE(CLD.FEE_MISC,0))/COALESCE(DET.ORD_Q,1)+ COALESCE(DET.FEE_MISC,0) AS PRN_DIG_PRC,
                        COALESCE(CLD.FEE_MISC,0)/COALESCE(DET.ORD_Q,1) AS FEE_MISC,COALESCE(CLD.DISC_AMT,0)/COALESCE(DET.ORD_Q,1) AS DISC_AMT,
                        CLD.VAL_ADD_AMT,
                        CLD.TOT_SUB + (COALESCE(DET.FEE_MISC,0)*COALESCE(DET.ORD_Q,1)) AS TOT_SUB,
                        COALESCE(DET.FEE_MISC,0) AS DET_FEE_MISC 
                    FROM ". $detailtable ." DET 
                        LEFT OUTER JOIN
                        (
                            SELECT ORD_DET_NBR_PAR,
                                SUM(ORD_Q) AS ORD_Q,
                                SUM(FEE_MISC*ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS FEE_MISC,
                                SUM(FAIL_CNT) AS FAIL_CNT,SUM(DISC_AMT*ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS DISC_AMT,
                                SUM(VAL_ADD_AMT) AS VAL_ADD_AMT,
                                SUM(TOT_SUB) AS TOT_SUB
                            FROM ". $detailtable ." DET
                            WHERE ORD_DET_NBR_PAR=".$rowDetails['ORD_DET_NBR']." AND DET.DEL_NBR=0 GROUP BY 1 ORDER BY 1
                        )CLD ON DET.ORD_DET_NBR=CLD.ORD_DET_NBR_PAR
                        LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
                    WHERE ORD_DET_NBR=".$rowDetails['ORD_DET_NBR']." AND DET.DEL_NBR=0 AND DET.PRN_DIG_TYP='PROD' 
                    GROUP BY 1,2 
                    ORDER BY 1";
        }else{
            $query="SELECT 
                        ORD_DET_NBR,
                        DET.ORD_NBR,
                        ORD_DET_NBR_PAR,
                        DET_TTL,
                        PRN_DIG_DESC,
                        DET.PRN_DIG_PRC,
                        DET.PRN_DIG_TYP,
                        ORD_Q,FIL_LOC,
                        PRN_LEN,
                        PRN_WID,
                        FEE_MISC,
                        FAIL_CNT,
                        DISC_PCT,
                        DISC_AMT,
                        VAL_ADD_AMT,
                        TOT_SUB
                    FROM ". $detailtable ." DET 
                        LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP
                    WHERE ORD_DET_NBR=".$rowDetails['ORD_DET_NBR']." OR ORD_DET_NBR_PAR=".$rowDetails['ORD_DET_NBR']." AND DET.DEL_NBR=0 
                    ORDER BY 1";
        }

        $resultc=mysql_query($query);
        while($rowc=mysql_fetch_array($resultc))
        {
            $SubTotal = $rowc['TOT_SUB'];
            $QTotal += $rowc['ORD_Q'];
            $PriceTotal += $SubTotal;
            if(($rowc['PRN_LEN']!="")&&($rowc['PRN_WID']!="")){$prnDim=" ".$rowc['PRN_LEN']."x".$rowc['PRN_WID'];}else{$prnDim="";}
            $price=$rowc['PRN_DIG_PRC']+$rowc['VAL_ADD_AMT']+$rowc['FEE_MISC'];
            $tot_price = $price - $rowc['DISC_AMT'];
            if ($i%2 == 0) {$color="#f2f2f2";}else{$color="#FFFFFF";}

            $sheet->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C:F')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $sheet->setCellValue('A' . $rowIndex, $rowDetails['ORD_Q']);
            $sheet->setCellValue('B' . $rowIndex, trim($rowc['DET_TTL']." ".$rowc['PRN_DIG_DESC'].$prnDim));
            $sheet->setCellValue('C' . $rowIndex, number_format($rowc['PRN_DIG_PRC'], 2));
            $sheet->setCellValue('D' . $rowIndex, number_format($rowc['DISC_AMT'], 2));
            $sheet->setCellValue('E' . $rowIndex, number_format($rowc['TOT_SUB'], 2));

            $rowIndex++;
        }
    }

    $sheet->getStyle('D' . ($rowIndex + 2) . ':D' . ($rowIndex + 8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


    $sheet->setCellValue('D' . ($rowIndex + 2), 'Subtotal');
    $sheet->setCellValue('D' . ($rowIndex + 3), 'Tax');
    $sheet->setCellValue('D' . ($rowIndex + 4), 'S&H');
    $sheet->setCellValue('D' . ($rowIndex + 5), 'Total');
    $sheet->setCellValue('D' . ($rowIndex + 6), 'Paid');
    $sheet->setCellValue('D' . ($rowIndex + 7), 'Balance');

    $sheet->getStyle('D' . ($rowIndex + 5))->getFont()->setBold(true); 
    $sheet->getStyle('D' . ($rowIndex + 8))->getFont()->setBold(true); 

    $sheet->getStyle('E' . ($rowIndex + 2) . ':E' . ($rowIndex + 8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

    $sheet->setCellValue('E' . ($rowIndex + 2), number_format($PriceTotal, 2));
    $sheet->setCellValue('E' . ($rowIndex + 3), number_format($row['TAX_AMT'], 2));
    $sheet->setCellValue('E' . ($rowIndex + 4), number_format($row['FEE_MISC'], 2));
    $sheet->setCellValue('E' . ($rowIndex + 5), number_format($row['TOT_AMT'], 2));
    $sheet->getStyle('E' . ($rowIndex + 5))->getFont()->setBold(true); 
    $sheet->setCellValue('E' . ($rowIndex + 6), number_format($row['TOT_PYMT'], 2)); 
    $sheet->setCellValue('E' . ($rowIndex + 7), number_format($row['TOT_REM'], 2));
    $sheet->getStyle('E' . ($rowIndex + 7))->getFont()->setBold(true); 

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Nota_' . $orderNumber . '.xlsx"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    $objWriter->save('php://output');
    exit();
?>
