<?php
require_once "framework/database/connect.php";
require_once "framework/security/default.php";

$security = getSecurity($_SESSION['userID'], "Executive");

//$query = "SELECT BK_NBR, YEAR(BEG_DTE) AS YEAR FROM RTL.ACCTG_BK WHERE ACT_F=1 AND MONTH(BEG_DTE) = MONTH(CURRENT_DATE)";

$query = "SELECT BK_NBR, YEAR(BEG_DTE) AS YEAR FROM RTL.ACCTG_BK WHERE ACT_F=1 AND DEL_NBR = 0 ORDER BY BK_NBR DESC LIMIT 1";

$result 	= mysql_query($query);
$row 		= mysql_fetch_array($result);
$bookNumber = $row['BK_NBR'];
$year		= $row['YEAR'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript">parent.Pace.restart();</script>
</head>
<body class="sub">

<div class="leftmenusel" onclick="changeSiblingUrl('content','accounting-account-major.php');selLeftMenu(this);">Daftar Grup Rekening</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','accounting-account.php');selLeftMenu(this);">Daftar Rekening</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','accounting-book.php');selLeftMenu(this);">Buku</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','accounting-balance.php?BK_NBR=<?php echo $bookNumber;?>&PLUS=<?php echo $lock; ?>');selLeftMenu(this);">Saldo Awal</div>
<!--
<div class="leftmenu" onclick="changeSiblingUrl('content','depreciation.php?BK_NBR=<?php echo $bookNumber;?>&PLUS=<?php echo $_SESSION['PLUS_MODE'];?>');selLeftMenu(this);">Penyusutan</div>
-->

<div class="leftmenu" onclick="changeSiblingUrl('content','general-journal.php?BK_NBR=<?php echo $bookNumber;?>&NST=0&PLUS=<?php echo $lock;?>&ACTG=0');selLeftMenu(this);">Jurnal Umum</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','general-ledger.php?BK_NBR=<?php echo $bookNumber;?>&PLUS=<?php echo $lock;?>&ACTG=0');selLeftMenu(this);">Buku Besar</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','trial-balance.php?BK_NBR=<?php echo $bookNumber;?>&PLUS=<?php echo $lock;?>&ACTG=0');selLeftMenu(this);">Neraca Saldo</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','hpp.php?ACTG=0&BK_NBR=<?php echo $bookNumber;?>&PLUS=<?php echo $lock;?>&ACTG=0');selLeftMenu(this);">HPP</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','profit-lost-statement.php?BK_NBR=<?php echo $bookNumber; ?>&ACTG=0');selLeftMenu(this);">Laba Rugi</div>
<!--
<div class="leftmenu" onclick="changeSiblingUrl('content','profit-lost-year.php?ACTG=0&RL_TYP=RL_YEAR');selLeftMenu(this);">Laba Rugi Tahunan</div>
-->

<div class="leftmenu" onclick="changeSiblingUrl('content','equity.php?BK_NBR=<?php echo $bookNumber;?>&PLUS=<?php echo $lock;?>&ACTG=0');selLeftMenu(this);">Perubahan Modal</div>

<!--
<div class="leftmenu" onclick="changeSiblingUrl('content','cash-book.php?BK_NBR=<?php echo $bookNumber;?>&PLUS=<?php echo $lock;?>&ACTG=0');selLeftMenu(this);">Buku Kas</div>
-->

<div class="leftmenu" onclick="changeSiblingUrl('content','balance-report.php?BK_NBR=<?php echo $bookNumber;?>&PLUS=<?php echo $lock;?>&ACTG=0');selLeftMenu(this);">Neraca</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','accounting-posting.php?BK_NBR=<?php echo $bookNumber;?>');selLeftMenu(this);">Posting Data</div>

<div class="leftmenu" onclick="changeSiblingUrl('content','accounting-close-book.php');selLeftMenu(this);">Close Book</div>
</body>
</html>
