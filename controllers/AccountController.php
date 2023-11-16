<?php

require_once __DIR__ . '/controller.php';

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/../models/Accounts.php';
require_once __DIR__ . '/../models/BankAccounts.php';
require_once __DIR__ . '/../models/Boats.php';
require_once __DIR__ . '/../models/PayrexxTokenizations.php';
require_once __DIR__ . '/../models/MapOverlays.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions/utilfunctions.php';
require_once __DIR__ . '/../vendor/autoload.php';

class AccountController extends Controller
{
    public $account_id = false;

    function __construct()
    {
    }

    function get_account_data()
    {
        $authController = new AuthController();
        $authController->require_authentification();
        require_once __DIR__ . '/account/get_account_data.php';
    }
}
