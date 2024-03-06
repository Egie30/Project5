<?php       
	include "framework/database/connect-cloud.php";

	$query_atnd		= "SELECT * FROM PAY.ATND_CLOK 
					WHERE UPD_TS > (SELECT COALESCE(MAX(UPD_TS),'0000-00-00') AS UPD_TS FROM PAY.MACH_CLOK)
					ORDER BY PRSN_NBR, CRT_TS";	
	// echo $query_atnd."<br/>";
	$result_atnd	= mysql_query($query_atnd,$local);
	while($row_atnd = mysql_fetch_array($result_atnd)) {

		$personNbr	= $row_atnd['PRSN_NBR'];
		$scanDate	= $row_atnd['CRT_TS']; //UPD_TS diubah menjadi CRT_TS
		
		$query 		= "SELECT * FROM 
							PAY.EXCPTN_ETRY 
						WHERE PRSN_NBR = " . $personNbr . " 
							AND DATE(EXCPTN_ETRY_TS) = '" . date('Y-m-d', strtotime($scanDate)) . "'";
		$result     = mysql_query($query,$local);

		if (mysql_num_rows($result)>0){
			$queryD = "DELETE FROM PAY.MACH_CLOK WHERE DATE(CLOK_IN_TS)='". date('Y-m-d', strtotime($scanDate))."' AND PRSN_NBR=".$personNbr;
			$resultD= mysql_query($queryD,$local);
			
			$queryA = "SELECT * FROM PAY.ATND_CLOK 
						WHERE PRSN_NBR=".$personNbr."
						AND DATE(CRT_TS)='".date('Y-m-d', strtotime($scanDate))."' AND DEL_NBR=0 ORDER BY CRT_TS ASC";
			$resultA= mysql_query($queryA,$local);
			
			echo $queryA."<br />";
			
			while ($rows= mysql_fetch_array($resultA)){
				$queryDel = "SELECT * FROM PAY.MACH_CLOK WHERE PRSN_NBR = " . $rows['PRSN_NBR'] . " AND DATE(CLOK_IN_TS) = '" . date('Y-m-d', strtotime($rows['CRT_TS'])) . "' AND CLOK_OT_TS IS NULL";
				$rsDel = mysql_query($queryDel,$local);
			
				$num_rowsDel = mysql_num_rows($rsDel);
				$rowDel = mysql_fetch_array($rsDel);

	            if ($num_rowsDel == 0) {
	                $queryIn = "INSERT INTO PAY.MACH_CLOK(PRSN_NBR, CLOK_IN_TS, UPD_TS) VALUES (" . $rows['PRSN_NBR'] . ",'" . $rows['CRT_TS'] . "',CURRENT_TIMESTAMP)";
	                mysql_query($queryIn,$local);
					
					echo $queryIn."<br /><br />";
					
	            } else {
	                
					$query_diffUp = "SELECT HOUR(TIMEDIFF('" . date('Y-m-d H:i:s', strtotime($rows['CRT_TS'])) . "','" . $rowDel['CLOK_IN_TS'] . "')) AS diff";
					echo $query_diffUp."<br /><br />";
	                $result_diffUp 	= mysql_query($query_diffUp,$local);
	                $row_diffUp		= mysql_fetch_array($result_diffUp);

					if ($row_diffUp['diff'] >= 1) {
	                    $query = "UPDATE PAY.MACH_CLOK SET CLOK_OT_TS = '" . $rows['CRT_TS'] . "', UPD_TS = CURRENT_TIMESTAMP WHERE CLOK_NBR = " . $rowDel['CLOK_NBR'];
	                    mysql_query($query,$local);
						
						echo $query."<br />";
	                } 
					
            	}
			}
		}else{
            $sql = "SELECT * FROM PAY.MACH_CLOK WHERE PRSN_NBR = " . $personNbr . " AND DATE(CLOK_IN_TS) = '" . date('Y-m-d', strtotime($scanDate)) . "' AND CLOK_OT_TS IS NULL";
            $rs = mysql_query($sql,$local);
			
			$num_rows = mysql_num_rows($rs);
            $row = mysql_fetch_array($rs);

            if ($num_rows == 0) { echo $row_atnd['ATND_F']."<br/>";
            	if ($row_atnd['ATND_F']==2){
            		$dateBefore= date('Y-m-d', strtotime('-1 DAY',strtotime($scanDate)));
					$queryB = "SELECT * FROM PAY.MACH_CLOK WHERE PRSN_NBR = " . $personNbr . " AND DATE(CLOK_IN_TS) = '" . date('Y-m-d', strtotime($dateBefore)) . "' AND CLOK_OT_TS IS NULL";
					$resultB= mysql_query($queryB,$local);
					echo $queryB."<br />";
					if (mysql_num_rows($resultB)>0){
						$rowUp= mysql_fetch_array($resultB);
						//print_r($rowUp);
						updateBefMachClok($rowUp['CLOK_NBR'],$scanDate,$personNbr);
						updateMachClok($rowUp['CLOK_NBR'],$scanDate,$personNbr);
					}else{
						$query= "UPDATE PAY.MACH_CLOK SET UPD_TS=CURRENT_TIMESTAMP WHERE PRSN_NBR=".$personNbr." AND CLOK_OT_TS ='".$scanDate."'";
						mysql_query($query,$local);
					}
				}else if ($row_atnd['ATND_F']==1) {
				
					$query= "SELECT * FROM PAY.MACH_CLOK WHERE CLOK_OT_TS='".$scanDate."' AND PRSN_NBR=".$personNbr;
					$result= mysql_query($query,$local);
					$rowO= mysql_fetch_array($result);

					if (mysql_num_rows($result)>0){

						$query = "INSERT INTO PAY.MACH_CLOK(PRSN_NBR,CLOK_IN_TS,UPD_TS) VALUES (".$personNbr.",'".$scanDate."',CURRENT_TIMESTAMP)";
							mysql_query($query,$local);
							echo $query."<br />";

						$query= "UPDATE PAY.MACH_CLOK SET CLOK_OT_TS=NULL WHERE PRSN_NBR=".$personNbr." AND CLOK_NBR=".$rowO['CLOK_NBR'];
						mysql_query($query,$local);
					}

				}else{
					$query = "INSERT INTO PAY.MACH_CLOK(PRSN_NBR, CLOK_IN_TS, UPD_TS) VALUES (" . $personNbr . ",'" . $scanDate . "',CURRENT_TIMESTAMP)";
	                mysql_query($query,$local);
					
					echo $query."<br />";
				}
            } else { echo $row_atnd['ATND_F']."<br/>";
                if ($row_atnd['ATND_F']==2){
                	if (strtotime($scanDate)<=strtotime($row['CLOK_IN_TS'])){
                		$dateBefore= date('Y-m-d', strtotime('-1 DAY',strtotime($scanDate)));
                	}else{
                		$dateBefore=$scanDate;
                	}

                	$query = "SELECT * FROM PAY.MACH_CLOK WHERE PRSN_NBR = " . $personNbr . " AND DATE(CLOK_IN_TS) = '" . date('Y-m-d', strtotime($dateBefore)) . "' AND CLOK_OT_TS IS NULL";
					$result= mysql_query($query,$local);
					//echo $query;
					if (mysql_num_rows($result)>0){
						$rowUp= mysql_fetch_array($result);
						//print_r($rowUp);
						updateBefMachClok($rowUp['CLOK_NBR'],$scanDate,$personNbr);
						updateMachClok($rowUp['CLOK_NBR'],$scanDate,$personNbr);
					}else{
						$query= "UPDATE PAY.MACH_CLOK SET UPD_TS=CURRENT_TIMESTAMP WHERE PRSN_NBR=".$personNbr." AND CLOK_OT_TS ='".$scanDate."'";
						mysql_query($query,$local);
					}
            		
				}else if ($row_atnd['ATND_F']==1) {
					
					
					$query= "SELECT * FROM PAY.MACH_CLOK WHERE CLOK_OT_TS='".$scanDate."' AND PRSN_NBR=".$personNbr;
					$result= mysql_query($query,$local);
					$rowO= mysql_fetch_array($result);
					echo $query."<br />";

					if (mysql_num_rows($result)>0){
						
					
							$query = "INSERT INTO PAY.MACH_CLOK(PRSN_NBR,CLOK_IN_TS,UPD_TS) VALUES (".$personNbr.",'".$scanDate."',CURRENT_TIMESTAMP)";
							mysql_query($query,$local);
							echo $query."<br />";
						

							$query= "UPDATE PAY.MACH_CLOK SET CLOK_OT_TS=NULL WHERE PRSN_NBR=".$personNbr." AND CLOK_NBR=".$rowO['CLOK_NBR'];
							mysql_query($query,$local);
							echo $query."<br />";
					}

				}else{
					$query_diff = "SELECT HOUR(TIMEDIFF('" . date('Y-m-d H:i:s', strtotime($scanDate)) . "','" . $row['CLOK_IN_TS'] . "')) AS diff";
				
	                $result_diff 	= mysql_query($query_diff,$local);
	                $row_diff		= mysql_fetch_array($result_diff);

					if ($row_diff['diff'] >= 1) {
	                    $query = "UPDATE PAY.MACH_CLOK SET CLOK_OT_TS = '" . $scanDate . "', UPD_TS = CURRENT_TIMESTAMP WHERE CLOK_NBR = " . $row['CLOK_NBR'];
	                    mysql_query($query,$local);
						
						echo $query."<br />";
	                } 
				}

				
			}
		}
	}
	
	
	function updateBefMachClok($CLOK_NBR,$scanDate,$personNbr){
		$query = "UPDATE PAY.MACH_CLOK SET CLOK_OT_TS='".$scanDate."', UPD_TS = CURRENT_TIMESTAMP WHERE CLOK_NBR=".$CLOK_NBR;
		$result=mysql_query($query,$local);
		echo "<br />1= ".$query."<br />";
	}

	function updateMachClok($CLOK_NBR,$scanDate,$personNbr){
		$query = "SELECT * FROM PAY.MACH_CLOK WHERE PRSN_NBR=".$personNbr." AND CLOK_IN_TS='".$scanDate."'";
		$result= mysql_query($query,$local);
		$rowD  = mysql_fetch_array($result);
		echo "<br />2= ".$query."<br />";

		if ($rowD['CLOK_OT_TS']==''){$clokOtTs="NULL";}else{$clokOtTs="'".$rowD['CLOK_OT_TS']."'";}

		$query = "UPDATE PAY.MACH_CLOK SET CLOK_IN_TS=".$clokOtTs.", CLOK_OT_TS=NULL, UPD_TS = CURRENT_TIMESTAMP WHERE CLOK_NBR=".$rowD['CLOK_NBR']." AND PRSN_NBR=".$personNbr;
		$result= mysql_query($query,$local);
		echo "<br />3= ".$query."<br />";
	}
	
	//======================= Membuat absensi setengah hari ====================================
	/*
	//Hari yang akan terkena  setengah hari
	$dateTo= date('Y-m-d', strtotime('- 2 day'));
	
	$query = "SELECT * FROM CMP.MACH_CLOK MAC 
				LEFT OUTER JOIN CMP.PEOPLE PPL ON MAC.PRSN_NBR=PPL.PRSN_NBR
				WHERE DATE(CLOK_IN_TS)='".$dateTo."' AND CLOK_OT_TS IS NULL AND CO_NBR IN (SELECT CO_NBR_CMPST FROM NST.PARAM_COMPANY WHERE CO_NBR IN (1002,997,1099,889)) ";
	$result= mysql_query($query,$local);

	
	while ($rowG=mysql_fetch_array($result)) {
		$timeOut = date('Y-m-d H:i:s', strtotime($rowG['CLOK_IN_TS'].'+4 hour'));

		$query   = "INSERT INTO $CMP.ATND_CLOK (PRSN_NBR,CRT_TS,UPD_TS) VALUES (
						".$rowG['PRSN_NBR'].",'".$timeOut."',CURRENT_TIMESTAMP
					)";

		$result=mysql_query($query,$cloud);
		$query=str_replace($CMP,"CMP",$query);
		$result=mysql_query($query,$local);

		echo $query;
	}

	$query_atnd		= "SELECT * FROM CMP.ATND_CLOK 
					WHERE UPD_TS > (SELECT COALESCE(MAX(UPD_TS),'0000-00-00') AS UPD_TS FROM CMP.MACH_CLOK)
					ORDER BY PRSN_NBR, CRT_TS";	
	
	$result_atnd	= mysql_query($query_atnd,$local);
	while($row_atnd = mysql_fetch_array($result_atnd)) {
		$personNbr	= $row_atnd['PRSN_NBR'];
		$scanDate	= $row_atnd['CRT_TS']; //UPD_TS diubah menjadi CRT_TS

		$sql = "SELECT * FROM MACH_CLOK WHERE PRSN_NBR = " . $personNbr . " AND DATE(CLOK_IN_TS) = '" . date('Y-m-d', strtotime($scanDate)) . "' AND CLOK_OT_TS IS NULL";
		$rs = mysql_query($sql,$local);
			
		$num_rows = mysql_num_rows($rs);
        $row = mysql_fetch_array($rs);

        if ($num_rows!=0){
        	$query_diff = "SELECT HOUR(TIMEDIFF('" . date('Y-m-d H:i:s', strtotime($scanDate)) . "','" . $row['CLOK_IN_TS'] . "')) AS diff";
        	$result_diff 	= mysql_query($query_diff,$local);
        	$row_diff		= mysql_fetch_array($result_diff);

			if ($row_diff['diff'] >= 1) {
                $query = "UPDATE MACH_CLOK SET CLOK_OT_TS = '" . $scanDate . "', UPD_TS = CURRENT_TIMESTAMP WHERE CLOK_NBR = " . $row['CLOK_NBR'];
                mysql_query($query,$local);
                echo $query."<br />";
            }
        }
	}*/
?>