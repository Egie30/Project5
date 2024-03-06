<?php
$shipper	= mysql_connect("192.168.1.20","nestor","{;?qSpu!Fw)^jX8E","CMP"); //192.168.1.20
$receiver 	= mysql_connect("192.168.1.10","nestor","cG>cFk3q!RMh]qa#","CMP"); //192.168.1.10

$query 		= "UPDATE CMP.TRNSP_HEAD TSP 
				LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED 
				ON TSP.ORD_NBR = HED.ORD_NBR 
				SET TSP.ACTG_TYP = HED.ACTG_TYP
				WHERE TSP.TRNSP_STT_ID != 'RP'";
mysql_query($query, $shipper);

$query 		= "UPDATE CMP.TRNSP_HEAD TSP 
				LEFT JOIN RTL.RTL_STK_HEAD HED 
				ON TSP.ORD_NBR = HED.ORD_NBR 
				SET TSP.ACTG_TYP = HED.ACTG_TYP
				WHERE TSP.TRNSP_STT_ID = 'RP'";
mysql_query($query, $shipper);

echo '<pre>'.$query.'</pre>';



