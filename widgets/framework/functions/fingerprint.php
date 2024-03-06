<?php
//require_once __DIR__ . "/../framework/database/connect.php";

$f = new Fingerprint();
$f->getScanLog();
class Fingerprint
{
    private $sn = "2251016140066";
    private $port = "80/";
    private $ip_server = "http://192.168.1.20";
    private $server = "";

    /**
     * Fingerprint constructor.
     */
    public function __construct()
    {
        $this->server = $this->ip_server . ":" . $this->port;
    }

    function setUser($pin, $nama)
    {
        $pin = "1" . $pin;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->port,
            CURLOPT_URL => $this->server . "user/set",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "sn=" . $this->sn . "&pin=" . $pin . "&nama=" . $nama . "&pwd=0&rfid=0&priv=0&tmp=%5B%5D",
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
        } else {
            return $response;
        }
    }

    /**
     * insert ke database lokal
     */
    function getScanLog()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->port,
            CURLOPT_URL => $this->server . "scanlog/all",#semua scanlog "scanlog/all"
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "sn=" . $this->sn,
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
        foreach ($data as $item) {
            $personNbr = substr($item->PIN, 1, strlen($item->PIN));
            $scanDate = date(strtotime($item->ScanDate));

            $sql = "SELECT * FROM MACH_CLOK WHERE PRSN_NBR = " . $personNbr . " AND DATE(CLOK_IN_TS) = '" . date('Y-m-d', $scanDate) . "' AND CLOK_OT_TS IS NULL";
            $rs = mysql_query($sql);

            $num_rows = mysql_num_rows($rs);
            $row = mysql_fetch_array($rs);

            if ($num_rows == 0) {
                $sql = "INSERT INTO MACH_CLOK(PRSN_NBR, CLOK_IN_TS, UPD_TS) VALUES (" . $personNbr . ",'" . $item->ScanDate . "',CURRENT_TIMESTAMP)";
                mysql_query($sql);
            } else {
                echo date('Y-m-d H:i:s', $scanDate)." ".$row['CLOK_IN_TS']."\n";

                $rs = mysql_query("SELECT HOUR(TIMEDIFF('" . date('Y-m-d H:i:s', $scanDate) . "','" . $row['CLOK_IN_TS'] . "')) AS diff");
                $rows = mysql_fetch_array($rs);

                echo "diff ".$rows['diff']."\n";

                if ($rows['diff'] < 1) {
                    $sql = "UPDATE MACH_CLOK SET CLOK_OT_TS = NULL, UPD_TS = CURRENT_TIMESTAMP WHERE CLOK_NBR = " . $row['CLOK_NBR'];
                    mysql_query($sql);
                } else {
                    $sql = "UPDATE MACH_CLOK SET CLOK_OT_TS = '" . $item->ScanDate . "', UPD_TS = CURRENT_TIMESTAMP WHERE CLOK_NBR = " . $row['CLOK_NBR'];
                    mysql_query($sql);
                }
            }
            $i++;
        }

    }

    function uploadScanLog()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://nestor.asia/mach_clok.php?q=get-update-time'
        ));

        $result = curl_exec($curl);
        curl_close($curl);

        #0 mean no data
        if ($result == 0) {
            $rs = mysql_query("SELECT * FROM MACH_CLOK");

            $jsonData = array();
            while ($array = mysql_fetch_array($rs)) {
                $jsonData[] = $array;
            }
            $json = json_encode($jsonData);
        } else {
            $rs = mysql_query("SELECT * FROM MACH_CLOK WHERE UPD_TS > '" . $result . "'");
            $jsonData = array();
            while ($array = mysql_fetch_array($rs)) {
                $jsonData[] = $array;
            }
            $json = json_encode($jsonData);
        }


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "nestor.asia/mach_clok.php?q=upload-scanlog",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Content-Length: " . strlen($json)
            )
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    function getUser()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->port,
            CURLOPT_URL => $this->server . "user/all",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "sn=" . $this->sn,
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
        } else {
            return $response;
        }
    }
}