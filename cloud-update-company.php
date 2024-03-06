<?php
include "framework/database/connect-cloud.php";
include_once("framework/functions/default.php");
include_once("framework/security/default.php");

$query="SELECT CO_NBR_DEF,WHSE_NBR_DEF FROM NST.PARAM_LOC";
$result=mysql_query($query);
$row=mysql_fetch_array($result);
$CoNbrDef=$row['CO_NBR_DEF'];

if($cloud != false){

//UPDATE SUP_F ON TABLE COMPANY

$query 	= "SELECT HED.SHP_CO_NBR 
			FROM RTL.RTL_STK_HEAD HED
				WHERE DATE(HED.CRT_TS) = CURRENT_DATE - INTERVAL 1 DAY
					AND HED.RCV_CO_NBR = ".$CoNbrDef."
					AND HED.DEL_F = 0
					AND HED.IVC_TYP = 'RC'
			GROUP BY HED.SHP_CO_NBR
			";
$result	= mysql_query($query, $local);

while($row = mysql_fetch_array($result)) {
	
	$ShipperCoNbr 	= $row['SHP_CO_NBR'];
	
	$query_upd	= "UPDATE $CMP.COMPANY 
					SET SUP_F = 1 
					WHERE CO_NBR = ".$ShipperCoNbr." ";
	$result_upd	=mysql_query($query_upd,$cloud);
	
	
	echo "<br />".$query_upd."<br />";
	
	$query_upd=str_replace($CMP,"CMP",$query_upd);
	$result_upd=mysql_query($query_upd,$local);

}


//UPDATE 3RD_PTY_NBR ON TABLE COMPANY

$query 	= "SELECT HED.BUY_CO_NBR,
				HED.CNS_CO_NBR
			FROM CMP.PRN_DIG_ORD_HEAD HED
				WHERE DATE(HED.ORD_TS) = CURRENT_DATE - INTERVAL 1 DAY
					AND HED.PRN_CO_NBR = ".$CoNbrDef."
					AND HED.CNS_CO_NBR IS NOT NULL
					AND HED.DEL_NBR = 0
			GROUP BY HED.CNS_CO_NBR
			";
$result	= mysql_query($query, $local);

while($row = mysql_fetch_array($result)) {
	
	$BuyCoNbr 	= $row['BUY_CO_NBR'];
	$CnsCoNbr	= $row['CNS_CO_NBR'];
	
	$query_upd	= "UPDATE $CMP.COMPANY 
					SET 3RD_PTY_NBR = ".$BuyCoNbr." 
					WHERE CO_NBR = ".$CnsCoNbr." ";
	$result_upd	=mysql_query($query_upd,$cloud);
	
	echo "<br />".$query_upd."<br />";
	
	$query_upd=str_replace($CMP,"CMP",$query_upd);
	$result_upd=mysql_query($query_upd,$local);

	
	}

	
//UPDATE LAST_ACT_TS ON TABLE COMPANY

$query 	= "SELECT HED.BUY_CO_NBR,
				MAX(HED.UPD_TS) AS UPD_TS
			FROM CMP.PRN_DIG_ORD_HEAD HED
				WHERE DATE(HED.ORD_TS) = CURRENT_DATE - INTERVAL 1 DAY
					AND HED.PRN_CO_NBR = ".$CoNbrDef."
					AND HED.DEL_NBR = 0
			GROUP BY HED.BUY_CO_NBR
			ORDER BY HED.UPD_TS DESC
			";
$result	= mysql_query($query, $local);

while($row = mysql_fetch_array($result)) {
	
	$BuyCoNbr 	= $row['BUY_CO_NBR'];
	$UpdTime	= $row['UPD_TS'];
	
	$query_upd	= "UPDATE $CMP.COMPANY 
					SET LAST_ACT_TS = '".$UpdTime."' 
					WHERE CO_NBR = ".$BuyCoNbr." ";
	$result_upd	=mysql_query($query_upd,$cloud);

	
	echo "<br />".$query_upd."<br />";
	
	$query_upd=str_replace($CMP,"CMP",$query_upd);
	$result_upd=mysql_query($query_upd,$local);

	
	}

	
}





?>
