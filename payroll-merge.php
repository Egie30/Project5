<?php
	ini_set('max_execution_time', 300);
	include "framework/database/connect.php";
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";

	require_once "excel_reader/excel_reader2.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/functions/default.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>

<body>
<form enctype="multipart/form-data" action="#" method="post" style="width:400px">
	<p>
		<h2>Upload File Absensi</h2>	
		<div class='browse' onclick="document.getElementById('FIL_ATT').click();">Browse ...<input class="browse" id="FIL_ATT" name="FIL_ATT" type="file" style="border:0px;" tabindex=-1 /></div>
		<input class="process" type="submit" name="proses-mach" value="Proses"/>
	</p>
</form>
<?php 
if(isset($_POST['proses-mach'])){    
		$file 		= $_FILES['FIL_ATT']['name'];
		$file_loc 	= $_FILES['FIL_ATT']['tmp_name'];
		$file_size 	= $_FILES['FIL_ATT']['size'];
		$file_type 	= $_FILES['FIL_ATT']['type'];
		$folder		="presensi/";
		 
		move_uploaded_file($file_loc,$folder.$file);

		//Proses mach atnd
		$namafile   = $folder.$file;
		$data       = new Spreadsheet_Excel_Reader($namafile);
		$sheet_index= 2; //embaca sheet ke 3
		$dataResult = $data->rowcount($sheet_index);
		$time 		= array();
		$date_mach 	= array();
		//$data->val(baris, kolom, index_sheet);
		$periode    = $data->val(3,3,$sheet_index);
		$periode    = substr($periode, 6, 4)."-".substr($periode, 3, 2);
?>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th>No</th>
				<th>Nama</th>
				<th>Tanggal</th>
				<th>Jam</th>
			</tr>
		</thead>
		<tbody>
<?php
		for ($i=5; $i <= $dataResult; $i++){ //mulai membaca baris ke 5
		    if($i%2!=0){
		        $prsnNbr = $data->val($i,3,$sheet_index);
		        $name 	 = $data->val($i,11,$sheet_index);
		    }
		    $Nbr = substr($prsnNbr,1);
		    if (substr($prsnNbr,0,1)=="1"){ //karyawan champion
		        //echo "<tr>";
		        for ($j=1; $j <= 31; $j++){ //mulai membaca kolom ke 1
		            $val=""; 
		            $val = $data->val($i,$j,$sheet_index);
		            
		            if ($val!=""){
		                if($i%2==0){
		                    $time = explode("\n", trim($val));
		                    $sift = count($time);

		                    for ($k=0; $k<$sift; $k++){
		                    	if ($j<10) $date = $periode."-0".$j; else $date = $periode."-".$j;
		                    	$crtTS      = $periode."-".$j." ".$time[$k].":00";
		                    	$crtTS_where= date('Y-m-d H:i' , strtotime($crtTS));
		                    	//cek apakah sudah ada di atnd_clok atau belum?
		                        $query_cek  = "SELECT PRSN_NBR FROM PAY.ATND_CLOK WHERE PRSN_NBR='$Nbr' AND LEFT(CRT_TS,16)='$crtTS_where'";
								//echo $query_cek.'<br/>';
		                        $result_cek = mysql_query($query_cek,$local);
		                        $jum_cek    = mysql_num_rows($result_cek);
		                        if ($jum_cek==0){
		                            $query  = "INSERT INTO $PAY.ATND_CLOK VALUES('$Nbr','$crtTS',CURRENT_TIMESTAMP,0,0)";
									//echo $query.'<br/>';
		                            mysql_query($query,$cloud);
		                            $query  = str_replace($PAY,"PAY",$query);
		                            mysql_query($query,$local);
		                            //echo $query.'<br/>';
		                            array_push($date_mach, $date);

		                            echo "<tr $alt>";
			                    	echo "<td class='std'>$Nbr</td>";
			                    	echo "<td class='std'>$name</td>";
			                    	echo "<td class='std'>$date</td>";
			                    	echo "<td class='std'>$time[$k]</td>";
			                    	echo "</tr>";
		                        }
		                    }
		                    
		                } 
		            } 
		        }
		    } 
		} 
		unlink("presensi\\".$file);
		if (count($date_mach)==0){ echo "<tr $alt><td colspan='4' class='std' align='center'>Tidak ada data yang diproses</td></tr>"; }
		$date_mach = array_unique($date_mach); //tgl mana aja yg mau di mach
		//Proses mach clok berdasarkan tanggal
		foreach ($date_mach as $key => $value) {
			$query_atnd		= "SELECT * FROM PAY.ATND_CLOK 
								WHERE DATE(CRT_TS) = '$value'
								ORDER BY PRSN_NBR, CRT_TS";
			$result_atnd	= mysql_query($query_atnd,$local);

			while($row_atnd = mysql_fetch_array($result_atnd)) {	
				$personNbr	= $row_atnd['PRSN_NBR'];
				$scanDate	= $row_atnd['CRT_TS']; //jam absennya
						
		            $sql = "SELECT * FROM PAY.MACH_CLOK WHERE PRSN_NBR = " . $personNbr . " AND DATE(CLOK_IN_TS) = '" . date('Y-m-d', strtotime($scanDate)) . "' AND CLOK_OT_TS IS NULL";
		            //echo "Mengecek ke mach clok --> ".$sql."<br/>";
		            $rs = mysql_query($sql,$local);
					
					$num_rows = mysql_num_rows($rs);
		            $row = mysql_fetch_array($rs);

		            if ($num_rows == 0) {
		            	//filter disini masih bingung...
		            	$query_filter = "SELECT * FROM PAY.MACH_CLOK WHERE PRSN_NBR = " . $personNbr . " AND DATE(CLOK_IN_TS) = '" . date('Y-m-d', strtotime($scanDate)) . "' AND CLOK_OT_TS IS NOT NULL";
		            	$result_filter= mysql_query($query_filter);
		            	$num_filter	  = mysql_num_rows($result_filter);
		            	$row_filter	  = mysql_fetch_array($result_filter);
		            	if ($num_filter == 0) {
		            		$query = "INSERT INTO PAY.MACH_CLOK(PRSN_NBR, CLOK_IN_TS, UPD_TS) VALUES (" . $personNbr . ",'" . $scanDate . "',CURRENT_TIMESTAMP)";
		                	//echo "Input jika belum ada --> ".$query."<br/>";
		                	mysql_query($query,$local);
		            	}
	
		            } else {
						$query_diff = "SELECT HOUR(TIMEDIFF('" . date('Y-m-d H:i:s', strtotime($scanDate)) . "','" . $row['CLOK_IN_TS'] . "')) AS diff";
		                $result_diff 	= mysql_query($query_diff,$local);
		                $row_diff	= mysql_fetch_array($result_diff);
		                //echo "Ngecek jarak waktu --> ".$query_diff."--".$row_diff['diff']."--<br/>";
						if ($row_diff['diff'] >= 1) {
		                    $query = "UPDATE PAY.MACH_CLOK SET CLOK_OT_TS = '" . $scanDate . "', UPD_TS = CURRENT_TIMESTAMP WHERE CLOK_NBR = " . $row['CLOK_NBR'];
		                    mysql_query($query,$local);
							//echo "Update jika jaraknya >=1 --> ".$query."<br />";
		                } 
						
		            }
			}
		}
}
?>
		</tbody>
	</table>
</div>

<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>
</body>
</html>