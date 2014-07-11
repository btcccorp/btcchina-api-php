<?php

class BTCChinaException extends Exception
{
    private $method, $error_code;
    function __construct($message, $method=NULL, $error_code=NULL)
    {
        parent::__construct($message);
        $this->method = $method;
        $this->error_code = $error_code;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getErrorCode()
    {
        return $this->error_code;
    }
}

class ConnectionException extends BTCChinaException
{
}

class JsonRequestException extends BTCChinaException
{
}

class ContentException extends BTCChinaException
{
}
