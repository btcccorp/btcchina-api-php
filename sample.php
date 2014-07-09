<?php

require_once(dirname(__FILE__) . '\BTCChina.php');

// Here we store accesskey and secret key in config file.
// You can hard code the key[0],key[1] here for testing.
$keys = file(dirname(__FILE__) . '\account.config', FILE_IGNORE_NEW_LINES);

echo "<html><body><form action=\"\" method=\"post\"><input type=text name=func /><input type=submit value=submit />";
if($_POST)
{
	echo "<br /><pre>";
	try
	{
		$testAPI = new BTCChinaAPI($keys[0], $keys[1]);
		// testAPI can be used directly.
		// Here we use eval to call the method on UI.
		eval("\$res=\$testAPI->".$_POST['func']);
		echo htmlspecialchars(print_r($res));
	}
	catch(JsonRequestException $e)
	{
		echo print_r($e->getMessage() . $e->getMethod() . $e->getCode());
	}
	catch(ContentException $e)
	{
		echo print_r($e->getMessage() . $e->getMethod() . $e->getCode());
	}
	catch(ConnectionException $e)
	{
		echo print_r($e->getMessage() . $e->getMethod() . $e->getCode());
	}
	finally
	{
		unset($testAPI);
	}
	echo "</pre>";
}
echo "</form></body></html>"
?>