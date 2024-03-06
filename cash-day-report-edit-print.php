<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/functions/dotmatrix.php";

$print             = strtoupper(trim(getParam('config', 'print')));

switch ($print) {
    case 'EPSON':
    case 'TM-U220': // Epson TM-U220
        // TO CECK OPEN CASH DROWER
        if ($isOpenCashDrawer) {
            $receipt = chr(27) . chr(112) . chr(48) . chr(25) . chr(250);
        }
        break;
    default: // SP712
        $receipt = chr(7);
        break;
}

$cashRegisterDir   = __DIR__ . DIRECTORY_SEPARATOR . "cash-register" . DIRECTORY_SEPARATOR;

$CshDayDte	= $_GET['CSH_DAY_DTE'];
$SNbr		= $_GET['S_NBR'];
$POSID		= $_GET['POS_ID'];

$query		= "SELECT   DATE(CSH_DAY_DTE) AS CSH_DAY_DTE
						,SUM(CHK_AMT) AS CHK_AMT
						,SUM(CSH_AMT) AS CSH_AMT
						,CSH_PRSN_NBR
						,PPC.NAME AS CSH_NAME
						,DATE(DEP_DTE) AS DEP_DTE
						,PPL.NAME
						,PLE.NAME AS DEP_NAME
				FROM RTL.CSH_DAY CAD
				LEFT OUTER JOIN CMP.PEOPLE PPL ON  PPL.PRSN_NBR =CAD.VRFD_NBR 
				LEFT OUTER JOIN CMP.PEOPLE PLE ON  PLE.PRSN_NBR=CAD.CRT_NBR
				LEFT OUTER JOIN CMP.PEOPLE PPC ON  PPC.PRSN_NBR=CAD.CSH_PRSN_NBR
				WHERE CSH_DAY_DTE='".$CshDayDte."' AND S_NBR=".$SNbr." AND POS_ID = ".$POSID."
				GROUP BY CAD.CSH_DAY_NBR";
				
$result		= mysql_query($query);
while ($row	= mysql_fetch_array($result)) {

$receipt.="            CHAMPION CAMPUS".chr(13).chr(10);
$receipt.="         Lt.2 The Jayan Building".chr(13).chr(10);
$receipt.="        Jl. Affandi (Gejayan) No.4 ".chr(13).chr(10).chr(13).chr(10);

$receipt.="                SETORAN".chr(13).chr(10);
$receipt.="----------------------------------------".chr(13).chr(10);
$receipt.=followSpace("Uang Kontan",20)."= "." Rp. ".leadSpace($row['CSH_AMT'],11).chr(13).chr(10);
$receipt.="----------------------------------------".chr(13).chr(10);
$receipt.=followSpace("Cek/Giro",20)."= "." Rp. ".leadSpace($row['CHK_AMT'],11).chr(13).chr(10);
$receipt.="----------------------------------------".chr(13).chr(10);
$receipt.=followSpace("Tanggal",20)."= ".leadSpace($row['CSH_DAY_DTE'],16).chr(13).chr(10);
$receipt.="----------------------------------------".chr(13).chr(10);
$receipt.=followSpace("Kasir",20)."= ".leadSpace($row['CSH_NAME'],16).chr(13).chr(10);
$receipt.="----------------------------------------".chr(13).chr(10).chr(13).chr(10);;

}

$string=$receipt;

echo "<pre style='font-size:8pt;letter-spacing:-1.25px;'>";
echo $string;
echo "</pre>";
	
$fh=fopen($cashRegisterDir. $CshDayDte.".txt", "w");
fwrite($fh, chr(15).$string.chr(18));
//fclose($fh);
?>