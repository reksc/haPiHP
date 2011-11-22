<?php

class BaseClient {
    // BaseClient class to be extended by specific hapi clients

    // Declare variables
    protected $HAPIKey;
    protected $API_PATH;
    protected $API_VERSION;
    protected $isTest = false;
    protected $PATH_DIV = '/';
    protected $KEY_PARAM = '?hapikey=';
    protected $PROD_DOMAIN = 'https://hubapi.com';
    protected $QA_DOMAIN = 'https://hubapiqa.com';

    /**
    * Constructor.
    *
    * @param $HAPIKey: String value of HubSpot API Key for requests
    **/
    function __construct($HAPIKey) {
        $this->HAPIKey = $HAPIKey;
    }

    /**
    * Returns API_PATH that is set in specific hapi clients.  All
    * clients that extend BaseClient should set $API_PATH to the 
    * base path for the API (e.g.: the leads api sets the value to
    * 'leads')
    *
    * @throws exception
    **/
    protected function get_api() {
        if ($this->isBlank($this->API_PATH)) {
            throw new Exception('API_PATH must be defined');        
        } else {
            return $this->API_PATH;
        }
    }

    /**
    * Returns API_VERSION that is set in specific hapi clients. All
    * clients that extend BaseClient should set $API_VERSION to the
    * version that the client is developed for (e.g.: the leads v1
    * client sets the value to 'v1')
    *
    * @throws exception
    **/
    protected function get_api_version() {
        if ($this->isBlank($this->API_VERSION)) {
            throw new Exception('API_VERSION must be defined');
        } else {
            return $this->API_VERSION;
        }
    }
    
    /**
    * Allows developer to set testing flag to true in order to 
    * execute api requests against hubapiqa.com
    *
    * @param $testing: Boolean
    **/
    public function set_is_test($testing) {
        if ($testing==true) {
            $this->isTest = true;
        }
    }

    /**
    * Returns the hapi domain to use for requests based on isTesting
    *
    * @returns: String value of domain, including https protocol
    **/
    protected function get_domain() {
       if ($this->isTest == true){
           return $this->QA_DOMAIN;
       } else {
           return $this->PROD_DOMAIN;
       }
    }

    /**
    * Creates the url to be used for the api request
    *
    * @param endpoint: String value for the endpoint to be used (appears after version in url)
    * @param params: Array containing query parameters and values
    *
    * @returns String
    **/
    protected function get_request_url($endpoint,$params) {
        $paramstring = '';
        if ($params != null) {
            foreach ($params as $parameter => $value) {
                 $paramstring = $paramstring . '&' . $parameter . '=' . $value;
            }
        }
        return $this->get_domain() . $this->PATH_DIV . 
               $this->get_api() . $this->PATH_DIV . 
               $this->get_api_version() . $this->PATH_DIV . 
               $endpoint . 
               $this->KEY_PARAM . $this->HAPIKey .  
               $paramstring;
    }

    /**
    * Executes HTTP GET request
    *
    * @param URL: String value for the URL to GET
    *
    * @returns: Body of request result
    * 
    * @throws exception
    **/
    protected function execute_get_request($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ( $errno > 0) {
            throw new Exception('cURL error: ' + $error);
        } else {
            return $output;
        }
    }
    
    /**
    * Executes HTTP POST request
    *
    * @param URL: String value for the URL to POST to
    * @param fields: Array containing names and values for fields to post
    *
    * @returns: Body of request result
    * 
    * @throws exception
    **/
    protected function execute_post_request($url, $fields) {
        $strPost = "";
        
        // Turn $fields into POST-compatible list of parameters
        foreach ($fields as $fieldName => $fieldValue)
        {
            $strPost .= urlencode($fieldName) . '=';
            $strPost .= urlencode($fieldValue);
            $strPost .= '&';
        }
        
        $strPost = rtrim($strPost, '&'); // nuke the final ampersand
        
        // intialize cURL and send POST data
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $strPost);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch); 
        if ($errno > 0) {
            throw new Exception ('cURL error: ' + $error);
        } else {
            return $output;
        }
    }

    /**
    * Executes HTTP PUT request
    *
    * @param URL: String value for the URL to PUT to
    * @param body: String value of the body of the PUT request
    *
    * @returns: Body of request result
    * 
    * @throws exception
    **/
    protected function execute_put_request($url, $body) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($body)));
        //curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$body);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        $apierr = curl_errno($ch); 
        $errmsg = curl_error($ch); 
        curl_close($ch);
        if ($apierr > 0) {
            throw new Exception('cURL error: ' + $errmsg);
        } else {
            return $result;
        }
    }
    
    protected function execute_delete_request($url) {
        
    }

    /**
    * Utility function used to determine if variable is empty
    *
    * @param s: Variable to be evaluated
    *
    * @returns Boolean
    **/
    protected function isBlank ($s) {
        if ((trim($s)=='')||($s==null)) {
            return true;
        } else {
            return false;
        }
    }

}
?>
