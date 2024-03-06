<?php
    $nestor=session_name("nestor");
    session_set_cookie_params(0,'/','.champs.asia');
    session_start();
	include_once "function_db.php";

    //$OLTP='192.168.1.20';
    //$OLTA='192.168.1.10';
    $OLTP='localhost';
    $OLTA='localhost';

	$_SESSION['userID']=$_SESSION['userID'];
	$_SESSION['personNBR']=$_SESSION['personNBR'];
	
    //db connection OLTA
    mysql_connect($OLTA,"root","Pr0reliance");
    //mysql_connect($OLTA,"root");
    mysql_select_db("cmp");
    
    $query="SELECT TAX_LOCK FROM NST.PARAM_LOC";
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

	//db connection
	//mysql_connect($defServer,"root","Pr0reliance");
	mysql_connect($defServer,"root");
	mysql_select_db("cmp");
    //echo $defServer;
	
	$query="SELECT CO_NBR_DEF,WHSE_NBR_DEF FROM NST.PARAM_LOC";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$CoNbrDef=$row['CO_NBR_DEF'];
	$WhseNbrDef=$row['WHSE_NBR_DEF'];

?>