<?php

include_once '../framework/functions/crypt.php';
include_once '../framework/database/connect.php';

$UA = $_SERVER['HTTP_USER_AGENT'];
$Origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : "*";

header("Access-Control-Allow-Origin: ${Origin}");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

function checkLogin($userID, $password)
{
    $query = "SELECT SEC_KEY, PRSN_NBR, NAME 
             FROM CMP.PEOPLE PPL 
             INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP 
             WHERE PRSN_ID='" . $userID . "' 
             AND (PWD='" . $password . "' OR PWD='" . hash('sha512', $password) . "') 
             AND TERM_DTE IS NULL";
    $result = mysql_query($query);
    $row = mysql_fetch_assoc($result);
    if (mysql_num_rows($result) == 0) {
        return false;
    } else {
        return $row;
    }
}

function doLogin($postData)
{
    if ($row = checkLogin($postData['userID'], $postData['password'])) {
        session_start();
        $sess = session_id();
        $hostname = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'];
        header("Set-Cookie: PHPSESSID=${sess}; path=/; ${hostname}; HttpOnly; SameSite=None; Secure");
        $_SESSION['userID'] = $postData['userID'];
        $_SESSION['personNBR'] = $row['PRSN_NBR'];
        header("Location: ../index.php", true, 307);
    } else {
        header("Location: ../login.php", true, 307);
    }
}

function loginViaToken($token)
{
    $result = [];
    $data = simple_crypt($token, 'd');
    $token = explode("+", $data)[0];
    $time = explode("+", $data)[1];
    if ((time() - $time) < 21600) { // 6 hours
        doLogin(json_decode($token, true));
    } else {
        $result['time'] = time();
        $result['ptime'] = $time;
        $result['token'] = json_decode($token);
        $result['data'] = $data;
        $result['status'] = false;
        $result['error'] = 'timeout';
    }
    return $result;
}

$result = [
    'status' => true,
    'error'  => false,
    'post'   => $_POST,
    //        'server' => $_SERVER,
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $Action = isset($_GET['action']) ? $_GET['action'] : '';
    switch ($Action) {
        case "getToken":
            $userID = mysql_escape_string($_POST['userID']);
            $password = mysql_escape_string($_POST['password']);
            if (($userID != '') && ($password != '') && ($row = checkLogin($userID, $password))) {
                $newToken = json_encode($_POST) . "+" . time();
                $result['token'] = simple_crypt($newToken);
                $result['row'] = $row;
            } else {
                $result['status'] = false;
                $result['error'] = 'Identitas atau kata sandi salah.';
            }
            break;
        case "doLogin":
            array_merge($result, loginViaToken($_POST['session_token']));
            break;
        case "logout":
            $result['sess'] = session_id();
            session_destroy();
            $result['status'] = true;
            break;
        default:
            $result['status'] = false;
    }
} else {
    if (isset($_GET['action']) && isset($_GET['session_token'])) {
        array_merge($result, loginViaToken($_GET['session_token']));
    } else {
        $result = ['status' => false];
        http_response_code(404);
    }
}

echo json_encode($result);