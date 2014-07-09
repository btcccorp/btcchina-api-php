<?php

class BTCChinaException extends Exception
{
    private $method, $http_code;
    function __construct($message, $method=NULL, $http_code=NULL)
    {
        parent::__construct($message);
        $this->method = $method;
        $this->http_code = $http_code;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getHttpCode()
    {
        return $this->http_code;
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
