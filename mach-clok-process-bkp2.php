<?php      
	include "framework/database/connect.php";

	$query_atnd		= "SELECT * FROM CMP.ATND_CLOK 
					WHERE UPD_TS > (SELECT COALESCE(MAX(UPD_TS),'0000-00-00') AS UPD_TS FROM CMP.MACH_CLOK)
					ORDER BY PRSN_NBR, CRT_TS";	
	
	$result_atnd	= mysql_query($query_atnd);
	while($row_atnd = mysql_fetch_array($result_atnd)) {

		$personNbr	= $row_atnd['PRSN_NBR'];
		$scanDate	= $row_atnd['CRT_TS']; //UPD_TS diubah menjadi CRT_TS
		
		$query 		= "SELECT * FROM 
							EXCPTN_ETRY 
						WHERE PRSN_NBR = " . $personNbr . " 
							AND DATE(EXCPTN_ETRY_TS) = '" . date('Y-m-d', strtotime($scanDate)) . "'";
		$result     = mysql_query($query);
		if (mysql_num_rows($result)>0){
			$queryD = "DELETE FROM MACH_CLOK WHERE DATE(CLOK_IN_TS)='". date('Y-m-d', strtotime($scanDate))."' AND PRSN_NBR=".$personNbr;
			$resultD= mysql_query($queryD);
			
			$queryA = "SELECT * FROM CMP.ATND_CLOK 
						WHERE PRSN_NBR=".$personNbr."
						AND DATE(CRT_TS)='".date('Y-m-d', strtotime($scanDate))."' AND DEL_NBR=0 ORDER BY CRT_TS ASC";
			$resultA= mysql_query($queryA);
			
			echo $queryA."<br />";
			
			while ($rows= mysql_fetch_array($resultA)){
				$queryDel = "SELECT * FROM MACH_CLOK WHERE PRSN_NBR = " . $rows['PRSN_NBR'] . " AND DATE(CLOK_IN_TS) = '" . date('Y-m-d', strtotime($rows['CRT_TS'])) . "' AND CLOK_OT_TS IS NULL";
				$rsDel = mysql_query($queryDel);
			
				$num_rowsDel = mysql_num_rows($rsDel);
				$rowDel = mysql_fetch_array($rsDel);

            if ($num_rowsDel == 0) {
                $queryIn = "INSERT INTO MACH_CLOK(PRSN_NBR, CLOK_IN_TS, UPD_TS) VALUES (" . $rows['PRSN_NBR'] . ",'" . $rows['CRT_TS'] . "',CURRENT_TIMESTAMP)";
                mysql_query($queryIn);
				
				echo $queryIn."<br /><br />";
				
            } else {
                
				$query_diffUp = "SELECT HOUR(TIMEDIFF('" . date('Y-m-d H:i:s', strtotime($rows['CRT_TS'])) . "','" . $rowDel['CLOK_IN_TS'] . "')) AS diff";
				echo $query_diffUp."<br /><br />";
                $result_diffUp 	= mysql_query($query_diffUp);
                $row_diffUp		= mysql_fetch_array($result_diffUp);

				if ($row_diffUp['diff'] >= 1) {
                    $query = "UPDATE MACH_CLOK SET CLOK_OT_TS = '" . $rows['CRT_TS'] . "', UPD_TS = CURRENT_TIMESTAMP WHERE CLOK_NBR = " . $rowDel['CLOK_NBR'];
                    mysql_query($query);
					
					echo $query."<br />";
                } 
				
            }
			}
		}else{
		
            $sql = "SELECT * FROM MACH_CLOK WHERE PRSN_NBR = " . $personNbr . " AND DATE(CLOK_IN_TS) = '" . date('Y-m-d', strtotime($scanDate)) . "' AND CLOK_OT_TS IS NULL";
            $rs = mysql_query($sql);
			
			$num_rows = mysql_num_rows($rs);
            $row = mysql_fetch_array($rs);

            if ($num_rows == 0) {
                $query = "INSERT INTO MACH_CLOK(PRSN_NBR, CLOK_IN_TS, UPD_TS) VALUES (" . $personNbr . ",'" . $scanDate . "',CURRENT_TIMESTAMP)";
                mysql_query($query);
				
				echo $query."<br />";
				
            } else {
                
				$query_diff = "SELECT HOUR(TIMEDIFF('" . date('Y-m-d H:i:s', strtotime($scanDate)) . "','" . $row['CLOK_IN_TS'] . "')) AS diff";
				
                $result_diff 	= mysql_query($query_diff);
                $row_diff		= mysql_fetch_array($result_diff);

				if ($row_diff['diff'] >= 1) {
                    $query = "UPDATE MACH_CLOK SET CLOK_OT_TS = '" . $scanDate . "', UPD_TS = CURRENT_TIMESTAMP WHERE CLOK_NBR = " . $row['CLOK_NBR'];
                    mysql_query($query);
					
					echo $query."<br />";
                } 
			}
		}
	}
?>