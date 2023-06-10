<?php
error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';
require './controllers/Routes.php';

$data = json_decode(file_get_contents("php://input"));    

if ($config->secret != $data->secret)
    die();

switch ($data->type) {
    case "confirmation":
        echo $config->confirm_token;
        break;
    case "message_new":
        echo 'ok';
        
        $routes = new Routes($data);
        $routes->route();
        break;
}
?>