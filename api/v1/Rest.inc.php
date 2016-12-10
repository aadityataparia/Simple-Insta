<?php

class RestUtils
{
    public static function processRequest()
    {
        $return_obj = new RestRequest();
        $data = array();
        $request = array();
        if (isset($_REQUEST['url'])) {
            $data = explode("/", $_REQUEST['url']);
        } else {
          header($_SERVER["SERVER_PROTOCOL"].' 404 Not Found');
          exit;
        }
        if(isset($_REQUEST)){
          foreach ($_REQUEST as $key => $value) {
              if ($key != 'url') {
                  $request[$key] = $value;
              }
          }
        }
        // foreach ($_GET as $key => $value) {
        //     $request[$key] = $value;
        // }
        // foreach ($_POST as $key => $value) {
        //     $request[$key] = $value;
        // }
        $put =  file_get_contents("php://input", true);
        //parse_str(file_get_contents("php://input"), $put);
        $put = json_decode($put, true);
        if(is_array($put)){
          foreach ($put as $key => $value) {
              $request[$key] = $value;
          }
        }
        $return_obj->setMethod(strtoupper($_SERVER['REQUEST_METHOD']));
        $return_obj->setRequestVars($request);
        $return_obj->setData($data);

        if (isset($data['data'])) {
          $return_obj->setData(json_decode($data['data']));
        }

        return $return_obj;
    }

    public static function getStatusCodeMessage($status)
    {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = array(
            200 => 'OK',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            304 => 'Not Modified',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            408 => 'Request Timeout',
            410 => 'Token Expired',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            601 => 'Payment not authorized',
            602 => 'Duplicate POST not allowed'
        );

        return (isset($codes[$status])) ? $codes[$status] : '';
    }
}

class RestRequest
{
    private $request_vars;
    private $data;
    private $http_accept;
    private $method;

    public function __construct()
    {
        $this->request_vars      = array();
        $this->data              = array();
        $this->method            = 'GET';
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function setRequestVars($request_vars)
    {
        $this->request_vars = $request_vars;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getHttpAccept()
    {
        return $this->http_accept;
    }

    public function getRequestVars()
    {
        return $this->request_vars;
    }
}
