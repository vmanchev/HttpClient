HTTP client

cUrl wrapper as client for HTTP queries. Useful when consuming RESTful web services. Still a lot to be done, but at present time - fully working as per my current needs.

Usage: 
 
```
<?php

use Prodio\Http\Client as HttpClient;
use Prodio\Http\Exception\Curl as CurlException;

$HttpClient->setMethod('POST')
    ->setUrl('http://example.org/end-point')
    ->setParams(array(
        'param1' => 'value1',
        'param2' => 'value2'
    ));
 
try {
    $response = $HttpClient->send();
} catch(CurlException $ex) {
    echo $ex->getMessage();
}

```
