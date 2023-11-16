<?php

/*generate a random String
 * parameters:
 * length: length of the String (20 Sby default)
 */
function generateRandomString($length = 20)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generateRandomNumberString($length = 20)
{
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


//converts bools 1/null to 1/0 
function convertBoolToNumber($bool)
{
    if ($bool != "1") {
        $bool = 0;
    }
    return $bool;
}


function convertNumberToBool($number)
{
    if ($number == "1") {
        $bool = true;
    } else {
        $bool = false;
    }
    return $bool;
}

function convertBoolToText($bool)
{
    if ($bool != "1") {
        $bool = "false";
    } else {
        $bool = "true";
    }
    return $bool;
}

function convertTextToBool($text)
{
    if ($text == "true") {
        $bool = "1";
    } else {
        $bool = "0";
    }
    return $bool;
}


function convertNumberToText($number)
{
    if ($number == 1) {
        $text = "yes";
    } else {
        $text = "no";
    }
    return $text;
}

function convertTextToNumber($text)
{
    if ($text == "yes" || $text == "true") {
        $number = "1";
    } else {
        $number = "0";
    }
    return $number;
}






function check_in_range($start_date, $end_date, $date_from_user)
{
    // Convert to timestamp
    $start_ts = strtotime($start_date);
    $end_ts = strtotime($end_date);
    $user_ts = strtotime($date_from_user);

    // Check that user date is between start & end
    return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
}

/**
 * Checks if timespan1 contains timespan2
 * */
function timespan_contains($start_date1, $end_date1, $start_date2, $end_date2)
{

    $start_ts1 = strtotime($start_date1);
    $end_ts1 = strtotime($end_date1);
    $start_ts2 = strtotime($start_date2);
    $end_ts2 = strtotime($end_date2);

    return (($start_ts2 >= $start_ts1) && ($end_ts2 <= $end_ts1));
}

function timespans_overlap($from_date_1, $to_date_1, $from_date_2, $to_date_2)
{
    $from_date_1 = new DateTime($from_date_1);
    $to_date_1 = new DateTime($to_date_1);
    $from_date_2 = new DateTime($from_date_2);
    $to_date_2 = new DateTime($to_date_2);

    //DATE_1 IS AFTER DATE_2                                        //DATE_2 ist after DATE_1
    if (($from_date_1 >= $from_date_2 && $from_date_1 < $to_date_2) || ($from_date_2 >= $from_date_1 && $from_date_2 < $to_date_1)) return true;
    //"2019-11-29" > "2019-11-29" && "2019-11-29" < "2019-12-24"
    else return false;
}

function is_today_until_tomorrow($from_date, $to_date)
{
    $today = new DateTime();
    $today = $today->format('Y-m-d');

    $tomorrow = new DateTime();
    $tomorrow = $tomorrow->modify('+1 day');
    $tomorrow = $tomorrow->format('Y-m-d');

    if ($from_date == $today && $to_date == $tomorrow) {
        return true;
    }
    return false;
}

/**
 * Takes out passwords from the input://file content for auto logging
 */
function censor_password($input_contents)
{
    $post = (object)json_decode($input_contents);
    if (isset($post->password)) {
        $post->password = "****";
        $post->passwordRepeat = "****";
    }
    if (isset($post->pw)) {
        $post->pw = "****";
    }
    if (isset($post->user->password)) {
        $post->user->password = "****";
        $post->user->passwordRepeat = "****";
    }
    if (isset($post->accountData->password)) {
        $post->accountData->password = "****";
        $post->accountData->passwordRepeat = "****";
    }
    return json_encode($post);
}

function get_time_zone_approximation($longitude)
{

    return floor(($longitude - 7.000000001) / 15) + 1 + 1; //+1 for summer time
}

function format_number($number)
{
    return number_format($number, 2, '.', " ");
}


//  check if string is set and not empty
function notEmpty($text)
{
    if (isset($text) && $text != "") {
        return true;
    }
    return false;
}
