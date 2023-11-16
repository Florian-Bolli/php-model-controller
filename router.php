<?php

/**
 * router.php gets the object-type, id and atributes from the uri
 * https://(dev).api.boatrpark.app/{version}/{object}/{action}
 * 
 * For each object, a controller with the correstponding model should be initialized as a Switch/Case statement
 * Where the function controller->proccess_request() should be called
 */

require_once __DIR__ . "/functions/ApiResponseHeaders.php";
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/CatController.php';
require_once __DIR__ . '/functions/ErrorHandler.php';

date_default_timezone_set('Europe/Zurich');

//Cut URI into arguments
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace("%20", "", $uri); // remove all spaces

$uri = explode('/', $uri);
$uri = str_replace("%20", "", $uri); // remove all spaces


$request_method = $_SERVER['REQUEST_METHOD'];

//Object type, id, and Function that is called
$version = $uri[0];
$object = $uri[1];
$request_type = isset($uri[2]) ? $uri[2] : NULL;

if ($object == Null) showIndexPage();


//Initiate controllers depending on the object type
switch ($object) {
    case 'auth':
        require_once __DIR__ . "/controllers/AuthController.php";
        $controller = new AuthController();
        $controller->respond_request($request_type);
        break;
    case 'cats':
        require_once __DIR__ . "/controllers/CatController.php";
        $controller = new CatController();
        $controller->respond_request($request_type);
        break;
    case 'general':
        break;
}



/**If there is no request, just show some API information */
function showIndexPage()
{
    header("Content-Type: HTML");
?>
    <html>

    <head>
        <title>Boatpark API</title>
    </head>

    <body>
        <h1>This is the API for Boatpark</h1>
    </body>

    </html>
<?php } ?>