<?php
include_once("/framework/database/connect.php");
include_once("/framework/database/connect-cloud-tes.php");
include_once("/framework/database/function_db.php");

//BCA Counter Exchange Rate
if(date('H:m', strtotime(NOW))<='10:00'){
function readHTML($url){
     $data = curl_init();
     curl_setopt($data, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($data, CURLOPT_URL, $url);
     $result = curl_exec($data);
     curl_close($data);
     return $result;
}
$BCA_URL =  readHTML('http://www.bca.co.id/en/biaya-limit/kurs_counter_bca/kurs_counter_bca_landing.jsp');
$explode1 = explode('<div style="float:left;margin-left:10px;padding: 10px;width:700px;padding-bottom:40px;min-height:220px;">', $BCA_URL);
$explode2 = explode('<b>Notes :</b>', $explode1[1]);
$explode3 = explode('</table>', $explode2[0]);
$curr1 = explode('</tr>', $explode3[0]);//row
if(count($explode3)>=3)
 {
 $conn = mysql_connect('localhost', 'root', '') or die(mysql_error());
 //Clear All data
 $query="TRUNCATE CDW.BCA_EXC_RATE;";
 mysql_query($query,$conn);
		
	for ($z=1; $z<=3; $z++)
	{
		$row = explode('</tr>', $explode3[$z]);//row
		for ($x=2; $x<=15; $x++)
		{
			$datetime=explode('<div align="center">', $row[0]);
			$col = explode('</td>', $row[$x]);//col
			$type=explode('</strong>', $row[0]);
			//Insert data
			$query="INSERT INTO CDW.BCA_EXC_RATE (RATE_CURRENCY,RATE_TS,RATE_SELL,RATE_BUY,RATE_TYPE)
					VALUES ('".trim(strip_tags($curr1[$x-1]))."','".strip_tags($datetime[1])."',".strip_tags($col[0]).",".strip_tags($col[1]).",'".trim(strip_tags($type[0]))."');";
			mysql_query($query,$conn);		
		}
	}
 }
}

//Print Digital Dashboard on Cloud
if(paramCloud()==1){	
$day_before = date( 'Y-m-d', strtotime( NOW. ' -365 day' ) );
$query="SELECT COUNT(*) NUMDSB FROM CDW.PRN_DIG_DSH_BRD WHERE DTE BETWEEN '$day_before' AND DATE(NOW());";
$result=mysql_query($query);
$row=mysql_fetch_array($result);
$NumDsb=$row['NUMDSB'];
$query="SELECT COUNT(*) NUMDSBC FROM $CMP.PRN_DIG_DSH_BRD WHERE DTE BETWEEN '$day_before' AND DATE(NOW());";
$result=mysql_query($query,$cloud);

//Compare cloud and local 
if($NumDsb!=$row['NUMDSBC']){
		//Clear data on cloud
		$query="DELETE FROM $CMP.PRN_DIG_DSH_BRD WHERE CO_NBR='$CoNbrDef' AND DTE BETWEEN '$day_before' AND DATE(NOW());";
		$result=mysql_query($query,$cloud);
		//Insert data on cloud
		$query="SELECT DTE,REV,TOT_REM,FLJ320P,KMC6501,RVS640,REV_FLJ320P,REV_KMC6501,REV_RVS640,AJ1800F,REV_AJ1800F,REV_RTL,
				REV_ALL,TOT_REM_ALL,FLJ320P_ALL,KMC6501_ALL,RVS640_ALL,REV_FLJ320P_ALL,REV_KMC6501_ALL,REV_RVS640_ALL,AJ1800F_ALL,REV_AJ1800F_ALL 
				FROM CDW.PRN_DIG_DSH_BRD WHERE DTE BETWEEN '$day_before' AND DATE(NOW());";
		$result=mysql_query($query);
		while($row=mysql_fetch_array($result)){
		$query="INSERT INTO $CMP.PRN_DIG_DSH_BRD (DTE,REV,TOT_REM,FLJ320P,KMC6501,RVS640,REV_FLJ320P,REV_KMC6501,REV_RVS640,AJ1800F,REV_AJ1800F,REV_RTL,
				REV_ALL,TOT_REM_ALL,FLJ320P_ALL,KMC6501_ALL,RVS640_ALL,REV_FLJ320P_ALL,REV_KMC6501_ALL,REV_RVS640_ALL,AJ1800F_ALL,REV_AJ1800F_ALL,CO_NBR) 
				VALUES ('".$row['DTE']."',".$row['REV'].",".$row['TOT_REM'].",".$row['FLJ320P'].",".$row['KMC6501'].",".$row['RVS640'].",".$row['REV_FLJ320P'].",
				".$row['REV_KMC6501'].",".$row['REV_RVS640'].",".$row['AJ1800F'].",".$row['REV_AJ1800F'].",".$row['REV_RTL'].",
				".$row['REV_ALL'].",".$row['TOT_REM_ALL'].",".$row['FLJ320P_ALL'].",".$row['KMC6501_ALL'].",".$row['RVS640_ALL'].",".$row['REV_FLJ320P_ALL'].",
				".$row['REV_KMC6501_ALL'].",".$row['REV_RVS640_ALL'].",".$row['AJ1800F_ALL'].",".$row['REV_AJ1800F_ALL'].",'$CoNbrDef');";	
		mysql_query($query,$cloud);
		//echo $query;
		}
}
//Bonus Plan Cloud
		//Clear data on cloud
		$query="DELETE FROM $CMP.PRN_DIG_BON_PLAN WHERE CO_NBR='$CoNbrDef';";
		//echo $query;
		$result=mysql_query($query,$cloud);
		//Insert data
		$query="SELECT FLEX_BASE_Q,DOC_BASE_Q,BASE_PCT,FLEX_INC_Q,DOC_INC_Q,FLEX_INC_PCT,DOC_INC_PCT,BEG_DT,END_DT FROM CMP.PRN_DIG_BON_PLAN;";
		//echo $query;
		$result=mysql_query($query);
		while($row=mysql_fetch_array($result)){
		$query="INSERT INTO $CMP.PRN_DIG_BON_PLAN (FLEX_BASE_Q,DOC_BASE_Q,BASE_PCT,FLEX_INC_Q,DOC_INC_Q,FLEX_INC_PCT,DOC_INC_PCT,BEG_DT,END_DT,CO_NBR) 
				VALUES ('".$row['FLEX_BASE_Q']."','".$row['DOC_BASE_Q']."','".$row['BASE_PCT']."','".$row['FLEX_INC_Q']."','".$row['DOC_INC_Q']."',
				'".$row['FLEX_INC_PCT']."','".$row['DOC_INC_PCT']."','".$row['BEG_DT']."','".$row['END_DT']."','$CoNbrDef');"	;	
		mysql_query($query,$cloud);
		//echo $query;
		}
}
?>