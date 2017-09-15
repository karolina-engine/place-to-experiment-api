<?php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Silex\Provider;
use Symfony\Component\Translation\Loader\YamlFileLoader;


$app = new Silex\Application();


$app['ci'] = false;

require('bootstrap.php');

$app->run();
