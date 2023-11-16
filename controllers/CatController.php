<?php

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Cats.php';

class CatController extends Controller
{

    function __construct()
    {
    }

    function get_all_request($post, $get, $auth_controller)
    {
        $cats = Cats::get_all();

        $response = new Response('success', $cats);
        return $response;
    }
}
