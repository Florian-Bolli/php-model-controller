<?php

require_once __DIR__ . '/../../models/VerificationCodes.php';
require_once __DIR__ . '/../../models/Boats.php';
require_once __DIR__ . '/../../functions/mailer.php';
require_once __DIR__ . '/../../functions/bookings/booking_utilities.php';
require_once __DIR__ . '/../../functions/smssender.php';
require_once __DIR__ . '/../../functions/utilfunctions.php';
require_once __DIR__ . '/../../translations/translations.php';


class AccountUtilities
{

    public static function correct_input_data($account)
    {
        // Correct input data
        $account->email = self::correct_input_email($account->email);
        $account->mobilenumber = self::correct_input_mobile($account->mobilenumber);
        $account->role = strtolower($account->role);
        // TODO: correct input language in every case
        // $account->communication_language = self::correct_input_language($account->communication_language);
        $account->communication_language = strtoupper($account->communication_language);
        $account->mobilenumber = str_replace(' ', '', $account->mobilenumber);   // Remove spaces from mobile number

        return $account;
    }

    public static function correct_input_email($email)
    {
        $email = strtolower($email);
        $email = str_replace(' ', '', $email); //Remove all spaces
        return $email;
    }

    public static function correct_input_mobile($mobile)
    {
        $mobile = strtolower($mobile);
        $mobile = preg_replace("/[^+0-9.]/", "", $mobile);
        if (substr($mobile, 0, 2) === "00") {
            $mobile = "+" . substr($mobile, 2);
        }
        return $mobile;
    }
}
