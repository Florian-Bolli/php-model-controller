<?php

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../models/cat.php';


class CatController extends Controller
{
    private $cat;
    private $cats;

    function __construct($request_method, $id, $auth_controller = null)
    {
        $cat = new Cat(null);
        $cats = new Cats();
        $this->cat = $cat;
        $this->cats = $cats;
        $this->cat->id = $id;

        parent::__construct($cat, $cats, $request_method, $id);

        //Optional: only if authorization is needed
        $this->auth_controller = $auth_controller;

        //Set rights for basic parent's functions
        $this->right_get = true;
        $this->right_index = true;
        //to access the basic functions of Controller, rights have to be set
        if ($this->auth_controller->is_authenticated()) {
            $this->right_create = true;
            $this->right_update = true;
            $this->right_delete = true;
        }
    }

    /**
     * Custom controller functions are called by the api like the following:
     * https://api.example.ch/cats/{id}/increase_weight
     */
    function increase_weight()
    {
        $this->cat->increase_weight(0.4);
        $this->cat->Increasing = "Done";
        $this->cat->retreive();
        echo $this->cat->text();
    }

    function decrease_weight()
    {
        $this->cat->increase_weight(-0.4);
        $this->cat->Decreasing = "Done";
        $this->cat->retreive();
        echo $this->cat->text();
    }
}