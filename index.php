<?php

require_once 'vendor/autoload.php';

//use Psr\Http\Message\ResponseInterface as Response;
//use Psr\Http\Message\ServerRequestInterface as Request;
//use Slim\Factory\AppFactory;
//use Slim\Exception\NotFoundException;

$app = new SimplePHP\Bootstrap(__DIR__);

//$router = $app->router;
//
//$container = $router->getContainer();
//$container['HomeController'] = function($c) {
//    $view = $c->get("view"); // retrieve the 'view' from the container
//    return new HomeController($view);
//};
//
//$router->get('/', \Test\Controllers\HomeController::class .':home');
//
//$router->run();

function test($string) {
    return str_replace(":", "-", $string);
}

$nascimento = new SimplePHP\Types\Date("22:4:1999 00:00:01");
$cpf = new SimplePHP\Types\CpfCnpj("14496236702");
echo $cpf;
echo $nascimento;
SimplePHP\Exception\ErrorRegister::render();
