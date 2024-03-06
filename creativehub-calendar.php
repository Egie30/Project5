<?php
include_once ('framework/database/connect.php');
include_once ('framework/functions/default.php');
include_once ('framework/security/default.php');

$json = array();
$sql = "SELECT
	HED.ORD_NBR,
	ORD_DET_NBR,
	HED.ORD_TS,
    DET.BEG_TS,
    DET.END_TS,
	HED.BUY_CO_NBR,
	BUY.NAME,
	HED.ORD_STT_ID,
	STT.ORD_STT_DESC,
	HED.ORD_TTL,
	DET.RM_NBR,
	ROOM.RM_COLR,
	ROOM.RM_DESC
FROM CMP.RTL_ORD_HEAD HED
	INNER JOIN CMP.RTL_ORD_DET DET ON HED.ORD_NBR = DET.ORD_NBR
	INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
	INNER JOIN CMP.ROOM ROOM ON ROOM.RM_NBR = DET.RM_NBR
	LEFT JOIN CMP.COMPANY BUY ON HED.BUY_CO_NBR=BUY.CO_NBR
WHERE 
	HED.DEL_NBR= 0 
	AND DET.DEL_NBR= 0 
GROUP BY DET.ORD_DET_NBR DESC";

$show = mysql_query($sql);

while ($row = mysql_fetch_assoc($show)) {
    $json[] = array(
        'title' => date('H:i', strtotime($row['BEG_TS']))." ".$row['ORD_TTL']." ". $row['NAME'],
        'start' => date('Y-m-d H:i', strtotime($row['BEG_TS'])),
        'end' => date('Y-m-d H:i', strtotime($row['END_TS'])),
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
                    parent.document.getElementById('printDigitalPopupEditContent').src='creativehub-calendar-detail.php?start=' + event.start.format('YYYY-MM-DD');
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
