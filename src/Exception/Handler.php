<?php

namespace SimplePHP\Exception;

class Handler {

    public function handle(\Throwable $e) {
        if (\Whoops\Util\Misc::isAjaxRequest()) {
            
        } else {
//            echo $e->xdebug_message;
        }
    }

    public function handleException(\Throwable $t) {
//        $this->adminNotify($t);
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
//            $this->adminNotify(new SimpleException($message, $level, null, $file, $line));
            ErrorRegister::register(new Error("Ocorreu um problema inesperado no sistema. Por favor tente novamente em instantes.", "SYSTEM"), true);
        } else {
            ErrorRegister::register(new Error($message, $level));
        }
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
