<?php

class RestCRUD
{
    private $userID;
    private $data;
    private $request;
    private $outputPHP;
    private $conn;
    private $statement;
    private $ea = false;
    //private $http_accept;
    //private $method;

    public function __construct($data)
    {
        $this->data = $data->getData();
        $this->request = $data->getRequestVars();
        $this->outputPHP = array();
        $this->getConn();
        $this->userID = $this->validateUser();
        $this->gotError(200);
        $this->outputPHP['ok'] = true;
        $this->outputPHP['type']=$this->data[0];
        $this->outputPHP['method']=$data->getMethod();
        if(!$this->checkMethod($data->getMethod())){
          return false;
        }
        switch ($data->getMethod()) {
          case 'GET':
            $stmt = $this->GETq();
            break;
          case 'HEAD':
            $stmt = $this->GETq();
            break;
          case 'PUT':
            $stmt = $this->PUTq();
            break;
          case 'POST':
            $stmt = $this->POSTq();
            break;
          case 'DELETE':
            $stmt = $this->DELETEq();
            break;
        }
        if ($this->statement) {
            $this->PDOerror($this->statement);
        }
        $this->conn = null;
        $this->lastModified();
    }

    private function GETq()
    {
      require_once './CRUD/get.php';
    }

    private function POSTq()
    {
      require_once './CRUD/post.php';
    }

    private function PUTq()
    {
      require_once './CRUD/put.php';
    }

    private function DELETEq()
    {
      require_once './CRUD/delete.php';
    }

    public function printPHP()
    {
        var_dump($this->outputPHP);
    }

    public function printJSON()
    {
        if (isset($this->request['pretty'])) {
            print_r(json_encode($this->outputPHP, JSON_PRETTY_PRINT));
        } else {
            print_r(json_encode($this->outputPHP));
        }
    }
    public function printXML()
    {
        $data = $this->outputPHP;
        $xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
        function array_to_xml($data, $xml_data){
          foreach ($data as $key => $value) {
              if (is_array($value)) {
                  $key = is_numeric($key) ? "item$key" : $key;
                  $subnode = $xml_data->addChild("$key");
                  array_to_xml($value, $subnode);
              } else {
                  $key = is_numeric($key) ? "item$key" : $key;
                  $xml_data->addChild("$key", "$value");
              }
          }
          return $xml_data;
        }
        $xml_data = array_to_xml($data, $xml_data);
        print $xml_data->asXML();
    }

    private function PDOerror($stmt)
    {
        $error = $stmt->errorInfo();
        $count = 0;
        if(isset($this->outputPHP['results'])){
          $count = sizeof($this->outputPHP['results']);
        }
        if ($error[0]>0) {
            $this->outputPHP['error'] = true;
            $this->outputPHP['message'] = "not successfull";
            $this->outputPHP['pdo_err_code'] = $error[1];
            $this->outputPHP['pdo_err_message'] = $error[2];
        } else if($count == 0 && $this->outputPHP['method'] == 'GET'){
          $this->gotError(404);
        }
        $this->addtoPHP('count', $count);
    }

    private function getConn()
    {
      include_once('config.php');
      // Connecting to mysql database
      $conne = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);      // returing connection resource
      $this->conn = $conne;
      $this->conn->setAttribute(
      PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
      // $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
      $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      return $this->conn;
    }

    public function addtoPHP($key, $value)
    {
        $this->outputPHP[$key] = $value;
    }

    private function checkMethod($method){
      require_once("./methods.php");
      if(!isset($methodsAllowed[$method])){
        $this->gotError(405);
        return false;
      } else if(!in_array($this->data[0],$methodsAllowed[$method])){
        $this->gotError(400);
        return false;
      }
      return true;
    }

    private function validateUser(){
      // $this->userID = 1;
      // return 1;
      if(isset($this->request['sessionpass'])){
        $sessionpass = $this->request['sessionpass'];
      } else if(isset($_SERVER['HTTP_X_SESSION_PASS'])){
        $sessionpass = $_SERVER['HTTP_X_SESSION_PASS'];
      } else {
        return 0;
      }
      $select = 'SELECT *
      FROM logins
      WHERE sessionpass = :sessionpass LIMIT 1';//AND expiry > NOW() LIMIT 1';
      $stmt = $this->conn->prepare($select);
      $stmt->execute(array('sessionpass' => $sessionpass));
      $result = $stmt->fetchAll();
      if(sizeof($result) > 0){
        $select = 'UPDATE logins
        SET expiry = NOW() + INTERVAL 1 DAY
        WHERE sessionpass = :sessionpass LIMIT 1';//AND expiry > NOW() LIMIT 1';
        $stmt = $this->conn->prepare($select);
        $stmt->execute(array('sessionpass' => $sessionpass));

        $select = 'SELECT id
        FROM users
        WHERE id = :id';
        $stmt = $this->conn->prepare($select);
        $stmt->execute(array('id' => $result[0]['userid']));
        $result = $stmt->fetchAll();
        $this->outputPHP['logged'] = true;
        $this->outputPHP['userid'] = $result[0]['id'];
        $this->outputPHP['sessionpass'] = $sessionpass;
        return $result[0]['id'];
      }
      return 0;
    }

    private function gotError($num){
      $this->outputPHP['ok']=false;
      $this->outputPHP['status'] = $num;
      $this->outputPHP['message'] = RestUtils::getStatusCodeMessage($num);
    }

    private function lastModified(){
      $mostRecent = 0;
      // if($this->outputPHP['error'] == false && isset($this->outputPHP['results'][0]['time_modified'])){
      if($this->outputPHP['ok'] == true && isset($this->outputPHP['results'])){
        foreach($this->outputPHP['results'] as $key => $value){
          if(isset($value[0]) && is_array($value[0])){
            foreach($value as $key2 => $value2){
              if(isset($value2['time_modified'])){
                $cur = strtotime($this->outputPHP['results'][$key][$key2]['time_modified']);
                if($cur > $mostRecent){
                  $mostRecent = $cur;
                }
                $this->outputPHP['results'][$key][$key2]['time_modified'] = $cur;
              }
            }
          } else {
            if(isset($value['time_modified']) && strtotime($value['time_modified']) > $mostRecent){
              $cur = strtotime($this->outputPHP['results'][$key]['time_modified']);
              if($cur > $mostRecent){
                $mostRecent = $cur;
              }
              $this->outputPHP['results'][$key]['time_modified'] = $cur;
            }
          }
        }
        $this->outputPHP['time_modified'] = $mostRecent;
        $this->lm = $mostRecent;
        return 1;
      } else {
        return 0;
      }

    }

    public function lm(){
      if(isset($this->outputPHP['time_modified'])){
        return $this->outputPHP['time_modified'];
      } else {
        return intval(microtime(true)*1000);
      }
    }
}
