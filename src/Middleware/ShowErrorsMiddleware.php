<?php

namespace SimplePHP\Middleware;

use \Slim\Psr7\Response;

class ShowErrorsMiddleware {
    
    /**
     * Example middleware invokable class
     *
     * @param  ServerRequest  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface {
        $response = $handler->handle($request);
        $errorRenderer = new \SimplePHP\Exception\ErrorRenderer();
        $responseBody = $response->getBody();
        return $response;
        var_dump($responseBody); exit();
//        $responseBody = json_decode($response->getBody());
        $errors = json_decode($errorRenderer());
        if ($errors->errors) {
            if ($responseBody) {
                $responseBody->errors = $errors->errors;
            } else {
                $responseBody = json_encode($errors);
            }
        }
        $finalResponse = new Response();
        $finalResponse->getBody()->write(json_encode($responseBody));
        return $finalResponse->withHeader('Content-Type', "application/json");
    }

}
