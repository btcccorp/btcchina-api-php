<?php

final Class BTCChinaAPI
{
    const URL = 'https://api.btcchina.com/api_trade_v1.php';
    private $accessKey, $secretKey, $ch;

    private static $marketType = array('BTCCNY', 'LTCCNY', 'LTCBTC', 'ALL');
    private static $currencyType = array('BTC', 'LTC');
    private static $transactionType= array(
	    'all', 'fundbtc', 'withdrawbtc', 'fundmoney', 'withdrawmoney', 'refundmoney',
		'buybtc', 'sellbtc', 'buyltc', 'sellltc', 'tradefee', 'rebate');

    function __construct($access, $secret)
    {
        $this->accessKey = $access;
        $this->secretKey = $secret;
        $this->ch = curl_init();
    }

    function __destruct()
    {
        curl_close($this->ch);
    }
	
    //test if a variable is a non-negative number.
    private static function is_nnn($var)
    {
        if(is_numeric($var) && !is_string($var) && $var >= 0)
            return TRUE;
        else
            return FALSE;
    }

    private static function is_market($var, $all=FALSE)
    {
        if(array_search($var, self::$marketType, TRUE) === NULL)
            return FALSE;
        else if($var === 'ALL' && !$all )
            return FALSE;
        else
            return TRUE;
    }

    private static function is_currency($var)
    {
        if(array_search($var, self::$currencyType, TRUE) === NULL)
            return FALSE;
        else
            return TRUE;
    }

    private static function is_transaction($var)
    {
        if(array_search($var, self::$transactionType, TRUE) === NULL)
            return FALSE;
        else
            return TRUE;
    }

    //one-way function, try to make it atomic, and reduce tounce lag
    private function DoMethod($method, $params = array())
    {
        if($this->ch === FALSE)
        {
            $this->ch = curl_init();
            throw new ConnectionException('cURL ERROR:' . curl_error($this->ch), $method);
        }
        else
        {
            //get tounce
            $mt = explode(' ', microtime());
            $ts = $mt[1] . substr($mt[0], 2, 6);
            $id = mt_rand();
            //build signature string
            $signature = urldecode(http_build_query(array(
                'tonce' => $ts,
                'accesskey' => $this->accessKey,
                'requestmethod' => 'post',
                'id' => $id,
                'method' => $method,
                'params' => implode(',', $params), //it's not JSON yet so PHP's type-juggling is fine.
                )));

            $auth = base64_encode($this->accessKey . ':' . hash_hmac('sha1', $signature, $this->secretKey));
            //http header
            $headers = array(
                'Authorization: Basic ' . $auth,
                'Json-Rpc-Tonce: ' . $ts,
                );
            //post body
            $postData = json_encode(array(
                'method' => $method,
                'params' => $params,
                'id' => $id,
                ));
            //set curl options
            curl_setopt_array($this->ch, array(
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_URL => BTCChinaAPI::URL,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => FALSE,
            ));

            // run the query
            $res = curl_exec($this->ch);
            // get necessary info for exception
            $httpStatus = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            // curl error
            if($httpStatus != 200)
            {
                throw new JsonRequestException('cURL ERROR:' . curl_error($this->ch), $method, $httpStatus);
            }
            // try to compare id and remove it upon success
            $jsonResult = json_decode($res);
            // 401 etc.
            if($jsonResult === NULL)
            {
                throw new JsonRequestException('Error parse JSON result:' . $res, $method, $httpStatus);
            }
            else if($jsonResult->id != $id)
            {
                throw new JsonRequestException('JSON request ID did not match!', $method, $httpStatus);
            }
            // api error code
            if(isset($jsonResult->error))
                throw new JsonRequestException('JSON request error:' . $jsonResult->error->message, $method, $jsonResult->error->code);
            if(isset($jsonResult->result))
                return $jsonResult->result;
            else
                return $jsonResult;
        }
    }

    public function getAccountInfo()
    {
        $method = 'getAccountInfo';
        return $this->DoMethod($method);
    }

    // negative amount considered as sell
    // positive amount considered as buy
    // price cannot be negative
    public function placeOrder($price=NULL, $amount, $market='BTCCNY')
    {
        $method = '';
        if(is_numeric($amount) && !is_string($amount))
        {
            if($amount < 0)
            {
                $method = 'sellOrder2';
                $amount = -$amount;
            }
            else
                $method = 'buyOrder2';
        }
        else
            throw new ContentException('amount should be numeric.', 'placeOrder');

        if(!is_null($price) && !$this->is_nnn($price))
            throw new ContentException('price should be either null or positive numeric.', 'placeOrder');

        if(!$this->is_market($market, FALSE))
            throw new ContentException('Market must be \'BTCCNY\', \'LTCCNY\' or \'LTCBTC\'.', 'placeOrder');

        return $this->DoMethod($method, array($price, $amount, $market));
    }

    public function cancelOrder($orderID, $market='BTCCNY')
    {
        $method = 'cancelOrder';
        if(!$this->is_market($market, FALSE))
            throw new ContentException('Market must be \'BTCCNY\', \'LTCCNY\' or \'LTCBTC\'.', 'cancelOrder');
        if(!$this->is_nnn($orderID))
            throw new ContentException('orderID is a non-negative numeric value.', 'cancelOrder');
        return $this->DoMethod($method, array($orderID, $market));
    }

    public function getMarketDepth($limit=10, $market='BTCCNY')
    {
        $method = 'getMarketDepth2';
        if(!$this->is_nnn($limit))
            throw new ContentException('limit is a non-negative, numeric value.', 'getMarketDepth');
        if(!$this->is_market($market, TRUE))//'ALL' is ok.
            throw new ContentException('market available: \'BTCCNY\', \'LTCCNY\' , \'LTCBTC\' and \'ALL\'', 'getMarketDepth');
        return $this->DoMethod($method, array($limit, $market));
    }

    public function getDeposits($currency, $pendingonly=TRUE)
    {
        $method = 'getDeposits';
        if(!$this->is_currency($currency))
            throw new ContentException('currency: \'BTC\' or \'LTC\'', 'getDeposits');
        if(!is_bool($pendingonly))
            throw new ContentException('pendingonly: TRUE or FALSE', 'getDeposits');
        return $this->DoMethod($method, array($currency, $pendingonly));
    }

    public function getWithdrawals($currency, $pendingonly=TRUE)
    {
		$method = 'getWithdrawals';
        if(!$this->is_currency($currency))
            throw new ContentException('currency: \'BTC\' or \'LTC\'', 'getWithdrawals');
        if(!is_bool($pendingonly))
            throw new ContentException('pendingonly: TRUE or FALSE', 'getWithdrawals');
        return $this->DoMethod($method, array($currency, $pendingonly));
    }

    public function getWithdrawal($withdrawID, $currency)
    {
        $method = 'getWithdrawal';
        if(!$this->is_currency($currency))
            throw new ContentException('currency: \'BTC\' or \'LTC\'', 'getWithdrawal');
        if(!$this->is_nnn($withdrawID))
            throw new ContentException('withdrawID is a numeric value.', 'getWithdrawal');
        return $this->DoMethod($method, array($withdrawID, $currency));
    }

    public function requestWithdrawal($currency, $amount)
    {
        $method = 'requestWithdrawal';
        if(!$this->is_currency($currency))
            throw new ContentException('currency: \'BTC\' or \'LTC\'', 'requestWithdrawal');
        if(!$this->is_nnn($amount))
            throw new ContentException('amount is a non-negative numeric value.', 'requestWithdrawal');
        return $this->DoMethod($method, array($currency, $amount));
    }

    public function getOrder($orderID, $market)
    {
        $method = 'getOrder';
        if(!$this->is_market($market, FALSE))
            throw new ContentException('market available: \'BTCCNY\', \'LTCCNY\' and \'LTCBTC\'.', 'getOrder');
        if(!$this->is_nnn($orderID))
            throw new ContentException('orderID is a non-negative numeric value.', 'getOrder');
        return $this->DoMethod($method, array($orderID));
    }

    public function getOrders($openonly = TRUE, $market = 'BTCCNY', $limit = 1000, $offset = 0)
    {
        $method = 'getOrders';
        if(!$this->is_market($market, TRUE))
            throw new ContentException('market available: \'BTCCNY\', \'LTCCNY\' , \'LTCBTC\' and \'ALL\'', 'getOrders');
        if(!$this->is_nnn($limit))
            throw new ContentException('limit is a non-negative numeric value.', 'getOrders');
        if(!$this->is_nnn($offset))
            throw new ContentException('offset is a non-negative numeric value.', 'getOrders');
        return $this->DoMethod($method, array($openonly, $market, $limit, $offset));
    }

    public function getTransactions($transaction = 'all', $limit = 10, $offset = 0)
    {
        $method = 'getTransactions';
        if(!$this->is_transaction($transaction))
            throw new ContentException('transaction type not available.', 'getTransactions');
        if(!$this->is_nnn($limit))
            throw new ContentException('limit is a non-negative numeric value.', 'getTransactions');
        if(!$this->is_nnn($offset))
            throw new ContentException('offset is a non-negative numeric value.', 'getTransactions');
        return $this->DoMethod($method, array($transaction, $limit, $offset));
    }
}
