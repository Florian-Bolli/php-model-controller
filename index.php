<?php

/**
 * index.php gets the object-type, id and atributes from the uri
 * https://api.example.ch/{object}/{id}/{function}
 * 
 * For each object, a controller with the correstponding model should be initialized as a Switch/Case statement
 * Where the function controller->proccess_request() should be called
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//Cut URI into arguments
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
$request_method = $_SERVER['REQUEST_METHOD'];

//Object type, id, and Function that is called
$object = $uri[1];
$id = isset($uri[2]) ? $uri[2] : NULL;
$request_type = isset($uri[3]) ? $uri[3] : NULL;

if($object == Null) showIndexPage();

//Initiate controllers depending on the object type
switch ($object) {
    case 'cats':
        require_once __DIR__ . "/controllers/cat_controller.php";
        $controller = new CatController($request_method, $id);
        $controller->process_request($request_type);
        break;
    case 'dogs':
        require_once __DIR__ . "/controllers/dog_controller.php";
        $controller = new DogController($request_method, $id);
        $controller->process_request($request_type);
        break;
}



/**If there is no request, just show some API information */
function showIndexPage(){ 
    header("Content-Type: HTML");
?>


<html>

<head>
    <title>Sample API</title>
</head>

<body>
    <h1>This is the API for Sample</h1>
    Check out the <a href="example.php">example</a>
</body>
</html>

 
<?php } ?>

