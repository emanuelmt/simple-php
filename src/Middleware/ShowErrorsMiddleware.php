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
        $responseBody = $response->getBody();
        $headers = $response->getHeaders();

        $jsonBody = json_decode($responseBody);
        if (json_last_error() == JSON_ERROR_NONE) {
            $errorRenderer = new \SimplePHP\Exception\ErrorRenderer();
            $errors = json_decode($errorRenderer());
            if (isset($jsonBody->errors) && $errors->errors) {
                foreach ($errors->errors as $erro) {
                    $jsonBody->errors[] = $erro;
                }
            } else if ($errors->errors) {
                $jsonBody->errors = $errors->errors;
            }
            $finalResponse = new \GuzzleHttp\Psr7\Response();
            foreach ($headers as $header => $value) {
                $finalResponse = $finalResponse->withAddedHeader($header, implode(", ", $value));
            }

            $response = $errorRenderer->renderErrors($finalResponse, json_encode($jsonBody));
        }
        return $response;
    }

}
