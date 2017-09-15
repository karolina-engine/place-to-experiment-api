<?php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Silex\Provider;
use Symfony\Component\Translation\Loader\YamlFileLoader;

// COMPOSER
require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();


/*********************************************
	Environment variables
*********************************************/

$environment = new Karolina\Environment(false);
$dotenv = new Dotenv\Dotenv(__DIR__ .'/../', 'defaultconfig.env');


$environment->setEnvVariablesFromArray($envVariables);

/*********************************************
	CodeIgniter Instance
*********************************************/

$app['ci'] = NULL;

/*********************************************
	Database
*********************************************/

$database = new \Karolina\Database\DatabaseConnector();
$database->addConnection(getenv('database_name'));
$database->makeGlobal();

include('bootstrap.php');

return $app;