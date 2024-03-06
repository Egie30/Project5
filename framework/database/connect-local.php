<?php
session_start();
mysql_connect('localhost', 'root', '');
mysql_select_db('CMP');

$query = "SELECT CO_NBR_DEF,WHSE_NBR_DEF FROM NST.PARAM_LOC";
$result = mysql_query($query);
$row = mysql_fetch_array($result);
$CoNbrDef = $row['CO_NBR_DEF'];
$WhseNbrDef = $row['WHSE_NBR_DEF'];
?>