<?php
/**
 * HTTP client
 *
 * cUrl wrapper as client for HTTP queries. Useful when consuming RESTful web 
 * services. Still a lot to be done, but at present time - fully working as per 
 * my current needs.
 *
 * @categoty Prodio
 * @package  Http
 * @version  0.0.1
 * @author   Venelin Manchev <manchev@phpwisdom.com>
 */

namespace Prodio\Http;

use Prodio\Http\Exception\Curl as CurlException;

/**
 * HTTP client
 *
 * Usage:
 *
 * <code>
 * <?php
 *
 * use Prodio\Http\Client as HttpClient;
 * use Prodio\Http\Exception\Curl as CurlException;
 *
 * $HttpClient->setMethod('POST')
 *      ->setUrl('http://example.org/end-point')
 *      ->setParams(array(
 *          'param1' => 'value1',
 *          'param2' => 'value2'
 *      ));
 *
 * try {
 *      $response = $HttpClient->send();
 * } catch(CurlException $ex) {
 *      echo $ex->getMessage();
 * }
 *
 * </code>
 */
class Client
{
    /**
     * cUrl handle
     */
    protected $ch;

    /**
     * Service URL to send the requests to
     * @var string
     */
    protected $url;

    /**
     * Request params
     * @var mixed Could be one of array, object or key/value query string. 
     */
    protected $params;

    /**
     * HTTP method
     * @var string HTTP method to be used for this request (GET or POST).
     */
    protected $method;

    /**
     * Request headers
     * @var array Array of headers to be used for this request
     */
    protected $headers = array();

    /**
     * Class constructor
     * 
     * Initialize cUrl and set the default values. 
     */
    public function __construct()
    {
        $this->ch = curl_init();

        $this->__setDefaults();
    }

    /**
     * Send the HTTP request
     *
     * @return mixed Request result (string) or false.
     * @throws Prodio\Http\Exception\Curl
     */
    public function send()
    {
        if($this->method == 'GET'){
            $url = $this->url . '?' . $this->__buildQuery();
            $this->setUrl($url);
        }

        $result = curl_exec($this->ch);

        if ($result === false) {
            throw new CurlException($this->ch);
        }

        curl_close($this->ch);

        return $result;
    }

    /**
     * Send asynchronous HTTP request
     *
     * "Try to send" asynchronous HTTP request, by limiting the timeout/max execution time. In other words, send
     * the request and do not wait for response.
     *
     * @return boolean
     * @throws CurlException
     */
    public function sendAsync()
    {
        $this->__setAsyncOptions();
        $result = curl_exec($this->ch);

        if ($result === false) {
            throw new CurlException($this->ch);
        }

        curl_close($this->ch);
        return true;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        curl_setopt($this->ch, CURLOPT_URL, $this->url);

        return $this;
    }

    public function setParams($params)
    {
        $this->params = $params;

        if($this->method == 'POST'){
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->__buildQuery());
        }

        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);

        return $this;
    }

    public function addRawHeader($raw_header)
    {
        $this->headers[] = $raw_header;

        $this->setHeaders($this->headers);
    }

    public function setMethod($method)
    {
        $this->method = strtoupper(strtolower($method));

        switch ($this->method) {
            case 'POST':
                curl_setopt($this->ch, CURLOPT_POST, true);
                break;
            case 'GET':
                curl_setopt($this->ch, CURLOPT_HTTPGET, true);
                break;
            case 'DELETE':
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            case 'PUT':
                curl_setopt($this->ch, CURLOPT_PUT, true);
                break;
            default:
                curl_setopt($this->ch, CURLOPT_HTTPGET, true);
                break;
        }

        return $this;
    }

    /**
     * Enable SSL connection
     *
     * To enable the SSL connection, provide the full path to the certificate on
     * your server. For more information:
     *
     * 1) How to convert a CA to pem file:
     * http://nl3.php.net/manual/en/function.curl-setopt.php#110457
     *
     * 2) How to get the certificate and set up everyhing:
     * http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
     *
     * @param type $cert_path
     */
    public function enableSsl($cert_path){
       curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 1);
       curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
       curl_setopt($this->ch, CURLOPT_SSLVERSION, 3);
       curl_setopt($this->ch, CURLOPT_CAINFO, $cert_path);
       return $this;
    }

    /**
     * Disable SSL support
     *
     * @return \Prodio\Http\Client
     */
    public function disableSsl(){
       curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
       return $this;
    }

    /**
     * Set default options
     *
     * @todo Create setters and getters or options
     * @todo Headers management
     */
    private function __setDefaults()
    {
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_ENCODING, '');
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("Accept: gzip,deflate"));
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_FRESH_CONNECT, 1);

        return $this;
    }

    /**
     * Set asynchronous options
     * 
     * There are some examples on the Internet, which are using CURL_TIMEOUT_MS, but if the value is less
     * then a few seconds, a timeout error will be thrown.
     * @return \Prodio\Http\Client
     */
    private function __setAsyncOptions()
    {
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 5);
        return $this;
    }

    /**
     * Builds the query string
     *
     * Builds an HTTP query string out of the Client::params property. If params
     * property holds an array, it could be one-dimensional or multi-dimensional.
     * If params property holds an object, only its public properties will be used.
     *
     * @return string Key/value pairs, e.g. param1=value1&param2=value2
     */
    private function __buildQuery()
    {

        $query_str = '';

        if (is_array($this->params) || is_object($this->params)) {
            $query_str = http_build_query($this->params);
        } else {
            $query_str = $this->params;
        }

        return $query_str;
    }

}
