<?php

require_once '../models/cat.php';


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET["id"];

    $cat = new Cat();

    try {
        $cat->retreive_by_id($id);
        $cat->print();
    } catch (Exception $e) {
        print("Error. $e No cat with id $id.");
    }
}
