<?php
	session_start();
	include_once "function_db.php";
	error_reporting(0);

    //Need to have a more robust logic to prevent spoofing and ability to handle IPv6
    if(substr($_SERVER['REMOTE_ADDR'],0,8)==substr($_SERVER['SERVER_ADDR'],0,8)){
        $OLTP='localhost';
        $OLTA='192.168.1.70';
    }else{
        $OLTP='localhost';
        $OLTA='192.168.1.70';
    }
	$_SESSION['userID']=$_SESSION['userID'];
	$_SESSION['personNBR']=$_SESSION['personNBR'];
	
    //db connection OLTA
    mysql_connect($OLTA,"root","");
    mysql_select_db("cmp");
    
    $query="SELECT TAX_LOCK FROM NST.PARAM_LOC";
    $result=mysql_query($query);
    $row=mysql_fetch_array($result);
    $locked=$row['TAX_LOCK'];

    if($locked==0){
        if ($_COOKIE["LOCK"] == "LOCK") {
            $defServer=$OLTA;
	    $password = "";
        }else{
            $defServer=$OLTP;
	    $password = "";
        }
    }else{
        $defServer=$OLTA;
	$password = "";
    }  

    if($display=='TRIM'){
        $defServer=$OLTA;
	$password = "";
    }

	//db connection
	mysql_connect($defServer,"root",$password);
	mysql_select_db("cmp");
    //echo $defServer;
	
	$query="SELECT CO_NBR_DEF,WHSE_NBR_DEF,PAY_HLD_DIV,CO_NBR_PKP FROM NST.PARAM_LOC";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$CoNbrDef=$row['CO_NBR_DEF'];
	$WhseNbrDef=$row['WHSE_NBR_DEF'];
	$PayHldDiv=$row['PAY_HLD_DIV'];
	$CoNbrPkp   = $row['CO_NBR_PKP'];
	

?>
