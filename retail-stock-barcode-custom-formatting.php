<?php
$brc = $_POST['BRCLST'];
$brc = str_replace(array(",","|",chr(13),chr(9),"\r\n", "\r", "\n")," ", $brc);
echo $brc;
?>
