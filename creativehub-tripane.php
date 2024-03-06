<?php

$OrdSttId = mysql_escape_string($_GET['STT']);
$type = isset($_GET['TYP']) ? mysql_escape_string($_GET['TYP']) : '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta charset="UTF-8">
    <script type="text/javascript">if (parent.Pace) parent.Pace.restart()</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <style>
        td.leftpane-adjust {
            width: 250px;
        }

        td.rightpane-adjust {
            padding-left: 10px;
            border-bottom: 0;
            border-left: #dddddd 1px solid;
            -webkit-overflow-scrolling: touch;
        }

        div.rightpane-adjust,
        div.leftpane-adjust {
            min-width: 10%;
            width: 100%;
            height: 100%;
            overflow: hidden;
            -webkit-overflow-scrolling: touch;
            /*resize: horizontal;*/
        }

        table.pane-adjust {
            width: 100%;
            height: 100%;
            padding: 0;
        }

        table.pane-adjust tr {
            height: 100%;
        }

        @media only screen and (min-width: 1105px) {
            td.leftpane-adjust {
                min-width: 10%;
                width: 30%;
            }

            td.rightpane-adjust {
                width: 90%;
            }
        }

        iframe#rightpane {
            height: calc(100% - 3px);
            width: 100%;
            border-right: 10px;
            overflow: hidden;
        }

        iframe#leftpane {
            width: 100%;
            overflow: hidden;
            height: calc(100% - 4px);
        }

        iframe {
            border: none;
        }

        .gutter {
            display: none;
            cursor: ew-resize;
            width: 1px;
            height: 100%;
            float: left;
            position: relative;
            padding: 0 2px 0 2px;
        }
    </style>
    <title>Creative Hub</title>
</head>
<body>
<table class="pane-adjust">
    <tr>
        <td class="leftpane-adjust">
            <div class="leftpane-adjust">
                <iframe id="leftpane"
                        src="creativehub-ls.php?STT=<?php echo $OrdSttId . "&TYP=" . $type . "&GOTO=TOP" ?>"
                        onmouseover="this.focus();"></iframe>
            </div>
        </td>
        <td class="gutter"></td>
        <td class="rightpane-adjust">
            <div class="rightpane-adjust">
                <iframe id="rightpane" src="creativehub-edit.php"></iframe>
            </div>
        </td>
    </tr>
</table>
<script>
  (function ($) {
    $('.gutter', document).on('mousedown', function (e) {
      $(this).css('cursor', 'col-resize')
      $(this).on('mousemove', function (e) {
        let x1 = e.pageX
        let x0 = $(this).data('x0') || x1
        console.log(x1)
        $(this).data('x0', x1)
      })
    }).on('mouseup', function () {
      $(this).off('mousemove')
      $(this).css('cursor', 'ew-resize')
    })
  })(top.jQuery)
</script>
</body>
</html>