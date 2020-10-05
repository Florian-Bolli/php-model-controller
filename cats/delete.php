<?php

require_once '../models/cat.php';


if ($_SERVER['REQUEST_METHOD'] === 'DELETE') { 
    
    $id = $_GET["id"];

    $cat = new Cat();
    $cat->delete_by_id($id);

    echo "Success. Cat with id $id has been deleted.";

}






