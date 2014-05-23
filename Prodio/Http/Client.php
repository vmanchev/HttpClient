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
        $result = curl_exec($this->ch);

        if ($result === false) {
            throw new CurlException($this->ch);
        }

        curl_close($this->ch);

        return $result;
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

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->__buildQuery());

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
                curl_setopt($this->ch, CURLOPT_POST, 1);
                break;
            case 'GET':
                curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
                break;
            default:
                curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
                break;
        }

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
