<?php

include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";
date_default_timezone_set("Asia/Jakarta");

//security
$Security = getSecurity($_SESSION['userID'], "Finance");

//Process filter
$OrdSttId = mysql_escape_string($_GET['STT']) ?: 'ALL';
$type = mysql_escape_string($_GET['TYP']);
$PrsnNbr = mysql_escape_string($_GET['PRSN_NBR']);

//Process auto detail display
$Goto = mysql_escape_string($_GET['GOTO']);

//Process delete entry
$delete = false;
if ($_GET['DEL'] != "") {
    $DEL_ORD_NBR = mysql_escape_string($_GET['DEL']);
    $query = "UPDATE CMP.RTL_ORD_HEAD SET DEL_NBR=" . $_SESSION['personNBR'] . " WHERE ORD_NBR=" . $DEL_ORD_NBR;
    //echo $query;
    $result = mysql_query($query);
    $OrdSttId = "ACT";
    $Goto = "TOP";
    $delete = true;

    $query = "UPDATE CMP.RTL_ORD_DET SET DEL_NBR=" . $_SESSION['personNBR'] . " WHERE ORD_NBR=" . $DEL_ORD_NBR;
    $result = mysql_query($query);
}
//Get active order parameter
//$activePeriod=getParam("print-digital","period-order-active-month");
//$badPeriod=getParam("print-digital","period-bad-order-month");
$activePeriod = 3;
$badPeriod = 12;
//Continue process filter
if ($OrdSttId == "DEL") {
    $where = "WHERE HED.ORD_STT_ID LIKE '%' AND HED.DEL_NBR<>0";
} elseif ($OrdSttId == "ALL") {
    $where = "WHERE HED.ORD_STT_ID LIKE '%' AND HED.DEL_NBR=0";
} elseif ($OrdSttId == "IBX") {
    $family = getChildren($_SESSION['personNBR']);
    if ($PrsnNbr == '') {
        $children = getChildren($_SESSION['personNBR']);
        if ($children != '') {
            $children = $_SESSION['personNBR'] . ',' . $children;
        } else {
            $children = $_SESSION['personNBR'];
        }
    } else {
        $children = $PrsnNbr;
    }
    $where = "WHERE HED.ORD_STT_ID!='CP' AND (HED.ORD_NBR IN (SELECT ORD_NBR FROM CMP.JRN_PRN_DIG WHERE CRT_NBR IN ("
        . $children . ") GROUP BY ORD_NBR) OR CRT_NBR IN (" . $children . ") OR HED.UPD_NBR IN (" . $children
        . ") OR SLS_PRSN_NBR IN (" . $children . ") OR ACCT_EXEC_NBR IN (" . $children . ")) AND HED.DEL_NBR=0";
} elseif ($OrdSttId == "ACT") {
    $where
        = "WHERE (HED.ORD_STT_ID!='CP' OR (HED.ORD_STT_ID='CP' AND TIMESTAMPADD(MONTH,$activePeriod,ORD_BEG_TS)>=CURRENT_TIMESTAMP 
        AND LAST_DAY(DATE_ADD(ORD_BEG_TS,INTERVAL COALESCE(COM.PAY_TERM,32) DAY))>=CURRENT_DATE) 
        OR (HED.ORD_STT_ID!='CP' AND TOT_REM>0 AND TIMESTAMPADD(MONTH,$badPeriod,ORD_BEG_TS)>=CURRENT_TIMESTAMP)) 
        AND HED.DEL_NBR=0";
} elseif ($OrdSttId == "SLM") {
    $where
        = "WHERE HED.ORD_STT_ID!='CP' AND JRN.CRT_TS <= NOW() - INTERVAL 48 HOUR AND HED.DEL_NBR=0 GROUP BY HED.ORD_NBR";
} elseif ($OrdSttId == "CP") {
    $where
        = "WHERE HED.ORD_STT_ID='CP' AND TIMESTAMPADD(MONTH,$activePeriod,ORD_BEG_TS)>=CURRENT_TIMESTAMP AND HED.DEL_NBR=0";
} elseif ($OrdSttId == "DUE") {
    $where
        = "WHERE TOT_REM>0 AND DATE_ADD(CMP_TS,INTERVAL COALESCE(COM.PAY_TERM,0) DAY)<=CURRENT_TIMESTAMP AND HED.DEL_NBR=0";
} elseif ($OrdSttId == "COL") {
    $buyPrsnNbr = $_GET['BUY_PRSN_NBR'];
    $buyCoNbr = $_GET['BUY_CO_NBR'];
    if ($buyCoNbr != "") {
        $whereString = " AND BUY_CO_NBR=" . $buyCoNbr;
        if ($buyPrsnNbr != "") {
            $whereString .= " AND BUY_PRSN_NBR=" . $buyPrsnNbr;
        }
    } else {
        if ($buyPrsnNbr != "") {
            $whereString = " AND BUY_PRSN_NBR=" . $buyPrsnNbr;
        }
    }
    if (($buyPrsnNbr == "0") && ($buyCoNbr == "0")) {
        $whereString = " AND (BUY_CO_NBR IS NULL AND BUY_PRSN_NBR IS NULL)";
    }
    $where = "WHERE HED.DEL_NBR=0 " . $whereString . " AND YEAR(ORD_TS)=" . $_GET['YEAR'] . " AND MONTH(ORD_TS)="
        . $_GET['MONTH'] . " AND TOT_REM>0";
} elseif ($OrdSttId == "DLO") {
    $where = "WHERE HED.ORD_STT_ID!='CP' AND DL_CNT>0 AND HED.DEL_NBR=0";
} elseif ($OrdSttId == "POD") {
    $where
        = "WHERE HED.ORD_STT_ID ='CP' AND TOT_REM>0 AND LAST_DAY(DATE_ADD(ORD_TS,INTERVAL COALESCE(COM.PAY_TERM,32) DAY))<=CURRENT_DATE AND HED.DEL_NBR=0";
} else {
    $where = "WHERE HED.ORD_STT_ID='" . $OrdSttId . "' AND HED.DEL_NBR=0";
}

$select = "SELECT HED.ORD_NBR,
	HED.ORD_STT_ID,
	STT.ORD_STT_DESC,
	PPL.NAME AS NAME_PPL,
	COM.NAME AS NAME_CO,
	HED.REF_NBR,HED.ORD_TTL,
	HED.DUE_TS,
	HED.FEE_MISC,
	HED.TOT_AMT,
	HED.PYMT_DOWN,
	HED.PYMT_REM,
	HED.TOT_REM,
	HED.CMP_TS,
	HED.CRT_TS,
	HED.CRT_NBR,
	HED.UPD_TS,
	HED.UPD_NBR,
	HED.SPC_NTE, 
	HED.IVC_PRN_CNT,
	TIMESTAMPDIFF(HOUR,JRN.CRT_TS,NOW()) AS SLM_HRS,
	DATEDIFF(DATE_ADD(HED.CMP_TS,INTERVAL COALESCE(COM.PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS PAST_DUE
FROM CMP.RTL_ORD_HEAD HED
	INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR 
	LEFT OUTER JOIN(
		SELECT
			ORD_NBR,
			MAX(CRT_TS) AS CRT_TS
		FROM CMP.RTL_ORD_JRN
		GROUP BY ORD_NBR
	)JRN ON HED.ORD_NBR=JRN.ORD_NBR
	$where";

/* Capture ajax, scroll and search request */
if ($_GET["LAST_ID"]) {
    $last_id = mysql_escape_string($_GET["LAST_ID"]);
    $query = $select . " AND HED.ORD_NBR < " . $last_id . " ORDER BY HED.UPD_TS DESC LIMIT 10";
    //echo "<pre>" . $query . "</pre>";
    $result = mysql_query($query);
    include("creativehub-ls-template.php");
    exit();
} else {
    if ($_GET["s"]) {
        $s = mysql_escape_string($_GET['s']);
        $query = $select . " AND PPL.NAME LIKE '%" . $s . "%' 
    OR COM.NAME LIKE '%" . $s . "%' 
    ORDER BY HED.UPD_TS DESC";
        //echo "<pre>" . $query . "</pre>";
        $result = mysql_query($query);
        include("creativehub-ls-template.php");
        exit();
    } else {
        if ($_GET["REFRESH"]) {
            $nbr = mysql_escape_string($_GET['REFRESH']);
            $query = $select . " AND HED.ORD_NBR = " . $nbr . " LIMIT 1";
            $result = mysql_query($query);
            include("creativehub-ls-template.php");
            exit();
        } else {
            if ( ! in_array($Goto, array("", "TOP"))) {
                $query = $select . " AND HED.ORD_NBR IN ";
                $query .= "(SELECT O.* FROM (
                    (SELECT ORD_NBR FROM CMP.RTL_ORD_HEAD WHERE ORD_NBR < " . $Goto . " AND ORD_STT_ID='" . $OrdSttId . "' ORDER BY 1 DESC LIMIT 10)
                    UNION
                    (SELECT ORD_NBR FROM CMP.RTL_ORD_HEAD WHERE ORD_NBR >= " . $Goto . " AND ORD_STT_ID='" . $OrdSttId . "' ORDER BY 1 ASC)
                    ) AS O ORDER BY 1)";
                $query .= " ORDER BY HED.UPD_TS DESC";
            } else {
                $query = $select . " ORDER BY HED.UPD_TS DESC LIMIT 20";
            }
            //echo "<pre>" . $query . "</pre>";
            $result = mysql_query($query);
            $count = mysql_num_rows($result);
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta charset="UTF-8">
    <script>if (top.Pace && !top.Pace.running) top.Pace.restart()</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <script src="framework/functions/default.js"></script>
    <script src="framework/database/jquery.min.js"></script>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
    <style>
        html {
            scroll-behavior: smooth;
            overflow: hidden;
        }

        ::-webkit-scrollbar {
            width: 7px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
            background: #aaa;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
            background: #888;
        }

        .mainResult {
            height: calc(100% - 41px);
            -webkit-overflow-scrolling: touch !important;
            overflow-y: auto !important;
            overflow-x: hidden;
            scroll-behavior: smooth;
            scrollbar-width: thin;
        }

        .mainResult .listable {
            display: inline;
            float: right;
        }

        .mainResult .tripane-list {
            margin-right: 10px;
        }

        .mainResult .tripane-list.active {
            background-color: #eef8fb;
        }

        .mainResult .order-number {
            font-weight: bold;
            color: #666666;
            font-size: 12pt;
            display: inline;
        }

        .mainResult .order-title {
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .mainResult .order-time {
            display: inline;
            float: right;
        }

        .mainResult .order-icons {
            display: block;
            height: 8px;
            padding-top: 8px;
        }

        .mainResult .order-icons div.listable,
        .mainResult .order-icons span {
            float: left;
        }

        .mainResult .order-name {
            font-weight: 700;
            color: #3464bc;
            padding-top: 10px;
        }

        .mainResult .order-name span.fa-circle {
            display: none;
        }

        .mainResult .order-name span.red {
            display: inline;
            color: #d92115;
        }

        .mainResult .order-name span.yellow {
            display: inline;
            color: #fbad06;
        }

        .mainResult .order-status {
            font-weight: 700;
        }

        .mainResult .order-date {
            display: inline;
        }

        .mainResult .order-total {
            float: right;
        }

        .mainResult .payment-status {
            font-size: 8pt;
            color: #3464bc;
            padding-right: 5px;
            padding-top: 3px;
        }

        div.toolbar {
            padding-bottom: 10px;
            position: sticky;
            top: 0;
            border-bottom: 1px solid #f1f1f1;
        }

        p.toolbar-left span {
            cursor: pointer;
        }

        p.toolbar-left span:hover {
            color: #3464bc;
        }

        p.toolbar-right {
            margin-right: 100px;
        }

        p.toolbar-right span {
            cursor: default;
        }

        p.toolbar-right #livesearch {
            width: 150px;
        }

        p.toolbar-right select {
            background: white;
            border-radius: 12px;
            padding: 2px 10px;
        }

        .searchresult {
            display: none;
            height: calc(100% - 41px);
            -webkit-overflow-scrolling: touch !important;
        }

        .preloader {
            display: none;
            text-align: center;
            width: 100%;
            position: absolute;
            bottom: 27px;
        }

        .preloader .spinner {
            margin: auto;
        }

        /* goTop button */
        #goTop {
            visibility: hidden;
            opacity: 0; /* Hidden by default */
            transition: visibility 0.3s linear, opacity 0.3s linear;
            position: fixed; /* Fixed/sticky position */
            bottom: 20px; /* Place the button at the bottom of the page */
            right: 30px; /* Place the button 30px from the right */
            z-index: 99; /* Make sure it does not overlap */
            border: none; /* Remove borders */
            outline: none; /* Remove outline */
            color: white; /* Text color */
            cursor: pointer; /* Add a mouse pointer on hover */
            padding: 5px; /* Some padding */
            border-radius: 3px; /* Rounded corners */
            font-size: 18px; /* Increase font size */
            background-color: #3464bc17;
        }

        #goTop:hover {
            background-color: #3464bc; /* Add a dark-grey background on hover */
        }
    </style>

</head>

<body>
<div class="toolbar">
    <?php
    if ( ! in_array($OrdSttId, array('SLM', 'POD'))) { ?>
        <p class="toolbar-left">
            <span class='fa fa-plus toolbar add' title="Tambah Nota"></span>
        </p>
        <?php
    } ?>
    <p class="toolbar-right">
        <span class='fa fa-search fa-flip-horizontal toolbar'></span>
        <input type="search" id="livesearch" class="livesearch" placeholder="Cari" autocomplete="off"
               spellcheck="false"/>
        <?php
        if ($OrdSttId == "ALL" || $_GET['FILTER']) { ?>
            <select name="ORD_STT_ID" id="ORD_STT_ID" class="chosen-select" title="Filter Status">
                <?php
                $stt_query = "SELECT STT.ORD_STT_ID, STT.ORD_STT_DESC, STT.ORD_STT_ORD FROM CMP.RTL_ORD_STT STT";
                genCombo($stt_query, "ORD_STT_ID", "ORD_STT_DESC", $OrdSttId, "Filter Status");
                ?>
            </select>
            <?php
        } ?>
    </p>
</div>
<div class="searchresult mainResult" id="liveRequestResults"></div>
<div id="mainResult" class="mainResult">
    <?php
    if ($count > 0) {
        include("creativehub-ls-template.php");
    } else {
        echo "<div class='searchStatus'>Data atau nomor belum ada didalam kumpulan data</div>";
    }
    ?>
</div>
<button onclick="goTop()" id="goTop" title="Go to top"><i class="fa fa-arrow-up"></i></button>
<div class="preloader">
    <div class='spinner'>
        <div class='double-bounce1'></div>
        <div class='double-bounce2'></div>
    </div>
</div>
<script>
  /* goTop button */
  // Get the button:
  const gotTopBtn = document.getElementById('goTop')
  const scrolledElement = document.getElementById('mainResult')

  // When the user scrolls down 20px from the top of the document, show the button
  scrolledElement.onscroll = function () {
    scrollFunction()
  }

  function scrollFunction () {
    if (scrolledElement.scrollTop > 20) {
      gotTopBtn.style.visibility = 'visible'
      gotTopBtn.style.opacity = '1'
    } else {
      gotTopBtn.style.visibility = 'hidden'
      gotTopBtn.style.opacity = '0'
    }
  }

  // When the user clicks on the button, scroll to the top of the document
  function goTop () {
    scrolledElement.scrollTop = 0
  }
</script>
<script>
  const goto = "<?php echo $Goto; ?>"
  const ord_stt = "<?php echo $OrdSttId; ?>"
  const ord_type = "<?php echo $type; ?>"
  const del = "<?php echo $delete; ?>"

  if (del == '1') {
    top.leftmenu.contentWindow.reload()
    let url = new URL(window.location)
    url.searchParams.delete('DEL')
    window.location = url.href
  }

  if (goto === 'TOP') {
    let $el = $('#mainResult div.tripane-list:first-child')
    let firstNbr = $el.data('order')
    changeSibling(firstNbr)
    selLeftPaneEx($el)
  } else if (goto !== '') {
    selLeftPaneByOrder(goto)
  }

  function findByOrder (ord) {
    return $('.tripane-list').filter(function () {
      return $(this).data('order') == ord
    })
  }

  function selLeftPaneByOrder (ord) {
    const el = findByOrder(ord)
    selLeftPaneEx(el)
  }

  function selLeftPaneEx (element) {
    deSelLeftPaneEx()
    $(element).addClass('active')

    // Scroll if not in view
    const $main = $('#mainResult')
    const el = $(element).get(0)
    // const {top, left, bottom, right} = el.getBoundingClientRect();
    const top = $(element).offset()?.top
    const offset = $('div.toolbar').outerHeight()
    // console.info(`element.top: ${top}, offset: ${offset}`)
    if (top < 0 || $main.innerHeight() + $main.scrollTop() < top) {
      console.info(`main.innerHeight: ${$main.innerHeight()}, main.scrollTop: ${$main.scrollTop()}`)
      // window.scrollTo({top: top + offset, behavior: "smooth"});
      // main.animate({ scrollTop: top - offset })
      el.scrollIntoView(true)
    }
  }

  function deSelLeftPaneEx () {
    $('div.tripane-list').each(function (i, el) {
      $(el).removeClass('active')
    })
  }

  function changeSibling (NBR) {
    let url = new URL('creativehub-edit.php', window.location)
    url.searchParams.append('ORD_NBR', NBR)
    url.searchParams.append('STT', ord_stt)
    url.searchParams.append('TYP', ord_type)
    try {
      let sib_url = parent.rightpane.contentWindow.location.href
      if (sib_url !== url.href) {
        changeSiblingUrl('rightpane', url.href)
      } else {
        if (top.Pace) top.Pace.stop()
      }
    } catch {
      changeSiblingUrl('rightpane', url.href)
    }

  }

  function changeStatus (STT, GOTO) {
    top.leftmenu.contentWindow.reload()
    let url = new URL(window.location)
    url.searchParams.set('STT', STT)
    url.searchParams.set('GOTO', GOTO)
    window.location = url.href
  }

  $('span.add').on('click', function () {
    changeSibling(-1)
    deSelLeftPaneEx()
    document.documentElement.scrollTop = 0
  })


</script>
<script type="text/javascript">
  let searchStr = ''
  $('#livesearch').on('change keyup', function (evt) {
    if (evt.key === 'Escape' || evt.key === 'Enter') {
      $(this).trigger('blur')
      return
    }
    let s = this.value
    let url = new URL(window.location)
    url.searchParams.append('s', s)
    if (s === searchStr) {
      return
    }
    if (s != '') {
      searchStr = s
      $('.preloader').css('top', $('#mainResult').innerHeight() / 2)
      callAjax(url.href, function (data) {
        //show data
        $('#mainResult').hide()
        $('#liveRequestResults').fadeIn(100).html(data)
        $('.preloader').fadeOut()
      })
    } else {
      //reset
      $('.preloader').css('top', 'auto')
      $('#mainResult').show()
      $('#liveRequestResults').hide()
    }
  })

  //when page scrolled to end of page
  //call function loadMoreData
  var last_req_id = -1
  $('#mainResult, #liveRequestResults').scroll(function () {
    if ($(this).scrollTop() + $(this).height() >= $(this).prop('scrollHeight')) {
      let last_id = $('.tripane-list:last').data('order')
      if (last_req_id !== last_id) {
        last_req_id = last_id
        if ($('#mainResult').is(':visible')) {
          loadMoreData(last_id)
        }
      }
    }
  })

  //loadMoreData will call when page scrolled to end of the page
  function loadMoreData (last_id) {
    let url = new URL(window.location)
    url.searchParams.append('LAST_ID', last_id)
    callAjax(url.href, function (data) {
      //show data
      let newData = $(data)
      newData.hide()
      $('#mainResult').append(newData)
      newData.slideDown('slow')
      $('.preloader').fadeOut('slow')
    })
  }

  function callAjax (url, callback) {
    return $.ajax({
      url: url,
      type: 'get',
      beforeSend: function () {
        return $('.preloader').show()
      },
    }).done(callback).fail(function (jqXHR, ajaxOptions, thrownError) {
      alert('server not responding...')
    })
  }

  /* Call this from other page to reload the order
  *  `top.content.leftpane.contentWindow.updateInPlace(5)`  */
  function updateInPlace (nbr) {
    if (!nbr) {
      nbr = $('div.tripane-list.active').data('order')
    }
    let url = new URL(window.location)
    url.searchParams.set('REFRESH', nbr)
    let ord = findByOrder(nbr)
    // ord.load(url.href + ' #O' + nbr, function () {
    //   selLeftPaneEx(findByOrder(nbr))
    // })

    $.get(url.href, function (data) {
      let old = ord.replaceWith(data)
      selLeftPaneEx(findByOrder(nbr))
    })
  }

  function findByOrder (nbr) {
    // $('div.tripane-list[data-order=' + goto + ']');
    return $('div.tripane-list').filter(function () {
      return $(this).data('order') == nbr
    })
  }

  $('document').ready(function ($) {
    $('#mainResult, #liveRequestResults').on('click.handler', 'div.tripane-list', function () {
      let order = $(this).data('order')
      changeSibling(order)
      selLeftPaneEx(this)
    })

    $('#ORD_STT_ID').on('change', function () {
      let url = new URL(window.location)
      url.searchParams.set('STT', this.value)
      url.searchParams.set('FILTER', true)
      window.location = url.href
    })
  })
</script>

</body>
</html>