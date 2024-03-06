<?php
include_once ('framework/database/connect.php');
include_once ('framework/functions/default.php');
include_once ('framework/security/default.php');

$json = array();
$sql = "SELECT
	HED.ORD_NBR, 
	DET.ORD_NBR,
	DET.ORD_DET_NBR,
	HED.ORD_TS, 
	HED.ORD_STT_ID, 
	DET.DET_TTL,
	SUM(TOT_SUB) AS TOT_SUB,
	ROM.RM_NBR,
	ROM.RM_COLR,
	BUY.NAME,
	HED.ORD_TTL, 
	PPL.NAME AS NAME_PPL,
	STT.ORD_STT_DESC
FROM CMP.RTL_ORD_HEAD HED
	INNER JOIN CMP.RTL_ORD_DET DET ON DET.ORD_NBR = HED.ORD_NBR
	INNER JOIN CMP.ROOM ROM ON DET.RM_NBR=ROM.RM_NBR
	LEFT OUTER JOIN CMP.COMPANY BUY ON HED.BUY_CO_NBR=BUY.CO_NBR
	INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
WHERE HED.DEL_NBR = 0 
GROUP BY HED.ORD_NBR DESC";

$show = mysql_query($sql);

while ($row = mysql_fetch_assoc($show)) {
    $json[] = array(
        'title' => date('H:i', strtotime($row['ORD_TS']))." ".$row['ORD_TTL']." ". $row['NAME'],
        'start' => date('Y-m-d', strtotime($row['ORD_TS'])),
        'ord_nbr' => $row['ORD_NBR'], 
        'det_ord_nbr' => $row['ORD_DET_NBR'], 
        'color' => $row['RM_COLR'] 
    );
}
echo json_encode($show);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta charset="UTF-8">
    <script>if (top.Pace) top.Pace.restart()</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />
    <link rel="stylesheet" href="framework/fullcalendar/fullcalendar.min.css" />
    <script src="framework/fullcalendar/jquery.min.js"></script>
    <script src="framework/fullcalendar/moment.js"></script>
    <script src="framework/fullcalendar/fullcalendar.min.js"></script>
    <script>
        $(document).ready(function() {
            var calendar = $('#calendar').fullCalendar({
                height: 480,
                editable : false,
                selectable : true,
                selectHelper : true,
                header: {
                    left :'prev,next today',
                    center : 'title',
                    right : 'month,agendaWeek,agendaDay'
                },
                buttontext: {
                    today: 'today',
                    month: 'month',
                    week: 'week',
                    day: 'day'
                },
                events: <?php echo json_encode($json); ?>,
                eventClick: function(event) {
                    parent.document.getElementById('printDigitalPopupEditContent').src='tampilan.php?start=' + event.start.format('YYYY-MM-DD');
                    parent.document.getElementById('printDigitalPopupEdit').style.display='block';
                    parent.document.getElementById('fade').style.display='block'; 
                },
                eventRender: function(event, element) {
                    // Menambahkan warna sesuai dengan RM_COLR
                    element.css('background-color', event.color);
                }
            });
        });
    </script>
    <style>
        body {
            margin: 40px 10px;
            padding: 0;
            font-family: 'San Francisco Display', 'HelveticaNeue', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
        }
        #calendar {
            max-width: 1100px;
            max-height: 200px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div id="calendar" style="margin: 40px;"></div>
    <div id="printDigitalPopupEditContent">
</body>
</html>
