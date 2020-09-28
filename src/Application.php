<?php

namespace SimplePHP;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Factory\AppFactory;

class Application {

    public $errorsRegister;
    public $router;
    public $errorsHandler;
    private $contentType;
    private $path;

    public function __construct($path) {
        $this->path = $path;
        $this->startEnviroments();
        $this->startErrorHandler();
//        $this->startRouter();
        $GLOBALS['App'] = $this;
    }

    private function startEnviroments() {
        $dotenv = \Dotenv\Dotenv::createImmutable($this->path);
        $dotenv->load();
    }

    private function startErrorHandler() {
        $this->errorsRegister = new Exception\ErrorRegister($this);
        if (getenv("DEBUG")) {
            $run = new \Whoops\Run;
            $this->errorsHandler = new \Whoops\Handler\PrettyPageHandler;

            $this->errorsHandler->setPageTitle("Ooops... Ocorreu um problema inesperado!");

            if (\Whoops\Util\Misc::isAjaxRequest()) {
                $run->pushHandler(new \Whoops\Handler\JsonResponseHandler);
            } else {
                $run->pushHandler($this->errorsHandler);
            }
            $run->register();
        } else {
            $this->errorsHandler = new Exception\Handler($this);
            set_exception_handler([$this->errorsHandler, "handleException"]);
            set_error_handler([$this->errorsHandler, "handleError"]);
            $this->errorsRegister->initialize();
        }
    }

    public function startRouter() {
        $container = new \DI\Container();
        \Slim\Factory\AppFactory::setContainer($container);

        $container->set('view', function() {
            $twig = \Slim\Views\Twig::create($this->path . DIRECTORY_SEPARATOR . "templates", ['cache' => false]);
            $twig->addFunction(
                new \Twig\TwigFunction('getenv', function ($key) {
                return getenv($key);
            }));
            return $twig;
        });
        $router = \Slim\Factory\AppFactory::create();
        $router->setBasePath(getenv('WEBROOT'));
        $this->router = $router;
        if (getenv("DEBUG")) {
            $router->add(new \Zeuxisoo\Whoops\Slim\WhoopsMiddleware(['enable' => true]));
        } else {
            if ($this->errorsHandler) {
                $customErrorHandler = $this->errorsHandler;
                $this->errorsHandler->handleRequestErrors($router->getCallableResolver(), $router->getResponseFactory());
            } else {
                $customErrorHandler = new Exception\Handler($this, $router->getCallableResolver(), $router->getResponseFactory());
            }
            $errorMiddleware = $router->addErrorMiddleware(true, false, false);
            $errorMiddleware->setDefaultErrorHandler($customErrorHandler);
            $errorHandler = $errorMiddleware->getDefaultErrorHandler();
            $errorHandler->registerErrorRenderer('text/html', Exception\ErrorRenderer::class);
        }

        $router->add(\Slim\Views\TwigMiddleware::createFromContainer($router));

        $callables = func_get_args();
        if (!$callables) {
            throw new Exception\RouterException();
        }

        foreach ($callables as $callable) {
            if (!is_callable($callable)) {
                throw new Exception\RouterException();
            }
            $callable($this, $router);
        }

        $router->add(new Middleware\ShowErrorsMiddleware());

        $router->run();
    }

    public function getRouter(): \Slim\App {
        return $this->router;
    }

    public function getContentType() {
        return $this->contentType;
    }

    public function setContentType($contentType) {
        $this->contentType = $contentType;
    }

    public function getApp() {
        return $GLOBALS['App'];
    }

    public function getUrl($route, $args = [], $queryParams = []) {
        $routeParser = $this->router->getRouteCollector()->getRouteParser();
        return $routeParser->urlFor($route, $args, $queryParams);
//        echo $routeParser->urlFor('hello', ['name' => 'Josh'], ['example' => 'name']);
//        Outputs "/hello/Josh?example=name"
    }

    public function setViewVariable($name, $value) {
        $this->router->getContainer->get('view')->getEnvironment()->addGlobal($name, $value);
    }

    public function viewRender($response, $template, array $args = []) {
        $view = $this->getRouter()->getContainer()->get('view');
        return $view->render($response, $template . '.twig', $args);
    }

}
