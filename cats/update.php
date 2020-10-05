<?php

require_once '../models/cat.php';

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $post = json_decode(file_get_contents('php://input'), true);

    $cat = new Cat();
    $cat->overwrite_atributes($post);
    try{
        $cat->update();
        echo "Success. Cat has been updated.";
    }
    catch(Exception $e){
        echo "Error: $e";
    }

}
