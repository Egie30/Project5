<?php
ini_set('max_execution_time', 300);
include "framework/database/connect.php";
include "framework/database/connect-cloud.php";

require_once "excel_reader/excel_reader2.php";

$namafile   = "presensi/04Statistic.xls";

$data       = new Spreadsheet_Excel_Reader($namafile);
$sheet_index= 2; //embaca sheet ke 3
$dataResult = $data->rowcount($sheet_index);
$time=array();
//$data->val(baris, kolom, index_sheet);
$periode    = $data->val(3,3,$sheet_index);
$periode    = substr($periode, 6, 4)."-".substr($periode, 3, 2);
echo "<table border='1px'>";
for ($i=5; $i <= $dataResult; $i++){ //mulai membaca baris ke 5
    if($i%2!=0){
        $prsnNbr = $data->val($i,3,$sheet_index);
    }
    $Nbr = substr($prsnNbr,1);
    if (substr($prsnNbr,0,1)=="1"){ //karyawan champion
        echo "<tr>";
        for ($j=1; $j <= 31; $j++){ //mulai membaca kolom ke 1
            $val=""; 
            $val = $data->val($i,$j,$sheet_index);
            if ($val!=""){
                if($i%2==0){
                    $time = explode("\n", trim($val));
                    $sift = count($time);
                    echo "<td>";
                    for ($k=0; $k<$sift; $k++){
                        $updTS      = $periode."-".$j." ".$time[$k].":00";
                        $query_cek  = "SELECT PRSN_NBR FROM CMP.ATND_CLOK WHERE PRSN_NBR='$Nbr' AND DATE(UPD_TS)='$periode-$j'";
                        $result_cek = mysql_query($query_cek,$local);
                        $row_cek    = mysql_num_rows($result_cek);
                        if ($row_cek==0){
                            $query  = "INSERT INTO $CMP.ATND_CLOK VALUES('$Nbr','$updTS',0)";
                            //mysql_query($query,$cloud);
                            $query  = str_replace($CMP,"CMP",$query);
                            //mysql_query($query,$local);
                            echo $row_cek.' '.$query.'<br/>';
                        }
                    }
                    echo "</td>";
                } else {
                    echo "<td>".$val."</td>";   
                }
            } else echo "<td>kosong</td>";
        }
        echo "</tr>";
    }
}
echo "</table>";
?>