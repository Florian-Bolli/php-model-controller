<?php

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../models/cat.php';


class CatController extends Controller
{
    private $cat;

    function __construct($request_method, $id)
    {
        $cat = new Cat();
        $this->cat = $cat;
        $this->cat->id = $id;
        parent::__construct($cat, $request_method, $id);

        $this->right_get = true;
        $this->right_index = true;
        $this->right_create = true;
        $this->right_update = true;
        $this->right_delete = true;
    }

    /**
     * Controller functions are called by the api like the following:
     * https://api.sample.florianbolli.ch/cats/{id}/increase_weight
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
