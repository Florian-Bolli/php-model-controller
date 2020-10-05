<?php

echo "<h1>Sample API Examples</h1>";

require_once 'models/cat.php';

$cat = new Cat();

//Only needed the first time:
//create database entry for cats
$cat->create_db_table();

//Create new cat
$cat->name = "Camillo";
$cat->gender = "male";
$cat->date_of_birth = "2010-01-01";
$cat->weight = 5.9;
$cat->pretty = true;
$cat->teeth = 24;

//check if the cat iis valid
if ($cat->is_valid()) {
    echo "The cat is valid (all atributes are set) <br>";
} else {
    echo "Cat not valid: missing properties <br>";
}

//take a look at the cat
echo $cat->text();
echo "<br>";

//Save cat into the db
$inserted_id = $cat->save();
echo "$cat->name saved to DB with id $cat->id <br>";



//Save another cat
$cat = new Cat();
$cat->name = "Tigi";
$cat->gender = "male";
$cat->date_of_birth = "2014-02-03";
$cat->weight = 5.1;
$cat->pretty = 1;
$cat->teeth = 22;
echo $cat->text();
$cat->save();


//retreive first one
$cat= new Cat();
$cat->retreive($inserted_id);

echo $cat->text();
echo "<br>";

//update the cat
$cat->weight = 5.9;
$cat->update();


//get all cats
$number_of_cats = $cat->count();
echo "<br> There are $number_of_cats cats: <br>";

$cats = $cat->get_all();
echo json_encode($cats);
