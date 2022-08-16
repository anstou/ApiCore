<?php

namespace ApiCore\Library\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class LogManger
{
    private Logger $logger;

    public function __construct()
    {
        $dateFormat = "Y-m-d H:i:s";
        $output = "%datetime% > %level_name% > %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat);

        $this->logger = new Logger('Logger');

        foreach (Level::cases() as $value) {
            $path = storage_path('logs' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . strtolower($value->name) . '.log');
            $stream = new StreamHandler($path, $value->value);
            $stream->setFormatter($formatter);
            $this->logger->pushHandler($stream);
        }
    }

    /**
     * @param string $name
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    final public function __call(string $name, array $params)
    {
        return call_user_func_array([$this->logger, $name], $params);
    }


}