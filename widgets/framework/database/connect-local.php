<?php
mysql_connect('localhost', 'root', 'Pr0reliance');
mysql_select_db('CMP');

$query = "SELECT CO_NBR_DEF,WHSE_NBR_DEF FROM NST.PARAM_LOC";
$result = mysql_query($query);
$row = mysql_fetch_array($result);
$CoNbrDef = $row['CO_NBR_DEF'];
$WhseNbrDef = $row['WHSE_NBR_DEF'];
?>