<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	$Security=getSecurity($_SESSION['userID'],"Executive");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src="framework/functions/default.js"></script>

</head>

<body class="sub">
<div class="leftmenusel" onclick="changeSiblingUrl('content','/phpmyadmin/');parent.Pace.restart();selLeftMenu(this);">phpMyAdmin</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','/xampp/phpinfo.php');parent.Pace.restart();selLeftMenu(this);">phpinfo()</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','/xampp/status.php');parent.Pace.restart();selLeftMenu(this);">Status</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','slider.php');selLeftMenu(this);">Slider</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','print-digital-discount.php');selLeftMenu(this);">Volume Diskon</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','print-digital-hand-off.php');selLeftMenu(this);">Serah Terima</div>

<?php
if ($Security == 0) {
    ?>
    <div class="leftmenu" onclick="changeSiblingUrl('content', 'print-digital-komisi.php'); selLeftMenu(this);">Komisi
        Broker
    </div>
    <!-- <div class="leftmenu" onclick="changeSiblingUrl('content','fingerprint-people.php');selLeftMenu(this);">Finger Print</div> -->
<?php
}
?>
<div class="leftmenu" onclick="changeSiblingUrl('content','cloud-list.php');selLeftMenu(this);">Cloud</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','log.php');selLeftMenu(this);">Log Mesin</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','notif.php');selLeftMenu(this);">Notifikasi</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','device-authorization.php');selLeftMenu(this);">Device Authorization</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','payroll-config-dte.php');selLeftMenu(this);">Payroll Config Date</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','contribution-price-rate.php');selLeftMenu(this);">Reksadana</div>
</body>
</html>