<?php

require_once '../models/cat.php';


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET["id"];

    $cat = new Cat();

    try {
        $cat->retreive($id);
        echo $cat->text();
    } catch (Exception $e) {
        print("Error. $e No cat with id $id.");
    }
}
