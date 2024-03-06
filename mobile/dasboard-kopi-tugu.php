<?php
//include "framework/database/koneksi-kopi.php";
//include "framework/functions/print-digital.php";
include "framework/functions/crypt.php";
// include "framework/functions/default.php";
// include "framework/security/default.php";

$host = "192.168.1.11";  
$username = "root"; 
$password = ""; 
$database = "rtl"; 

$koneksi = new mysqli($host, $username, $password, $database);

if ($koneksi->connect_error) {
    die("Koneksi Gagal: " . $koneksi->connect_error);
}




// $Security = getSecurity($_SESSION['userID'], "Retail");
// $upperSecurity = getSecurity($_SESSION['userID'], "Executive");
?>

<?php
$n = (14 * 7);

$date = mktime(0, 0, 0, date("m"), date("d") - $n, date("Y"));
$beginDate = date('Y-m-d', $date);

$query = "SELECT 
					TGL.Date AS CRT_TS,
					COALESCE(RPT.ORD_DTE, CONCAT(MONTH(TGL.Date),'-',DAY(TGL.Date))) AS ORD_DTE,
					COALESCE(RPT.ORD_DAY, DAY(TGL.Date)) AS ORD_DAY,
					COALESCE(RPT.ORD_MONTH, MONTH(TGL.Date)) AS ORD_MONTH,
					COALESCE(RPT.ORD_YEAR, YEAR(TGL.Date)) AS ORD_YEAR,
					COALESCE(RPT.REVENUE, 0) AS REVENUE_KT,
					COALESCE(RPT.CSH_FLO_TYP, 'RT') AS CSH_FLO_TYP
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
							DATE_FORMAT(CRT_TS,'%e-%c') AS ORD_DTE,
							DATE_FORMAT(CRT_TS,'%e') AS ORD_DAY,
							DATE_FORMAT(CRT_TS,'%c') AS ORD_MONTH,
							DATE_FORMAT(CRT_TS,'%Y') AS ORD_YEAR,
							SUM(CASE WHEN CSH.CSH_FLO_TYP = 'RT' THEN CSH.TND_AMT - COALESCE((TTL.DISC_PCT_AMT + TTL.DISC_AMT), 0) ELSE CSH.TND_AMT END) AS REVENUE,
							DATE(CRT_TS) AS DTE,
							CSH.CSH_FLO_TYP
						FROM RTL.CSH_REG CSH
						LEFT JOIN 
						(
							SELECT 
								REG_NBR,
								COALESCE(CASE WHEN CSH_FLO_TYP ='RT' THEN DISC_PCT ELSE 0 END, 0) AS DISC_PCT, 
								COALESCE(CASE WHEN CSH_FLO_TYP ='RT' THEN (DISC_PCT/100)*TND_AMT ELSE 0 END, 0) AS DISC_PCT_AMT, 
								COALESCE(CASE WHEN CSH_FLO_TYP ='RT' THEN DISC_AMT ELSE 0 END, 0) AS DISC_AMT,
								DATE(CRT_TS) AS DTE
							FROM RTL.CSH_REG
							WHERE POS_ID=3 
						) TTL ON TTL.REG_NBR = CSH.REG_NBR
						WHERE DATE(CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 4 WEEK) AND CURRENT_DATE AND CSH.CSH_FLO_TYP='RT'
						GROUP BY DATE(CRT_TS)
					) RPT ON TGL.Date = RPT.DTE
				WHERE DATE(TGL.Date) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE 
				ORDER BY TGL.Date ASC";

$result = mysql_query($query);

// Counter to skip lead day for the moving average calculation
$leadDayRetail = 0;
$dailyRevRetail = array();

while ($row = mysql_fetch_array($result)) {
    $dailyRevRetail[] = $row['REVENUE_KT'];
    $leadDayRetail++;
}


$encryptedData = simple_crypt(json_encode([
    'dailyRevRetail' => $dailyRevRetail
]), 'e');

echo $encryptedData;
?>
