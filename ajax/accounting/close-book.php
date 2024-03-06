<?php
require_once __DIR__ . "/../../framework/database/connect.php";
require_once __DIR__ . "/../../framework/functions/default.php";

$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'total' => array()
);

$query	= "SELECT * FROM RTL.ACCTG_BK ORDER BY BK_NBR DESC";

$result	= mysql_query($query);

while ($row = mysql_fetch_array($result)) {

	$results['data'][] = $row;

	$results['total']['DEB'] += $row['DEB'];
	$results['total']['CRT'] += $row['CRT'];


}

echo json_encode($results);