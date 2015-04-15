#PHP Library of BTCChina Trade API
A PHP wrapper to trade bitcoin and litecoin via [BTCChina](https://www.btcchina.com) API.

##Installation

Firstly, download the library via:
```
git clone https://github.com/BTCChina/btcchina-api-php
```

Then, add the following line to your project:
```php
require_once('BTCChinaLibrary.php');
```

[cURL extension](http://php.net/manual/en/book.curl.php) is required.

##Usage
Create Trade API keys at https://vip.btcchina.com/account/apikeys, and set proper permissions as indicated.

Spawn BTCChinaAPI instance with access key and secret key mentioned above. Notice that these keys cannot be modified later.

```php
$btcAPI = new BTCChinaAPI(access_key, secret_key);
```

Call methods similiar to the format described in [API documentation](http://btcchina.org/api-trade-documentation-en).

```php
$res = $btcAPI->getAccountInfo();
```

##Returns
Decoded JSON objects of result or error on successful cURL executions.

##Exceptions
There are three exceptions extended from _BTCChinaException_:

- _ConnectionException_
- _JsonRequestException_
- _ContentException_

Aside from standard _getMessage()_, two more functions are added:

- _getMethod()_: return name of the method where exception occurred.
- _getErrorCode()_: implemented in _JsonRequestException_ only, to return the http error code or [trade api error code](http://btcchina.org/api-trade-documentation-en#error_codes).

##Examples
###Get user information
```php
$res = btcAPI->getAccountInfo();
```

_Result_:
JSON Objects of [profile](http://btcchina.org/api-trade-documentation-en#profile), [balance](http://btcchina.org/api-trade-documentation-en#balance) and [frozen](http://btcchina.org/api-trade-documentation-en#frozen).

###Place order
```php
$ res = btcAPI->placeOrder($price = NULL, $amount, $market = 'BTCCNY');
```

Market type determines the precision of price and amount. See [FAQ](http://btcchina.org/api-trade-documentation-en#faq) No.6 for details.
_Parameters:_

- _price_: set it to null to trade at market price.
- _amount_: negative value to sell while positive value to buy.
- _market_: name of the market to place this order. Default is 'BTCCNY'. Notice that ALL is not supported.

_Result:_
orderID on success. [Invalid amount or invalid price](http://btcchina.org/api-trade-documentation-en#error_codes) error may occur.

###Cancel order
```php
$res = btcAPI->cancelOrder($orderID, $market = 'BTCCNY');
```

_Parameters:_

- _orderID_: the ID returned by placeOrder method
- _market_: name of the market of the order placed previously. Notice that ALL is not supported.

_Result_:
TRUE if successful, otherwise FALSE.

###Get Market Depth
```php
$res = btcAPI->getMarketDepth($limit = 10, $market = 'BTCCNY');
```

Get the complete market depth.
_Parameters:_

- _limit_: number of orders returned per side.
- _market_: the market to get depth of. Notice that ALL is not supported.

_Result:_
[market_depth](http://btcchina.org/api-trade-documentation-en#market_depth) JSON object.

###Get Deposits
```php
$res = btcAPI->getDeposits($currency, $pendingonly = true);
```

Get all user deposits.

_Parameters:_

- _currency_: type of currency to get deposit records of.
- _pendingonly_: whether to get open deposits only.

_Result:_
Array of [deposit](http://btcchina.org/api-trade-documentation-en#deposit) JSON objects.

###Get Withdrawals
```php
$res = btcAPI->getWithdrawals($currency, $pendingonly = true);
```

Get all user withdrawals.

_Parameters:_

- _currency_: type of currency to get deposit records of.
- _pendingonly_: whether to get open withdrawals only.

_Result:_
Array of [withdrawal](http://btcchina.org/api-trade-documentation-en#withdrawal) JSON object.

###Get single withdrawal status
```php
$res = btcAPI->getWithdrawal($withdrawalID, $currency = 'BTC');
```

_Parameters:_

- _withdrawalID_: the withdrawal to get status of.
- _currency_: type of currency.

_Result:_
[withdrawal](http://btcchina.org/api-trade-documentation-en#withdrawal) JSON object.

###Request a withdrawal
```php
$res = btcAPI->requestWithdrawal($currency, $amount);
```

Make a withdrawal request. BTC withdrawals will pick last used withdrawal address from user profile.

_Parameters:_

- _currency_: type of currency to withdraw.
- _amount_: amount of currency to withdraw.

_Result:_
JSON object: {"id":"withdrawalID"}
Notice that the return format of withdrawalID is different from that of orderID.

###Get order status
```php
$res = btcAPI->getOrder($orderID, $market = 'BTCCNY');
```

_Parameters:_

- _orderID_: the order to get status of.
- _market_: the market in which the order is placed. Notice that ALL is not supported.

_Result:_
[order](http://btcchina.org/api-trade-documentation-en#order) JSON object.

###Get all order status
```php
$res = btcAPI->getOrders($openonly = true, $market = 'BTCCNY', $limit = 1000, $offset = 0);
```

_Parameters:_

- _openonly_: whether to get open orders only.
- _market_: the market in which orders are placed.
- _limit_: the number of orders to show.
- _offset_: page index of orders.

_Result:_
Array of [order](http://btcchina.org/api-trade-documentation-en#order) JSON objects.

###Get transaction log
```php
$res = btcAPI->getTransactions($transaction = 'all', $limit = 10, $offset = 0);
```

Notice that prices returned by this method may differ from placeOrder as it is the price get procceeded.

_Parameters:_

- _transaction_: type of transaction to fetch.
- _limit_: the number ot transactions.
- _offset_: page index ot transactions.

_Result:_
Array of [transaction](http://btcchina.org/api-trade-documentation-en#transaction) JSON objects.




> Written with [StackEdit](https://stackedit.io/).
