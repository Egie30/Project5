<?php
require_once __DIR__ . "/../functions/error.php";

date_default_timezone_set("Asia/Jakarta");

// Set the session name
session_name('NST_CSH_SESSID');

// Initialize the session.
session_start();

if ($_GET['POS_ID'] == "") {
    $_GET['POS_ID'] = $_SESSION['POS_ID'];
}

$POSID = $_GET['POS_ID'];


$OLTP='localhost';
$OLTA='localhost';
	
//db connection OLTA
mysql_connect($OLTA,"root","");
mysql_select_db("cmp");

$query="SELECT TAX_LOCK FROM nst.param_loc";
$result=mysql_query($query);
$row=mysql_fetch_array($result);
$locked=$row['TAX_LOCK'];

if($locked==0){
	$defServer=$OLTP;
}else{
	$defServer=$OLTA;
}  

if($display=='TRIM'){
	$defServer=$OLTA;
}

// Database connection for Server
$rtl = mysql_connect($defServer, "root", "", true);
mysql_select_db("rtl", $rtl);

$query = "SELECT CO_NBR_DEF FROM NST.PARAM_LOC";
$result = mysql_query($query, $rtl);
$row = mysql_fetch_array($result);
$CoNbrDef = $row['CO_NBR_DEF'];

$query = "SELECT WHSE_NBR_DEF FROM NST.PARAM_LOC";
$result = mysql_query($query, $rtl);
$row = mysql_fetch_array($result);
$WhseNbrDef = $row['WHSE_NBR_DEF'];

$query = "SELECT POS_ID, POS_IP FROM RTL.CSH_REG_IP WHERE POS_ID=" . $_GET['POS_ID'];
$result = mysql_query($query, $rtl);
$row = mysql_fetch_array($result);
$POSID = $row['POS_ID'];
$POSIP = $row['POS_IP'];

$_SESSION['POS_ID'] = $POSID;
$_SESSION['POS_IP'] = $POSIP;

// Database connection for POS
$csh = mysql_connect($POSIP , "root", "", true);
mysql_select_db("csh", $csh);

// Database connection for Server
$cmp = mysql_connect($defServer, "root", "", true);
mysql_select_db("cmp", $cmp);

?>