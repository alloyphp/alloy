<?php
namespace Alloy\Module;

/**
 * Generic module response template object
 * 
 *   Allows Module Controllers to respond in multiple possible formats
 *   and still pass HTTP response information down the chain without
 *   setting it directly on the main HTTP Response.
 * 
 *   This lets plugins intercept individual HMVC dispatch responses and
 *   modify them before the primary full response is sent.
 * 
 * @package Alloy Framework
 * @link http://alloyframework.com/
 */
class Response
{
    protected $_status = 200;
    protected $_content;
    protected $_errors = array();
    protected $_headers = array();


    /**
     * HTTP Status code
     *
     * @param int $statusCode HTTP status code to return
     */
    public function status($statusCode = null)
    {
        if(null === $statusCode) {
            return $this->_status;
        }

        $this->_status = $statusCode;
        return $this;
    }


    /**
     * Content getter/setter
     */
    public function content($content = null)
    {
        if(null === $content) {
            return $this->_content;
        }

        $this->_content = $content;
        return $this;
    }


    /**
     * Errors
     *
     * @param mixed $errors Error array to return
     */
    public function errors($errors = null)
    {
        if(null === $errors) {
            return $this->_errors;
        }

        $this->_errors += $errors;
        return $this;
    }


    /**
     * Headers
     *
     * @param mixed $headers Headers to send with response
     */
    public function headers($headers = null)
    {
        if(null === $headers) {
            return $this->_headers;
        }

        $this->_headers += $headers;
        return $this;
    }


    /**
     * Converts object content to string on the fly
     *
     * @return  string
     */
    public function __toString()
    {
        // Exceptions cannot be thrown in __toString method (results in fatal error)
        // We have to catch any that may be thrown and return a string
        try {
            $content = $this->content();
        } catch(\Exception $e) {
            $content = "<strong>" . __CLASS__ . "::__toString ERROR:</strong><br />" . $e->getMessage();
            if(\Kernel()->config('debug')) {
                \Kernel()->dump($e->getTraceAsString());
            }
        }
        return $content;
    }
}