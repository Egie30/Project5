<?php      
	include "framework/database/connect.php";

	$query_atnd		= "SELECT * FROM CMP.ATND_CLOK 
					WHERE UPD_TS > (SELECT COALESCE(MAX(UPD_TS),'0000-00-00') AS UPD_TS FROM CMP.MACH_CLOK)
					ORDER BY PRSN_NBR, CRT_TS";	
	
	$result_atnd	= mysql_query($query_atnd);
	while($row_atnd = mysql_fetch_array($result_atnd)) {

		$personNbr	= $row_atnd['PRSN_NBR'];
		$scanDate	= $row_atnd['CRT_TS']; //UPD_TS diubah menjadi CRT_TS
				
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
	
	
?><?php      
	include "framework/database/connect.php";

	$query_atnd		= "SELECT * FROM CMP.ATND_CLOK 
					WHERE UPD_TS > (SELECT COALESCE(MAX(UPD_TS),'0000-00-00') AS UPD_TS FROM CMP.MACH_CLOK)
					ORDER BY PRSN_NBR, CRT_TS";	
	
	$result_atnd	= mysql_query($query_atnd);
	while($row_atnd = mysql_fetch_array($result_atnd)) {

		$personNbr	= $row_atnd['PRSN_NBR'];
		$scanDate	= $row_atnd['CRT_TS']; //UPD_TS diubah menjadi CRT_TS
				
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
	
	
?>