<?php
$hostDB     = 'champion.id';
$NameDB     = 'champion_apps';
$UserDB     = 'champion_root';
$PassDB     = 'Pr0reliance';
$connect    = mysqli_connect($hostDB,$UserDB,$PassDB,$NameDB);

if (!$connect) {
  die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";
?>