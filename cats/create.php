<?php

require_once '../models/cat.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = json_decode(file_get_contents('php://input'), true);

    $cat = new Cat();
    $cat->overwrite_atributes($post);

    if ($id = $cat->save()) {
        echo "Success. Cat saved with id $id.";
    }
}
