<?php

include "framework/database/connect.php";
include "framework/security/default.php";
$Security = getSecurity($_SESSION['userID'], "Executive");

$query = "SELECT 
		STT.ORD_STT_ORD,
		STT.ORD_STT_ID,
		STT.ORD_STT_DESC,
		STT.ORD_STT_DET_F,
		COALESCE(COUNT(*),0) AS STT_NBR,
		SUM(CNT_NBR) AS CNT_NBR
	FROM CMP.RTL_ORD_STT STT
		LEFT OUTER JOIN CMP.RTL_ORD_HEAD HED ON STT.ORD_STT_ID=HED.ORD_STT_ID
		LEFT OUTER JOIN(
			SELECT
				ORD_NBR,
				COUNT(DISTINCT ORD_NBR) AS CNT_NBR
			FROM CMP.RTL_ORD_DET
			WHERE DEL_NBR = 0
			GROUP BY ORD_NBR
		)DET ON HED.ORD_NBR = DET.ORD_NBR
	WHERE HED.ORD_NBR IS NOT NULL AND STT.ORD_STT_ID!='CP' AND DEL_NBR=0
	GROUP BY 1,2,3,4
	ORDER BY 1"
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <script type="text/javascript" src="framework/functions/default.js"></script>
    <script type="text/javascript">if (top.Pace) top.Pace.restart()</script>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
    <script>
      window.onload = function () {
        document.addEventListener('click', function (evt) {
          let node = evt.target.closest('div[class^=leftmenu]')
          changeSiblingUrl('content', node.dataset.target)
          selLeftMenu(node)
          top.document.title = title + ' | ' + getTextContent(node)
        })
      }

      function getTextContent (n) {
        return Array.prototype.filter.
          call(n.childNodes, el => el.nodeType === Node.TEXT_NODE).
          map(x => x.textContent).
          join('').
          trim()
      }

      function selectByUrl (url) {
        document.querySelectorAll('div[class^=leftmenu]').forEach((el) => {
          if (el.dataset.target === url) {
            selLeftMenu(el)
          }
        })
      }

      /* Call this from other page to reload this page synchronously
        `top.leftmenu.contentWindow.reload()` */
      async function reload (callback) {
        const currentsel = document.querySelector('.leftmenusel').dataset.target
        const response = await fetch(window.location)
        const str = await response.text()
        const html = new DOMParser().parseFromString(str, 'text/html')
        document.body.replaceWith(html.body)
        selectByUrl(currentsel)
        if (typeof callback === 'function') callback(window)
      }

    </script>
</head>
<body class="sub" id="body">
<div id="proforma" class="leftmenusel" data-target="creativehub-tripane.php?STT=ACT">
    <span class="fa fa-fw fa-folder-open leftmenuicon"></span>Aktif
</div>
<!--
<div class="leftmenu" data-target="creativehub-tripane.php?STT=ALL">
    <span class="fa fa-fw fa-list-alt leftmenuicon"></span>Semua Nota
</div>
-->
<?php
$result = mysql_query($query);
while ($row = mysql_fetch_array($result)) {
    if ($row['ORD_STT_DET_F'] == 1) {
        // FIXME: What does it mean?
    } else { ?>
        <div class="leftmenu"
             data-target="creativehub-tripane.php?STT=<?php echo $row['ORD_STT_ID'] ?>">
            <span class="fa fa-fw fa-<?php echo returnIcon($row['ORD_STT_ID']) ?> leftmenuicon"></span><?php echo $row['ORD_STT_DESC'] ?>
            <span class='badge'><?php echo $row['STT_NBR'] ?></span>
        </div>
        <?php
    }
} ?>
<div class="leftmenu"
     data-target="creativehub-tripane.php?STT=CP">
    <span class="fa fa-fw fa-check leftmenuicon"></span>Selesai
    <!--    <span class='badge'></span>-->
</div>
<div class="leftmenu" data-target="creativehub-list.php?STT=ACT">
    <span class="fa fa-fw fa-list leftmenuicon"></span>Daftar
</div>
<div class="leftmenu" data-target="creativehub-list.php?STT=ALL">
    <span class="fa fa-fw fa-globe leftmenuicon"></span>Semua
</div>
<div class="leftmenu" data-target="creativehub-receivables.php">
    <span class="fa fa-fw fa-download leftmenuicon"></span>Receivables
</div>
<div class="leftmenu" data-target="creativehub-report-customer.php">
    <span class="fa fa-fw fa-clipboard leftmenuicon"></span>Laporan Customer
</div>
<div class="leftmenu" data-target="creativehub-report-sales.php">
    <span class="fa fa-fw fa-hand-peace-o leftmenuicon"></span>Laporan Sales
</div>
<div class="leftmenu" data-target="creativehub-service-type.php">
    <span class="fa fa-fw fa-gear leftmenuicon"></span>Layanan
</div>
<div class="leftmenu" data-target="creativehub-rooms.php">
    <span class="fa fa-fw fa-cube leftmenuicon"></span>Room
</div>
<div class="leftmenu" data-target="creativehub-tables.php">
    <span class="fa fa-fw fa-database leftmenuicon"></span>Table
</div>
<div class="leftmenu" data-target="creativehub-voucher.php">
    <span class="fa fa-fw fa-wifi leftmenuicon"></span>Voucher Wi-Fi
</div>
<div class="leftmenu" data-target="creativehub-calendar.php">
    <span class="fa fa-fw fa-calendar leftmenuicon"></span>Kalender
</div>
<div class="leftmenu" data-target="creative-hub-bonus-report.php">
    <span class="fa fa-fw fa-calendar leftmenuicon"></span>Bonus
</div>
</body>
</html>

<?php
function returnIcon($status){
	switch($status) {
	case "NE":
		return "file-o";
	break;
	case "RV":
		return "picture-o";
	break;
	case "IV":
		return "object-group";
	break;
	case "PY":
		return "thumbs-up";
	break;
	case "PR":
		return "print";
	break;
	case "RD":
		return "align-justify";
	break;
	case "DR":
		return "truck";
	break;
	case "CP":
		return "check";
	break;
	default:
		return "circle-thin";
	}
}
?>