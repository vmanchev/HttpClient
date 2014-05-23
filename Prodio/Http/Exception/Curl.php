<?php
/**
 * Exception for cUrl-based HTTP client.
 * 
 * Using a cUrl handle, set the exception code and message, in case of failed 
 * cUrl request.
 * 
 * @categoty Prodio
 * @package  Http\Exception
 * @version  0.0.1
 * @author   Venelin Manchev <manchev@phpwisdom.com>
 */

namespace Prodio\Http\Exception;

/**
 * Exception for cUrl-based HTTP client.
 */
class Curl extends \Exception
{
    /**
     * Error code
     * @var mixed 
     */
    protected $code;
    
    /**
     * Error message
     * @var string
     */
    protected $message;
    
    /**
     * Class constructor 
     * 
     * @param cUrl handle $ch
     */
    public function __construct($ch)
    {   
        parent::__construct(curl_error($ch), curl_errno($ch), null);
    }

    public function __toString()
    {
        return $this->getCode() . ': '.$this->getMessage();
    }
}