<?php

class AuthTuneout
{
    private $userID;
    private $data;
    private $request;
    private $outputPHP;
    private $conn;
    private $statement;

    public function __construct($data)
    {
        $this->data = $data->getData();
        $this->request = $data->getRequestVars();
        $this->outputPHP = array();
        $this->getConn();
        $this->userID = $this->validateUser();
        $this->gotError(200);
        $this->outputPHP['ok'] = true;
        $this->outputPHP['method']=$data->getMethod();
        $this->outputPHP['function']=$this->data[0];
        if(isset($this->userID) && $this->userID > 0 && $this->request['type'] != "password"){
          $this->data['signupidbam'] = $this->userID;
          $this->signin('id');
          return false;
        }
        switch ($data->getMethod()) {
          case 'POST':
            switch ($this->data[0]) {
              case 'signin':
                $this->signup();
                break;
              case 'signup':
                $this->signup();
                break;
              case 'signout':
                $this->signout();
                break;
              case 'verify':
                $this->verify();
                break;
              case 'magiclink':
                $this->sendmagiclink($this->request['email']);
                break;
              default:
                $this->gotError(406);
                break;
            }
            break;
          case 'GET':
            switch ($this->data[0]) {
              case 'verify':
                $this->verify();
                break;
              default:
                $this->gotError(406);
                break;
            }
            break;
          default:
            $this->gotError(405);
            break;
        }
        if ($this->statement) {
            $this->PDOerror($this->statement);
        }
        $this->conn = null;
        $this->lastModified();
    }

    private function signin($imp)
    {
      require_once './SIGN/signin.php';
    }

    private function signup()
    {
      require_once './SIGN/signup.php';
    }

    private function signout()
    {
      require_once './SIGN/signout.php';
    }

    private function verify()
    {
      require_once './SIGN/verify.php';
    }

    private function sendmagiclink($emailtos)
    {
      require_once './SIGN/magic.php';
    }

    public function addtoPHP($key, $value)
    {
        $this->outputPHP[$key] = $value;
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
          $this->gotError(204);
        }
        $this->addtoPHP('count', $count);
    }

    private function getConn()
    {
        include_once('../config.php');
        $conne = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);      // returing connection resource
        $this->conn = $conne;
        $this->conn->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $this->conn;
    }

    private function gotError($num){
      $this->outputPHP['ok']=false;
      $this->outputPHP['status'] = $num;
      $this->outputPHP['message'] = RestUtils::getStatusCodeMessage($num);
    }

    private function passToHash($pass){
      return sha1($pass);
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

        $this->userID = $result[0]['userid'];
        $this->outputPHP['logged'] = true;
        $this->outputPHP['userid'] = $result[0]['userid'];
        $this->outputPHP['sessionpass'] = $sessionpass;
        return $result[0]['userid'];
      }
      return 0;
    }

    private function uniqueid( $type = 'alnum', $length = 16, $time = true){
      switch ( $type ) {
        case 'alnum':
          $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
          break;
        case 'alpha':
          $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
          break;
        case 'hexdec':
          $pool = '0123456789abcdef';
          break;
        case 'numeric':
          $pool = '0123456789';
          break;
        case 'nozero':
          $pool = '123456789';
          break;
        case 'distinct':
          $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
          break;
        default:
          $pool = (string) $type;
          break;
      }
      $crypto_rand_secure = function ( $min, $max ) {
        $range = $max - $min;
        if ( $range < 0 ) return $min; // not so random...
        $log    = log( $range, 2 );
        $bytes  = (int) ( $log / 8 ) + 1; // length in bytes
        $bits   = (int) $log + 1; // length in bits
        $filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1
        do {
          $rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
          $rnd = $rnd & $filter; // discard irrelevant bits
        } while ( $rnd >= $range );
        return $min + $rnd;
      };
      $token = "";
      $max   = strlen( $pool );
      for ( $i = 0; $i < $length; $i++ ) {
        $token .= $pool[$crypto_rand_secure( 0, $max )];
      }
      if($time) return urlencode($token.microtime());
      return urlencode($token);
    }

    private function lastModified(){
      $mostRecent = 0;
      // if($this->outputPHP['error'] == false && isset($this->outputPHP['results'][0]['time_modified'])){
      if($this->outputPHP['ok'] == true && isset($this->outputPHP['results'])){
        foreach($this->outputPHP['results'] as $key => $value){
          if(is_array(current($value))){
            $mostRecent2 = 0;
            foreach($value as $key2 => $value2){
              $datetime2 = new DateTime($value2['time_modified']);
              $curDate2 = $datetime2->setTimezone(new DateTimeZone('GMT'))->getTimestamp();
              if ($curDate2 > $mostRecent2) {
                $mostRecent2 = $curDate2;
              }
            }
              $curDate = $mostRecent2;
          } else {
            $datetime = new DateTime($value['time_modified']);
            $curDate = $datetime->setTimezone(new DateTimeZone('GMT'))->getTimestamp();
          }
          if ($curDate > $mostRecent) {
            $mostRecent = $curDate;
          }
          $this->outputPHP['results'][$key]['time_modified'] = $curDate;
        }

        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $mostRecent).' GMT');
        $this->outputPHP['time_modified'] = $mostRecent;
        $this->lm = $mostRecent;;
        return 1;
      } else {
        return 0;
      }

    }
}
