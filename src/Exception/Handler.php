<?php

namespace SimplePHP\Exception;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\ErrorHandler as ErrorHandler;

class Handler extends ErrorHandler {

    public $application;

    public function __construct(&$application, $resolver = null, $factory = null) {
        $this->application = $application;
        if ($resolver || $factory) {
            parent::__construct($resolver, $factory);
        }
    }

    public function handle(\Throwable $e) {
        if (\Whoops\Util\Misc::isAjaxRequest()) {
            
        } else {
//            echo $e->xdebug_message;
        }
    }

    public function handleException(\Throwable $t) {
        if (getenv('ADMIN_NOTIFY')) {
            $this->adminNotify($t);
        }
//        var_dump($t);
        if (!($t instanceof \SimplePHP\Exception\SimpleException)
        ) {
            ErrorRegister::register(new Error((method_exists($t, 'getUserMessage') ? $t->getUserMessage() : "Ocorreu um problema inesperado no sistema. Por favor tente novamente em instantes."), "SYSTEM", $t->getCode(), $t), true);
        } else {
            ErrorRegister::register(new Error($t->getUserMessage(), "SYSTEM", $t->getCode(), $t), true);
        }
    }

    public function handleError($level, $message, $file = null, $line = null) {
        if ($level != E_USER_ERROR && $level != E_USER_WARNING && $level != E_USER_NOTICE) {
            if (getenv('ADMIN_NOTIFY')) {
                $this->adminNotify(new SimpleException($message, $level, null, $file, $line));
            }
            ErrorRegister::register(new Error("Ocorreu um problema inesperado no sistema. Por favor tente novamente em instantes.", "Ooops!", "SYSTEM"), true);
        } else {
            ErrorRegister::register(new Error($message, "Ahhh...", $level));
        }
    }

    public function handleRequestErrors(\Slim\Interfaces\CallableResolverInterface $callableResolver, \Psr\Http\Message\ResponseFactoryInterface $responseFactory) {
        $this->callableResolver = $callableResolver;
        $this->responseFactory = $responseFactory;
        $this->logger = null;
    }

    public function __invoke(ServerRequestInterface $request, \Throwable $exception = null, bool $displayErrorDetails = false, bool $logErrors = false, bool $logErrorDetails = false): ResponseInterface {
        if ($exception && getenv('ADMIN_NOTIFY')) {
            $this->adminNotify($exception);
        }
        $this->displayErrorDetails = $displayErrorDetails;
        $this->logErrors = $logErrors;
        $this->logErrorDetails = $logErrorDetails;
        $this->request = $request;
        $this->exception = $exception;
        $this->method = $request->getMethod();
        $this->statusCode = $this->determineStatusCode();
        if ($this->contentType === null) {
            $this->contentType = $this->determineContentType($request);
        }

        return $this->respond($request);
    }

    protected function respond(ServerRequestInterface $request = null): ResponseInterface {
        $response = $this->responseFactory->createResponse($this->statusCode);
        if ($this->contentType !== null && array_key_exists($this->contentType, $this->errorRenderers)) {
            $response = $response->withHeader('Content-type', $this->contentType);
        } else {
            $response = $response->withHeader('Content-type', $this->defaultErrorRendererContentType);
        }

        if ($this->exception instanceof HttpMethodNotAllowedException) {
            $allowedMethods = implode(', ', $this->exception->getAllowedMethods());
            $response = $response->withHeader('Allow', $allowedMethods);
        }
        if (!$this->exception){
            $response = $response->withStatus(400);            
        }

        $renderer = $this->determineRenderer();
        $body = call_user_func($renderer, $this->exception, $this->displayErrorDetails);
        return ErrorRenderer::renderErrors($request, $response, $body);
    }

    private function formatUserMessage(\Throwable $e) {
        $hoje = date('Y-m-d H:i:s');
        $msg_error = "<b>" . getenv("SYSNAME") . "</b> ($hoje)<br><b>Arquivo:</b> " . $e->getFile() . "<br><b>Linha:</b>" . $e->getLine() . "<br><b>Mensagem:</b>" . $e->getMessage() . "<br><b>Codigo:</b>" . $e->getCode() . "<br><b>Trace:</b>";
        $traceMessage = "";
        if ($e->getTrace()) {
            $i = 0;
            foreach (array_reverse($e->getTrace()) as $trace) {
                $traceMessage .= "<br>   <b>#$i</b>";
                $traceMessage .= "<br>   <b>Arquivo:</b> " . ($trace['file'] ?? '-');
                $traceMessage .= "<br>   <b>Linha:</b> " . ($trace['line'] ?? '-');
                $traceMessage .= "<br>   <b>Classe:</b> " . ($trace['class'] ?? '-');
                $traceMessage .= "<br>   <b>Função:</b> " . ($trace['function'] ?? '-');
                $traceMessage .= "<br>   <b>Argumentos:</b> " . json_encode(($trace['args'] ?? '-')) . "<br>";
                $i++;
            }
        }
        $msg_error .= $traceMessage;
        return $msg_error;
    }

    private function formatAdminMessage(\Throwable $e) {
        $hoje = date('Y-m-d H:i:s');
        $msg_error = "<b>" . getenv("SYSNAME") . "</b> ($hoje)<br><b>Arquivo:</b> " . $e->getFile() . "<br><b>Linha:</b>" . $e->getLine() . "<br><b>Mensagem:</b>" . $e->getMessage() . "<br><b>Codigo:</b>" . $e->getCode() . "<br><b>Trace:</b>";
        $traceMessage = "";
        if ($e->getTrace()) {
            $i = 0;
            foreach (array_reverse($e->getTrace()) as $trace) {
                $traceMessage .= "<br>   <b>#$i</b>";
                $traceMessage .= "<br>   <b>Arquivo:</b> " . ($trace['file'] ?? '-');
                $traceMessage .= "<br>   <b>Linha:</b> " . ($trace['line'] ?? '-');
                $traceMessage .= "<br>   <b>Classe:</b> " . ($trace['class'] ?? '-');
                $traceMessage .= "<br>   <b>Função:</b> " . ($trace['function'] ?? '-');
                $traceMessage .= "<br>   <b>Argumentos:</b> " . json_encode(($trace['args'] ?? '-')) . "<br>";
                $i++;
            }
        }
        $msg_error .= $traceMessage;
        return $msg_error;
    }

    private function adminTelegramNotify($msg_error) {
        new \SimplePHP\Notification\TelegramNotify(getenv("TELEGRAM_BOT_KEY"), getenv("TELEGRAM_BOT_CHANNEL"), $msg_error);
    }

    private function adminLogNotify($msg_error) {
        if (!is_dir("logs/")) {
            mkdir("logs/", 075);
        }

        error_log(str_replace(["<br>", "</br>"], ["\n", "\n"], $msg_error) . "\n------------------------------------------\n", 3, 'logs/errorsLog.dat');
    }

    private function adminNotify(\Throwable $e) {
        $msg_error = $this->formatAdminMessage($e);
        try {
            $this->adminTelegramNotify($msg_error);
        } catch (TelegramNotifyException $e) {
            $this->adminLogNotify($msg_error);
            return;
        }
    }

}
