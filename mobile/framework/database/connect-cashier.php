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

if (isset($_SESSION['NST_ACCESS'])) {
    /**
     * The GC will clear the session data files based on their last modification time. 
     * Thus if you never modify the session, you simply read from it, then the GC will eventually clean up
     */
    if ($_SESSION['NST_ACCESS'] + (1 * 60) <= time()) {
        $_SESSION['NST_ACCESS'] = time(); 
    }

    // Check timeout flag if created time was more than 12 hours ago
    if (isset($_SESSION['CREATED']) && $_SESSION['CREATED'] + (8 * 60 * 60) <= time()) {
        // Unset all of the session variables.
        $_SESSION = array();
        @session_destroy(); // Destroy session data in storage

        ?><script text="type/javascript">
            if (typeof window.top.forceLogin !== "function") {
                window.top.forceLogin();
            } else {
                window.top.document.getElementById('search').src='cashier-login.php?POS_ID=<?php echo $POSID;?>&COMMAND=LOGOUT';
            }
        </script><?php
    }

    // Check if last activity was more than 4 hour ago
    if (isset($_SESSION['LAST_ACTIVITY']) && $_SESSION['LAST_ACTIVITY'] + (4 * 60 * 60) <= time()) {
        // Unset all of the session variables.
        $_SESSION = array();
        @session_destroy(); // Destroy session data in storage

        ?><script text="type/javascript">
            if (typeof window.top.forceLogin !== "function") {
                window.top.forceLogin();
            } else {
                window.top.document.getElementById('search').src='cashier-login.php?POS_ID=<?php echo $POSID;?>&COMMAND=LOGOUT';
            }
        </script><?php
    }

    $_SESSION['LAST_ACTIVITY'] = time();
} elseif (strpos($_SERVER['REQUEST_URI'], "cashier-login.php") === false) {
    ?><script text="type/javascript">
        if (typeof window.top.forceLogin !== "function") {
            window.top.forceLogin();
        } else {
            window.top.document.getElementById('search').src='cashier-login.php?POS_ID=<?php echo $POSID;?>&COMMAND=LOGOUT';
        }
    </script><?php
}


    $OLTP='192.168.1.20';
    $OLTA='192.168.1.10';
		
    //db connection OLTA
    mysql_connect($OLTP,"root","Pr0reliance");
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
$rtl = mysql_connect($defServer, "root", "Pr0reliance", true);
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
$cmp = mysql_connect($defServer, "root", "Pr0reliance", true);
mysql_select_db("cmp", $cmp);

?>