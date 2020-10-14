<?php

declare(strict_types = 1);

namespace SimplePHP\Exception;

use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\ErrorRendererInterface as ErrorRendererInterface;
use \Throwable;

final class ErrorRenderer implements ErrorRendererInterface {

    protected $errors = [];

    public function __invoke(Throwable $exception = null, bool $displayErrorDetails = false): string {
        if ($exception && !($exception instanceof \SimplePHP\Exception\SimpleException)) {
            if ($exception instanceof \Slim\Exception\HttpException) {
                $type = 'REQUEST';
            } else {
                $type = 'SYSTEM';
            }
            if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
                $message = "O recurso solicitado não foi encontrado! Verifique se a URL foi informada corretamente e tente novamente.";
            } else if ($exception instanceof \Slim\Exception\HttpBadRequestException) {
                $message = "O servidor não pode processar o pedido devido a um aparente erro do cliente.";
            } else if ($exception instanceof \Slim\Exception\HttpForbiddenException) {
                $message = "Você não tem permissão para realizar a operação solicitada.";
            } else if ($exception instanceof \Slim\Exception\HttpInternalServerErrorException) {
                $message = "Foi encontrada uma condição inesperada impedindo o servidor de atender à solicitação.";
            } else if ($exception instanceof \Slim\Exception\HttpMethodNotAllowedException) {
                $message = "O método de solicitação não é compatível com o recurso solicitado.";
            } else if ($exception instanceof \Slim\Exception\HttpNotImplementedException) {
                $message = "O servidor não suporta a funcionalidade necessária para atender à solicitação.";
            } else if ($exception instanceof \Slim\Exception\HttpUnauthorizedException) {
                $message = "A solicitação requer uma autenticação válida.";
            } else {
                $message = "Ocorreu um problema inesperado no sistema. Por favor tente novamente em instantes.";
            }
            ErrorRegister::register(new Error((method_exists($exception, 'getUserMessage') ? $exception->getUserMessage() : $message), 'Ooops!', $type, $exception->getCode(), $exception), false);
        } else if ($exception && ($exception instanceof \SimplePHP\Exception\SimpleException)) {
            ErrorRegister::register(new Error(($exception->getMessage() ? $exception->getMessage() : $exception->getUserMessage()), 'Ooops!', "SYSTEM", $exception->getCode(), $exception), false);
        } else if ($exception) {
            ErrorRegister::register(new Error($exception->getUserMessage(), "SYSTEM", $exception->getCode(), $exception), false);
        }
        if ($GLOBALS['App']->errorsRegister->getErrors()) {
            foreach ($GLOBALS['App']->errorsRegister->getErrors() as $error) {
                $this->errors[] = ['message' => $error->getMessage(), 'title' => $error->getTitle(), 'type' => $error->getType(), 'code' => $error->getCode(), 'trace' => (getenv("DEBUG") ? $error->getTrace() : null)];
            }
        }

        $GLOBALS['App']->errorsRegister->initialize();

        if (getenv("DEBUG")) {
            var_dump(["errors" => $this->errors]);
            exit();
        } else {
            return json_encode(["errors" => $this->errors]);
        }
    }

    public static function renderErrors(\Psr\Http\Message\ResponseInterface $response, $body) {
        $contentType = $GLOBALS['App']->getContentType();
        if ($contentType == "application/json") {
            $response->getBody()->write($body);
            return $response->withHeader('Content-type', $contentType);
        } else {
            if (\Whoops\Util\Misc::isAjaxRequest()) {
                $response->getBody()->write($body);
                return $response->withHeader('Content-type', $contentType);
            } else {
                return $GLOBALS['App']->viewRender($response, 'request.error', [
                        "body" => json_decode($body)
                ]);
            }
        }
    }

}
