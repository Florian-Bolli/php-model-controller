<?php

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../models/dog.php';


class DogController extends Controller{

    function __construct($request_method, $id)
    {
        $dog = new Dog();
        parent::__construct($dog, $request_method, $id);

        //Define rights
        $this->right_get = true;
        $this->right_index = false;
        $this->right_create = true;
        $this->right_update = false;
        $this->right_delete = false;
    }
    
}
 


