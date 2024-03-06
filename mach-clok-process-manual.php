<?php      
	include "framework/database/connect.php";
	//mysql_connect("192.168.1.20","root","Pr0reliance");
    //mysql_select_db("cmp");

	//$query_atnd		= "SELECT * FROM CMP.ATND_CLOK 
	//				WHERE UPD_TS > (SELECT COALESCE(MAX(UPD_TS),'0000-00-00') AS UPD_TS FROM CMP.MACH_CLOK)
	//				ORDER BY PRSN_NBR, UPD_TS";	//query diubah
	
	$query_atnd		= "SELECT * FROM CMP.ATND_CLOK 
					WHERE DATE(UPD_TS) BETWEEN '2017-04-14' AND '2017-04-19'
					ORDER BY PRSN_NBR, UPD_TS";
	$result_atnd	= mysql_query($query_atnd);
	//echo $query_atnd;
	while($row_atnd = mysql_fetch_array($result_atnd)) {
		//echo $row_atnd['UPD_TS']."<br/>";	
		$personNbr	= $row_atnd['PRSN_NBR'];
		$scanDate	= $row_atnd['UPD_TS'];
				
            $sql = "SELECT * FROM MACH_CLOK WHERE PRSN_NBR = " . $personNbr . " AND DATE(CLOK_IN_TS) = '" . date('Y-m-d', strtotime($scanDate)) . "' AND CLOK_OT_TS IS NULL";
            $rs = mysql_query($sql);
			
			$num_rows = mysql_num_rows($rs);
            $row = mysql_fetch_array($rs);

            if ($num_rows == 0) {
                $query = "INSERT INTO MACH_CLOK(PRSN_NBR, CLOK_IN_TS, UPD_TS) VALUES (" . $personNbr . ",'" . $scanDate . "','" . $scanDate . "')";
                mysql_query($query);
				
				echo $query."<br />";
				
            } else {
                
				$query_diff = "SELECT HOUR(TIMEDIFF('" . date('Y-m-d H:i:s', strtotime($scanDate)) . "','" . $row['CLOK_IN_TS'] . "')) AS diff";
				
                $result_diff 	= mysql_query($query_diff);
                $row_diff		= mysql_fetch_array($result_diff);

				if ($row_diff['diff'] >= 1) {
                    $query = "UPDATE MACH_CLOK SET CLOK_OT_TS = '" . $scanDate . "', UPD_TS = '" . $scanDate . "' WHERE CLOK_NBR = " . $row['CLOK_NBR'];
                    mysql_query($query);
					
					echo $query."<br />";
                } 
				
            }
	}
?>