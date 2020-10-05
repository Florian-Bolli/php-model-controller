<?php

require_once '../models/cat.php';

$cat = new Cat();
$cats = $cat->get_all();
echo json_encode($cats);

