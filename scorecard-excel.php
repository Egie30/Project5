<?php

require_once 'framework/database/connect.php';
require_once 'framework/functions/default.php';
require_once 'framework/functions/dotmatrix.php';
require_once 'framework/phpExcel/Classes/PHPExcel.php';
require_once 'framework/functions/crypt.php';


if  ($_SESSION['personNBR']=='')
{
       echo "<script>parent.parent.location='login.php';</script>";
       exit;
}

if ($_GET['FLTR_DATE']  == '')
{
      $_GET['FLTR_DATE']      =  date('Y-m-d');

      if ($_GET['FLTR_DATE']  == date('Y-m-01',strtotime($_GET['FLTR_DATE'])))
      {
       $_GET['FLTR_DATE']     =   date('Y-m-d',strtotime('-1 day', strtotime($_GET['FLTR_DATE'])));
      }
}

    $filter_date = $_GET['FLTR_DATE'];

    $Details     = json_decode(simple_crypt(file_get_contents('http://printing.champs.asia/scorecard-data.php?FLTR_DATE='.$filter_date),'d'));


$title = 'SCORECARD';

$reports = array(
    'title' => $title,
    'column' => array('A', 'B', 'C', 'D', 'E', 'F', 'G','H','I'),
    'columnDimension' => array(15,  25,  25,  35,  10,  15, 25, 25, 35),
    'titles' => array(
        'Mesin',
        'Periode',
        'Volume',
        'Revenue',
        ' ',
        'Mesin',
        'Periode',
        'Volume',
        'Revenue'
    ),
    'styles' => array(
        'A' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            )
        ),
        'B' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            )
        ),
        'C' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
            'numberformat' => array(
                'code' => '#'
            )
        ),
        'D' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            )
        ),
        'E' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
             'numberformat' => array(
               'code' => '#,##0'
            )
        ),
        'F' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            ),
            'numberformat' => array(
                'code' => '#,##0'
            )
        ),
        'G' => array(
           'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            ),
            'numberformat' => array(
               'code' => '#,##0'
            )
        ),
        'H' => array(
           'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
            'numberformat' => array(
               'code' => '#,##0'
            )
        ),
        'I' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
             'numberformat' => array(
               'code' => '#,##0'
            )
        ),
    ),
    'data' => array(),
    'total' => array(
        array(),
    )
);

    $query = " SELECT SCD.PRN_DIG_EQP, 
                              (CASE WHEN PRN_DIG_EQP='FLJ320P' THEN 'Outdoor'
                                    WHEN PRN_DIG_EQP='KMC6501' THEN 'A3+'
                                    WHEN PRN_DIG_EQP='RVS640' THEN 'Indoor'
                                    WHEN PRN_DIG_EQP='AJ1800F' THEN 'Direct Fabric'
                                    WHEN PRN_DIG_EQP='MVJ1624' THEN 'Heat Transfer'
                                    WHEN PRN_DIG_EQP='KMC8000' THEN 'R2S'
                                    WHEN PRN_DIG_EQP='KMC1085' THEN 'R2P'
                                    WHEN PRN_DIG_EQP='HPL375' THEN 'Latex'
                                    WHEN PRN_DIG_EQP='LABSVCS ' THEN 'Labor Service'
                                    ELSE 'Head Press'
                                END) AS PRN_DIG_EQP_DESC, 
                              VOL, 
                              REV 
                        FROM (
                            SELECT 'FLJ320P' AS PRN_DIG_EQP, SUM(FLJ320P) AS VOL,SUM(REV_FLJ320P) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
                            UNION ALL
                            SELECT 'FLJ320P' AS PRN_DIG_EQP, SUM(FLJ320P) AS VOL,SUM(REV_FLJ320P) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
                            UNION ALL
                            SELECT 'FLJ320P' AS PRN_DIG_EQP, SUM(FLJ320P) AS VOL,SUM(REV_FLJ320P) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
                            UNION ALL
                            SELECT 'FLJ320P' AS PRN_DIG_EQP, SUM(FLJ320P) AS VOL,SUM(REV_FLJ320P) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
                            UNION ALL
                            SELECT 'KMC6501' AS PRN_DIG_EQP, SUM(KMC6501) AS VOL,SUM(REV_KMC6501) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
                            UNION ALL
                            SELECT 'KMC6501' AS PRN_DIG_EQP, SUM(KMC6501) AS VOL,SUM(REV_KMC6501) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
                            UNION ALL
                            SELECT 'KMC6501' AS PRN_DIG_EQP, SUM(KMC6501) AS VOL,SUM(REV_KMC6501) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
                            UNION ALL
                            SELECT 'KMC6501' AS PRN_DIG_EQP, SUM(KMC6501) AS VOL,SUM(REV_KMC6501) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
                            UNION ALL
                            SELECT 'RVS640' AS PRN_DIG_EQP, SUM(RVS640) AS VOL,SUM(REV_RVS640) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
                            UNION ALL
                            SELECT 'RVS640' AS PRN_DIG_EQP, SUM(RVS640) AS VOL,SUM(REV_RVS640) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
                            UNION ALL
                            SELECT 'RVS640' AS PRN_DIG_EQP, SUM(RVS640) AS VOL,SUM(REV_RVS640) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
                            UNION ALL
                            SELECT 'RVS640' AS PRN_DIG_EQP, SUM(RVS640) AS VOL,SUM(REV_RVS640) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
                            UNION ALL
                            SELECT 'AJ1800F' AS PRN_DIG_EQP, SUM(AJ1800F) AS VOL,SUM(REV_AJ1800F) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
                            UNION ALL
                            SELECT 'AJ1800F' AS PRN_DIG_EQP, SUM(AJ1800F) AS VOL,SUM(REV_AJ1800F) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
                            UNION ALL
                            SELECT 'AJ1800F' AS PRN_DIG_EQP, SUM(AJ1800F) AS VOL,SUM(REV_AJ1800F) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
                            UNION ALL
                            SELECT 'AJ1800F' AS PRN_DIG_EQP, SUM(AJ1800F) AS VOL,SUM(REV_AJ1800F) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
                            UNION ALL
                            SELECT 'MVJ1624' AS PRN_DIG_EQP, SUM(MVJ1624) AS VOL,SUM(REV_MVJ1624) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
                            UNION ALL
                            SELECT 'MVJ1624' AS PRN_DIG_EQP, SUM(MVJ1624) AS VOL,SUM(REV_MVJ1624) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
                            UNION ALL
                            SELECT 'MVJ1624' AS PRN_DIG_EQP, SUM(MVJ1624) AS VOL,SUM(REV_MVJ1624) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
                            UNION ALL
                            SELECT 'MVJ1624' AS PRN_DIG_EQP, SUM(MVJ1624) AS VOL,SUM(REV_MVJ1624) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
                            UNION ALL
                            SELECT 'KMC8000' AS PRN_DIG_EQP, SUM(KMC8000) AS VOL,SUM(REV_KMC8000) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
                            UNION ALL
                            SELECT 'KMC8000' AS PRN_DIG_EQP, SUM(KMC8000) AS VOL,SUM(REV_KMC8000) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
                            UNION ALL
                            SELECT 'KMC8000' AS PRN_DIG_EQP, SUM(KMC8000) AS VOL,SUM(REV_KMC8000) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
                            UNION ALL
                            SELECT 'KMC8000' AS PRN_DIG_EQP, SUM(KMC8000) AS VOL,SUM(REV_KMC8000) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
                            UNION ALL
                            SELECT 'KMC1085' AS PRN_DIG_EQP, SUM(KMC1085) AS VOL,SUM(REV_KMC1085) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
                            UNION ALL
                            SELECT 'KMC1085' AS PRN_DIG_EQP, SUM(KMC1085) AS VOL,SUM(REV_KMC1085) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
                            UNION ALL
                            SELECT 'KMC1085' AS PRN_DIG_EQP, SUM(KMC1085) AS VOL,SUM(REV_KMC1085) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
                            UNION ALL
                            SELECT 'KMC1085' AS PRN_DIG_EQP, SUM(KMC1085) AS VOL,SUM(REV_KMC1085) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
                            UNION ALL
                            SELECT 'HPL375' AS PRN_DIG_EQP, SUM(HPL375) AS VOL,SUM(REV_HPL375) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
                            UNION ALL
                            SELECT 'HPL375' AS PRN_DIG_EQP, SUM(HPL375) AS VOL,SUM(REV_HPL375) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
                            UNION ALL
                            SELECT 'HPL375' AS PRN_DIG_EQP, SUM(HPL375) AS VOL,SUM(REV_HPL375) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
                            UNION ALL
                            SELECT 'HPL375' AS PRN_DIG_EQP, SUM(HPL375) AS VOL,SUM(REV_HPL375) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
                            UNION ALL
                            SELECT 'ATX67' AS PRN_DIG_EQP, SUM(ATX67) AS VOL,SUM(REV_ATX67) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
                            UNION ALL
                            SELECT 'ATX67' AS PRN_DIG_EQP, SUM(ATX67) AS VOL,SUM(REV_ATX67) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
                            UNION ALL
                            SELECT 'ATX67' AS PRN_DIG_EQP, SUM(ATX67) AS VOL,SUM(REV_ATX67) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
                            UNION ALL
                            SELECT 'ATX67' AS PRN_DIG_EQP, SUM(ATX67) AS VOL,SUM(REV_ATX67) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
                            UNION ALL
                            SELECT 'LABSVCS' AS PRN_DIG_EQP, SUM(LABSVCS) AS VOL,SUM(REV_LABSVCS) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH('".$filter_date."') AND YEAR(DTE)=YEAR('".$filter_date."')
                            UNION ALL
                            SELECT 'LABSVCS' AS PRN_DIG_EQP, SUM(LABSVCS) AS VOL,SUM(REV_LABSVCS) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 1 MONTH)))
                            UNION ALL
                            SELECT 'LABSVCS' AS PRN_DIG_EQP, SUM(LABSVCS) AS VOL,SUM(REV_LABSVCS) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 2 MONTH)))
                            UNION ALL
                            SELECT 'LABSVCS' AS PRN_DIG_EQP, SUM(LABSVCS) AS VOL,SUM(REV_LABSVCS) AS REV FROM CDW.PRN_DIG_DSH_BRD WHERE MONTH(DTE)= MONTH(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH))) AND YEAR(DTE)=YEAR(LAST_DAY(DATE_SUB('".$filter_date."', INTERVAL 12 MONTH)))
                            ) SCD";
                
                
                $resultDet = mysql_query($query);
                $Ket       = array('Bulan ini', 'Bulan Lalu', '2 Bulan Lalu', 'Bulan Sama Tahun Lalu');
                
                 $i=0;$j=0;

                while($rowDet=mysql_fetch_array($resultDet))
                {
                
                if ($i==0)
                {
                    $volOld='';
                    //$revOldPct1='';
                }

                if ($volOld!='')
                {
                    if ($rowDet['VOL']!=0)
                    {
                        $volPercen  = (($volOld-$rowDet['VOL'])/$rowDet['VOL'])*100;
                    }
                    else 
                    {
                        $volPercen  = 0;
                    }
                    
                    if ($Details->data[$j]->VOL!=0)
                    {
                        $volPcnCbng = (($volOldCbng-$Details->data[$j]->VOL)/$Details->data[$j]->VOL)*100;
                    }
                    else
                    {
                        $volPcnCbng = 0;
                    }

                    if($rowDet['REV']!=0)
                    {
                        $revPrc     = (($revOld-$rowDet['REV'])/$rowDet['REV'])*100;
                        $revPrcCbg  = (($revOld-$rowDet['REV'])/$rowDet['REV'])*100;
                    }
                    else
                    {
                        $revPrc    = 0;
                        $revPrcCbg = 0;
                    }

                    if ($Details->data[$j]->REV!=0)
                    {
                        $revPctCbng = (($revOldCbng-$Details->data[$j]->REV)/$Details->data[$j]->REV)*100; 
                    }
                    else 
                    {
                        $revPctCbng = 0;
                    }
                        
                    
                    if ($volPercen  == 0)  { $class           = '';$volPercen= 0;}
                    if ($volPcnCbng == 0)  { $classCbng       = '';$volPcnCbng='';}
                    if ($revPrcCbg  == 0)  { $classRevCbng    = '';$revPrcCbg='';}
                    if ($revPctCbng == 0)  { $classRevPctCbng = '';$revPctCbng='';}

                    if ($volPercen   != ''){$volPercen  = number_format($volPercen, 2, ",", ".").'%';}
                    if ($volPcnCbng  != ''){$volPcnCbng = number_format($volPcnCbng, 2, ",", ".").'%';}
                    if ($revPrcCbg   != ''){$revPrcCbg  = number_format($revPrcCbg, 2, ",", ".").'%';}
                    if ($revPctCbng  != ''){$revPctCbng = number_format($revPctCbng, 2, ",", ".").'%';}
                    
                }
                else
                {
                    $volPercen       = 0;
                    $volPcnCbng      = 0;
                    $revPrcCbg       = 0;
                    $revPctCbng      = 0;
                    $class           = 0;
                    $classCbng       = 0;
                    $classRevCbng    = 0;
                    $classRevPctCbng = 0;

                }
                //Hitung Revenue
                if ($rowDet['REV']!=0)
                {
                    $rev      = ($rowDet['REV']/($rowDet['REV']+$Details->data[$j]->REV))*100;
                }
                else
                {
                    $rev =0;
                }
                
                if ($Details->data[$j]->REV!=0)
                {
                    $revCbg   = ($Details->data[$j]->REV/($Details->data[$j]->REV+$rowDet['REV']))*100;
                }
                else
                {
                    $revCbg =0;
                }
                           
    
                $dataArray = array(
            $rowDet['PRN_DIG_EQP_DESC'],
            $Ket[$i],
            number_format($rowDet['VOL'], 2, ",", "."). " ( " .$volPercen." ) ",
            number_format($rowDet['REV'], 2, ",", "."). " - " .number_format($rev, 1, ",", ".")."% ( ".$revPrcCbg." ) ",
            " ",
            $rowDet['PRN_DIG_EQP_DESC'],
            $Ket[$i],
            number_format($Details->data[$j]->VOL, 0, ",", "."). " ( " .$volPcnCbng." ) ",
            number_format($Details->data[$j]->REV, 0, ",", "."). " - " .number_format($revCbg, 1, ",", ".")."% ( ".$revPctCbng." ) "
                   );

                    $reports['data'][] = $dataArray;


                if ($i == 0)
                {
                    $volOld     = $rowDet['VOL'];
                    $revOld     = $rowDet['REV'];
                    $volOldCbng = $Details->data[$j]->VOL;
                    $revOldCbng = $Details->data[$j]->REV;
                    
                }
                $prnDigEqp = $rowDet['PRN_DIG_EQP'];
                $i++;$j++;
                if ($i == 4){ $i=0; }

            } 

            
return $reports;


?>