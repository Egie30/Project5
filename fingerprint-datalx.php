<?php
//ini_set('max_execution_time', 30);      
	include "framework/database/connect-cloud.php";

	$sn 		= "2251016140155";
    $port 		= "8080/";
    $ip_server 	= "http://192.168.1.10";

	$server 	= $ip_server . ":" . $port;
   
	  $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $port,
            CURLOPT_URL => $server . "scanlog/new",#semua scanlog "scanlog/all"
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 1000,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "sn=" . $sn,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded"
            ),
        ));
		
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        }
		

        #save to local
        $obj = json_decode($response);
        $data = $obj->Data;

        $i = 0;
		
	echo "<pre>";
	print_r($data);
	print_r($obj);
	echo "</pre>";
		
	echo "<br/>";
	
	if (is_array($data)) {
		foreach ($data as $item) {
	    if (substr($item->PIN,0,1)=="1"){ //karyawan champion
            $personNbr = substr($item->PIN, 1, strlen($item->PIN));
	        //$scanDate = $item->ScanDate;
            $scanDate = $item->ScanDate;

			$query	= "SELECT * FROM PAY.ATND_CLOK 
						WHERE PRSN_NBR = ".$personNbr."
							AND CRT_TS = '".$scanDate."' ";
		echo $query."<br/>";
			
            $result	= mysql_query($query, $local);

            $num_rows = mysql_num_rows($result);
            $row = mysql_fetch_array($result); 

            if ($num_rows == 0) {
                $query_ins = "INSERT INTO $PAY.ATND_CLOK (PRSN_NBR, CRT_TS, UPD_TS) VALUES (".$personNbr.", '".$item->ScanDate."', CURRENT_TIMESTAMP)";
		//$query_ins = "INSERT INTO $PAY.ATND_CLOK (PRSN_NBR, CRT_TS, UPD_TS) VALUES (".$personNbr.", '".$item->ScanDate."', '2019-02-16 10:00:00')";
		        echo $query_ins."<br/>";
               
				$result_ins =mysql_query($query_ins,$cloud);
				
				$query_ins=str_replace($PAY,"PAY",$query_ins);
				$result_ins=mysql_query($query_ins,$local);
            }
			
            $i++;
	}
        }
	}
        
			
		$queryUpdate	= "UPDATE CDW.UPD_LAST SET ATND_CLOK = CURRENT_TIMESTAMP";
		$resultUpdate	= mysql_query($queryUpdate, $local);


?>