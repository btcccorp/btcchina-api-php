<?php

function sign($method, $params = array()){

    $accessKey = "YOUR_ACCESS_KEY"; 
    $secretKey = "YOUR_SECRET_KEY"; 

    $mt = explode(' ', microtime());
    $ts = $mt[1] . substr($mt[0], 2, 6);

    $signature = urldecode(http_build_query(array(
        'tonce' => $ts,r
        'accesskey' => $accessKey,
        'requestmethod' => 'post',
        'id' => 1,
        'method' => $method,
        'params' => '', //implode(',', $params),
    )));
    var_dump($signature);

    $hash = hash_hmac('sha1', $signature, $secretKey);

    return array(
        'ts' => $ts,
        'hash' => $hash,
        'auth' => base64_encode($accessKey.':'. $hash),
    );
}
 
function request($method, $params){
    $sign = sign($method, $params);

    $options = array( 
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic ' . $sign['auth'],
            'Json-Rpc-Tonce: ' . $sign['ts'],
        ),
    );

    $postData = json_encode(array(
        'method' => $method,
        'params' => $params,
        'id' => 1,
    ));
    print($postData);

    $headers = array(
            'Authorization: Basic ' . $sign['auth'],
            'Json-Rpc-Tonce: ' . $sign['ts'],
        );        
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 
    'Mozilla/4.0 (compatible; BTC China Trade Bot; '.php_uname('a').'; PHP/'.phpversion().')'
    );
    curl_setopt($ch, CURLOPT_URL, 'https://api.btcchina.com/api_trade_v1.php');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    // run the query
    $res = curl_exec($ch);
    return $res;

}
 
try { 
    var_dump(request('getAccountInfo', array()));
 
} catch (Exception $e) {
    echo "Error:".$e->getMessage();
} 
?>
