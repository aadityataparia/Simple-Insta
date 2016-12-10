<?php

if(array_key_exists('logged', $this->outputPHP) && $this->outputPHP['logged'] == true && $this->request['type'] != 'password'){
  $imp = 'id';
}

switch ($imp) {
  case 'email':
    $exec_var = array('email' => $this->request['email']);
    $where = 'email = :email';
    break;
  case 'id':
  	$exec_var = array('id' => $this->userID);
    $where = 'id = :id';
    break;
  default:
    $this->gotError(401);
    return false;
    break;
}

$select="SELECT *, CHAR_LENGTH(password) as password
from users
WHERE ".$where;
$stmt=$this->conn->prepare($select);
$stmt->execute($exec_var);
$this->outputPHP['type'] = 'user';
$this->outputPHP['results'] = $stmt->fetchAll();
$id = $this->outputPHP['results'][0]['id'];

$useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Not Received';
$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Not Received';
$fip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : 'Not Received';
$sessionpass = $this->uniqueid();

if($imp != 'id'){
  $select="SELECT *
  from logins
  WHERE userid = :userid AND useragent = :useragent AND expiry > NOW() LIMIT 1";
  $stmt=$this->conn->prepare($select);
  $stmt->execute(array("userid" => $id, "useragent" => $useragent));
  $this->outputPHP['type'] = 'user';
  $session = $stmt->fetchAll();
  if(sizeof($session) < 1){
    $a = array('userid' => $id, 'sessionpass' => $sessionpass, 'useragent' => $useragent, 'ip' => $ip, 'fip' => $fip, 'expiry' => date("Y-m-d H:i:s", time()+60*60*24));
    $fields=array_keys($a);
    $values=array_values($a);
    $fieldlist=implode(',',$fields);
    $qs=str_repeat("?,",count($fields)-1);
    $insert="INSERT into logins ($fieldlist) values (${qs}?)";
    $stmt=$this->conn->prepare($insert);
    $stmt->execute($values);
    $id = $this->conn->lastInsertId();
    $this->outputPHP['sessionpass'] = $sessionpass;
  } else {
    $this->outputPHP['sessionpass'] = $session[0]["sessionpass"];
    $select = 'UPDATE logins
    SET expiry = NOW() + INTERVAL 1 DAY
    WHERE sessionpass = :sessionpass AND expiry > NOW() LIMIT 1';
    $stmt = $this->conn->prepare($select);
    $stmt->execute(array('sessionpass' => $session[0]["sessionpass"]));
  }
  $this->outputPHP["function"] = "signin";
}
