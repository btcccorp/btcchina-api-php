<?php

require_once(dirname(__FILE__) . '/BTCChinaLibrary.php');

// Here we store accesskey and secret key in config file.
// You can hard code the key[0],key[1] here for testing.
$keys = file(dirname(__FILE__) . '/account.config', FILE_IGNORE_NEW_LINES);

$form = '<form action="" method="post">
    <input type="text" name="func" value="">
    <input type="submit" name="submit" value="submit"> 
</form>';

echo $form;

if($_POST)
{
    echo "<br /><pre>";
    try
    {
        $testAPI = new BTCChinaAPI($keys[0], $keys[1]);
        // testAPI can be used directly.
        // Here we use eval to call the method on UI.
        eval("\$res=\$testAPI->{$_POST['func']};");

        echo htmlspecialchars(var_dump($res));
    }
    catch(JsonRequestException $e)
    {
        echo var_dump($e->getMessage() . $e->getMethod() . $e->getErrorCode());
    }
    catch(ContentException $e)
    {
        echo var_dump($e->getMessage() . $e->getMethod() . $e->getErrorCode());
    }
    catch(ConnectionException $e)
    {
        echo var_dump($e->getMessage() . $e->getMethod() . $e->getErrorCode());
    }
    finally
    {
        unset($testAPI);
    }
    echo "</pre>";
}

?>