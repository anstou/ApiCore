<?php

namespace ApiCore\Library\Exception;

use ApiCore\Library\Http\Response;
use JetBrains\PhpStorm\NoReturn;
use JetBrains\PhpStorm\Pure;

abstract class  ApiException extends \Exception
{
    #[Pure] public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }


    #[NoReturn] public function responseErrByJson(?string $message = null, ?int $code = null)
    {
        Response::response($message ?? $this->message, $code ?? $this->code);
    }

}