<?php
namespace Alloy;

/**
 * REST Client
 * Makes RESTful HTTP requests on webservices
 *
 * @package Alloy
 * @link http://alloyframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Client
{
    /**
     * GET
     * 
     * @param string $url URL to perform action on
     * @param optional array $params Array of key => value parameters to pass
     */
    public function get($url, array $params = array())
    {
        return $this->_fetch($url, $params, 'GET');
    }
    
    
    /**
     * POST
     * 
     * @param string $url URL to perform action on
     * @param optional array $params Array of key => value parameters to pass
     */
    public function post($url, array $params = array())
    {
        return $this->_fetch($url, $params, 'POST');
    }
    
    
    /**
     * PUT
     * 
     * @param string $url URL to perform action on
     * @param optional array $params Array of key => value parameters to pass
     */
    public function put($url, array $params = array())
    {
        return $this->_fetch($url, $params, 'PUT');
    }
    
    
    /**
     * DELETE
     * 
     * @param string $url URL to perform action on
     * @param optional array $params Array of key => value parameters to pass
     */
    public function delete($url, array $params = array())
    {
        return $this->_fetch($url, $params, 'DELETE');
    }
    
    
    /**
     * Fetch a URL with given parameters
     */
    protected function _fetch($url, array $params = array(), $method = 'GET')
    {
        $method = strtoupper($method);
        
        $urlParts = parse_url($url);
        $queryString = http_build_query($params);
        
        // Append params to URL as query string if not a POST
        if(strtoupper($method) != 'POST') {
            $url = $url . "?" . $queryString;
        }
        
        //echo $url;
        //var_dump("Fetching External URL: [" . $method . "] " . $url, $params);
        
        // Use cURL
        if(function_exists('curl_init')) {
            $ch = curl_init($urlParts['host']);
            
            // METHOD differences
            switch($method) {
                case 'GET':
                    curl_setopt($ch, CURLOPT_URL, $url . "?" . $queryString);
                break;
                case 'POST':
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
                break;
                 
                case 'PUT':
                    curl_setopt($ch, CURLOPT_URL, $url);
                    $putData = file_put_contents("php://memory", $queryString);
                    curl_setopt($ch, CURLOPT_PUT, true);
                    curl_setopt($ch, CURLOPT_INFILE, $putData);
                    curl_setopt($ch, CURLOPT_INFILESIZE, strlen($queryString));
                break;
                 
                case 'DELETE':
                    curl_setopt($ch, CURLOPT_URL, $url . "?" . $queryString);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            }
            
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the data
            curl_setopt($ch, CURLOPT_HEADER, false); // Get headers
            
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            
            // HTTP digest authentication
            if(isset($urlParts['user']) && isset($urlParts['pass'])) {
                $authHeaders = array("Authorization: Basic ".base64_encode($urlParts['user'].':'.$urlParts['pass']));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $authHeaders);
            }
            
            $response = curl_exec($ch);
            $responseInfo = curl_getinfo($ch);
            curl_close($ch);
            
        // Use sockets... (eventually)
        } else {
            throw new Exception(__METHOD__ . " Requres the cURL library to work.");
        }
        
        // Only return false on 404 or 500 errors for now
        if($responseInfo['http_code'] == 404 || $responseInfo['http_code'] == 500) {
            $response = false;
        }
        
        return $response;
    }
}