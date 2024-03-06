<?php
include "framework/database/connect.php";
include "framework/database/connect-cloud.php";
include "framework/functions/default.php";

$typId 		= $_GET['TYP_ID'];
$timeTsTo 	= $_GET['TIME_TS_TO'];
$timeTsFm 	= $_GET['TIME_TS_FM'];
$timeTo 	= $_GET['TIME_TO'];
$timeFm 	= $_GET['TIME_FM'];
$prsnNbr 	= $_GET['PRSN_NBR'];

if ($typId  == 'UP'){
	//Mencari data absensi yang dituju
	$queryTo = "SELECT
					CLOK_NBR, CLOK_IN_TS,CLOK_OT_TS,HOUR(CLOK_IN_TS) AS HR_IN
			  FROM  PAY.MACH_CLOK
			  WHERE PRSN_NBR=".$prsnNbr."
				AND DATE(CLOK_IN_TS)='".$timeTsTo."'
				AND DATE_FORMAT(CLOK_IN_TS,'%H:%i')='".$timeTo."'
			  ORDER BY CLOK_IN_TS DESC
			  LIMIT 1";
	$resultTo= mysql_query($queryTo);	
	$rowTo   = mysql_fetch_array($resultTo);
	//echo $queryTo."<br/>";
	
	//Mencari data yang akan dipidah.
	$queryFm = "SELECT
					CLOK_NBR, CLOK_IN_TS,CLOK_OT_TS,HOUR(CLOK_IN_TS) AS HR_IN
			  FROM  PAY.MACH_CLOK
			  WHERE PRSN_NBR=".$prsnNbr."
				AND DATE(CLOK_IN_TS)='".$timeTsFm."'
				AND DATE_FORMAT(CLOK_IN_TS,'%H:%i')='".$timeFm."'
			  ORDER BY CLOK_IN_TS DESC
			  LIMIT 1";
	$resultFm= mysql_query($queryFm);
	$rowFm 	 = mysql_fetch_array($resultFm);
	//echo $queryFm."<br/>";
	
	//Merubah absensi keluar yang dituju
	$query   = "UPDATE PAY.MACH_CLOK SET 
					CLOK_OT_TS='".$rowFm['CLOK_IN_TS']."'
				WHERE CLOK_NBR=".$rowTo['CLOK_NBR'];
	$result  = mysql_query($query);
	//echo $query."<br/>";
	$query1=$query;
	//Merubah data yang dipindah 
	if ($rowFm['CLOK_OT_TS']==''){$clokOtTs = "NULL";}else{$clokOtTs="'".$rowFm['CLOK_OT_TS']."'";}
	
	$query 	= "UPDATE PAY.MACH_CLOK SET 
					CLOK_IN_TS=".$clokOtTs.",
					CLOK_OT_TS= NULL
			   WHERE CLOK_NBR=".$rowFm['CLOK_NBR'];
	$result = mysql_query($query);
	//echo $query;
	
	//Mengambil data yang terbaru
	$querys = "SELECT 
					 CLOK_NBR, CLOK_IN_TS,CLOK_OT_TS,HOUR(CLOK_IN_TS) AS HR_IN
				FROM PAY.MACH_CLOK 
				WHERE CLOK_NBR=".$rowTo['CLOK_NBR'];
	$res 	= mysql_query($querys);
	$row 	= mysql_fetch_array($res);

	//Mengubah data pada ATND CLOK
	$query  = "UPDATE $PAY.ATND_CLOK SET ATND_F=2, UPD_TS=CURRENT_TIMESTAMP WHERE PRSN_NBR=".$prsnNbr." AND CRT_TS IN ('".$row['CLOK_OT_TS']."')";
	//$result = mysql_query($query);
	$result=mysql_query($query,$cloud);
	$query=str_replace($PAY,"PAY",$query);
	$result=mysql_query($query,$local);
	
	$query = "SELECT 
					CLOK_NBR, 
					DATE(CLOK_IN_TS) AS DATE_TS, 
					SUM(ROUND(TIME_TO_SEC(TIMEDIFF(CLOK_OT_TS,CLOK_IN_TS))/3600,1))AS DIFF 
				FROM PAY.MACH_CLOK 
				WHERE 
					DATE(CLOK_IN_TS) IN ('".date("Y-m-d",strtotime($row['CLOK_IN_TS']))."','".date("Y-m-d",strtotime($rowFm['CLOK_IN_TS']))."') 
					AND PRSN_NBR=".$prsnNbr." 
				GROUP BY DATE(CLOK_IN_TS)";
	//ECHO $query;
	$result= mysql_query($query);
	while ($rowD = mysql_fetch_array($result)){
		if (date('Y-m-d',strtotime($row['CLOK_IN_TS'])) == date("Y-m-d",strtotime($rowFm['CLOK_IN_TS']))){
			if (date('Y-m-d',strtotime($row['CLOK_IN_TS']))==$rowD['DATE_TS']){
				$DiffTo = $rowD['DIFF'];
			}	
		}else{
			if (date('Y-m-d',strtotime($row['CLOK_IN_TS']))==$rowD['DATE_TS']){
				$DiffTo = $rowD['DIFF'];
				//ECHO $rowD['DIFF'];
			}

			if (date("Y-m-d",strtotime($rowFm['CLOK_IN_TS']))==$rowD['DATE_TS']){
				if ($rowD['DIFF']==''){$DiffFm='0.0';}else{$DiffFm = $rowD['DIFF'];}
			}
		}
	}

	$queryTot = "SELECT 
					CLOK_NBR, 
					DATE(CLOK_IN_TS) AS DATE_TS, 
					SUM(ROUND(TIME_TO_SEC(TIMEDIFF(CLOK_OT_TS,CLOK_IN_TS))/3600,1))AS DIFF 
				FROM PAY.MACH_CLOK 
				WHERE 
					DATE(CLOK_IN_TS) BETWEEN (SELECT MAX(PAYR.PYMT_DTE) AS MAX_PYMT_DTE FROM PAY.PAYROLL PAYR WHERE PAYR.PRSN_NBR=".$prsnNbr." AND DEL_NBR=0) AND (CURDATE() - INTERVAL 1 DAY)
					AND PRSN_NBR=".$prsnNbr;

	$resultTot= mysql_query($queryTot);
	$rowTot = mysql_fetch_array($resultTot);
	$totAllAbsensi = $rowTot['DIFF'];
	$totAllDayAbsensi = number_format($totAllAbsensi/8);

	//echo $query;
	if ($row['CLOK_IN_TS']==''){$row['CLOK_IN_TS']='';}else{$row['CLOK_IN_TS']= date("H:i",strtotime($row['CLOK_IN_TS']));}
	if ($row['CLOK_OT_TS']==''){$row['CLOK_OT_TS']='';}else{$row['CLOK_OT_TS']= date("H:i",strtotime($row['CLOK_OT_TS']));}
	if ($rowFm['CLOK_OT_TS']==''){$rowFm['CLOK_OT_TS']='';}else{$rowFm['CLOK_OT_TS']=date("H:i",strtotime($rowFm['CLOK_OT_TS']));}

	//hitung waktu kerja\
	$data=array(
				"CLOK_IN_TS_TO"=>$row['CLOK_IN_TS'],
				"CLOK_OT_TS_TO"=>$row['CLOK_OT_TS'],
				"CLOK_IN_TS_FM"=>$rowFm['CLOK_OT_TS'],
				"DIFF_TO"=>$DiffTo,
				"DIFF_FM"=>$DiffFm,
				"DIFF_TOT"=>$totAllAbsensi,
				"DIFF_TOT_DAY"=>$totAllDayAbsensi
			);
	
	echo json_encode($data);
	//$data=array("oke");
	//echo json_encode($data);
}else if ($typId=='DOWN'){
	$query = "SELECT 
					CLOK_NBR,
					CLOK_IN_TS,
					CLOK_OT_TS
				FROM PAY.MACH_CLOK 
				WHERE 
					DATE(CLOK_IN_TS)='".$timeTsFm."'
					AND DATE_FORMAT(CLOK_OT_TS,'%H:%i')='".$timeFm."'
					AND PRSN_NBR=".$prsnNbr;
	$result= mysql_query($query);
	$row   = mysql_fetch_array($result);

	if (date('Y-m-d',strtotime($row['CLOK_OT_TS']))==date('Y-m-d',strtotime($timeTsTo))){
		$queryNbr  = "SELECT MAX(CLOK_NBR)+1 AS NEW_NBR FROM PAY.MACH_CLOK";
		$resultNbr = mysql_query($queryNbr);
		$rowNbr    = mysql_fetch_array($resultNbr);	
		$clokNbr   =  $rowNbr['NEW_NBR'];

		$query = "UPDATE PAY.MACH_CLOK SET CLOK_OT_TS = NULL WHERE CLOK_NBR=".$row['CLOK_NBR'];
		$result= mysql_query($query); 

		$query = "INSERT PAY.MACH_CLOK(CLOK_NBR, PRSN_NBR, CLOK_IN_TS) 
					VALUES (".$clokNbr.",".$prsnNbr.",'".$row['CLOK_OT_TS']."')";
		$result=mysql_query($query);

		//Mengubah data pada ATND CLOK
		$query  = "UPDATE $PAY.ATND_CLOK SET ATND_F=1,UPD_TS=CURRENT_TIMESTAMP WHERE PRSN_NBR=".$prsnNbr." AND CRT_TS IN ('".$row['CLOK_OT_TS']."')";
		//$result = mysql_query($query);
		$result=mysql_query($query,$cloud);
		$query=str_replace($PAY,"PAY",$query);
		$result=mysql_query($query,$local);

		$query = "SELECT 
					CLOK_NBR, 
					DATE(CLOK_IN_TS) AS DATE_TS, 
					SUM(ROUND(TIME_TO_SEC(TIMEDIFF(CLOK_OT_TS,CLOK_IN_TS))/3600,1))AS DIFF 
				FROM PAY.MACH_CLOK 
				WHERE 
					DATE(CLOK_IN_TS) IN ('".date("Y-m-d",strtotime($timeTsTo))."','".date("Y-m-d",strtotime($timeTsFm))."')
					AND PRSN_NBR=".$prsnNbr." 
					GROUP BY DATE(CLOK_IN_TS)";
		$result= mysql_query($query);
		while ($rowD = mysql_fetch_array($result)){
			if (date('Y-m-d',strtotime($timeTsTo)) == date("Y-m-d",strtotime($timeTsFm))){
				if (date('Y-m-d',strtotime($timeTsTo))==$rowD['DATE_TS']){
					$DiffTo = $rowD['DIFF'];
				}	
			}else{
				if (date('Y-m-d',strtotime($timeTsTo))==$rowD['DATE_TS']){
					if ($rowD['DIFF']==''){$DiffTo='0.0';}else{$DiffTo = $rowD['DIFF'];}
				}

				if (date("Y-m-d",strtotime($timeTsFm))==$rowD['DATE_TS']){
					if ($rowD['DIFF']==''){$DiffFm='0.0';}else{$DiffFm = $rowD['DIFF'];}
				}
			}
		}

		//Mencari total absensi
		$queryTot = "SELECT 
						CLOK_NBR, 
						DATE(CLOK_IN_TS) AS DATE_TS, 
						SUM(ROUND(TIME_TO_SEC(TIMEDIFF(CLOK_OT_TS,CLOK_IN_TS))/3600,1))AS DIFF 
					FROM PAY.MACH_CLOK 
					WHERE 
						DATE(CLOK_IN_TS) BETWEEN (SELECT MAX(PAYR.PYMT_DTE) AS MAX_PYMT_DTE FROM PAY.PAYROLL PAYR WHERE PAYR.PRSN_NBR=".$prsnNbr." AND DEL_NBR=0) AND (CURDATE() - INTERVAL 1 DAY)
						AND PRSN_NBR=".$prsnNbr;

		$resultTot= mysql_query($queryTot);
		$rowTot = mysql_fetch_array($resultTot);
		$totAllAbsensi = $rowTot['DIFF'];
		$totAllDayAbsensi = number_format($totAllAbsensi/8);

		if ($row['CLOK_OT_TS']==''){$clokInTsTo='';}else{$clokInTsTo=date('Y-m-d',strtotime($row['CLOK_OT_TS']));}
		if ($row['CLOK_OT_TS']==''){$clokTimeTo='';}else{$clokTimeTo=date('H:i',strtotime($row['CLOK_OT_TS']));}
		
		$data 	= array(
						"CLOK_IN_TS_TO"=>$clokInTsTo,
						"TIME_TO"=>$clokTimeTo,
						"DIFF_TO"=>$DiffTo,
						"DIFF_FM"=>$DiffFm,
						"DIFF_TOT"=>$totAllAbsensi,
						"DIFF_TOT_DAY"=>$totAllDayAbsensi
						);

		echo json_encode($data);
	}
}
?>