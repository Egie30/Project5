<?php

include "framework/database/connect.php";
include "framework/functions/default.php";

$wifiCategoryNbr = 12;
$typ = $_REQUEST['TYP'] || 1;
$sqlValues = [];

function addSqlValues($Username, $Password, $typ)
{
    global $sqlValues;
    $sqlValues[] = "('$Username','$Password', $typ, " . $_SESSION['personNBR'] . ", NULL)";
}

// Read the imported data
$json = file_get_contents('php://input');
if ( ! empty($json)) {
    $jsUpload = true;
    $lists = json_decode($json);
    $durasi = $lists->durasi;
//    var_dump($lists->data);
    foreach ($lists->data as $list) {
        $Username = trim(explode(":", $list[0])[1]);
        $Password = trim(explode(":", $list[1])[1]);
        if ($Username == '' || $Password == '') {
            continue;
        }
        addSqlValues($Username, $Password, $durasi);
    }
}

// Handle if js upload error.
if ( ! empty($_FILES['excel'])) {
    var_dump($_FILES['excel']);
    $tmp_name = $_FILES['excel']['tmp_name'];
    require_once 'framework/phpExcel/Classes/PHPExcel.php';
    $objPHPExcel = PHPExcel_IOFactory::load($tmp_name);
    foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
        $sheetName = $worksheet->getTitle();
        $B = $worksheet->getHighestColumn(1);
        if ($B == "B") {
            $maxRows = $worksheet->getHighestRow();
            for ($i = 2; $i <= $maxRows; $i++) {
                $UsernameValue = $worksheet->getCell('A' . $i)->getValue();
                $Username = trim(explode(":", $UsernameValue)[1]);
                $PassValue = $worksheet->getCell('B' . $i)->getValue();
                $Password = trim(explode(":", $PassValue)[1]);
                addSqlValues($Username, $Password, $typ);
            }
        }
    }
}

if ( ! empty($sqlValues)) {
    $query = "INSERT INTO CMP.WIFI_VCHR(WIFI_UNM, WIFI_PWD, TYP_NBR, CRT_NBR, CRT_TS) VALUES ";
    $query .= implode(",", $sqlValues);
    $resp = mysql_query($query); // TODO: Handle duplicates
    if (isset($jsUpload)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => true, 'error' => '', 'query' => $resp]);
    } else {
        header("HTTP/1.1 303 See Other");
        header("Location: creativehub-voucher.php");
    }
    die();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta charset="utf-8">
    <script>if (top.Pace && !top.Pace.running) top.Pace.restart()</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="framework/tablesorter/themes/nestor/style.css">
    <link rel="stylesheet" href="framework/combobox/chosen.css">
    <script src="https://unpkg.com/read-excel-file@5.x/bundle/read-excel-file.min.js"></script>
    <script>
      let uploadData

      window.onload = () => {
        window.focus()
        if (top.Pace) top.Pace.stop()

        excelFile.addEventListener('change', function (e) {
          const selectedFile = this.files[0]
          readXlsxFile(selectedFile).then(function (rows) {
            // `rows` is an array of rows
            // each row being an array of cells.
            console.log(rows)
            if (rows.length > 0) {
              document.querySelector('#submit').classList.add('show')
              const h3 = document.querySelector('h3')
              h3.classList.add('show')
              h3.innerText = selectedFile.name
              populateTable(rows)
              uploadData = rows
            } else {
              alert('Gagal membaca file excel')
            }
          }).catch((err) => {
            document.querySelector('#submit').classList.remove('show')
            document.querySelector('h3').classList.remove('show')
            document.querySelector('#wrapper').classList.remove('show')
            alert(err)
          })
        })
      }

      function send (event, form) {
        const url = form.action || window.location
        const dur = form.DUR.value
        if (!dur) {
          alert('Silahkan pilih durasi')
          event.preventDefault()
          return
        }
        console.info(uploadData)
        fetch(url, {
          method: 'post',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ data: uploadData, durasi: dur }),
        }).then((response) => response.json()).then((resp) => {
          if (!!resp.error) {
            window.document.forms[0].innerHTML = error
          } else if (resp.query) {
            window.location.href = 'creativehub-voucher.php'
          }
        })
        event.preventDefault()
      }

      function populateTable (rows) {
        document.querySelector('#wrapper').classList.add('show')
        const tbl = document.querySelector('table')
        const tbd = tbl.querySelector('tbody')
        let i = 1
        rows.forEach((row) => {
          const rUsername = row[0].split(':')[1].trim()
          const rPassword = row[1].split(':')[1].trim()
          if (!!rUsername) {
            const tr = tbd.insertRow()
            const no = tr.insertCell()
            no.appendChild(document.createTextNode(`${i}`))
            const username = tr.insertCell()
            username.appendChild(document.createTextNode(`${rUsername}`))
            const password = tr.insertCell()
            password.appendChild(document.createTextNode(`${rPassword}`))
            i++
          }
        })
      }
    </script>
    <style>
        html {
            height: 100%;
        }

        #wrapper {
            width: 500px;
            height: 300px;
            overflow-y: scroll;
            display: none;
            resize: horizontal;
        }

        #wrapper.show,
        #submit.show,
        h3.show {
            display: block;
        }

        h3, #submit {
            display: none;
        }

        table thead th {
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 1;
            background: #fff;
        }

        table td {
            text-align: center;
        }

        table tr:nth-child(even) {
            background: #f6f6f6;
        }
    </style>
</head>
<body>
<form class="mainForm" action="" method="post" onsubmit="send(event, this)" enctype="multipart/form-data">
    <h2>Upload File Excel : Kode Voucher</h2>
    <select name="DUR" id="DUR" class="chosen-single">
        <?php
        $durQuery = 'SELECT * FROM CMP.RTL_ORD_TYP WHERE CAT_ID=' . $wifiCategoryNbr;
        genCombo($durQuery, 'RTL_ORD_TYP', 'RTL_ORD_DESC', '', 'Pilih durasi');
        ?>
    </select>
    <br>
    <div class="browse" onclick="document.getElementById('excelFile').click();">
        Browse ...
        <input class="browse" tabindex="-1" type=file id="excelFile" name="excel"
               accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
    </div>
    <h3>Preview data</h3>
    <div id="wrapper">
        <table>
            <thead>
            <tr>
                <th>No</th>
                <th>Username</th>
                <th>Password</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <input class="process" type="submit" id="submit" value="Import">
</form>
</body>

</html>
