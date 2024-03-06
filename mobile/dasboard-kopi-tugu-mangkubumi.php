<?php

include "framework/functions/crypt.php";

$host = "localhost";  
$username = "champion_wp"; 
$password = "vmZxvcwuy5fD"; 
$database = "champion_negaraku"; 

$koneksi = new mysqli($host, $username, $password, $database);

if ($koneksi->connect_error) {
    die("Koneksi Gagal: " . $koneksi->connect_error);
}

$n = (14 * 7);

$date = mktime(0, 0, 0, date("m"), date("d") - $n, date("Y"));
$beginDate = date('Y-m-d', $date);

$query = "SELECT 
                TGL.Date AS DTE,
                COALESCE(RPT.ORD_DTE, CONCAT(MONTH(TGL.Date),'-',DAY(TGL.Date))) AS ORD_DTE,
                COALESCE(RPT.ORD_DAY, DAY(TGL.Date)) AS ORD_DAY,
                COALESCE(RPT.ORD_MONTH, MONTH(TGL.Date)) AS ORD_MONTH,
                COALESCE(RPT.ORD_YEAR, YEAR(TGL.Date)) AS ORD_YEAR,
                COALESCE(RPT.REVENUE, 0) AS REVENUE
                FROM
                (
                    SELECT '".$beginDate."' + INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
                    FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
                    CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
                    CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
                ) TGL
                LEFT OUTER JOIN 
                (
                    SELECT 
                        DATE_FORMAT(DATE_CREATED,'%e-%c') AS ORD_DTE,
                        DATE_FORMAT(DATE_CREATED,'%e') AS ORD_DAY,
                        DATE_FORMAT(DATE_CREATED,'%c') AS ORD_MONTH,
                        DATE_FORMAT(DATE_CREATED,'%Y') AS ORD_YEAR,
                        SUM(ORD.NET_TOTAL) AS REVENUE,
                        DATE(DATE_CREATED) AS DTE
                    FROM CMP_NEGARA.WP_WC_ORDER_STATS ORD
                    WHERE DATE(DATE_CREATED) BETWEEN (CURRENT_DATE - INTERVAL 4 WEEK) AND CURRENT_DATE AND ORD.STATUS='WC-COMPLETED'
                    GROUP BY DATE(DATE_CREATED)
                ) RPT ON TGL.Date = RPT.DTE
                WHERE DATE(TGL.Date) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE 
                ORDER BY TGL.Date ASC";
  
                $result = mysql_query($query);

// Counter to skip lead day for the moving average calculation
$leadDay = 0;
$DRevkopi = array();

while ($row = mysql_fetch_array($result)) {
    $DRevkopi[] = $row['REVENUE'];
    $leadDay++;
}


$encryptedData = simple_crypt(json_encode([
    'dailyRevRetail' => $DRevkopi
]), 'e');

echo $encryptedData;
?>
