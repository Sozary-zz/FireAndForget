<?php

namespace Sozary\FireAndForget;

use ErrorException;

class MethodException extends ErrorException
{
    public function __construct($msg, $code = 0)
    {
        parent::__construct($msg, $code);
    }

    public function __toString()
    {
        return $this->message;
    }
}
