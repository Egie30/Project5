<?php
// Automatic ComboBox Generator
// $query: SQL to get the list
// $key: Key column in the SQL
// $value: Value column in the SQL
// $default: Selected value in the combo box
// $blank: Empty selection, key will return a zero-length string by default
function genCombo($query, $key, $value, $default, $blank = "")
{
    if ($blank != "") {
        echo "<option value='' selected>$blank</option>";
    }
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result)) {
        echo "<option value='" . $row[$key] . "'";
        if ($row[$key] == $default) {
            echo " selected";
        }
        echo ">";
        echo removeExtraSpaces($row[$value]);
        echo "</option>";
    }
}

// $array
function genComboArrayVal($array, $default, $blank = "")
{
    if ($blank != "") {
        echo "<option value='$blank' selected>$blank</option>";
    }
    foreach ($array as $value) {
        echo "<option value='" . $value . "'";
        if ($value == $default) {
            echo " selected";
        }
        echo ">";
        echo removeExtraSpaces($value);
        echo "</option>";
    }
}

// Calendar fiscal year
function getFiscalYear()
{
    $curYear = date('Y');
    $lastYear = $curYear - 1;
    $nextYear = $curYear + 1;

    if (time() < mktime(0, 0, 0, 6, 1, $curYear, 1)) {
        $fiscalYear = "'" . $lastYear . "-06-01' AND '" . $curYear . "-05-31'";
    } else {
        $fiscalYear = "'" . $curYear . "-06-01' AND '" . $nextYear . "-05-31'";
    }
    return $fiscalYear;
}

// Timestamp related functions
function parseDate($timeStamp)
{
    return substr($timeStamp, 0, 10);
}

function parseDateShort($timeStamp)
{
    if ($timeStamp == "") {
        return "";
    } else {
        return substr($timeStamp, 8, 2) . "-" . substr($timeStamp, 5, 2) . "-" . substr($timeStamp, 2, 2);
    }
}

function parseYear($timeStamp)
{
    return substr($timeStamp, 0, 4);
}

function parseMonth($timeStamp)
{
    return substr($timeStamp, 5, 2);
}

function parseDateOnly($timeStamp)
{
    return substr($timeStamp, 8, 2);
}

function parseTime($timeStamp)
{
    return substr($timeStamp, 11, 8);
}

function parseTimeShort($timeStamp)
{
    return substr($timeStamp, 11, 5);
}

function parseHour($timeStamp)
{
    return substr($timeStamp, 11, 2);
}

function parseMinute($timeStamp)
{
    return substr($timeStamp, 14, 2);
}

//Remove extra spaces in the middle of the sentence
function removeExtraSpaces($text)
{
    $pattern = '/\s{2,}/';
    $replace = ' ';

    return preg_replace($pattern, $replace, $text);
}

//Crop name       12345678901234567890
//Maximum length: Isabella Onggowijaya --> 20 characters
function dispNameScreen($name)
{
    if (strlen($name) >= 20) {
        $pos = strrpos(substr($name, 0, 20), " ");
        if ($pos == "") {
            $pos = 20;
        }
    } else {
        $pos = 20;
    }
    $name = ucwords(substr($name, 0, $pos));
    return $name;
}

function roundTo($number, $to)
{
    return round($number / $to, 0) * $to;
}

function getParam($paramName, $paramNode)
{
    $xml = simplexml_load_file("data/param.xml");
    return $xml->$paramName->$paramNode;
}

function Luhn($number)
{
    $stack = 0;
    $number = str_split(strrev($number));
    foreach ($number as $key => $value) {
        if ($key % 2 == 0) {
            $value = array_sum(str_split($value * 2));
        }
        $stack += $value;
    }
    $stack %= 10;
    if ($stack != 0) {
        $stack -= 10;
        $stack = abs($stack);
    }
    $number = implode('', array_reverse($number));
    $number = $number . strval($stack);
    return $number;
}

function LuhnVal($number)
{
    $sum = 0;
    $alt = false;
    for ($i = strlen($number) - 1; $i >= 0; $i--) {
        $n = substr($number, $i, 1);
        if ($alt) {
            //square n
            $n *= 2;
            if ($n > 9) {
                //calculate remainder
                $n = ($n % 10) + 1;
            }
        }
        $sum += $n;
        $alt = ! $alt;
    }
    //echo $sum;
    //if $sum divides by 10 with no remainder then it's valid
    return ($sum % 10 == 0);
}

function shortName($longName)
{
    $names = explode(' ', $longName);
    $count = 1;
    foreach ($names as $name) {
        if ($count == 1) {
            $shortName = $name . " ";
        } else {
            $shortName .= substr($name, 0, 1);
        }
        $count++;
    }
    return $shortName;
}

function secs_to_h($secs)
{
    $units = array(
        "minggu" => 7 * 24 * 3600,
        "hari"   => 24 * 3600,
        "jam"    => 3600,
        "menit"  => 60,
        "detik"  => 1,
    );

    // specifically handle zero
    if ($secs == 0) {
        return "0 detik";
    }
    $s = "";
    foreach ($units as $name => $divisor) {
        if ($quot = intval($secs / $divisor)) {
            $s .= "$quot $name";
            $s .= (abs($quot) > 1 ? "" : "") . ", "; //fill with "s" if English
            $secs -= $quot * $divisor;
        }
    }

    return substr($s, 0, -2);
}

function bonusTenure($PrsnNbr, $days)
{
    $query = "SELECT HIRE_DTE FROM CMP.PEOPLE	WHERE PRSN_NBR=" . $PrsnNbr;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);
    $hireDate = $row['HIRE_DTE'];

    $tenure = date("Y-m-d", mktime(0, 0, 0, date('m'), date('d') - $days, date('Y')));
    if ($hireDate < $tenure) {
        return true;
    } else {
        return false;
    }
}

function humanTiming($time)
{
    $time = time() - $time; // to get the time since that moment
    $tokens = array(
        31536000 => 'tahun', //year
        2592000  => 'bulan',  //month
        604800   => 'minggu',  //week
        86400    => 'hari',     //day
        3600     => 'jam',       //hour
        60       => 'menit',       //minute
        1        => 'detik'         //second
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) {
            continue;
        }
        $numberOfUnits = floor($time / $unit);
        //return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':''); //deactivated for local language
        return $numberOfUnits . ' ' . $text;
    }
}

function getChildren($PrsnNbr)
{
    $query = "SELECT PRSN_NBR FROM CMP.PEOPLE WHERE MGR_NBR=$PrsnNbr AND DEL_NBR=0 AND TERM_DTE IS NULL";
    //echo $query."<br>";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result)) {
        $children .= $row['PRSN_NBR'] . ",";
        $children .= getChildren($row['PRSN_NBR']);
    }
    $children = substr($children, 0, -1);
    return $children;
}

function getInitials($words)
{
    $words = explode(" ", $words);
    $acronym = "";
    foreach ($words as $w) {
        $acronym .= strtoupper($w[0]);
    }
    return $acronym;
}

function FollowNull($value, $length)
{
    while (strlen($value) < $length) {
        $value .= '0';
    }

    return $value;
}

function parseNormalDate($timeStamp, $format = 'd-m-Y')
{
    return date($format, strtotime($timeStamp));
}

function parseDateTimeLiteralShort($timeStamp, $format = 'd-m-Y')
{
    $months = array("Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des");
    $days = array("Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu");
    $phpTimeStamp = strtotime($timeStamp);

    $today = new DateTime();
    $today->setTime(0, 0, 0);
    $match_date = DateTime::createFromFormat("Y-m-d H:i:s", $timeStamp);
    $match_date->setTime(0, 0, 0);
    $diff = $today->diff($match_date);
    $diffDays = (integer)$diff->format("%R%a");

    switch ($diffDays) {
        case 0:
            $weekday = "Hari ini";
            break;
        case -1:
            $weekday = "Kemarin";
            break;
        case +1:
            $weekday = "Besok";
            break;
        default:
            $weekday = $days[date('N', $phpTimeStamp) - 1];
    }
    return $weekday . " " . substr($timeStamp, 8, 2) . " " . $months[strval(substr($timeStamp, 5, 2)) - 1] . " "
        . substr($timeStamp, 11, 5);
}

function parseDateTimeline($timeStamp, $format = 'd-m-Y')
{
    $months = array("Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des");
    $days = array("Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu");
    $phpTimeStamp = strtotime($timeStamp);

    $today = new DateTime();
    $today->setTime(0, 0, 0);
    $match_date = DateTime::createFromFormat("Y-m-d H:i:s", $timeStamp);
    $match_date->setTime(0, 0, 0);
    $diff = $today->diff($match_date);
    $diffDays = (integer)$diff->format("%R%a");

    switch ($diffDays) {
        case 0:
            $weekday = "Hari ini";
            break;
        case -1:
            $weekday = "Kemarin";
            break;
        case +1:
            $weekday = "Besok";
            break;
        default:
            $weekday = $days[date('N', $phpTimeStamp) - 1];
    }
    return $weekday . " " . substr($timeStamp, 8, 2) . " " . $months[strval(substr($timeStamp, 5, 2)) - 1] . " "
        . substr($timeStamp, 11, 5);
}

function parseMonthName($timeStamp, $format = 'd-m-Y')
{
    $months = array("Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des");
    $phpTimeStamp = strtotime($timeStamp);

    $today = new DateTime();
    $today->setTime(0, 0, 0);
    $match_date = DateTime::createFromFormat("Y-m-d H:i:s", $timeStamp);
    $match_date->setTime(0, 0, 0);
    $diff = $today->diff($match_date);
    $diffDays = (integer)$diff->format("%R%a");

    switch ($diffDays) {
        case 0:
            $weekday = "Hari ini";
            break;
        case -1:
            $weekday = "Kemarin";
            break;
        case +1:
            $weekday = "Besok";
            break;
        default:
            $weekday = $days[date('N', $phpTimeStamp) - 1];
    }
    return $months[strval(substr($timeStamp, 5, 2)) - 1];
}

function getSubCompany($CoNbr)
{
    $query = "SELECT CO_NBR FROM CMP.COMPANY WHERE CO_NBR_PAR=$CoNbr AND DEL_NBR=0 ORDER BY CO_NBR";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result)) {
        $subCompany .= $row['CO_NBR'] . ",";
        $recursive = getSubCompany($row['CO_NBR']);
        if ($recursive != '') {
            $subCompany .= $recursive . ",";
        }
    }
    $subCompany = substr($subCompany, 0, -1);
    return $subCompany;
}

function getRootCompany($CoNbr)
{
    $subCompany = '';
    $query = "SELECT CO_NBR FROM CMP.COMPANY WHERE CO_NBR_PAR=$CoNbr AND DEL_NBR=0 ORDER BY CO_NBR";
    //echo $query."<br>";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result)) {
        $lastCompany = $row['CO_NBR'];
        if (hasChildCompany($lastCompany)) {
            $recursive = getRootCompany($lastCompany);
            $subCompany .= $recursive . ",";
        } else {
            $subCompany .= $lastCompany . ",";
        }
    }
    $subCompany = substr($subCompany, 0, -1);
    return $subCompany;
}

function hasChildCompany($CoNbr)
{
    $query = "SELECT CO_NBR FROM CMP.COMPANY WHERE CO_NBR_PAR=$CoNbr AND DEL_NBR=0";
    $result = mysql_query($query);
    if (mysql_num_rows($result) == 0) {
        return false;
    } else {
        return true;
    }
}

function generateUrl($CoNbr, $CoNbrDef)
{
    if ($CoNbr == $CoNbrDef) {
        $Url = '192.168.1.20';
    } else {
        $query = "SELECT CO_NBR,NAME FROM CMP.COMPANY WHERE CO_NBR=$CoNbr";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result);
        $Url = strtolower(str_replace(" ", ".", trim(str_replace('Champion', '', $row['NAME'])))) . '.champs.asia';
    }
    return $Url;
}

?>
